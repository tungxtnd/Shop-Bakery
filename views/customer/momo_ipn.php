<?php
header("content-type: application/json; charset=UTF-8");
http_response_code(200);

include '../../connectdb.php';

if (!empty($_POST)) {
    $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa';

    $partnerCode  = $_POST["partnerCode"];
    $orderId      = $_POST["orderId"]; // e.g. 54_169080923
    $requestId    = $_POST["requestId"];
    $amount       = $_POST["amount"];
    $orderInfo    = $_POST["orderInfo"];
    $orderType    = $_POST["orderType"];
    $transId      = $_POST["transId"];
    $resultCode   = $_POST["resultCode"];
    $message      = $_POST["message"];
    $payType      = $_POST["payType"];
    $responseTime = $_POST["responseTime"];
    $extraData    = $_POST["extraData"];
    $m2signature  = $_POST["signature"];

    // Tái tạo chữ ký để đối chiếu
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

    if ($m2signature == $partnerSignature) {
        if ($resultCode == '0') {
            // Thanh toán thành công!
            $real_order_id = (int) explode('_', $orderId)[0];

            // 1. Cập nhật trạng thái đơn hàng → Paid
            $stmt = $conn->prepare("UPDATE orders SET status = 'Paid' WHERE id = ?");
            $stmt->bind_param("i", $real_order_id);
            $stmt->execute();
            $stmt->close();

            // 2. Trừ kho: chỉ thực hiện tại đây với đơn MoMo (đơn COD đã trừ khi tạo đơn)
            $items_stmt = $conn->prepare(
                "SELECT product_id, quantity FROM order_items WHERE order_id = ?"
            );
            $items_stmt->bind_param("i", $real_order_id);
            $items_stmt->execute();
            $items_result = $items_stmt->get_result();
            while ($item = $items_result->fetch_assoc()) {
                $upd = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $upd->bind_param("ii", $item['quantity'], $item['product_id']);
                $upd->execute();
                $upd->close();
            }
            $items_stmt->close();
        }
    }

    // Luôn trả về HTTP 200 để MoMo biết server đã nhận tín hiệu
    echo json_encode(['message' => 'Received payment result']);
}
?>