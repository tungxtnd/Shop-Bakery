<?php
// filepath: d:\Xampp\htdocs\flower_shop\views\customer\noti.php
session_start();
include '../../connectdb.php';


// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../views/auth/login.php");
    exit;
}
$user_id = $_SESSION['user_id'];


// Fetch notifications for this user (either sent to them or about their orders)
$sql = "SELECT n.*, p.name AS product_name, o.id AS order_number
        FROM notifications n
        LEFT JOIN products p ON n.product_id = p.id
        LEFT JOIN orders o ON n.order_id = o.id
        WHERE (n.target_user_id = ? OR n.user_id = ?)
          AND n.type NOT IN ('login', 'logout')
        ORDER BY n.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$notifications = [];
while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Notifications</title>
    <style>
        body { background: #f8f8f8; font-family: 'Segoe UI', Arial, sans-serif; }
        .container { max-width: 700px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 12px #eee; padding: 32px; }
        h2 { color: #e75480; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        th, td { padding: 12px 8px; border-bottom: 1px solid #f0f0f0; text-align: left; }
        th { background: #f8f8f8; color: #e75480; }
        .noti-type-order_status { color: #1e90ff; }
        .noti-type-admin_message { color: #e75480; }
        .noti-type-review { color: #2ecc40; }
        .noti-type-login, .noti-type-logout { color: #888; }
        .noti-date { font-size: 0.98em; color: #888; }
        .empty { text-align: center; color: #aaa; padding: 40px 0; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <div class="container">
        <h2>Notifications</h2>
        <?php if (empty($notifications)): ?>
            <div class="empty">You have no notifications.</div>
        <?php else: ?>
        <table>
            <tr>
                <th>Type</th>
                <th>Message</th>
                <th>Product</th>
                <th>Order</th>
                <th>Date</th>
            </tr>
            <?php foreach ($notifications as $noti): ?>
            <tr>
                <td class="noti-type-<?php echo htmlspecialchars($noti['type']); ?>">
                    <?php echo ucfirst(str_replace('_', ' ', $noti['type'])); ?>
                </td>
                <td><?php echo htmlspecialchars($noti['message']); ?></td>
                <td>
                    <?php echo $noti['product_name'] ? htmlspecialchars($noti['product_name']) : '-'; ?>
                </td>
                <td>
                    <?php echo $noti['order_number'] ? 'Order #' . htmlspecialchars($noti['order_number']) : '-'; ?>
                </td>
                <td class="noti-date"><?php echo date('d/m/Y H:i', strtotime($noti['created_at'])); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        <?php endif; ?>
    </div>
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
