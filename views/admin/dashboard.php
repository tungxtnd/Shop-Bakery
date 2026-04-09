<?php


session_start();
// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../homepage.php");
    exit;
}
include '../../connectdb.php';


// Sales by day (last 7 days)
$sales_days = [];
$sales_day_data = [];
$day_sql = "
    SELECT DATE(order_date) as day, SUM(total_amount) as total
    FROM orders
    GROUP BY day
    ORDER BY day DESC
    LIMIT 7
";
$day_result = $conn->query($day_sql);
while ($row = $day_result->fetch_assoc()) {
    $sales_days[] = $row['day'];
    $sales_day_data[] = $row['total'];
}
$sales_days = array_reverse($sales_days);
$sales_day_data = array_reverse($sales_day_data);


// Sales by week (last 6 weeks)
$sales_weeks = [];
$sales_week_data = [];
$week_sql = "
    SELECT YEAR(order_date) as y, WEEK(order_date) as w, SUM(total_amount) as total
    FROM orders
    GROUP BY y, w
    ORDER BY y DESC, w DESC
    LIMIT 6
";
$week_result = $conn->query($week_sql);
while ($row = $week_result->fetch_assoc()) {
    $sales_weeks[] = $row['y'] . '-W' . $row['w'];
    $sales_week_data[] = $row['total'];
    }
$sales_weeks = array_reverse($sales_weeks);
$sales_week_data = array_reverse($sales_week_data);


// Sales by month (last 6 months)
$sales_data = [];
$months = [];
$sales_sql = "
    SELECT DATE_FORMAT(order_date, '%Y-%m') as month, SUM(total_amount) as total_sales
    FROM orders
    GROUP BY month
    ORDER BY month DESC
    LIMIT 6
";
$sales_result = $conn->query($sales_sql);
while ($row = $sales_result->fetch_assoc()) {
    $months[] = $row['month'];
    $sales_data[] = $row['total_sales'];
}
$months = array_reverse($months);
$sales_data = array_reverse($sales_data);


// Order status distribution
$status_labels = [];
$status_counts = [];
$status_sql = "
    SELECT status, COUNT(*) as count
    FROM orders
    GROUP BY status
";
$status_result = $conn->query($status_sql);
while ($row = $status_result->fetch_assoc()) {
    $status_labels[] = ucfirst($row['status']);
    $status_counts[] = $row['count'];
}


// Total Revenue
$revenue_sql = "SELECT SUM(total_amount) as revenue FROM orders";
$revenue_result = $conn->query($revenue_sql);
$total_revenue = $revenue_result->fetch_assoc()['revenue'] ?? 0;


// Number of Products
$product_sql = "SELECT COUNT(*) as total_products FROM products";
$product_result = $conn->query($product_sql);
$total_products = $product_result->fetch_assoc()['total_products'] ?? 0;


// Total Orders
$order_sql = "SELECT COUNT(*) as total_orders FROM orders";
$order_result = $conn->query($order_sql);
$total_orders = $order_result->fetch_assoc()['total_orders'] ?? 0;


// Low-stock Alerts (e.g., stock <= 5)
$low_stock_sql = "SELECT id, name, stock FROM products WHERE stock <= 5";
$low_stock_result = $conn->query($low_stock_sql);
$low_stock = $low_stock_result->num_rows;
$low_stock_products = [];
while ($row = $low_stock_result->fetch_assoc()) {
    $low_stock_products[] = $row;
}


// Best-selling products (top 5)
$best_products = [];
$best_qty = [];
$best_sql = "
    SELECT p.name, SUM(oi.quantity) as qty
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    GROUP BY oi.product_id
    ORDER BY qty DESC
    LIMIT 5
";
$best_result = $conn->query($best_sql);
while ($row = $best_result->fetch_assoc()) {
    $best_products[] = $row['name'];
    $best_qty[] = $row['qty'];
}


// Average rating per product (top 5 by rating count)
$rating_products = [];
$rating_avgs = [];
$rating_sql = "
    SELECT p.name, AVG(r.rating) as avg_rating
    FROM reviews r
    JOIN products p ON r.product_id = p.id
    GROUP BY r.product_id
    HAVING COUNT(r.id) >= 1
    ORDER BY avg_rating DESC
    LIMIT 5
";
$rating_result = $conn->query($rating_sql);
while ($row = $rating_result->fetch_assoc()) {
    $rating_products[] = $row['name'];
    $rating_avgs[] = round($row['avg_rating'], 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Bakery Shop</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: #f8f8f8; font-family: Arial, sans-serif; margin: 0; }
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
        .dashboard-container {
            max-width: 1100px;
            margin: 40px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 12px #eee;
            padding: 32px;
        }
        h1 { color: #7A5230; }
        .admin-welcome {
            font-size: 20px;
            color: #444;
            margin-bottom: 30px;
        }
        .charts-row {
            display: flex;
            gap: 40px;
            margin-top: 40px;
            flex-wrap: wrap;
        }
        .chart-card {
            flex: 1;
            min-width: 320px;
            background: #faf6f8;
            border-radius: 8px;
            padding: 24px;
            box-shadow: 0 2px 8px #eee;
            text-align: center;
        }
        .admin-links {
            display: flex;
            gap: 30px;
            margin-top: 30px;
        }
        .admin-link-card {
            flex: 1;
            background: #faf6f8;
            border-radius: 8px;
            padding: 24px;
            text-align: center;
            box-shadow: 0 2px 8px #eee;
            transition: box-shadow 0.2s;
        }
        .admin-link-card:hover {
            box-shadow: 0 4px 16px #e75480;



