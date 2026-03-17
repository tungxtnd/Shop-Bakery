<?php
    $currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Flower Shop</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <style>
        body { margin: 0; }
            .swiper { width: 100%; height: 100vh; }
        .hero-banner {
            position: relative; width: 100%; height: 100%;
            background-size: cover; background-position: center;
            display: flex; align-items: center; justify-content: center; text-align: center;overflow: hidden;
        }
        .overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background-color: rgba(0, 0, 0, 0.4); z-index: 1;
        }
        .banner-content {
            position: relative; z-index: 2; max-width: 800px; padding: 0 20px; color: #ffffff;
        }
        .main-title {
            font-family: 'Times New Roman', serif; font-size: 80px; font-weight: bold;
            text-transform: uppercase; letter-spacing: 5px; margin: 10px 0;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.6);
        }
        .sub-text {
            font-family: 'Montserrat', sans-serif; font-size: 16px; line-height: 1.6;
        }
        .bg-video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover; /* Quan trọng: Lệnh này giúp video tràn viền giống y hệt background-size: cover */
            z-index: 0; /* Nằm dưới cùng */
        }


        /* Đảm bảo overlay và nội dung nằm trên video */
        .hero-banner .overlay { z-index: 1; }
        .hero-banner .banner-content { z-index: 2; }
        /* Đổi màu nút điều hướng của Swiper thành trắng */
        .swiper-button-next, .swiper-button-prev { color: rgba(255, 255, 255, 0.7) !important; }
        .swiper-button-next:hover, .swiper-button-prev:hover { color: #ffffff !important; }
        .swiper-pagination-bullet { background: #ffffff !important; opacity: 0.5; }
        .swiper-pagination-bullet-active { opacity: 1; }
        .header-top {
            background: #e3f5e1;
            height: 8px;
        }
        /* 1. Trạng thái mặc định: Ẩn toàn bộ chữ và đẩy xê xuống dưới 50px */
        .swiper-slide .small-text,
        .swiper-slide .main-title,
        .swiper-slide .sub-text {
            opacity: 0;
            transform: translateY(50px);
            transition: all 0.8s ease-out; /* Thời gian chuyển động là 0.8 giây */
        }


        /* 2. Trạng thái Active: Khi slide xuất hiện, chữ sẽ hiện rõ và bay về vị trí cũ (0px) */
        .swiper-slide-active .small-text {
            opacity: 1;
            transform: translateY(0);
            transition-delay: 0.3s; /* Dòng 1 xuất hiện sau 0.3 giây */
        }


        .swiper-slide-active .main-title {
            opacity: 1;
            transform: translateY(0);
            transition-delay: 0.6s; /* Dòng 2 xuất hiện sau 0.6 giây */
        }


        .swiper-slide-active .sub-text {
            opacity: 1;
            transform: translateY(0);
            transition-delay: 0.9s; /* Dòng 3 xuất hiện sau 0.9 giây */
        }
       /* 1. ĐỊNH DẠNG MẶC ĐỊNH CHO TẤT CẢ CÁC TRANG CON (Shop, Giỏ hàng...) */
        .header-main {
            background: #7e481b; /* Nền màu be kem */
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 40px;
            height: 70px;
            font-family: 'Montserrat', Arial, sans-serif;
            width: 100%;
            box-sizing: border-box;
            border-bottom: 1px solid #EADDCA; /* Viền dưới phân cách */
            position: relative; /* Trở lại bình thường, đẩy phần nội dung bên dưới xuống */
            z-index: 10;
        }


        /* 2. LỚP (CLASS) RIÊNG CHỈ DÀNH CHO TRANG CHỦ */
        .header-main.header-transparent {
            position: absolute; /* Trôi lơ lửng đè lên ảnh */
            top: 8px; /* Cách thanh màu nâu header-top 8px */
            background: transparent; /* Nền trong suốt */
            border-bottom: none;
        }
        .header-left {
            display: flex;
            align-items: center;
            gap: 40px;
            flex: 1;
        }
        .logo {
            display: flex;
            align-items: center;
        }
        .logo-img {
            width: 60px;
            height: 60px;
            background: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
            border: 1px solid #eee;
        }
        .nav {
            display: flex;
            gap: 40px;
            position: relative;
        }
        .nav-item {
            position: relative;
        }
        .nav a {
            text-decoration: none;
            color: #ffff;
            font-size: 14px;
            font-weight: 100; /* Montserrat Thin */
            letter-spacing: 1px;
            font-family: 'Montserrat', Verdana;
            padding: 8px 0;
            display: inline-block;
            transition: color 0.2s;
        }
        .nav a::after {
            content: "";
            display: block;
            height: 2px;
            width: 0;
            background: #b97a56;
            transition: width 0.3s;
            margin: 0 auto;
        }
        .nav a:hover, .nav a:focus {
            color: #b97a56;
        }
        .nav a:hover::after, .nav a:focus::after {
            width: 100%;
        }
        /* Dropdown styles */
        .dropdown {
            display: none;
            position: absolute;
            left: 0;
            top: 100%;
            background: #945819;
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.08);
            border-radius: 4px;
            z-index: 10;
        }
        .dropdown a {
            color: #ffffff;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: 500;
            display: block;
            background: none;
        }
        .dropdown a:hover {
            background: #FAD1CC;
            color: #b97a56;
        }
        .nav-item:hover .dropdown {
            display: block;
        }
        .header-actions {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-left: 60px;
        }
        .call {
            display: flex;
            align-items: center;        
            text-decoration: none;
            color: #ffffff;
            font-size: 14px;
            font-weight: 100;
            letter-spacing: 1px;
            font-family: 'Montserrat', Verdana;
            color: #ffffff;
        }
        .call-text {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
            font-size: 10px;
            font-weight: 100;
            color: #ffffff;
        }
        .call-icon {
            margin-right: 7px;
            font-size: 25px;
        }
        .call-number {
            margin-left: 0;
            color: #ffffff;
            font-family: 'Montserrat', Verdana;
            font-size: 14px;
        }
        .icon-cart {
            font-size: 24px;
            color: #ffffff;
            margin-right: 10px;
        }
        .sign-in {
            font-size: 17px;
            color: #ffffff;
            text-decoration: none;
            color: #ffffff;
            font-size: 14px;
            font-weight: 100;
            letter-spacing: 1px;
            font-family: 'Montserrat', Verdana;
            font-weight: 500;
            display: flex;
            align-items: center;
        }
        .dot {
            color: #e74c3c;
            font-size: 22px;
            margin-right: 5px;
            align-self: center;
            line-height: 1;
        }
        .user-icon:hover .dropdown {
            display: block;
        }
        .user-icon .dropdown {
            left: -50px;
            top: 20px;
            position: absolute;
            background: #fff;
            min-width: 130px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.08);
            border-radius: 4px;
            z-index: 10;
        }
    </style>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
        <div class="header-main <?php echo ($currentPage == 'homepage.php') ? 'header-transparent' : ''; ?>">
        <div class="header-left">
            <div class="logo">
                <a href="/homepage.php" style="background:none; border:none; display:inline-block;">
                    <div class="logo-img" style="background:none; border:none;">
                        <img src="/assets/img/logo.png" alt="Blossom Logo" style="width:60px; height:60px; object-fit:contain;">
                    </div>
                </a>
            </div>
            <nav class="nav">
                <div class="nav-item">
                    <a href="/shop.php">BAKERY</a>
                </div>
                <div class="nav-item">
                    <a href="<?php echo ($currentPage == 'homepage.php') ? '#collection' : '/views/customer/collection.php'; ?>">
                        COLLECTION
                    </a>
                    <div class="dropdown">
                        <a href="/views/customer/collection.php?c=all">All collections</a>
                       
                        <a href="/views/customer/collection.php?c=1">Cookies & Snacks</a>
                        <a href="/views/customer/collection.php?c=2">Chilled Cakes & Desserts</a>
                        <a href="/views/customer/collection.php?c=3">Traditional Cake</a>
                        <a href="/views/customer/collection.php?c=0">Birthday Cake</a>
                        <a href="/views/customer/collection.php?c=4">Pastries Cake</a>
                        <!-- <a href="/views/customer/collection.php?c=5">International's Day</a> -->
                    </div>
                </div>
                <div class="nav-item">
                        <a href="/views/customer/drinks.php">DRINKS</a>
                    </div>
                <div class="nav-item">
                    <a href="#about">OUR STORY</a>
                    <div class="dropdown">
                        <a href="/views/customer/about_us.php">About Us</a>
                        <a href="/views/customer/our_team.php">Our Team</a>
                    </div>
                </div>
            </nav>
        </div>
        <div class="header-actions">
            <div class="call">
                <span class="call-icon"><i class="fa fa-phone"></i></span>
                <div class="call-text">
                    <span>CALL TO ORDER</span>
                    <span class="call-number">+84 9001090</span>
                </div>
            </div>
            <span class="icon-cart" style="cursor:pointer;"
                onclick="
                    <?php if (isset($_SESSION['user_id'])): ?>
                        window.location.href='/views/customer/noti.php';
                    <?php else: ?>
                        alert('You need to log in first!');
                    <?php endif; ?>
                "
            >
            <i class="fa-solid fa-bell"></i>
            </span>
            <span class="icon-cart" style="cursor:pointer;"
                onclick="
                    <?php if (isset($_SESSION['user_id'])): ?>
                        window.location.href='/views/customer/cart.php';
                    <?php else: ?>
                        alert('You need to log in first!');
                    <?php endif; ?>
                "
            >
                <i class="fa-solid fa-cart-shopping"></i>
            </span>
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="user-icon" style="position:relative; cursor:pointer;">
                    <i class="fa-solid fa-user"></i>
                    <div class="dropdown">
                        <a href="/views/customer/account.php" style="padding:10px 20px; display:block; text-decoration:none; color:#222;">Profile</a>
                        <a href="/views/customer/orderhistory.php" style="padding:10px 20px; display:block; text-decoration:none; color:#222;">My Orders</a>
                    </div>
                </div>
                <a href="/views/auth/logout.php" class="sign-in" onclick="return confirm('Are you sure you want to logout?');"><span class="dot">•</span><b>LOGOUT</b></a>
            <?php else: ?>
                <a href="/views/auth/login.php" class="sign-in"><span class="dot">•</span><b>SIGN-IN</b></a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

