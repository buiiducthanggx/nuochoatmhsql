<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

$id = (string)($_GET['id'] ?? '');
$cart = cart_items();

if (isset($cart[$id])) {
    unset($cart[$id]);
    $_SESSION['cart'] = $cart;
    $_SESSION['flash_success'] = 'Đã xóa sản phẩm khỏi giỏ.';
}

redirect('cart.php');
