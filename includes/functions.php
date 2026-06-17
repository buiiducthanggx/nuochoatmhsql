<?php
require_once __DIR__ . '/db.php';

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    $trimmed = ltrim($path, '/');
    if (BASE_URL === '/') {
        return '/' . $trimmed;
    }
    return BASE_URL . '/' . $trimmed;
}

function redirect(string $path): void
{
    $path = ltrim($path, '/');
    if (BASE_URL === '/') {
        $location = '/' . $path;
    } else {
        $location = BASE_URL . '/' . $path;
    }
    header('Location: ' . $location);
    exit;
}

function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!current_user()) {
        $_SESSION['flash_error'] = 'Vui lòng đăng nhập để tiếp tục.';
        redirect('login.php');
    }
}

function is_admin(): bool
{
    $user = current_user();
    return $user && (($user['role'] ?? 'customer') === 'admin');
}

function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        $_SESSION['flash_error'] = 'Bạn không có quyền truy cập khu vực quản trị.';
        redirect('index.php');
    }
}

function flash(string $key): ?string
{
    if (!isset($_SESSION[$key])) {
        return null;
    }

    $msg = $_SESSION[$key];
    unset($_SESSION[$key]);

    return $msg;
}

function cart_items(): array
{
    return $_SESSION['cart'] ?? [];
}

function cart_count(): int
{
    $total = 0;
    foreach (cart_items() as $item) {
        $total += (int)($item['qty'] ?? 0);
    }

    return $total;
}

function cart_subtotal(): float
{
    $subtotal = 0;
    foreach (cart_items() as $item) {
        $subtotal += ((float)$item['price']) * ((int)$item['qty']);
    }

    return $subtotal;
}

function cart_total_qty(): int
{
    return cart_count();
}

function cart_pricing_summary(string $shippingMethod = 'standard'): array
{
    $subtotal = cart_subtotal();
    $totalQty = cart_total_qty();

    $discountRate = $totalQty >= 3 ? 0.05 : 0.0;
    $discountAmount = $subtotal * $discountRate;

    // Free shipping for product subtotal from 500,000đ regardless of shipping method.
    if ($subtotal >= 500000) {
        $shippingFee = 0.0;
    } else {
        $shippingFee = $shippingMethod === 'express' ? 60000.0 : 30000.0;
    }

    $grandTotal = max(0, $subtotal - $discountAmount + $shippingFee);

    return [
        'subtotal' => $subtotal,
        'total_qty' => $totalQty,
        'discount_rate' => $discountRate,
        'discount_amount' => $discountAmount,
        'shipping_fee' => $shippingFee,
        'grand_total' => $grandTotal,
    ];
}

function find_product(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM products WHERE id = :id AND is_active = 1 LIMIT 1');
    $stmt->execute(['id' => $id]);

    $product = $stmt->fetch();
    return $product ?: null;
}

function all_products(): array
{
    $stmt = db()->query('SELECT * FROM products WHERE is_active = 1 ORDER BY id DESC');
    return $stmt->fetchAll();
}

function order_code(): string
{
    return 'TMH' . date('YmdHis') . random_int(10, 99);
}

function order_status_label(string $status): string
{
    $map = [
        'pending' => 'Chờ xử lý',
        'confirmed' => 'Đã xác nhận',
        'shipping' => 'Đang giao',
        'completed' => 'Hoàn thành',
        'cancelled' => 'Đã hủy',
    ];

    return $map[$status] ?? $status;
}

function current_url(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? (BASE_URL . '/index.php');
    return $uri;
}

function page_meta(): array
{
    $script = basename((string)($_SERVER['PHP_SELF'] ?? 'index.php'));

    $meta = [
        'title' => 'Nước Hoa TMH - Nước Hoa Chính Hãng',
        'description' => 'TMH Perfume chuyên nước hoa chính hãng cao cấp. Mua sắm nhanh, giao hàng toàn quốc, hỗ trợ tận tâm.',
    ];

    $map = [
        'index.php' => ['title' => 'Trang chủ - Nước Hoa TMH', 'description' => 'Khám phá bộ sưu tập nước hoa chính hãng tại TMH Perfume với nhiều ưu đãi hấp dẫn.'],
        'product.php' => ['title' => 'Chi tiết sản phẩm - Nước Hoa TMH', 'description' => 'Xem thông tin chi tiết sản phẩm nước hoa và đặt mua nhanh chóng tại TMH Perfume.'],
        'category.php' => ['title' => 'Danh mục sản phẩm - Nước Hoa TMH', 'description' => 'Lọc sản phẩm theo giá, thương hiệu, màu sắc và dung tích một cách nhanh chóng.'],
        'cart.php' => ['title' => 'Giỏ hàng - Nước Hoa TMH', 'description' => 'Kiểm tra giỏ hàng, cập nhật số lượng và hoàn tất đơn hàng của bạn.'],
        'checkout.php' => ['title' => 'Thanh toán - Nước Hoa TMH', 'description' => 'Điền thông tin giao hàng và hoàn tất thanh toán đơn hàng an toàn.'],
        'login.php' => ['title' => 'Đăng nhập - Nước Hoa TMH', 'description' => 'Đăng nhập tài khoản để theo dõi đơn hàng và nhận ưu đãi mới nhất.'],
        'register.php' => ['title' => 'Đăng ký - Nước Hoa TMH', 'description' => 'Tạo tài khoản nhanh để mua sắm dễ dàng tại TMH Perfume.'],
        'blog.php' => ['title' => 'Blog - Nước Hoa TMH', 'description' => 'Kiến thức và kinh nghiệm chọn nước hoa phù hợp phong cách cá nhân.'],
        'profile.php' => ['title' => 'Tài khoản của tôi - Nước Hoa TMH', 'description' => 'Cập nhật hồ sơ, địa chỉ nhận hàng và thông tin tài khoản cá nhân.'],
        'admin_orders.php' => ['title' => 'Quản lý đơn hàng - Nước Hoa TMH', 'description' => 'Quản trị viên theo dõi trạng thái đơn hàng và cập nhật tiến trình xử lý.'],
    ];

    if (isset($map[$script])) {
        $meta = $map[$script];
    }

    return $meta;
}

function parse_gallery_urls(?string $raw, ?string $fallback = null): array
{
    $items = [];

    if ($fallback) {
        $items[] = $fallback;
    }

    if ($raw) {
        foreach (explode('|', $raw) as $url) {
            $trimmed = trim($url);
            if ($trimmed !== '') {
                $items[] = $trimmed;
            }
        }
    }

    return array_values(array_unique($items));
}

function user_initial(?string $fullName): string
{
    $name = trim((string)$fullName);
    if ($name === '') {
        return 'U';
    }

    if (function_exists('mb_substr')) {
        return mb_strtoupper(mb_substr($name, 0, 1, 'UTF-8'), 'UTF-8');
    }

    return strtoupper(substr($name, 0, 1));
}

function user_avatar_url(?array $user): ?string
{
    if (!$user) {
        return null;
    }

    $path = trim((string)($user['avatar_path'] ?? ''));
    if ($path === '') {
        return null;
    }

    return url($path);
}
