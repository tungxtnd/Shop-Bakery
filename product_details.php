!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/assets/css/product_details.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <br>
    <!-- Breadcrumb -->
    <a href="/homepage.php" style="text-decoration:none; margin-left:2%; color:#000;">Home</a> /
    <a href="/shop.php" style="text-decoration:none; color:#000;">All Bouquets</a> /
    <a href="/product_details.php?id=<?php echo $product_id; ?>" style="text-decoration:none; color:#000;">
        <?php echo htmlspecialchars($product['name']); ?>
    </a>
<div class="product-detail">
 
        <!-- LEFT COLUMN -->
        <div class="product-detail-left">
            <h2><?php echo htmlspecialchars($product['name']); ?></h2>
 
            <!-- Main Image -->
            <div style="position:relative; width:100%; max-width:500px; margin:20px auto 10px auto;">
                <img id="main-image"
                    src="/assets/img/<?php echo htmlspecialchars($product['image']); ?>"
                    alt="<?php echo htmlspecialchars($product['name']); ?>"
                    style="width:100%; max-width:500px; height:500px; object-fit:cover; display:block; border-radius:12px;">
                <span style="position:absolute; right:0; top:0; background:rgba(183,94,31,0.9); color:#fff; padding:8px 16px; border-top-right-radius:12px; font-size:20px; font-weight:bold;">
                    <?php echo number_format($product['price']); ?> VND
                </span>
            </div>
 <!-- Thumbnail Gallery -->
            <div class="thumbnail-gallery" style="display:flex; justify-content:flex-start; gap:12px; max-width:500px; margin:15px auto 30px auto; overflow-x:auto;">
                <?php foreach ($gallery_images as $index => $img_name): ?>
                    <img src="/assets/img/<?php echo htmlspecialchars($img_name); ?>"
                        class="thumb-item"
                        onclick="changeMainImage(this, '/assets/img/<?php echo htmlspecialchars($img_name); ?>')"
                        style="width:55px; height:55px; margin:0 !important; display:block; flex-shrink:0; object-fit:cover; border-radius:6px; cursor:pointer; border:2px solid <?php echo $index === 0 ? '#9d503b' : 'transparent'; ?>; transition:border-color 0.2s;"
                        alt="Gallery Image">
                <?php endforeach; ?>
            </div>
<!-- Customer Reviews -->
            <div style="margin-top:40px;">
                <h3 style="color:#840000;">Customer Reviews</h3>
                <div style="margin-bottom:18px;">
                    <span style="font-size:1.25em; font-weight:bold; color:#d17c7c;">
                        <?php echo $avg_rating; ?> / 5.0
                    </span>
                    <?php for ($i = 1; $i <= 5; $i++) {
                        echo '<span style="color:' . ($i <= round($avg_rating) ? '#f7b731' : '#ddd') . '; font-size:1.2em;">&#9733;</span>';
                    } ?>
                    <span style="color:#888; font-size:1em; margin-left:10px;">
                        (<?php echo $total_reviews; ?> review<?php echo $total_reviews == 1 ? '' : 's'; ?>)
                    </span>
                </div>
 <?php if ($review_result && $review_result->num_rows > 0): ?>
                    <?php while ($review = $review_result->fetch_assoc()): ?>
                        <div style="border-bottom:1px solid #eee; padding:14px 0;">
                            <div>
                                <?php for ($i = 1; $i <= 5; $i++) {
                                    echo '<span style="color:' . ($i <= $review['rating'] ? '#f7b731' : '#ddd') . '; font-size:1.1em;">&#9733;</span>';
                                } ?>
                                <span style="color:#888; font-size:0.97em; margin-left:10px;">
                                    <?php echo htmlspecialchars($review['full_name'] ?? 'Customer'); ?>
                                    - <?php echo date('Y-m-d', strtotime($review['created_at'])); ?>
                                </span>
                            </div>
                            <div style="margin-top:6px; color:#333;">
                                <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div style="color:#888; margin:18px 0;">No reviews yet for this product.</div>
                <?php endif; ?>
            </div>
        </div>
 
