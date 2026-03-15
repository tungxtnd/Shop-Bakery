<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <link href='https://fonts.googleapis.com/css?family=Abel' rel='stylesheet'>
  <title>Bakery Footer</title>
  <style>
    footer {
      margin: 0;
      font-family: 'Abel';
      color: #000;
      background: #7e481b;
    }
    .footer-bg {
      background: #7e481b;
      width: 100%;
    }
    .footer-top {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      max-width: 1200px;
      margin: 0 auto;
      padding: 24px 48px 0 48px;
    }
    .footer-logo img {
      height: 110px;
      width: auto;
      display: block;
    }
    .footer-social {
      display: flex;
      gap: 24px;
      margin-top: 12px;
    }
    .footer-social img {
      height: 32px;
      width: 32px;
      transition: opacity 0.2s;
      cursor: pointer;
    }
    .footer-social img:hover {
      opacity: 0.7;
    }
    .footer-main {
      max-width: 1200px;
      margin: 0 auto;
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 48px;
      padding: 8px 48px 32px 48px;
    }
    .footer-col {
      text-align: left;
    }
    .footer-col h3 {
      font-size: 1.1rem;
      font-weight: bold;
      margin-bottom: 10px;
      margin-top: 0;
      color: #ffff;
    }
    .footer-col ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }
    .footer-col ul li {
      margin-bottom: 5px;
      font-size: 0.97rem;
      font-weight: normal;
      color: #ffff;
    }
    .footer-col ul li a {
      color: #ffff;
      text-decoration: none;
      transition: text-decoration 0.2s;
    }
    .footer-col ul li a:hover {
      text-decoration: underline;
    }
    .footer-delivery-cols {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 0 24px;
    }
    .footer-bottom {
      max-width: 1200px;
      margin: 0 auto;
      border-top: 1px solid #e5b6b0;
      padding: 12px 0 0 0;
      text-align: center;
      font-size: 13px;
      color: #000;
      letter-spacing: 0.01em;
    }
    .footer-bottom a {
      color: #ffff;
      text-decoration: none;
      margin: 0 18px;
      transition: text-decoration 0.2s;
    }
    .footer-bottom a:hover {
      text-decoration: underline;
    }
    @media (max-width: 900px) {
      .footer-main {
        grid-template-columns: 1fr 1fr;
        gap: 32px;
        padding: 8px 24px 32px 24px;
      }
      .footer-top {
        flex-direction: column;
        align-items: flex-start;
        padding: 24px 24px 0 24px;
        gap: 16px;
      }
      .footer-social {
        margin-top: 16px;
      }
    }
    @media (max-width: 600px) {
      .footer-main {
        grid-template-columns: 1fr;
        gap: 16px;
        padding: 8px 12px 32px 12px;
      }
      .footer-top {
        padding: 16px 12px 0 12px;
      }
      .footer-logo img {
        height: 70px;
      }
      .footer-social img {
        height: 26px;
        width: 26px;
      }
      .footer-delivery-cols {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
  <footer>
  <div class="footer-bg">
    <!-- Top footer: Logo & Social icons -->
    <div class="footer-top">
      <div class="footer-logo">
        <img src="/assets/img/logo.png" alt="Blossom Logo">
      </div>
      <div class="footer-social">
        <a href="#"><img src="/assets/img/ig.png" alt="Instagram"></a>
        <a href="#"><img src="/assets/img/facebook.png" alt="Facebook"></a>
        <a href="#"><img src="/assets/img/x.png" alt="X"></a>
        <a href="#"><img src="/assets/img/youtube.png" alt="YouTube"></a>
        <a href="#"><img src="/assets/img/email.png" alt="Email"></a>
      </div>
    </div>


    <!-- Footer main -->
    <div class="footer-main">
      <div class="footer-col">
        <h3>Shop</h3>
        <ul>
          <li><a href="#">All cakes</a></li>
          <li><a href="#">Signature Cakes</a></li>
          <li><a href="#">Preserved Cakes</a></li>
          <li><a href="#">Traditional Cakes</a></li>
          <li><a href="#">Birthday Cakes</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h3>About</h3>
        <ul>
          <li><a href="#">Our Story</a></li>
          <li><a href="#">Contact Us</a></li>
          <li><a href="#">Blog</a></li>
          <li><a href="#">Your Account</a></li>
          <li><a href="#">FAQ</a></li>
          <li><a href="#">Where We Deliver</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h3>Same-day Delivery</h3>
        <div class="footer-delivery-cols">
          <ul>
            <li>Cau Giay</li>
            <li>Dong Da</li>
            <li>Thanh Xuan</li>
            <li>Nam Tu Liem</li>
            <li>Bac Tu Liem</li>
            <li>Ha Dong</li>
          </ul>
          <ul>
            <li>Ba Dinh</li>
            <li>Tay Ho</li>
          </ul>
        </div>
      </div>
      <div class="footer-col">
        <h3>Next-day Delivery</h3>
        <div class="footer-delivery-cols">
          <ul>
            <li>Hoai Duc</li>
            <li>Son Tay</li>
            <li>Dan Phuong</li>
            <li>Chuong My</li>
            <li>Thach That</li>
            <li>Soc Son</li>
          </ul>
          <ul>
            <li>Hai Duong</li>
            <li>Ha Nam</li>
            <li>Ninh Binh</li>
            <li>Hung Yen</li>
          </ul>
        </div>
      </div>
    </div>


    <!-- Bottom links -->
    <div class="footer-bottom">
      <a href="#">Sitemap</a>
      <a href="#">Accessibility Statement</a>
      <a href="#">Term & Condition</a>
      <a href="#">Privacy Policy</a>
    </div>
  </div>
  </footer>
</body>
</html>


