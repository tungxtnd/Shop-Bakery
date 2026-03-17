<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link href='https://fonts.googleapis.com/css?family=Charmonman' rel='stylesheet'>
    <link href='https://fonts.googleapis.com/css?family=Inria Serif' rel='stylesheet'>
    <title>Bakery Shop</title>
    <style>
        html {
            scroll-behavior: smooth;
            margin: 0;
            padding: 0;
            width: 100%;
        }
        body {
            font-family: Montserrat;
            background-color: #f8f8f8;
            margin: 0;
        }
        header {
            width: 100%;
            background: #FFE8EE;
            color: black;
            padding: 130px 0px 20px 0px;
            position: relative;
        }
        header h1 {
            font-size: 48px;
            margin: 0 30px;
            font-weight: bold;
        }
        header p {
            font-size: 20px;
            margin: 10px 30px;
            line-height: 1.5;
            width: 50%;
        }
        .containerb {
            clear: both;
            width: 80%;
            background: #fff;
            margin: 0 auto;
            padding: 20px 10%;


        }
        .container {
            clear: both;
            width: 100%;
            background: #fff;
            margin: 0 auto;
            padding: 20px 0px;
        }
        .container h2 {
            margin: 0 30px;
        }
        .bestsellers {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            width: 100%;
        }
        .bakeries {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            width: 60%;
            justify-content: right;
            margin-right: 20px;
        }


        .bestsellers-text {
            font-size: 15px;
            width: 40%;
            color: #9d503b;
            margin-left: 30px;
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-top: auto;
        }


        .bestsellers-text button {
            width: 160px;
            background: #9d503b;
            color: #fff;
            border: none;
            padding: 15px 20px;
            border-radius: 4px;
            cursor: pointer;
        }
        .bestsellers-text button:hover {
            background: #9d503b;
        }
        .text-up {
            font-size: 18px;
            font-weight: bold;
            color: #000;
            margin-bottom: 210px;
        }
        .text-up h2 {
            margin: 0;
        }
        .text-down {
            font-size: 15px;
            color: #333;
            margin-top: 10px;
        }


        .bakery {
            background: #fafafa;
            border: 1px solid #eee;
            border-radius: 8px;
            width: 30%;
            text-align: center;
            box-shadow: 0 2px 8px #eee;
        }
        .bakery img {
            width: 100%;
            height: 280px;
            object-fit: cover;
            border-radius: 8px 8px 0 0;
        }
        .bakery h3 {
            margin: 15px 0 5px;
        }
        .bakery p {
            margin: 0 0 15px;
        }
        .buy-btn {
            background: #9d503b;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin: 0px 20px;
            text-decoration: none;
        }
       
        .bottomleft {
            position: absolute;
            bottom: 8px;
            left: 12px;
            font-size: 20px;
            color: white;
            font-family: 'Inria Serif', serif;
            width: 60%;
        }


        footer {
            clear: both;
            text-align: center;
            color: #888;
            margin: 40px 0 10px;
        }
        .container1 {
            width: 49%;
            position: relative;
            float: left;
            margin: 0 0.5%;
            object-fit: cover;
        }
        .container2 {
            width: 33%;
            position: relative;
            float: left;
            margin: 0 0.1%;
        }
        .collection1 {
            margin-top: 20px;
           
        }
        .collection1::after {
            content: "";
            display: table;
            clear: both;
        }
        .containerc {
            width: 80%;
            margin: 0 auto;
            padding: 20px 10%;
            background: #fff;
            color: #333;
            font-size: 16px;
            line-height: 1.6;
        }
        .circle-button {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: none;
            background-color: #9d503b;
            color: white;
            text-align: center;
            line-height: 48px; /* vertically center text */
            font-size: 18px;
            cursor: pointer;
        }
        .circle-button:hover {
            background-color: #840000;
        }


        .containerc p, .containerc h4 {
            display: none;
        }
        .containerc .faq-section h3,
        .containerc .faq-section h4 {
            display: flex;
            align-items: center;
            cursor: pointer;
            justify-content: space-between;
        }
        .containerc .toggle-icon {
            font-size: 18px;
            margin-left: 10px;
            transition: transform 0.2s;
        }


        .faq-section {
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 0 15px;
        }


        .toggle-icon {
            font-size: 12px;
            margin-left: 5px;
        }
        .login-toast {
            position: fixed;
            left: 24px;
            bottom: 32px;
            background: #2ecc40;
            color: #fff;
            padding: 16px 32px;
            border-radius: 8px;
            font-size: 1.1rem;
            box-shadow: 0 2px 12px #aaa;
            z-index: 9999;
            width: 300px;
            opacity: 1;
            transition: opacity 0.5s;
        }


        /* --- CSS CHO HIỆU ỨNG HOVER SLIDESHOW SẢN PHẨM --- */
