<?php
require 'config.php';
requireLogin(); // phải đăng nhập mới dùng được giỏ hàng

$user_id = $_SESSION['user_id'];

// ================= XỬ LÝ THÊM / SỬA / XÓA =================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $variant_id = (int)$_POST['variant_id'];
        $quantity   = max(1, (int)($_POST['quantity'] ?? 1));
        $back       = $_POST['back'] ?? '';
        $fromProduct = (bool)preg_match('/^product\.php\?id=\d+$/', $back);
        $errBack    = $fromProduct ? $back : 'index.php';

        // Kiểm tra phiên bản có tồn tại, còn bán, còn hàng không
        $stmt = $pdo->prepare(
            "SELECT v.stock_quantity, p.status
             FROM product_variants v JOIN products p ON p.product_id = v.product_id
             WHERE v.variant_id = ?"
        );
        $stmt->execute([$variant_id]);
        $v = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$v || $v['status'] !== 'available') {
            setFlash('error', 'Sản phẩm không tồn tại hoặc đã ngừng kinh doanh.');
            header("Location: $errBack"); exit;
        }
        if ($v['stock_quantity'] <= 0) {
            setFlash('error', 'Phiên bản này đã hết hàng.');
            header("Location: $errBack"); exit;
        }

        // Đã có trong giỏ -> cộng dồn (không vượt quá tồn kho)
        $stmt = $pdo->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND variant_id = ?");
        $stmt->execute([$user_id, $variant_id]);
        $existing = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existing) {
            $newQty = min($v['stock_quantity'], $existing['quantity'] + $quantity);
            $pdo->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?")->execute([$newQty, $existing['cart_id']]);
        } else {
            $pdo->prepare("INSERT INTO cart (user_id, variant_id, quantity) VALUES (?, ?, ?)")
                ->execute([$user_id, $variant_id, min($v['stock_quantity'], $quantity)]);
        }

        // "Thêm vào giỏ" -> ở lại trang sản phẩm; "Mua ngay" -> sang giỏ hàng
        if (($_POST['buy'] ?? '') === 'cart' && $fromProduct) {
            setFlash('success', 'Đã thêm sản phẩm vào giỏ hàng.');
            header("Location: $back"); exit;
        }
    }

    if ($action === 'update') {
        $cart_id  = (int)$_POST['cart_id'];
        $quantity = max(1, (int)$_POST['quantity']);
        $pdo->prepare(
            "UPDATE cart c JOIN product_variants v ON v.variant_id = c.variant_id
             SET c.quantity = LEAST(?, GREATEST(v.stock_quantity, 1))
             WHERE c.cart_id = ? AND c.user_id = ?"
        )->execute([$quantity, $cart_id, $user_id]);
    }

    if ($action === 'remove') {
        $cart_id = (int)$_POST['cart_id'];
        $pdo->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?")->execute([$cart_id, $user_id]);
    }

    header("Location: cart.php");
    exit;
}

// ================= LẤY DANH SÁCH GIỎ HÀNG =================
$stmt = $pdo->prepare(
    "SELECT c.cart_id, c.quantity, v.variant_id, v.color, v.storage, v.price, v.stock_quantity,
            p.product_id, p.product_name,
            (SELECT pi.image_url FROM product_images pi WHERE pi.product_id = p.product_id
             ORDER BY pi.is_main DESC, pi.image_id LIMIT 1) AS image_url
     FROM cart c
     JOIN product_variants v ON v.variant_id = c.variant_id
     JOIN products p ON p.product_id = v.product_id
     WHERE c.user_id = ?
     ORDER BY c.cart_id DESC"
);
$stmt->execute([$user_id]);
$items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
$totalQty = 0;
foreach ($items as $item) {
    $total += $item['price'] * $item['quantity'];
    $totalQty += $item['quantity'];
}

$pageTitle = 'Giỏ hàng - PhoneShop';
require 'includes/header.php';
?>

<div class="breadcrumb"><a href="index.php">Trang chủ</a> / Giỏ hàng</div>
<h1 class="list-title">🛒 Giỏ hàng của bạn</h1>

<?php if (empty($items)): ?>
    <div class="empty-box">
        <p>Giỏ hàng đang trống.</p>
        <a class="btn-red" href="category.php">Tiếp tục mua sắm</a>
    </div>
<?php else: ?>

<div class="cart-layout">
    <div class="card" style="margin-top:0">
        <?php foreach ($items as $item): ?>
        <div class="cart-item">
            <img src="<?= htmlspecialchars(imgSrc($item['image_url'])) ?>" alt="">
            <div>
                <a class="ci-name" href="product.php?id=<?= $item['product_id'] ?>"><?= htmlspecialchars($item['product_name']) ?></a>
                <div class="ci-var"><?= htmlspecialchars($item['color']) ?> · <?= htmlspecialchars($item['storage']) ?></div>
                <form method="POST" class="inline-form">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                    <label>Số lượng
                        <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1"
                               max="<?= max(1, $item['stock_quantity']) ?>" onchange="this.form.submit()" style="width:70px">
                    </label>
                </form>
            </div>
            <div class="ci-right">
                <div class="price"><?= formatPrice($item['price'] * $item['quantity']) ?></div>
                <div class="meta"><?= formatPrice($item['price']) ?> / cái</div>
                <form method="POST" class="inline-form" style="justify-content:flex-end;margin-top:8px">
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                    <button type="submit" class="btn-danger" style="color:#fff;border:none;border-radius:6px;cursor:pointer">Xóa</button>
                </form>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="card" style="margin-top:0">
        <h3>Tóm tắt đơn hàng</h3>
        <div class="summary-row"><span>Số lượng</span><b><?= $totalQty ?></b></div>
        <div class="summary-row"><span>Phí vận chuyển</span><b>Miễn phí</b></div>
        <hr style="border:none;border-top:1px solid #eee">
        <div class="summary-row"><span>Tổng cộng</span><span class="summary-total"><?= formatPrice($total) ?></span></div>
        <a href="checkout.php" class="btn-checkout">Tiến hành đặt hàng</a>
    </div>
</div>

<?php endif; ?>

<?php require 'includes/footer.php'; ?>
