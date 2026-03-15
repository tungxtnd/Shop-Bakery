<?php
session_start();
include '../../connectdb.php';


if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $type = 'logout';
    $message = 'You have logged out.';
    $created_at = date('Y-m-d H:i:s');
    $noti_stmt = $conn->prepare("INSERT INTO notifications (user_id, target_user_id, type, message, created_at) VALUES (?, ?, ?, ?, ?)");
    $noti_stmt->bind_param("iisss", $user_id, $user_id, $type, $message, $created_at);
    $noti_stmt->execute();
    $noti_stmt->close();
}
session_unset();
session_destroy();
header("Location:/homepage.php");
exit;
