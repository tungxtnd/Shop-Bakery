<?php
// filepath: c:\xampp\htdocs\Flower_Shop\views\customer\orderhistory.php
session_start();
include '../../connectdb.php';

$current_user_id = $_SESSION['user_id'] ?? 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order_id'])) {
    $cancel_order_id = intval($_POST['cancel_order_id']);

    // Bước 1: Kiểm tra đơn hàng có tồn tại, thuộc user, đang Pending không
    $chk = $conn->prepare(
        "SELECT id, payment_method FROM orders WHERE id = ? AND user_id = ? AND status = 'Pending'"
    );
    $chk->bind_param("ii", $cancel_order_id, $current_user_id);
    $chk->execute();
    $order_info = $chk->get_result()->fetch_assoc();
    $chk->close();

    if ($order_info) {
        // Bước 2: Hủy đơn
        $upd = $conn->prepare(
            "UPDATE orders SET status = 'Cancelled' WHERE id = ? AND user_id = ? AND status = 'Pending'"
        );
        $upd->bind_param("ii", $cancel_order_id, $current_user_id);
        $upd->execute();
        $upd->close();

        // Bước 3: Hoàn kho
        // - COD: đã trừ kho khi đặt → phải cộng lại
        // - MoMo: chưa trừ kho → KHÔNG cộng
        // - NULL (đơn cũ trước khi có cột payment_method): giả định COD → cộng lại
        $pm = $order_info['payment_method'] ?? 'cod';
        if ($pm !== 'momo') {
            $its = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $its->bind_param("i", $cancel_order_id);
            $its->execute();
            $its_result = $its->get_result();
            while ($item = $its_result->fetch_assoc()) {
                $rs = $conn->prepare("UPDATE products SET stock = stock + ? WHERE id = ?");
                $rs->bind_param("ii", $item['quantity'], $item['product_id']);
                $rs->execute();
                $rs->close();
            }
            $its->close();
        }

        // Bước 4: Thông báo
        $type = 'order_status';
        $message = 'You have cancelled order #' . $cancel_order_id . '.';
        $created_at = date('Y-m-d H:i:s');
        $noti_stmt = $conn->prepare(
            "INSERT INTO notifications (user_id, target_user_id, order_id, type, message, created_at) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $noti_stmt->bind_param("iiisss", $current_user_id, $current_user_id, $cancel_order_id, $type, $message, $created_at);
        $noti_stmt->execute();
        $noti_stmt->close();
    }

    header("Location: orderhistory.php?status=Pending");
    exit;
}

// Confirm delivery for shipped orders
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delivered_id'])) {
    $confirm_order_id = intval($_POST['confirm_delivered_id']);
    // Only allow confirming own delivering orders
    $sql = "UPDATE orders SET status = 'Completed' WHERE id = $confirm_order_id AND user_id = $current_user_id AND status = 'Delivering'";
    $conn->query($sql);

    // Add notification for delivered order
    $type = 'order_status';
    $message = 'You have confirmed delivery for order #' . $confirm_order_id . '.';
    $created_at = date('Y-m-d H:i:s');
    $noti_stmt = $conn->prepare("INSERT INTO notifications (user_id, target_user_id, order_id, type, message, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    $noti_stmt->bind_param("iiisss", $current_user_id, $current_user_id, $confirm_order_id, $type, $message, $created_at);
    $noti_stmt->execute();
    $noti_stmt->close();

    // Optional: reload to update the list
    header("Location: orderhistory.php?status=Completed");
    exit;
}

// Lấy status filter từ query string, mặc định là 'Pending'
$status_filter = $_GET['status'] ?? 'Pending';
$valid_status = ['Pending', 'Paid', 'Processing', 'Ready for Delivery', 'Delivering', 'Completed', 'Cancelled'];
if (!in_array($status_filter, $valid_status))
    $status_filter = 'Pending';

$conn->set_charset('utf8mb4');
$orders = [];
$ord_stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? AND status = ? ORDER BY order_date DESC");
$ord_stmt->bind_param("is", $current_user_id, $status_filter);
$ord_stmt->execute();
$ord_result = $ord_stmt->get_result();
while ($row = $ord_result->fetch_assoc()) {
    $orders[] = $row;
}
$ord_stmt->close();

