<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

require_login();
$user = current_user();

if (is_post()) {
    $fullName = trim((string)($_POST['full_name'] ?? ''));
    $phone = trim((string)($_POST['phone'] ?? ''));
    $address = trim((string)($_POST['address'] ?? ''));
    $avatarPath = trim((string)($user['avatar_path'] ?? ''));

    if ($fullName === '') {
        $_SESSION['flash_error'] = 'Họ tên không được để trống.';
        redirect('profile.php');
    }

    if (isset($_FILES['avatar']) && (int)($_FILES['avatar']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $fileError = (int)($_FILES['avatar']['error'] ?? UPLOAD_ERR_OK);
        if ($fileError !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Tải ảnh đại diện thất bại. Vui lòng thử lại.';
            redirect('profile.php');
        }

        $tmpFile = (string)($_FILES['avatar']['tmp_name'] ?? '');
        $size = (int)($_FILES['avatar']['size'] ?? 0);
        if (!is_uploaded_file($tmpFile)) {
            $_SESSION['flash_error'] = 'Tệp ảnh không hợp lệ.';
            redirect('profile.php');
        }
        if ($size <= 0 || $size > 10 * 1024 * 1024) {
            $_SESSION['flash_error'] = 'Ảnh đại diện tối đa 10MB.';
            redirect('profile.php');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? (string)finfo_file($finfo, $tmpFile) : '';
        if ($finfo) {
            finfo_close($finfo);
        }

        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];
        if (!isset($allowed[$mime])) {
            $_SESSION['flash_error'] = 'Chỉ hỗ trợ JPG, PNG, WEBP hoặc GIF.';
            redirect('profile.php');
        }

        $avatarDir = __DIR__ . '/uploads/avatars';
        if (!is_dir($avatarDir) && !mkdir($avatarDir, 0775, true) && !is_dir($avatarDir)) {
            $_SESSION['flash_error'] = 'Không thể tạo thư mục lưu ảnh đại diện.';
            redirect('profile.php');
        }

        $ext = $allowed[$mime];
        $fileName = 'avatar_' . (int)$user['id'] . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $targetPath = $avatarDir . '/' . $fileName;
        if (!move_uploaded_file($tmpFile, $targetPath)) {
            $_SESSION['flash_error'] = 'Không thể lưu ảnh đại diện.';
            redirect('profile.php');
        }

        $newAvatarPath = 'uploads/avatars/' . $fileName;
        if ($avatarPath !== '') {
            $oldFile = __DIR__ . '/' . ltrim($avatarPath, '/');
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }
        $avatarPath = $newAvatarPath;
    }

    $stmt = db()->prepare('UPDATE customers SET full_name = :full_name, phone = :phone, address = :address, avatar_path = :avatar_path WHERE id = :id');
    $stmt->execute([
        'id' => $user['id'],
        'full_name' => $fullName,
        'phone' => $phone,
        'address' => $address,
        'avatar_path' => $avatarPath !== '' ? $avatarPath : null,
    ]);

    $_SESSION['user']['full_name'] = $fullName;
    $_SESSION['user']['phone'] = $phone;
    $_SESSION['user']['address'] = $address;
    $_SESSION['user']['avatar_path'] = $avatarPath !== '' ? $avatarPath : null;
    $_SESSION['flash_success'] = 'Cập nhật tài khoản thành công.';
    redirect('profile.php');
}

$refreshStmt = db()->prepare('SELECT * FROM customers WHERE id = :id LIMIT 1');
$refreshStmt->execute(['id' => $user['id']]);
$profile = $refreshStmt->fetch();

include __DIR__ . '/includes/header.php';
?>
<div class="form-card" style="max-width:760px;">
    <h1>Tài khoản người dùng</h1>
    <div class="profile-avatar-wrap">
        <?php if ($avatar = user_avatar_url($profile ?: $user)): ?>
            <img class="profile-avatar-image" src="<?= e($avatar) ?>" alt="Ảnh đại diện">
        <?php else: ?>
            <div class="profile-avatar-fallback"><?= e(user_initial((string)($profile['full_name'] ?? $user['full_name'] ?? 'U'))) ?></div>
        <?php endif; ?>
        <p class="profile-avatar-note">Ảnh mặc định sẽ hiển thị chữ cái đầu nếu bạn chưa tải ảnh lên.</p>
    </div>

    <form method="post" enctype="multipart/form-data">
        <label>Họ và tên</label>
        <input type="text" name="full_name" value="<?= e($profile['full_name'] ?? '') ?>" required>

        <label>Email</label>
        <input type="email" value="<?= e($profile['email'] ?? '') ?>" disabled>

        <label>Số điện thoại</label>
        <input type="text" name="phone" value="<?= e($profile['phone'] ?? '') ?>">

        <label>Địa chỉ mặc định</label>
        <textarea name="address" rows="3"><?= e($profile['address'] ?? '') ?></textarea>

        <label>Ảnh đại diện (JPG, PNG, WEBP, GIF - tối đa 10MB)</label>
        <input type="file" name="avatar" accept="image/png,image/jpeg,image/webp,image/gif">

        <button class="btn" type="submit">Lưu thông tin</button>
    </form>
</div>
<?php include __DIR__ . '/includes/footer.php';
