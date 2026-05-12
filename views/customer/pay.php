<?php
ob_start();
session_start();
include '../../includes/header.php';
include '../../connectdb.php';

$order_id      = 0;
$order_success = false;
$account_created = false;
$order_error   = '';
$show_checkout = false;
$checkout_ctx  = null;
$shipping_fee  = 20000;

// =====================================================================
// CASE 0: MoMo redirect về sau khi khách thanh toán (?momo_return=1)
// MoMo IPN không thể gọi về localhost → xử lý cập nhật tại đây
// =====================================================================
if (isset($_GET['momo_return'])) {
    $secretKey    = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';
    $partnerCode  = $_GET['partnerCode']  ?? '';
    $orderId      = $_GET['orderId']      ?? ''; // e.g. "54_1715000000"
    $requestId    = $_GET['requestId']    ?? '';
    $amount       = $_GET['amount']       ?? '';
    $orderInfo    = $_GET['orderInfo']    ?? '';
    $orderType    = $_GET['orderType']    ?? '';
    $transId      = $_GET['transId']      ?? '';
    $resultCode   = $_GET['resultCode']   ?? '-1';
    $message      = $_GET['message']      ?? '';
    $payType      = $_GET['payType']      ?? '';
    $responseTime = $_GET['responseTime'] ?? '';
    $extraData    = $_GET['extraData']    ?? '';
    $signature    = $_GET['signature']    ?? '';

    // Xác minh chữ ký
    $rawHash = "accessKey=klm05TvNBzhg7h7j"
        . "&amount="       . $amount
        . "&extraData="    . $extraData
        . "&message="      . $message
        . "&orderId="      . $orderId
        . "&orderInfo="    . $orderInfo
        . "&orderType="    . $orderType
        . "&partnerCode="  . $partnerCode
        . "&payType="      . $payType
        . "&requestId="    . $requestId
        . "&responseTime=" . $responseTime
        . "&resultCode="   . $resultCode
        . "&transId="      . $transId;
    $partnerSignature = hash_hmac("sha256", $rawHash, $secretKey);

    if ($signature === $partnerSignature && $resultCode === '0') {
        $real_order_id = (int) explode('_', $orderId)[0];

        // Cập nhật trạng thái → Paid (chỉ nếu vẫn còn Pending)
        $upd = $conn->prepare("UPDATE orders SET status = 'Paid' WHERE id = ? AND status = 'Pending'");
        $upd->bind_param("i", $real_order_id);
        $upd->execute();
        $affected = $upd->affected_rows;
        $upd->close();

        // Trừ kho (chỉ khi vừa cập nhật thành công, tránh trừ 2 lần)
        if ($affected > 0) {
            $its = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $its->bind_param("i", $real_order_id);
            $its->execute();
            $its_result = $its->get_result();
            while ($item = $its_result->fetch_assoc()) {
                $us = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $us->bind_param("ii", $item['quantity'], $item['product_id']);
                $us->execute();
                $us->close();
            }
            $its->close();
        }

        $order_id      = $real_order_id;
        $order_success = true;
    } else {
        // Thanh toán thất bại hoặc chữ ký không khớp
        $order_error = "MoMo payment failed or was cancelled. Your order has been kept as Pending.";
    }
}

// =====================================================================
// CASE 1: GET ?id= → Mua ngay từ trang Product Detail
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id']) && intval($_GET['id']) > 0) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /views/auth/login.php');
        exit;
    }
    $pid     = intval($_GET['id']);
    $qty     = max(1, intval($_GET['quantity'] ?? 1));
    $svc_id  = (isset($_GET['card']) && $_GET['card'] !== '') ? intval($_GET['card']) : null;
    $card_msg = isset($_GET['message']) ? rawurldecode($_GET['message']) : '';

    $pres = $conn->prepare("SELECT price FROM products WHERE id = ? AND status = 'in_stock' LIMIT 1");
    $pres->bind_param("i", $pid);
    $pres->execute();
    $prow = $pres->get_result()->fetch_assoc();
    $pres->close();

    if ($prow) {
        $card_price = 0;
        if ($svc_id) {
            $sres = $conn->prepare("SELECT price FROM services WHERE id = ? LIMIT 1");
            $sres->bind_param("i", $svc_id);
            $sres->execute();
            $sd = $sres->get_result()->fetch_assoc();
            $sres->close();
            if ($sd) $card_price = floatval($sd['price']);
        }
        $total_calc = floatval($prow['price']) * $qty + $card_price + $shipping_fee;

        $uid = (int) $_SESSION['user_id'];
        $up  = $conn->prepare("SELECT full_name, email, phone, address FROM users WHERE id = ?");
        $up->bind_param("i", $uid);
        $up->execute();
        $urow = $up->get_result()->fetch_assoc();
        $up->close();

        $show_checkout = true;
        $checkout_ctx  = [
            'product_id'   => $pid,
            'quantity'     => $qty,
            'service_id'   => $svc_id,
            'card_message' => $card_msg,
            'total_amount' => $total_calc,
            'shipping_fee' => $shipping_fee,
            'user'         => $urow ?: [],
            'is_cart'      => false,
        ];
    }
}

