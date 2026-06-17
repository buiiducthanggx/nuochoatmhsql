<?php
/**
 * Production configuration for buiiducthangg.me
 * Update database credentials before uploading to hosting
 */

// ===== DATABASE =====
define('DB_HOST', 'localhost');
define('DB_USER', 'nuoc_hoa_user');      // Change this - FTP username from cPanel
define('DB_PASS', 'YOUR_DB_PASSWORD');   // Change this - Database password
define('DB_NAME', 'nuoc_hoa_tmh');       // Keep as is unless you created different DB name
define('DB_PORT', 3306);

// ===== ENVIRONMENT =====
define('ENVIRONMENT', 'production');
define('DEBUG', false);

// ===== SESSION =====
define('SESSION_TIMEOUT', 24 * 60 * 60); // 24 hours

// ===== SECURITY =====
define('CSRF_TOKEN_LIFETIME', 3600);

// ===== EMAIL (Optional) =====
define('SMTP_HOST', 'localhost');
define('SMTP_PORT', 25);
define('SMTP_FROM', 'noreply@buiiducthangg.me');

// PDO connection
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    if (DEBUG) {
        die('Database connection failed: ' . $e->getMessage());
    } else {
        die('Unable to connect to database. Please contact support.');
    }
}

// Helper functions
function db() {
    global $pdo;
    return $pdo;
}

function is_post() {
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

function redirect($path) {
    header('Location: ' . $path);
    exit;
}

function e($text) {
    return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
}

function require_admin() {
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
        $_SESSION['flash_error'] = 'Yêu cầu quyền admin.';
        redirect('login.php');
    }
}

function require_login() {
    session_start();
    if (!isset($_SESSION['user'])) {
        redirect('login.php');
    }
}