// Lấy order_ids để lấy order_items
$order_ids = array_column($orders, 'id');
$order_items = [];
$products = [];
$product_ids = [];
if (!empty($order_ids)) {
    // Prepared statement với IN() động
    $ph = implode(',', array_fill(0, count($order_ids), '?'));
    $types = str_repeat('i', count($order_ids));
    $oi_stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id IN ($ph)");
    $oi_stmt->bind_param($types, ...array_values($order_ids));
    $oi_stmt->execute();
    $oi_result = $oi_stmt->get_result();
    while ($row = $oi_result->fetch_assoc()) {
        $order_items[$row['order_id']][] = $row;
        $product_ids[] = $row['product_id'];
    }
    $oi_stmt->close();

    if (!empty($product_ids)) {
        $unique_pids = array_values(array_unique($product_ids));
        $ph2 = implode(',', array_fill(0, count($unique_pids), '?'));
        $types2 = str_repeat('i', count($unique_pids));
        $pr_stmt = $conn->prepare("SELECT * FROM products WHERE id IN ($ph2)");
        $pr_stmt->bind_param($types2, ...$unique_pids);
        $pr_stmt->execute();
        $pr_result = $pr_stmt->get_result();
        while ($row = $pr_result->fetch_assoc()) {
            $products[$row['id']] = $row;
        }
        $pr_stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <style>
        .orderhistory-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 32px 0 48px 0;
            min-height: 200px;
        }

        .orderhistory-menu {
            display: flex;
            justify-content: center;
            gap: 32px;
            margin-bottom: 32px;
        }

        .orderhistory-menu a {
            padding: 10px 32px;
            /* border-radius: 6px 6px 0 0; */
            color: #222;
            font-weight: 500;
            text-decoration: none;
            font-size: 1.1rem;
            /* border: 1px solid #f0e0de; */
            border-bottom: none;
            background: none;
            transition: color 0.15s;
        }

        .orderhistory-menu a.active {
            color: #222;
            border-bottom: 2.5px solid #d17c7c;
            border-radius: 0;
            font-weight: 500;
            background: none;
        }

        .order-list {
            display: flex;
            flex-direction: column;
            gap: 28px;
        }

        .order-item {
            background: #fff;
            border-radius: 10px;
            border: 1px solid #f0e0de;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
            margin-bottom: 0;
            padding: 18px 24px;
        }

        .order-head {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 12px;
        }

        .order-id {
            font-weight: bold;
            font-size: 1.08rem;
        }

        .order-date {
            color: #888;
            font-size: 0.98rem;
        }

        .order-status {
            margin-left: auto;
            font-weight: 600;
        }

        .order-products {
            border-top: 1px solid #eee;
            padding-top: 12px;
            margin-top: 8px;
        }

        .product-row {
            display: flex;
            align-items: center;
            gap: 18px;
            margin-bottom: 10px;
        }

        .product-img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            background: #f5f5f5;
            border: 1px solid #eee;
        }

        .product-info {
            flex: 1;
        }

        .product-name {
            font-weight: 500;
            font-size: 1rem;
        }

        .product-qty {
            color: #888;
            font-size: 0.97rem;
        }

        .product-price {
            color: #d17c7c;
            font-weight: bold;
            font-size: 1rem;
            margin-left: 12px;
        }

        .product-feedback {
            margin-left: 18px;
        }

        .feedback-btn {
            background: #d17c7c;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 5px 14px;
            font-size: 0.98rem;
            cursor: pointer;
            transition: opacity 0.15s;
            text-decoration: none;
            display: inline-block;
        }

        .feedback-btn:hover {
            opacity: 0.85;
        }
    </style>
</head>

