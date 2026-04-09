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
            }
        .admin-link-card a {
            color: #e75480;
            text-decoration: none;
            font-size: 20px;
            font-weight: bold;
        }
        .kpi-row {
            display: flex;
            gap: 30px;
            margin-bottom: 40px;
            margin-top: 30px;
        }
        .kpi-card {
            flex: 1;
            background: #faf6f8;
            border-radius: 8px;
            padding: 24px;
            text-align: center;
            box-shadow: 0 2px 8px #eee;
            font-size: 18px;
        }
        .kpi-title {
            color: #888;
            font-size: 15px;
            margin-bottom: 8px;
        }
        .kpi-value {
            color: #C26E3D;
            font-size: 28px;
            font-weight: bold;
        }
        .kpi-alert {
            color: #D32F2F;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <nav class="admin-navbar">
        <a href="dashboard.php" class="active">Dashboard</a>
        <a href="mana_orders.php">Manage Orders</a>
        <a href="mana_products.php">Manage Products</a>
        <a href="mana_reviews.php">Manage Reviews</a>
        <a href="mana_users.php">Manage Users</a>
        <a href="mana_noti.php">Manage Notifications</a>
        <a href="/views/auth/logout.php" style="margin-left:auto;" onclick="return confirm('Are you sure you want to logout?');">Logout</a>
    </nav>
    <div class="dashboard-container">
        <h1>Admin Dashboard</h1>
        <div class="kpi-row">
            <div class="kpi-card">
                <div class="kpi-title">Total Revenue</div>
                <div class="kpi-value"><?php echo number_format($total_revenue); ?> VND</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Number of Products</div>
                <div class="kpi-value"><?php echo $total_products; ?></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Total Orders</div>
                <div class="kpi-value"><?php echo $total_orders; ?></div>
            </div>
            <div class="kpi-card">
                <div class="kpi-title">Low-stock Alerts</div>
                <div class="kpi-value kpi-alert" id="lowStockToggle" style="cursor:pointer;">
                    <?php echo $low_stock; ?>
                </div>
                <?php if ($low_stock > 0): ?>
                    <div id="lowStockList" style="display:none; margin-top:12px; font-size:14px; color:#b97a56; text-align:left;">
                        <b>Products low in stock:</b>
                        <ul style="margin:8px 0 0 18px; padding:0;">
                            <?php foreach ($low_stock_products as $prod): ?>
                                <li>
                                    <?php echo htmlspecialchars($prod['name']); ?>
                                    (Stock: <?php echo $prod['stock']; ?>)
                                    </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <script>
                        document.getElementById('lowStockToggle').onclick = function() {
                            var list = document.getElementById('lowStockList');
                            list.style.display = (list.style.display === 'none') ? 'block' : 'none';
                        };
                    </script>
                <?php endif; ?>
            </div>
        </div>
            <div class="charts-row">
                <div class="chart-card">
                    <h3>Sales Report
                        <select id="salesFilter" style="margin-left:10px; padding:4px 8px; font-size:15px;">
                            <option value="daily" selected>Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </h3>
                    <canvas id="salesReportChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Best-selling Products</h3>
                    <canvas id="bestProductChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Order Status Distribution</h3>
                    <canvas id="orderStatusChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Average Rating per Product</h3>
                    <canvas id="avgRatingChart"></canvas>
                </div>
            </div>
    </div>
    <script>
    // Prepare sales data for all periods
    const salesData = {
        daily: {
            labels: <?php echo json_encode($sales_days); ?>,
            data: <?php echo json_encode($sales_day_data); ?>,
            label: 'Sales (VND) - Daily'
        },
        weekly: {
            labels: <?php echo json_encode($sales_weeks); ?>,
            data: <?php echo json_encode($sales_week_data); ?>,
            label: 'Sales (VND) - Weekly'
        },
        monthly: {
            labels: <?php echo json_encode($months); ?>,
            data: <?php echo json_encode($sales_data); ?>,
            label: 'Sales (VND) - Monthly'
        }
    };


    const ctx = document.getElementById('salesReportChart').getContext('2d');
    let salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: salesData.daily.labels,
            datasets: [{
                label: salesData.daily.label,
                data: salesData.daily.data,
                backgroundColor: [
                    '#C9E9D2', '#789DBC', '#FFE3E3', '#FEF9F2', '#bdbdbd'
                ],
                borderColor: '#e75480',
                fill: true
            }]
        },
        options: {
            scales: { y: { beginAtZero: true } }
        }
    });