// =====================================================================
// CASE 2: POST checkout_items nhưng CHƯA có fullname → hiển thị form
// (đến từ cart.php bấm Checkout)
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkout_items']) && !isset($_POST['fullname'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /views/auth/login.php');
        exit;
    }
    $uid          = (int) $_SESSION['user_id'];
    $checkout_ids = array_filter(array_map('intval', explode(',', $_POST['checkout_items'])));

    if (!empty($checkout_ids)) {
        $up = $conn->prepare("SELECT full_name, email, phone, address FROM users WHERE id = ?");
        $up->bind_param("i", $uid);
        $up->execute();
        $urow = $up->get_result()->fetch_assoc();
        $up->close();

        // Tính tổng từ DB (không tin tưởng dữ liệu phía client)
        $placeholders = implode(',', array_fill(0, count($checkout_ids), '?'));
        $types        = 'i' . str_repeat('i', count($checkout_ids));
        $params       = array_merge([$uid], array_values($checkout_ids));

        $cs = $conn->prepare(
            "SELECT ci.quantity, ci.service_id, p.price AS product_price
             FROM cart_items ci JOIN products p ON ci.product_id = p.id
             WHERE ci.user_id = ? AND ci.id IN ($placeholders)"
        );
        $cs->bind_param($types, ...$params);
        $cs->execute();
        $cr          = $cs->get_result();
        $total_calc  = 0;
        while ($row = $cr->fetch_assoc()) {
            $cp = 0;
            if ($row['service_id']) {
                $ss = $conn->prepare("SELECT price FROM services WHERE id = ? LIMIT 1");
                $ss->bind_param("i", $row['service_id']);
                $ss->execute();
                $sd = $ss->get_result()->fetch_assoc();
                $ss->close();
                if ($sd) $cp = floatval($sd['price']);
            }
            $total_calc += floatval($row['product_price']) * $row['quantity'] + $cp;
        }
        $cs->close();
        $total_calc += $shipping_fee;

        $show_checkout = true;
        $checkout_ctx  = [
            'checkout_items' => implode(',', $checkout_ids),
            'total_amount'   => $total_calc,
            'shipping_fee'   => $shipping_fee,
            'user'           => $urow ?: [],
            'is_cart'        => true,
        ];
    }
}

