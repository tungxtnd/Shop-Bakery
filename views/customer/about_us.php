<?php include '../../includes/header.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Us | Bakes Bakery</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f8f8f8; }
        .about-container {
            max-width: 800px;
            margin: 40px auto 60px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            padding: 40px 35px;
        }
        .about-title {
            color: #b97a56;
            font-size: 2.2em;
            font-weight: bold;
            margin-bottom: 25px;
            text-align: center;
        }
        .about-section {
            margin-bottom: 35px;
        }
        .about-section h2 {
            color: #333;
            font-size: 1.5em;
            margin-bottom: 15px;
            border-bottom: 2px solid #f0e4df;
            padding-bottom: 8px;
        }
        .about-section p {
            color: #555;
            font-size: 1.05em;
            line-height: 1.8;
            text-align: justify;
        }
        .values-list {
            list-style-type: none;
            padding: 0;
            margin-top: 15px;
        }
        .values-list li {
            margin-bottom: 15px;
            font-size: 1.05em;
            color: #555;
            line-height: 1.6;
            position: relative;
            padding-left: 25px;
        }
        .values-list li::before {
            content: "✨";
            position: absolute;
            left: 0;
            top: 0;
        }
        .values-list li strong {
            color: #b97a56;
        }
        .contact-info p {
            text-align: left;
        }
        .contact-info a {
            color: #b97a56;
            text-decoration: none;
            font-weight: bold;
        }
        .contact-info a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="about-container">
        <div class="about-title">About Bakes</div>
        
        <div class="about-section">
            <h2>Our Story</h2>
            <p>
                Born from a deep love for French pastry and modern baking arts, Bakes started as a small dream in a home kitchen. Today, it has grown into a beloved bakery destination where traditional techniques meet contemporary flavors. We believe that every cake has a story to tell, and we dedicate ourselves to making every bite a memorable experience for you and your loved ones.
            </p>
        </div>

        <div class="about-section">
            <h2>Our Core Values</h2>
            <p>Instead of mass production, we focus on the art of artisanal baking. Here is what we always stand for:</p>
            <ul class="values-list">
                <li><strong>Daily Freshness:</strong> Every pastry, loaf, and cake is baked fresh every single morning. We never sell yesterday's leftovers.</li>
                <li><strong>Premium Ingredients:</strong> We carefully source our ingredients, from rich European butter to organic matcha and fresh local fruits, ensuring no artificial preservatives are used.</li>
                <li><strong>Made with Passion:</strong> Our team treats baking not just as a process, but as an art form. Every detail, from flavor balance to visual decoration, is crafted with utmost care.</li>
            </ul>
        </div>

        <div class="about-section contact-info">
            <h2>Get in Touch</h2>
            <p>
                Whether you have a question, need a custom birthday cake, or just want to say hi, we would love to hear from you!
            </p>
            <p>
                📧 <strong>Email:</strong> <a href="mailto:hello@bakesbakery.com">hello@bakesbakery.com</a><br><br>
                📞 <strong>Phone:</strong> (+84) 123-456-789<br><br>
                📍 <strong>Address:</strong> 123 Nguyen Trai Street, Thanh Xuan District, Hanoi, Vietnam
            </p>
        </div>
    </div>
</body>
</html>
<?php include '../../includes/footer.php'; ?>