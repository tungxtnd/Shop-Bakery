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
 