// =====================================================================
// CASE 3: POST với fullname → Tạo đơn hàng
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fullname'], $_POST['email'], $_POST['phone'], $_POST['address'])) {
    $fullname       = trim($_POST['fullname']);
    $email          = trim($_POST['email']);
    $phone          = trim($_POST['phone']);
    $address        = trim($_POST['address']);
    $total_amount   = floatval($_POST['total_amount'] ?? 0);
    $payment_method = in_array($_POST['payment_method'] ?? '', ['cod', 'momo']) ? $_POST['payment_method'] : 'cod';
    $order_date     = date('Y-m-d H:i:s');
    $status         = 'Pending';

    // Lấy user_id
    if (!isset($_SESSION['user_id'])) {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();
        if ($check->num_rows == 0) {
            $role     = 'customer';
            $password = '12345';
            $st       = $conn->prepare("INSERT INTO users (full_name, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, ?)");
            $st->bind_param("ssssss", $fullname, $email, $password, $phone, $address, $role);
            if ($st->execute()) {
                $user_id             = $st->insert_id;
                $_SESSION['user_id'] = $user_id;
                $account_created     = true;
                $account_message     = "Account created! Login with <b>$email</b> / password: <b>12345</b>";
            }
            $st->close();
        } else {
            $check->bind_result($user_id);
            $check->fetch();
            $_SESSION['user_id'] = $user_id;
        }
        $check->close();
    } else {
        $user_id = $_SESSION['user_id'];
    }

    // Insert order (chỉ lưu payment_method, không có recipient)
    $stmt = $conn->prepare(
        "INSERT INTO orders (user_id, total_amount, order_date, status, payment_method)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("idsss",
        $user_id, $total_amount, $order_date, $status, $payment_method
    );

    if ($stmt->execute()) {
        $order_id = $stmt->insert_id;
        $stmt->close();

        // Notification
        $type    = 'order_status';
        $msg_noti = 'Your order #' . $order_id . ' has been placed and is pending confirmation.';
        $now     = date('Y-m-d H:i:s');
        $ns      = $conn->prepare("INSERT INTO notifications (user_id, target_user_id, order_id, type, message, created_at) VALUES (?, ?, ?, ?, ?, ?)");
        $ns->bind_param("iiisss", $user_id, $user_id, $order_id, $type, $msg_noti, $now);
        $ns->execute();
        $ns->close();

        // ---- Xử lý order items ----
        if (isset($_POST['checkout_items'])) {
            // Từ giỏ hàng
            $ids_array = array_filter(array_map('intval', explode(',', $_POST['checkout_items'])));
            if (!empty($ids_array)) {
                $ph    = implode(',', array_fill(0, count($ids_array), '?'));
                $types = 'i' . str_repeat('i', count($ids_array));
                $prms  = array_merge([$user_id], array_values($ids_array));

                $cr = $conn->prepare(
                    "SELECT ci.*, p.price AS product_price
                     FROM cart_items ci JOIN products p ON ci.product_id = p.id
                     WHERE ci.user_id = ? AND ci.id IN ($ph)"
                );
                $cr->bind_param($types, ...$prms);
                $cr->execute();
                $result = $cr->get_result();

                while ($row = $result->fetch_assoc()) {
                    $price = floatval($row['product_price']);
                    $s2    = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, service_id, card_message) VALUES (?, ?, ?, ?, ?, ?)");
                    $s2->bind_param("iiidis", $order_id, $row['product_id'], $row['quantity'], $price, $row['service_id'], $row['card_message']);
                    $s2->execute();
                    $s2->close();

                    // Trừ kho ngay CHỈ với COD. MoMo sẽ trừ trong momo_ipn.php
                    if ($payment_method === 'cod') {
                        $us = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                        $us->bind_param("ii", $row['quantity'], $row['product_id']);
                        $us->execute();
                        $us->close();
                    }
                }
                $cr->close();

                // Xoá khỏi giỏ hàng
                $del_ph = implode(',', array_fill(0, count($ids_array), '?'));
                $del_types = 'i' . str_repeat('i', count($ids_array));
                $del_prms  = array_merge([$user_id], array_values($ids_array));
                $del = $conn->prepare("DELETE FROM cart_items WHERE user_id = ? AND id IN ($del_ph)");
                $del->bind_param($del_types, ...$del_prms);
                $del->execute();
                $del->close();

                $order_success = true;
            } else {
                $order_error = "No valid cart items selected.";
            }

        } elseif (isset($_POST['product_id'])) {
            // Mua ngay
            $product_id  = intval($_POST['product_id']);
            $quantity    = max(1, intval($_POST['quantity']));
            $card_message = $_POST['card_message'] ?? '';
            $service_id  = (isset($_POST['service_id']) && $_POST['service_id'] !== '') ? intval($_POST['service_id']) : null;

            $pr = $conn->prepare("SELECT price FROM products WHERE id = ? LIMIT 1");
            $pr->bind_param("i", $product_id);
            $pr->execute();
            $product = $pr->get_result()->fetch_assoc();
            $pr->close();

            if (!$product) {
                $order_error = "Product not found.";
            } else {
                $price = floatval($product['price']);
                $s2    = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, service_id, card_message) VALUES (?, ?, ?, ?, ?, ?)");
                $s2->bind_param("iiidis", $order_id, $product_id, $quantity, $price, $service_id, $card_message);
                $s2->execute();
                $s2->close();

                // Trừ kho ngay CHỈ với COD
                if ($payment_method === 'cod') {
                    $us = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                    $us->bind_param("ii", $quantity, $product_id);
                    $us->execute();
                    $us->close();
                }
                $order_success = true;
            }
        } else {
            $order_error = "Order failed. Please try again.";
        }
    } else {
        $order_error = "Could not create order. Please try again.";
    }

    // ---- Redirect sang MoMo nếu cần ----
    if (!empty($order_success) && $payment_method === 'momo') {
        $partnerCode = "MOMOBKUN20180529";
        $accessKey   = "klm05TvNBzhg7h7j";
        $secretKey   = "at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa";

        $orderInfo   = "Thanh toan don hang Bakes #" . $order_id;
        $amount      = (string) intval($total_amount);
        $orderId_momo = $order_id . "_" . time();

        $scheme      = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host        = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $baseUrl     = $scheme . '://' . $host;
        $redirectUrl = $baseUrl . '/views/customer/pay.php?momo_return=1';
        $ipnUrl      = $baseUrl . '/views/customer/momo_ipn.php';
        $extraData   = "";
        $requestId   = time() . "";
        $requestType = "captureWallet";

        $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData
            . "&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId_momo . "&orderInfo=" . $orderInfo
            . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl
            . "&requestId=" . $requestId . "&requestType=" . $requestType;
        $signature = hash_hmac("sha256", $rawHash, $secretKey);

        $data = [
            'partnerCode' => $partnerCode, 'partnerName' => "Bakes Bakery",
            'storeId'     => "BakesStore",  'requestId'   => $requestId,
            'amount'      => $amount,        'orderId'     => $orderId_momo,
            'orderInfo'   => $orderInfo,     'redirectUrl' => $redirectUrl,
            'ipnUrl'      => $ipnUrl,        'lang'        => 'vi',
            'extraData'   => $extraData,     'requestType' => $requestType,
            'signature'   => $signature,
        ];

        $ch = curl_init("https://test-payment.momo.vn/v2/gateway/api/create");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $result = curl_exec($ch);
        curl_close($ch);

        $jsonResult = json_decode($result, true);
        if (isset($jsonResult['payUrl'])) {
            header('Location: ' . $jsonResult['payUrl']);
            exit;
        } else {
            $err = $jsonResult['message'] ?? 'Lỗi không xác định';
            echo "<script>alert('Không thể tạo QR MoMo: $err'); window.history.back();</script>";
            exit;
        }
    }
}

