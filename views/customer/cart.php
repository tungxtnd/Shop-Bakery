<?php
// filepath: d:\Xampp\htdocs\flower_shop\views\customer\cart.php
session_start();
include '../../connectdb.php';


if (!isset($_SESSION['user_id'])) {
    echo "<script>alert('You need to log in first!'); window.location='../../login.php';</script>";
    exit;
}
$user_id = $_SESSION['user_id'];


// Handle remove action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove'])) {
    $cart_id = intval($_POST['remove']);
    $stmt = $conn->prepare("DELETE FROM cart_items WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();
    // Refresh to update the cart view
    header("Location: cart.php");
    exit;
}
