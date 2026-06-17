<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
$user = current_user();

$stmt = db()->prepare('SELECT * FROM orders WHERE customer_id = :customer_id ORDER BY id DESC');
$stmt->execute(['customer_id' => $user['id']]);
$orders = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<h1>Đơn hàng của tôi</h1>
<?php if (!$orders): ?>
    <p>Bạn chưa có đơn hàng nào.</p>
<?php else: ?>
<table>
    <thead>
        <tr>
            <th>Mã đơn</th>
            <th>Ngày tạo</th>
            <th>Trạng thái</th>
            <th>Thanh toán</th>
            <th>Tổng tiền</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($orders as $order): ?>
        <tr>
            <td><?= e($order['order_code']) ?></td>
            <td><?= e($order['created_at']) ?></td>
            <td><?= e(order_status_label((string)$order['status'])) ?></td>
            <td><?= e((string)($order['payment_status'] ?? 'unpaid')) ?></td>
            <td><?= number_format((float)$order['total_amount'], 0, ',', '.') ?> đ</td>
            <td><a class="btn light" href="<?= url('order_detail.php?id=' . (int)$order['id']) ?>">Chi tiết</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
<?php include __DIR__ . '/includes/footer.php';
