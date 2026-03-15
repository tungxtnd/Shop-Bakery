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
