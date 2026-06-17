<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

unset($_SESSION['user']);
$_SESSION['flash_success'] = 'Đã đăng xuất.';
redirect('index.php');
