<?php
// config/config.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ── Detect subfolder automatically ───────────────────────
// Works on localhost/fake-address-gen AND yourdomain.com/fake-address-gen
// Override by setting APP_BASE_URL env var on cPanel
defined('DB_HOST')    || define('DB_HOST',    getenv('DB_HOST') ?: 'localhost');
defined('DB_NAME')    || define('DB_NAME',    getenv('DB_NAME') ?: 'fakeaddrgen');
defined('DB_USER')    || define('DB_USER',    getenv('DB_USER') ?: 'root');
defined('DB_PASS')    || define('DB_PASS',    getenv('DB_PASS') ?: 'mysql');
defined('DB_CHARSET') || define('DB_CHARSET', 'utf8mb4');

// Auto-detect BASE_URL from the script path
if (!defined('BASE_URL')) {
    if (getenv('APP_BASE_URL')) {
        define('BASE_URL', rtrim(getenv('APP_BASE_URL'), 'http://localhost/fakeaddrgen'));
    } else {
        $scheme   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host     = $_SERVER['HTTP_HOST'] ?? 'localhost';
        // Get the subfolder by comparing DOCUMENT_ROOT to the app root
        $docRoot  = rtrim($_SERVER['DOCUMENT_ROOT'] ?? '', '/');
        $appRoot  = rtrim(dirname(__DIR__), '/');
        $subPath  = str_replace($docRoot, '', $appRoot);
        define('BASE_URL', $scheme . '://' . $host . $subPath);
    }
}

defined('ROOT_PATH')   || define('ROOT_PATH',   dirname(__DIR__));
defined('UPLOAD_PATH') || define('UPLOAD_PATH', ROOT_PATH . '/uploads');
defined('UPLOAD_URL')  || define('UPLOAD_URL',  BASE_URL . '/uploads');

defined('SESSION_NAME')     || define('SESSION_NAME',     'fag_sess');
defined('SESSION_SECURE')   || define('SESSION_SECURE',   !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
defined('SESSION_HTTPONLY') || define('SESSION_HTTPONLY',  true);

defined('MAX_FEATURED_POSTS') || define('MAX_FEATURED_POSTS', 5);
defined('ALLOWED_IMAGE_TYPES') || define('ALLOWED_IMAGE_TYPES', ['image/jpeg','image/png','image/webp','image/gif']);
defined('MAX_UPLOAD_SIZE')     || define('MAX_UPLOAD_SIZE',     5 * 1024 * 1024);
