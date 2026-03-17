<?php
ob_start();
session_start();
include '../../includes/header.php';
include '../../connectdb.php';
$success = false;
$error = '';
$product = null;


$current_user_id = $_SESSION['user_id'] ?? 0;
$product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;


if ($product_id) {
    $sql = "SELECT * FROM products WHERE id = $product_id";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
    }
}


// Check if user has already reviewed this product
$already_reviewed = false;
if ($current_user_id && $product_id) {
    $today = date('Y-m-d');
    $check = $conn->query("SELECT id FROM reviews WHERE user_id = $current_user_id AND product_id = $product_id AND DATE(created_at) = '$today'");
    if ($check && $check->num_rows > 0) {
        $already_reviewed = true;
    }
}


// Handle submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$already_reviewed) {
    error_log(print_r($_FILES, true));
    $rating = intval($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');


    if ($rating < 1 || $rating > 5) {
        $error = "Please select a rating.";
    } elseif (empty($comment)) {
        $error = "Please enter your feedback.";
    } else {
        // Kiểm tra lại một lần nữa theo user_id và product_id
        $check = $conn->query("SELECT * FROM reviews WHERE user_id = $current_user_id AND product_id = $product_id");
        if ($check && $check->num_rows > 0) {
            $sql = "UPDATE reviews SET rating=?, comment=?, created_at=NOW() WHERE user_id=? AND product_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isii", $rating, $comment, $current_user_id, $product_id);
            $stmt->execute();
            $review_row = $conn->query("SELECT id FROM reviews WHERE user_id = $current_user_id AND product_id = $product_id")->fetch_assoc();
            $review_id = $review_row['id'];
        } else {
            $sql = "INSERT INTO reviews (product_id, user_id, rating, comment, created_at) VALUES (?, ?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiis", $product_id, $current_user_id, $rating, $comment);
            $stmt->execute();
            $review_id = $conn->insert_id;
        }
        error_log("review_id: " . $review_id);
        error_log(print_r($_FILES, true));
        // Upload images
        if (!empty($_FILES['review_images']['name'][0])) {
            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/Shop-Bakery-management/assets/img/review/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            foreach ($_FILES['review_images']['tmp_name'] as $idx => $tmp_name) {
                if ($_FILES['review_images']['error'][$idx] !== UPLOAD_ERR_OK) continue;
                $file_name = basename($_FILES['review_images']['name'][$idx]);
                $ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
                $allowed = ['jpg','jpeg','png','gif','webp'];
                if (!in_array($ext, $allowed)) continue;
                if (!@getimagesize($tmp_name)) continue;
                if ($_FILES['review_images']['size'][$idx] > 2 * 1024 * 1024) continue;
                $new_name = uniqid('review_') . '.' . $ext;
                $target = $upload_dir . $new_name;
                if (move_uploaded_file($tmp_name, $target)) {
                    $img_path = '/flower_shop/assets/img/review/' . $new_name;
                    $sql = "INSERT INTO review_images (review_id, image_path, created_at) VALUES ($review_id, '$img_path', NOW())";
                    if (!$conn->query($sql)) {
                        error_log("Insert failed: " . $conn->error);
                    }
                }
            }
        }


        // Add notification for successful review
        $type = 'review';
        $message = 'Thank you for reviewing "' . htmlspecialchars($product['name']) . '"!';
        $created_at = date('Y-m-d H:i:s');
        $noti_stmt = $conn->prepare("INSERT INTO notifications (user_id, target_user_id, product_id, type, message, created_at) VALUES (?, ?, ?, ?, ?, ?)");
        $noti_stmt->bind_param("iiisss", $current_user_id, $current_user_id, $product_id, $type, $message, $created_at);
        $noti_stmt->execute();
        $noti_stmt->close();


        $success = true;
        $stmt->close();
        $already_reviewed = true;
    }
}


