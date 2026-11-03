<?php
require 'config.php';

$product_id = (int)($_GET['id'] ?? 0);

$stmt = $pdo->prepare(
    "SELECT p.*, b.brand_name, c.category_name
     FROM products p
     LEFT JOIN brands b ON b.brand_id = p.brand_id
     LEFT JOIN categories c ON c.category_id = p.category_id
     WHERE p.product_id = ?"
);
$stmt->execute([$product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    http_response_code(404);
    $pageTitle = 'Không tìm thấy sản phẩm';
    require 'includes/header.php';
    echo '<div class="empty-box">Không tìm thấy sản phẩm. <a href="index.php" style="color:#d70018">Về trang chủ</a></div>';
    require 'includes/footer.php';
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM product_variants WHERE product_id = ? ORDER BY price ASC, variant_id ASC");
$stmt->execute([$product_id]);
$variants = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_main DESC, image_id ASC");
$stmt->execute([$product_id]);
$images = $stmt->fetchAll(PDO::FETCH_ASSOC);
$mainImage = $images[0]['image_url'] ?? null;

$stmt = $pdo->prepare("SELECT ROUND(AVG(rating), 1) AS avg_rating, COUNT(*) AS cnt FROM reviews WHERE product_id = ?");
$stmt->execute([$product_id]);
$rating = $stmt->fetch(PDO::FETCH_ASSOC);

$canBuy  = $product['status'] === 'available' && !empty($variants);
$related = fetchProducts($pdo, ['brand_id' => $product['brand_id'], 'exclude_id' => $product_id, 'limit' => 5]);

// Dữ liệu phiên bản truyền sang JavaScript
$vjs = [];
foreach ($variants as $v) {
    $vjs[] = [
        'id'      => (int)$v['variant_id'],
        'color'   => $v['color'],
        'storage' => $v['storage'],
        'ram'     => $v['ram'],
        'price'   => (int)$v['price'],
        'orig'    => (int)$v['original_price'],
        'stock'   => (int)$v['stock_quantity'],
    ];
}

$pageTitle = $product['product_name'] . ' - PhoneShop';
require 'includes/header.php';
?>

<div class="breadcrumb">
    <a href="index.php">Trang chủ</a> /
    <?php if ($product['brand_name']): ?>
        <a href="category.php?brand=<?= (int)$product['brand_id'] ?>"><?= htmlspecialchars($product['brand_name']) ?></a> /
    <?php endif; ?>
    <?= htmlspecialchars($product['product_name']) ?>
</div>

<div class="pd-head">
    <h1><?= htmlspecialchars($product['product_name']) ?></h1>
    <?php if ($rating['cnt'] > 0): ?>
        <span><span class="rating">★ <?= htmlspecialchars($rating['avg_rating']) ?></span> (<?= (int)$rating['cnt'] ?> đánh giá)</span>
    <?php else: ?>
        <span style="color:#707070">Chưa có đánh giá</span>
    <?php endif; ?>
</div>

<div class="pd-layout">
    <!-- ===== CỘT TRÁI: ẢNH ===== -->
    <div class="gallery">
        <div class="gallery-main"><img id="mainImg" src="<?= htmlspecialchars(imgSrc($mainImage)) ?>" alt=""></div>
        <?php if (count($images) > 1): ?>
        <div class="gallery-thumbs">
            <?php foreach ($images as $i => $img): ?>
                <img src="<?= htmlspecialchars(imgSrc($img['image_url'])) ?>" class="<?= $i === 0 ? 'active' : '' ?>" onclick="setImg(this)" alt="">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- ===== CỘT PHẢI: GIÁ + CHỌN PHIÊN BẢN ===== -->
    <div>
        <?php if ($product['status'] !== 'available'): ?>
            <p class="alert-error">Sản phẩm này hiện đã ngừng kinh doanh.</p>
        <?php endif; ?>

        <?php if (empty($variants)): ?>
            <div class="empty-box">Sản phẩm chưa có phiên bản để bán.</div>
        <?php else: ?>
        <div class="price-box">
            <span class="cur-price" id="curPrice"></span>
            <span class="old" id="oldPrice"></span>
            <span class="disc" id="discTag" style="display:none"></span>
        </div>

        <div class="opt-title">Phiên bản</div>
        <div class="opts" id="storageOpts"></div>

        <div class="opt-title">Màu sắc</div>
        <div class="opts" id="colorOpts"></div>

        <div class="stock-info" id="stockInfo"></div>

        <form id="buyForm" method="POST" action="cart.php" class="plain-form">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="variant_id" id="variantId" value="">
            <input type="hidden" name="quantity" value="1">
            <input type="hidden" name="back" value="product.php?id=<?= $product_id ?>">
            <div class="buy-actions">
                <button type="submit" name="buy" value="now" class="btn-buy" data-buy>
                    Mua ngay<small>Giao tận nơi hoặc nhận tại cửa hàng</small>
                </button>
                <button type="submit" name="buy" value="cart" class="btn-cart" data-buy>🛒 Thêm vào giỏ</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<!-- ===== MÔ TẢ + THÔNG SỐ ===== -->
<div class="pd-bottom">
    <div class="card">
        <h3>Mô tả sản phẩm</h3>
        <p><?= nl2br(htmlspecialchars($product['description'] ?: 'Đang cập nhật...')) ?></p>
    </div>
    <div class="card">
        <h3>Thông số kỹ thuật</h3>
        <table class="spec-table">
            <tr><td>Hãng</td><td><?= htmlspecialchars($product['brand_name'] ?? '-') ?></td></tr>
            <tr><td>Màn hình</td><td><?= htmlspecialchars($product['screen'] ?: '-') ?></td></tr>
            <tr><td>Chip xử lý</td><td><?= htmlspecialchars($product['chipset'] ?: '-') ?></td></tr>
            <tr><td>Pin</td><td><?= htmlspecialchars($product['battery'] ?: '-') ?></td></tr>
            <tr><td>Camera</td><td><?= htmlspecialchars($product['camera'] ?: '-') ?></td></tr>
            <tr><td>Hệ điều hành</td><td><?= htmlspecialchars($product['os'] ?: '-') ?></td></tr>
        </table>
    </div>
</div>

<?php if ($related): ?>
<div class="section">
    <div class="section-head"><h2>Sản phẩm cùng hãng</h2></div>
    <div class="product-grid">
        <?php foreach ($related as $p) renderProductCard($p); ?>
    </div>
</div>
<?php endif; ?>

<!-- ===== THANH MUA NHANH (hiện khi cuộn xuống) ===== -->
<?php if (!empty($variants)): ?>
<div class="sticky-bar" id="stickyBar">
    <img src="<?= htmlspecialchars(imgSrc($mainImage)) ?>" alt="">
    <div class="sb-name" id="sbName"></div>
    <div class="sb-price" id="sbPrice"></div>
    <button type="submit" form="buyForm" name="buy" value="now" class="btn-buy" data-buy>Mua ngay</button>
</div>
<?php endif; ?>

<script>
function setImg(el) {
    document.getElementById('mainImg').src = el.src;
    document.querySelectorAll('.gallery-thumbs img').forEach(function (i) { i.classList.remove('active'); });
    el.classList.add('active');
}

(function () {
    var variants = <?= json_encode($vjs, JSON_UNESCAPED_UNICODE) ?>;
    if (!variants.length) return;

    var canBuy = <?= $canBuy ? 'true' : 'false' ?>;
    var thumb  = <?= json_encode(imgSrc($mainImage), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    var pname  = <?= json_encode($product['product_name'], JSON_UNESCAPED_UNICODE) ?>;
    var fmt = function (n) { return new Intl.NumberFormat('vi-VN').format(n) + 'đ'; };

    var storages = [];
    variants.forEach(function (v) { if (storages.indexOf(v.storage) === -1) storages.push(v.storage); });

    var curStorage = null, cur = null;
    var storageBox = document.getElementById('storageOpts');
    var colorBox   = document.getElementById('colorOpts');

    function inStorage(st) { return variants.filter(function (v) { return v.storage === st; }); }

    function renderStorages() {
        storageBox.innerHTML = '';
        storages.forEach(function (st) {
            var list = inStorage(st);
            var min = Math.min.apply(null, list.map(function (v) { return v.price; }));
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'opt' + (st === curStorage ? ' active' : '');
            b.textContent = st;
            var s = document.createElement('small');
            s.textContent = 'Từ ' + fmt(min);
            b.appendChild(s);
            b.onclick = function () { chooseStorage(st); };
            storageBox.appendChild(b);
        });
    }

    function renderColors() {
        colorBox.innerHTML = '';
        inStorage(curStorage).forEach(function (v) {
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'opt color' + (cur && cur.id === v.id ? ' active' : '') + (v.stock <= 0 ? ' soldout' : '');
            var img = document.createElement('img');
            img.src = thumb; img.alt = '';
            var box = document.createElement('div');
            var name = document.createElement('b');
            name.textContent = v.color;
            var price = document.createElement('small');
            price.textContent = v.stock > 0 ? fmt(v.price) : 'Hết hàng';
            box.appendChild(name); box.appendChild(price);
            b.appendChild(img); b.appendChild(box);
            b.onclick = function () { selectVariant(v); renderColors(); };
            colorBox.appendChild(b);
        });
    }

    function chooseStorage(st, preferred) {
        curStorage = st;
        var list = inStorage(st);
        var pick = preferred || list.filter(function (v) { return v.stock > 0; })[0] || list[0];
        selectVariant(pick);
        renderStorages();
        renderColors();
    }

    function selectVariant(v) {
        cur = v;
        document.getElementById('curPrice').textContent = fmt(v.price);
        var old = document.getElementById('oldPrice'), disc = document.getElementById('discTag');
        if (v.orig > v.price) {
            old.textContent = fmt(v.orig);
            disc.textContent = 'Giảm ' + Math.round((1 - v.price / v.orig) * 100) + '%';
            disc.style.display = '';
        } else { old.textContent = ''; disc.style.display = 'none'; }

        document.getElementById('variantId').value = v.id;
        document.getElementById('sbName').textContent = pname + ' - ' + v.color + ' ' + v.storage;
        document.getElementById('sbPrice').textContent = fmt(v.price);

        var info = (v.ram ? 'RAM ' + v.ram + ' · ' : '') +
                   (v.stock <= 0 ? 'Hết hàng' : (v.stock <= 5 ? 'Chỉ còn ' + v.stock + ' sản phẩm' : 'Còn hàng'));
        document.getElementById('stockInfo').textContent = info;

        document.querySelectorAll('[data-buy]').forEach(function (b) { b.disabled = !canBuy || v.stock <= 0; });
    }

    var first = variants.filter(function (v) { return v.stock > 0; })[0] || variants[0];
    chooseStorage(first.storage, first);

    // Hiện thanh mua nhanh khi cuộn xuống
    var bar = document.getElementById('stickyBar');
    window.addEventListener('scroll', function () { bar.classList.toggle('show', window.scrollY > 450); });
})();
</script>

<?php require 'includes/footer.php'; ?>
