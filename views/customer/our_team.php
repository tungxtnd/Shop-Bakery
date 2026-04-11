<?php include '../../includes/header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Our Team | Bakes Bakery</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f8f8f8; }
        .team-container {
            max-width: 1000px;
            margin: 40px auto 60px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 12px #eee;
            padding: 40px 32px;
        }
        .team-title {
            color: #b97a56;
            font-size: 2.2em;
            font-weight: bold;
            margin-bottom: 10px;
            text-align: center;
        }
        .team-subtitle {
            text-align: center; 
            color: #777; 
            margin-bottom: 30px;
            font-size: 1.1em;
        }
        .team-list {
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 24px;
        }
        .team-member {
            background: #f9f3f5;
            border-radius: 8px;
            padding: 25px 20px;
            text-align: center;
            width: 240px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }
        .team-member:hover {
            transform: translateY(-5px);
        }
        .team-member img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid #b97a56;
            background: #fff;
        }
        .name {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .role {
            font-size: 0.95em;
            color: #b97a56;
            font-weight: bold;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .desc {
            font-size: 0.9em;
            color: #666;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="team-container">
        <div class="team-title">Meet The Bakes Team</div>
        <div class="team-subtitle">Behind every delicious cake is a passionate and creative team.</div>
        
        <div class="team-list">
            <div class="team-member">
                <img src="../../assets/img/bakery-icon.png" alt="Chi Phuong">
                <div class="name">Pham Chi Phuong</div>
                <div class="role">Founder & Head Baker</div>
                <div class="desc">With over 10 years of experience, Huong brings a fierce passion to creating authentic French pastries.</div>
            </div>
            
            <div class="team-member">
                <img src="../../assets/img/bakery-icon.png" alt="Khanh Linh">
                <div class="name">Pham Thi Khanh Linh</div>
                <div class="role">Pastry Chef</div>
                <div class="desc">Trang specializes in modern dessert styles, ensuring every Mousse or Tiramisu is a work of art.</div>
            </div>

            <div class="team-member">
                <img src="../../assets/img/bakery-icon.png" alt="Thuy Duong">
                <div class="name">Dau Thuy Duong</div>
                <div class="role">Store Manager</div>
                <div class="desc">Ha Anh ensures the shop's atmosphere is always cozy and every customer leaves with a smile and a cake box in hand.</div>
            </div>

            <div class="team-member">
                <img src="../../assets/img/bakery-icon.png" alt="Duy Tung">
                <div class="name">Nguyen Duy Tung</div>
                <div class="role">Quality Control</div>
                <div class="desc">Nguyen is responsible for selecting the freshest and highest quality ingredients, from unsalted butter to organic matcha powder.</div>
            </div>

            <div class="team-member">
                <img src="../../assets/img/bakery-icon.png" alt="Quang Ha">
                <div class="name">Pham Quang Ha</div>
                <div class="role">IT & E-commerce Lead</div>
                <div class="desc">The person behind building the entire Bakes website system, bringing a seamless cake ordering experience to your screen.</div>
            </div>

            <div class="team-member">
                <img src="../../assets/img/bakery-icon.png" alt="Ngoc Anh">
                <div class="name">Phan Thi Ngoc Anh</div>
                <div class="role">Cake Decorator</div>
                <div class="desc">Nam is the shop's visual artist. He transforms ordinary birthday cakes into masterpieces with a personal touch.</div>
            </div>

            <div class="team-member">
                <img src="../../assets/img/bakery-icon.png" alt="Thi Tho">
                <div class="name">Hoang Thi Tho</div>
                <div class="role">Head Barista</div>
                <div class="desc">Mai is in charge of the beverage counter, creating the perfect coffee, latte, and matcha to accompany the sweet pastries.</div>
            </div>
        </div>
    </div>
</body>
</html>
<?php include '../../includes/footer.php'; ?>