<body>
    <?php include '../../includes/header.php'; ?>
    <div class="orderhistory-container">
        <div class="orderhistory-menu">
            <a href="?status=Pending" class="<?php if ($status_filter == 'Pending')
                echo 'active'; ?>">Pending</a>
            <a href="?status=Paid" class="<?php if ($status_filter == 'Paid')
                echo 'active'; ?>">Paid</a>
            <a href="?status=Processing" class="<?php if ($status_filter == 'Processing')
                echo 'active'; ?>">Processing</a>
            <a href="?status=Ready for Delivery" class="<?php if ($status_filter == 'Ready for Delivery')
                echo 'active'; ?>">Ready</a>
            <a href="?status=Delivering" class="<?php if ($status_filter == 'Delivering')
                echo 'active'; ?>">Delivering</a>
            <a href="?status=Completed" class="<?php if ($status_filter == 'Completed')
                echo 'active'; ?>">Completed</a>
            <a href="?status=Cancelled" class="<?php if ($status_filter == 'Cancelled')
                echo 'active'; ?>">Cancelled</a>
        </div>
        <div class="order-list">
            <?php if (empty($orders)): ?>
                <div style="text-align:center;color:#888;font-size:1.1rem;">No orders found.</div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-item">
                        <div class="order-head">
                            <span class="order-id">Order #<?php echo htmlspecialchars($order['id']); ?></span>
                            <span class="order-date"><?php echo date('d/m/Y', strtotime($order['order_date'])); ?></span>
                            <span class="order-status" style="
                        <?php
                        $color = "#888";
                        if ($order['status'] === 'Pending')
                            $color = "#e5b600";
                        if ($order['status'] === 'Paid')
                            $color = "#2ecc40";
                        if ($order['status'] === 'Processing')
                            $color = "#1e90ff";
                        if ($order['status'] === 'Ready for Delivery')
                            $color = "#8e44ad";
                        if ($order['status'] === 'Delivering')
                            $color = "#f39c12";
                        if ($order['status'] === 'Completed')
                            $color = "#27ae60";
                        if ($order['status'] === 'Cancelled')
                            $color = "#d17c7c";
                        ?>
                        color:<?php echo $color; ?>;
                    ">
                                <?php echo htmlspecialchars($order['status']); ?>
                            </span>
                        </div>

                        <div class="order-products">
                            <?php
                            $items = $order_items[$order['id']] ?? [];
                            foreach ($items as $item):
                                $product = $products[$item['product_id']] ?? null;
                                if (!$product)
                                    continue;
                                ?>
                                <div class="product-row">
                                    <?php if (strtolower($order['status']) === 'pending'): ?>
                                        <!-- Nút Pay Now cho đơn Pending (chưa thanh toán) -->
                                        <a href="pay.php?repay=<?php echo $order['id']; ?>" class="feedback-btn"
                                            style="margin-left:12px; background:#ae5f5f; padding:6px 14px; font-size:.9rem; text-decoration:none; color:#fff; border-radius:6px; display:inline-block;">
                                            Pay Now
                                        </a>
                                        <form method="post" action="" style="display:inline;">
                                            <input type="hidden" name="cancel_order_id" value="<?php echo $order['id']; ?>">
                                            <button type="submit"
                                                style="margin-left:8px; background:#fff; color:#c0392b; border:1.5px solid #c0392b; border-radius:6px; padding:6px 14px; font-size:.9rem; cursor:pointer;"
                                                onclick="return confirm('Bạn có chắc muốn hủy đơn hàng #<?php echo $order['id']; ?>?');">
                                                ✕ Cancel
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <img class="product-img"
                                        src="../../assets/img/<?php echo htmlspecialchars($product['image']); ?>"
                                        alt="<?php echo htmlspecialchars($product['name']); ?>">
                                    <div class="product-info">
                                        <div class="product-name"><?php echo htmlspecialchars($product['name']); ?></div>
                                        <div class="product-qty">x<?php echo $item['quantity']; ?></div>
                                    </div>
                                    <div class="product-price"><?php echo number_format($item['price']); ?> VND</div>
                                    <?php if (strtolower($order['status']) === 'delivering'): ?>
                                        <form method="post" action="" style="display:inline;">
                                            <input type="hidden" name="confirm_delivered_id" value="<?php echo $order['id']; ?>">
                                            <button type="submit" class="feedback-btn"
                                                style="margin-left:12px; background:none; width: 50px;"
                                                onclick="return confirm('Are you sure you want to confirm the order is delivered?');"><img
                                                    src="/assets/img/check.png" width=100%></button>
                                        </form>
                                    <?php endif; ?>
                                    <div class="product-feedback">
                                        <?php if (strtolower($order['status']) === 'completed'): ?>
                                            <a class="feedback-btn"
                                                href="review.php?order_id=<?php echo $order['id']; ?>&product_id=<?php echo $product['id']; ?>">Feedback</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
<?php include '../../includes/footer.php'; ?>

</html>