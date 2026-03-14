<?php
session_start();
include 'connectdb.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch product info
$sql = "SELECT name, image, price, description FROM products WHERE id = $product_id AND status = 1 LIMIT 1";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $product = $result->fetch_assoc();
} else {
    echo "<p style='text-align:center;margin-top:40px;'>Product not found.</p>";
    exit;
}

// Fetch gallery images
$gallery_images = [];
$gallery_images[] = $product['image'];
$gal_sql = "SELECT image_name FROM product_images WHERE product_id = $product_id";
$gal_result = $conn->query($gal_sql);
if ($gal_result && $gal_result->num_rows > 0) {
    while ($gal_row = $gal_result->fetch_assoc()) {
        $gallery_images[] = $gal_row['image_name'];
    }
}

// Fetch cards from services table
$cards = [];
$card_sql = "SELECT id, name, price, image, description FROM services";
$card_result = $conn->query($card_sql);
if ($card_result && $card_result->num_rows > 0) {
    while ($row = $card_result->fetch_assoc()) {
        $cards[] = $row;
    }
}

// Fetch reviews
$avg_sql = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE product_id = $product_id";
$avg_result = $conn->query($avg_sql);
$avg_row = $avg_result ? $avg_result->fetch_assoc() : null;
$avg_rating = $avg_row && $avg_row['avg_rating'] ? round($avg_row['avg_rating'], 2) : 0;
$total_reviews = $avg_row ? intval($avg_row['total_reviews']) : 0;

$review_sql = "SELECT r.rating, r.comment, r.created_at, u.full_name
               FROM reviews r
               LEFT JOIN users u ON r.user_id = u.id
               WHERE r.product_id = $product_id
               ORDER BY r.created_at DESC";
$review_result = $conn->query($review_sql);

// Handle Add to Cart
if (isset($_POST['add_to_cart'])) {
    if (!isset($_SESSION['user_id'])) {
        echo "<script>alert('You need to log in first!'); window.location='/views/auth/login.php';</script>";
        exit;
    }
    $user_id     = $_SESSION['user_id'];
    $quantity    = isset($_POST['quantity'])     ? intval($_POST['quantity'])    : 1;
    $service_id  = isset($_POST['card'])         ? intval($_POST['card'])        : null;
    $card_message = isset($_POST['card_message']) ? $_POST['card_message']       : '';

    $stmt = $conn->prepare("INSERT INTO cart_items (user_id, product_id, quantity, service_id, card_message) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiiis", $user_id, $product_id, $quantity, $service_id, $card_message);
    if ($stmt->execute()) {
        echo "<script>alert('Add to cart successfully!'); window.location='/views/customer/cart.php';</script>";
        exit;
    } else {
        echo "<script>alert('Failed to add to cart.');</script>";
    }
}

$shipping_fee = 20000;

!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/product_details.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <br>
    <!-- Breadcrumb -->
    <a href="/homepage.php" style="text-decoration:none; margin-left:2%; color:#000;">Home</a> /
    <a href="/shop.php" style="text-decoration:none; color:#000;">All Bouquets</a> /
    <a href="/product_details.php?id=<?php echo $product_id; ?>" style="text-decoration:none; color:#000;">
        <?php echo htmlspecialchars($product['name']); ?>
    </a>
<div class="product-detail">
 
        <!-- LEFT COLUMN -->
        <div class="product-detail-left">
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
 
            <!-- Main Image -->
            <div style="position:relative; width:100%; max-width:500px; margin:20px auto 10px auto;">
                <img id="main-image"
                    src="/assets/img/<?php echo htmlspecialchars($product['image']); ?>"
                    alt="<?php echo htmlspecialchars($product['name']); ?>"
                    style="width:100%; max-width:500px; height:500px; object-fit:cover; display:block; border-radius:12px;">
                <span style="position:absolute; right:0; top:0; background:rgba(183,94,31,0.9); color:#fff; padding:8px 16px; border-top-right-radius:12px; font-size:20px; font-weight:bold;">
                    <?php echo number_format($product['price']); ?> VND
                </span>
            </div>
 <!-- Thumbnail Gallery -->
            <div class="thumbnail-gallery" style="display:flex; justify-content:flex-start; gap:12px; max-width:500px; margin:15px auto 30px auto; overflow-x:auto;">
                <?php foreach ($gallery_images as $index => $img_name): ?>
                    <img src="/assets/img/<?php echo htmlspecialchars($img_name); ?>"
                        class="thumb-item"
                        onclick="changeMainImage(this, '/assets/img/<?php echo htmlspecialchars($img_name); ?>')"
                        style="width:55px; height:55px; margin:0 !important; display:block; flex-shrink:0; object-fit:cover; border-radius:6px; cursor:pointer; border:2px solid <?php echo $index === 0 ? '#9d503b' : 'transparent'; ?>; transition:border-color 0.2s;"
                        alt="Gallery Image">
                <?php endforeach; ?>
            </div>
