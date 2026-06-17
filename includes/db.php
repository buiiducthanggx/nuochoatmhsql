<?php
require_once __DIR__ . '/../config.php';

function init_schema(PDO $pdo): void
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS customers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(150) NOT NULL,
        email VARCHAR(190) NOT NULL UNIQUE,
        phone VARCHAR(30) DEFAULT NULL,
        avatar_path VARCHAR(255) DEFAULT NULL,
        password_hash VARCHAR(255) NOT NULL,
        role VARCHAR(20) NOT NULL DEFAULT "customer",
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

    // Backward-compatible migration for databases created before role support.
    $roleColumn = $pdo->query("SHOW COLUMNS FROM customers LIKE 'role'")->fetch();
    if (!$roleColumn) {
        $pdo->exec("ALTER TABLE customers ADD COLUMN role VARCHAR(20) NOT NULL DEFAULT 'customer' AFTER password_hash");
    }

    $addressColumn = $pdo->query("SHOW COLUMNS FROM customers LIKE 'address'")->fetch();
    if (!$addressColumn) {
        $pdo->exec("ALTER TABLE customers ADD COLUMN address VARCHAR(255) DEFAULT NULL AFTER phone");
    }

    $avatarPathColumn = $pdo->query("SHOW COLUMNS FROM customers LIKE 'avatar_path'")->fetch();
    if (!$avatarPathColumn) {
        $pdo->exec("ALTER TABLE customers ADD COLUMN avatar_path VARCHAR(255) DEFAULT NULL AFTER address");
    }

    $pdo->exec('CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(190) NOT NULL,
        description TEXT,
        price DECIMAL(12,2) NOT NULL DEFAULT 0,
        old_price DECIMAL(12,2) DEFAULT NULL,
        promo_text VARCHAR(255) DEFAULT NULL,
        category_name VARCHAR(120) DEFAULT NULL,
        brand_name VARCHAR(120) DEFAULT NULL,
        color_name VARCHAR(80) DEFAULT NULL,
        size_name VARCHAR(80) DEFAULT NULL,
        scent_type VARCHAR(80) DEFAULT NULL,
        image_url VARCHAR(255) DEFAULT NULL,
        gallery_urls TEXT,
        video_url VARCHAR(255) DEFAULT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

    $productColumns = [
        "ALTER TABLE products ADD COLUMN old_price DECIMAL(12,2) DEFAULT NULL AFTER price" => "SHOW COLUMNS FROM products LIKE 'old_price'",
        "ALTER TABLE products ADD COLUMN promo_text VARCHAR(255) DEFAULT NULL AFTER old_price" => "SHOW COLUMNS FROM products LIKE 'promo_text'",
        "ALTER TABLE products ADD COLUMN category_name VARCHAR(120) DEFAULT NULL AFTER promo_text" => "SHOW COLUMNS FROM products LIKE 'category_name'",
        "ALTER TABLE products ADD COLUMN brand_name VARCHAR(120) DEFAULT NULL AFTER category_name" => "SHOW COLUMNS FROM products LIKE 'brand_name'",
        "ALTER TABLE products ADD COLUMN color_name VARCHAR(80) DEFAULT NULL AFTER brand_name" => "SHOW COLUMNS FROM products LIKE 'color_name'",
        "ALTER TABLE products ADD COLUMN size_name VARCHAR(80) DEFAULT NULL AFTER color_name" => "SHOW COLUMNS FROM products LIKE 'size_name'",
        "ALTER TABLE products ADD COLUMN scent_type VARCHAR(80) DEFAULT NULL AFTER size_name" => "SHOW COLUMNS FROM products LIKE 'scent_type'",
        "ALTER TABLE products ADD COLUMN gallery_urls TEXT AFTER image_url" => "SHOW COLUMNS FROM products LIKE 'gallery_urls'",
        "ALTER TABLE products ADD COLUMN video_url VARCHAR(255) DEFAULT NULL AFTER gallery_urls" => "SHOW COLUMNS FROM products LIKE 'video_url'",
    ];

    foreach ($productColumns as $alter => $check) {
        if (!$pdo->query($check)->fetch()) {
            $pdo->exec($alter);
        }
    }

    $pdo->exec('CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_code VARCHAR(30) NOT NULL UNIQUE,
        customer_id INT NOT NULL,
        receiver_name VARCHAR(150) NOT NULL,
        receiver_phone VARCHAR(30) NOT NULL,
        shipping_address VARCHAR(255) NOT NULL,
        shipping_method VARCHAR(50) DEFAULT "standard",
        payment_method VARCHAR(50) DEFAULT "cod",
        payment_status VARCHAR(30) DEFAULT "unpaid",
        note TEXT,
        subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
        discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
        shipping_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
        total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
        status VARCHAR(30) NOT NULL DEFAULT "pending",
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_orders_customer FOREIGN KEY (customer_id) REFERENCES customers(id)
    ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

    $orderColumns = [
        "ALTER TABLE orders ADD COLUMN shipping_method VARCHAR(50) DEFAULT 'standard' AFTER shipping_address" => "SHOW COLUMNS FROM orders LIKE 'shipping_method'",
        "ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) DEFAULT 'cod' AFTER shipping_method" => "SHOW COLUMNS FROM orders LIKE 'payment_method'",
        "ALTER TABLE orders ADD COLUMN payment_status VARCHAR(30) DEFAULT 'unpaid' AFTER payment_method" => "SHOW COLUMNS FROM orders LIKE 'payment_status'",
        "ALTER TABLE orders ADD COLUMN discount_amount DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER subtotal" => "SHOW COLUMNS FROM orders LIKE 'discount_amount'",
    ];

    foreach ($orderColumns as $alter => $check) {
        if (!$pdo->query($check)->fetch()) {
            $pdo->exec($alter);
        }
    }

    $pdo->exec('CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        product_name VARCHAR(190) NOT NULL,
        product_price DECIMAL(12,2) NOT NULL,
        quantity INT NOT NULL,
        line_total DECIMAL(12,2) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id)
    ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

    $pdo->exec('CREATE TABLE IF NOT EXISTS newsletter_subscribers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(190) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

    $pdo->exec('CREATE TABLE IF NOT EXISTS contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        full_name VARCHAR(150) NOT NULL,
        email VARCHAR(190) NOT NULL,
        phone VARCHAR(30) DEFAULT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

    $pdo->exec('CREATE TABLE IF NOT EXISTS product_reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        customer_id INT DEFAULT NULL,
        reviewer_name VARCHAR(150) NOT NULL,
        rating TINYINT NOT NULL,
        comment TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_reviews_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        CONSTRAINT fk_reviews_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
    ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

    $pdo->exec('CREATE TABLE IF NOT EXISTS community_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        customer_id INT NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_community_customer FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
    ) ENGINE=InnoDB CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

    $count = (int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
    if ($count === 0) {
        $pdo->exec("INSERT INTO products (name, description, price, old_price, promo_text, category_name, brand_name, color_name, size_name, scent_type, image_url, gallery_urls, video_url, is_active) VALUES
            ('Dior Sauvage EDP', 'Mùi hương nam tính, mạnh mẽ, độ bền mùi tốt cho ngày dài.', 2850000, 3200000, 'Giảm 11% tuần này', 'Nước hoa nam', 'Dior', 'Đen', '100ml', 'Hương gỗ', 'https://images.unsplash.com/photo-1590736969955-71cc94901144?q=80&w=1000&auto=format&fit=crop', 'https://images.unsplash.com/photo-1610465299996-30d8168e7d94?q=80&w=1000&auto=format&fit=crop|https://images.unsplash.com/photo-1588405748880-12d1d2a59b62?q=80&w=1000&auto=format&fit=crop', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 1),
            ('Bleu de Chanel Parfum', 'Nhóm gỗ thơm lịch lãm, phù hợp đi làm và sự kiện.', 3450000, 3750000, 'Tặng quà mini 5ml', 'Nước hoa nam', 'Chanel', 'Xanh navy', '100ml', 'Hương biển', 'https://images.unsplash.com/photo-1615634262417-28f7a0a6d111?q=80&w=1000&auto=format&fit=crop', NULL, NULL, 1),
            ('YSL Libre EDP', 'Mùi hương nữ tính hiện đại, ngọt và sang trọng.', 3150000, 3500000, 'Ưu đãi thành viên mới', 'Nước hoa nữ', 'YSL', 'Vàng', '90ml', 'Hương hoa', 'https://images.unsplash.com/photo-1611930022073-b7a4ba5fcccd?q=80&w=1000&auto=format&fit=crop', NULL, NULL, 1),
            ('Versace Eros EDT', 'Mùi hương tươi trẻ, nồng ấm và cực kỳ cuốn hút.', 2300000, NULL, NULL, 'Nước hoa nam', 'Versace', 'Xanh', '100ml', 'Hương biển', 'https://images.unsplash.com/photo-1620916566398-39f1143ab7be?q=80&w=1000&auto=format&fit=crop', NULL, NULL, 1),
            ('Jo Malone Wood Sage & Sea Salt', 'Mùi hương nhẹ nhàng, thanh lịch, dễ dùng hằng ngày.', 2980000, NULL, NULL, 'Unisex', 'Jo Malone', 'Trắng', '100ml', 'Hương trầm', 'https://images.unsplash.com/photo-1588405748880-12d1d2a59b62?q=80&w=1000&auto=format&fit=crop', NULL, NULL, 1)");
    }

    $adminExists = (int)$pdo->query("SELECT COUNT(*) FROM customers WHERE role = 'admin'")->fetchColumn();
    if ($adminExists === 0) {
        $stmt = $pdo->prepare('INSERT INTO customers(full_name, email, phone, password_hash, role) VALUES(:full_name, :email, :phone, :password_hash, :role)');
        $stmt->execute([
            'full_name' => 'Administrator',
            'email' => 'admin@tmh.local',
            'phone' => '0900000000',
            'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
            'role' => 'admin',
        ]);
    }
}

function db(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];

    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    } catch (PDOException $e) {
        // 1049 = Unknown database. Auto-create for first run on XAMPP.
        if ((int)$e->errorInfo[1] !== 1049) {
            throw $e;
        }

        $serverDsn = 'mysql:host=' . DB_HOST . ';charset=utf8mb4';
        $serverPdo = new PDO($serverDsn, DB_USER, DB_PASS, $options);
        $serverPdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        $pdo->exec("SET NAMES utf8mb4");
        $pdo->exec("SET CHARACTER SET utf8mb4");
    }

    init_schema($pdo);

    return $pdo;
}
