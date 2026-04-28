<?php
// includes/helpers.php

if (!function_exists('e')) {
    function e(string $s): string {
        return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('slugify')) {
    function slugify(string $text): string {
        $text = mb_strtolower($text, 'UTF-8');
        $text = preg_replace('/[^\p{L}\p{N}\s-]/u', '', $text);
        $text = preg_replace('/[\s_]+/', '-', $text);
        return trim($text, '-');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $url): never { header('Location: ' . $url); exit; }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('csrf_verify')) {
    function csrf_verify(): bool {
        return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
            && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
    }
}

if (!function_exists('get_setting')) {
    function get_setting(string $key, string $default = ''): string {
        static $cache = [];
        if (!isset($cache[$key])) {
            try {
                $stmt = get_db()->prepare('SELECT setting_value FROM settings WHERE setting_key = ?');
                $stmt->execute([$key]);
                $row = $stmt->fetch();
                $cache[$key] = $row ? (string)$row['setting_value'] : $default;
            } catch (\Throwable) {
                $cache[$key] = $default;
            }
        }
        return $cache[$key];
    }
}

if (!function_exists('upload_thumbnail')) {
    function upload_thumbnail(array $file): string {
        if ($file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Upload error: ' . $file['error']);
        if ($file['size'] > MAX_UPLOAD_SIZE)  throw new RuntimeException('File too large (max 5 MB).');
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        if (!in_array($mime, ALLOWED_IMAGE_TYPES, true)) throw new RuntimeException('Invalid image type.');
        $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $name = bin2hex(random_bytes(12)) . '.' . $ext;
        $dir  = UPLOAD_PATH . '/thumbnails/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        if (!move_uploaded_file($file['tmp_name'], $dir . $name)) throw new RuntimeException('Upload failed.');
        return 'uploads/thumbnails/' . $name;
    }
}

if (!function_exists('excerpt')) {
    function excerpt(string $html, int $words = 25): string {
        $text  = strip_tags($html);
        $parts = explode(' ', $text);
        if (count($parts) <= $words) return $text;
        return implode(' ', array_slice($parts, 0, $words)) . '…';
    }
}

if (!function_exists('get_ip')) {
    function get_ip(): string {
        foreach (['HTTP_CLIENT_IP','HTTP_X_FORWARDED_FOR','REMOTE_ADDR'] as $k) {
            if (!empty($_SERVER[$k])) {
                $ip = trim(explode(',', $_SERVER[$k])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
            }
        }
        return '0.0.0.0';
    }
}

if (!function_exists('geolocate_ip')) {
    function geolocate_ip(string $ip): ?array {
        if (in_array($ip, ['127.0.0.1','::1','0.0.0.0'])) return null;
        $ctx = stream_context_create(['http' => ['timeout' => 2]]);
        $res = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,regionName,city,countryCode", false, $ctx);
        if (!$res) return null;
        $data = json_decode($res, true);
        return ($data && $data['status'] === 'success') ? $data : null;
    }
}

if (!function_exists('fmt_date')) {
    function fmt_date(string $date): string {
        return date('F j, Y', strtotime($date));
    }
}

if (!function_exists('paginate')) {
    function paginate(int $total, int $perPage, int $page): array {
        $totalPages = (int)ceil($total / max(1, $perPage));
        $page = max(1, min($page, $totalPages ?: 1));
        return [
            'offset'       => ($page - 1) * $perPage,
            'total_pages'  => $totalPages,
            'current_page' => $page,
            'per_page'     => $perPage,
            'total'        => $total,
        ];
    }
}
