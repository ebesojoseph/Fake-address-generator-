<?php
// includes/bootstrap.php — Load everything

define('APP_START', microtime(true));

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/address_generator.php';

// ------------------------------------------------------------------
// Session bootstrap
// ------------------------------------------------------------------
if (session_status() === PHP_SESSION_NONE) {
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => $cookieParams['path'],
        'domain'   => $cookieParams['domain'],
        'secure'   => SESSION_SECURE,
        'httponly' => SESSION_HTTPONLY,
        'samesite' => 'Lax',
    ]);
    session_name(SESSION_NAME);
    session_start();
}

// ------------------------------------------------------------------
// Routing helper
// ------------------------------------------------------------------
function current_path(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($uri, PHP_URL_PATH);
    return rtrim($path ?: '/', '/') ?: '/';
}
