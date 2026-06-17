<?php
// Database configuration with environment-variable overrides.
// Defaults kept for local XAMPP usage if environment variables are not set.
$env = function ($name, $default) {
    $v = getenv($name);
    return $v !== false ? $v : $default;
};

define('DB_HOST', $env('DB_HOST', 'localhost'));
define('DB_NAME', $env('DB_NAME', 'buiiducthangg_nuoc_hoa_tmh'));
define('DB_USER', $env('DB_USER', 'buiiducthangg_nuoc_hoa_tmh'));
define('DB_PASS', $env('DB_PASS', 'Buii2Duc0Thangg4'));

// BASE_URL: set to '/' when serving from container root. Keep previous path as fallback.
define('BASE_URL', $env('BASE_URL', '/'));

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
