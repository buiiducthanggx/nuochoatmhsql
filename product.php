<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

if (is_post() && (string)($_POST['action'] ?? '') === 'add_review') {
    require_login();
    $productId = (int)($_POST['product_id'] ?? 0);
    $rating = max(1, min(5, (int)($_POST['rating'] ?? 5)));
    $comment = trim((string)($_POST['comment'] ?? ''));
    $user = current_user();

    if ($productId <= 0 || $comment === '') {
        $_SESSION['flash_error'] = 'Vui lòng nhập nội dung đánh giá hợp lệ.';
        redirect('product.php?id=' . $productId);
    }

    $stmt = db()->prepare('INSERT INTO product_reviews(product_id, customer_id, reviewer_name, rating, comment) VALUES(:product_id, :customer_id, :reviewer_name, :rating, :comment)');
    $stmt->execute([
        'product_id' => $productId,
        'customer_id' => $user['id'] ?? null,
        'reviewer_name' => $user['full_name'] ?? 'Khách hàng',
        'rating' => $rating,
        'comment' => $comment,
    ]);

    $_SESSION['flash_success'] = 'Cảm ơn bạn đã gửi đánh giá sản phẩm.';
    redirect('product.php?id=' . $productId);
}

$id = (int)($_GET['id'] ?? 0);
$product = $id > 0 ? find_product($id) : null;

if (!$product) {
    $_SESSION['flash_error'] = 'Sản phẩm không tồn tại.';
    redirect('index.php');
}

$gallery = parse_gallery_urls($product['gallery_urls'] ?? null, $product['image_url'] ?? null);
$mainImage = $gallery[0] ?? 'https://images.unsplash.com/photo-1541643600914-78b084683601?q=80&w=800&auto=format&fit=crop';
$reviewStmt = db()->prepare('SELECT reviewer_name, rating, comment, created_at FROM product_reviews WHERE product_id = :id ORDER BY id DESC');
$reviewStmt->execute(['id' => $id]);
$reviews = $reviewStmt->fetchAll();

