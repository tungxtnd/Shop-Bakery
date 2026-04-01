<?php
session_start();
include '../../includes/header.php';
 
// Lấy danh sách collection từ database
$conn = new mysqli('localhost', 'root_user', 'admin123', 'ql_bakery');
$conn->set_charset('utf8');
 
$collections = [];
$sql = "SELECT id, name, description FROM collections WHERE id != 6";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $collections[] = $row;
    }
}
$conn->close();
 // Xác định collection được chọn
$selected = isset($_GET['c']) ? $_GET['c'] : 'all';
 
// Lấy sản phẩm thuộc collection nếu đã chọn collection cụ thể
$products = [];
if (is_numeric($selected) && isset($collections[$selected])) {
    $conn = new mysqli('localhost', 'root_user', 'admin123', 'ql_bakery');
    $conn->set_charset('utf8');
    $stmt = $conn->prepare("SELECT * FROM products WHERE collection_id = ? AND stock > 0");
    $stmt->bind_param("i", $collections[$selected]['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
    $stmt->close();
    $conn->close();
}
?>
<style>
body {
    background: #fff !important;
}
.collection-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 32px 0 48px 0;
}
.collection-menu {
    display: flex;
    justify-content: center;
    gap: 32px;
    margin-bottom: 32px;
    flex-wrap: wrap;
    background: transparent;
}
.collection-menu a {
    color: #222;
    text-decoration: none;
    font-size: 1rem;
    padding: 6px 10px 10px 10px;
    border-radius: 0;
    border-bottom: 2.5px solid transparent;
    transition: border-bottom 0.15s, color 0.15s;
    position: relative;
    font-weight: normal;
    background: transparent;
}
.collection-menu a.active,
.collection-menu a:hover {
    border-bottom: 2.5px solid #d17c7c;
    color: #000;
    background: transparent;
    font-weight: normal;
}
.collection-title {
    text-align: center;
    font-size: 2rem;
    font-family: 'Times New Roman', serif;
    margin-bottom: 32px;
    font-weight: 500;
    letter-spacing: 0.01em;
}
.collection-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 32px;
}
.collection-card {
    position: relative;
    background: none;
    border-radius: 0;
    box-shadow: none;
    overflow: hidden;
    text-align: left;
}
.collection-card img {
    width: 100%;
    height: 260px;
    object-fit: cover;
    display: block;
    border-radius: 0;
    box-shadow: none;
}
.collection-card-info {
    position: absolute;
    left: 0; bottom: 0; right: 0;
    padding: 0 0 18px 18px;
    color: #fff;
    text-shadow: 0 2px 8px rgba(0,0,0,0.25);
    pointer-events: none;
}
.collection-card-label {
    font-size: 0.85rem;
    letter-spacing: 0.08em;
    opacity: 0.85;
    margin-bottom: 2px;
    font-family: 'Times New Roman', serif;
}
.collection-card-title {
    font-size: 1.1rem;
    font-weight: 500;
    line-height: 1.3;
    font-family: 'Times New Roman', serif;
}
.shop-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 32px;
    margin-top: 40px;
}
.shop-products-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 32px;
    margin-top: 40px;
}
.shop-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 12px #eee;
    overflow: hidden;
    text-align: center;
    transition: box-shadow 0.2s;
    display: flex;
    flex-direction: column;
    height: 480px;
}
.shop-card:hover {
    box-shadow: 0 4px 24px #e75480aa;
}
.shop-card img {
    width: 100%;
    height: 400px;
    object-fit: cover;
    display: block;
}
.shop-card-info {
    padding: 16px 12px 18px 12px;
    flex: 1 1 auto;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}
.shop-card-title {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 8px;
    color: #e75480;
}
.shop-card-price {
    font-size: 1rem;
    color: #b97a56;
    font-weight: 500;
}
@media (max-width: 900px) {
    .shop-products-grid { grid-template-columns: 1fr 1fr; gap: 20px; }
    .shop-card img { height: 160px; }
}
@media (max-width: 600px) {
    .shop-products-grid { grid-template-columns: 1fr; gap: 16px; }
    .shop-card img { height: 120px; }
}
</style>
<div class="collection-container">
    <!-- Thanh menu phụ -->
    <div class="collection-menu">
        <a href="?c=all" class="<?php if ($selected == 'all') echo 'active'; ?>">All Occasions</a>
        <?php foreach ($collections as $i => $col): ?>
            <a href="?c=<?php echo $i; ?>" class="<?php if ($selected == $i) echo 'active'; ?>">
                <?php echo htmlspecialchars($col['name']); ?>
            </a>
        <?php endforeach; ?>
    </div>
 
    <div class="collection-title">
        EXPLORE OUR BAKERY COLLECTION<br>
        FOR ALL OCCASIONS
    </div>
 
    <div>
        <?php
        if ($selected === 'all') {
            echo '<div class="collection-grid">';
            foreach ($collections as $idx => $col) {
                $img = "../../assets/img/collection" . ($idx + 1) . ".jpg";
                echo '<div class="collection-card">';
                echo '<a href="?c=' . $idx . '">';
                echo '<img src="' . $img . '" alt="' . htmlspecialchars($col['name']) . '">';
                echo '<div class="collection-card-info">';
                echo '<div class="collection-card-label">COLLECTIONS</div>';
                echo '<div class="collection-card-title">' . htmlspecialchars($col['name']) . '</div>';
                echo '</div>';
                echo '</a>';
                echo '</div>';
            }
            echo '</div>';
        } elseif (is_numeric($selected) && isset($collections[$selected])) {
            echo '<div class="shop-products-grid">';
            if (empty($products)) {
                echo '<div style="padding:32px;text-align:center;color:#888;">No products in this collection.</div>';
            } else {
                foreach ($products as $product) {
                    $img = "/assets/img/" . htmlspecialchars($product['image']);
                    echo '<div class="shop-card">';
                    echo '<a href="/product_details.php?id=' . $product['id'] . '">';
                    echo '<img src="' . $img . '" alt="' . htmlspecialchars($product['name']) . '">';
                    echo '<div class="shop-card-info">';
                    echo '<div class="shop-card-title">' . htmlspecialchars($product['name']) . '</div>';
                    echo '<div class="shop-card-price">' . number_format($product['price']) . '₫</div>';
                    echo '</div>';
                    echo '</a>';
                    echo '</div>';
                }
            }
            echo '</div>';
        }
        ?>
    </div>
</div>
 
<?php include '../../includes/footer.php'; ?>
