<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') { header("Location: ../auth/login.php"); exit; }
include '../../connectdb.php';

$order_id = $_GET['id'] ?? 0;

// Xử lý cập nhật trạng thái đơn hàng & Tự động HOÀN KHO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_status'])) {
    $new_status = $_POST['new_status'];
    
    // Lấy trạng thái cũ của đơn hàng để so sánh
    $status_check = $conn->query("SELECT status FROM orders WHERE id = $order_id")->fetch_assoc();
    $old_status = $status_check['status'] ?? '';
    
    // 1. Cập nhật trạng thái mới
    $update_stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $update_stmt->bind_param("si", $new_status, $order_id);
    $update_stmt->execute();

    // 2. Tự động CỘNG LẠI Tồn kho (Restore Stock) nếu đơn hàng bị Hủy (Cancelled)
    // Điều kiện $old_status !== 'Cancelled' giúp chặn lỗi lỡ tay bấm Hủy 2 lần bị cộng dồn kho
    if ($new_status === 'Cancelled' && $old_status !== 'Cancelled') {
        $items = $conn->query("SELECT product_id, quantity FROM order_items WHERE order_id = $order_id");
        while ($item = $items->fetch_assoc()) {
            $p_id = $item['product_id'];
            $qty = $item['quantity'];
            $conn->query("UPDATE products SET stock = stock + $qty WHERE id = $p_id");
        }
    }
    
    header("Location: dashboard.php");
    exit;
}

// Lấy thông tin chi tiết các món trong đơn
$sql_items = "
    SELECT oi.*, p.name as product_name, s.name as service_name 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    LEFT JOIN services s ON oi.service_id = s.id
    WHERE oi.order_id = $order_id
";
$items = $conn->query($sql_items);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Details | Bakes Staff</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; margin: 0; display: flex; height: 100vh; overflow: hidden; }
        .sidebar { width: 250px; background: #343a40; color: #fff; display: flex; flex-direction: column; }
        .sidebar-brand { padding: 20px; font-size: 1.5em; font-weight: bold; text-align: center; border-bottom: 1px solid #4f5962; color: #fff; background: #343a40; letter-spacing: 1px; display: flex; align-items: center; justify-content: center; gap: 10px; }
        .sidebar-brand img { width: 38px; height: 38px; object-fit: contain; display: block; }
        .sidebar-brand span { color: #b97a56; }
        .nav-menu { list-style: none; padding: 0; margin: 0; flex: 1; overflow-y: auto; margin-top: 10px; }
        .nav-menu li a { display: block; padding: 15px 20px; color: #c2c7d0; text-decoration: none; transition: 0.3s; }
        .nav-menu li a i { margin-right: 12px; width: 20px; text-align: center; }
        .nav-menu li a:hover, .nav-menu li a.active { background: #494e53; color: #fff; border-left: 4px solid #b97a56; }
        
        .main-wrapper { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .top-header { background: #fff; padding: 15px 25px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; z-index: 10; }
        .content { padding: 25px; overflow-y: auto; flex: 1; }
        
        .card { background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 30px; border-top: 4px solid #b97a56; }
        .card-header { margin-bottom: 20px; border-bottom: 2px solid #f0e4df; padding-bottom: 15px; }
        .item-row { display: flex; justify-content: space-between; border-bottom: 1px dashed #ddd; padding: 15px 0; }
        .item-name { font-size: 1.15em; font-weight: bold; color: #333; margin-bottom: 5px;}
        .item-notes { font-size: 0.95em; color: #e74c3c; margin-top: 8px; background: #fdf2f2; padding: 6px 12px; border-radius: 5px; display: inline-block; border: 1px solid #f8d7da;}
        
        .status-box { background: #f9f9f9; padding: 25px; border-radius: 8px; border: 1px solid #eee; margin-top: 30px; }
        .status-form select { padding: 10px; border-radius: 5px; border: 1px solid #ccc; font-size: 1em; width: 300px; margin-right: 10px;}
        .btn-update { background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; font-size: 1em; transition: 0.2s;}
        .btn-update:hover { background: #218838; }
        .btn-back { display: inline-block; background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 25px;}
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
            <li><a href="completed.php"><i class="fa-solid fa-check-double"></i> Completed Orders</a></li>
            <li><a href="recipes.php"><i class="fa-solid fa-book-open"></i> Recipes & Menu</a></li>
            <li><a href="profile.php"><i class="fa-solid fa-user-gear"></i> My Profile</a></li>
        </ul>
    </div>

    <div class="main-wrapper">
        <div class="top-header">
            <div style="font-weight: bold; color: #555;">👨‍🍳 Order Processing</div>
            <a href="../auth/logout.php" style="color: #dc3545; text-decoration: none; font-weight: bold;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
        
        <div class="content">
            <div class="card">
                <div class="card-header">
                    <h2 style="margin: 0; color: #333; font-size: 1.6em;">Order Request #<?php echo $order_id; ?></h2>
                </div>
                
                <h3 style="color: #b97a56; margin-top: 0;">Items to Prepare:</h3>
                
                <?php if ($items && $items->num_rows > 0): ?>
                    <?php while($item = $items->fetch_assoc()): ?>
                        <div class="item-row">
                            <div>
                                <div class="item-name"><?php echo $item['quantity']; ?>x <?php echo htmlspecialchars($item['product_name']); ?></div>
                                <?php if (!empty($item['service_name'])): ?>
                                    <div class="item-notes"><i class="fa-solid fa-plus"></i> Topping/Extra: <strong><?php echo htmlspecialchars($item['service_name']); ?></strong></div>
                                <?php endif; ?>
                                <?php if (!empty($item['card_message'])): ?>
                                    <div class="item-notes"><i class="fa-solid fa-pen-nib"></i> Note for Baker: <em>"<?php echo htmlspecialchars($item['card_message']); ?>"</em></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php endif; ?>

                <div class="status-box">
                    <form method="POST" class="status-form">
                        <label style="font-weight: bold; font-size: 1.1em; display: block; margin-bottom: 10px;">Change Order Status:</label>
                        <select name="new_status">
                            <option value="Processing">Processing (Baking)</option>
                            <option value="Ready for Delivery">Ready for Delivery (Done Baking)</option>
                            <option value="Delivering">Delivering (Out for delivery)</option>
                            <option value="Completed">Completed (Delivered)</option>
                            <option value="Cancelled" style="color: red; font-weight: bold;">❌ Cancelled (Hủy đơn & Hoàn kho)</option>
                        </select>
                        <button type="submit" class="btn-update"><i class="fa-solid fa-floppy-disk"></i> Update Status</button>
                    </form>
                </div>

                <a href="dashboard.php" class="btn-back"><i class="fa-solid fa-arrow-left"></i> Back to Live Orders</a>
            </div>
        </div>
    </div>
</body>
</html>