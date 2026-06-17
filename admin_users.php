<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

require_admin();

if (is_post()) {
    $action = trim((string)($_POST['action'] ?? ''));
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'update_role' && $id > 0) {
        $role = trim((string)($_POST['role'] ?? 'customer'));

        if (in_array($role, ['admin', 'customer'], true)) {
            $stmt = db()->prepare('UPDATE customers SET role = :role WHERE id = :id');
            $stmt->execute(['id' => $id, 'role' => $role]);
            $_SESSION['flash_success'] = 'Đã cập nhật vai trò người dùng.';
        }
    }

    if ($action === 'delete' && $id > 0) {
        try {
            $pdo = db();
            $pdo->beginTransaction();

            // Lấy danh sách order_id của khách hàng
            $ordersStmt = $pdo->prepare('SELECT id FROM orders WHERE customer_id = :id');
            $ordersStmt->execute(['id' => $id]);
            $orders = $ordersStmt->fetchAll(PDO::FETCH_COLUMN);

            // Xóa tất cả order_items liên quan
            if (!empty($orders)) {
                $placeholders = implode(',', array_fill(0, count($orders), '?'));
                $pdo->prepare("DELETE FROM order_items WHERE order_id IN ($placeholders)")->execute($orders);
            }

            // Xóa tất cả đơn hàng của người dùng
            $deleteOrders = $pdo->prepare('DELETE FROM orders WHERE customer_id = :id');
            $deleteOrders->execute(['id' => $id]);

            // Xóa tài khoản khách hàng
            $deleteUser = $pdo->prepare('DELETE FROM customers WHERE id = :id');
            $deleteUser->execute(['id' => $id]);

            $pdo->commit();
            $_SESSION['flash_success'] = 'Đã xóa tài khoản khách hàng.';
        } catch (Throwable $e) {
            if (db()->inTransaction()) {
                db()->rollBack();
            }
            $_SESSION['flash_error'] = 'Xóa tài khoản thất bại. Vui lòng thử lại.';
        }
    }

    redirect('admin_users.php');
}

$users = db()->query("SELECT id, full_name, email, phone, role, created_at FROM customers ORDER BY FIELD(role, 'admin', 'customer'), id DESC")->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<h1>Quản lý người dùng</h1>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Họ tên</th>
            <th>Email</th>
            <th>SĐT</th>
            <th>Vai trò</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($users as $u): ?>
        <tr>
            <td><?= (int)$u['id'] ?></td>
            <td><?= e($u['full_name']) ?></td>
            <td><?= e($u['email']) ?></td>
            <td><?= e($u['phone'] ?? '') ?></td>
            <td><?= e($u['role']) ?></td>
            <td>
                <form method="post" class="inline-actions">
                    <input type="hidden" name="action" value="update_role">
                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                    <select name="role">
                        <option value="customer" <?= $u['role'] === 'customer' ? 'selected' : '' ?>>customer</option>
                        <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>admin</option>
                    </select>
                    <button class="btn" type="submit">Cập nhật</button>
                </form>
                <form method="post" class="inline-actions" onsubmit="return confirm('Xác nhận xóa tài khoản khách hàng này? Tất cả đơn hàng liên quan sẽ bị xóa.');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                    <button class="btn danger" type="submit">Xóa</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php include __DIR__ . '/includes/footer.php';
