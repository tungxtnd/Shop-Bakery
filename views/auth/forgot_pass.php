<?php
session_start();
include '../../connectdb.php';


$email = '';
$success = '';
$error = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $new_pass = $_POST['new_password'] ?? '';
    $confirm_pass = $_POST['confirm_password'] ?? '';


    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } elseif (strlen($new_pass) < 6) {
        $error = "Password must be at least 6 characters.";
    } elseif ($new_pass !== $confirm_pass) {
        $error = "Passwords do not match.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) {
            $error = "No account found with that email.";
        } else {
            $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $update->bind_param('ss', $new_pass, $email);
            if ($update->execute()) {
                $success = "Password changed successfully. You can now <a href='login.php'>login</a>.";
            } else {
                $error = "Failed to update password. Please try again.";
            }
            $update->close();
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Forgot Password</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8f8f8; }
        .forgot-container {
            max-width: 400px; margin: 60px auto; background: #fff; border-radius: 8px;
            box-shadow: 0 2px 12px #eee; padding: 32px;
        }
        .forgot-container h2 { color: #cb5d00; margin-bottom: 24px; }
        .forgot-container input[type="email"],
        .forgot-container input[type="password"] {
            width: 100%; padding: 10px; margin-bottom: 18px; border: 1px solid #ccc; border-radius: 4px;
        }
        .forgot-container button {
            background: #cb5d00; color: #fff; border: none; padding: 10px 24px; border-radius: 4px;
            font-size: 1em; cursor: pointer; transition: background 0.2s;
        }
        .forgot-container button:hover { background: #840000; }
        .msg { margin-bottom: 18px; color: #219653; }
        .err { margin-bottom: 18px; color: #840000; }
    </style>
</head>
<body>
    <?php include '../../includes/header.php'; ?>
    <div class="forgot-container">
        <h2>Reset Password</h2>
        <?php if ($success): ?>
            <div class="msg"><?php echo $success; ?></div>
        <?php elseif ($error): ?>
            <div class="err"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" autocomplete="off">
            <label for="email">Your email address:</label>
            <input type="email" name="email" id="email" required value="<?php echo htmlspecialchars($email); ?>">
            <label for="new_password">New password:</label>
            <input type="password" name="new_password" id="new_password" required>
            <label for="confirm_password">Confirm new password:</label>
            <input type="password" name="confirm_password" id="confirm_password" required>
            <button type="submit">Change Password</button>
        </form>
        <div style="margin-top:18px;">
            <a href="login.php" style="color:#888;text-decoration:none;">Back to Login</a>
        </div>
    </div>
    <?php include '../../includes/footer.php'; ?>
</body>
</html>