$conn->close();
?>
<style>
body { background: #fff !important; }
.review-container {
    max-width: 440px;
    margin: 60px auto 60px auto;
    background: #fff;
    border-radius: 16px;
    border: 1px solid #f0e0de;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    padding: 48px 24px 28px 24px;
    display: flex;
    flex-direction: column;
    align-items: center;
    position: relative;
}
.review-back-btn {
    position: absolute;
    top: 18px;
    left: 18px;
    background: none;
    border: none;
    color: #d17c7c;
    font-size: 1.08rem;
    font-weight: 500;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 4px;
    text-decoration: none;
    transition: color 0.15s;
    z-index: 2;
}
.review-back-btn:hover {
    color: #a34e4e;
    text-decoration: underline;
}
.review-form {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.review-product-row {
    width: 100%;
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 18px;
    justify-content: flex-start;
}
.review-product-img {
    width: 54px;
    height: 54px;
    object-fit: cover;
    border-radius: 8px;
    background: #f5f5f5;
    border: 1px solid #eee;
    flex-shrink: 0;
}
.review-product-name {
    font-size: 1.08rem;
    font-weight: 500;
    color: #222;
    font-family: 'Times New Roman', serif;
    text-align: left;
}
.review-rating-row {
    width: 100%;
    margin-bottom: 18px;
    display: flex;
    align-items: center;
    gap: 14px;
    justify-content: flex-start;
}
.review-rating-label {
    font-size: 1.08rem;
    font-weight: 500;
    min-width: 90px;
    color: #222;
}
.review-stars {
    font-size: 1.7rem;
    cursor: pointer;
    user-select: none;
    display: flex;
    gap: 2px;
}
.review-star {
    color: #222;
    transition: color 0.15s;
    cursor: pointer;
}
.review-star.selected {
    color: #d17c7c;
}
.review-addimg-label {
    width: 100%;
    font-size: 1.08rem;
    font-weight: 500;
    margin-bottom: 8px;
    text-align: left;
    color: #222;
    letter-spacing: 0.01em;
}
.review-addimg-box {
    width: 100%;
    min-height: 120px;
    border: 2px dashed #e5b6b0;
    border-radius: 8px;
    background: #faf7f7;
    display: flex;
    align-items: flex-start;
    justify-content: flex-start;
    gap: 12px;
    padding: 18px 0 18px 18px;
    margin-bottom: 18px;
    position: relative;
    flex-wrap: nowrap;
    box-sizing: border-box;
}
.review-img-slot {
    flex: 0 0 90px;
    width: 90px;
    aspect-ratio: 3/4;
    height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    margin: 0;
}
.review-addimg-plus, .review-img-thumb-wrap {
    width: 90px;
    height: 120px;
    aspect-ratio: 3/4;
    min-width: 60px;
    min-height: 80px;
    max-width: 90px;
    max-height: 120px;
    box-sizing: border-box;
}
.review-addimg-plus {
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    border: none;
    background: transparent;
    font-size: 2rem;
    color: #d17c7c;
    border-radius: 10px;
    border: 1.5px dashed #d17c7c;
    transition: background 0.15s, border 0.15s;
    position: relative;
}
.review-addimg-plus input[type="file"] {
    display: none;
}
.review-addimg-plus.disabled {
    opacity: 0.5;
    pointer-events: none;
}
.review-img-thumb {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 10px;
    border: 1px solid #e5b6b0;
    background: #fff;
}
.review-img-thumb-wrap {
    position: relative;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.review-img-thumb-remove {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #fff;
    color: #d17c7c;
    border: 1px solid #d17c7c;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    font-size: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    z-index: 2;
}
.review-feedback-title {
    width: 100%;
    text-align: left;
    font-size: 1.08rem;
    font-weight: 500;
    margin-bottom: 8px;
    color: #222;
    letter-spacing: 0.01em;
}
.review-feedback-row {
    width: 100%;
    margin-bottom: 18px;
    display: flex;
    justify-content: center;
}
.review-feedback-row textarea {
    width: 100%;
    max-width: 100%;
    padding: 10px 14px;
    border: 1px solid #e5b6b0;
    border-radius: 6px;
    font-size: 1rem;
    min-height: 80px;
    background: #faf7f7;
    resize: vertical;
    display: block;
    margin: 0 auto;
    box-sizing: border-box;
}
.review-submit-btn {
    background: #d17c7c;
    color: #fff;
    border: none;
    border-radius: 6px;
    padding: 10px 32px;
    font-size: 1.08rem;
    font-weight: 500;
    cursor: pointer;
    transition: opacity 0.15s;
    margin-top: 8px;
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
}
.review-submit-btn:hover { opacity: 0.85; }
.review-error {
    color: #d17c7c;
    font-size: 1rem;
    margin-bottom: 10px;
    text-align: center;
}
.review-success {
    color: #2e9c4b;
    font-size: 1.08rem;
    margin-bottom: 14px;
    text-align: center;
    font-weight: 500;
}
.review-toast {
    position: fixed;
    top: 24px;
    left: 50%;
    transform: translateX(-50%);
    background: #2e9c4b;
    color: #fff;
    padding: 12px 28px;
    border-radius: 8px;
    font-size: 1.08rem;
    font-weight: 500;
    z-index: 9999;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    display: none;
}
</style>


<div class="review-container">
    <a href="javascript:history.back()" class="review-back-btn">
        &#8592; Back
    </a>
    <?php if ($error): ?>
        <div class="review-error"><?php echo htmlspecialchars($error); ?></div>
    <?php elseif ($already_reviewed): ?>
        <div class="review-error">You have already sent feedback for this product!</div>
    <?php endif; ?>
    <form class="review-form" method="post" action="" enctype="multipart/form-data" id="reviewForm"
        <?php if($success || $already_reviewed) echo 'style="display:none"'; ?>>
        <div class="review-product-row">
            <img class="review-product-img" src="../../assets/img/<?php echo htmlspecialchars($product['image']); ?>" alt="">
            <div class="review-product-name">
                <?php echo $product ? htmlspecialchars($product['name']) : 'Product'; ?>
            </div>
        </div>
        <div class="review-rating-row">
            <span class="review-rating-label">Your Rating:</span>
            <span class="review-stars">
                <?php for ($i=1;$i<=5;$i++): ?>
                    <span class="review-star" data-value="<?php echo $i; ?>">★</span>
                <?php endfor; ?>
            </span>
        </div>
        <div class="review-addimg-label">
            You can add up to 3 real product photos (optional):
        </div>
        <div class="review-addimg-box" id="review-addimg-box">
            <!-- 4 slots: 1 add, 3 preview -->
            <div class="review-img-slot" id="addimg-slot">
                <label class="review-addimg-plus" id="addimg-plus">
                    <span>+</span>
                    <input type="file" id="review-img-input" name="review_images[]" accept="image/*" multiple>
                </label>
            </div>
            <div class="review-img-slot" id="img-slot-1"></div>
            <div class="review-img-slot" id="img-slot-2"></div>
            <div class="review-img-slot" id="img-slot-3"></div>
        </div>
        <div class="review-feedback-title">Write your feedback about this product:</div>
        <div class="review-feedback-row">
            <textarea name="comment" required placeholder="Write your feedback..."></textarea>
        </div>
        <button type="submit" class="review-submit-btn">Submit</button>
        <input type="hidden" name="rating" value="">
    </form>
</div>


<div class="review-toast" id="review-toast">Feedback sent successfully!</div>


<script>
document.querySelectorAll('.review-star').forEach(function(star, idx, stars) {
    star.addEventListener('click', function() {
        let value = parseInt(this.getAttribute('data-value'));
        stars.forEach(function(s, i) {
            s.classList.toggle('selected', i < value);
        });
        document.querySelector('input[name="rating"]').value = value;
    });
});


// 4 slot image logic: 1 add, 3 preview
const imgInput = document.getElementById('review-img-input');
const addImgPlus = document.getElementById('addimg-plus');
const imgSlots = [
    document.getElementById('img-slot-1'),
    document.getElementById('img-slot-2'),
    document.getElementById('img-slot-3')
];


let selectedFiles = [];


function updateInputFiles() {
    const dt = new DataTransfer();
    selectedFiles.forEach(f => dt.items.add(f));
    imgInput.files = dt.files;
}


function renderImageSlots() {
    imgSlots.forEach(slot => slot.innerHTML = '');
    selectedFiles.forEach((file, idx) => {
        const reader = new FileReader();
        reader.onload = function(e) {
            const wrap = document.createElement('div');
            wrap.className = 'review-img-thumb-wrap';
            const img = document.createElement('img');
            img.className = 'review-img-thumb';
            img.src = e.target.result;
            // Remove button
            const removeBtn = document.createElement('span');
            removeBtn.className = 'review-img-thumb-remove';
            removeBtn.innerHTML = '&times;';
            removeBtn.onclick = function(ev) {
                selectedFiles.splice(idx, 1);
                updateInputFiles();
                renderImageSlots();
            };
            wrap.appendChild(img);
            wrap.appendChild(removeBtn);
            imgSlots[idx].appendChild(wrap);
        };
        reader.readAsDataURL(file);
    });


    // Disable addimg-plus if 3 images selected
    if (selectedFiles.length >= 3) {
        addImgPlus.classList.add('disabled');
        imgInput.disabled = true;
    } else {
        addImgPlus.classList.remove('disabled');
        imgInput.disabled = false;
    }
}


if (imgInput) {
    imgInput.addEventListener('change', function() {
        let filesArr = Array.from(this.files);
        let remain = 3 - selectedFiles.length;
        filesArr.slice(0, remain).forEach(f => selectedFiles.push(f));
        selectedFiles = selectedFiles.slice(0, 3);
        updateInputFiles();
        renderImageSlots();
        imgInput.value = '';
    });
}


window.addEventListener('DOMContentLoaded', function() {
    selectedFiles = [];
    renderImageSlots();


    <?php if ($success): ?>
        // Hiện toast báo thành công
        var toast = document.getElementById('review-toast');
        toast.style.display = 'block';
        setTimeout(function() {
            toast.style.display = 'none';
            window.location.href = 'orderhistory.php';
        }, 1500);
    <?php endif; ?>
});
</script>


<?php include '../../includes/footer.php'; ?>
