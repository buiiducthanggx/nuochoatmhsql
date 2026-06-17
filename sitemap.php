<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

$isAdmin = is_admin();

$groups = [
    [
        'title' => 'Khám phá',
        'desc' => 'Các trang giúp khách duyệt sản phẩm nhanh nhất.',
        'links' => [
            ['label' => 'Trang chủ', 'url' => BASE_URL . '/index.php'],
            ['label' => 'Danh mục sản phẩm', 'url' => BASE_URL . '/category.php'],
            ['label' => 'Blog', 'url' => BASE_URL . '/blog.php'],
            ['label' => 'Giới thiệu', 'url' => BASE_URL . '/gioi-thieu.php'],
        ],
    ],
    [
        'title' => 'Mua sắm',
        'desc' => 'Luồng mua hàng và theo dõi đơn.',
        'links' => [
            ['label' => 'Giỏ hàng', 'url' => BASE_URL . '/cart.php'],
            ['label' => 'Thanh toán', 'url' => BASE_URL . '/checkout.php'],
            ['label' => 'Đơn hàng của tôi', 'url' => BASE_URL . '/my_orders.php'],
            ['label' => 'Liên hệ', 'url' => BASE_URL . '/lien-he.php'],
        ],
    ],
    [
        'title' => 'Tài khoản',
        'desc' => 'Đăng nhập, đăng ký và quản lý thông tin.',
        'links' => [
            ['label' => 'Đăng nhập', 'url' => BASE_URL . '/login.php'],
            ['label' => 'Đăng ký', 'url' => BASE_URL . '/register.php'],
            ['label' => 'Hồ sơ cá nhân', 'url' => BASE_URL . '/profile.php'],
            ['label' => 'FAQ', 'url' => BASE_URL . '/faq.php'],
        ],
    ],
    [
        'title' => 'Thông tin pháp lý',
        'desc' => 'Điều khoản và chính sách vận hành website.',
        'links' => [
            ['label' => 'Chính sách bảo mật', 'url' => BASE_URL . '/privacy-policy.php'],
            ['label' => 'Điều khoản sử dụng', 'url' => BASE_URL . '/terms.php'],
            ['label' => 'Sơ đồ website', 'url' => BASE_URL . '/sitemap.php'],
        ],
    ],
];

if ($isAdmin) {
    $groups[] = [
        'title' => 'Quản trị',
        'desc' => 'Khu vực vận hành nội bộ dành cho tài khoản admin.',
        'links' => [
            ['label' => 'Quản lý sản phẩm', 'url' => BASE_URL . '/manage_products.php'],
            ['label' => 'Quản lý đơn hàng', 'url' => BASE_URL . '/admin_orders.php'],
            ['label' => 'Quản lý người dùng', 'url' => BASE_URL . '/admin_users.php'],
        ],
    ];
}

include __DIR__ . '/includes/header.php';
?>
<section class="sitemap-hero">
    <p>Sơ đồ website</p>
    <h1>Đi đến đúng trang chỉ trong 1 cú nhấp</h1>
    <span>Toàn bộ khu vực quan trọng của TMH Perfume được nhóm theo từng mục đích sử dụng.</span>
</section>

<section class="sitemap-grid">
    <?php foreach ($groups as $group): ?>
        <article class="sitemap-card">
            <h2><?= e($group['title']) ?></h2>
            <p><?= e($group['desc']) ?></p>
            <ul>
                <?php foreach ($group['links'] as $link): ?>
                    <li><a href="<?= e($link['url']) ?>"><?= e($link['label']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </article>
    <?php endforeach; ?>
</section>
<?php include __DIR__ . '/includes/footer.php';
