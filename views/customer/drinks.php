<?php
session_start();
include '../../includes/header.php';
include '../../connectdb.php';


// BƯỚC QUAN TRỌNG: Thay số 6 bằng đúng ID danh mục "Món Nước" trong bảng collections của bạn
$drink_collection_id = 6;


// Truy vấn chỉ lấy các sản phẩm thuộc danh mục Nước
$sql = "SELECT id, name, image, price, description FROM products WHERE status = 1 AND stock > 0 AND collection_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $drink_collection_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Drinks Menu - Bakes</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        body {
            background: #fff;
            font-family: 'Times New Roman', serif;
        }
        .drinks-container {
            width: 80%;
            margin: 0 auto 60px auto;
        }
        .drinks-title {
            text-align: center;
            margin: 40px 0 10px 0;
            color: #333;
            letter-spacing: 1px;
        }
        .drinks-desc {
            text-align: center;
            color: #666;
            margin-bottom: 40px;
            font-style: italic;
