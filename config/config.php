<?php
// config/config.php — Central configuration file

error_reporting(E_ALL);
ini_set('display_errors', 'On');

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'fakeaddrgen');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 'mysql');
define('DB_CHARSET', 'utf8mb4');

define('BASE_URL',    rtrim(getenv('BASE_URL') ?: 'http://localhost/fake-address-gen/', '/'));
define('ROOT_PATH',   dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads');
define('UPLOAD_URL',  BASE_URL . '/uploads');

define('SESSION_NAME',    'fag_session');
define('SESSION_SECURE',  false); // set true in production with HTTPS
define('SESSION_HTTPONLY', true);

// Max featured posts
define('MAX_FEATURED_POSTS', 5);

// Allowed image MIME types for upload
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5 MB
