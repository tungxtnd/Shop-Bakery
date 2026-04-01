<?php


session_start();
include '../../connectdb.php';


// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../homepage.php");
    exit;
}


// Handle delete action
if (isset($_POST['delete_id'])) {
    $del_id = intval($_POST['delete_id']);
    $conn->query("DELETE FROM products WHERE id = $del_id");
    header("Location: mana_products.php");
    exit;
}


// Fetch all products
$result = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - Manage Products</title>
    <style>
        body { background: #f8f8f8; font-family: Arial, sans-serif; margin: 0; padding: 0; }
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
        .container { max-width: 1100px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 12px #eee; padding: 32px; }
        h2 { color: #7A5230; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: center; border-bottom: 1px solid #eee; }
        th { background: #faf6f8; color: #7A5230; }
        img { width: 60px; height: 60px; object-fit: cover; border-radius: 6px; }
        .action-btn {
            background: #5C3A21; color: #fff; border: none; border-radius: 4px;
            padding: 6px 14px; cursor: pointer; transition: background 0.2s;
            text-decoration: none;
        }
        .action-btn:hover { background: #7A5230; }
        .add-btn {
            background: #4CAF50; color: #fff; border: none; border-radius: 4px;
            padding: 8px 18px; cursor: pointer; margin-bottom: 18px; float: right;
            text-decoration: none;
        }
        .add-btn:hover { background: #388e3c; }
    </style>
</head>
<body>
    <nav class="admin-navbar">
        <a href="dashboard.php">Dashboard</a>
        <a href="mana_orders.php">Manage Orders</a>
        <a href="mana_products.php" class="active">Manage Products</a>
        <a href="mana_reviews.php">Manage Reviews</a>
        <a href="mana_users.php">Manage Users</a>
        <a href="mana_noti.php">Manage Notifications</a>
        <a href="/views/auth/logout.php" style="margin-left:auto;" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
    </nav>
    <div class="container">
        <h2>Product Management</h2>
        <a href="add_new_product.php" class="add-btn">+ Add Product</a>
        <table>
            <tr>
                <th>ID</th>
                <th>Image</th>
                <th>Name</th>
                <th>Price (VND)</th>
                <th>Status</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['id']; ?></td>
                <td>
                    <?php if ($row['image']): ?>
                        <img src="../../assets/img/<?php echo htmlspecialchars($row['image']); ?>" alt="">
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($row['name']); ?></td>
                <td><?php echo number_format($row['price']); ?></td>
                <td>
                    <?php
                        if (isset($row['stock']) && $row['stock'] == 0) {
                            echo '<span style="color:#f44336;font-weight:bold;">Out of Stock</span>';
                        } else {
                            echo '<span style="color:#388e3c;font-weight:bold;">In Stock</span>';
                        }
                    ?>
                </td>
                <td><?php echo $row['created_at']; ?></td>
                <td>
                    <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="action-btn" style="background:#2196F3;">Edit</a>
                    <form method="post" action="" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                        <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                        <button type="submit" class="action-btn" style="background:#f44336;">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>
</body>
</html>
