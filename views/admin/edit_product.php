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
