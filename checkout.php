<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
$cart = cart_items();

if (!$cart) {
    $_SESSION['flash_error'] = 'Giỏ hàng đang trống.';
    redirect('index.php');
}

$user = current_user();
$summary = cart_pricing_summary('standard');

include __DIR__ . '/includes/header.php';
?>
<h1>Thông tin thanh toán</h1>
<form class="form-card" action="<?= url('place_order.php') ?>" method="post" style="max-width:640px;">
    <label>Họ tên người nhận *</label>
    <input type="text" name="receiver_name" value="<?= e($user['full_name']) ?>" required>

    <label>Số điện thoại *</label>
    <input type="text" name="receiver_phone" value="<?= e($user['phone'] ?? '') ?>" required>

    <label>Địa chỉ nhận hàng *</label>
    <textarea name="shipping_address" required><?= e($user['address'] ?? '') ?></textarea>

    <label>Phương thức vận chuyển *</label>
    <select name="shipping_method" required>
        <option value="standard">Tiêu chuẩn (30.000đ)</option>
        <option value="express">Hỏa tốc (60.000đ)</option>
    </select>

    <label>Phương thức thanh toán *</label>
    <select name="payment_method" required>
        <option value="cod">Thanh toán khi nhận hàng (COD)</option>
        <option value="bank">Chuyển khoản ngân hàng</option>
        <option value="momo">Ví MoMo</option>
    </select>

    <label>Ghi chú</label>
    <textarea name="note"></textarea>

    <p>Tổng số lượng: <strong><?= (int)$summary['total_qty'] ?></strong></p>
    <p>Tạm tính: <strong><?= number_format((float)$summary['subtotal'], 0, ',', '.') ?> đ</strong></p>
    <p>Giảm giá: <strong>-<?= number_format((float)$summary['discount_amount'], 0, ',', '.') ?> đ</strong></p>
    <p>Phí giao hàng (tạm tính): <strong><?= number_format((float)$summary['shipping_fee'], 0, ',', '.') ?> đ</strong></p>
    <p class="price">Tổng thanh toán tạm tính: <?= number_format((float)$summary['grand_total'], 0, ',', '.') ?> đ</p>
    <p><small>Chính sách: miễn phí ship khi giá trị sản phẩm từ 500.000đ; mua từ 3 sản phẩm giảm 5%.</small></p>

    <button class="btn full" type="submit">Đặt hàng ngay</button>
</form>
<?php include __DIR__ . '/includes/footer.php';
