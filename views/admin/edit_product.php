<?php
session_start();
include '../../connectdb.php';
 
// Check admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../homepage.php");
    exit;
}
 
// Get product id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: mana_products.php");
    exit;
}
$id = intval($_GET['id']);
 
// Fetch product
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
if (!$product) {
    header("Location: mana_products.php");
    exit;
}
 
// Fetch collections
$collections = $conn->query("SELECT id, name FROM collections ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
 
// Fetch edit history (edit_at) from products table
$history = [];
$history_stmt = $conn->prepare("SELECT edit_at FROM products WHERE id = ? AND edit_at IS NOT NULL ORDER BY edit_at DESC");
if ($history_stmt) {
    $history_stmt->bind_param('i', $id);
    $history_stmt->execute();
    $result = $history_stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $history[] = $row['edit_at'];
    }
}
 // Handle update
$errors = [];
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = trim($_POST['name'] ?? '');
    $description   = trim($_POST['description'] ?? '');
    $price         = floatval($_POST['price'] ?? 0);
    $collection_id = intval($_POST['collection_id'] ?? 0);
    $stock         = intval($_POST['stock'] ?? 0);
    $status        = $_POST['status'] ?? 'in_stock';
 
    // Validate
    if ($name === '') $errors[] = "Product name cannot be empty.";
    if ($price < 0)   $errors[] = "Price must be non-negative.";
    if ($stock < 0)   $errors[] = "Stock must be non-negative.";
    if (!in_array($status, ['in_stock', 'out_of_stock'])) $errors[] = "Invalid status.";
 
    // Handle image upload
    $image = $product['image'];
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext   = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allow = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allow)) {
            $errors[] = "Invalid image format.";
        } else {
            $newname = uniqid('prod_') . '.' . $ext;
            $target  = "../../assets/img/$newname";
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $image = $newname;
            } else {
                $errors[] = "Image upload failed.";
            }
        }
    }
 
    // Auto-set status to out_of_stock if stock is 0
    if ($stock == 0) {
        $status = 'out_of_stock';
    }
 
  // Update DB
    if (!$errors) {
        $edit_at = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, image=?, collection_id=?, stock=?, status=?, edit_at=? WHERE id=?");
        $stmt->bind_param('ssdsiissi', $name, $description, $price, $image, $collection_id, $stock, $status, $edit_at, $id);
        if ($stmt->execute()) {
            $success = true;
            // Refresh product info
            $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $product = $stmt->get_result()->fetch_assoc();
            // Refresh history
            $history = [];
            $history_stmt = $conn->prepare("SELECT edit_at FROM products WHERE id = ? AND edit_at IS NOT NULL ORDER BY edit_at DESC");
            if ($history_stmt) {
                $history_stmt->bind_param('i', $id);
                $history_stmt->execute();
                $result = $history_stmt->get_result();
                while ($row = $result->fetch_assoc()) {
                    $history[] = $row['edit_at'];
                }
            }
        } else {
            $errors[] = "Update failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product: <?php echo htmlspecialchars($product['name']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { background: #f8f8f8; font-family: 'Segoe UI', Arial, sans-serif; margin: 0; padding: 0; }
        .breadcrumbs {
            margin: 24px 0 10px 0;
            font-size: 1.08rem;
            color: #888;
            text-align: center;
        }
        .breadcrumbs a { color: #7A5230; text-decoration: none; }
        .container {
            max-width: 1100px;
            margin: 40px auto 0 auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 24px #eee;
            padding: 38px 38px 28px 38px;
            display: flex;
            gap: 48px;
            align-items: flex-start;
        }
        .main-form { flex: 2; }
        .side-info { flex: 1; background: #faf6f8; border-radius: 10px; padding: 28px 22px; margin-top: 12px; min-width: 220px; }
        h2 { color: #7A5230; margin-bottom: 22px; text-align: center; letter-spacing: 0.5px; }
        .form-group { margin-bottom: 22px; }
        label { display: block; font-weight: 500; margin-bottom: 8px; color: #7A5230; }
        .input-row {
            display: flex;
            gap: 24px;
            margin-bottom: 22px;
        }
        .input-row .form-group {
            flex: 1;
            margin-bottom: 0;
        }
        input[type="text"], input[type="number"], select, textarea {
            width: 100%; padding: 11px 14px; border-radius: 7px; border: 1.5px solid #ddd; font-size: 1rem;
            background: #fafafa; transition: border 0.2s;
            box-sizing: border-box;
        }
        input[type="text"]:focus, input[type="number"]:focus, select:focus, textarea:focus {
            border-color: #7A5230;
            outline: none;
        }
        textarea { min-height: 100px; resize: vertical; }
        .img-preview {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 18px;
        }
        .img-preview img {
            width: 100px; height: 100px; object-fit: cover; border-radius: 9px; border: 1.5px solid #eee;
        }
        .img-preview .img-name { color: #888; font-size: 0.98em; }
        .form-actions {
            margin-top: 38px;
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .btn, .btn-danger {
            min-width: 200px;
            max-width: 240px;
            flex: 1 1 200px;
            margin: 0;
            text-align: center;
            box-sizing: border-box;
            font-size: 1.08rem;
            padding: 13px 0;
        }
        .btn {
            background: #7A5230; color: #fff; border: none; border-radius: 6px;
            font-weight: 500;
            transition: background 0.15s;
        }
        .btn:hover { background: #5C3A21; }
        .btn-danger {
            background: #fff0f3; color: #7A5230; border: 1.5px solid #7A5230; border-radius: 6px;
        }
        .btn-danger:hover { background: #ffe3ea; }
        .error-list { color: #c0392b; background: #fff0f0; border-radius: 7px; padding: 12px 20px; margin-bottom: 22px; }
        .success-msg { color: #219653; background: #f3fff3; border-radius: 7px; padding: 12px 20px; margin-bottom: 22px; }
        .edit-history {
            margin-top: 24px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px #eee;
            padding: 18px 16px;
        }
        .edit-history h4 {
            color: #7A5230;
            margin: 0 0 10px 0;
            font-size: 1.08rem;
            letter-spacing: 0.2px;
        }
        .edit-history ul {
            list-style: none;
            padding: 0;
            margin: 0;
            font-size: 0.98rem;
        }
        .edit-history li {
            padding: 6px 0;
            border-bottom: 1px solid #f2dbe3;
            color: #555;
        }
        .edit-history li:last-child {
            border-bottom: none;
        }
        .side-info div { margin-bottom: 12px; color: #444; }
        .side-info b { color: #7A5230; }
        @media (max-width: 900px) {
            .container { flex-direction: column; padding: 18px 4vw; }
            .side-info { margin-top: 24px; }
            .form-actions { flex-direction: column; gap: 16px; }
            .btn, .btn-danger { width: 100%; min-width: 0; max-width: none; }
            .input-row { flex-direction: column; gap: 0; }
        }
        @media (max-width: 600px) {
            .container { padding: 8px 0; }
            .main-form, .side-info { padding: 12px 8px; }
            .form-actions { flex-direction: column; gap: 10px; }
            .btn, .btn-danger { width: 100%; min-width: 0; max-width: none; }
        }
    </style>
    <script>
    function previewImage(input) {
        const img  = document.getElementById('img-preview');
        const name = document.getElementById('img-name');
        if (input.files && input.files[0]) {
            img.src        = URL.createObjectURL(input.files[0]);
            name.textContent = input.files[0].name;
        }
    }
    </script>
</head>
<body>
    <div class="breadcrumbs">
        <a href="dashboard.php">Dashboard</a> &gt;
        <a href="mana_products.php">Products</a> &gt;
        Edit
    </div>
 
    <div class="container">
        <form class="main-form" method="post" enctype="multipart/form-data" autocomplete="off">
            <h2>Edit Product: <?php echo htmlspecialchars($product['name']); ?></h2>
 
            <?php if ($errors): ?>
                <div class="error-list">
                    <?php foreach ($errors as $e) echo "<div>$e</div>"; ?>
                </div>
            <?php endif; ?>
 
            <?php if ($success): ?>
                <div class="success-msg">Update successful!</div>
            <?php endif; ?>
 
            <div class="form-group">
                <label for="name">Product name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
            </div>
 
            <div class="form-group">
                <label for="price">Price (VND)</label>
                <input type="number" id="price" name="price" min="0" step="1000" value="<?php echo htmlspecialchars($product['price']); ?>" required>
            </div>
 
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?php echo htmlspecialchars($product['description']); ?></textarea>
            </div>
 
            <div class="input-row">
                <div class="form-group">
                    <label for="collection_id">Collection</label>
                    <select id="collection_id" name="collection_id" required>
                        <option value="">-- Select collection --</option>
                        <?php foreach ($collections as $col): ?>
                            <option value="<?php echo $col['id']; ?>" <?php if ($product['collection_id'] == $col['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($col['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="stock">Stock</label>
                    <input type="number" id="stock" name="stock" min="0" step="1" value="<?php echo htmlspecialchars($product['stock']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="in_stock"     <?php if ($product['status'] == 'in_stock')     echo 'selected'; ?>>In stock</option>
                        <option value="out_of_stock" <?php if ($product['status'] == 'out_of_stock') echo 'selected'; ?>>Out of stock</option>
                    </select>
                </div>
            </div>
 
            <div class="form-group">
                <label>Product image</label>
                <div class="img-preview">
                    <img id="img-preview" src="../../assets/img/<?php echo htmlspecialchars($product['image']); ?>" alt="Product image">
                    <span class="img-name" id="img-name"><?php echo htmlspecialchars($product['image']); ?></span>
                </div>
                <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(this)">
            </div>
 
            <div class="form-group form-actions">
                <button type="submit" class="btn" id="save-btn">Update</button>
                <a href="mana_products.php" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this product?');">Delete product</a>
            </div>
        </form>
 
        <div class="side-info">
            <div><b>ID:</b> <?php echo $product['id']; ?></div>
            <div><b>Created at:</b> <?php echo $product['created_at']; ?></div>
            <?php if (!empty($product['edit_at'])): ?>
                <div><b>Last edited:</b> <?php echo $product['edit_at']; ?></div>
            <?php endif; ?>
            <div class="edit-history">
                <h4>Edit History</h4>
                <?php if (count($history)): ?>
                    <ul>
                        <?php foreach ($history as $edit_time): ?>
                            <li><?php echo htmlspecialchars($edit_time); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <div style="color:#aaa;">No edit history.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
