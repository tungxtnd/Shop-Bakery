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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manage Reviews</title>
    <style>
    body {
        font-family: 'Segoe UI', Arial, sans-serif;
        background: #fff;
        margin: 0;
    }
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
    .breadcrumbs {
        margin: 24px 0 10px 0;
        font-size: 1.08rem;
        color: #888;
    }
    .breadcrumbs a { color: #7A5230; text-decoration: none; }
    .review-stats {
        margin-bottom: 18px;
        font-size: 1.08rem;
    }
    .review-stats span { color: #7A5230; font-weight: 500; }
    .review-filter {
        background: #faf6f8;
        border-radius: 8px;
        padding: 14px 18px;
        margin-bottom: 18px;
        display: flex;
        flex-wrap: wrap;
        gap: 14px;
        align-items: center;
    }
    .review-filter input, .review-filter select {
        padding: 7px 12px;
        border-radius: 5px;
        border: 1px solid #ddd;
        font-size: 1rem;
        min-width: 170px;
    }
    .review-filter button {
        background: #7A5230;
        color: #fff;
        padding: 7px 18px;
        border-radius: 5px;
        border: none;
        font-size: 1rem;
        cursor: pointer;
        transition: opacity 0.15s;
    }
    .review-filter button:hover { opacity: 0.85; }
    .review-table {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 8px #eee;
    }
    .review-table th, .review-table td {
        padding: 12px 14px;
        border-bottom: 1px solid #f0e0de;
        text-align: left;
        font-size: 1.05rem;
        vertical-align: top;
    }
    .review-table th {
        background: #f8eaea;
        color: #7A5230;
        font-weight: 600;
        text-align: left;
    }
    .review-table tr:last-child td { border-bottom: none; }
    .review-table td {
        background: #fff;
    }
    .review-table tr:nth-child(even) { background: #faf6f8; }
    .review-table tr:hover td { background: #f5eaea; transition: background 0.2s; }
    .review-actions button {
        border: none;
        background: #f8eaea;
        cursor: pointer;
        color: #7A5230;
        font-size: 1.15rem;
        padding: 5px 10px;
        border-radius: 5px;
        margin-right: 4px;
        transition: background 0.1s;
    }
    .review-actions button:hover {
        background: #f2d6d6;
    }
    .bulk-actions {
        margin: 16px 0 0 0;
        display: flex;
        gap: 10px;
    }
    .bulk-actions button {
        background: #7A5230;
        color: #fff;
        border: none;
        border-radius: 5px;
        padding: 6px 18px;
        font-size: 1rem;
        cursor: pointer;
        transition: opacity 0.15s;
    }
    .bulk-actions button:hover { opacity: 0.85; }
    .reply-content {
        margin-top: 10px;
        color: #219653;
        font-size: 1.05em;
        font-weight: 500;
        background: #f3fff3;
        border-left: 4px solid #2ecc40;
        border-radius: 5px;
        padding: 8px 12px;
        display: block;
        max-width: 420px;
        white-space: pre-line;
        transition: background 0.2s;
        animation: fadeIn 0.5s;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px);}
        to { opacity: 1; transform: translateY(0);}
    }
    .reply-content .reply-date {
        color: #888;
        font-size: 0.97em;
        font-weight: 400;
        margin-left: 8px;
    }
    .reply-box {
        margin-top: 8px;
        margin-left: 70%;
        background: #f8f8f8;
        border-radius: 6px;
        padding: 10px 12px;
        border: 1px solid #eee;
        max-width: 350px;
        box-shadow: 0 2px 8px #e0f7e0;
        animation: fadeIn 0.3s;
    }
    .pagination {
        margin: 30px 0 30px 0;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
        font-size: 1.08rem;
    }
    .pagination a, .pagination span {
        min-width: 32px;
        min-height: 32px;
        line-height: 32px;
        padding: 0;
        border-radius: 6px;
        border: 1.5px solid #e2bcbc;
        background: #fff;
        color: #7A5230;
        text-decoration: none;
        font-weight: 500;
        text-align: center;
        display: inline-block;
        box-sizing: border-box;
        font-size: 1rem;
        transition: background 0.15s, color 0.15s;
    }
    .pagination a:hover {
        background: #f8eaea;
        color: #7A5230;
    }
    .pagination .active {
        background: #7A5230;
        color: #fff;
        font-weight: bold;
        border-color: #d17c7c;
        pointer-events: none;
    }
    .pagination .dots {
        background: none;
        border: none;
        color: #bbb;
        padding: 0 6px;
        min-width: unset;
        min-height: unset;
        line-height: 32px;
        font-size: 1.1em;
    }
    .toast {
        position: fixed;
        left: 50%;
        bottom: 40px;
        transform: translateX(-50%);
        background: #2ecc40;
        color: #fff;
        padding: 14px 32px;
        border-radius: 8px;
        font-size: 1.1rem;
        box-shadow: 0 2px 12px #aaa;
        z-index: 9999;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.4s;
    }
    .toast.show { opacity: 1; pointer-events: auto; }
    @media (max-width: 900px) {
        .review-table, .review-table th, .review-table td { font-size: 0.97rem; }
        .review-table, .review-table th, .review-table td { display: block; width: 100%; }
        .review-table th, .review-table td { box-sizing: border-box; }
        .review-table tr { margin-bottom: 18px; border-radius: 10px; box-shadow: 0 2px 8px #eee; }
    }
