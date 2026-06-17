<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/index.php');
    exit;
}

$email = trim((string)($_POST['email'] ?? ''));

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['flash_error'] = 'Email không hợp lệ. Vui lòng nhập lại.';
} else {
    try {
        $stmt = db()->prepare('INSERT INTO newsletter_subscribers(email) VALUES(:email)');
        $stmt->execute(['email' => $email]);
        $_SESSION['flash_success'] = 'Đăng ký nhận tin thành công. Cảm ơn bạn đã quan tâm!';
    } catch (Throwable $e) {
        $_SESSION['flash_error'] = 'Email này đã đăng ký trước đó hoặc có lỗi xảy ra.';
    }
}

$back = $_SERVER['HTTP_REFERER'] ?? (BASE_URL . '/index.php');
header('Location: ' . $back);
exit;
