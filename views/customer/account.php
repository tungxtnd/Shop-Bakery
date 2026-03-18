<?php
session_start();
include '../../connectdb.php';


// Check customer login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'customer') {
    header("Location: ../../homepage.php");
    exit;
}
$user_id = $_SESSION['user_id'];


// Fetch customer info
$stmt = $conn->prepare("SELECT id, full_name, email, phone, address FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) {
    echo "User not found.";
    exit;
}


$edit_success = false;
$edit_errors = [];
$pass_success = false;
$pass_errors = [];


// Handle profile update
if (isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);


    if ($full_name === '') $edit_errors[] = "Full name cannot be empty.";


    if (!$edit_errors) {
        $stmt = $conn->prepare("UPDATE users SET full_name=?, phone=?, address=? WHERE id=?");
        $stmt->bind_param('sssi', $full_name, $phone, $address, $user_id);
        if ($stmt->execute()) {
            $edit_success = true;
            // Refresh user info
            $stmt = $conn->prepare("SELECT id, full_name, email, phone, address FROM users WHERE id = ?");
            $stmt->bind_param('i', $user_id);
            $stmt->execute();
            $user = $stmt->get_result()->fetch_assoc();
        } else {
            $edit_errors[] = "Update failed. Please try again.";
        }
    }
}


// Handle password change
if (isset($_POST['change_password'])) {
    $old_pass = $_POST['old_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';


    // Fetch current password hash
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $current_hash = $row['password'];


    if (!password_verify($old_pass, $current_hash)) {
        $pass_errors[] = "Old password is incorrect.";
    }
    if (strlen($new_pass) < 6) {
        $pass_errors[] = "New password must be at least 6 characters.";
    }
    if ($new_pass !== $confirm_pass) {
        $pass_errors[] = "Password confirmation does not match.";
    }


    if (!$pass_errors) {
        $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param('si', $new_hash, $user_id);
        if ($stmt->execute()) {
            $pass_success = true;
        } else {
            $pass_errors[] = "Password change failed. Please try again.";
        }
    }
}
$show_change_pass = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $show_change_pass = true;
}
?>
