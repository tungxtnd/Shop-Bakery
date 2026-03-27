<?php
// filepath: d:\Xampp\htdocs\flower_shop\views\customer\pay.php
session_start();
include '../../connectdb.php';


$shipping_fee = 30000;
$total = 0;
$cart_products = [];
$card_message = '';


if (isset($_POST['checkout_items'])) {
    // Coming from cart, multiple items
    $checkout_items = explode(',', $_POST['checkout_items']);
    $user_id = $_SESSION['user_id'];
    $ids = implode(',', array_map('intval', $checkout_items));
    $sql = "
        SELECT ci.id as cart_id, p.name as product_name, p.image as product_image, p.price as product_price,
               ci.quantity, s.name as card_name, s.price as card_price, s.image as card_image, ci.card_message
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        LEFT JOIN services s ON ci.service_id = s.id
        WHERE ci.user_id = $user_id AND ci.id IN ($ids)
    ";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $cart_products[] = $row;
            $subtotal = $row['product_price'] * $row['quantity'] + ($row['card_price'] ?? 0);
            $total += $subtotal;
        }
    }
    $total += $shipping_fee;
} else {
    // Fallback: single product (old logic)
    $product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;
    $card_id = isset($_GET['card']) ? intval($_GET['card']) : null;
    $card_message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : '';


    $product = null;
    $card = null;


    if ($product_id) {
        $result = $conn->query("SELECT name, price, image FROM products WHERE id = $product_id");
        if ($result && $result->num_rows > 0) {
            $product = $result->fetch_assoc();
        }
    }
    if ($card_id) {
        $result = $conn->query("SELECT name, price, image FROM services WHERE id = $card_id");
        if ($result && $result->num_rows > 0) {
            $card = $result->fetch_assoc();
        }
    }
    if ($product) {
        $total += $product['price'] * $quantity;
    }
    if ($card) {
        $total += $card['price'];
    }
    $total += $shipping_fee;
}


$user_fullname = $user_phone = $user_email = $user_address = '';
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $user_result = $conn->query("SELECT full_name, phone, email, address FROM users WHERE id = $uid LIMIT 1");
    if ($user_result && $user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();
        $user_fullname = $user['full_name'];
        $user_phone = $user['phone'];
        $user_email = $user['email'];
        $user_address = $user['address'];
    }
}


