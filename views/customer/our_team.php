<?php include '../../includes/header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Our Team | Flower Shop</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f8f8f8; }
        .team-container {
            max-width: 900px;
            margin: 40px auto 60px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 12px #eee;
            padding: 40px 32px;
        }
        .team-title {
            color: #e75480;
            font-size: 2.2em;
            font-weight: bold;
            margin-bottom: 18px;
            text-align: center;
        }
        .team-list {
            display: flex;
            gap: 36px;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 24px;
        }
        .team-member {
            background: #f9f3f5;
            border-radius: 8px;
            padding: 22px 26px;
            text-align: center;
            width: 200px;
            box-shadow: 0 1px 4px #eee;
        }
        .team-member img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 12px;
        }
        .team-member .name {
            font-weight: bold;
            color: #e75480;
            margin-bottom: 4px;
            font-size: 1.1em;
        }
        .team-member .role {
            color: #b97a56;
            font-size: 1em;
            margin-bottom: 8px;
        }
        .team-member .desc {
            color: #444;
            font-size: 0.98em;
            line-height: 1.5;
        }
        @media (max-width: 700px) {
            .team-container { padding: 18px 6px; }
            .team-title { font-size: 1.3em; }
            .team-list { flex-direction: column; gap: 18px; }
            .team-member { width: 100%; }
        }
    </style>
</head>
<body>
    <div class="team-container">
        <div class="team-title">Meet Our Team</div>
        <div class="team-list">
            <div class="team-member">
                <img src="../../assets/img/flower_5768957.png" alt="Duy Tung">
                <div class="name">Nguyen Duy Tung</div>
                <div class="role">Founder &amp; Florist</div>
                <div class="desc">Chi brings over 10 years of floral design experience and a passion for creating beautiful arrangements for every occasion.</div>
            </div>
            <div class="team-member">
                <img src="../../assets/img/flower_5768957.png" alt="Trang Thai">
                <div class="name">Thai Ha Trang</div>
                <div class="role">Designer</div>
                <div class="desc">Trang specializes in modern floral styles and ensures every bouquet is unique and memorable.</div>
            </div>
            <div class="team-member">
                <img src="../../assets/img/flower_5768957.png" alt="Ha Anh Nguyen">
                <div class="name">Nguyen Thi Ha Anh</div>
                <div class="role">Customer Support</div>
                <div class="desc">Ha Anh is dedicated to helping customers with their orders and making sure every experience is delightful.</div>
            </div>
            <div class="team-member">
                <img src="../../assets/img/flower_5768957.png" alt="Hoang Thai Nguyen">
                <div class="name">Hoang Thai Nguyen</div>
                <div class="role">Delivery Manager</div>
                <div class="desc">Nguyen coordinates our fast and reliable delivery service, ensuring your flowers arrive fresh and on time.</div>
            </div>
        </div>
    </div>
</body>
</html>
<?php include '../../includes/footer.php'; ?>
