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

