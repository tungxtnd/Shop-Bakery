<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'staff') { 
    header("Location: ../auth/login.php"); 
    exit; 
}
include '../../connectdb.php';

$msg = '';

// Xử lý logic cập nhật số lượng hàng tồn kho
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'], $_POST['new_stock'])) {
    $p_id = intval($_POST['product_id']);
    $n_stock = intval($_POST['new_stock']);
    
    // Cập nhật vào cơ sở dữ liệu
    $stmt = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
    $stmt->bind_param("ii", $n_stock, $p_id);
    
    if($stmt->execute()) {
        $msg = "Cập nhật số lượng kho thành công!";
    } else {
        $msg = "Có lỗi xảy ra, vui lòng thử lại.";
    }
}

// Truy xuất danh sách sản phẩm (Bánh & Nước)
$search = $_GET['search'] ?? '';
$where_sql = "1=1";
if ($search) {
    $search_esc = $conn->real_escape_string($search);
    $where_sql .= " AND name LIKE '%$search_esc%'";
}

$products = $conn->query("SELECT id, name, image, stock FROM products WHERE $where_sql ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Update Inventory | Bakes Staff</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Đồng bộ CSS Giao diện chung */
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
        .top-header .welcome { font-weight: bold; color: #555; }
        .content { padding: 25px; overflow-y: auto; flex: 1; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 25px; border-top: 4px solid #b97a56; }
        
        /* CSS riêng cho bảng Tồn kho */
        .search-bar { display: flex; gap: 10px; margin-bottom: 20px; }
        .search-bar input { padding: 10px; width: 300px; border: 1px solid #ddd; border-radius: 5px; }
        .search-bar button { padding: 10px 20px; background: #343a40; color: white; border: none; border-radius: 5px; cursor: pointer; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { padding: 12px 15px; border-bottom: 1px solid #eee; text-align: left; vertical-align: middle;}
        th { background: #f9f9f9; color: #555; font-weight: bold; }
        
        .stock-input { width: 80px; padding: 8px; border: 1px solid #ccc; border-radius: 4px; text-align: center; font-weight: bold;}
        .btn-update { background: #b97a56; color: white; border: none; padding: 8px 15px; border-radius: 5px; cursor: pointer; font-weight: bold; transition: 0.2s;}
        .btn-update:hover { background: #9c6343; }
        
        .stock-warning { color: #dc3545; font-weight: bold; background: #fdf2f2; padding: 5px 10px; border-radius: 20px; font-size: 0.9em; }
        .stock-good { color: #28a745; font-weight: bold; }
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
            <li><a href="inventory.php" class="active"><i class="fa-solid fa-boxes-stacked"></i> Update Inventory</a></li>
            <li><a href="schedule.php"><i class="fa-solid fa-calendar-days"></i> My Schedule</a></li>
            <li><a href="profile.php"><i class="fa-solid fa-user-gear"></i> My Profile</a></li>
        </ul>
    </div>

    <div class="main-wrapper">
        <div class="top-header">
            <div class="welcome">👨‍🍳 Kitchen Inventory</div>
            <a href="../auth/logout.php" style="color: #dc3545; text-decoration:none; font-weight:bold;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
        
        <div class="content">
            <div class="card">
                <h2 style="margin-top: 0; margin-bottom: 20px;">Manage Product Stock</h2>
                
                <?php if($msg): ?>
                    <div style="color: #155724; background-color: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin-bottom: 20px; border-radius: 5px; font-weight: bold;">
                        <i class="fa-solid fa-check-circle"></i> <?php echo $msg; ?>
                    </div>
                <?php endif; ?>

                <form class="search-bar" method="GET">
                    <input type="text" name="search" placeholder="Search product name..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit"><i class="fa-solid fa-magnifying-glass"></i> Search</button>
                    <a href="inventory.php" style="padding: 10px 20px; background: #eee; color: #333; text-decoration: none; border-radius: 5px; line-height: 20px;">Clear</a>
                </form>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 80px;">Image</th>
                            <th>Product Name</th>
                            <th>Status / Warning</th>
                            <th>Quick Update Stock</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($products->num_rows > 0): ?>
                            <?php while($row = $products->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <img src="../../assets/img/<?php echo $row['image']; ?>" width="60" height="60" style="border-radius:8px; object-fit:cover; border: 1px solid #eee;">
                                </td>
                                <td><strong style="color: #333; font-size: 1.1em;"><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                <td>
                                    <?php if($row['stock'] <= 5): ?>
                                        <span class="stock-warning"><i class="fa-solid fa-triangle-exclamation"></i> Low Stock (<?php echo $row['stock']; ?> left)</span>
                                    <?php else: ?>
                                        <span class="stock-good"><i class="fa-solid fa-check"></i> In Stock (<?php echo $row['stock']; ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" style="display: flex; gap: 10px; align-items: center;">
                                        <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                        <input type="number" name="new_stock" class="stock-input" value="<?php echo $row['stock']; ?>" min="0" required>
                                        <button type="submit" class="btn-update"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align: center; padding: 30px; color: #888;">No products found!</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>