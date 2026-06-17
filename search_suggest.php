<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

$q = trim((string)($_GET['q'] ?? ''));
if ($q === '' || strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

$stmt = db()->prepare('SELECT id, name, price FROM products WHERE is_active = 1 AND (name LIKE :q OR brand_name LIKE :q) ORDER BY id DESC LIMIT 8');
$stmt->execute(['q' => '%' . $q . '%']);
$rows = $stmt->fetchAll();

echo json_encode($rows, JSON_UNESCAPED_UNICODE);
