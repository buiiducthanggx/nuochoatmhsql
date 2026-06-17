<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

if (current_user()) {
    redirect('index.php');
}

if (is_post()) {
    $email = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    $stmt = db()->prepare('SELECT * FROM customers WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $customer = $stmt->fetch();

    if (!$customer || !password_verify($password, $customer['password_hash'])) {
        $_SESSION['flash_error'] = 'Thông tin đăng nhập không đúng.';
        redirect('login.php');
    }

    unset($customer['password_hash']);
    $_SESSION['user'] = $customer;

    $_SESSION['flash_success'] = 'Đăng nhập thành công.';
    redirect('index.php');
}

include __DIR__ . '/includes/header.php';
?>
<div class="form-card">
    <h1>Đăng nhập</h1>
    <form method="post">
        <label>Email</label>
        <input type="email" name="email" required>

        <label>Mật khẩu</label>
        <input type="password" name="password" required>

        <button class="btn full" type="submit">Đăng nhập</button>
    </form>
</div>
<?php include __DIR__ . '/includes/footer.php';
