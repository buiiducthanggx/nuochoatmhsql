<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

$keyword = trim((string)($_GET['q'] ?? ''));

if ($keyword !== '') {
    $stmt = db()->prepare('SELECT * FROM products WHERE is_active = 1 AND (name LIKE :kw OR description LIKE :kw) ORDER BY id DESC');
    $stmt->execute(['kw' => '%' . $keyword . '%']);
    $products = $stmt->fetchAll();
} else {
    $products = all_products();
}

$bestSellers = db()->query('SELECT p.id, p.name, p.price, p.image_url, COALESCE(SUM(oi.quantity), 0) AS sold_count FROM products p LEFT JOIN order_items oi ON oi.product_id = p.id WHERE p.is_active = 1 GROUP BY p.id ORDER BY sold_count DESC, p.id DESC LIMIT 4')->fetchAll();
$hotCategories = db()->query('SELECT category_name, COUNT(*) AS total FROM products WHERE is_active = 1 AND category_name IS NOT NULL AND category_name <> "" GROUP BY category_name ORDER BY total DESC LIMIT 6')->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="hero-badge">✦ Bộ Sưu Tập Mới ✦</div>
    <h1>Khám phá hương thơm đẳng cấp</h1>
    <p>TMH Perfume mang đến trải nghiệm mua sắm nước hoa chính hãng với phong cách sang trọng như bản nuoc-hoa-tmh.</p>
    <div class="hero-stats">
        <div><strong>500+</strong><span>Sản phẩm</span></div>
        <div><strong>50+</strong><span>Thương hiệu</span></div>
        <div><strong>10K+</strong><span>Khách hàng</span></div>
    </div>
</section>

<section class="form-card" style="max-width:100%; margin-bottom:18px;">
    <h2>Trải nghiệm mua sắm tối ưu cho người yêu nước hoa</h2>
    <p>Giao diện được tối ưu cho máy tính, máy tính bảng và di động giúp bạn tìm kiếm nhanh, xem chi tiết rõ ràng và đặt hàng mượt mà chỉ trong vài bước.</p>
</section>

<section class="form-card" style="max-width:100%; margin-bottom:18px;">
    <h2>Danh mục bán chạy</h2>
    <div class="tag-list">
        <?php foreach ($hotCategories as $cat): ?>
            <a class="tag" href="<?= url('category.php?category=' . urlencode((string)$cat['category_name'])) ?>">
                <?= e((string)$cat['category_name']) ?> (<?= (int)$cat['total'] ?>)
            </a>
        <?php endforeach; ?>
    </div>
</section>

<h2 class="section-title" id="san-pham">
    <?= $keyword !== '' ? 'Kết quả tìm kiếm cho: "' . e($keyword) . '"' : 'Sản phẩm nổi bật' ?>
</h2>

<?php if ($keyword !== '' && !$products): ?>
    <p>Không tìm thấy sản phẩm phù hợp. Bạn thử từ khóa khác nhé.</p>
<?php endif; ?>

<div class="grid">
    <?php foreach ($products as $product): ?>
        <?php $oldPrice = isset($product['old_price']) ? (float)$product['old_price'] : 0; ?>
        <article class="card product-card">
            <a class="product-media" href="<?= url('product.php?id=' . (int)$product['id']) ?>">
                <img src="<?= e($product['image_url'] ?: 'https://images.unsplash.com/photo-1541643600914-78b084683601?q=80&w=800&auto=format&fit=crop') ?>" alt="<?= e($product['name']) ?>">
                <?php if ($oldPrice > (float)$product['price']): ?>
                    <span class="product-badge">Giảm <?= (int)round((1 - ((float)$product['price'] / $oldPrice)) * 100) ?>%</span>
                <?php endif; ?>
                <div class="product-overlay">
                    <h3><?= e($product['name']) ?></h3>
                    <p class="product-price-row">
                        <?php if ($oldPrice > (float)$product['price']): ?>
                            <span class="price-old"><?= number_format($oldPrice, 0, ',', '.') ?> đ</span>
                        <?php endif; ?>
                        <span class="price-new"><?= number_format((float)$product['price'], 0, ',', '.') ?> đ</span>
                    </p>
                </div>
            </a>
            <form action="<?= url('add_to_cart.php') ?>" method="post" class="quick-add-form quick-add-form--button">
                <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                <input type="hidden" name="qty" value="1">
                <button class="btn btn-cart" type="submit">Thêm vào giỏ hàng</button>
            </form>
        </article>
    <?php endforeach; ?>
</div>

<h2 class="section-title" style="margin-top:20px;">Sản phẩm bán chạy</h2>
<div class="grid">
    <?php foreach ($bestSellers as $product): ?>
        <article class="card product-card">
            <a class="product-media" href="<?= url('product.php?id=' . (int)$product['id']) ?>">
                <img src="<?= e($product['image_url'] ?: 'https://images.unsplash.com/photo-1541643600914-78b084683601?q=80&w=800&auto=format&fit=crop') ?>" alt="<?= e($product['name']) ?>">
                <span class="product-badge subtle">Đã bán <?= (int)$product['sold_count'] ?></span>
                <div class="product-overlay">
                    <h3><?= e($product['name']) ?></h3>
                    <p class="product-price-row">
                        <span class="price-new"><?= number_format((float)$product['price'], 0, ',', '.') ?> đ</span>
                    </p>
                </div>
            </a>
            <form action="<?= url('add_to_cart.php') ?>" method="post" class="quick-add-form quick-add-form--button">
                <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                <input type="hidden" name="qty" value="1">
                <button class="btn btn-cart" type="submit">Thêm vào giỏ hàng</button>
            </form>
        </article>
    <?php endforeach; ?>
</div>
<?php include __DIR__ . '/includes/footer.php';
