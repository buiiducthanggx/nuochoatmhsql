<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

if (is_post()) {
    $fullName = trim((string)($_POST['full_name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $message = trim((string)($_POST['message'] ?? ''));

    if ($fullName === '' || $email === '' || $message === '') {
        $_SESSION['flash_error'] = 'Vui lòng điền đầy đủ các trường bắt buộc.';
        redirect('lien-he.php');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash_error'] = 'Email không hợp lệ.';
        redirect('lien-he.php');
    }

    $stmt = db()->prepare('INSERT INTO contact_messages(full_name, email, phone, message) VALUES(:full_name, :email, :phone, :message)');
    $stmt->execute([
        'full_name' => $fullName,
        'email' => $email,
        'phone' => $phone,
        'message' => $message,
    ]);

    $_SESSION['flash_success'] = 'Gửi liên hệ thành công. Chúng tôi sẽ phản hồi sớm nhất!';
    redirect('lien-he.php');
}

include __DIR__ . '/includes/header.php';
?>
<div class="form-card" style="max-width:960px;">
    <h1>Liên hệ</h1>
    <p>Nếu bạn cần tư vấn mùi hương hoặc hỗ trợ đơn hàng, hãy để lại thông tin:</p>

    <form method="post" action="<?= url('lien-he.php') ?>">
        <label>Họ và tên *</label>
        <input type="text" name="full_name" placeholder="Nguyễn Văn A" required>

        <label>Email *</label>
        <input type="email" name="email" placeholder="ban@example.com" required>

        <label>Số điện thoại</label>
        <input type="text" name="phone" placeholder="09xx xxx xxx">

        <label>Nội dung *</label>
        <textarea rows="5" name="message" placeholder="Nội dung cần hỗ trợ..." required></textarea>

        <button class="btn" type="submit">Gửi liên hệ</button>
    </form>
</div>
<?php include __DIR__ . '/includes/footer.php';
