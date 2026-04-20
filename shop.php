<?php
session_start();
include 'connectdb.php';

// Handle search and filter
$where = "WHERE status = 1 AND stock > 0 AND collection_id != 6";
$params = [];
if (!empty($_GET['search'])) {
    $where .= " AND name LIKE ?";
    $params[] = '%' . $_GET['search'] . '%';
}
if (!empty($_GET['min_price'])) {
    $where .= " AND price >= ?";
    $params[] = intval($_GET['min_price']);
}
if (!empty($_GET['max_price'])) {
    $where .= " AND price <= ?";
    $params[] = intval($_GET['max_price']);
}
if (!empty($_GET['collection_id'])) {
    $where .= " AND collection_id = ?";
    $params[] = intval($_GET['collection_id']);
}

// Pagination setup
$per_page = 12;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $per_page;

$sql = "SELECT id, name, image, price, description FROM products $where ORDER BY created_at DESC LIMIT $per_page OFFSET $offset";
$stmt = $conn->prepare($sql);

// Bind params dynamically
if ($params) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$collections = [];
$col_result = $conn->query("SELECT id, name FROM collections");
if ($col_result && $col_result->num_rows > 0) {
    while ($row = $col_result->fetch_assoc()) {
        $collections[] = $row;
    }
}

// Count total products for pagination
$count_sql = "SELECT COUNT(*) as total FROM products $where";
$count_stmt = $conn->prepare($count_sql);
if ($params) {
    $types = str_repeat('s', count($params));
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_products = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_products / $per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shop - All Products</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        body { 
            background: #fff; 
            font-family: 'Times New Roman', serif;
        }
        .shop-main { 
            display: flex; 
            width: 100%; 
            margin: 0 0 30px 0; 
            background: #fff;
        }
        .sidebar {
            width: 220px;
            background: #fff;
            border-radius: 8px;
            margin: 0 30px;
            padding: 24px 18px;
            box-shadow: 0 2px 8px #eee;
            height: fit-content;
            border: 1px solid #ddd;
        }
        .products-area { 
            flex: 1; 
        }
        .search-bar {
            width: 95%;
            background: #fff;
            border-radius: 8px;
            display: flex;
            gap: 12px;
            justify-content: right;
            margin: 20px auto;
        }
        .search-bar input, .search-bar button {
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .products-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
       
        .products-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr); 
            gap: 30px; 
            width: 100%;
        }

        
        .product-card {
            background: transparent; 
            box-shadow: none; 
            padding: 0; 
            width: 100%;
            box-sizing: border-box;
            text-align: center;
            height: 100%;
            transition: transform 0.3s ease; 
        }

        .product-card:hover { 
            box-shadow: none; 
            transform: translateY(-8px); 
        }

        .product-card img { 
            width: 100%; 
            height: 280px; 
            object-fit: cover; 
            border-radius: 12px; 
            margin-bottom: 15px; 
        }
        .product-card h3 { 
            margin: 12px 0 8px 0; 
            font-size: 20px; 
            color: #840000; 
        }
        .product-card p { 
            color: #840000; 
            font-size: 18px; 
            margin: 0 0 10px 0; 
        }
        .product-card .desc { 
            color: #666; 
            font-size: 14px; 
            min-height: 40px; 
        }
        .product-card a {
            display: inline-block;
            margin-top: 10px;
            background: #840000;
            color: #fff;
            padding: 8px 18px;
            border-radius: 4px;
            text-decoration: none;
            transition: background 0.2s;
        }
        .product-card a:hover { 
            background: #840000; 
        }
        button[type="submit"] {
            background: #cb5d00;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .product-card-link {
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .product-card-link:visited,
        .product-card-link:active {
            color: inherit;
        }
        input[type="number"], select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        @media (max-width: 900px) {
            .shop-main { flex-direction: column; }
            .sidebar { width: 100%; margin-right: 0; margin-bottom: 20px; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <h1 style="text-align:center; margin: 40px 0 20px 0;">ALL BOUQUETS</h1>
    <p style="text-align:center;">We design bouquets the French way, using seasons and our Parisian roots as inspiration.</p>
    <form class="search-bar" method="get" action="shop.php">
        <input type="text" name="search" placeholder="Search products..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
        <button type="submit">Search</button>
        <a href="shop.php" style="color:#cb5d00; text-align:center; padding: 8px 12px; border: 1px solid #ccc; border-radius: 4px; text-decoration: none;">Reset</a>
    </form>
    <div class="shop-main">
        <form class="sidebar" method="get" action="shop.php">
            <h3 style="margin-top:0;">Filter by Price</h3>
            <input type="hidden" name="search" value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
            <input type="hidden" name="collection_id" value="<?php echo isset($_GET['collection_id']) ? intval($_GET['collection_id']) : ''; ?>">
            <div style="margin-bottom:12px;">
                <label>Min price:</label>
                <input type="number" step="10000" min="0" name="min_price" placeholder="Min" min="0" value="<?php echo isset($_GET['min_price']) ? intval($_GET['min_price']) : ''; ?>">
            </div>
            <div style="margin-bottom:12px;">
                <label>Max price:</label>
                <input type="number" step="10000" min="0" name="max_price" placeholder="Max" min="0" value="<?php echo isset($_GET['max_price']) ? intval($_GET['max_price']) : ''; ?>">
            </div>
            <div style="margin-bottom:18px;">
                <label for="collection_id"><b>Occasion:</b></label>
                <select name="collection_id" id="collection_id" style="width:100%;padding:6px;border-radius:4px;">
                    <option value="">All</option>
                    <?php foreach ($collections as $col): ?>
                        <option value="<?php echo $col['id']; ?>" <?php if(isset($_GET['collection_id']) && $_GET['collection_id'] == $col['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($col['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" style="width: auto;">Apply Filter</button>
        </form>
        <div class="products-area">
            <div class="products-grid">
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while($row = $result->fetch_assoc()): ?>
                        <a href="/product_details.php?id=<?php echo $row['id']; ?>" class="product-card-link" style="text-decoration:none;color:inherit;">
                            <div class="product-card">
                                <img src="/assets/img/<?php echo htmlspecialchars($row['image']); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                                <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                                <p><?php echo number_format($row['price']); ?> VND</p>
                                <div class="desc"><?php echo htmlspecialchars(mb_strimwidth($row['description'], 0, 60, "...")); ?></div>
                            </div>
                        </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="width:100%;text-align:center;">No products found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php if ($total_pages > 1): ?>
    <div class="pagination" style="margin: 32px 0; display: flex; justify-content: center; gap: 8px;">
        <?php for ($p = 1; $p <= $total_pages; $p++): ?>
            <?php
            // Preserve other query params
            $query = $_GET;
            $query['page'] = $p;
            $url = 'shop.php?' . http_build_query($query);
            ?>
            <a href="<?php echo $url; ?>" style="padding:8px 14px;border-radius:4px;<?php if($p == $page) echo 'background:#cb5d00;color:#fff;'; else echo 'background:#fff;color:#e75480;border:1px solid #840000;'; ?>">
                <?php echo $p; ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php include 'includes/footer.php'; ?>
</body>
</html>