// =====================================================================
// CASE 4: GET ?repay=ORDER_ID → Hiển thị form xác nhận lại + chọn PT thanh toán
// =====================================================================
if (isset($_GET['repay']) && intval($_GET['repay']) > 0 && !isset($_POST['repay_order_id'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /views/auth/login.php');
        exit;
    }
    $uid      = (int) $_SESSION['user_id'];
    $repay_id = intval($_GET['repay']);

    $chk = $conn->prepare(
        "SELECT o.*, u.full_name, u.email, u.phone, u.address
         FROM orders o JOIN users u ON o.user_id = u.id
         WHERE o.id = ? AND o.user_id = ? AND o.status = 'Pending'"
    );
    $chk->bind_param("ii", $repay_id, $uid);
    $chk->execute();
    $repay_order = $chk->get_result()->fetch_assoc();
    $chk->close();

    if ($repay_order) {
        $show_checkout = true;
        $checkout_ctx  = [
            'repay_order_id' => $repay_id,
            'total_amount'   => $repay_order['total_amount'],
            'shipping_fee'   => 0, // phí ship đã tính trong total rồi
            'user'           => [
                'full_name' => $repay_order['full_name'],
                'email'     => $repay_order['email'],
                'phone'     => $repay_order['phone'],
                'address'   => $repay_order['address'],
            ],
            'is_cart'   => false,
            'is_repay'  => true,
        ];
    } else {
        $order_error = "Order not found or cannot be modified.";
    }
}

// =====================================================================
// CASE 5: POST repay_order_id → Xử lý thanh toán lại đơn Pending
// =====================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['repay_order_id'])) {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /views/auth/login.php');
        exit;
    }
    $uid            = (int) $_SESSION['user_id'];
    $repay_id       = intval($_POST['repay_order_id']);
    $payment_method = in_array($_POST['payment_method'] ?? '', ['cod', 'momo']) ? $_POST['payment_method'] : 'momo';

    // Xác minh đơn hàng
    $chk = $conn->prepare("SELECT id, total_amount FROM orders WHERE id = ? AND user_id = ? AND status = 'Pending'");
    $chk->bind_param("ii", $repay_id, $uid);
    $chk->execute();
    $repay_order = $chk->get_result()->fetch_assoc();
    $chk->close();

    if (!$repay_order) {
        $order_error = "Order not found.";
    } else {
        // Cập nhật payment_method trong DB
        $upd = $conn->prepare("UPDATE orders SET payment_method = ? WHERE id = ?");
        $upd->bind_param("si", $payment_method, $repay_id);
        $upd->execute();
        $upd->close();

        $total_amount = floatval($repay_order['total_amount']);
        $order_id     = $repay_id;

        if ($payment_method === 'cod') {
            // COD: trừ kho ngay và hoàn thành
            $its = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $its->bind_param("i", $repay_id);
            $its->execute();
            $its_result = $its->get_result();
            while ($item = $its_result->fetch_assoc()) {
                $us = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $us->bind_param("ii", $item['quantity'], $item['product_id']);
                $us->execute();
                $us->close();
            }
            $its->close();
            $order_success = true;

        } else {
            // MoMo: tạo QR và redirect
            $partnerCode  = "MOMOBKUN20180529";
            $accessKey    = "klm05TvNBzhg7h7j";
            $secretKey    = "at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa";

            $orderInfo    = "Thanh toan don hang Bakes #" . $repay_id;
            $amount       = (string) intval($total_amount);
            $orderId_momo = $repay_id . "_" . time();

            $scheme      = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
            $host        = $_SERVER['HTTP_HOST'] ?? 'localhost';
            $baseUrl     = $scheme . '://' . $host;
            $redirectUrl = $baseUrl . '/views/customer/pay.php?momo_return=1';
            $ipnUrl      = $baseUrl . '/views/customer/momo_ipn.php';
            $extraData   = "";
            $requestId   = time() . "";
            $requestType = "captureWallet";

            $rawHash = "accessKey=" . $accessKey . "&amount=" . $amount . "&extraData=" . $extraData
                . "&ipnUrl=" . $ipnUrl . "&orderId=" . $orderId_momo . "&orderInfo=" . $orderInfo
                . "&partnerCode=" . $partnerCode . "&redirectUrl=" . $redirectUrl
                . "&requestId=" . $requestId . "&requestType=" . $requestType;
            $signature = hash_hmac("sha256", $rawHash, $secretKey);

            $data = [
                'partnerCode' => $partnerCode, 'partnerName' => "Bakes Bakery",
                'storeId'     => "BakesStore",  'requestId'   => $requestId,
                'amount'      => $amount,        'orderId'     => $orderId_momo,
                'orderInfo'   => $orderInfo,     'redirectUrl' => $redirectUrl,
                'ipnUrl'      => $ipnUrl,        'lang'        => 'vi',
                'extraData'   => $extraData,     'requestType' => $requestType,
                'signature'   => $signature,
            ];

            $ch = curl_init("https://test-payment.momo.vn/v2/gateway/api/create");
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            $result = curl_exec($ch);
            curl_close($ch);

            $jsonResult = json_decode($result, true);
            if (isset($jsonResult['payUrl'])) {
                header('Location: ' . $jsonResult['payUrl']);
                exit;
            } else {
                $err = $jsonResult['message'] ?? 'Lỗi không xác định';
                echo "<script>alert('Không thể tạo QR MoMo: $err'); window.history.back();</script>";
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout — Bakes Bakery</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/font-awesome.min.css">
    <style>
        .checkout-wrap {
            max-width: 560px; margin: 48px auto;
            background: #fff; border-radius: 16px;
            border: 1px solid #f0e0de;
            box-shadow: 0 2px 12px rgba(0,0,0,.04);
            padding: 36px 32px;
        }
        .checkout-wrap h2 { margin-top: 0; color: #222; }
        .checkout-wrap label { display: block; margin-bottom: 14px; font-size: .95rem; color: #444; }
        .checkout-wrap input[type="text"],
        .checkout-wrap input[type="email"],
        .checkout-wrap input[type="tel"] {
            width: 100%; box-sizing: border-box; margin-top: 6px;
            padding: 10px 12px; border: 1px solid #ddd; border-radius: 6px;
        }
        .recipient-section { display: none; background: #fdf6f6; border-radius: 8px; padding: 16px; margin: 12px 0; }
        .pay-row { margin: 18px 0; }
        .checkout-wrap button[type="submit"] {
            background: #d17c7c; color: #fff; border: none;
            border-radius: 6px; padding: 12px 32px; font-size: 1rem; cursor: pointer; margin-top: 8px;
        }
        .checkout-wrap button[type="submit"]:hover { opacity: .85; }
        .summary-box { background: #fdf6f6; border-radius: 8px; padding: 14px 16px; margin-bottom: 20px; font-size: .97rem; }
        /* Success / Error */
        .processpay-container {
            max-width: 480px; margin: 60px auto;
            background: #fff; border-radius: 16px; border: 1px solid #f0e0de;
            box-shadow: 0 2px 12px rgba(0,0,0,.04); padding: 48px 24px 36px;
            display: flex; flex-direction: column; align-items: center;
        }
        .processpay-title { font-size: 1.25rem; font-weight: 600; color: #222; margin-bottom: 10px; text-align: center; }
        .processpay-desc  { font-size: 1.08rem; color: #444; margin-bottom: 24px; text-align: center; }
        .processpay-orderid { font-size: 1.05rem; color: #d17c7c; font-weight: 500; margin-bottom: 18px; }
        .processpay-btns { display: flex; gap: 18px; margin-top: 12px; justify-content: center; }
        .processpay-btn {
            background: #d17c7c; color: #fff; border: none; border-radius: 6px;
            padding: 10px 28px; font-size: 1.08rem; font-weight: 500;
            cursor: pointer; text-decoration: none; display: inline-block;
        }
        .processpay-btn:hover { opacity: .85; }
        .processpay-error { max-width: 480px; margin: 60px auto; padding: 32px; text-align: center; color: #c0392b; }
        .order-toast {
            position: fixed; left: 24px; bottom: 32px; background: #2ecc40;
            color: #fff; padding: 16px 32px; border-radius: 8px;
            font-size: 1.1rem; box-shadow: 0 2px 12px #aaa; z-index: 9999; transition: opacity .5s;
        }
    </style>
</head>
<body>

<?php if ($show_checkout && $checkout_ctx): ?>
    <?php
    $u  = $checkout_ctx['user'];
    $fn = htmlspecialchars($u['full_name'] ?? '', ENT_QUOTES, 'UTF-8');
    $em = htmlspecialchars($u['email']     ?? '', ENT_QUOTES, 'UTF-8');
    $ph = htmlspecialchars($u['phone']     ?? '', ENT_QUOTES, 'UTF-8');
    $ad = htmlspecialchars($u['address']   ?? '', ENT_QUOTES, 'UTF-8');
    ?>
    <div class="checkout-wrap">
        <?php if (!empty($checkout_ctx['is_repay'])): ?>
            <h2>Pay for Order #<?php echo (int) $checkout_ctx['repay_order_id']; ?></h2>
            <p style="color:#666;">Choose your payment method to complete this order.</p>
        <?php else: ?>
            <h2>Checkout</h2>
            <p style="color:#666;">Please confirm your details and payment method.</p>
        <?php endif; ?>

        <div class="summary-box">
            <strong>Order total<?php echo $checkout_ctx['shipping_fee'] > 0 ? ' (incl. ' . number_format($checkout_ctx['shipping_fee']) . ' VND shipping)' : ''; ?>:</strong>
            <?php echo number_format($checkout_ctx['total_amount']); ?> VND
        </div>

        <form method="post" action="">
            <?php if (!empty($checkout_ctx['is_repay'])): ?>
                <input type="hidden" name="repay_order_id" value="<?php echo (int) $checkout_ctx['repay_order_id']; ?>">
            <?php elseif (!empty($checkout_ctx['is_cart'])): ?>
                <input type="hidden" name="checkout_items" value="<?php echo htmlspecialchars($checkout_ctx['checkout_items'], ENT_QUOTES, 'UTF-8'); ?>">
            <?php else: ?>
                <input type="hidden" name="product_id"   value="<?php echo (int) $checkout_ctx['product_id']; ?>">
                <input type="hidden" name="quantity"      value="<?php echo (int) $checkout_ctx['quantity']; ?>">
                <input type="hidden" name="card_message" value="<?php echo htmlspecialchars($checkout_ctx['card_message'], ENT_QUOTES, 'UTF-8'); ?>">
                <?php if (!empty($checkout_ctx['service_id'])): ?>
                    <input type="hidden" name="service_id" value="<?php echo (int) $checkout_ctx['service_id']; ?>">
                <?php endif; ?>
            <?php endif; ?>
            <input type="hidden" name="total_amount" value="<?php echo (float) $checkout_ctx['total_amount']; ?>">

            <label>Full name  <input type="text"  name="fullname" required value="<?php echo $fn; ?>"></label>
            <label>Email      <input type="email" name="email"    required value="<?php echo $em; ?>"></label>
            <label>Phone      <input type="tel"   name="phone"    required value="<?php echo $ph; ?>"></label>
            <label>Address    <input type="text"  name="address"  required value="<?php echo $ad; ?>"></label>

            <div class="pay-row">
                <span style="font-weight:600;">Payment method</span><br>
                <label style="display:inline;margin-right:16px;margin-top:10px;">
                    <input type="radio" name="payment_method" value="momo" checked> MoMo
                </label>
                <label style="display:inline;">
                    <input type="radio" name="payment_method" value="cod"> Cash on delivery
                </label>
            </div>

            <button type="submit">
                <?php echo !empty($checkout_ctx['is_repay']) ? 'Confirm Payment' : 'Place Order'; ?>
            </button>
        </form>
    </div>

<?php elseif (!empty($order_error)): ?>
    <div class="processpay-error">
        <p><?php echo htmlspecialchars($order_error); ?></p>
        <p><a href="cart.php" class="processpay-btn" style="display:inline-block;margin-top:16px;text-decoration:none;">Back to cart</a></p>
    </div>

<?php elseif (!empty($order_success)): ?>
    <?php if ($account_created): ?>
        <script>alert("<?php echo addslashes($account_message); ?>");</script>
    <?php endif; ?>
    <div id="order-toast" class="order-toast">Your order has been placed successfully!</div>
    <script>
        setTimeout(function(){ document.getElementById('order-toast').style.opacity='0'; }, 2000);
        setTimeout(function(){ document.getElementById('order-toast').style.display='none'; }, 2500);
    </script>
    <div class="processpay-container">
        <div style="font-size:3rem;margin-bottom:18px;">
            <svg width="48" height="48" viewBox="0 0 48 48" fill="none">
                <circle cx="24" cy="24" r="24" fill="#eafbe7"/>
                <path d="M15 25.5L21 31.5L33 19.5" stroke="#2ecc40" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
        <div class="processpay-title">Order placed successfully!</div>
        <div class="processpay-desc">Thank you for ordering at Bakes Bakery.<br>Your order will be processed shortly.</div>
        <?php if ($order_id): ?>
            <div class="processpay-orderid">Order ID: #<?php echo htmlspecialchars((string) $order_id); ?></div>
        <?php endif; ?>
        <div class="processpay-btns">
            <a href="orderhistory.php" class="processpay-btn">View orders</a>
            <a href="/index.php" class="processpay-btn" style="background:#fff;color:#d17c7c;border:1.5px solid #d17c7c;">Home</a>
        </div>
    </div>

<?php else: ?>
    <div class="processpay-container">
        <div class="processpay-title" style="margin-bottom:16px;">Checkout</div>
        <div class="processpay-desc">Open a product and click Checkout, or go to your cart.</div>
        <div class="processpay-btns">
            <a href="cart.php" class="processpay-btn">Your cart</a>
            <a href="/index.php" class="processpay-btn" style="background:#fff;color:#d17c7c;border:1.5px solid #d17c7c;">Home</a>
        </div>
    </div>
<?php endif; ?>

</body>
<?php include '../../includes/footer.php'; ?>
</html>