<!-- Customer Reviews -->
            <div style="margin-top:40px;">
                <h3 style="color:#840000;">Customer Reviews</h3>
                <div style="margin-bottom:18px;">
                    <span style="font-size:1.25em; font-weight:bold; color:#d17c7c;">
                        <?php echo $avg_rating; ?> / 5.0
                    </span>
                    <?php for ($i = 1; $i <= 5; $i++) {
                        echo '<span style="color:' . ($i <= round($avg_rating) ? '#f7b731' : '#ddd') . '; font-size:1.2em;">&#9733;</span>';
                    } ?>
                    <span style="color:#888; font-size:1em; margin-left:10px;">
                        (<?php echo $total_reviews; ?> review<?php echo $total_reviews == 1 ? '' : 's'; ?>)
                    </span>
                </div>
 <?php if ($review_result && $review_result->num_rows > 0): ?>
                    <?php while ($review = $review_result->fetch_assoc()): ?>
                        <div style="border-bottom:1px solid #eee; padding:14px 0;">
                            <div>
                                <?php for ($i = 1; $i <= 5; $i++) {
                                    echo '<span style="color:' . ($i <= $review['rating'] ? '#f7b731' : '#ddd') . '; font-size:1.1em;">&#9733;</span>';
                                } ?>
                                <span style="color:#888; font-size:0.97em; margin-left:10px;">
                                    <?php echo htmlspecialchars($review['full_name'] ?? 'Customer'); ?>
                                    - <?php echo date('Y-m-d', strtotime($review['created_at'])); ?>
                                </span>
                            </div>
                            <div style="margin-top:6px; color:#333;">
                                <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="color:#888; margin:18px 0;">No reviews yet for this product.</div>
                <?php endif; ?>
            </div>
        </div>
 <!-- RIGHT COLUMN -->
        <div class="product-detail-right">
            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
 
            <form method="post" id="cart-form">
                <p>Quantity:
                    <input type="number" id="quantity" name="quantity" value="1" min="1"
                        style="width:60px; padding:5px; border-radius:4px; border:1px solid #ccc;">
                </p>
 
                <p>Pick a card (optional):</p>
                <div class="card">
                    <?php foreach ($cards as $card): ?>
                        <label data-card-id="<?php echo $card['id']; ?>"
                            style="text-align:center; display:inline-block; border-radius:2px;">
                            <input type="radio" name="card"
                                value="<?php echo $card['id']; ?>"
                                data-card-price="<?php echo $card['price']; ?>">
                            <div>
                                <img src="/assets/img/<?php echo htmlspecialchars($card['image']); ?>"
                                    alt="<?php echo htmlspecialchars($card['name']); ?>"
                                    style="width:150px; height:auto; display:block; margin:0;">
                                <div><?php echo htmlspecialchars($card['name']); ?></div>
                                <div style="color:#840000;">+ <?php echo number_format($card['price']); ?> VND</div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>
 
                <p style="margin-top:15px;">Card message (optional):</p>
                <input type="text" name="card_message"
                    style="width:100%; border-radius:4px; border:1px solid #ccc; padding:8px;"
                    placeholder="Leave your message here...">
 
                <p style="margin-top:15px; font-size:18px;">Shipping Fee:
                    <b id="shipping-fee" data-fee="<?php echo $shipping_fee; ?>" style="color:#840000;">
                        <?php echo number_format($shipping_fee); ?> VND
                    </b>
                </p>
                <p style="margin-top:15px;">Total Price:</p>
                <p style="font-size:20px; color:#840000;">
                    <b id="total-price" data-price="<?php echo $product['price']; ?>">
                        <?php echo number_format($product['price']); ?> VND
                    </b>
                </p>
 
                <input type="submit" name="add_to_cart" value="🛒 Add to Cart"
                    style="width:30%; background-color:#9d503b; color:white; padding:14px 20px; border:none; border-radius:4px; cursor:pointer;">
                <button type="button" id="checkout-btn"
                    style="width:30%; background-color:#840000; color:white; padding:14px 20px; border:none; border-radius:4px; cursor:pointer; margin-left:10px;">
                    Checkout
                </button>
            </form>
        </div>
 
    </div>
 
    <script src="/assets/js/product_details.js"></script>