.bakery .image-wrapper {
    position: relative !important;
    width: 100% !important;
    height: 280px !important;
    overflow: hidden !important;
    border-radius: 8px 8px 0 0 !important;
    display: block !important;
}


/* ÉP CÁC ẢNH PHẢI XẾP CHỒNG LÊN NHAU TẠI 1 VỊ TRÍ */
.bakery .image-wrapper img.hover-frame {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    margin: 0 !important;
    padding: 0 !important;
    transition: opacity 0.3s ease-in-out !important; /* Hiệu ứng mờ dần */
}


/* Thanh tiến trình màu hồng */
.bakery .progress-bar {
    position: absolute !important;
    bottom: 0 !important;
    left: 0 !important;
    height: 4px !important;
    background: #9d503b !important;
    z-index: 10 !important;
    width: 0;
}


@keyframes progressLoop {
    0% { width: 0; }
    100% { width: 100%; }
}


.bakery .image-wrapper.is-playing .progress-bar {
    animation: progressLoop 1s linear infinite !important;
}
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php if (!empty($_SESSION['login_success'])): ?>
        <div id="login-toast" class="login-toast"><img src="/assets/img/bell.png" style = "width: 6%; margin-right:20px;" >Login successful!</div>
        <script>
            setTimeout(function() {
                document.getElementById('login-toast').style.opacity = '0';
            }, 2000);
            setTimeout(function() {
                document.getElementById('login-toast').style.display = 'none';
            }, 2500);
        </script>
        <?php unset($_SESSION['login_success']); ?>
    <?php endif; ?>
        <div class="swiper myHeroSwiper">
        <div class="swiper-wrapper">
           
           


            <div class="swiper-slide">
                <div class="hero-banner">
                   
                    <video autoplay loop muted playsinline class="bg-video">
                        <source src="/assets/img/make_cake.mp4" type="video/mp4">
                        Trình duyệt của bạn không hỗ trợ video.
                    </video>


                    <div class="overlay"></div>
                    <div class="banner-content">
                        <p class="small-text" style="font-size: 20px; margin:0;">“Bánh luôn đạt</p>
                        <h1 class="main-title">CHẤT LƯỢNG CAO</h1>
                        <p class="sub-text">Để đảm bảo chất lượng hàng đầu, Bakery luôn chọn nguyên liệu chuẩn, đầu tư máy móc hiện đại, mang đến bánh chất lượng cao cho khách hàng.”</p>
                    </div>


                </div>
            </div>
        <div class="swiper-slide">
                        <div class="hero-banner" style="background-image: url('/assets/img/banner1.jpg');">
                            <div class="overlay"></div>
                            <div class="banner-content">
                                <p style="font-size: 20px; margin:0;">“Ai cũng có</p>
                                <h1 class="main-title">TRÁCH NHIỆM</h1>
                                <p class="sub-text">Chăm chỉ, thân thiện, trách nhiệm là yêu cầu cốt lõi cho mọi nhân viên tại Bakery ngay từ những ngày đầu làm việc.”</p>
                            </div>
                        </div>
                    </div>
            <div class="swiper-slide">
                <div class="hero-banner" style="background-image: url('https://images.unsplash.com/photo-1509440159596-0249088772ff?q=80&w=2072&auto=format&fit=crop');">
                    <div class="overlay"></div>
                    <div class="banner-content">
                        <p style="font-size: 20px; margin:0;">“Hương vị</p>
                        <h1 class="main-title">TRUYỀN THỐNG</h1>
                        <p class="sub-text">Chúng tôi giữ gìn trọn vẹn công thức làm bánh thủ công, mang đến những mẻ bánh nóng hổi và thơm ngon nhất mỗi ngày.”</p>
                    </div>
                </div>
            </div>


        </div>


        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-pagination"></div>
    </div>
    <div class="container" id="bouquet">
        <div class="bestsellers">
            <div class="bestsellers-text">
                <div class="text-up">
                    <h2>Best Sellers</h2>
                </div>
                <div class="text-down">
                    Our selection of best selling Cakes by Bakery Shop. Send a beautiful bouquet today.<br>
                    <br><button><a href="shop.php" style="text-decoration:none; color:white;">Shop Bestsellers</a></button>
                </div>
            </div>
            <button onclick="prevBakery()" class="circle-button"><</button>
            <div class="bakeries" id="bakeries-slideshow">
                <!-- Images will be injected by JavaScript -->
            </div>
            <button onclick="nextBakery()" class="circle-button">></button>
        </div>
    </div>
    <div class="containerb" id="collection">
        <center>
            <h3>Discover</h3>
            <h2><img src="/assets/img/flower_5768957.png" width=1.4%> Our Collections <img src="/assets/img/flower_5768957.png" width=1.4%></h2>
        </center>
        <div class="collection1">
            <div class="container1">
                <a href="/views/customer/collection.php?c=1">
                    <img src="/assets/img/collection1.jpg" alt="Bouquet 1" style="width: 100%; height: 400px; object-fit: cover; border-radius: 8px;">
                    <div class="bottomleft">Collection<br><span style="font-size: 20pt;"><b>Cookies & Snacks</b></span></div>
                </a>
            </div>
            <div class="container1">
                <a href="/views/customer/collection.php?c=2">
                    <img src="/assets/img/collection2.jpg" alt="Bouquet 2" style="width: 100%; height: 400px; object-fit: cover; border-radius: 8px;">
                    <div class="bottomleft">Collection<br><span style="font-size: 20pt;"><b>Chilled Cakes & Desserts</b></span></div>
                </a>
            </div>
            <div class="container2">
                <a href="/views/customer/collection.php?c=3">
                    <img src="/assets/img/collection3.jpg" alt="Bouquet 3" style="width: 100%; height: 770px; object-fit: cover; border-radius: 8px;">
                    <div class="bottomleft">Collection<br><span style="font-size: 20pt;"><b>Traditional Cake</b></span></div>
                </a>
            </div>
            <div class="container2">
                <a href="/views/customer/collection.php?c=4">
                    <img src="/assets/img/collection4.jpg" alt="Bouquet 4" style="width: 100%; height: 770px; object-fit: cover; border-radius: 8px;">
                    <div class="bottomleft">Collection<br><span style="font-size: 20pt;"><b>Birthday Cake</b></span></div>
                </a>
            </div>
            <div class="container2">
                <a href="/views/customer/collection.php?c=5">
                    <img src="/assets/img/collection5.jpg" alt="Bouquet 5" style="width: 100%; height: 770px; object-fit: cover; border-radius: 8px;">
                    <div class="bottomleft">Collection<br><span style="font-size: 20pt;"><b>Pastries Cake</b></span></div>
                </a>
            </div>
        </div>
    </div>


    <div class="containerc" id="about">
        <h2>ABOUT BAKERY DELIVERY WITH BAKES BAKERY </h2>
        <div class="faq-section">
            <h3>
                Celebrate a special occasion or send a thoughtful message with an impressive and delicious cake.
                <span class="toggle-icon"><i class="fa-solid fa-chevron-down"></i></span>
            </h3>
            <p>BAKES BAKERY is a one-of-a-kind bakery with an efficient delivery service. We craft our cakes and pastries with the finest ingredients and package them carefully to ensure both our customers and their recipients are 100% satisfied.


            <br><br>Our wide variety of baked goods means you can order online the perfect treat for any occasion. Send a classic red velvet cake for an anniversary or a bright assorted pastry box for a loved one's birthday.


            <br><br>Additionally, our sweet treats are ideal for a thank you gift, a get well soon gesture, congratulating someone on a promotion, or celebrating the birth of a new baby. And if you forget to mark your calendar for Valentine's Day or Mother's Day, don't worry — we're pros at last-minute deliveries.


            <br><br>Need some inspiration? Browse the Collection tab on our website to see what cakes our expert bakers recommend. Once you've made your choice, all you need to do is order online and we'll get started on your bakery delivery right away.</p>
        </div>
        <div class="faq-section">
            <h3>
                Types of Baked Goods We Offer
                <span class="toggle-icon"><i class="fa-solid fa-chevron-down"></i></span>
            </h3>
            <p>With our extensive selection of cakes and pastries, you're sure to find the perfect treat for your loved one. Here are the types of baked goods we offer:


            <span><br><br><b>Whole Cakes</b></span>: Surprise your loved one with a timeless signature cake in a flavor they'll adore, whether it's elegant vanilla, brilliant red velvet, rich chocolate, or refreshing matcha.


            <span><br><b>Chilled Desserts</b></span>: Our soft and delicate mousse and tiramisu cakes add sweetness and elegance to any tea party or office gathering.


            <span><br><b>Fresh Pastries</b></span>: Crispy croissants and buttery tarts add the perfect touch of joy to any morning routine.


            <span><br><b>Assorted Treat Boxes</b></span>: While one pastry is delicious on its own, consider a mixed box of assorted cookies and snacks — the more the merrier!</p>
        </div>
        <div class="faq-section">
            <h3>
                Why Order from Bakes Bakery?
                <span class="toggle-icon"><i class="fa-solid fa-chevron-down"></i></span>
            </h3>
            <p>Here are three of the many reasons you should order and send treats through Bakes Bakery.<br>
                <b>1. Premium Ingredients</b><br>


                Thanks to Bakes Bakery, you don't have to travel to Europe to find authentic pastries. We source our ingredients from top eco-friendly suppliers, using premium butter, pure chocolate, and fresh seasonal fruits. Through our strict selection process, we can provide you with fresh, delicious, and high-quality baked goods year-round.


                <br><br><b>2. Baked Freshly with Love</b><br>


                We treat every order that comes to our bakery with the utmost care and attention. Our skilled bakers craft each cake with an exquisite touch, bake them fresh daily to ensure optimal taste, and then carefully package your order in our unique signature pastry box.


                <br><br><b>3. Fast & Safe Same-Day Delivery</b><br>


                At Bakes Bakery, we pride ourselves on quick and efficient delivery services. We deliver our cakes with the utmost care to keep their shape and decoration intact. For last-minute purchases, we also offer same-day delivery in the city. Check our delivery zones to see how soon we can deliver fresh cakes near you.
            </p>
        </div>
        <div class="faq-section">
            <h3>
                Frequently Asked Questions About Our Bakery Delivery
                <span class="toggle-icon"><i class="fa-solid fa-chevron-down"></i></span>
            </h3>
            <p>Below are some common questions regarding Bakes Bakery delivery.</p>
            <h4>
                <b>How Much Is The Delivery Fee?</b>
                <span class="toggle-icon"><i class="fa-solid fa-chevron-down"></i></span>
            </h4>
            <p>Our delivery fees vary from 15.000₫ for smaller orders to 30.000₫ for large custom cakes, depending on your distance. The large majority of our standard deliveries are around 20.000₫.</p>


            <h4>
                <b>What Payment Methods Do You Accept?</b>
                <span class="toggle-icon"><i class="fa-solid fa-chevron-down"></i></span>
            </h4>
            <p>We currently accept Visa, Mastercard, debit and credit card payments. You can also check out online using mobile banking.</p>


            <h4>
                <b>How Do You Package The Cakes?</b>
                <span class="toggle-icon"><i class="fa-solid fa-chevron-down"></i></span>
            </h4>
            <p>We place our cakes in sturdy, custom-fitted cake boxes with cooling gel packs if necessary, ensuring they remain perfectly intact, fresh, and beautiful during transit.</p>


            <h4>
                <b>How Do I Know When My Order Is on the Way?</b>
                <span class="toggle-icon"><i class="fa-solid fa-chevron-down"></i></span>
            </h4>
            <p>Once your order leaves our bakery, we'll send you a photo of your cake to notify you that it's on the way. After that, you can follow it with our online order tracking feature.</p>


            <h4>
                <b>How Long Will My Cake Last?</b>
                <span class="toggle-icon"><i class="fa-solid fa-chevron-down"></i></span>
            </h4>
            <p>While this usually depends on the type of pastry, most of our chilled cakes and breads will stay fresh for 2-3 days in the refrigerator, while dry cookies and snacks can last up to 2 weeks.</p>
        </div>
        <div class="faq-section">
            <h3>
                Order Cakes Online Today
                <span class="toggle-icon"><i class="fa-solid fa-chevron-down"></i></span>
            </h3>
            <p>We also offer same-day cake delivery for those who need a last-minute gift. Simply place your order before our cut-off time and we'll ensure your sweet treats arrive on the same day.</p>
            <p>At BAKES BAKERY, we take pride in our customer service and quality. Our team is dedicated to making sure your delivery experience is seamless and enjoyable. Whether you're sending a cake to a loved one or treating yourself, we guarantee you'll be satisfied with our service.</p>
            <p>So why wait? Order your cakes online today and let us help you make someone's day special with a delicious treat from BAKES BAKERY.</p>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
    <?php
    include 'connectdb.php';


    $sql = "
    SELECT
        p.id,
        p.name,
        p.image AS primary_image, /* Lấy ảnh gốc làm ảnh đại diện */
        p.price,
        /* Gom tất cả ảnh phụ từ bảng product_images thành 1 chuỗi */
        GROUP_CONCAT(pi.image_name SEPARATOR ',') AS gallery_images,
        SUM(oi.quantity) AS total_sales
    FROM products p
    LEFT JOIN order_items oi ON p.id = oi.product_id
    LEFT JOIN product_images pi ON p.id = pi.product_id
    WHERE p.status = 1
    GROUP BY p.id
    ORDER BY total_sales DESC
    LIMIT 5
    ";
    $result = $conn->query($sql);


    $bakeries = [];
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $bakeries[] = $row;
        }
    }
    ?>


