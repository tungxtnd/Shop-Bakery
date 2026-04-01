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
            }
       
        /* 1. LƯỚI SẢN PHẨM: 3 cột giống hệt trang Shop */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            width: 100%;
        }


        /* 2. THẺ SẢN PHẨM: Không viền nền trắng, hiệu ứng nảy */
        .product-card {
            background: transparent;
            box-shadow: none;
            padding: 0;
            width: 100%;
            box-sizing: border-box;
            text-align: center;
            height: 100%;
            transition: transform 0.3s ease;
            text-decoration: none;
            display: block;
        }


        .product-card:hover {
            transform: translateY(-8px);
        }


        /* 3. ẢNH LY NƯỚC: Bo góc 12px chuẩn Bakes */
        .product-card img {
            width: 100%;
            height: 280px;
            /* MẸO: Nếu ảnh ly nước của bạn đã tách nền trong suốt, hãy đổi 'cover' thành 'contain' để ly nước không bị cắt mép */
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 15px;
        }
       
        /* Tiêu đề món nước (Màu đỏ sẫm) */
        .product-card h3 {
            margin: 12px 0 8px 0;
            font-size: 20px;

