<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

function slugify_option(string $text): string
{
    $text = trim(mb_strtolower($text, 'UTF-8'));
    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    if ($ascii !== false) {
        $text = $ascii;
    }
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text) ?? '';
    return trim($text, '-');
}

function build_slug_map(array $rows, string $key): array
{
    $map = [];
    foreach ($rows as $row) {
        $name = trim((string)($row[$key] ?? ''));
        if ($name === '') {
            continue;
        }

        $base = slugify_option($name);
        if ($base === '') {
            continue;
        }

        $slug = $base;
        $i = 2;
        while (isset($map[$slug]) && $map[$slug] !== $name) {
            $slug = $base . '-' . $i;
            $i++;
        }
        $map[$slug] = $name;
    }

    return $map;
}

$categoryRows = db()->query('SELECT DISTINCT category_name FROM products WHERE is_active = 1 AND category_name IS NOT NULL AND category_name <> "" ORDER BY category_name ASC')->fetchAll();
$brandRows = db()->query('SELECT DISTINCT brand_name FROM products WHERE is_active = 1 AND brand_name IS NOT NULL AND brand_name <> "" ORDER BY brand_name ASC')->fetchAll();
$sizeRows = db()->query('SELECT DISTINCT size_name FROM products WHERE is_active = 1 AND size_name IS NOT NULL AND size_name <> "" ORDER BY size_name ASC')->fetchAll();
$hasScentType = (bool)db()->query("SHOW COLUMNS FROM products LIKE 'scent_type'")->fetch();

$scentRows = [];
if ($hasScentType) {
    $scentRows = db()->query('SELECT DISTINCT scent_type FROM products WHERE is_active = 1 AND scent_type IS NOT NULL AND scent_type <> "" ORDER BY scent_type ASC')->fetchAll();
}

$categorySlugMap = build_slug_map($categoryRows, 'category_name');
$brandSlugMap = build_slug_map($brandRows, 'brand_name');

$sizeOptions = [];
foreach ($sizeRows as $row) {
    $value = trim((string)($row['size_name'] ?? ''));
    if ($value !== '') {
        $sizeOptions[$value] = $value;
    }
}

$defaultSizes = ['10ml', '50ml', '100ml', '200ml'];
foreach ($defaultSizes as $defaultSize) {
    if (!isset($sizeOptions[$defaultSize])) {
        $sizeOptions[$defaultSize] = $defaultSize;
    }
}

$scentOptions = [];
if ($hasScentType) {
    foreach ($scentRows as $row) {
        $value = trim((string)($row['scent_type'] ?? ''));
        if ($value !== '') {
            $scentOptions[$value] = $value;
        }
    }
}
if (!$scentOptions) {
    $scentOptions = [
        'Hương gỗ' => 'Hương gỗ',
        'Hương biển' => 'Hương biển',
        'Hương hoa' => 'Hương hoa',
        'Hương trầm' => 'Hương trầm',
    ];
}

$filters = [
    'category_slug' => trim((string)($_GET['category_slug'] ?? '')),
    'brand_slug' => trim((string)($_GET['brand_slug'] ?? '')),
    'size' => trim((string)($_GET['size'] ?? '')),
    'scent_type' => trim((string)($_GET['scent_type'] ?? '')),
    'price_range' => trim((string)($_GET['price_range'] ?? '')),
    'q' => trim((string)($_GET['q'] ?? '')),
    'min' => (float)($_GET['min'] ?? 0),
    'max' => (float)($_GET['max'] ?? 0),
];

$selectedCategory = $filters['category_slug'] !== '' ? ($categorySlugMap[$filters['category_slug']] ?? '') : '';
$selectedBrand = $filters['brand_slug'] !== '' ? ($brandSlugMap[$filters['brand_slug']] ?? '') : '';

$sql = 'SELECT * FROM products WHERE is_active = 1';
$params = [];

