<?php
session_start();

// ================= CẤU HÌNH KẾT NỐI CSDL =================
$host = 'localhost';
$dbname = 'banhang_dienthoai';
$user = 'root';
$pass = ''; // XAMPP mặc định password rỗng

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Kết nối CSDL thất bại: " . $e->getMessage());
}

// ================= HÀM HỖ TRỢ PHÂN QUYỀN =================

// Kiểm tra đã đăng nhập chưa
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Kiểm tra có phải admin không
function isAdmin() {
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

// Chặn truy cập nếu chưa đăng nhập -> đá về trang login
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit;
    }
}

// Chặn truy cập trang quản trị nếu không phải admin
function requireAdmin() {
    if (!isAdmin()) {
        header("Location: ../index.php");
        exit;
    }
}

// ================= THÔNG BÁO (flash message) =================
function setFlash($type, $msg) {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function showFlash() {
    if (!empty($_SESSION['flash'])) {
        $cls = $_SESSION['flash']['type'] === 'error' ? 'alert-error' : 'alert-success';
        echo '<p class="' . $cls . '">' . htmlspecialchars($_SESSION['flash']['msg']) . '</p>';
        unset($_SESSION['flash']);
    }
}

// ================= XỬ LÝ ẢNH =================

// Trả về đường dẫn ảnh để hiển thị (có ảnh dự phòng nếu chưa có ảnh)
// $prefix = '../' khi dùng trong thư mục admin
function imgSrc($path, $prefix = '') {
    if (!$path) return $prefix . 'images/no-image.svg';
    if (preg_match('#^https?://#i', $path)) return $path;
    return $prefix . $path;
}

// Upload 1 ảnh: chỉ nhận JPG/PNG/WEBP, tối đa 5MB. Trả về 'images/xxx.jpg' hoặc false
function uploadImage($file) {
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) return false;
    if ($file['size'] > 5 * 1024 * 1024) return false;

    $info = @getimagesize($file['tmp_name']);   // kiểm tra đúng là file ảnh thật
    if (!$info) return false;

    $allowed = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    if (!isset($allowed[$info[2]])) return false;

    $dir = __DIR__ . '/images/';
    if (!is_dir($dir)) mkdir($dir, 0777, true);

    $name = 'sp_' . bin2hex(random_bytes(8)) . '.' . $allowed[$info[2]];
    if (move_uploaded_file($file['tmp_name'], $dir . $name)) {
        return 'images/' . $name;
    }
    return false;
}

// Upload nhiều ảnh cùng lúc (input name="images[]" multiple)
function uploadMultipleImages($files) {
    $saved = [];
    if (!isset($files['name']) || !is_array($files['name'])) return $saved;

    foreach ($files['name'] as $i => $n) {
        $f = [
            'name'     => $n,
            'tmp_name' => $files['tmp_name'][$i],
            'error'    => $files['error'][$i],
            'size'     => $files['size'][$i],
        ];
        $p = uploadImage($f);
        if ($p) $saved[] = $p;
    }
    return $saved;
}

// Xóa file ảnh khỏi ổ đĩa (chỉ xóa file nằm trong thư mục images/)
function deleteImageFile($path) {
    if ($path && strpos($path, 'images/') === 0 && strpos($path, '..') === false) {
        $full = __DIR__ . '/' . $path;
        if (is_file($full)) @unlink($full);
    }
}
require_once __DIR__ . '/includes/functions.php';
