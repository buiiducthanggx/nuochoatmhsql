<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

require_admin();
$pdo = db();

if (is_post()) {
    $action = (string)($_POST['action'] ?? '');

    if ($action === 'bulk_upload') {
        if (!isset($_FILES['products_file']) || (int)($_FILES['products_file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Vui lòng chọn file CSV hợp lệ để tải lên.';
            redirect('manage_products.php');
        }

        $tmpFile = (string)($_FILES['products_file']['tmp_name'] ?? '');
        $originalName = strtolower((string)($_FILES['products_file']['name'] ?? ''));

        if ($tmpFile === '' || !is_uploaded_file($tmpFile) || pathinfo($originalName, PATHINFO_EXTENSION) !== 'csv') {
            $_SESSION['flash_error'] = 'Chỉ hỗ trợ file .csv cho chức năng tải lên sản phẩm.';
            redirect('manage_products.php');
        }

        $handle = fopen($tmpFile, 'r');
        if (!$handle) {
            $_SESSION['flash_error'] = 'Không thể đọc file tải lên.';
            redirect('manage_products.php');
        }

        $firstRow = fgetcsv($handle);
        if ($firstRow === false) {
            fclose($handle);
            $_SESSION['flash_error'] = 'File CSV rỗng.';
            redirect('manage_products.php');
        }

        $header = array_map(static fn($v) => strtolower(trim((string)$v)), $firstRow);
        $expected = ['name', 'price', 'old_price', 'promo_text', 'category_name', 'brand_name', 'color_name', 'size_name', 'image_url', 'gallery_urls', 'video_url', 'description', 'is_active'];
        $hasHeader = $header === $expected;

        $insertStmt = $pdo->prepare('INSERT INTO products(name, description, price, old_price, promo_text, category_name, brand_name, color_name, size_name, image_url, gallery_urls, video_url, is_active) VALUES(:name, :description, :price, :old_price, :promo_text, :category_name, :brand_name, :color_name, :size_name, :image_url, :gallery_urls, :video_url, :is_active)');
        $created = 0;
        $skipped = 0;

        if (!$hasHeader) {
            $data = $firstRow;
            $name = trim((string)($data[0] ?? ''));
            $price = (float)($data[1] ?? 0);
            $oldPrice = isset($data[2]) && $data[2] !== '' ? (float)$data[2] : null;
            $promoText = trim((string)($data[3] ?? ''));
            $categoryName = trim((string)($data[4] ?? ''));
            $brandName = trim((string)($data[5] ?? ''));
            $colorName = trim((string)($data[6] ?? ''));
            $sizeName = trim((string)($data[7] ?? ''));
            $imageUrl = trim((string)($data[8] ?? ''));
            $galleryUrls = trim((string)($data[9] ?? ''));
            $videoUrl = trim((string)($data[10] ?? ''));
            $description = trim((string)($data[11] ?? ''));
            $isActive = ((int)($data[12] ?? 1)) === 1 ? 1 : 0;

            if ($name !== '' && $price > 0) {
                $insertStmt->execute([
                    'name' => $name,
                    'description' => $description,
                    'price' => $price,
                    'old_price' => $oldPrice,
                    'promo_text' => $promoText,
                    'category_name' => $categoryName,
                    'brand_name' => $brandName,
                    'color_name' => $colorName,
                    'size_name' => $sizeName,
                    'image_url' => $imageUrl,
                    'gallery_urls' => $galleryUrls,
                    'video_url' => $videoUrl,
                    'is_active' => $isActive,
                ]);
                $created++;
            } else {
                $skipped++;
            }
        }

        while (($data = fgetcsv($handle)) !== false) {
            $name = trim((string)($data[0] ?? ''));
            // Remove commas from price values (e.g., "2,850,000" -> "2850000")
            $priceStr = trim((string)($data[1] ?? '0'));
            $priceStr = str_replace(',', '', $priceStr);
            $price = (float)$priceStr;
            
            $oldPriceStr = isset($data[2]) && $data[2] !== '' ? trim((string)$data[2]) : '';
            $oldPriceStr = str_replace(',', '', $oldPriceStr);
            $oldPrice = $oldPriceStr !== '' ? (float)$oldPriceStr : null;
            
            $promoText = trim((string)($data[3] ?? ''));
            $categoryName = trim((string)($data[4] ?? ''));
            $brandName = trim((string)($data[5] ?? ''));
            $colorName = trim((string)($data[6] ?? ''));
            $sizeName = trim((string)($data[7] ?? ''));
            $imageUrl = trim((string)($data[8] ?? ''));
            $galleryUrls = trim((string)($data[9] ?? ''));
            $videoUrl = trim((string)($data[10] ?? ''));
            $description = trim((string)($data[11] ?? ''));
            $isActive = ((int)($data[12] ?? 1)) === 1 ? 1 : 0;

            // Validate price is in valid range for DECIMAL(12,2)
            if ($name === '' || $price <= 0 || $price > 9999999.99) {
                $skipped++;
                continue;
            }
            
            // Validate old_price if provided
            if ($oldPrice !== null && ($oldPrice <= 0 || $oldPrice > 9999999.99)) {
                $skipped++;
                continue;
            }

            try {
                $insertStmt->execute([
                    'name' => $name,
                    'description' => $description,
                    'price' => round($price, 2),
                    'old_price' => $oldPrice !== null ? round($oldPrice, 2) : null,
                    'promo_text' => $promoText,
                    'category_name' => $categoryName,
                    'brand_name' => $brandName,
                    'color_name' => $colorName,
                    'size_name' => $sizeName,
                    'image_url' => $imageUrl,
                    'gallery_urls' => $galleryUrls,
                    'video_url' => $videoUrl,
                    'is_active' => $isActive,
                ]);

                $created++;
            } catch (Throwable $e) {
                $skipped++;
            }
        }

        fclose($handle);

        $_SESSION['flash_success'] = 'Tải lên thành công: ' . $created . ' sản phẩm. Bỏ qua: ' . $skipped . ' dòng không hợp lệ.';
        redirect('manage_products.php');
    }

    if ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            try {
                $pdo->beginTransaction();

                // Xóa các order_items liên quan (tự động xóa do foreign key ON DELETE CASCADE)
                $deleteItems = $pdo->prepare('DELETE FROM order_items WHERE product_id = :id');
                $deleteItems->execute(['id' => $id]);

                // Xóa sản phẩm
                $stmt = $pdo->prepare('DELETE FROM products WHERE id = :id');
                $stmt->execute(['id' => $id]);

                $pdo->commit();
                $_SESSION['flash_success'] = 'Đã xóa sản phẩm thành công.';
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                $_SESSION['flash_error'] = 'Xóa sản phẩm thất bại. Vui lòng thử lại.';
            }
        }
        redirect('manage_products.php');
    }

    if ($action === 'create' || $action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim((string)($_POST['name'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $imageUrl = trim((string)($_POST['image_url'] ?? ''));
        $galleryUrls = trim((string)($_POST['gallery_urls'] ?? ''));
        $videoUrl = trim((string)($_POST['video_url'] ?? ''));
        $price = (float)($_POST['price'] ?? 0);
        $oldPrice = trim((string)($_POST['old_price'] ?? ''));
        $promoText = trim((string)($_POST['promo_text'] ?? ''));
        $categoryName = trim((string)($_POST['category_name'] ?? ''));
        $brandName = trim((string)($_POST['brand_name'] ?? ''));
        $colorName = trim((string)($_POST['color_name'] ?? ''));
        $sizeName = trim((string)($_POST['size_name'] ?? ''));
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($name === '' || $price <= 0) {
            $_SESSION['flash_error'] = 'Tên sản phẩm và giá bán là bắt buộc hợp lệ.';
            redirect('manage_products.php' . ($id > 0 ? ('?edit=' . $id) : ''));
        }

        if ($action === 'create') {
            $stmt = $pdo->prepare('INSERT INTO products(name, description, price, old_price, promo_text, category_name, brand_name, color_name, size_name, image_url, gallery_urls, video_url, is_active) VALUES(:name, :description, :price, :old_price, :promo_text, :category_name, :brand_name, :color_name, :size_name, :image_url, :gallery_urls, :video_url, :is_active)');
            $stmt->execute([
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'old_price' => $oldPrice === '' ? null : (float)$oldPrice,
                'promo_text' => $promoText,
                'category_name' => $categoryName,
                'brand_name' => $brandName,
                'color_name' => $colorName,
                'size_name' => $sizeName,
                'image_url' => $imageUrl,
                'gallery_urls' => $galleryUrls,
                'video_url' => $videoUrl,
                'is_active' => $isActive,
            ]);
            $_SESSION['flash_success'] = 'Thêm sản phẩm thành công.';
            redirect('manage_products.php');
        }

        if ($id > 0) {
            $stmt = $pdo->prepare('UPDATE products SET name = :name, description = :description, price = :price, old_price = :old_price, promo_text = :promo_text, category_name = :category_name, brand_name = :brand_name, color_name = :color_name, size_name = :size_name, image_url = :image_url, gallery_urls = :gallery_urls, video_url = :video_url, is_active = :is_active WHERE id = :id');
            $stmt->execute([
                'id' => $id,
                'name' => $name,
                'description' => $description,
                'price' => $price,
                'old_price' => $oldPrice === '' ? null : (float)$oldPrice,
                'promo_text' => $promoText,
                'category_name' => $categoryName,
                'brand_name' => $brandName,
                'color_name' => $colorName,
                'size_name' => $sizeName,
                'image_url' => $imageUrl,
                'gallery_urls' => $galleryUrls,
                'video_url' => $videoUrl,
                'is_active' => $isActive,
            ]);
            $_SESSION['flash_success'] = 'Cập nhật sản phẩm thành công.';
        }

        redirect('manage_products.php');
    }
}

$editId = (int)($_GET['edit'] ?? 0);
$editing = null;

if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $editId]);
    $editing = $stmt->fetch() ?: null;
}

