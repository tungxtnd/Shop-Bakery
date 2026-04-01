<?php


session_start();
include '../../connectdb.php';


// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../homepage.php");
    exit;
}


// --- Handle order status update ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $oid = intval($_POST['order_id']);
    $status = $_POST['status'];
    $allowed = ['pending', 'shipped', 'delivered', 'cancelled'];


    // Get current status and user_id before update
    $cur = $conn->prepare("SELECT status, user_id FROM orders WHERE id = ?");
    $cur->bind_param("i", $oid);
    $cur->execute();
    $cur->bind_result($old_status, $user_id);
    $cur->fetch();
    $cur->close();


    if (in_array($status, $allowed)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $oid);
        $stmt->execute();
        $stmt->close();


        // Add notification if status changes from pending to shipped
        if ($old_status === 'pending' && $status === 'shipped') {
            $type = 'order_status';
            $message = 'Your order #' . $oid . ' has been shipped.';
            $created_at = date('Y-m-d H:i:s');
            $noti_stmt = $conn->prepare("INSERT INTO notifications (user_id, target_user_id, order_id, type, message, created_at) VALUES (?, ?, ?, ?, ?, ?)");
            $noti_stmt->bind_param("iiisss", $user_id, $user_id, $oid, $type, $message, $created_at);
            $noti_stmt->execute();
            $noti_stmt->close();
        }
    }
    // Refresh to avoid resubmission
    header("Location: mana_orders.php");
    exit;
}


// Fetch all orders with customer info
$sql = "
    SELECT o.id, o.order_date, o.status, o.total_amount, u.full_name, u.email, u.phone, u.address
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.order_date DESC
";
$result = $conn->query($sql);


// Fetch order items for a specific order if requested
$order_items = [];
if (isset($_GET['order_id'])) {
    $oid = intval($_GET['order_id']);
    $item_sql = "
        SELECT oi.*, p.name as product_name, s.name as card_name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        LEFT JOIN services s ON oi.service_id = s.id
        WHERE oi.order_id = $oid
    ";
    $item_result = $conn->query($item_sql);
    while ($row = $item_result->fetch_assoc()) {
        $order_items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Orders</title>
    <style>
        body { background: #f8f8f8; font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .admin-navbar {
            background: #5C3A21;
            padding: 0;
            margin: 0;
            display: flex;
            align-items: center;
            height: 60px;
        }
        .admin-navbar a {
            color: #fff;
            text-decoration: none;
            padding: 0 32px;
            font-size: 18px;
            line-height: 60px;
            display: block;
            transition: background 0.2s;
        }
        .admin-navbar a:hover, .admin-navbar a.active {
            background: #7A5230;
        }
        .container { max-width: 1100px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 12px #eee; padding: 32px; }
        h2 { color: #7A5230; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: center; border-bottom: 1px solid #eee; }
        th { background: #faf6f8; color: #7A5230; }
        .view-btn {
            background: #7A5230; color: #fff; border: none; border-radius: 4px;
            padding: 6px 14px; cursor: pointer; transition: background 0.2s;
            text-decoration: none;
        }
        .view-btn:hover { background: #5C3A21; }
        .status-select { padding: 4px 8px; border-radius: 4px; }
        .order-items { margin-top: 30px; }
        .order-items th { background: #f0f0f0; color: #333; }
        .back-link { color: #7A5230; text-decoration: none; margin-bottom: 20px; display: inline-block; }
    </style>
</head>
<body>
    <nav class="admin-navbar">
        <a href="dashboard.php">Dashboard</a>
        <a href="mana_orders.php"  class="active">Manage Orders</a>
        <a href="mana_products.php">Manage Products</a>
        <a href="mana_reviews.php">Manage Reviews</a>
        <a href="mana_users.php">Manage Users</a>
        <a href="mana_noti.php">Manage Notifications</a>
        <a href="/views/auth/logout.php" style="margin-left:auto;" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
    </nav>
    <div class="container">
        <h2>Order Management</h2>
        <?php if (isset($_GET['order_id'])): ?>
            <a href="mana_orders.php" class="back-link">&larr; Back to all orders</a>
            <h3>Order #<?php echo intval($_GET['order_id']); ?> Details</h3>
            <table class="order-items">
                <tr>
                    <th>Product</th>
                    <th>Card</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>
                <?php foreach ($order_items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td><?php echo $item['card_name'] ? htmlspecialchars($item['card_name']) : '<span style="color:#888;">None</span>'; ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo number_format($item['price']); ?> VND</td>
                    <td><?php echo number_format($item['price'] * $item['quantity']); ?> VND</td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <table>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Created At</th>
                    <th>Action</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td><?php echo htmlspecialchars($row['address']); ?></td>
                    <td><?php echo number_format($row['total_amount']); ?> VND</td>
                    <td>
                        <form method="post" action="" style="margin:0;">
                            <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                            <select name="status" class="status-select"
                                onchange="this.form.submit()"
                                <?php if($row['status']=='delivered' || $row['status']=='cancelled') echo 'disabled'; ?>>
                                <option value="pending" <?php if($row['status']=='pending') echo 'selected'; ?>>Pending</option>
                                <option value="shipped" <?php if($row['status']=='shipped') echo 'selected'; ?>>Shipped</option>
                                <option value="delivered" <?php if($row['status']=='delivered') echo 'selected'; ?>>Delivered</option>
                                <option value="cancelled" <?php if($row['status']=='cancelled') echo 'selected'; ?>>Cancelled</option>
                            </select>
                        </form>
                    </td>
                    <td><?php echo $row['order_date']; ?></td>
                    <td>
                        <a href="mana_orders.php?order_id=<?php echo $row['id']; ?>" class="view-btn">View</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
