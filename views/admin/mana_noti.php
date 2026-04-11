<?php
session_start();
include '../../connectdb.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

// Xử lý xóa thông báo
if (isset($_POST['delete_id'])) {
    $del_id = intval($_POST['delete_id']);
    $conn->query("DELETE FROM notifications WHERE id = $del_id");
    header("Location: mana_noti.php");
    exit;
}

// Truy xuất tất cả thông báo với đầy đủ thông tin liên kết
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Notifications | Bakes Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; margin: 0; display: flex; height: 100vh; overflow: hidden; }
        
        /* SIDEBAR DỌC ĐỒNG BỘ */
        .sidebar { width: 260px; background: #343a40; color: #fff; display: flex; flex-direction: column; flex-shrink: 0; }
        .sidebar-brand { padding: 25px 20px; font-size: 1.6em; font-weight: bold; text-align: center; border-bottom: 1px solid #4f5962; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .sidebar-brand img { width: 38px; height: 38px; object-fit: contain; display: block; }
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
        
        /* BẢNG THÔNG BÁO */
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 0.9em; }
        th, td { padding: 12px 10px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f9f9f9; color: #555; font-weight: bold; text-transform: uppercase; font-size: 0.85em; }
        
        /* MÀU SẮC THEO LOẠI THÔNG BÁO */
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: bold; }
        .noti-order { background: #e7f3ff; color: #007bff; }
        .noti-admin { background: #fff4e5; color: #b97a56; }
        .noti-review { background: #eafbe7; color: #28a745; }
        .noti-auth { background: #f2f2f2; color: #888; }

        .noti-date { font-size: 0.85em; color: #888; display: block; margin-top: 4px; }
        .btn-delete { background: #dc3545; color: #fff; border: none; border-radius: 4px; padding: 6px 10px; cursor: pointer; transition: 0.2s; }
        .btn-delete:hover { background: #c82333; }
        .empty { text-align: center; color: #aaa; padding: 40px 0; font-style: italic; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand">
            <img src="/assets/img/logo.png" alt="Bakes Logo">
            Bakes <span>Admin</span>
        </div>
        <ul class="nav-menu">
            <li><a href="dashboard.php"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
            <li><a href="mana_orders.php"><i class="fa-solid fa-cart-shopping"></i> Manage Orders</a></li>
            <li><a href="mana_products.php"><i class="fa-solid fa-cookie-bite"></i> Manage Products</a></li>
            <li><a href="mana_reviews.php"><i class="fa-solid fa-star"></i> Manage Reviews</a></li>
            <li><a href="mana_users.php"><i class="fa-solid fa-users"></i> Manage Users</a></li>
            <li><a href="mana_noti.php" class="active"><i class="fa-solid fa-bell"></i> Notifications</a></li>
            <li style="margin-top: 20px;"><a href="/views/auth/logout.php" onclick="return confirm('Logout?');" style="color: #ff7675;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-wrapper">
        <div class="top-header">
            <div style="font-weight: bold; color: #555;">System Logs & Notifications</div>
            <div style="color: #888;">Logged in as <strong>Admin</strong></div>
        </div>

        <div class="content">
            <div class="card">
                <h2>Manage Notifications</h2>
                <?php if (empty($notifications)): ?>
                    <div class="empty">No notifications found in the system.</div>
                <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th style="width: 120px;">Category</th>
                            <th>Involved Parties</th>
                            <th>Reference</th>
                            <th>Message</th>
                            <th style="width: 80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($notifications as $noti): ?>
                        <tr>
                            <td>#<?php echo $noti['id']; ?></td>
                            <td>
                                <?php 
                                    $class = 'noti-auth';
                                    if(strpos($noti['type'], 'order') !== false) $class = 'noti-order';
                                    if(strpos($noti['type'], 'admin') !== false) $class = 'noti-admin';
                                    if(strpos($noti['type'], 'review') !== false) $class = 'noti-review';
                                ?>
                                <span class="badge <?php echo $class; ?>">
                                    <?php echo strtoupper(str_replace('_', ' ', $noti['type'])); ?>
                                </span>
                            </td>
                            <td>
                                <div style="font-weight: bold;"><?php echo $noti['user_name'] ? htmlspecialchars($noti['user_name']) : '-'; ?></div>
                                <div style="color: #666; font-size: 0.9em;">To: <?php echo $noti['target_user_name'] ? htmlspecialchars($noti['target_user_name']) : 'System'; ?></div>
                            </td>
                            <td>
                                <?php if($noti['product_name']): ?>
                                    <div style="font-size: 0.9em;"><i class="fa-solid fa-cookie"></i> <?php echo htmlspecialchars($noti['product_name']); ?></div>
                                <?php endif; ?>
                                <?php if($noti['order_number']): ?>
                                    <div style="font-size: 0.9em; font-weight: bold;"><i class="fa-solid fa-receipt"></i> Order #<?php echo $noti['order_number']; ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($noti['message']); ?>
                                <span class="noti-date"><?php echo date('d/m/Y H:i', strtotime($noti['created_at'])); ?></span>
                            </td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="delete_id" value="<?php echo $noti['id']; ?>">
                                    <button type="submit" class="btn-delete" onclick="return confirm('Delete this notification?');" title="Delete">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>