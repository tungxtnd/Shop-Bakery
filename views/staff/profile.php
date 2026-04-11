<?php
session_start();
// Kiểm tra quyền truy cập của Staff
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') { 
    header("Location: ../auth/login.php"); 
    exit; 
}
include '../../connectdb.php';

$user_id = $_SESSION['user_id'];
$message = '';

// Xử lý cập nhật thông tin cá nhân
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);

    if (!empty($password)) {
        // Cập nhật bao gồm mật khẩu mới
        $stmt = $conn->prepare("UPDATE users SET full_name=?, phone=?, password=? WHERE id=?");
        $stmt->bind_param("sssi", $full_name, $phone, $password, $user_id);
    } else {
        // Chỉ cập nhật tên và số điện thoại
        $stmt = $conn->prepare("UPDATE users SET full_name=?, phone=? WHERE id=?");
        $stmt->bind_param("ssi", $full_name, $phone, $user_id);
    }
    
    if ($stmt->execute()) {
        $message = "Profile updated successfully!";
        $_SESSION['full_name'] = $full_name; // Cập nhật lại tên hiển thị trong session
    }
}

// Lấy thông tin hiện tại của người dùng
$user = $conn->query("SELECT * FROM users WHERE id = $user_id")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile | Bakes Staff</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; margin: 0; display: flex; height: 100vh; overflow: hidden; }
        
        /* SIDEBAR DỌC ĐỒNG BỘ */
        .sidebar { width: 250px; background: #343a40; color: #fff; display: flex; flex-direction: column; flex-shrink: 0; }
        .sidebar-brand { padding: 20px; font-size: 1.5em; font-weight: bold; text-align: center; border-bottom: 1px solid #4f5962; color: #fff; background: #343a40; letter-spacing: 1px; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .sidebar-brand img { width: 38px; height: 38px; object-fit: contain; display: block; }
        .sidebar-brand span { color: #b97a56; }
        .nav-menu { list-style: none; padding: 0; margin: 0; flex: 1; overflow-y: auto; margin-top: 10px; }
        .nav-menu li a { display: block; padding: 15px 20px; color: #c2c7d0; text-decoration: none; transition: 0.3s; }
        .nav-menu li a i { margin-right: 12px; width: 20px; text-align: center; }
        .nav-menu li a:hover, .nav-menu li a.active { background: #494e53; color: #fff; border-left: 4px solid #b97a56; }
        
        /* MAIN WRAPPER */
        .main-wrapper { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .top-header { background: #fff; padding: 15px 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; z-index: 10; }
        .top-header .welcome { font-weight: bold; color: #555; }
        
        /* CONTENT AREA */
        .content { padding: 25px; overflow-y: auto; flex: 1; display: flex; justify-content: center; align-items: flex-start; }
        .profile-card { background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 30px; width: 100%; max-width: 600px; border-top: 4px solid #b97a56; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 8px; color: #333; font-size: 0.9em; }
        .form-group input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; font-size: 1em; transition: 0.2s; }
        .form-group input:focus { border-color: #b97a56; outline: none; box-shadow: 0 0 5px rgba(185, 122, 86, 0.2); }
        
        .btn-save { background: #b97a56; color: white; border: none; padding: 14px; border-radius: 5px; cursor: pointer; font-weight: bold; width: 100%; font-size: 1.1em; transition: 0.3s; margin-top: 10px; }
        .btn-save:hover { background: #9c6343; }
        .success-msg { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center; font-weight: bold; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand">
            <img src="/assets/img/logo.png" alt="Bakes Logo">
            Bakes <span>Staff</span>
        </div>
        <ul class="nav-menu">
            <li><a href="dashboard.php"><i class="fa-solid fa-bell-concierge"></i> Live Orders</a></li>
            <li><a href="inventory.php"><i class="fa-solid fa-boxes-stacked"></i> Update Inventory</a></li>
            <li><a href="schedule.php"><i class="fa-solid fa-calendar-days"></i> My Schedule</a></li>   
            <li><a href="profile.php" class="active"><i class="fa-solid fa-user-gear"></i> My Profile</a></li>
        </ul>
    </div>

    <div class="main-wrapper">
        <div class="top-header">
            <div class="welcome">👨‍🍳 Account Settings</div>
            <a href="../auth/logout.php" style="color: #dc3545; text-decoration: none; font-weight: bold;">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </div>
        
        <div class="content">
            <div class="profile-card">
                <h2 style="color: #333; margin-top: 0; margin-bottom: 25px; text-align: center;">Personal Information</h2>
                
                <?php if($message): ?>
                    <div class="success-msg"><i class="fa-solid fa-circle-check"></i> <?php echo $message; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label><i class="fa-solid fa-user"></i> Full Name</label>
                        <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fa-solid fa-envelope"></i> Email Address (Read-only)</label>
                        <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="background: #f8f9fa; color: #6c757d; cursor: not-allowed;">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fa-solid fa-phone"></i> Phone Number</label>
                        <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fa-solid fa-lock"></i> New Password</label>
                        <input type="password" name="password" placeholder="Leave blank to keep current password">
                    </div>
                    
                    <button type="submit" class="btn-save">
                        <i class="fa-solid fa-floppy-disk"></i> Save Changes
                    </button>
                    
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="dashboard.php" style="color: #888; text-decoration: none; font-size: 0.9em;">
                            <i class="fa-solid fa-arrow-left"></i> Cancel and go back
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>