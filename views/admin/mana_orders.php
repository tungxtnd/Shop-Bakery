<?php
session_start();
include '../../connectdb.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../homepage.php");
    exit;
}

// --- Xử lý cập nhật trạng thái đơn hàng ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'], $_POST['status'])) {
    $oid = intval($_POST['order_id']);
    $status = $_POST['status'];
    // Cập nhật danh sách trạng thái mới chuẩn Database
    $allowed = ['Pending', 'Paid', 'Processing', 'Ready for Delivery', 'Delivering', 'Completed', 'Cancelled'];

    // Lấy trạng thái cũ và user_id trước khi update
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

        // Gửi thông báo nếu chuyển trạng thái sang Delivering (Đang giao)
        if ($old_status !== 'Delivering' && $status === 'Delivering') {
            $type = 'order_status';
            $message = 'Your order #' . $oid . ' is now being delivered.';
            $created_at = date('Y-m-d H:i:s');
            $noti_stmt = $conn->prepare("INSERT INTO notifications (user_id, target_user_id, order_id, type, message, created_at) VALUES (?, ?, ?, ?, ?, ?)");
            $noti_stmt->bind_param("iiisss", $user_id, $user_id, $oid, $type, $message, $created_at);
            $noti_stmt->execute();
            $noti_stmt->close();
        }
    }
    header("Location: mana_orders.php");
    exit;
}

// Lấy danh sách đơn hàng
$sql = "
    SELECT o.id, o.order_date, o.status, o.total_amount, u.full_name, u.email, u.phone, u.address
    FROM orders o
    JOIN users u ON o.user_id = u.id
    ORDER BY o.order_date DESC
";
$result = $conn->query($sql);

// Lấy chi tiết món ăn nếu có yêu cầu
$order_items = [];
if (isset($_GET['order_id'])) {
    $oid = intval($_GET['order_id']);
    $item_sql = "
        SELECT oi.*, p.name as product_name, s.name as service_name
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
    <title>Manage Orders | Bakes Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; margin: 0; display: flex; height: 100vh; overflow: hidden; }
        
        /* SIDEBAR DỌC */
        .sidebar { width: 260px; background: #343a40; color: #fff; display: flex; flex-direction: column; flex-shrink: 0; }
        .sidebar-brand { padding: 25px 20px; font-size: 1.6em; font-weight: bold; text-align: center; border-bottom: 1px solid #4f5962; }
        .sidebar-brand span { color: #b97a56; }
        .nav-menu { list-style: none; padding: 0; margin: 15px 0; flex: 1; overflow-y: auto; }
        .nav-menu li a { display: block; padding: 12px 20px; color: #c2c7d0; text-decoration: none; transition: 0.3s; }
        .nav-menu li a i { margin-right: 12px; width: 20px; text-align: center; }
        .nav-menu li a:hover, .nav-menu li a.active { background: #494e53; color: #fff; border-left: 4px solid #b97a56; }
        
        /* MAIN WRAPPER */
        .main-wrapper { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .top-header { background: #fff; padding: 15px 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; z-index: 10; }
        .content { padding: 25px; overflow-y: auto; flex: 1; }

        .card { background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 25px; border-top: 4px solid #b97a56; }
        h2 { color: #333; margin-top: 0; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.95em; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f9f9f9; color: #555; font-weight: bold; }
        
        .status-select { padding: 6px; border-radius: 4px; border: 1px solid #ddd; width: 100%; cursor: pointer; }
        .view-btn { background: #b97a56; color: #fff; border: none; border-radius: 4px; padding: 6px 12px; text-decoration: none; font-size: 0.9em; }
        .view-btn:hover { background: #9c6343; }
        .back-link { color: #b97a56; text-decoration: none; font-weight: bold; margin-bottom: 15px; display: inline-block; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand"><i class="fa-solid fa-cake-candles"></i> Bakes <span>Admin</span></div>
        <ul class="nav-menu">
            <li><a href="dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
            <li><a href="mana_orders.php" class="active"><i class="fa-solid fa-cart-shopping"></i> Manage Orders</a></li>
            <li><a href="mana_products.php"><i class="fa-solid fa-cookie-bite"></i> Manage Products</a></li>
            <li><a href="mana_reviews.php"><i class="fa-solid fa-star"></i> Manage Reviews</a></li>
            <li><a href="mana_users.php"><i class="fa-solid fa-users"></i> Manage Users</a></li>
            <li><a href="mana_noti.php"><i class="fa-solid fa-bell"></i> Notifications</a></li>
            <li style="margin-top: 20px;"><a href="/views/auth/logout.php" onclick="return confirm('Logout?');" style="color: #ff7675;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-wrapper">
        <div class="top-header">
            <div style="font-weight: bold; color: #555;">Order Management</div>
            <div style="color: #888;">Logged in as <strong>Admin</strong></div>
        </div>

        <div class="content">
            <div class="card">
                <?php if (isset($_GET['order_id'])): ?>
                    <a href="mana_orders.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Back to all orders</a>
                    <h2>Details for Order #<?php echo intval($_GET['order_id']); ?></h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Topping/Card</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($order_items as $item): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($item['product_name']); ?></strong></td>
                                <td><?php echo $item['service_name'] ? htmlspecialchars($item['service_name']) : '<span style="color:#ccc;">None</span>'; ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><?php echo number_format($item['price']); ?> VND</td>
                                <td><?php echo number_format($item['price'] * $item['quantity']); ?> VND</td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <h2>All Customer Orders</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Customer</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['full_name']); ?></strong><br>
                                    <small style="color:#888;"><?php echo htmlspecialchars($row['phone']); ?></small>
                                </td>
                                <td><strong><?php echo number_format($row['total_amount']); ?> VND</strong></td>
                                <td>
                                    <form method="post" action="" style="margin:0;">
                                        <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                        <select name="status" class="status-select" onchange="this.form.submit()" 
                                            <?php if($row['status']=='Completed' || $row['status']=='Cancelled') echo 'disabled'; ?>>
                                            <option value="Pending" <?php if($row['status']=='Pending') echo 'selected'; ?>>Pending</option>
                                            <option value="Paid" <?php if($row['status']=='Paid') echo 'selected'; ?>>Paid</option>
                                            <option value="Processing" <?php if($row['status']=='Processing') echo 'selected'; ?>>Processing</option>
                                            <option value="Ready for Delivery" <?php if($row['status']=='Ready for Delivery') echo 'selected'; ?>>Ready for Delivery</option>
                                            <option value="Delivering" <?php if($row['status']=='Delivering') echo 'selected'; ?>>Delivering</option>
                                            <option value="Completed" <?php if($row['status']=='Completed') echo 'selected'; ?>>Completed</option>
                                            <option value="Cancelled" <?php if($row['status']=='Cancelled') echo 'selected'; ?>>Cancelled</option>
                                        </select>
                                    </form>
                                </td>
                                <td><small><?php echo date('d/m/Y H:i', strtotime($row['order_date'])); ?></small></td>
                                <td>
                                    <a href="mana_orders.php?order_id=<?php echo $row['id']; ?>" class="view-btn"><i class="fa-solid fa-eye"></i> View</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>