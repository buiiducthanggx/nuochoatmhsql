<?php
require_once __DIR__ . '/config.php';

try {
    // Connect to server (not specific database)
    $serverDsn = 'mysql:host=' . DB_HOST;
    $serverPdo = new PDO($serverDsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Drop old database
    $serverPdo->exec('DROP DATABASE IF EXISTS `' . DB_NAME . '`');
    echo "✅ Database cũ đã bị xóa.<br>";

    // Create new database with correct charset
    $serverPdo->exec('CREATE DATABASE `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    echo "✅ Database mới đã tạo với charset UTF-8MB4.<br>";

    // Connect to new database
    $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $pdo->exec("SET NAMES utf8mb4");
    $pdo->exec("SET CHARACTER SET utf8mb4");

    // Initialize schema with new database
    require_once __DIR__ . '/includes/db.php';
    init_schema($pdo);
    
    echo "✅ Tất cả bảng đã được tạo với dữ liệu mặc định.<br>";
    echo "<p style='color:green; font-weight:bold;'>Thành công! Database đã được reset.</p>";
    echo "<p><a href='index.php'>👉 Quay về trang chủ</a></p>";

} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Lỗi: " . e($e->getMessage()) . "</p>";
}
?>
