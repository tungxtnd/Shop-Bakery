<?php
session_start();
include '../../connectdb.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}

// Xử lý xóa (Chỉ xóa người dùng không phải Admin)
if (isset($_POST['delete_id'])) {
    $del_id = intval($_POST['delete_id']);
    // Kiểm tra role trước khi xóa
    $check = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $check->bind_param("i", $del_id);
    $check->execute();
    $check->bind_result($role);
    $check->fetch();
    $check->close();
    
    if ($role !== 'admin') {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $del_id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: mana_users.php");
    exit;
}

// Xử lý chỉnh sửa thông tin (Chỉ dành cho người dùng không phải Admin)
$edit_success = false;
$edit_errors = [];
if (isset($_POST['edit_id']) && isset($_POST['full_name']) && isset($_POST['email'])) {
    $edit_id = intval($_POST['edit_id']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    $check = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $check->bind_param("i", $edit_id);
    $check->execute();
    $check->bind_result($role);
    $check->fetch();
    $check->close();
    
    if ($role !== 'admin') {
        $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, phone=?, address=? WHERE id=?");
        $stmt->bind_param("ssssi", $full_name, $email, $phone, $address, $edit_id);
        if ($stmt->execute()) {
            $edit_success = true;
        } else {
            $edit_errors[] = "Update failed.";
        }
        $stmt->close();
    } else {
        $edit_errors[] = "Cannot edit admin user.";
    }
}

// Truy xuất tất cả người dùng
$result = $conn->query("SELECT id, full_name, email, phone, address, role FROM users ORDER BY role ASC, id ASC");
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users | Bakes Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; margin: 0; display: flex; height: 100vh; overflow: hidden; }
        
        /* SIDEBAR DỌC */
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
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f9f9f9; color: #555; font-weight: bold; font-size: 0.9em; text-transform: uppercase; }
        
        /* ROLE BADGES */
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 0.8em; font-weight: bold; display: inline-block; }
        .badge-admin { background: #f8d7da; color: #721c24; }
        .badge-staff { background: #d1ecf1; color: #0c5460; }
        .badge-customer { background: #e2e3e5; color: #383d41; }

        /* ACTIONS */
        .btn-action { padding: 6px 10px; border-radius: 4px; text-decoration: none; font-size: 0.85em; color: #fff; border: none; cursor: pointer; margin-right: 5px; }
        .btn-edit { background: #007bff; }
        .btn-delete { background: #dc3545; }
        .btn-save { background: #28a745; }
        
        .edit-form input { padding: 6px; border: 1px solid #ddd; border-radius: 4px; width: 100%; box-sizing: border-box; }
        .success-msg { color: #155724; background: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 15px; }
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
            <li><a href="mana_users.php" class="active"><i class="fa-solid fa-users"></i> Manage Users</a></li>
            <li><a href="mana_noti.php"><i class="fa-solid fa-bell"></i> Notifications</a></li>
            <li style="margin-top: 20px;"><a href="/views/auth/logout.php" onclick="return confirm('Logout?');" style="color: #ff7675;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-wrapper">
        <div class="top-header">
            <div style="font-weight: bold; color: #555;">User Account Management</div>
            <div style="color: #888;">Logged in as <strong>Admin</strong></div>
        </div>

        <div class="content">
            <div class="card">
                <h2>System Users</h2>
                <?php if ($edit_success): ?><div class="success-msg">User updated successfully!</div><?php endif; ?>
                
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td>#<?php echo $user['id']; ?></td>
                            <td>
                                <?php if (isset($_GET['edit']) && $_GET['edit'] == $user['id'] && $user['role'] !== 'admin'): ?>
                                    <form method="post" id="form-<?php echo $user['id']; ?>" class="edit-form">
                                        <input type="hidden" name="edit_id" value="<?php echo $user['id']; ?>">
                                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                <?php else: ?>
                                    <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (isset($_GET['edit']) && $_GET['edit'] == $user['id'] && $user['role'] !== 'admin'): ?>
                                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($user['email']); ?>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo strtolower($user['role']); ?>">
                                    <?php echo strtoupper($user['role']); ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user['role'] !== 'admin'): ?>
                                    <?php if (isset($_GET['edit']) && $_GET['edit'] == $user['id']): ?>
                                        <button type="submit" class="btn-action btn-save"><i class="fa-solid fa-check"></i></button>
                                        </form>
                                        <a href="mana_users.php" class="btn-action" style="background:#6c757d;"><i class="fa-solid fa-xmark"></i></a>
                                    <?php else: ?>
                                        <a href="mana_users.php?edit=<?php echo $user['id']; ?>" class="btn-action btn-edit"><i class="fa-solid fa-user-pen"></i> Edit</a>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="delete_id" value="<?php echo $user['id']; ?>">
                                            <button type="submit" class="btn-action btn-delete" onclick="return confirm('Delete this user?');"><i class="fa-solid fa-user-minus"></i></button>
                                        </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <small style="color:#ccc;">Protected</small>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>