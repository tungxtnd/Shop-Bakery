<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') { header("Location: ../auth/login.php"); exit; }
include '../../connectdb.php';

// Nhận tham số Search và Filter
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

// Xây dựng câu truy vấn động
$where_clauses = ["o.status IN ('Pending', 'Paid', 'Processing', 'Ready for Delivery')"];
if ($search) {
    $search_esc = $conn->real_escape_string($search);
    $where_clauses[] = "(o.id LIKE '%$search_esc%' OR u.full_name LIKE '%$search_esc%')";
}
if ($status_filter) {
    $status_esc = $conn->real_escape_string($status_filter);
    $where_clauses[] = "o.status = '$status_esc'";
}
$where_sql = implode(' AND ', $where_clauses);

$sql = "SELECT o.*, u.full_name as customer_name FROM orders o LEFT JOIN users u ON o.user_id = u.id WHERE $where_sql ORDER BY o.order_date ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="30">
    <title>Baker Dashboard | Bakes</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; margin: 0; display: flex; height: 100vh; overflow: hidden; }
        
        /* --- SIDEBAR CHUẨN ADMIN --- */
        .sidebar { width: 250px; background: #343a40; color: #fff; display: flex; flex-direction: column; }
        .sidebar-brand { padding: 20px; font-size: 1.5em; font-weight: bold; text-align: center; border-bottom: 1px solid #4f5962; color: #fff; background: #343a40; letter-spacing: 1px; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .sidebar-brand img { width: 38px; height: 38px; object-fit: contain; display: block; }
        .sidebar-brand span { color: #b97a56; }
        .nav-menu { list-style: none; padding: 0; margin: 0; flex: 1; overflow-y: auto; margin-top: 10px; }
        .nav-menu li a { display: block; padding: 15px 20px; color: #c2c7d0; text-decoration: none; transition: 0.3s; }
        .nav-menu li a i { margin-right: 12px; width: 20px; text-align: center; }
        .nav-menu li a:hover, .nav-menu li a.active { background: #494e53; color: #fff; border-left: 4px solid #b97a56; }
        
        /* --- KHU VỰC NỘI DUNG CHÍNH --- */
        .main-wrapper { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        
        /* THANH ĐIỀU HƯỚNG TRÊN CÙNG */
        .top-header { background: #fff; padding: 15px 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; z-index: 10; }
        .top-header .welcome { font-weight: bold; color: #555; }
        .top-header .btn-logout { color: #dc3545; text-decoration: none; font-weight: bold; }
        
        /* VÙNG CHỨA BẢNG DATA */
        .content { padding: 25px; overflow-y: auto; flex: 1; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 25px; margin-bottom: 25px; border-top: 4px solid #b97a56; }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .card-header h2 { margin: 0; color: #333; font-size: 1.4em; }
        
        /* BẢNG ĐƠN HÀNG */
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px 15px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #f9f9f9; color: #555; font-weight: bold; }
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 0.85em; font-weight: bold; }
        .status-pending { background: #ffeeba; color: #28a745; }
        .status-processing { background: #b8daff; color: #004085; }
        .btn-view { background: #b97a56; color: #fff; padding: 6px 12px; border-radius: 5px; text-decoration: none; font-size: 0.9em; transition: 0.2s; }
        .btn-view:hover { background: #9c6343; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand">
            <img src="/assets/img/logo.png" alt="Bakes Logo">
            Bakes <span>Staff</span>
        </div>
        <ul class="nav-menu">
            <li><a href="dashboard.php" class="active"><i class="fa-solid fa-bell-concierge"></i> Live Orders</a></li>
            <li><a href="inventory.php"><i class="fa-solid fa-boxes-stacked"></i> Update Inventory</a></li>
            <li><a href="schedule.php"><i class="fa-solid fa-calendar-days"></i> My Schedule</a></li>
            <li><a href="profile.php"><i class="fa-solid fa-user-gear"></i> My Profile</a></li>
            
        </ul>
    </div>

    <div class="main-wrapper">
        <div class="top-header">
            <div class="welcome">👨‍🍳 Kitchen Dashboard</div>
            <a href="../auth/logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
        
        <div class="content">
            <div class="card">
                <div class="card-header">
                    <h2>Orders to Prepare</h2>
                    <span style="color: #888; font-size: 0.9em;">(Auto-refreshes every 30s)</span>
                </div>

                <form method="GET" style="display: flex; gap: 15px; margin-bottom: 20px;">
                    <input type="text" name="search" placeholder="Search by Order ID or Name..." value="<?php echo htmlspecialchars($search); ?>" style="padding: 10px; width: 300px; border: 1px solid #ddd; border-radius: 5px;">
                    <select name="status" style="padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                        <option value="">-- All Active Status --</option>
                        <option value="Pending" <?php if($status_filter=='Pending') echo 'selected';?>>Pending</option>
                        <option value="Processing" <?php if($status_filter=='Processing') echo 'selected';?>>Processing (Baking)</option>
                    </select>
                    <button type="submit" style="background: #b97a56; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;"><i class="fa-solid fa-filter"></i> Filter</button>
                    <a href="dashboard.php" style="padding: 10px 20px; background: #eee; color: #333; text-decoration: none; border-radius: 5px; display: flex; align-items: center;">Clear</a>
                </form>

                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date & Time</th>
                            <th>Customer Name</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?php echo $row['id']; ?></strong></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($row['order_date'])); ?></td>
                                <td><?php echo htmlspecialchars($row['customer_name'] ?? 'Guest'); ?></td>
                                <td>
                                    <span class="status-badge <?php echo ($row['status'] == 'Processing') ? 'status-processing' : 'status-pending'; ?>">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>
                                <td><a href="order_details.php?id=<?php echo $row['id']; ?>" class="btn-view">View Details</a></td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" style="text-align: center; color: #888; padding: 30px;">No active orders matching your filter. Kitchen is clear! ✨</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>