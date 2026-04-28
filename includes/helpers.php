<?php
// includes/helpers.php — Shared utility functions

/**
 * Sanitize output for HTML context
 */
function e(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Generate a URL-friendly slug from a string
 */
function slugify(string $text): string
{
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $text);
    $text = preg_replace('/[\s_]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}

/**
 * Redirect and exit
 */
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/**
 * Generate a CSRF token and store in session
 */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token from POST
 */
function csrf_verify(): bool
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

/**
 * Get a site setting from DB (cached in memory for the request)
 */
function get_setting(string $key, string $default = ''): string
{
    static $cache = [];
    if (!isset($cache[$key])) {
        $stmt = get_db()->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        $cache[$key] = $row ? (string)$row['setting_value'] : $default;
    }
    return $cache[$key];
}

/**
 * Handle file upload for post thumbnails
 * Returns the relative path on success, throws on error.
 */
function upload_thumbnail(array $file): string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload error code: ' . $file['error']);
    }
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        throw new RuntimeException('File exceeds maximum allowed size (5 MB).');
    }
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES, true)) {
        throw new RuntimeException('Invalid file type. Only JPG, PNG, WebP and GIF are allowed.');
    }
    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = bin2hex(random_bytes(12)) . '.' . strtolower($ext);
    $destDir  = UPLOAD_PATH . '/thumbnails/';
    if (!is_dir($destDir) && !mkdir($destDir, 0755, true)) {
        throw new RuntimeException('Cannot create upload directory.');
    }
    $destPath = $destDir . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        throw new RuntimeException('Failed to move uploaded file.');
    }
    return 'uploads/thumbnails/' . $filename;
}

/**
 * Truncate text to a given word count
 */
function excerpt(string $html, int $words = 25): string
{
    $text  = wp_strip_tags($html);
    $parts = explode(' ', $text);
    if (count($parts) <= $words) return $text;
    return implode(' ', array_slice($parts, 0, $words)) . '…';
}

function wp_strip_tags(string $html): string
{
    return strip_tags($html);
}

/**
 * Get the visitor's real IP address
 */
function get_ip(): string
{
    foreach (['HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '0.0.0.0';
}

/**
 * Get geolocation info from ip-api.com (free tier)
 * Returns assoc array or null on failure.
 */
function geolocate_ip(string $ip): ?array
{
    if (in_array($ip, ['127.0.0.1', '::1', '0.0.0.0'])) return null;
    $url = "http://ip-api.com/json/{$ip}?fields=status,country,regionName,city,countryCode";
    $ctx = stream_context_create(['http' => ['timeout' => 2]]);
    $res = @file_get_contents($url, false, $ctx);
    if (!$res) return null;
    $data = json_decode($res, true);
    return ($data && $data['status'] === 'success') ? $data : null;
}

/**
 * Format date nicely
 */
function fmt_date(string $date): string
{
    return date('F j, Y', strtotime($date));
}

/**
 * Paginate: returns array with 'offset', 'total_pages', 'current_page'
 */
function paginate(int $total, int $perPage, int $currentPage): array
{
    $totalPages  = (int)ceil($total / $perPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    return [
        'offset'       => ($currentPage - 1) * $perPage,
        'total_pages'  => $totalPages,
        'current_page' => $currentPage,
        'per_page'     => $perPage,
        'total'        => $total,
    ];
}
