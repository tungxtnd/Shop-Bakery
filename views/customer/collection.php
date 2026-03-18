<?php
session_start();
include '../../includes/header.php';
 
// Lấy danh sách collection từ database
$conn = new mysqli('localhost', 'root_user', 'admin123', 'ql_bakery');
$conn->set_charset('utf8');
 
$collections = [];
$sql = "SELECT id, name, description FROM collections WHERE id != 6";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $collections[] = $row;
    }
}
$conn->close();
 // Xác định collection được chọn
$selected = isset($_GET['c']) ? $_GET['c'] : 'all';
 
// Lấy sản phẩm thuộc collection nếu đã chọn collection cụ thể
$products = [];
if (is_numeric($selected) && isset($collections[$selected])) {
    $conn = new mysqli('localhost', 'root_user', 'admin123', 'ql_bakery');
    $conn->set_charset('utf8');
    $stmt = $conn->prepare("SELECT * FROM products WHERE collection_id = ? AND stock > 0");
    $stmt->bind_param("i", $collections[$selected]['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
    $conn->close();
}
?>
