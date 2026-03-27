<?php


session_start();
include '../../connectdb.php';


// Handle delete review (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    $stmt = $conn->prepare("DELETE FROM reviews WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    echo 'success';
    exit;
}




// Handle filter/search
$where = [];
$params = [];
$types = '';
if (!empty($_GET['user'])) {
    $where[] = "(u.full_name LIKE ? OR u.email LIKE ?)";
    $params[] = '%' . $_GET['user'] . '%';
    $params[] = '%' . $_GET['user'] . '%';
    $types .= 'ss';
}
if (!empty($_GET['product'])) {
    $where[] = "p.name LIKE ?";
    $params[] = '%' . $_GET['product'] . '%';
    $types .= 's';
}
if (!empty($_GET['date'])) {
    $where[] = "DATE(r.created_at) = ?";
    $params[] = $_GET['date'];
    $types .= 's';
}
if (isset($_GET['rating']) && $_GET['rating'] !== '') {
    $where[] = "r.rating = ?";
    $params[] = intval($_GET['rating']);
    $types .= 'i';
}
$where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';


// Total reviews
$total_reviews = $conn->query("SELECT COUNT(*) as total FROM reviews")->fetch_assoc()['total'];


// Pagination
$limit = 10; // 10 reviews per page
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;


// Get reviews data (with admin_reply and admin_created_at)
$sql = "
    SELECT r.*, u.full_name, u.email, p.name as product_name
    FROM reviews r
    LEFT JOIN users u ON r.user_id = u.id
    LEFT JOIN products p ON r.product_id = p.id
    $where_sql
    ORDER BY r.created_at DESC
    LIMIT ? OFFSET ?
";
if ($params) {
    $types_with_limit = $types . 'ii';
    $params_with_limit = array_merge($params, [$limit, $offset]);
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types_with_limit, ...$params_with_limit);
} else {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $limit, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
$reviews = $result->fetch_all(MYSQLI_ASSOC);


// Get total filtered
$count_sql = "
    SELECT COUNT(*) as total
    FROM reviews r
    LEFT JOIN users u ON r.user_id = u.id
    LEFT JOIN products p ON r.product_id = p.id
    $where_sql
";
if ($params) {
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->bind_param($types, ...$params);
} else {
    $count_stmt = $conn->prepare($count_sql);
}
$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_filtered = $count_result->fetch_assoc()['total'];
$total_pages = max(1, ceil($total_filtered / $limit));
// Handle reply (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_id'], $_POST['reply_content'])) {
    $id = intval($_POST['reply_id']);
    $reply = trim($_POST['reply_content']);
    $now = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("UPDATE reviews SET admin_reply = ?, admin_created_at = ? WHERE id = ?");
    $stmt->bind_param('ssi', $reply, $now, $id);
    $stmt->execute();


    // Get review info for notification
    $review_info = $conn->query("SELECT user_id, product_id FROM reviews WHERE id = $id")->fetch_assoc();
    if ($review_info) {
        $type = 'admin_message';
        $message = 'Admin replied to your review: ' . $reply;
        $created_at = $now;
        $noti_stmt = $conn->prepare("INSERT INTO notifications (user_id, target_user_id, product_id, type, message, created_at) VALUES (?, ?, ?, ?, ?, ?)");
        $noti_stmt->bind_param("iiisss", $review_info['user_id'], $review_info['user_id'], $review_info['product_id'], $type, $message, $created_at);
        $noti_stmt->execute();
        $noti_stmt->close();
    }


    // Return reply and created_at as JSON
    echo json_encode([
        'reply' => htmlspecialchars($reply),
        'created_at' => date('Y-m-d H:i', strtotime($now))
    ]);
    exit;
}
