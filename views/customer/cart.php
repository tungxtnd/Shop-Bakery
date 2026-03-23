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
    SELECT ci.id as cart_id, p.id as product_id, p.name as product_name, p.image as product_image, p.price as product_price,
           ci.quantity, s.name as card_name, s.price as card_price, s.image as card_image
    FROM cart_items ci
    JOIN products p ON ci.product_id = p.id
    LEFT JOIN services s ON ci.service_id = s.id
    WHERE ci.user_id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Cart</title>
    <style>
        body { background: #f8f8f8; font-family: Arial, sans-serif; }
        .cart-container { max-width: 900px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 12px #eee; padding: 32px; }
        h2 { color: #e75480; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: center; border-bottom: 1px solid #eee; }
        th { background: #faf6f8; color: #e75480; }
        img { width: 60px; height: 60px; object-fit: cover; border-radius: 6px; }
        .remove-btn {
            background: #e75480; color: #fff; border: none; border-radius: 4px;
            padding: 6px 14px; cursor: pointer; transition: background 0.2s;
        }
        .remove-btn:hover { background: #d84372; }
        .checkout-btn {
            margin-top: 24px; background: #e75480; color: #fff; border: none; border-radius: 5px;