</body>
<?php include 'includes/footer.php'; ?>
</html>
/* =============================================
   product_details.css
   Styles for Product Details page
   ============================================= */

body {
    clear: both;
    margin: 0;
    background-color: #fff;
    font-family: 'Times New Roman', serif;
}

/* Main container */
.product-detail {
    width: 90%;
    margin: 40px auto;
    padding: 24px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 8px #eee;
    overflow: hidden;
}

/* Two-column layout */
.product-detail-left,
.product-detail-right {
    float: left;
    width: 50%;
}

.product-detail-left p {
    text-align: center;
}

.product-detail h2 {
    text-align: center;
    color: #333;
}

.product-detail img {
    width: 100%;
    max-width: 500px;
    display: block;
    margin: 20px auto;
    border-radius: 8px;
}

.product-detail p {
    font-size: 16px;
    color: #000;
}

/* Back link button */
.product-detail a {
    display: inline-block;
    margin-top: 20px;
    color: #fff;
    background: #840000;
    padding: 10px 20px;
    border-radius: 4px;
    text-decoration: none;
}

.product-detail a:hover {
    background: #d84372;
}

/* Card / Gift selection */
.card {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
}

.card input[type="radio"] {
    display: none;
}

.card label {
    border: 2px solid #ccc;
    border-radius: 2px;
    width: 150px;
    cursor: pointer;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.card label.selected {
    border-color: #840000;
    box-shadow: 0 0 8px #e75480;
}

.card label img {
    width: 100%;
    height: auto;
    border-radius: 2px 2px 0 0;
}
// =============================================
// product_details.js
// Handles: image gallery, total price, cart toggle, checkout
// =============================================

// Change main image when clicking thumbnail
function changeMainImage(element, newSrc) {
    document.getElementById('main-image').src = newSrc;
    document.querySelectorAll('.thumb-item').forEach(function(thumb) {
        thumb.style.borderColor = 'transparent';
    });
    element.style.borderColor = '#840000';
}

// Recalculate total = (price × qty) + card price + shipping
function updateTotal() {
    const price       = parseInt(document.getElementById('total-price').getAttribute('data-price'));
    const qty         = parseInt(document.getElementById('quantity').value) || 1;
    const cardRadio   = document.querySelector('input[name="card"]:checked');
    const cardPrice   = cardRadio ? parseInt(cardRadio.getAttribute('data-card-price')) : 0;
    const shippingFee = parseInt(document.getElementById('shipping-fee').getAttribute('data-fee')) || 0;

    const total = price * qty + cardPrice + shippingFee;
    document.getElementById('total-price').textContent = total.toLocaleString() + ' VND';
}

// Update total when quantity changes
document.getElementById('quantity').addEventListener('input', updateTotal);

// Update total when card selection changes
document.querySelectorAll('input[name="card"]').forEach(function(radio) {
    radio.addEventListener('change', updateTotal);
});

// Toggle card selection (click again to deselect)
document.querySelectorAll('.card label').forEach(function(label) {
    label.addEventListener('click', function(e) {
        const input = this.querySelector('input[type="radio"]');
        if (input.checked) {
            // Deselect if already selected
            input.checked = false;
            document.querySelectorAll('.card label').forEach(l => l.classList.remove('selected'));
        } else {
            // Select this card
            document.querySelectorAll('.card label').forEach(l => l.classList.remove('selected'));
            input.checked = true;
            this.classList.add('selected');
        }
        updateTotal();
        e.preventDefault();
    });
});

// Run on page load
updateTotal();

// Checkout button — build URL with params and redirect
document.getElementById('checkout-btn').addEventListener('click', function() {
    const productId = document.getElementById('total-price').getAttribute('data-price') !== undefined
        ? new URLSearchParams(window.location.search).get('id')
        : 0;
    const quantity  = document.getElementById('quantity').value || 1;
    const card      = document.querySelector('input[name="card"]:checked');
    const cardId    = card ? card.value : '';
    const message   = encodeURIComponent(document.querySelector('input[name="card_message"]').value || '');

    let url = `/views/customer/pay.php?id=${productId}&quantity=${quantity}`;
    if (cardId)  url += `&card=${cardId}`;
    if (message) url += `&message=${message}`;

    window.location.href = url;
});
