<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

if (current_user()) {
    redirect('index.php');
}

if (is_post()) {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $passwordConfirm = (string)($_POST['password_confirm'] ?? '');

    if ($fullName === '' || $email === '' || $password === '') {
        $_SESSION['flash_error'] = 'Vui lòng điền đầy đủ thông tin bắt buộc.';
        redirect('register.php');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash_error'] = 'Email không hợp lệ.';
        redirect('register.php');
    }

    if ($password !== $passwordConfirm) {
        $_SESSION['flash_error'] = 'Nhập lại mật khẩu không khớp.';
        redirect('register.php');
    }

    $check = db()->prepare('SELECT id FROM customers WHERE email = :email LIMIT 1');
    $check->execute(['email' => $email]);

    if ($check->fetch()) {
        $_SESSION['flash_error'] = 'Email đã tồn tại.';
        redirect('register.php');
    }

    $stmt = db()->prepare('INSERT INTO customers(full_name, email, phone, password_hash, role) VALUES(:full_name, :email, :phone, :password_hash, :role)');
    $stmt->execute([
        'full_name' => $fullName,
        'email' => $email,
        'phone' => $phone,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'role' => 'customer',
    ]);

    $_SESSION['flash_success'] = 'Đăng ký thành công. Vui lòng đăng nhập.';
    redirect('login.php');
}

include __DIR__ . '/includes/header.php';
?>
<div class="form-card">
    <h1>Đăng ký tài khoản</h1>
    <form method="post">
        <label>Họ và tên *</label>
        <input type="text" name="full_name" required>

        <label>Email *</label>
        <input type="email" name="email" required>

        <label>Số điện thoại</label>
        <input type="text" name="phone">

        <label>Mật khẩu *</label>
        <input type="password" name="password" required>

        <label>Nhập lại mật khẩu *</label>
        <input type="password" name="password_confirm" required>

        <button class="btn full" type="submit">Đăng ký</button>
    </form>
</div>
<?php include __DIR__ . '/includes/footer.php';
