<?php
session_start();
include '../../connectdb.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../homepage.php");
    exit;
}

// Xử lý xóa sản phẩm
if (isset($_POST['delete_id'])) {
    $del_id = intval($_POST['delete_id']);
    // Thực hiện xóa (Có thể bổ sung xóa ảnh trong thư mục assets nếu cần)
    $conn->query("DELETE FROM products WHERE id = $del_id");
    header("Location: mana_products.php");
    exit;
}

// Truy xuất tất cả sản phẩm
$result = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Products | Bakes Admin</title>
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
        .top-header .title { font-weight: bold; color: #555; font-size: 1.1em; }
        
        /* CONTENT AREA */
        .content { padding: 25px; overflow-y: auto; flex: 1; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); padding: 25px; border-top: 4px solid #b97a56; }
        
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .card-header h2 { margin: 0; color: #333; font-size: 1.5em; }
        
        /* NÚT THÊM MỚI */
        .add-btn { background: #28a745; color: #fff; text-decoration: none; padding: 10px 18px; border-radius: 5px; font-weight: bold; font-size: 0.9em; transition: 0.2s; }
        .add-btn:hover { background: #218838; box-shadow: 0 2px 5px rgba(40, 167, 69, 0.3); }

        /* BẢNG SẢN PHẨM */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px 15px; border-bottom: 1px solid #eee; text-align: left; vertical-align: middle; }
        th { background: #f9f9f9; color: #555; font-weight: bold; text-transform: uppercase; font-size: 0.85em; }
        
        .product-img { width: 55px; height: 55px; object-fit: cover; border-radius: 6px; border: 1px solid #eee; }
        
        /* TRẠNG THÁI TỒN KHO */
        .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.8em; font-weight: bold; }
        .status-in { background: #d4edda; color: #155724; }
        .status-out { background: #f8d7da; color: #721c24; }

        /* NÚT THAO TÁC */
        .btn-action { padding: 6px 10px; border-radius: 4px; text-decoration: none; font-size: 0.85em; color: #fff; margin-right: 5px; border: none; cursor: pointer; display: inline-block; }
        .btn-edit { background: #007bff; }
        .btn-edit:hover { background: #0069d9; }
        .btn-delete { background: #dc3545; }
        .btn-delete:hover { background: #c82333; }
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
            <li><a href="mana_products.php" class="active"><i class="fa-solid fa-cookie-bite"></i> Manage Products</a></li>
            <li><a href="mana_reviews.php"><i class="fa-solid fa-star"></i> Manage Reviews</a></li>
            <li><a href="mana_users.php"><i class="fa-solid fa-users"></i> Manage Users</a></li>
            <li><a href="mana_noti.php"><i class="fa-solid fa-bell"></i> Notifications</a></li>
            <li style="margin-top: 20px;">
                <a href="/views/auth/logout.php" onclick="return confirm('Logout?');" style="color: #ff7675;">
                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                </a>
            </li>
        </ul>
    </div>

    <div class="main-wrapper">
        <div class="top-header">
            <div class="title">Product Catalog Management</div>
            <div style="color: #888;">Logged in as <strong>Admin</strong></div>
        </div>

        <div class="content">
            <div class="card">
                <div class="card-header">
                    <h2>Inventory List</h2>
                    <a href="add_new_product.php" class="add-btn"><i class="fa-solid fa-plus"></i> Add New Product</a>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th style="width: 80px;">Image</th>
                            <th>Product Name</th>
                            <th>Price (VND)</th>
                            <th>Inventory</th>
                            <th>Created Date</th>
                            <th style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['id']; ?></td>
                                <td>
                                    <?php if ($row['image']): ?>
                                        <img src="../../assets/img/<?php echo htmlspecialchars($row['image']); ?>" class="product-img" alt="">
                                    <?php else: ?>
                                        <div style="width:55px; height:55px; background:#eee; border-radius:6px; display:flex; align-items:center; justify-content:center; color:#ccc;"><i class="fa-solid fa-image"></i></div>
                                    <?php endif; ?>
                                </td>
                                <td><strong style="color: #333;"><?php echo htmlspecialchars($row['name']); ?></strong></td>
                                <td><strong><?php echo number_format($row['price']); ?></strong></td>
                                <td>
                                    <?php
                                        if (isset($row['stock']) && $row['stock'] == 0) {
                                            echo '<span class="status-badge status-out">Out of Stock</span>';
                                        } else {
                                            echo '<span class="status-badge status-in">In Stock ('.$row['stock'].')</span>';
                                        }
                                    ?>
                                </td>
                                <td><small style="color:#888;"><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></small></td>
                                <td>
                                    <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="btn-action btn-edit" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </a>
                                    
                                    <form method="post" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                        <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                        <button type="submit" class="btn-action btn-delete" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" style="text-align: center; padding: 40px; color: #888;">No products found in database.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>