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

// Handle quantity update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quantities'])) {
    foreach ($_POST['quantities'] as $cart_id => $qty) {
        $cart_id = intval($cart_id);
        $qty = max(1, intval($qty));
        $stmt = $conn->prepare("UPDATE cart_items SET quantity = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("iii", $qty, $cart_id, $user_id);
        $stmt->execute();
    }
    // Refresh to update the cart view and prevent resubmission
    header("Location: cart.php");
    exit;
}


// Fetch cart items with product and card info
$sql = "
