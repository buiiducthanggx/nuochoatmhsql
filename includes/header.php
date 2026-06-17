<?php
$user = current_user();
$isHome = basename((string)($_SERVER['PHP_SELF'] ?? '')) === 'index.php';
$searchValue = trim((string)($_GET['q'] ?? ''));
$meta = page_meta();
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($meta['title']) ?></title>
    <meta name="description" content="<?= e($meta['description']) ?>">
    <meta property="og:title" content="<?= e($meta['title']) ?>">
    <meta property="og:description" content="<?= e($meta['description']) ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= e(current_url()) ?>">
    <link rel="stylesheet" href="<?= BASE_URL === '/' ? '/assets/style.css' : BASE_URL . '/assets/style.css' ?>">
</head>
<body>
<div class="site-topbar">
    <div class="container topbar-row">
        <div class="topbar-left">Hotline: 0355 152 212</div>
        <div class="topbar-right">Miễn phí vận chuyển đơn từ 500.000đ</div>
    </div>
</div>
<header class="site-header">
    <div class="container header-main">
        <a class="logo" href="<?= url('index.php') ?>">TMH <span>Perfume</span></a>

        <form class="header-search" action="<?= url('index.php') ?>" method="get">
            <input id="searchInput" autocomplete="off" type="search" name="q" placeholder="Tìm kiếm nước hoa, thương hiệu..." value="<?= e($searchValue) ?>">
            <button class="btn header-search-btn" type="submit" aria-label="Tìm kiếm">
                <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path d="M15.5 14h-.79l-.28-.27A6.5 6.5 0 1 0 14 15.5l.27.28v.79L20 22l2-2-6.5-6zM9.5 14A4.5 4.5 0 1 1 14 9.5 4.5 4.5 0 0 1 9.5 14z"></path>
                </svg>
            </button>
            <ul id="searchSuggestions" class="search-suggestions"></ul>
        </form>

        <nav class="nav quick-nav">
            <a href="<?= url('cart.php') ?>">Giỏ hàng (<?= cart_count() ?>)</a>
            <?php if ($user): ?>
                <a href="<?= url('my_orders.php') ?>">Đơn của tôi</a>
                <a href="<?= url('profile.php') ?>" class="account-link">
                    <?php if ($avatarUrl = user_avatar_url($user)): ?>
                        <img class="account-avatar-image" src="<?= e($avatarUrl) ?>" alt="Avatar">
                    <?php else: ?>
                        <span class="account-avatar-fallback"><?= e(user_initial((string)($user['full_name'] ?? 'U'))) ?></span>
                    <?php endif; ?>
                    <span>Tài khoản</span>
                </a>
                <?php if (is_admin()): ?>
                    <a href="<?= url('manage_products.php') ?>">Quản trị</a>
                    <a href="<?= url('admin_orders.php') ?>">Đơn hàng</a>
                    <a href="<?= url('admin_users.php') ?>">Người dùng</a>
                <?php endif; ?>
                <a href="<?= url('logout.php') ?>">Đăng xuất</a>
            <?php else: ?>
                <a href="<?= url('auth.php?mode=login') ?>">Đăng nhập</a>
            <?php endif; ?>
        </nav>
        <button id="menuToggle" class="menu-toggle" type="button" aria-label="Mở menu">Menu</button>
    </div>

    <div class="nav-secondary-wrap">
        <nav id="mainNav" class="container nav nav-secondary">
            <a href="<?= url('index.php') ?>">Trang chủ</a>
            <a href="<?= url('gioi-thieu.php') ?>">Giới thiệu</a>
            <a href="<?= url('category.php') ?>">Danh mục</a>
            <a href="<?= url('index.php') ?>">Sản phẩm</a>
            <a href="<?= url('blog.php') ?>">Blog</a>
            <a href="<?= url('lien-he.php') ?>">Liên hệ</a>
            <a href="<?= url('faq.php') ?>">FAQ</a>
        </nav>
    </div>
</header>

<?php if ($isHome): ?>
<section class="header-banner">
    <div class="container header-banner-inner">
        <div>
            <strong>Flash Sale Cuối Tuần:</strong> Giảm đến 30% cho các dòng nước hoa bán chạy.
        </div>
        <a class="btn light" href="<?= url('index.php') ?>#san-pham">Xem ưu đãi</a>
    </div>
</section>
<?php endif; ?>
<main class="container">
<?php if ($ok = flash('flash_success')): ?>
    <div class="alert success"><?= e($ok) ?></div>
<?php endif; ?>
<?php if ($err = flash('flash_error')): ?>
    <div class="alert error"><?= e($err) ?></div>
<?php endif; ?>
