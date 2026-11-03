<?php
require 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        $error = "Email hoặc mật khẩu không đúng.";
    } elseif ($user['status'] === 'locked') {
        $error = "Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.";
    } else {
        // Lưu thông tin vào session -> dùng để phân quyền hiển thị menu
        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role']      = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: admin/index.php");
        } else {
            header("Location: index.php");
        }
        exit;
    }
}

require 'includes/header.php';
?>

<div class="auth-box">
<h2>Đăng nhập</h2>

<?php if ($error): ?>
    <p class="alert-error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<form method="POST" action="login.php">
    <label>Email</label>
    <input type="email" name="email" required>

    <label>Mật khẩu</label>
    <input type="password" name="password" required>

    <button type="submit">Đăng nhập</button>
</form>

<p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>

</div>

<?php require 'includes/footer.php'; ?>
