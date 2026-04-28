<?php
// admin/api/auth.php — Admin authentication endpoints

require_once __DIR__ . '/../../includes/bootstrap.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

function json_response(array $data, int $code = 200): never
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function require_auth(): array
{
    if (empty($_SESSION['admin_id'])) {
        json_response(['error' => 'Unauthorized'], 401);
    }
    return [
        'id'       => $_SESSION['admin_id'],
        'username' => $_SESSION['admin_username'],
        'role'     => $_SESSION['admin_role'],
    ];
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    case 'login':
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            json_response(['error' => 'Method not allowed'], 405);
        }
        $body     = json_decode(file_get_contents('php://input'), true) ?? [];
        $username = trim($body['username'] ?? '');
        $password = trim($body['password'] ?? '');

        if (empty($username) || empty($password)) {
            json_response(['error' => 'Username and password are required'], 422);
        }

        $stmt = get_db()->prepare('SELECT * FROM admin_users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password_hash'])) {
            // Consistent timing to prevent enumeration
            usleep(300000);
            json_response(['error' => 'Invalid credentials'], 401);
        }

        // Regenerate session ID on login
        session_regenerate_id(true);
        $_SESSION['admin_id']       = $user['id'];
        $_SESSION['admin_username'] = $user['username'];
        $_SESSION['admin_role']     = $user['role'];

        // Update last_login
        get_db()->prepare('UPDATE admin_users SET last_login = NOW() WHERE id = ?')->execute([$user['id']]);

        json_response([
            'success' => true,
            'user'    => [
                'id'       => $user['id'],
                'username' => $user['username'],
                'email'    => $user['email'],
                'role'     => $user['role'],
            ],
        ]);

    case 'logout':
        $_SESSION = [];
        session_destroy();
        json_response(['success' => true]);

    case 'me':
        $admin = require_auth();
        $stmt  = get_db()->prepare('SELECT id, username, email, role, last_login FROM admin_users WHERE id = ?');
        $stmt->execute([$admin['id']]);
        $user = $stmt->fetch();
        json_response(['success' => true, 'user' => $user]);

    case 'change_password':
        $admin = require_auth();
        $body  = json_decode(file_get_contents('php://input'), true) ?? [];
        $current = $body['current_password'] ?? '';
        $new     = $body['new_password']     ?? '';

        if (strlen($new) < 8) {
            json_response(['error' => 'New password must be at least 8 characters'], 422);
        }

        $stmt = get_db()->prepare('SELECT password_hash FROM admin_users WHERE id = ?');
        $stmt->execute([$admin['id']]);
        $row = $stmt->fetch();

        if (!password_verify($current, $row['password_hash'])) {
            json_response(['error' => 'Current password is incorrect'], 403);
        }

        $hash = password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]);
        get_db()->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?')->execute([$hash, $admin['id']]);
        json_response(['success' => true]);

    default:
        json_response(['error' => 'Unknown action'], 400);
}
