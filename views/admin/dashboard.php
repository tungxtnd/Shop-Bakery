<?php
session_start();
// Kiểm tra quyền Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../homepage.php");
    exit;
}
include '../../connectdb.php';

// --- 1. LOGIC XỬ LÝ DỮ LIỆU BIỂU ĐỒ (BẮT BUỘC ĐẦY ĐỦ) ---

// Doanh thu theo ngày (7 ngày gần nhất)
$sales_days = []; $sales_day_data = [];
$day_sql = "SELECT DATE(order_date) as day, SUM(total_amount) as total FROM orders GROUP BY day ORDER BY day DESC LIMIT 7";
$day_result = $conn->query($day_sql);
while ($row = $day_result->fetch_assoc()) {
    $sales_days[] = $row['day'];
    $sales_day_data[] = $row['total'];
}
$sales_days = array_reverse($sales_days); $sales_day_data = array_reverse($sales_day_data);

// Doanh thu theo tuần (6 tuần gần nhất)
$sales_weeks = []; $sales_week_data = [];
$week_sql = "SELECT YEAR(order_date) as y, WEEK(order_date) as w, SUM(total_amount) as total FROM orders GROUP BY y, w ORDER BY y DESC, w DESC LIMIT 6";
$week_result = $conn->query($week_sql);
while ($row = $week_result->fetch_assoc()) {
    $sales_weeks[] = $row['y'] . '-W' . $row['w'];
    $sales_week_data[] = $row['total'];
}
$sales_weeks = array_reverse($sales_weeks); $sales_week_data = array_reverse($sales_week_data);

// Doanh thu theo tháng (6 tháng gần nhất)
$sales_data = []; $months = [];
$sales_sql = "SELECT DATE_FORMAT(order_date, '%Y-%m') as month, SUM(total_amount) as total_sales FROM orders GROUP BY month ORDER BY month DESC LIMIT 6";
$sales_result = $conn->query($sales_sql);
while ($row = $sales_result->fetch_assoc()) {
    $months[] = $row['month'];
    $sales_data[] = $row['total_sales'];
}
$months = array_reverse($months); $sales_data = array_reverse($sales_data);

// Trạng thái đơn hàng
$status_labels = []; $status_counts = [];
$status_sql = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
$status_result = $conn->query($status_sql);
while ($row = $status_result->fetch_assoc()) {
    $status_labels[] = ucfirst($row['status']);
    $status_counts[] = $row['count'];
}

// Tổng doanh thu, sản phẩm, đơn hàng
$total_revenue = $conn->query("SELECT SUM(total_amount) as revenue FROM orders")->fetch_assoc()['revenue'] ?? 0;
$total_products = $conn->query("SELECT COUNT(*) as total_products FROM products")->fetch_assoc()['total_products'] ?? 0;
$total_orders = $conn->query("SELECT COUNT(*) as total_orders FROM orders")->fetch_assoc()['total_orders'] ?? 0;

// Cảnh báo tồn kho thấp
$low_stock_sql = "SELECT id, name, stock FROM products WHERE stock <= 5";
$low_stock_result = $conn->query($low_stock_sql);
$low_stock = $low_stock_result->num_rows;

// Top 5 sản phẩm bán chạy
$best_products = []; $best_qty = [];
$best_sql = "SELECT p.name, SUM(oi.quantity) as qty FROM order_items oi JOIN products p ON oi.product_id = p.id GROUP BY oi.product_id ORDER BY qty DESC LIMIT 5";
$best_result = $conn->query($best_sql);
while ($row = $best_result->fetch_assoc()) {
    $best_products[] = $row['name'];
    $best_qty[] = $row['qty'];
}

