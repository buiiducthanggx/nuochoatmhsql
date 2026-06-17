<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

if (is_post()) {
    $cart = cart_items();

    foreach ($_POST['qty'] ?? [] as $id => $qty) {
        $id = (string)$id;
        $qty = (int)$qty;

        if (!isset($cart[$id])) {
            continue;
        }

        if ($qty <= 0) {
            unset($cart[$id]);
        } else {
            $cart[$id]['qty'] = $qty;
        }
    }

    $_SESSION['cart'] = $cart;
    $_SESSION['flash_success'] = 'Đã cập nhật giỏ hàng.';
    redirect('cart.php');
}

$cart = cart_items();
$summary = cart_pricing_summary('standard');
include __DIR__ . '/includes/header.php';
?>
<h1>Giỏ hàng</h1>
<?php if (!$cart): ?>
    <p>Chưa có sản phẩm trong giỏ. <a href="<?= url('index.php') ?>">Mua ngay</a></p>
<?php else: ?>
<form method="post">
    <table>
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Giá</th>
                <th>Số lượng</th>
                <th>Thành tiền</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cart as $cartKey => $item): ?>
                <tr>
                    <td>
                        <?= e($item['name']) ?>
                        <?php if (!empty($item['selected_color']) || !empty($item['selected_size'])): ?>
                            <br><small>
                                <?= !empty($item['selected_color']) ? 'Màu: ' . e($item['selected_color']) : '' ?>
                                <?= !empty($item['selected_size']) ? ' | Dung tích: ' . e($item['selected_size']) : '' ?>
                            </small>
                        <?php endif; ?>
                    </td>
                    <td><?= number_format((float)$item['price'], 0, ',', '.') ?> đ</td>
                    <td style="max-width:120px;"><input type="number" min="0" name="qty[<?= e((string)$cartKey) ?>]" value="<?= (int)$item['qty'] ?>"></td>
                    <td><?= number_format(((float)$item['price']) * ((int)$item['qty']), 0, ',', '.') ?> đ</td>
                    <td><a class="btn light" href="<?= url('remove_cart_item.php?id=' . urlencode((string)$cartKey)) ?>">Xóa</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <p>Tổng số lượng: <strong><?= (int)$summary['total_qty'] ?></strong></p>
    <p>Tạm tính: <strong><?= number_format((float)$summary['subtotal'], 0, ',', '.') ?> đ</strong></p>
    <p>Giảm giá (5% khi mua từ 3 sản phẩm): <strong>-<?= number_format((float)$summary['discount_amount'], 0, ',', '.') ?> đ</strong></p>
    <p>Phí ship dự kiến: <strong><?= number_format((float)$summary['shipping_fee'], 0, ',', '.') ?> đ</strong></p>
    <p class="price">Tổng ước tính: <?= number_format((float)$summary['grand_total'], 0, ',', '.') ?> đ</p>
    <p><small>Miễn phí ship khi giá trị sản phẩm từ 500.000đ.</small></p>

    <button class="btn" type="submit">Cập nhật giỏ</button>
    <a class="btn" href="<?= url('checkout.php') ?>">Tiến hành đặt hàng</a>
</form>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php';
