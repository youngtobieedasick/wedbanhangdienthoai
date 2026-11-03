<?php
require 'config.php';

$brands = $pdo->query("SELECT brand_id, brand_name FROM brands ORDER BY brand_name")->fetchAll(PDO::FETCH_ASSOC);
$topCats = $pdo->query("SELECT category_id, category_name FROM categories WHERE parent_id IS NULL ORDER BY category_name")->fetchAll(PDO::FETCH_ASSOC);

$latest = fetchProducts($pdo, ['sort' => 'new', 'limit' => 10]);
$slides = array_slice($latest, 0, 4);   // banner lấy từ 4 sản phẩm mới nhất

$gradients = [
    'linear-gradient(120deg,#d70018,#ff6a4d)',
    'linear-gradient(120deg,#1a1a2e,#4b4ba0)',
    'linear-gradient(120deg,#0f766e,#34d399)',
    'linear-gradient(120deg,#9d174d,#f472b6)',
];

$pageTitle = 'PhoneShop - Điện thoại chính hãng, giá tốt';
require 'includes/header.php';
?>

<!-- ============ KHU VỰC ĐẦU TRANG ============ -->
<div class="hero">
    <nav class="side-menu">
        <?php foreach ($brands as $b): ?>
            <a href="category.php?brand=<?= $b['brand_id'] ?>"><span>📱 Điện thoại <?= htmlspecialchars($b['brand_name']) ?></span><span>›</span></a>
        <?php endforeach; ?>
        <?php foreach ($topCats as $c): ?>
            <a href="category.php?cat=<?= $c['category_id'] ?>"><span>🗂️ <?= htmlspecialchars($c['category_name']) ?></span><span>›</span></a>
        <?php endforeach; ?>
    </nav>

    <div class="slider">
        <?php if ($slides): ?>
            <?php foreach ($slides as $i => $s): ?>
            <div class="slide <?= $i === 0 ? 'active' : '' ?>" style="background:<?= $gradients[$i % count($gradients)] ?>">
                <div>
                    <span class="tag">Sản phẩm mới</span>
                    <h2><?= htmlspecialchars($s['product_name']) ?></h2>
                    <p>Giá chỉ từ <b><?= formatPrice($s['min_price']) ?></b></p>
                    <a class="btn-slide" href="product.php?id=<?= (int)$s['product_id'] ?>">Xem ngay</a>
                </div>
                <div class="s-img"><img src="<?= htmlspecialchars(imgSrc($s['image_url'])) ?>" alt=""></div>
            </div>
            <?php endforeach; ?>
            <div class="dots">
                <?php foreach ($slides as $i => $s): ?><button type="button" class="<?= $i === 0 ? 'active' : '' ?>"></button><?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="slide active" style="background:<?= $gradients[0] ?>">
                <div><h2>Chào mừng đến PhoneShop</h2><p>Điện thoại chính hãng - Giá tốt - Giao nhanh</p></div>
            </div>
        <?php endif; ?>
    </div>

    <div class="side-panel">
        <div class="panel-box">
            <?php if (isLoggedIn()): ?>
                <h4>Xin chào, <?= htmlspecialchars($_SESSION['full_name']) ?> 👋</h4>
                <p>Chúc bạn mua sắm vui vẻ!</p>
                <a class="btn-red" href="cart.php">🛒 Giỏ hàng</a>
                <?php if (isAdmin()): ?><a class="btn-outline" href="admin/index.php">Quản trị</a><?php endif; ?>
            <?php else: ?>
                <h4>Chào mừng bạn đến PhoneShop</h4>
                <p>Đăng nhập để mua hàng và theo dõi đơn hàng dễ dàng.</p>
                <a class="btn-red" href="login.php">Đăng nhập</a>
                <a class="btn-outline" href="register.php">Đăng ký</a>
            <?php endif; ?>
        </div>
        <div class="panel-box">
            <h4>Vì sao chọn PhoneShop?</h4>
            <ul>
                <li>Hàng chính hãng 100%</li>
                <li>Giao nhanh toàn quốc</li>
                <li>Đổi trả trong 7 ngày</li>
                <li>Hỗ trợ trả góp 0%</li>
            </ul>
        </div>
    </div>
</div>

<!-- ============ SẢN PHẨM MỚI NHẤT ============ -->
<?php if (empty($latest)): ?>
    <div class="empty-box">Chưa có sản phẩm nào. Quản trị viên hãy thêm sản phẩm trong trang Quản trị.</div>
<?php else: ?>

<div class="section">
    <div class="section-head">
        <h2>🔥 Điện thoại mới nhất</h2>
        <div class="chips">
            <?php foreach ($brands as $b): ?>
                <a href="category.php?brand=<?= $b['brand_id'] ?>"><?= htmlspecialchars($b['brand_name']) ?></a>
            <?php endforeach; ?>
            <a href="category.php">Xem tất cả</a>
        </div>
    </div>
    <div class="product-grid">
        <?php foreach ($latest as $p) renderProductCard($p); ?>
    </div>
</div>

<!-- ============ TỪNG HÃNG ============ -->
<?php foreach ($brands as $b):
    $items = fetchProducts($pdo, ['brand_id' => $b['brand_id'], 'limit' => 5]);
    if (!$items) continue;
?>
<div class="section">
    <div class="section-head">
        <h2>Điện thoại <?= htmlspecialchars($b['brand_name']) ?></h2>
        <div class="chips"><a href="category.php?brand=<?= $b['brand_id'] ?>">Xem tất cả ›</a></div>
    </div>
    <div class="product-grid">
        <?php foreach ($items as $p) renderProductCard($p); ?>
    </div>
</div>
<?php endforeach; ?>

<?php endif; ?>

<script>
(function () {
    var slides = document.querySelectorAll('.slide'), dots = document.querySelectorAll('.dots button'), i = 0, timer;
    if (slides.length < 2) return;
    function go(n) {
        slides[i].classList.remove('active'); dots[i].classList.remove('active');
        i = (n + slides.length) % slides.length;
        slides[i].classList.add('active'); dots[i].classList.add('active');
    }
    timer = setInterval(function () { go(i + 1); }, 4500);
    dots.forEach(function (d, k) { d.addEventListener('click', function () { go(k); clearInterval(timer); }); });
})();
</script>

<?php require 'includes/footer.php'; ?>
