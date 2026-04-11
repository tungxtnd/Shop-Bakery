<?php
session_start(); // Bắt đầu phiên làm việc
include '../../connectdb.php'; // Kết nối cơ sở dữ liệu

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? ''); // Loại bỏ khoảng trắng tên
    $email = trim($_POST['email'] ?? ''); // Loại bỏ khoảng trắng email
    $password = $_POST['password'] ?? ''; // Nhận mật khẩu
    $confirm_password = $_POST['confirm_password'] ?? ''; // Nhận mật khẩu xác nhận
    $phone = trim($_POST['phone'] ?? ''); // Loại bỏ khoảng trắng số điện thoại
    $address = trim($_POST['address'] ?? ''); // Loại bỏ khoảng trắng địa chỉ

    if ($password !== $confirm_password) {
        $error = "Passwords do not match."; // Kiểm tra mật khẩu khớp nhau
    } else {
        // Kiểm tra email đã tồn tại chưa
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = "Email already exists."; // Thông báo nếu email đã tồn tại
        } else {
            // MỚI: Mã hóa mật khẩu trước khi lưu vào Database
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Thực hiện chèn người dùng mới với mật khẩu đã mã hóa và vai trò 'customer'
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, 'customer')");
            $stmt->bind_param("sssss", $full_name, $email, $hashed_password, $phone, $address);
            
            if ($stmt->execute()) {
                header("Location: login.php?registered=1"); // Chuyển hướng sau khi đăng ký thành công
                exit;
            } else {
                $error = "Registration failed. Please try again."; // Thông báo lỗi đăng ký
            }
        }
        $stmt->close();
    }
}
include '../../includes/header.php'; // Nhúng header
?>

<div class="register-bg">
    <div class="register-box">
        <div class="register-title">REGISTER</div>
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <form method="post" action="register.php">
            <div class="form-row">
                <div class="form-col">
                    <label class="register-label" for="full_name">Full Name</label>
                    <input class="register-input" type="text" name="full_name" id="full_name" required autofocus>
                </div>
                <div class="form-col">
                    <label class="register-label" for="email">Email</label>
                    <input class="register-input" type="email" name="email" id="email" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-col">
                    <label class="register-label" for="phone">Phone</label>
                    <input class="register-input" type="text" name="phone" id="phone">
                </div>
                <div class="form-col">
                    <label class="register-label" for="address">Address</label>
                    <input class="register-input" type="text" name="address" id="address">
                </div>
            </div>
            <div class="form-row">
                <div class="form-col">
                    <label class="register-label" for="password">Password</label>
                    <input class="register-input" type="password" name="password" id="password" required>
                </div>
                <div class="form-col">
                    <label class="register-label" for="confirm_password">Confirm Password</label>
                    <input class="register-input" type="password" name="confirm_password" id="confirm_password" required>
                </div>
            </div>
            <button class="register-btn" type="submit">Register</button>
        </form>
        <hr class="register-divider">
        <div class="register-new-title">Already have an account?</div>
        <form action="login.php" method="get">
            <button class="register-create-btn" type="submit">Sign in</button>
        </form>
    </div>
</div>
<?php include '../../includes/footer.php'; ?>

<style>
/* Phần CSS giữ nguyên từ file gốc của bạn */
.register-bg {
    height: calc(100vh - 70px);
    min-height: unset;
    background: url('https://bromabakery.com/wp-content/uploads/2020/01/Healthy-Thin-Mints-2-1067x1600.jpg') center center/cover no-repeat;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    padding-top: 0;
}
.register-box {
    position: relative;
    z-index: 1;
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid #222;
    border-radius: 12px;
    max-width: 550px;
    width: 100%;
    margin: 40px auto;
    padding: 30px 40px 24px 40px;
    box-sizing: border-box;
    box-shadow: 0 4px 24px #eee;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.register-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.8rem;
    font-weight: 700;
    letter-spacing: 1px;
    text-align: center;
    margin-bottom: 20px;
    width: 100%;
}
.register-label {
    font-size: 1rem;
    color: #222;
    margin-bottom: 4px;
    display: block;
    font-weight: 500;
}
.register-input {
    width: 100%;
    padding: 10px 14px;
    margin-bottom: 16px;
    border-radius: 6px;
    border: 1.5px solid #222;
    font-size: 1rem;
    font-family: 'Montserrat', Arial, sans-serif;
    background: #fff;
    box-sizing: border-box;
    height: 40px;
}
.register-input:focus {
    outline: none;
    border-color: #cb5d00;
}
.register-btn {
    width: 60%;
    margin: 16px auto 0 auto;
    display: block;
    background: #222;
    color: #fff;
    border: none;
    border-radius: 24px;
    padding: 10px 0;
    font-size: 15px;
    font-family: 'Montserrat', Arial, sans-serif;
    font-weight: 700;
    cursor: pointer;
    transition: background 0.2s;
}
.register-btn:hover {
    background: #cb5d00;
}
.register-divider {
    border: none;
    border-top: 1px solid #eee;
    margin: 16px 0;
    width: 100%;
}
.register-new-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.1rem;
    font-style: italic;
    color: #222;
    margin-bottom: 10px;
    text-align: center;
    width: 100%;
}
.register-create-btn {
    width: 150px;
    margin: 16px auto 0 auto;
    display: block;
    background: #fff;
    color: #222;
    border: 1.5px solid #222;
    border-radius: 24px;
    padding: 10px 0;
    font-size: 15px;
    font-family: 'Montserrat', Arial, sans-serif;
    font-weight: 500;
    cursor: pointer;
    transition: background 0.2s, color 0.2s;
}
.register-create-btn:hover {
    background: #cb5d00;
    color: #fff;
    border-color: #840000;
}
.error {
    color: #840000;
    text-align: center;
    margin-bottom: 12px;
    font-size: 14px;
}
.form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 0;
    width: 100%;
}
.form-col {
    flex: 1;
    display: flex;
    flex-direction: column;
}
@media (max-width: 900px) {
    .register-box {
        max-width: 98vw;
        padding: 24px 8vw;
    }
    .form-row {
        flex-direction: column;
        gap: 0;
    }
    .register-btn, .register-create-btn {
        width: 100%;
    }
}
</style>