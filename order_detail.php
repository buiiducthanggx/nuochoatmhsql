<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
$user = current_user();
$orderId = (int)($_GET['id'] ?? 0);

$stmt = db()->prepare('SELECT * FROM orders WHERE id = :id AND customer_id = :customer_id LIMIT 1');
$stmt->execute([
    'id' => $orderId,
    'customer_id' => $user['id'],
]);
$order = $stmt->fetch();

if (!$order) {
    $_SESSION['flash_error'] = 'Không tìm thấy đơn hàng.';
    redirect('my_orders.php');
}

$itemStmt = db()->prepare('SELECT * FROM order_items WHERE order_id = :order_id ORDER BY id ASC');
$itemStmt->execute(['order_id' => $orderId]);
$items = $itemStmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<h1>Chi tiết đơn <?= e($order['order_code']) ?></h1>
<p>Trạng thái: <strong><?= e(order_status_label((string)$order['status'])) ?></strong></p>
<p>Người nhận: <?= e($order['receiver_name']) ?> - <?= e($order['receiver_phone']) ?></p>
<p>Địa chỉ: <?= e($order['shipping_address']) ?></p>
<p>Vận chuyển: <?= e((string)($order['shipping_method'] ?? 'standard')) ?></p>
<p>Thanh toán: <?= e((string)($order['payment_method'] ?? 'cod')) ?> / <?= e((string)($order['payment_status'] ?? 'unpaid')) ?></p>

<table>
    <thead>
        <tr>
            <th>Sản phẩm</th>
            <th>Giá</th>
            <th>Số lượng</th>
            <th>Thành tiền</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $item): ?>
        <tr>
            <td><?= e($item['product_name']) ?></td>
            <td><?= number_format((float)$item['product_price'], 0, ',', '.') ?> đ</td>
            <td><?= (int)$item['quantity'] ?></td>
            <td><?= number_format((float)$item['line_total'], 0, ',', '.') ?> đ</td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<p>Tạm tính: <strong><?= number_format((float)$order['subtotal'], 0, ',', '.') ?> đ</strong></p>
<p>Giảm giá: <strong>-<?= number_format((float)($order['discount_amount'] ?? 0), 0, ',', '.') ?> đ</strong></p>
<p>Phí ship: <strong><?= number_format((float)$order['shipping_fee'], 0, ',', '.') ?> đ</strong></p>
<p class="price">Tổng cộng: <?= number_format((float)$order['total_amount'], 0, ',', '.') ?> đ</p>

<a class="btn light" href="<?= url('my_orders.php') ?>">Quay lại danh sách đơn</a>
<?php include __DIR__ . '/includes/footer.php';