if ($selectedCategory !== '') {
    $sql .= ' AND category_name = :category';
    $params['category'] = $selectedCategory;
}
if ($selectedBrand !== '') {
    $sql .= ' AND brand_name = :brand';
    $params['brand'] = $selectedBrand;
}
if ($filters['size'] !== '') {
    $sql .= ' AND size_name LIKE :size';
    $params['size'] = '%' . $filters['size'] . '%';
}
if ($filters['q'] !== '') {
    $sql .= ' AND (name LIKE :q OR description LIKE :q OR brand_name LIKE :q)';
    $params['q'] = '%' . $filters['q'] . '%';
}
if ($filters['price_range'] === 'under_3m') {
    $sql .= ' AND price < 3000000';
}
if ($filters['price_range'] === 'from_3m_to_10m') {
    $sql .= ' AND price BETWEEN 3000000 AND 10000000';
}
if ($filters['price_range'] === 'above_10m') {
    $sql .= ' AND price > 10000000';
}
if ($filters['min'] > 0) {
    $sql .= ' AND price >= :min';
    $params['min'] = $filters['min'];
}
if ($filters['max'] > 0) {
    $sql .= ' AND price <= :max';
    $params['max'] = $filters['max'];
}
if ($filters['scent_type'] !== '') {
    if ($hasScentType) {
        $sql .= ' AND scent_type = :scent_type';
    } else {
        $sql .= ' AND color_name = :scent_type';
    }
    $params['scent_type'] = $filters['scent_type'];
}

$sql .= ' ORDER BY id DESC';

$stmt = db()->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>
<h1>Danh mục sản phẩm</h1>

<form id="categoryFilterForm" method="get" class="form-card" style="max-width:100%; margin-bottom:16px;">
    <input type="hidden" name="category_slug" id="filterCategorySlug" value="<?= e($filters['category_slug']) ?>">
    <input type="hidden" name="brand_slug" id="filterBrandSlug" value="<?= e($filters['brand_slug']) ?>">
    <input type="hidden" name="price_range" id="filterPriceRange" value="<?= e($filters['price_range']) ?>">
    <input type="hidden" name="size" id="filterSize" value="<?= e($filters['size']) ?>">
    <input type="hidden" name="scent_type" id="filterScentType" value="<?= e($filters['scent_type']) ?>">

    <div class="click-filter-wrap">
        <div class="click-filter-group">
            <h3>Danh mục</h3>
            <div class="click-filter-list">
                <button type="button" class="chip-btn <?= $filters['category_slug'] === '' ? 'is-active' : '' ?>" data-filter-target="filterCategorySlug" data-filter-value="">Tất cả</button>
                <?php foreach ($categorySlugMap as $slug => $name): ?>
                    <button type="button" class="chip-btn <?= $filters['category_slug'] === $slug ? 'is-active' : '' ?>" data-filter-target="filterCategorySlug" data-filter-value="<?= e($slug) ?>"><?= e($name) ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="click-filter-group">
            <h3>Thương hiệu</h3>
            <div class="click-filter-list">
                <button type="button" class="chip-btn <?= $filters['brand_slug'] === '' ? 'is-active' : '' ?>" data-filter-target="filterBrandSlug" data-filter-value="">Tất cả</button>
                <?php foreach ($brandSlugMap as $slug => $name): ?>
                    <button type="button" class="chip-btn <?= $filters['brand_slug'] === $slug ? 'is-active' : '' ?>" data-filter-target="filterBrandSlug" data-filter-value="<?= e($slug) ?>"><?= e($name) ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="click-filter-group">
            <h3>Khoảng giá</h3>
            <div class="click-filter-list">
                <button type="button" class="chip-btn <?= $filters['price_range'] === '' ? 'is-active' : '' ?>" data-filter-target="filterPriceRange" data-filter-value="">Tất cả</button>
                <button type="button" class="chip-btn <?= $filters['price_range'] === 'under_3m' ? 'is-active' : '' ?>" data-filter-target="filterPriceRange" data-filter-value="under_3m">Dưới 3 triệu</button>
                <button type="button" class="chip-btn <?= $filters['price_range'] === 'from_3m_to_10m' ? 'is-active' : '' ?>" data-filter-target="filterPriceRange" data-filter-value="from_3m_to_10m">3 - 10 triệu</button>
                <button type="button" class="chip-btn <?= $filters['price_range'] === 'above_10m' ? 'is-active' : '' ?>" data-filter-target="filterPriceRange" data-filter-value="above_10m">Trên 10 triệu</button>
            </div>
        </div>

        <div class="click-filter-group">
            <h3>Dung tích</h3>
            <div class="click-filter-list">
                <button type="button" class="chip-btn <?= $filters['size'] === '' ? 'is-active' : '' ?>" data-filter-target="filterSize" data-filter-value="">Tất cả</button>
                <?php foreach ($sizeOptions as $sizeValue): ?>
                    <button type="button" class="chip-btn <?= $filters['size'] === $sizeValue ? 'is-active' : '' ?>" data-filter-target="filterSize" data-filter-value="<?= e($sizeValue) ?>"><?= e($sizeValue) ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="click-filter-group">
            <h3>Nhóm hương</h3>
            <div class="click-filter-list">
                <button type="button" class="chip-btn <?= $filters['scent_type'] === '' ? 'is-active' : '' ?>" data-filter-target="filterScentType" data-filter-value="">Tất cả</button>
                <?php foreach ($scentOptions as $scentValue): ?>
                    <button type="button" class="chip-btn <?= $filters['scent_type'] === $scentValue ? 'is-active' : '' ?>" data-filter-target="filterScentType" data-filter-value="<?= e($scentValue) ?>"><?= e($scentValue) ?></button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="filter-grid">
        <input type="text" name="q" placeholder="Từ khóa" value="<?= e($filters['q']) ?>">
        <input type="number" name="min" placeholder="Giá từ" value="<?= $filters['min'] > 0 ? e((string)$filters['min']) : '' ?>">
        <input type="number" name="max" placeholder="Đến giá" value="<?= $filters['max'] > 0 ? e((string)$filters['max']) : '' ?>">

        <button class="btn" type="submit">Tìm kiếm</button>
        <button class="btn light" type="button" id="clearClickFilters">Bỏ chọn nhanh</button>
        <a class="btn light" href="<?= url('category.php') ?>">Xóa bộ lọc</a>
    </div>
