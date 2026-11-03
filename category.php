<?php
require 'config.php';

$brand_id = (int)($_GET['brand'] ?? 0);
$cat_id   = (int)($_GET['cat'] ?? 0);
$q        = trim($_GET['q'] ?? '');
$price    = $_GET['price'] ?? '';
$sort     = $_GET['sort'] ?? 'new';

// Khoảng giá: [nhãn, từ, đến]
$ranges = [
    'lt5'  => ['Dưới 5 triệu', null, 5000000],
    '5-10' => ['Từ 5 - 10 triệu', 5000000, 10000000],
    '10-20'=> ['Từ 10 - 20 triệu', 10000000, 20000000],
    'gt20' => ['Trên 20 triệu', 20000000, null],
];
$sorts = ['new' => 'Mới nhất', 'price_asc' => 'Giá thấp → cao', 'price_desc' => 'Giá cao → thấp', 'name' => 'Tên A → Z'];

$opts = ['brand_id' => $brand_id, 'category_id' => $cat_id, 'q' => $q, 'sort' => $sort];
if (isset($ranges[$price])) {
    if ($ranges[$price][1] !== null) $opts['price_min'] = $ranges[$price][1];
    if ($ranges[$price][2] !== null) $opts['price_max'] = $ranges[$price][2];
}
$products = fetchProducts($pdo, $opts);

$brands = $pdo->query("SELECT brand_id, brand_name FROM brands ORDER BY brand_name")->fetchAll(PDO::FETCH_ASSOC);
$cats   = $pdo->query("SELECT category_id, category_name FROM categories ORDER BY category_name")->fetchAll(PDO::FETCH_ASSOC);

// Tạo link giữ lại các bộ lọc hiện tại, chỉ đổi 1 tham số
function qs(array $over = []) {
    $p = array_merge($_GET, $over);
    $p = array_filter($p, function ($v) { return $v !== '' && $v !== null && $v !== '0' && $v !== 0; });
    return 'category.php' . ($p ? '?' . http_build_query($p) : '');
}

// Tiêu đề trang
$title = 'Tất cả điện thoại';
if ($q !== '') {
    $title = 'Kết quả tìm kiếm cho "' . $q . '"';
} elseif ($brand_id) {
    foreach ($brands as $b) if ($b['brand_id'] == $brand_id) $title = 'Điện thoại ' . $b['brand_name'];
} elseif ($cat_id) {
    foreach ($cats as $c) if ($c['category_id'] == $cat_id) $title = $c['category_name'];
}

$pageTitle = $title . ' - PhoneShop';
require 'includes/header.php';
?>

<div class="breadcrumb"><a href="index.php">Trang chủ</a> / <?= htmlspecialchars($title) ?></div>

<div class="list-layout">
    <aside>
        <div class="filter-box">
            <h4>Hãng sản xuất</h4>
            <a href="<?= qs(['brand' => '']) ?>" class="<?= !$brand_id ? 'active' : '' ?>">Tất cả</a>
            <?php foreach ($brands as $b): ?>
                <a href="<?= qs(['brand' => $b['brand_id']]) ?>" class="<?= $brand_id == $b['brand_id'] ? 'active' : '' ?>">
                    <?= htmlspecialchars($b['brand_name']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="filter-box">
            <h4>Mức giá</h4>
            <a href="<?= qs(['price' => '']) ?>" class="<?= $price === '' ? 'active' : '' ?>">Tất cả</a>
            <?php foreach ($ranges as $key => $r): ?>
                <a href="<?= qs(['price' => $key]) ?>" class="<?= $price === $key ? 'active' : '' ?>"><?= $r[0] ?></a>
            <?php endforeach; ?>
        </div>

        <div class="filter-box">
            <h4>Danh mục</h4>
            <a href="<?= qs(['cat' => '']) ?>" class="<?= !$cat_id ? 'active' : '' ?>">Tất cả</a>
            <?php foreach ($cats as $c): ?>
                <a href="<?= qs(['cat' => $c['category_id']]) ?>" class="<?= $cat_id == $c['category_id'] ? 'active' : '' ?>">
                    <?= htmlspecialchars($c['category_name']) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </aside>

    <section>
        <h1 class="list-title"><?= htmlspecialchars($title) ?> <small style="font-weight:normal;color:#707070">(<?= count($products) ?> sản phẩm)</small></h1>

        <div class="sort-bar">
            <span>Sắp xếp theo:</span>
            <?php foreach ($sorts as $key => $label): ?>
                <a href="<?= qs(['sort' => $key === 'new' ? '' : $key]) ?>" class="<?= $sort === $key ? 'active' : '' ?>"><?= $label ?></a>
            <?php endforeach; ?>
        </div>

        <?php if ($products): ?>
            <div class="product-grid cols4">
                <?php foreach ($products as $p) renderProductCard($p); ?>
            </div>
        <?php else: ?>
            <div class="empty-box">Không tìm thấy sản phẩm nào phù hợp. <a href="category.php" style="color:#d70018">Xem tất cả sản phẩm</a></div>
        <?php endif; ?>
    </section>
</div>

<?php require 'includes/footer.php'; ?>