// Đánh giá trung bình
$rating_products = []; $rating_avgs = [];
$rating_sql = "SELECT p.name, AVG(r.rating) as avg_rating FROM reviews r JOIN products p ON r.product_id = p.id GROUP BY r.product_id HAVING COUNT(r.id) >= 1 ORDER BY avg_rating DESC LIMIT 5";
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
    <title>Admin Dashboard | Bakes Bakery</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f4f6f9; margin: 0; display: flex; height: 100vh; overflow: hidden; }
        .sidebar { width: 260px; background: #343a40; color: #fff; display: flex; flex-direction: column; flex-shrink: 0; }
        .sidebar-brand { padding: 25px 20px; font-size: 1.6em; font-weight: bold; text-align: center; border-bottom: 1px solid #4f5962; }
        .sidebar-brand span { color: #b97a56; }
        .nav-menu { list-style: none; padding: 0; margin: 15px 0; flex: 1; overflow-y: auto; }
        .nav-menu li a { display: block; padding: 12px 20px; color: #c2c7d0; text-decoration: none; transition: 0.3s; }
        .nav-menu li a i { margin-right: 12px; width: 20px; text-align: center; }
        .nav-menu li a:hover, .nav-menu li a.active { background: #494e53; color: #fff; border-left: 4px solid #b97a56; }
        .main-wrapper { flex: 1; display: flex; flex-direction: column; overflow: hidden; }
        .top-header { background: #fff; padding: 15px 30px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; }
        .content { padding: 25px; overflow-y: auto; flex: 1; }
        .kpi-row { display: flex; gap: 20px; margin-bottom: 25px; }
        .kpi-card { flex: 1; background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border-top: 4px solid #b97a56; text-align: center; }
        .kpi-value { color: #333; font-size: 22px; font-weight: bold; margin-top: 5px; }
        .kpi-alert { color: #D32F2F; }
        .charts-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 25px; }
        .chart-card { background: #fff; border-radius: 10px; padding: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); min-height: 350px; }
        .chart-card h3 { margin-top: 0; color: #555; border-bottom: 1px solid #eee; padding-bottom: 10px; font-size: 1.1em; display: flex; justify-content: space-between; align-items: center; }
    </style>
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-brand"><i class="fa-solid fa-cake-candles"></i> Bakes <span>Admin</span></div>
        <ul class="nav-menu">
            <li><a href="dashboard.php" class="active"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
            <li><a href="mana_orders.php"><i class="fa-solid fa-cart-shopping"></i> Manage Orders</a></li>
            <li><a href="mana_products.php"><i class="fa-solid fa-cookie-bite"></i> Manage Products</a></li>
            <li><a href="mana_reviews.php"><i class="fa-solid fa-star"></i> Manage Reviews</a></li>
            <li><a href="mana_users.php"><i class="fa-solid fa-users"></i> Manage Users</a></li>
            <li><a href="mana_noti.php"><i class="fa-solid fa-bell"></i> Notifications</a></li>
            <li style="margin-top: 20px;"><a href="/views/auth/logout.php" onclick="return confirm('Logout?');" style="color: #ff7675;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
        </ul>
    </div>

    <div class="main-wrapper">
        <div class="top-header">
            <div style="font-weight: bold; color: #555;">Administrative Overview</div>
            <div style="color: #888;">Welcome back, <strong>Admin</strong></div>
        </div>
        <div class="content">
            <div class="kpi-row">
                <div class="kpi-card"><div>TOTAL REVENUE</div><div class="kpi-value"><?php echo number_format($total_revenue); ?> VND</div></div>
                <div class="kpi-card"><div>TOTAL PRODUCTS</div><div class="kpi-value"><?php echo $total_products; ?></div></div>
                <div class="kpi-card"><div>TOTAL ORDERS</div><div class="kpi-value"><?php echo $total_orders; ?></div></div>
                <div class="kpi-card"><div>LOW-STOCK ALERTS</div><div class="kpi-value kpi-alert"><?php echo $low_stock; ?></div></div>
            </div>
            <div class="charts-grid">
                <div class="chart-card"><h3>Sales Report <select id="salesFilter" style="padding: 2px 5px;"><option value="daily">Daily</option><option value="weekly">Weekly</option><option value="monthly">Monthly</option></select></h3><canvas id="salesReportChart"></canvas></div>
                <div class="chart-card"><h3>Best-selling Products</h3><canvas id="bestProductChart"></canvas></div>
                <div class="chart-card"><h3>Order Status Distribution</h3><canvas id="orderStatusChart"></canvas></div>
                <div class="chart-card"><h3>Average Rating per Product</h3><canvas id="avgRatingChart"></canvas></div>
            </div>
        </div>
    </div>

    <script>
    const salesData = {
        daily: { labels: <?php echo json_encode($sales_days); ?>, data: <?php echo json_encode($sales_day_data); ?>, label: 'Sales (VND) - Daily' },
        weekly: { labels: <?php echo json_encode($sales_weeks); ?>, data: <?php echo json_encode($sales_week_data); ?>, label: 'Sales (VND) - Weekly' },
        monthly: { labels: <?php echo json_encode($months); ?>, data: <?php echo json_encode($sales_data); ?>, label: 'Sales (VND) - Monthly' }
    };

    document.addEventListener("DOMContentLoaded", function() {
        // Sales Chart
        const ctxSales = document.getElementById('salesReportChart').getContext('2d');
        let salesChart = new Chart(ctxSales, {
            type: 'line',
            data: {
                labels: salesData.daily.labels,
                datasets: [{ label: salesData.daily.label, data: salesData.daily.data, backgroundColor: '#f0e4df', borderColor: '#b97a56', fill: true, tension: 0.3 }]
            }
        });

        document.getElementById('salesFilter').addEventListener('change', function() {
            const period = this.value;
            salesChart.data.labels = salesData[period].labels;
            salesChart.data.datasets[0].data = salesData[period].data;
            salesChart.data.datasets[0].label = salesData[period].label;
            salesChart.config.type = (period === 'daily') ? 'line' : 'bar';
            salesChart.update();
        });

        // Best-selling Products
        new Chart(document.getElementById('bestProductChart'), {
            type: 'bar',
            data: { labels: <?php echo json_encode($best_products); ?>, datasets: [{ label: 'Sold Qty', data: <?php echo json_encode($best_qty); ?>, backgroundColor: '#b97a56' }] }
        });

        // Order Status
        new Chart(document.getElementById('orderStatusChart'), {
            type: 'pie',
            data: { labels: <?php echo json_encode($status_labels); ?>, datasets: [{ data: <?php echo json_encode($status_counts); ?>, backgroundColor: ['#FFC107', '#28A745', '#007BFF', '#6F42C1', '#FD7E14', '#198754', '#DC3545'] }] }
        });

        // Average Rating
        new Chart(document.getElementById('avgRatingChart'), {
            type: 'bar',
            data: { labels: <?php echo json_encode($rating_products); ?>, datasets: [{ label: 'Avg Rating', data: <?php echo json_encode($rating_avgs); ?>, backgroundColor: '#FFBB91' }] },
            options: { scales: { y: { beginAtZero: true, max: 5 } } }
        });
    });
    </script>
</body>
</html>