$products = $pdo->query('SELECT * FROM products ORDER BY id DESC')->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<section class="admin-wrap">
    <div class="admin-card">
        <h1><?= $editing ? 'Sửa sản phẩm' : 'Thêm sản phẩm mới' ?></h1>
        <form method="post">
            <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>">
            <?php if ($editing): ?>
                <input type="hidden" name="id" value="<?= (int)$editing['id'] ?>">
            <?php endif; ?>

            <label>Tên sản phẩm *</label>
            <input type="text" name="name" required value="<?= e($editing['name'] ?? '') ?>">

            <label>Giá (VNĐ) *</label>
            <input type="number" min="1000" step="1000" name="price" required value="<?= e((string)($editing['price'] ?? '')) ?>">

            <label>Giá gốc</label>
            <input type="number" min="0" step="1000" name="old_price" value="<?= e((string)($editing['old_price'] ?? '')) ?>">

            <label>Nội dung khuyến mãi</label>
            <input type="text" name="promo_text" value="<?= e((string)($editing['promo_text'] ?? '')) ?>">

            <label>Danh mục</label>
            <input type="text" name="category_name" value="<?= e((string)($editing['category_name'] ?? '')) ?>">

            <label>Thương hiệu</label>
            <input type="text" name="brand_name" value="<?= e((string)($editing['brand_name'] ?? '')) ?>">

            <label>Màu sắc</label>
            <input type="text" name="color_name" value="<?= e((string)($editing['color_name'] ?? '')) ?>">

            <label>Dung tích / size</label>
            <input type="text" name="size_name" value="<?= e((string)($editing['size_name'] ?? '')) ?>">

            <label>Link hình ảnh</label>
            <input type="text" name="image_url" value="<?= e($editing['image_url'] ?? '') ?>">

            <label>Gallery ảnh (cách nhau bằng dấu |)</label>
            <input type="text" name="gallery_urls" value="<?= e((string)($editing['gallery_urls'] ?? '')) ?>">

            <label>Video URL (YouTube embed)</label>
            <input type="text" name="video_url" value="<?= e((string)($editing['video_url'] ?? '')) ?>">

            <label>Mô tả</label>
            <textarea name="description" rows="4"><?= e($editing['description'] ?? '') ?></textarea>

            <label style="display:flex; gap:8px; align-items:center;">
                <input style="width:auto; margin:0;" type="checkbox" name="is_active" <?= (!isset($editing['is_active']) || (int)$editing['is_active'] === 1) ? 'checked' : '' ?>>
                Hiển thị trên cửa hàng
            </label>

            <div class="admin-actions">
                <button class="btn" type="submit"><?= $editing ? 'Lưu thay đổi' : 'Thêm sản phẩm' ?></button>
                <?php if ($editing): ?>
                    <a class="btn light" href="<?= url('manage_products.php') ?>">Hủy sửa</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="admin-card">
        <h2>Danh sách sản phẩm</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Danh mục</th>
                    <th>Thương hiệu</th>
                    <th>Giá</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= (int)$product['id'] ?></td>
                    <td><?= e($product['name']) ?></td>
                    <td><?= e((string)($product['category_name'] ?? '')) ?></td>
                    <td><?= e((string)($product['brand_name'] ?? '')) ?></td>
                    <td><?= number_format((float)$product['price'], 0, ',', '.') ?> đ</td>
                    <td><?= (int)$product['is_active'] === 1 ? 'Đang bán' : 'Ẩn' ?></td>
                    <td class="inline-actions">
                        <a class="btn light" href="<?= url('manage_products.php?edit=' . (int)$product['id']) ?>">Sửa</a>
                        <form method="post" onsubmit="return confirm('Bạn chắc chắn muốn xóa sản phẩm này?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= (int)$product['id'] ?>">
                            <button class="btn danger" type="submit">Xóa</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="admin-wrap" style="margin-top:12px;">
    <div class="admin-card">
        <h2>Tải lên sản phẩm hàng loạt</h2>
        <p>Dùng file CSV để thêm sản phẩm mà không cần nhập tay từng mục.</p>
        <p>Định dạng cột chuẩn: <strong>name,price,old_price,promo_text,category_name,brand_name,color_name,size_name,image_url,gallery_urls,video_url,description,is_active</strong></p>
        <p>Ví dụ: <em>Dior Sauvage,2850000,3200000,Giảm 10%,Nước hoa nam,Dior,Đen,100ml,https://img.jpg,https://g1.jpg|https://g2.jpg,https://youtube.com/embed/...,Mùi hương nam tính,1</em></p>

        <form method="post" enctype="multipart/form-data">
            <input type="hidden" name="action" value="bulk_upload">
            <input type="file" name="products_file" accept=".csv,text/csv" required>
            <button class="btn" type="submit">Tải lên sản phẩm</button>
        </form>
    </div>
</section>
<?php include __DIR__ . '/includes/footer.php';
