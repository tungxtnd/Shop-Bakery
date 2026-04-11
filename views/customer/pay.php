<?php
// filepath: c:\xampp\htdocs\Flower_Shop\views\customer\process_payment.php
ob_start();
session_start();
include '../../includes/header.php';
include '../../connectdb.php';
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$order_success = false;
$account_created = false;
$order_error = '';
$show_checkout = false;
$checkout_ctx = null;

// Mua nhanh từ trang chi tiết: GET pay.php?id=&quantity= — cần form thanh toán trước khi tạo đơn
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id']) && intval($_GET['id']) > 0) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /views/auth/login.php');
        exit;
    }
    $pid = intval($_GET['id']);
    $qty = max(1, intval($_GET['quantity'] ?? 1));
    $svc_id = (isset($_GET['card']) && $_GET['card'] !== '') ? intval($_GET['card']) : null;
    $card_msg = isset($_GET['message']) ? rawurldecode($_GET['message']) : '';
    $shipping_fee = 20000;

    $pres = $conn->query("SELECT price FROM products WHERE id = $pid AND status = 1 LIMIT 1");
    $prow = $pres ? $pres->fetch_assoc() : null;
    if ($prow) {
        $card_price = 0;
        if ($svc_id) {
            $sres = $conn->query("SELECT price FROM services WHERE id = " . intval($svc_id) . " LIMIT 1");
            if ($sres && $srow = $sres->fetch_assoc()) {
                $card_price = floatval($srow['price']);
            }
        }
        $unit = floatval($prow['price']);
        $total_calc = $unit * $qty + $card_price + $shipping_fee;

        $uid = (int) $_SESSION['user_id'];
        $up = $conn->prepare("SELECT full_name, email, phone, address FROM users WHERE id = ?");
        $up->bind_param("i", $uid);
        $up->execute();
        $urow = $up->get_result()->fetch_assoc();
        $up->close();

        $show_checkout = true;
        $checkout_ctx = [
            'product_id' => $pid,
            'quantity' => $qty,
            'service_id' => $svc_id,
            'card_message' => $card_msg,
            'total_amount' => $total_calc,
            'shipping_fee' => $shipping_fee,
            'user' => $urow ?: [],
        ];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fullname'], $_POST['email'], $_POST['phone'], $_POST['address'])) {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $total_amount = floatval($_POST['total_amount'] ?? 0);
    $payment_method = $_POST['payment_method'] ?? 'cod';
    $order_date = date('Y-m-d H:i:s');
    $status = 'Pending';
    $account_created = false;

    if (!isset($_SESSION['user_id'])) {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();
        if ($check->num_rows == 0) {
            // Create new user
            $role = 'customer';
            $password = '12345';
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $fullname, $email, $password, $phone, $address, $role);
            if ($stmt->execute()) {
                $user_id = $stmt->insert_id;
                $_SESSION['user_id'] = $user_id;
                $account_created = true;
                // Set the message here, after $email is defined
                $account_message = "Your account created successfully! You can track your order after logging in with email: <b>$email</b> and password: <b>12345</b>";
            }
        } else {
            // Email exists, fetch user_id for order
            $check->bind_result($user_id);
            $check->fetch();
            $_SESSION['user_id'] = $user_id;
        }
        $check->close();
    } else {
        $user_id = $_SESSION['user_id'];
    }
    // 2. Insert order
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, order_date, status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("idss", $user_id, $total_amount, $order_date, $status);
    if ($stmt->execute()) {
        $order_id = $stmt->insert_id;

        // Add notification for new order (pending)
        $type = 'order_status';
        $message = 'Your order #' . $order_id . ' has been placed and is pending confirmation.';
        $created_at = date('Y-m-d H:i:s');
        $noti_stmt = $conn->prepare("INSERT INTO notifications (user_id, target_user_id, order_id, type, message, created_at) VALUES (?, ?, ?, ?, ?, ?)");
        $noti_stmt->bind_param("iiisss", $user_id, $user_id, $order_id, $type, $message, $created_at);
        $noti_stmt->execute();
        $noti_stmt->close();

        // Insert order items
        if (isset($_POST['checkout_items'])) {
            // From cart
            $checkout_items = explode(',', $_POST['checkout_items']);
            $ids_array = array_filter(array_map('intval', $checkout_items));
            if (!empty($ids_array)) {
                $ids = implode(',', $ids_array);
                // Fetch cart items for this user and these IDs
                $result = $conn->query("SELECT * FROM cart_items WHERE user_id = $user_id AND id IN ($ids)");
                while ($row = $result->fetch_assoc()) {
                    // Get product price from products table
                    $product_id = $row['product_id'];
                    $product_result = $conn->query("SELECT price FROM products WHERE id = $product_id");
                    $product = $product_result->fetch_assoc();
                    if (!$product || !isset($product['price'])) {
                        continue; // Skip this cart item
                    }
                    $price = $product['price'];
                    $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, service_id, card_message) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt2->bind_param("iiiiis", $order_id, $row['product_id'], $row['quantity'], $price, $row['service_id'], $row['card_message']);
                    $stmt2->execute();
                    // Reduce stock
                    $update_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                    $update_stock->bind_param("ii", $row['quantity'], $row['product_id']);
                    $update_stock->execute();
                    $update_stock->close();
                }
                // Remove from cart
                $conn->query("DELETE FROM cart_items WHERE user_id = $user_id AND id IN ($ids)");
                $order_success = true;
            } else {
                $order_error = "No valid cart items selected.";
            }
        } else if (isset($_POST['product_id'])) {
            // Direct buy
            $product_id = intval($_POST['product_id']);
            $quantity = intval($_POST['quantity']);
            $card_id = isset($_POST['card_id']) && $_POST['card_id'] !== '' ? intval($_POST['card_id']) : null;
            $card_message = $_POST['card_message'] ?? '';
            $service_id = isset($_POST['service_id']) && $_POST['service_id'] !== '' ? intval($_POST['service_id']) : null;
            // Get product price
            $result = $conn->query("SELECT price FROM products WHERE id = $product_id");
            $product = $result->fetch_assoc();
            if (!$product || !isset($product['price'])) {
                $order_error = "Product not found or price missing.";
            } else {
                $price = floatval($product['price']);
                $stmt2 = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, service_id, card_message) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt2->bind_param("iiiids", $order_id, $product_id, $quantity, $price, $service_id, $card_message);
                $stmt2->execute();
                $order_success = true;
                // Reduce stock
                $update_stock = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $update_stock->bind_param("ii", $quantity, $product_id);
                $update_stock->execute();
                $update_stock->close();
            }
        } else {
            $order_error = "Order failed. Please try again.";
        }
    }

    if (!empty($order_success) && $payment_method === 'momo') {
        $endpoint = "https://test-payment.momo.vn/v2/gateway/api/create";

        // THAY BẰNG KEY TRONG TÀI KHOẢN DEV MOMO CỦA BẠN
        $partnerCode = "MOMOBKUN20180529"; 
        $accessKey = "klm05TvNBzhg7h7j";
        $secretKey = "at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa";
        
        $orderInfo = "Thanh toán đơn hàng Bakes #" . $order_id;
        $amount = (string)$total_amount;
        $orderId_momo = $order_id . "_" . time(); // Nối thêm time() để mã đơn MoMo không bị trùng
        
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $baseUrl = $scheme . '://' . $host;
        $redirectUrl = $baseUrl . '/views/customer/process_payment.php?momo_return=1';
        $ipnUrl = $baseUrl . '/views/customer/momo_ipn.php';
        
        $extraData = "";
        $requestId = time() . "";
        $requestType = "captureWallet";
        
        $rawHash = "accessKey=".$accessKey."&amount=".$amount."&extraData=".$extraData."&ipnUrl=".$ipnUrl."&orderId=".$orderId_momo."&orderInfo=".$orderInfo."&partnerCode=".$partnerCode."&redirectUrl=".$redirectUrl."&requestId=".$requestId."&requestType=".$requestType;
        $signature = hash_hmac("sha256", $rawHash, $secretKey);
        
        $data = array(
            'partnerCode' => $partnerCode,
            'partnerName' => "Bakes Bakery",
            "storeId" => "BakesStore",
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId_momo,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature
        );

        // Dùng cURL gửi request lên MoMo
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen(json_encode($data))));
        $result = curl_exec($ch);
        curl_close($ch);
        
        $jsonResult = json_decode($result, true);
        
        // Chuyển hướng người dùng sang trang quét mã QR của MoMo
        if (isset($jsonResult['payUrl'])) {
            header('Location: ' . $jsonResult['payUrl']);
            exit;
        } else {
            // Hiện thông báo lỗi nếu MoMo từ chối tạo mã (rất quan trọng để biết tại sao lỗi)
            $error_msg = $jsonResult['message'] ?? 'Lỗi không xác định từ MoMo';
            echo "<script>alert('Không thể tạo mã QR MoMo: " . $error_msg . "'); window.history.back();</script>";
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Payment Processed</title>
        <link rel="stylesheet" href="../../css/style.css">
        <link rel="stylesheet" href="../../css/font-awesome.min.css">
    </head>
<style>
.processpay-container {
    max-width: 480px;
    margin: 60px auto 60px auto;
    background: #fff;
    border-radius: 16px;
    border: 1px solid #f0e0de;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    padding: 48px 24px 36px 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
}
.processpay-icon {
    font-size: 3.2rem;
    color: #2ecc40;
    margin-bottom: 18px;
}
.processpay-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: #222;
    margin-bottom: 10px;
    text-align: center;
}
.processpay-desc {
    font-size: 1.08rem;
    color: #444;
    margin-bottom: 24px;
    text-align: center;
}
.processpay-orderid {
    font-size: 1.05rem;
    color: #d17c7c;
    font-weight: 500;
    margin-bottom: 18px;
}
.processpay-btns {
    display: flex;
    gap: 18px;
    margin-top: 12px;
    width: 100%;
    justify-content: center;
}
.processpay-btn {
    background: #d17c7c;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 10px 28px;
    font-size: 1.08rem;
    font-weight: 500;
    cursor: pointer;
    transition: opacity 0.15s;
    text-decoration: none;
    display: inline-block;
}
.processpay-btn:hover {
    opacity: 0.85;
}
.order-toast {
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
    opacity: 1;
    transition: opacity 0.5s;
}
.checkout-form-wrap { max-width: 520px; margin: 48px auto; padding: 28px; background: #fff; border-radius: 16px; border: 1px solid #f0e0de; box-shadow: 0 2px 12px rgba(0,0,0,0.04); }
.checkout-form-wrap h2 { margin-top: 0; color: #222; }
.checkout-form-wrap label { display: block; margin-bottom: 14px; font-size: 0.95rem; color: #444; }
.checkout-form-wrap input[type="text"],
.checkout-form-wrap input[type="email"],
.checkout-form-wrap input[type="tel"] { width: 100%; box-sizing: border-box; margin-top: 6px; padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px; }
.checkout-form-wrap .pay-row { margin: 18px 0; }
.checkout-form-wrap button[type="submit"] { background: #d17c7c; color: #fff; border: none; border-radius: 6px; padding: 12px 32px; font-size: 1rem; cursor: pointer; margin-top: 8px; }
.processpay-error { max-width: 480px; margin: 60px auto; padding: 32px; text-align: center; color: #c0392b; }
</style>
<body>
<?php if ($show_checkout && $checkout_ctx): ?>
    <?php
    $u = $checkout_ctx['user'];
    $fn = htmlspecialchars($u['full_name'] ?? '', ENT_QUOTES, 'UTF-8');
    $em = htmlspecialchars($u['email'] ?? '', ENT_QUOTES, 'UTF-8');
    $ph = htmlspecialchars($u['phone'] ?? '', ENT_QUOTES, 'UTF-8');
    $ad = htmlspecialchars($u['address'] ?? '', ENT_QUOTES, 'UTF-8');
    ?>
    <div class="checkout-form-wrap">
        <h2>Checkout</h2>
        <p style="color:#666;">Please confirm your details and payment method.</p>
        <form method="post" action="">
            <input type="hidden" name="product_id" value="<?php echo (int) $checkout_ctx['product_id']; ?>">
            <input type="hidden" name="quantity" value="<?php echo (int) $checkout_ctx['quantity']; ?>">
            <input type="hidden" name="card_message" value="<?php echo htmlspecialchars($checkout_ctx['card_message'], ENT_QUOTES, 'UTF-8'); ?>">
            <?php if (!empty($checkout_ctx['service_id'])): ?>
                <input type="hidden" name="service_id" value="<?php echo (int) $checkout_ctx['service_id']; ?>">
            <?php endif; ?>
            <input type="hidden" name="total_amount" value="<?php echo htmlspecialchars((string) $checkout_ctx['total_amount'], ENT_QUOTES, 'UTF-8'); ?>">

            <label>Full name <input type="text" name="fullname" required value="<?php echo $fn; ?>"></label>
            <label>Email <input type="email" name="email" required value="<?php echo $em; ?>"></label>
            <label>Phone <input type="tel" name="phone" required value="<?php echo $ph; ?>"></label>
            <label>Address <input type="text" name="address" required value="<?php echo $ad; ?>"></label>

            <div class="pay-row">
                <span style="font-weight:600;">Payment method</span><br>
                <label style="display:inline;margin-right:16px;margin-top:10px;"><input type="radio" name="payment_method" value="momo" checked> MoMo</label>
                <label style="display:inline;"><input type="radio" name="payment_method" value="cod"> Cash on delivery</label>
            </div>
            <p><strong>Total (incl. shipping):</strong> <?php echo number_format($checkout_ctx['total_amount']); ?> VND</p>
            <button type="submit">Place order</button>
        </form>
    </div>
<?php elseif (!empty($order_error)): ?>
    <div class="processpay-error">
        <p><?php echo htmlspecialchars($order_error); ?></p>
        <p><a href="cart.php" class="processpay-btn" style="display:inline-block;margin-top:16px;text-decoration:none;">Back to cart</a></p>
    </div>
<?php elseif (!empty($order_success)): ?>
    <?php if ($account_created): ?>
        <script>
            alert("<?php echo addslashes($account_message); ?>");
        </script>
    <?php endif; ?>
    <div id="order-toast" class="order-toast">Your order has been placed and is pending confirmation.</div>
    <script>
        setTimeout(function() {
            var t = document.getElementById('order-toast');
            if (t) t.style.opacity = '0';
        }, 2000);
        setTimeout(function() {
            var t = document.getElementById('order-toast');
            if (t) t.style.display = 'none';
        }, 2500);
    </script>
    <div class="processpay-container">
        <div class="processpay-icon">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                <circle cx="24" cy="24" r="24" fill="#eafbe7"/>
                <path d="M15 25.5L21 31.5L33 19.5" stroke="#2ecc40" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="processpay-title">Your order was placed successfully!</div>
        <div class="processpay-desc">
            Thank you for ordering at The Bakery Shop.<br>
            Your order will be processed and delivered as soon as possible.
        </div>
        <?php if ($order_id): ?>
            <div class="processpay-orderid">Order ID: #<?php echo htmlspecialchars((string) $order_id); ?></div>
        <?php endif; ?>
        <div class="processpay-btns">
            <a href="orderhistory.php" class="processpay-btn">View your orders</a>
            <a href="/index.php" class="processpay-btn" style="background:#fff;color:#d17c7c;border:1.5px solid #d17c7c;">Back to homepage</a>
        </div>
    </div>
<?php else: ?>
    <div class="processpay-container">
        <div class="processpay-title" style="margin-bottom:16px;">Checkout</div>
        <div class="processpay-desc">Open a product and use Checkout, or go to your cart to complete an order.</div>
        <div class="processpay-btns">
            <a href="cart.php" class="processpay-btn">Your cart</a>
            <a href="/index.php" class="processpay-btn" style="background:#fff;color:#d17c7c;border:1.5px solid #d17c7c;">Home</a>
        </div>
    </div>
<?php endif; ?>
</body>
<?php include '../../includes/footer.php'; ?>
</html>