$relatedSql = 'SELECT id, name, price, old_price, image_url FROM products WHERE is_active = 1 AND id <> :id';
$relatedParams = ['id' => $id];
if (!empty($product['category_name'])) {
    $relatedSql .= ' AND category_name = :category_name';
    $relatedParams['category_name'] = $product['category_name'];
}
$relatedSql .= ' ORDER BY id DESC LIMIT 4';
$relatedStmt = db()->prepare($relatedSql);
$relatedStmt->execute($relatedParams);
$relatedProducts = $relatedStmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<div class="shop-main">
    <article class="single-product card woocommerce-like">
        <div class="woocommerce-product-gallery">
            <a class="gallery-main-link" href="<?= e($mainImage) ?>" target="_blank" rel="noopener noreferrer">
                <img id="mainProductImage" class="gallery-main-image" src="<?= e($mainImage) ?>" alt="<?= e($product['name']) ?>">
            </a>

            <?php if (count($gallery) > 1): ?>
                <div class="gallery-thumbs">
                    <?php foreach (array_slice($gallery, 0, 6) as $index => $g): ?>
                        <button
                            class="gallery-thumb <?= $index === 0 ? 'is-active' : '' ?>"
                            type="button"
                            data-gallery-thumb
                            data-image="<?= e($g) ?>"
                            aria-label="Xem ảnh sản phẩm">
                            <img src="<?= e($g) ?>" alt="Ảnh sản phẩm">
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="summary entry-summary">
            <h1 class="product_title entry-title"><?= e($product['name']) ?></h1>

            <p class="single-price">
                <?php if (!empty($product['old_price']) && (float)$product['old_price'] > (float)$product['price']): ?>
                    <del><?= number_format((float)$product['old_price'], 0, ',', '.') ?> đ</del>
                <?php endif; ?>
                <ins><?= number_format((float)$product['price'], 0, ',', '.') ?> đ</ins>
            </p>

            <div class="woocommerce-product-details__short-description">
                <p><?= e((string)($product['promo_text'] ?: 'Hương thơm cao cấp, phù hợp cho nhiều dịp sử dụng.')) ?></p>
            </div>

            <form class="single-cart-form" action="<?= url('add_to_cart.php') ?>" method="post">
                <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">

                <div class="single-options">
                    <div>
                        <label>Màu sắc</label>
                        <input type="text" name="selected_color" value="<?= e((string)($product['color_name'] ?? '')) ?>">
                    </div>
                    <div>
                        <label>Dung tích / size</label>
                        <input type="text" name="selected_size" value="<?= e((string)($product['size_name'] ?? '')) ?>">
                    </div>
                </div>

                <div class="quantity" data-qty-wrap>
                    <button type="button" class="qty-minus" aria-label="Giảm số lượng">-</button>
                    <input type="number" class="qty-input" name="qty" min="1" value="1" aria-label="Số lượng sản phẩm">
                    <button type="button" class="qty-plus" aria-label="Tăng số lượng">+</button>
                </div>

                <button class="btn single_add_to_cart_button" type="submit">Thêm vào giỏ hàng</button>
            </form>

            <div class="product_meta">
                <span class="posted_in">Danh mục: <?= e((string)($product['category_name'] ?? 'Đang cập nhật')) ?></span>
                <span class="posted_in">Thương hiệu: <?= e((string)($product['brand_name'] ?? 'Đang cập nhật')) ?></span>
            </div>

            <div class="single-policy">
                <h3>Chính sách</h3>
                <ul>
                    <li>Miễn phí vận chuyển cho đơn từ 500.000đ.</li>
                    <li>Đổi trả trong 7 ngày nếu lỗi từ nhà sản xuất.</li>
                    <li>Hỗ trợ tư vấn mùi hương 24/7 qua live chat.</li>
                </ul>
            </div>
        </div>
    </article>

    <div class="woocommerce-tabs wc-tabs-wrapper card">
        <ul class="tabs wc-tabs" role="tablist">
            <li class="description_tab active" role="presentation">
                <a href="#tab-description" role="tab" aria-selected="true">Mô tả</a>
            </li>
        </ul>
        <div id="tab-description" class="woocommerce-Tabs-panel woocommerce-Tabs-panel--description panel entry-content wc-tab" role="tabpanel">
            <h2>Mô tả</h2>
            <p><?= nl2br(e((string)($product['description'] ?? ''))) ?></p>
        </div>
    </div>

    <section class="related products card">
        <h2>Sản phẩm tương tự</h2>
        <div class="grid">
            <?php foreach ($relatedProducts as $item): ?>
                <?php $itemOldPrice = isset($item['old_price']) ? (float)$item['old_price'] : 0; ?>
                <article class="card product-card">
                    <a class="product-media" href="<?= url('product.php?id=' . (int)$item['id']) ?>">
                        <img src="<?= e($item['image_url'] ?: 'https://images.unsplash.com/photo-1541643600914-78b084683601?q=80&w=800&auto=format&fit=crop') ?>" alt="<?= e($item['name']) ?>">
                        <?php if ($itemOldPrice > (float)$item['price']): ?>
                            <span class="product-badge">Giảm <?= (int)round((1 - ((float)$item['price'] / $itemOldPrice)) * 100) ?>%</span>
                        <?php endif; ?>
                        <div class="product-overlay">
                            <h3><?= e($item['name']) ?></h3>
                            <p class="product-price-row">
                                <?php if ($itemOldPrice > (float)$item['price']): ?>
                                    <span class="price-old"><?= number_format($itemOldPrice, 0, ',', '.') ?> đ</span>
                                <?php endif; ?>
                                <span class="price-new"><?= number_format((float)$item['price'], 0, ',', '.') ?> đ</span>
                            </p>
                        </div>
                    </a>
                    <form action="<?= url('add_to_cart.php') ?>" method="post" class="quick-add-form quick-add-form--button">
                        <input type="hidden" name="product_id" value="<?= (int)$item['id'] ?>">
                        <input type="hidden" name="qty" value="1">
                        <button class="btn btn-cart" type="submit">Thêm vào giỏ hàng</button>
                    </form>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="single-reviews card">
        <h2>Đánh giá khách hàng</h2>
        <?php if (!$reviews): ?>
            <p>Chưa có đánh giá nào cho sản phẩm này.</p>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review-item">
                    <strong><?= e($review['reviewer_name']) ?></strong>
                    <p><?= str_repeat('★', (int)$review['rating']) ?></p>
                    <p><?= e($review['comment']) ?></p>
                    <small><?= e($review['created_at']) ?></small>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <?php if (current_user()): ?>
            <form method="post" class="review-form">
                <input type="hidden" name="action" value="add_review">
                <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
                <label>Đánh giá (1-5 sao)</label>
                <input type="number" name="rating" min="1" max="5" value="5" required>
                <label>Nhận xét</label>
                <textarea name="comment" rows="4" required></textarea>
                <button class="btn" type="submit">Gửi đánh giá</button>
            </form>
        <?php else: ?>
            <p><a href="<?= url('login.php') ?>">Đăng nhập</a> để viết đánh giá.</p>
        <?php endif; ?>
    </section>
</div>
<?php include __DIR__ . '/includes/footer.php';
