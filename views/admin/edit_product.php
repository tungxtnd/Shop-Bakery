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
 
