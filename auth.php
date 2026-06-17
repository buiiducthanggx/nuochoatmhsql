<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

if (current_user()) {
    redirect('index.php');
}

$mode = $_GET['mode'] ?? 'login'; // 'login' or 'register'
if (!in_array($mode, ['login', 'register'])) {
    $mode = 'login';
}

// Handle login
if ($mode === 'login' && is_post()) {
    $email = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    $stmt = db()->prepare('SELECT * FROM customers WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $customer = $stmt->fetch();

    if (!$customer || !password_verify($password, $customer['password_hash'])) {
        $_SESSION['flash_error'] = 'Thông tin đăng nhập không đúng.';
        redirect('auth.php?mode=login');
    }

    unset($customer['password_hash']);
    $_SESSION['user'] = $customer;

    $_SESSION['flash_success'] = 'Đăng nhập thành công.';
    redirect('index.php');
}

// Handle register
if ($mode === 'register' && is_post()) {
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $passwordConfirm = (string)($_POST['password_confirm'] ?? '');

    if ($fullName === '' || $email === '' || $password === '') {
        $_SESSION['flash_error'] = 'Vui lòng điền đầy đủ thông tin bắt buộc.';
        redirect('auth.php?mode=register');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash_error'] = 'Email không hợp lệ.';
        redirect('auth.php?mode=register');
    }

    if ($password !== $passwordConfirm) {
        $_SESSION['flash_error'] = 'Nhập lại mật khẩu không khớp.';
        redirect('auth.php?mode=register');
    }

    $check = db()->prepare('SELECT id FROM customers WHERE email = :email LIMIT 1');
    $check->execute(['email' => $email]);

    if ($check->fetch()) {
        $_SESSION['flash_error'] = 'Email đã tồn tại.';
        redirect('auth.php?mode=register');
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
    redirect('auth.php?mode=login');
}

include __DIR__ . '/includes/header.php';
?>

<div class="auth-container">
    <div id="login-form" class="auth-form <?= $mode === 'login' ? 'active' : '' ?>">
        <h1>Đăng nhập</h1>
        <form method="post" action="?mode=login">
            <label>Email</label>
            <input type="email" name="email" required>

            <label>Mật khẩu</label>
            <input type="password" name="password" required>

            <button class="btn full" type="submit">Đăng nhập</button>
            
            <p class="auth-switch">
                Chưa có tài khoản? <a href="?mode=register" onclick="switchAuthMode(event, 'register')">Đăng ký</a>
            </p>
        </form>
    </div>

    <div id="register-form" class="auth-form <?= $mode === 'register' ? 'active' : '' ?>">
        <h1>Đăng ký tài khoản</h1>
        <form method="post" action="?mode=register">
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
            
            <p class="auth-switch">
                Đã có tài khoản? <a href="?mode=login" onclick="switchAuthMode(event, 'login')">Đăng nhập</a>
            </p>
        </form>
    </div>
</div>

<script>
function switchAuthMode(event, mode) {
    event.preventDefault();
    const loginForm = document.getElementById('login-form');
    const registerForm = document.getElementById('register-form');

    if (mode === 'login') {
        loginForm.classList.add('active');
        registerForm.classList.remove('active');
        window.history.pushState({}, '', '?mode=login');
    } else {
        registerForm.classList.add('active');
        loginForm.classList.remove('active');
        window.history.pushState({}, '', '?mode=register');
    }
}
</script>

<?php include __DIR__ . '/includes/footer.php';
