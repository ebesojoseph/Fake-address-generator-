<?php
require_once __DIR__ . '/../../includes/bootstrap.php';
header('Content-Type: application/json');

function jout(array $d, int $c = 200): never { http_response_code($c); echo json_encode($d); exit; }

function require_admin(): array {
    if (empty($_SESSION['admin_id'])) jout(['error' => 'Unauthorized'], 401);
    return ['id' => $_SESSION['admin_id'], 'username' => $_SESSION['admin_username'], 'role' => $_SESSION['admin_role']];
}

$action = $_GET['action'] ?? '';
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

switch ($action) {
    case 'login':
        $u = trim($body['username'] ?? '');
        $p = trim($body['password'] ?? '');
        if (!$u || !$p) jout(['error' => 'Username and password required'], 422);
        $stmt = get_db()->prepare('SELECT * FROM admin_users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$u, $u]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($p, $user['password_hash'])) { usleep(300000); jout(['error' => 'Invalid credentials'], 401); }
        session_regenerate_id(true);
        $_SESSION['admin_id']       = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_role']     = $user['role'];
        get_db()->prepare('UPDATE admin_users SET last_login = NOW() WHERE id = ?')->execute([$user['id']]);
        jout(['success' => true, 'user' => ['id' => $user['id'], 'username' => $user['username'], 'email' => $user['email'], 'role' => $user['role']]]);

    case 'logout':
        $_SESSION = []; session_destroy();
        jout(['success' => true]);

    case 'me':
        $a = require_admin();
        $stmt = get_db()->prepare('SELECT id, username, email, role, last_login FROM admin_users WHERE id = ?');
        $stmt->execute([$a['id']]);
        jout(['success' => true, 'user' => $stmt->fetch()]);

    case 'change_password':
        $a   = require_admin();
        $cur = $body['current_password'] ?? '';
        $new = $body['new_password']     ?? '';
        if (strlen($new) < 8) jout(['error' => 'New password must be at least 8 characters'], 422);
        $stmt = get_db()->prepare('SELECT password_hash FROM admin_users WHERE id = ?');
        $stmt->execute([$a['id']]);
        $row = $stmt->fetch();
        if (!password_verify($cur, $row['password_hash'])) jout(['error' => 'Current password incorrect'], 403);
        $hash = password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]);
        get_db()->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?')->execute([$hash, $a['id']]);
        jout(['success' => true]);

    default:
        jout(['error' => 'Unknown action'], 400);
}
