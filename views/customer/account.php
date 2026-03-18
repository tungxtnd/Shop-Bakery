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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Account</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { background: #f8f8f8; font-family: 'Segoe UI', Arial, sans-serif; margin: 0; padding: 0; }
        .breadcrumbs {
            margin: 24px 0 10px 0;
            font-size: 1.08rem;
            color: #888;
            text-align: left;
        }
        .breadcrumbs a { color: #e75480; text-decoration: none; }
        .container {
            max-width: 600px;
            margin: 40px auto 0 auto;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 24px #eee;
            padding: 38px 38px 28px 38px;
        }
        h2 {
            color: #e75480;
            margin-bottom: 22px;
            text-align: center;
            letter-spacing: 0.5px;
        }
        /* Vertical Navbar */
        ul.navbar {
            list-style-type: none;
            margin: 0;
            padding: 0;
            width: 200px;
            background-color: #fff;
            position: fixed;
            height: 100%;
            overflow: auto;
            left: 0;
            z-index: 10;
        }
        ul.navbar li a {
            display: block;
            color: #000;
            padding: 10px 18px;
            text-decoration: none;
            font-size: 16px;
            width: 100%;
        }
       
        ul.navbar li a.visited {
            background-color: #e75480;
            color: black;
        }
        ul.navbar li a:hover:not(.active) {
            background-color: #f8eaea;
            color: black;
        }
        ul.navbar .submenu {
            display: block !important;
            list-style-type: none;
            padding-left: 18px;
            background: none;
            margin: 0;
        }
        ul.navbar .submenu.show {
            display: block;
        }
        ul.navbar .submenu li a {
            font-size: 15px;
            padding-left: 32px;
            background: none;
        }
        .profile-info {
            display: flex;
            gap: 32px;
            align-items: flex-start;
            flex-wrap: wrap;
        }
        .avatar-box {
            flex: 0 0 110px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .avatar-box img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 2.5px solid #e75480;
            background: #faf6f8;
        }
        .avatar-box .username {
            margin-top: 12px;
            color: #e75480;
            font-weight: 600;
            font-size: 1.13em;
        }
        .info-table {
            flex: 1;
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        .info-table th, .info-table td {
            text-align: left;
            padding: 10px 0;
            font-size: 1.04em;
        }
        .info-table th {
            color: #e75480;
            width: 120px;
            font-weight: 500;
        }
        .info-table tr:not(:last-child) td, .info-table tr:not(:last-child) th {
            border-bottom: 1px solid #f2dbe3;
        }
        .profile-actions {
            margin-top: 36px;
            display: flex;
            gap: 24px;
            justify-content: flex-start;
        }
        .btn {
            background: #e75480;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            font-size: 1.08rem;
            padding: 13px 38px;
            transition: background 0.15s;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
        }
        .btn:hover { background: #d84372; }
        .btn-secondary {
            background: #faf6f8;
            color: #e75480;
            border: 1.5px solid #e75480;
        }
        .btn-secondary:hover { background: #f8eaea; }
        .form-section { margin-top: 38px; }
        .form-title { color: #e75480; font-size: 1.08em; margin-bottom: 10px; }
        .form-group { margin-bottom: 18px; }
        label { display: block; margin-bottom: 6px; color: #e75480; font-weight: 500; }
        input[type="text"], input[type="password"], textarea {
            width: 100%; padding: 10px 14px; border-radius: 7px; border: 1.5px solid #ddd; font-size: 1rem;
            background: #fafafa; transition: border 0.2s;
            box-sizing: border-box;
        }
        input[type="text"]:focus, input[type="password"]:focus, textarea:focus {
            border-color: #e75480;
            outline: none;
        }
        .error-list, .success-msg {
            border-radius: 7px; padding: 12px 20px; margin-bottom: 18px;
        }
        .error-list { color: #c0392b; background: #fff0f0; }
        .success-msg { color: #219653; background: #f3fff3; }
        .form-section { display: none; }
        .form-section.active { display: block; }
        @media (max-width: 700px) {
            .container { padding: 18px 4vw; }
            .profile-info { flex-direction: column; gap: 16px; align-items: stretch; }
            .avatar-box { align-items: flex-start; }
            ul.navbar { width: 100px; }
        }
        @media (max-width: 480px) {
            .container { padding: 8px 2vw; }
            .btn, .btn-secondary { width: 100%; padding: 13px 0; }
            .profile-actions { flex-direction: column; gap: 12px; }
            ul.navbar { width: 100%; position: static; height: auto; }
        }
