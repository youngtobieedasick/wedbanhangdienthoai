<?php
require 'config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $email     = trim($_POST['email']);
    $password  = $_POST['password'];
    $confirm   = $_POST['confirm_password'];
    $phone     = trim($_POST['phone']);
    $address   = trim($_POST['address']);

    if ($full_name === '' || $email === '' || $password === '') {
        $error = "Vui lòng nhập đầy đủ họ tên, email và mật khẩu.";
    } elseif ($password !== $confirm) {
        $error = "Mật khẩu nhập lại không khớp.";
    } else {
        // Kiểm tra email đã tồn tại chưa
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $error = "Email này đã được đăng ký. Vui lòng dùng email khác.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare(
                "INSERT INTO users (full_name, email, password, phone, address, role)
                 VALUES (?, ?, ?, ?, ?, 'customer')"
            );
            $stmt->execute([$full_name, $email, $hashedPassword, $phone, $address]);

            $success = "Đăng ký thành công! Bạn có thể đăng nhập ngay.";
        }
    }
}

require 'includes/header.php';
?>

<div class="auth-box">
<h2>Đăng ký tài khoản</h2>

<?php if ($error): ?>
    <p class="alert-error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p class="alert-success"><?= htmlspecialchars($success) ?> <a href="login.php">Đăng nhập tại đây</a></p>
<?php else: ?>

<form method="POST" action="register.php">
    <label>Họ và tên</label>
    <input type="text" name="full_name" required>

    <label>Email</label>
    <input type="email" name="email" required>

    <label>Mật khẩu</label>
    <input type="password" name="password" required>

    <label>Nhập lại mật khẩu</label>
    <input type="password" name="confirm_password" required>

    <label>Số điện thoại</label>
    <input type="text" name="phone">

    <label>Địa chỉ</label>
    <input type="text" name="address">

    <button type="submit">Đăng ký</button>
</form>

<p>Đã có tài khoản? <a href="login.php">Đăng nhập</a></p>

<?php endif; ?>

</div>

<?php require 'includes/footer.php'; ?>