if (isset($_GET['id'])) {
    $product_id = intval($_GET['id']);
    $quantity = isset($_GET['quantity']) ? intval($_GET['quantity']) : 1;
    $card_id = isset($_GET['card']) ? intval($_GET['card']) : null;
    $message = isset($_GET['message']) ? urldecode($_GET['message']) : '';


    // Fetch product
    $sql = "SELECT name, image, price FROM products WHERE id = $product_id";
    $result = $conn->query($sql);
    $product = $result ? $result->fetch_assoc() : null;


    // Fetch card/service if selected
    $card = null;
    if ($card_id) {
        $sql = "SELECT name, price, image FROM services WHERE id = $card_id";
        $result = $conn->query($sql);
        $card = $result ? $result->fetch_assoc() : null;
    }


    // Build $cart_products array for display
    $cart_products = [];
    if ($product) {
        $cart_products[] = [
            'product_name' => $product['name'],
            'product_image' => $product['image'],
            'product_price' => $product['price'],
            'quantity' => $quantity,
            'card_name' => $card['name'] ?? null,
            'card_price' => $card['price'] ?? 0,
            'card_image' => $card['image'] ?? null,
            'card_message' => $message,
        ];
    }
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment - Blossom Flower Shop</title>
    <style>
        body {
            background: #f8f8f8;
            font-family: 'Montserrat', sans-serif;
        }
        h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
            text-align: center;
        }
        .pay-container {
            width: 80%;
            max-width: 1200px;
            margin: 40px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 12px #eee;
            display: flex;
            overflow: hidden;
        }
        .pay-left, .pay-right {
            padding: 32px 28px;
            flex: 1;
        }
        .pay-left {
            border-right: 1px solid #f0f0f0;
            background: #faf6f8;
        }
        .pay-right {
            border: 1px solid #EE7FAF;
        }
        .pay-left h2, .pay-right h2 {
            color: #000;
            margin-top: 0;
        }
        .pay-left input, .pay-left textarea {
            width: 70%;
            padding: 10px;
            margin-bottom: 16px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        .pay-right .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 12px;
        }
        .pay-right .summary-item img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            margin-right: 10px;
        }
        .pay-right .total {
            font-size: 22px;
            color: #e75480;
            font-weight: bold;
            margin-top: 20px;
        }
        .pay-left button {
            width: auto;
            background: #e75480;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 8px 12px;
            font-size: 18px;
            margin-top: 30px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .pay-left button:hover { background: #d84372; }
        @media (max-width: 800px) {
            .pay-container { flex-direction: column; }
            .pay-left { border-right: none; border-bottom: 1px solid #f0f0f0; }
        }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <br>
    <a href="cart.php" style="text-decoration: none; color: #333; margin-left: 2%;">Cart</a> > Payment
    <h1>Complete Your Payment</h1>
   
        <!-- Left: Customer Info -->
     <form method="post" action="process_payment.php" class="pay-container">
       
        <div class="pay-left">
            <h2>Customer Information</h2>
            <label>Full Name:</label><br>
            <input type="text" name="fullname" required value="<?php echo htmlspecialchars($user_fullname); ?>" placeholder="Enter your full name">
            <br><label>Phone Number:</label><br>
            <input type="text" name="phone" required value="<?php echo htmlspecialchars($user_phone); ?>" placeholder="Enter your phone number">
            <br><label>Email:</label><br>
            <input type="email" name="email" required value="<?php echo htmlspecialchars($user_email); ?>" placeholder="Enter your email"><br>
            <br><label>Address:</label><br>
            <input type="text" name="address" required value="<?php echo htmlspecialchars($user_address); ?>" placeholder="Enter your address">
            <br><label>Note (optional):</label><br>
            <textarea name="note" rows="2" placeholder="Leave your message here"></textarea>
           
            <?php if (isset($_GET['id'])): ?>
                <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
                <input type="hidden" name="quantity" value="<?php echo $quantity; ?>">
                <input type="hidden" name="card_id" value="<?php echo $card_id; ?>">
                <input type="hidden" name="card_message" value="<?php echo $card_message; ?>">
                <input type="hidden" name="total_amount" value="<?php echo $total; ?>">
            <?php elseif (isset($_POST['checkout_items'])): ?>
                <input type="hidden" name="checkout_items" value="<?php echo htmlspecialchars($_POST['checkout_items']); ?>">
            <?php endif; ?>
           
            <br><button type="submit" id="btn-pay">Pay Now</button>
        </div>


        <div class="pay-right">
            <h2>Your order</h2>
            <hr>
            <?php if (!empty($cart_products)): ?>
                <?php foreach ($cart_products as $item): ?>
                    <div class="summary-item">
                        <div style="display:flex;align-items:center;">
                            <img src="../../assets/img/<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                            <div>
                                <div><?php echo htmlspecialchars($item['product_name']); ?></div>
                                <div>Quantity: <?php echo $item['quantity']; ?></div>
                            </div>
                        </div>
                        <div><?php echo number_format($item['product_price'] * $item['quantity']); ?> VND</div>
                    </div>
                    <?php if ($item['card_name']): ?>
                        <div class="summary-item" style="margin-left:40px;">
                            <div style="display:flex;align-items:center;">
                                <img src="../../assets/img/<?php echo htmlspecialchars($item['card_image']); ?>" alt="<?php echo htmlspecialchars($item['card_name']); ?>">
                                <div>
                                    <div><?php echo htmlspecialchars($item['card_name']); ?> (Card)</div>
                                    <?php if ($item['card_message']): ?>
                                        <div style="font-size:12px;color:#888;">Message: <?php echo htmlspecialchars($item['card_message']); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div><?php echo number_format($item['card_price']); ?> VND</div>
                        </div>
                    <?php endif; ?>
                    <hr>
                <?php endforeach; ?>
            <?php endif; ?>
            <div class="summary-item">
                <div>Shipping Fee</div>
                <div><?php echo number_format($shipping_fee); ?> VND</div>
            </div>
            <hr>
            <div class="total">
                Total: <?php echo number_format($total); ?> VND
            </div>
            <hr>
            <h2>Payment method</h2>
            <input type="radio" name="payment_method" value="cod" checked> Cash on Delivery (COD)<br>
            <input type="radio" name="payment_method" value="bank_transfer"> Bank Transfer<br>
            <input type="radio" name="payment_method" value="momo"> Thanh toán qua Ví MoMo (QR Code)<br>
           
            <div id="bank-info" style="display:none; margin-top:15px; color:#555;">
                Vui lòng chuyển khoản vào STK: <strong>123456789</strong> - Ngân hàng Vietcombank (Chi nhánh X)
            </div>
        </div>
    </form>
 
    <?php include '../../includes/footer.php'; ?>
   
    <script>
        // Chỉ giữ lại code hiển thị thông tin ngân hàng khi click vào Bank Transfer
        document.querySelectorAll('input[name="payment_method"]').forEach(function(radio) {
            radio.addEventListener('change', function() {
                var bankInfo = document.getElementById('bank-info');
                if (bankInfo) {
                    bankInfo.style.display = this.value === 'bank_transfer' ? 'block' : 'none';
                }
            });
        });
       
        // Show if already selected (on reload)
        var checkedMethod = document.querySelector('input[name="payment_method"]:checked');
        if(checkedMethod && checkedMethod.value === 'bank_transfer') {
            var bankInfo = document.getElementById('bank-info');
            if (bankInfo) bankInfo.style.display = 'block';
        }
    </script>
</body>
</html>
