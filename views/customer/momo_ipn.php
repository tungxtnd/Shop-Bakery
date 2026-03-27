<?php
header("content-type: application/json; charset=UTF-8");
http_response_code(200);


include '../../connectdb.php'; // Kết nối Database


if (!empty($_POST)) {
    $secretKey = 'at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa'; // Thay bằng SecretKey của bạn
   
    $partnerCode = $_POST["partnerCode"];
    $orderId = $_POST["orderId"]; // Ví dụ: 54_169080923
    $requestId = $_POST["requestId"];
    $amount = $_POST["amount"];    
    $orderInfo = $_POST["orderInfo"];
    $orderType = $_POST["orderType"];
    $transId = $_POST["transId"];
    $resultCode = $_POST["resultCode"];
    $message = $_POST["message"];
    $payType = $_POST["payType"];
    $responseTime = $_POST["responseTime"];
    $extraData = $_POST["extraData"];
    $m2signature = $_POST["signature"];
   
    // Tái tạo chữ ký để đối chiếu
    $rawHash = "accessKey=klm05TvNBzhg7h7j&amount=" . $amount . "&extraData=" . $extraData . "&message=" . $message . "&orderId=" . $orderId . "&orderInfo=" . $orderInfo . "&orderType=" . $orderType . "&partnerCode=" . $partnerCode . "&payType=" . $payType . "&requestId=" . $requestId . "&responseTime=" . $responseTime . "&resultCode=" . $resultCode . "&transId=" . $transId;
    $partnerSignature = hash_hmac("sha256", $rawHash, $secretKey);


    if ($m2signature == $partnerSignature) {
        if ($resultCode == '0') {
            // Thanh toán thành công!
            // Cắt chuỗi lấy ID đơn hàng gốc (ví dụ: 54_169080923 -> 54)
            $real_order_id = explode('_', $orderId)[0];
           
            // Cập nhật trạng thái đơn hàng trong Database
            $stmt = $conn->prepare("UPDATE orders SET status = 'Paid' WHERE id = ?");
            $stmt->bind_param("i", $real_order_id);
            $stmt->execute();
        }
    }
   
    // Luôn trả về HTTP 200 để MoMo biết server bạn đã nhận được tín hiệu
    echo json_encode(['message' => 'Received payment result']);
}
?>
