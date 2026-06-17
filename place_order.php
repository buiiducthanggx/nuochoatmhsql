<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

require_login();

if (!is_post()) {
    redirect('checkout.php');
}

$cart = cart_items();
if (!$cart) {
    $_SESSION['flash_error'] = 'Giỏ hàng rỗng, không thể đặt hàng.';
    redirect('index.php');
}

$receiverName = trim($_POST['receiver_name'] ?? '');
$receiverPhone = trim($_POST['receiver_phone'] ?? '');
$shippingAddress = trim($_POST['shipping_address'] ?? '');
$shippingMethod = trim((string)($_POST['shipping_method'] ?? 'standard'));
$paymentMethod = trim((string)($_POST['payment_method'] ?? 'cod'));
$note = trim($_POST['note'] ?? '');

if ($receiverName === '' || $receiverPhone === '' || $shippingAddress === '') {
    $_SESSION['flash_error'] = 'Vui lòng điền đầy đủ thông tin nhận hàng.';
    redirect('checkout.php');
}

$summary = cart_pricing_summary($shippingMethod);
$subtotal = (float)$summary['subtotal'];
$discountAmount = (float)$summary['discount_amount'];
$shippingFee = (float)$summary['shipping_fee'];
$grandTotal = (float)$summary['grand_total'];
$user = current_user();
$pdo = db();

try {
    $pdo->beginTransaction();

    $orderStmt = $pdo->prepare('INSERT INTO orders(order_code, customer_id, receiver_name, receiver_phone, shipping_address, shipping_method, payment_method, payment_status, note, subtotal, discount_amount, shipping_fee, total_amount, status) VALUES(:order_code, :customer_id, :receiver_name, :receiver_phone, :shipping_address, :shipping_method, :payment_method, :payment_status, :note, :subtotal, :discount_amount, :shipping_fee, :total_amount, :status)');

    $orderStmt->execute([
        'order_code' => order_code(),
        'customer_id' => $user['id'],
        'receiver_name' => $receiverName,
        'receiver_phone' => $receiverPhone,
        'shipping_address' => $shippingAddress,
        'shipping_method' => $shippingMethod,
        'payment_method' => $paymentMethod,
        'payment_status' => $paymentMethod === 'cod' ? 'unpaid' : 'pending',
        'note' => $note,
        'subtotal' => $subtotal,
        'discount_amount' => $discountAmount,
        'shipping_fee' => $shippingFee,
        'total_amount' => $grandTotal,
        'status' => 'pending',
    ]);

    $orderId = (int)$pdo->lastInsertId();

    $detailStmt = $pdo->prepare('INSERT INTO order_items(order_id, product_id, product_name, product_price, quantity, line_total) VALUES(:order_id, :product_id, :product_name, :product_price, :quantity, :line_total)');

    foreach ($cart as $item) {
        $lineTotal = ((float)$item['price']) * ((int)$item['qty']);

        $detailStmt->execute([
            'order_id' => $orderId,
            'product_id' => $item['id'],
            'product_name' => $item['name'],
            'product_price' => $item['price'],
            'quantity' => $item['qty'],
            'line_total' => $lineTotal,
        ]);
    }

    $pdo->commit();

    unset($_SESSION['cart']);
    $_SESSION['flash_success'] = 'Đặt hàng thành công. Mã đơn của bạn là #' . $orderId;
    redirect('my_orders.php');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $_SESSION['flash_error'] = 'Có lỗi khi đặt hàng. Vui lòng thử lại.';
    redirect('checkout.php');
}
