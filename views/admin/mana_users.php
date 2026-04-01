<?php


session_start();
include '../../connectdb.php';


// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../homepage.php");
    exit;
}


// Handle delete (only non-admin)
if (isset($_POST['delete_id'])) {
    $del_id = intval($_POST['delete_id']);
    // Check role before deleting
    $check = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $check->bind_param("i", $del_id);
    $check->execute();
    $check->bind_result($role);
    $check->fetch();
    $check->close();
    if ($role !== 'admin') {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $del_id);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: mana_users.php");
    exit;
}


// Handle edit (only non-admin)
$edit_success = false;
$edit_errors = [];
if (isset($_POST['edit_id']) && isset($_POST['full_name']) && isset($_POST['email']) && isset($_POST['phone']) && isset($_POST['address'])) {
    $edit_id = intval($_POST['edit_id']);
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    // Check role before editing
    $check = $conn->prepare("SELECT role FROM users WHERE id = ?");
    $check->bind_param("i", $edit_id);
    $check->execute();
    $check->bind_result($role);
    $check->fetch();
    $check->close();
    if ($role !== 'admin') {
        $stmt = $conn->prepare("UPDATE users SET full_name=?, email=?, phone=?, address=? WHERE id=?");
        $stmt->bind_param("ssssi", $full_name, $email, $phone, $address, $edit_id);
        if ($stmt->execute()) {
            $edit_success = true;
        } else {
            $edit_errors[] = "Update failed.";
        }
        $stmt->close();
    } else {
        $edit_errors[] = "Cannot edit admin user.";
    }
}


// Fetch all users
$result = $conn->query("SELECT id, full_name, email, phone, address, role FROM users ORDER BY role DESC, id ASC");
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <style>
        .admin-navbar {
            background: #5C3A21;
            padding: 0;
            margin: 0;
            display: flex;
            align-items: center;
            height: 60px;
        }
        .admin-navbar a {
            color: #fff;
            text-decoration: none;
            padding: 0 32px;
            font-size: 18px;
            line-height: 60px;
            display: block;
            transition: background 0.2s;
        }
        .admin-navbar a:hover, .admin-navbar a.active {
            background: #7A5230;
        }
        body { background: #f8f8f8; font-family: 'Segoe UI', Arial, sans-serif; margin: 0; padding: 0; }
        .container { max-width: 900px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 12px #eee; padding: 32px; }
        h2 { color: #7A5230; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        th, td { padding: 10px 8px; border-bottom: 1px solid #f0f0f0; text-align: left; }
        th { background: #f8f8f8; color: #7A5230; }
        .admin-row { background: #f8eaea; color: #7A5230; }
        .action-btn {
            background: #2196F3; color: #fff; border: none; border-radius: 4px; padding: 5px 12px; cursor: pointer;
            text-decoration: none;
        }
        .action-btn:hover { background: #7A5230; }
        .edit-form input[type="text"], .edit-form input[type="email"] {
            width: 100%; padding: 4px 8px; border-radius: 4px; border: 1px solid #ccc;
        }
        .edit-form { display: flex; gap: 8px; }
        .edit-form input, .edit-form button { font-size: 1em; }
        .success-msg { color: #219653; background: #f3fff3; border-radius: 7px; padding: 10px 16px; margin-bottom: 12px; }
        .error-list { color: #c0392b; background: #fff0f0; border-radius: 7px; padding: 10px 16px; margin-bottom: 12px; }
    </style>
</head>
<body>
    <nav class="admin-navbar">
        <a href="dashboard.php">Dashboard</a>
        <a href="mana_orders.php">Manage Orders</a>
        <a href="mana_products.php">Manage Products</a>
        <a href="mana_reviews.php">Manage Reviews</a>
        <a href="mana_users.php" class="active">Manage Users</a>
        <a href="mana_noti.php">Manage Notifications</a>
        <a href="/views/auth/logout.php" style="margin-left:auto;" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
    </nav>
    <div class="container">
        <h2>Manage Users</h2>
        <?php if ($edit_success): ?>
            <div class="success-msg">User updated successfully!</div>
        <?php endif; ?>
        <?php if ($edit_errors): ?>
            <div class="error-list"><?php foreach ($edit_errors as $e) echo "<div>$e</div>"; ?></div>
        <?php endif; ?>
        <table>
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
            <?php foreach ($users as $user): ?>
            <tr class="<?php if ($user['role'] === 'admin') echo 'admin-row'; ?>">
                <td><?php echo $user['id']; ?></td>
                <td>
                    <?php if (isset($_GET['edit']) && $_GET['edit'] == $user['id'] && $user['role'] !== 'admin'): ?>
                        <form method="post" class="edit-form">
                            <input type="hidden" name="edit_id" value="<?php echo $user['id']; ?>">
                            <input type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                    <?php else: ?>
                        <?php echo htmlspecialchars($user['full_name']); ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (isset($_GET['edit']) && $_GET['edit'] == $user['id'] && $user['role'] !== 'admin'): ?>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    <?php else: ?>
                        <?php echo htmlspecialchars($user['email']); ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (isset($_GET['edit']) && $_GET['edit'] == $user['id'] && $user['role'] !== 'admin'): ?>
                            <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
                    <?php else: ?>
                        <?php echo htmlspecialchars($user['phone']); ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (isset($_GET['edit']) && $_GET['edit'] == $user['id'] && $user['role'] !== 'admin'): ?>
                            <input type="text" name="address" value="<?php echo htmlspecialchars($user['address']); ?>">
                    <?php else: ?>
                        <?php echo htmlspecialchars($user['address']); ?>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($user['role']); ?></td>
                <td>
                    <?php if ($user['role'] !== 'admin'): ?>
                        <?php if (isset($_GET['edit']) && $_GET['edit'] == $user['id']): ?>
                            <button type="submit" class="action-btn">Save</button>
                        </form>
                        <a href="mana_users.php" class="action-btn" style="background:#888;">Cancel</a>
                        <?php else: ?>
                        <a href="mana_users.php?edit=<?php echo $user['id']; ?>" class="action-btn">Edit</a>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="delete_id" value="<?php echo $user['id']; ?>">
                            <button type="submit" class="action-btn" onclick="return confirm('Delete this user?');" style="background:#f44336;">Delete</button>
                        </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <span style="color:#888;">No action</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>