<script>
    // 1. NHẬN DỮ LIỆU TỪ PHP
    const bakeries = <?php echo json_encode($bakeries); ?>;
    let current = 0;
    const imagesPerSlide = 3;
    const slideshow = document.getElementById('bakeries-slideshow');
    let autoSlide; // Biến lưu trữ bộ đếm thời gian của Slide sản phẩm


    // 2. HÀM VẼ GIAO DIỆN SẢN PHẨM VÀ ẢNH
    function renderSlide() {
        slideshow.innerHTML = '';
        for (let i = 0; i < imagesPerSlide; i++) {
            const idx = (current + i) % bakeries.length;
            const bakery = bakeries[idx];
            const div = document.createElement('div');
            div.className = 'bakery';


            // Xử lý gộp ảnh chính và ảnh phụ
            let allImages = [bakery.primary_image];
            if (bakery.gallery_images) {
                let gallery = bakery.gallery_images.split(',').filter(img => img !== bakery.primary_image);
                allImages = allImages.concat(gallery);
            }


            // Tạo các thẻ HTML cho ảnh
            let imgTags = allImages.map((img, index) => {
                let opacity = index === 0 ? '1' : '0'; // Chỉ hiện ảnh đầu tiên
                return `<img src="/assets/img/${img}" class="hover-frame" style="opacity: ${opacity};">`;
            }).join('');


            // In ra màn hình (Gắn sự kiện di chuột vào class image-wrapper)
            div.innerHTML = `
                <a href="/product_details.php?id=${bakery.id}" style="text-decoration:none;color:inherit; display:block;">
                    <div class="image-wrapper" onmouseenter="playGallery(this)" onmouseleave="stopGallery(this)">
                        ${imgTags}
                        <div class="progress-bar"></div>
                    </div>
                    <h3>${bakery.name}</h3>
                    <p>${Number(bakery.price).toLocaleString()} VND</p>
                </a>
            `;
            slideshow.appendChild(div);
        }
    }


    function nextBakery() {
        current = (current + 1) % bakeries.length;
        renderSlide();
    }


    function prevBakery() {
        current = (current - 1 + bakeries.length) % bakeries.length;
        renderSlide();
    }


    function startAutoSlide() {
        // Tự động chuyển sản phẩm sau 3 giây
        autoSlide = setInterval(nextBakery, 3000);
    }


    function stopAutoSlide() {
        // Dừng chuyển sản phẩm
        clearInterval(autoSlide);
    }




    function playGallery(wrapper) {
        stopAutoSlide();
       
        const frames = wrapper.querySelectorAll('.hover-frame');
        if (frames.length <= 1) return; // Không có ảnh phụ thì không làm gì
       
        wrapper.classList.add('is-playing'); // Chạy thanh tiến trình màu hồng
        let currentIndex = 0;


        // Bắt đầu tua ảnh mỗi 1 giây
        wrapper.galleryInterval = setInterval(() => {
            frames[currentIndex].style.opacity = '0';
            currentIndex = (currentIndex + 1) % frames.length;
            frames[currentIndex].style.opacity = '1';
        }, 1000);
    }


    function stopGallery(wrapper) {
        // Tắt tua ảnh
        clearInterval(wrapper.galleryInterval);
        wrapper.classList.remove('is-playing'); // Tắt thanh tiến trình


        // Trả về ảnh gốc
        const frames = wrapper.querySelectorAll('.hover-frame');
        frames.forEach((frame, index) => {
            frame.style.opacity = index === 0 ? '1' : '0';
        });


        // QUAN TRỌNG: Rút chuột ra thì ra lệnh cho slide sản phẩm trượt tiếp
        startAutoSlide();
    }


    renderSlide();
    startAutoSlide();


    document.querySelectorAll('.containerc p, .containerc h4').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.containerc .faq-section h3').forEach(h3 => {
        h3.addEventListener('click', function() {
            const section = h3.parentElement;
            const isOpen = h3.classList.toggle('open');
            h3.querySelector('.toggle-icon').innerHTML = isOpen ? '<i class="fa-solid fa-chevron-up"></i>' : '<i class="fa-solid fa-chevron-down"></i>';
            Array.from(section.children).forEach(child => {
                if (child !== h3) {
                    if (child.tagName === 'H4') {
                        child.style.display = isOpen ? 'flex' : 'none';
                        child.classList.remove('open');
                        child.querySelector('.toggle-icon').innerHTML = '<i class="fa-solid fa-chevron-down"></i>';
                        let next = child.nextElementSibling;
                        while (next && next.tagName === 'P') {
                            next.style.display = 'none';
                            next = next.nextElementSibling;
                        }
                    } else if (child.tagName === 'P') {
                        const prev = child.previousElementSibling;
                        if (!prev || prev.tagName !== 'H4') {
                            child.style.display = isOpen ? 'block' : 'none';
                        }
                    }
                }
            });
        });
    });


    document.querySelectorAll('.containerc .faq-section h4').forEach(h4 => {
        h4.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = h4.classList.toggle('open');
            h4.querySelector('.toggle-icon').innerHTML = isOpen ? '<i class="fa-solid fa-chevron-up"></i>' : '<i class="fa-solid fa-chevron-down"></i>';
            let next = h4.nextElementSibling;
            while (next && next.tagName === 'P') {
                next.style.display = isOpen ? 'block' : 'none';
                next = next.nextElementSibling;
            }
        });
    });
</script>


<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>


<script>
    var swiper = new Swiper(".myHeroSwiper", {
        loop: true, // Cho phép trượt lặp lại vòng tròn
        speed: 1000, // Tốc độ trượt (1 giây)
        autoplay: {
            delay: 5000, // Tự động trượt sau 5 giây
            disableOnInteraction: false,
        },
        navigation: {
            nextEl: ".swiper-button-next",
            prevEl: ".swiper-button-prev",
        },
        pagination: {
            el: ".swiper-pagination",
            clickable: true,
        },
    });
</script>
</body>
</html>

