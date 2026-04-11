<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') { header("Location: ../auth/login.php"); exit; }
include '../../connectdb.php';
$user_id = $_SESSION['user_id'];
$msg = '';

// Xử lý đăng ký ca làm
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['work_date'], $_POST['shift_type'])) {
    $date = $_POST['work_date'];
    $shift = $_POST['shift_type'];
    
    // Kiểm tra trùng ca
    $check = $conn->query("SELECT id FROM work_schedules WHERE user_id = $user_id AND work_date = '$date' AND shift_type = '$shift'");
    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO work_schedules (user_id, work_date, shift_type) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $user_id, $date, $shift);
        if($stmt->execute()) $msg = "Shift registered successfully!";
    } else {
        $msg = "You are already registered for this shift!";
    }
}

// Logic mới: Xử lý dữ liệu dạng Ma trận (Matrix) cho 7 ngày
$schedules = $conn->query("SELECT * FROM work_schedules WHERE user_id = $user_id AND work_date >= CURDATE() AND work_date < DATE_ADD(CURDATE(), INTERVAL 7 DAY)");

// 1. Lưu các ca làm đã đăng ký vào một mảng 2 chiều: $my_shifts[Ngày][Ca] = Trạng thái
$my_shifts = [];
while ($row = $schedules->fetch_assoc()) {
    $my_shifts[$row['work_date']][$row['shift_type']] = $row['status'];
}

// 2. Tạo mảng 7 ngày tiếp theo (bắt đầu từ hôm nay)
$next_7_days = [];
for ($i = 0; $i < 7; $i++) {
    $next_7_days[] = date('Y-m-d', strtotime("+$i days"));
}

// 3. Mảng các ca làm chuẩn
$shifts = [
    'Morning (08:00-12:00)' => 'Morning',
    'Afternoon (12:00-17:00)' => 'Afternoon',
    'Evening (17:00-22:00)' => 'Evening'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Work Schedule | Bakes Staff</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; margin: 0; display: flex; height: 100vh; overflow: hidden; }
        .sidebar { width: 250px; background: #343a40; color: #fff; display: flex; flex-direction: column; }
        .sidebar-brand { padding: 20px; font-size: 1.5em; font-weight: bold; text-align: center; border-bottom: 1px solid #4f5962; color: #fff; background: #343a40; letter-spacing: 1px; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .sidebar-brand img { width: 38px; height: 38px; object-fit: contain; display: block; }
        .sidebar-brand span { color: #b97a56; }
        .nav-menu { list-style: none; padding: 0; margin: 0; flex: 1; overflow-y: auto; margin-top: 10px; }
        .nav-menu li a { display: block; padding: 15px 20px; color: #c2c7d0; text-decoration: none; transition: 0.3s; }
        .nav-menu li a:hover, .nav-menu li a.active { background: #494e53; color: #fff; border-left: 4px solid #b97a56; }
        .main-wrapper { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .top-header { background: #fff; padding: 15px 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; }
        .content { padding: 25px; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 20px; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 25px; border-top: 4px solid #b97a56;}
        
        /* CSS cho bảng Lịch dạng Calendar */
        .calendar-table { width: 100%; border-collapse: collapse; margin-top: 15px; text-align: center; }
        .calendar-table th, .calendar-table td { border: 1px solid #e0e0e0; padding: 15px 10px; }
        .calendar-table th { background: #f9f9f9; color: #555; font-weight: bold; }
        .calendar-table td { vertical-align: top; height: 60px; }
        .shift-name { font-weight: bold; color: #b97a56; background: #fff9f6; }
        
        /* Badge trạng thái */
        .badge { display: inline-block; padding: 6px 10px; border-radius: 6px; font-size: 0.85em; font-weight: bold; width: 100%; box-sizing: border-box; }
        .badge-registered { background: #fff3cd; color: #856404; border: 1px solid #ffeeba; }
        .badge-approved { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        
        /* Form */
        .form-row { display: flex; gap: 15px; align-items: flex-end; }
        .form-group { flex: 1; }
        input[type="date"], select { width: 100%; padding: 10px; margin-top: 5px; border: 1px solid #ddd; border-radius: 5px; box-sizing: border-box; }
        .btn-submit { background: #b97a56; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; height: 40px; }
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
            <li><a href="schedule.php" class="active"><i class="fa-solid fa-calendar-days"></i> My Schedule</a></li>
            <li><a href="profile.php"><i class="fa-solid fa-user-gear"></i> My Profile</a></li>
        </ul>
    </div>
    
    <div class="main-wrapper">
        <div class="top-header">
            <div style="font-weight: bold; color: #555;">👨‍🍳 Weekly Roster</div>
            <a href="../auth/logout.php" style="color: #dc3545; text-decoration: none; font-weight: bold;">Logout</a>
        </div>
        <div class="content">
            
            <div class="card">
                <h2 style="margin-top: 0;">Register New Shift</h2>
                <?php if($msg): ?><p style="color: green; font-weight: bold; margin: 5px 0;"><i class="fa-solid fa-circle-check"></i> <?php echo $msg; ?></p><?php endif; ?>
                <form method="POST" class="form-row">
                    <div class="form-group">
                        <label>Select Date:</label>
                        <input type="date" name="work_date" min="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Select Shift:</label>
                        <select name="shift_type" required>
                            <option value="Morning (08:00-12:00)">Morning (08:00-12:00)</option>
                            <option value="Afternoon (12:00-17:00)">Afternoon (12:00-17:00)</option>
                            <option value="Evening (17:00-22:00)">Evening (17:00-22:00)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn-submit"><i class="fa-solid fa-plus"></i> Register</button>
                </form>
            </div>
            
            <div class="card">
                <h2 style="margin-top: 0;">My 7-Day Calendar View</h2>
                <table class="calendar-table">
                    <thead>
                        <tr>
                            <th style="width: 12%;">Shift \ Date</th>
                            <?php foreach ($next_7_days as $d): ?>
                                <th>
                                    <?php echo date('l', strtotime($d)); ?><br>
                                    <small style="color: #888; font-weight: normal;"><?php echo date('d/m/Y', strtotime($d)); ?></small>
                                </th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($shifts as $full_shift_name => $short_name): ?>
                        <tr>
                            <td class="shift-name"><?php echo $short_name; ?></td>
                            
                            <?php foreach ($next_7_days as $d): ?>
                                <td>
                                    <?php if (isset($my_shifts[$d][$full_shift_name])): ?>
                                        <?php $status = $my_shifts[$d][$full_shift_name]; ?>
                                        <div class="badge <?php echo $status == 'Approved' ? 'badge-approved' : 'badge-registered'; ?>">
                                            <?php echo $status; ?>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #ddd;">-</span>
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                            
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>
</body>
</html>