</form>

<script>
(() => {
    const filterForm = document.getElementById('categoryFilterForm');
    const chips = document.querySelectorAll('.chip-btn[data-filter-target]');
    chips.forEach((chip) => {
        chip.addEventListener('click', () => {
            const fieldId = chip.getAttribute('data-filter-target');
            const value = chip.getAttribute('data-filter-value') || '';
            const target = document.getElementById(fieldId);
            if (!target) {
                return;
            }
            target.value = value;

            const group = chip.closest('.click-filter-list');
            if (group) {
                group.querySelectorAll('.chip-btn').forEach((btn) => btn.classList.remove('is-active'));
            }
            chip.classList.add('is-active');

            if (filterForm) {
                filterForm.requestSubmit();
            }
        });
    });

    const clearButton = document.getElementById('clearClickFilters');
    if (clearButton) {
        clearButton.addEventListener('click', () => {
            ['filterCategorySlug', 'filterBrandSlug', 'filterPriceRange', 'filterSize', 'filterScentType'].forEach((id) => {
                const el = document.getElementById(id);
                if (el) {
                    el.value = '';
                }
            });

            document.querySelectorAll('.click-filter-list').forEach((group) => {
                const buttons = group.querySelectorAll('.chip-btn');
                buttons.forEach((btn) => btn.classList.remove('is-active'));
                if (buttons.length > 0) {
                    buttons[0].classList.add('is-active');
                }
            });

            if (filterForm) {
                filterForm.requestSubmit();
            }
        });
    }
})();
</script>

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
<?php include __DIR__ . '/includes/footer.php';
