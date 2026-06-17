<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

require_admin();

if (is_post()) {
    $orderId = (int)($_POST['order_id'] ?? 0);
    $status = trim((string)($_POST['status'] ?? 'pending'));
    $paymentStatus = trim((string)($_POST['payment_status'] ?? 'unpaid'));

    if ($orderId > 0) {
        $stmt = db()->prepare('UPDATE orders SET status = :status, payment_status = :payment_status WHERE id = :id');
        $stmt->execute([
            'id' => $orderId,
            'status' => $status,
            'payment_status' => $paymentStatus,
        ]);
        $_SESSION['flash_success'] = 'Đã cập nhật trạng thái đơn hàng.';
    }
    redirect('admin_orders.php');
}

$orders = db()->query('SELECT o.*, c.full_name AS customer_name, c.email AS customer_email FROM orders o LEFT JOIN customers c ON c.id = o.customer_id ORDER BY o.id DESC')->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<h1>Quản lý đơn hàng</h1>
<table>
    <thead>
        <tr>
            <th>Mã đơn</th>
            <th>Khách hàng</th>
            <th>Tổng tiền</th>
            <th>Vận chuyển</th>
            <th>Thanh toán</th>
            <th>Cập nhật</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td><?= e($order['order_code']) ?></td>
                <td><?= e($order['customer_name'] ?? 'N/A') ?><br><small><?= e($order['customer_email'] ?? '') ?></small></td>
                <td><?= number_format((float)$order['total_amount'], 0, ',', '.') ?> đ</td>
                <td><?= e(order_status_label((string)$order['status'])) ?></td>
                <td><?= e((string)$order['payment_method']) ?> / <?= e((string)$order['payment_status']) ?></td>
                <td>
                    <form method="post" class="inline-actions">
                        <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                        <select name="status">
                            <?php foreach (['pending' => 'Chờ xử lý', 'confirmed' => 'Đã xác nhận', 'shipping' => 'Đang giao', 'completed' => 'Đã giao', 'cancelled' => 'Đã hủy'] as $key => $label): ?>
                                <option value="<?= e($key) ?>" <?= $order['status'] === $key ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="payment_status">
                            <option value="unpaid" <?= $order['payment_status'] === 'unpaid' ? 'selected' : '' ?>>Chưa thanh toán</option>
                            <option value="paid" <?= $order['payment_status'] === 'paid' ? 'selected' : '' ?>>Đã thanh toán</option>
                        </select>
                        <button class="btn" type="submit">Lưu</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php include __DIR__ . '/includes/footer.php';
