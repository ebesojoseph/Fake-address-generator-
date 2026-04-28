<?php
// includes/bootstrap.php

defined('APP_START') || define('APP_START', microtime(true));

// Load Composer autoloader (includes Faker + our PSR-4 classes)
$autoloader = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloader)) {
    http_response_code(503);
    die('<h2>Dependencies missing.</h2><p>Run <code>composer install</code> in the project root.</p>');
}
require_once $autoloader;

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/helpers.php';

// Session
if (session_status() === PHP_SESSION_NONE) {
    $params = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => $params['path'],
        'domain'   => $params['domain'],
        'secure'   => SESSION_SECURE,
        'httponly' => SESSION_HTTPONLY,
        'samesite' => 'Lax',
    ]);
    session_name(SESSION_NAME);
    session_start();
}

if (!function_exists('current_path')) {
    function current_path(): string
    {
        $uri  = $_SERVER['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH);
        // Strip subfolder prefix so /fake-address-gen/blog → /blog
        $base = rtrim(BASE_URL_PATH(), '/');
        if ($base && str_starts_with($path, $base)) {
            $path = substr($path, strlen($base));
        }
        return rtrim($path ?: '/', '/') ?: '/';
    }
}

if (!function_exists('BASE_URL_PATH')) {
    function BASE_URL_PATH(): string
    {
        return parse_url(BASE_URL, PHP_URL_PATH) ?? '';
    }
}
