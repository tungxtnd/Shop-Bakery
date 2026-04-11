<?php
session_start();
include '../../connectdb.php';


// Check admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../index.php");
    exit;
}


// Fetch collections
$collections = $conn->query("SELECT id, name FROM collections ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);


$errors = [];
$success = false;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $collection_id = intval($_POST['collection_id'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $status = $_POST['status'] ?? 'in_stock';


    // Validate
    if ($name === '') $errors[] = "Product name cannot be empty.";
    if ($price < 0) $errors[] = "Price must be non-negative.";
    if ($stock < 0) $errors[] = "Stock must be non-negative.";
    if (!in_array($status, ['in_stock', 'out_of_stock'])) $errors[] = "Invalid status.";
    if ($collection_id <= 0) $errors[] = "Please select a collection.";


    // Handle image upload
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allow = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($ext, $allow)) {
            $errors[] = "Invalid image format.";
        } else {
            $newname = uniqid('prod_') . '.' . $ext;
            $target = "../../assets/img/$newname";
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                $image = $newname;
            } else {
                $errors[] = "Image upload failed.";
            }
        }
    } else {
        $errors[] = "Product image is required.";
    }


    // Insert DB
    if (!$errors) {
        $created_at = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, image, collection_id, stock, status, created_at, edit_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssdsiisss', $name, $description, $price, $image, $collection_id, $stock, $status, $created_at, $created_at);
        if ($stmt->execute()) {
            $success = true;
            // Reset form fields
            $name = $description = '';
            $price = $stock = 0;
            $collection_id = '';
            $status = 'in_stock';
        } else {
            $errors[] = "Add failed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Product</title>
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
        .side-info { flex: 1; background: #faf6f8; border-radius: 10px; padding: 28px 22px; margin-top: 12px; min-width: 220px;}
        h2 { color: #7A5230; margin-bottom: 22px; text-align: center; letter-spacing: 0.5px;}
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
        const img = document.getElementById('img-preview');
        const name = document.getElementById('img-name');
        if (input.files && input.files[0]) {
            img.src = URL.createObjectURL(input.files[0]);
            name.textContent = input.files[0].name;
        } else {
            img.src = "https://via.placeholder.com/100x100?text=No+Image";
            name.textContent = "No image selected";
        }
    }
    </script>
</head>
<body>
    <div class="breadcrumbs">
        <a href="dashboard.php">Dashboard</a> &gt;
        <a href="mana_products.php">Products</a> &gt;
        Add New
    </div>
    <div class="container">
        <form class="main-form" method="post" enctype="multipart/form-data" autocomplete="off">
            <h2>Add New Product</h2>
            <?php if ($errors): ?>
                <div class="error-list">
                    <?php foreach ($errors as $e) echo "<div>$e</div>"; ?>
                </div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="success-msg">Product added successfully!</div>
            <?php endif; ?>
            <div class="form-group">
                <label for="name">Product name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?php echo htmlspecialchars($description ?? ''); ?></textarea>
            </div>
            <div class="input-row">
                <div class="form-group">
                    <label for="collection_id">Collection</label>
                    <select id="collection_id" name="collection_id" required>
                        <option value="">-- Select collection --</option>
                        <?php foreach ($collections as $col): ?>
                            <option value="<?php echo $col['id']; ?>" <?php if(($collection_id ?? '')==$col['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($col['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="stock">Stock</label>
                    <input type="number" id="stock" name="stock" min="0" step="1" value="<?php echo htmlspecialchars($stock ?? 0); ?>" required>
                </div>
                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="in_stock" <?php if(($status ?? 'in_stock')=='in_stock') echo 'selected'; ?>>In stock</option>
                        <option value="out_of_stock" <?php if(($status ?? '')=='out_of_stock') echo 'selected'; ?>>Out of stock</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="price">Price (VND)</label>
                <input type="number" id="price" name="price" min="0" step="1000" value="<?php echo htmlspecialchars($price ?? 0); ?>" required>
            </div>
            <div class="form-group">
                <label>Product image</label>
                <div class="img-preview">
                    <img id="img-preview" src="https://via.placeholder.com/100x100?text=No+Image" alt="Product image">
                    <span class="img-name" id="img-name">No image selected</span>
                </div>
                <input type="file" id="image" name="image" accept="image/*" onchange="previewImage(this)">
            </div>
            <div class="form-group form-actions">
                <button type="submit" class="btn" id="save-btn">Add Product</button>
                <a href="mana_products.php" class="btn btn-danger">Cancel</a>
            </div>
        </form>
        <div class="side-info">
            <div><b>Note:</b> Please fill all required fields and upload a product image.</div>
            <div style="color:#888;font-size:0.98em;">Created products will appear in the product management page.</div>
        </div>
    </div>
</body>
</html>

