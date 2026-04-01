<?php


session_start();
include '../../connectdb.php';


// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../homepage.php");
    exit;
}


// Fetch all notifications
$sql = "SELECT n.*, u.full_name AS user_name, tu.full_name AS target_user_name, p.name AS product_name, o.id AS order_number
        FROM notifications n
        LEFT JOIN users u ON n.user_id = u.id
        LEFT JOIN users tu ON n.target_user_id = tu.id
        LEFT JOIN products p ON n.product_id = p.id
        LEFT JOIN orders o ON n.order_id = o.id
        ORDER BY n.created_at DESC";
$result = $conn->query($sql);
$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}


// Handle delete
if (isset($_POST['delete_id'])) {
    $del_id = intval($_POST['delete_id']);
    $conn->query("DELETE FROM notifications WHERE id = $del_id");
    header("Location: mana_noti.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Notifications</title>
    <style>
        body { background: #f8f8f8; font-family: 'Segoe UI', Arial, sans-serif; margin: 0; padding: 0; }
        .container { max-width: 900px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 12px #eee; padding: 32px; }
        h2 { color: #7A5230; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        th, td { padding: 10px 8px; border-bottom: 1px solid #f0f0f0; text-align: left; }
        th { background: #f8f8f8; color: #7A5230; }
        .noti-type-order_status { color: #1e90ff; }
        .noti-type-admin_message { color: #7A5230; }
        .noti-type-review { color: #2ecc40; }
        .noti-type-login, .noti-type-logout { color: #888; }
        .noti-date { font-size: 0.98em; color: #888; }
        .empty { text-align: center; color: #aaa; padding: 40px 0; }
        .delete-btn {
            background: #f44336; color: #fff; border: none; border-radius: 4px; padding: 5px 12px; cursor: pointer;
        }
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
        .delete-btn:hover { background: #b94a48; }
    </style>
</head>
<body>
    <nav class="admin-navbar">
        <a href="dashboard.php">Dashboard</a>
        <a href="mana_orders.php">Manage Orders</a>
        <a href="mana_products.php">Manage Products</a>
        <a href="mana_reviews.php">Manage Reviews</a>
        <a href="mana_users.php">Manage Users</a>
        <a href="mana_noti.php" class="active">Manage Notifications</a>
        <a href="/views/auth/logout.php" style="margin-left:auto;" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
    </nav>
    <div class="container">
        <h2>Manage Notifications</h2>
        <?php if (empty($notifications)): ?>
            <div class="empty">No notifications found.</div>
        <?php else: ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Type</th>
                <th>User</th>
                <th>Target User</th>
                <th>Product</th>
                <th>Order</th>
                <th>Message</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
            <?php foreach ($notifications as $noti): ?>
            <tr>
                <td><?php echo $noti['id']; ?></td>
                <td class="noti-type-<?php echo htmlspecialchars($noti['type']); ?>">
                    <?php echo ucfirst(str_replace('_', ' ', $noti['type'])); ?>
                </td>
                <td><?php echo $noti['user_name'] ? htmlspecialchars($noti['user_name']) : '-'; ?></td>
                <td><?php echo $noti['target_user_name'] ? htmlspecialchars($noti['target_user_name']) : '-'; ?></td>
                <td><?php echo $noti['product_name'] ? htmlspecialchars($noti['product_name']) : '-'; ?></td>
                <td><?php echo $noti['order_number'] ? 'Order #' . htmlspecialchars($noti['order_number']) : '-'; ?></td>
                <td><?php echo htmlspecialchars($noti['message']); ?></td>
                <td class="noti-date"><?php echo date('d/m/Y H:i', strtotime($noti['created_at'])); ?></td>
                <td>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="delete_id" value="<?php echo $noti['id']; ?>">
                        <button type="submit" class="delete-btn" onclick="return confirm('Delete this notification?');">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
