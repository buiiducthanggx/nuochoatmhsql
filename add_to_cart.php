<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

if (!is_post()) {
    redirect('index.php');
}

$productId = (int)($_POST['product_id'] ?? 0);
$qty = max(1, (int)($_POST['qty'] ?? 1));
$selectedColor = trim((string)($_POST['selected_color'] ?? ''));
$selectedSize = trim((string)($_POST['selected_size'] ?? ''));
$product = find_product($productId);

if (!$product) {
    $_SESSION['flash_error'] = 'Không tìm thấy sản phẩm.';
    redirect('index.php');
}

$cart = cart_items();
$key = (string)$productId . '|' . $selectedColor . '|' . $selectedSize;

if (isset($cart[$key])) {
    $cart[$key]['qty'] += $qty;
} else {
    $cart[$key] = [
        'id' => (int)$product['id'],
        'name' => $product['name'],
        'price' => (float)$product['price'],
        'qty' => $qty,
        'image_url' => $product['image_url'],
        'selected_color' => $selectedColor,
        'selected_size' => $selectedSize,
    ];
}

$_SESSION['cart'] = $cart;
$_SESSION['flash_success'] = 'Đã thêm sản phẩm vào giỏ hàng.';
redirect('cart.php');
