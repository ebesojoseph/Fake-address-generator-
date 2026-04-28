<?php
// admin/api/data.php — Categories, Settings, Content Sections, Analytics, Footer Links

require_once __DIR__ . '/../../includes/bootstrap.php';

header('Content-Type: application/json');

function jout(array $data, int $code = 200): never
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function admin_check(): array
{
    if (empty($_SESSION['admin_id'])) jout(['error' => 'Unauthorized'], 401);
    return ['id' => $_SESSION['admin_id'], 'role' => $_SESSION['admin_role']];
}

$admin    = admin_check();
$resource = $_GET['resource'] ?? '';
$method   = $_SERVER['REQUEST_METHOD'];
$id       = (int)($_GET['id'] ?? 0);
$body     = json_decode(file_get_contents('php://input'), true) ?? [];

// ─────────────────────────────────────────────────────────
// CATEGORIES
// ─────────────────────────────────────────────────────────
if ($resource === 'categories') {

    if ($method === 'GET') {
        $rows = db()->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();
        jout(['success' => true, 'data' => $rows]);
    }

    if ($method === 'POST') {
        $name = trim($body['name'] ?? '');
        if (empty($name)) jout(['error' => 'Name required'], 422);
        $slug = slugify($name);
        // Ensure unique slug
        $base = $slug; $i = 1;
        while (db()->prepare('SELECT id FROM categories WHERE slug=?')->execute([$slug]) && db()->query("SELECT id FROM categories WHERE slug='$slug'")->fetch()) {
            $slug = $base . '-' . $i++;
        }
        $stmt = db()->prepare('INSERT INTO categories (name, slug, description) VALUES (?,?,?)');
        $stmt->execute([$name, $slug, trim($body['description'] ?? '')]);
        jout(['success' => true, 'id' => db()->lastInsertId()]);
    }

    if ($method === 'PUT' && $id) {
        $name = trim($body['name'] ?? '');
        if (empty($name)) jout(['error' => 'Name required'], 422);
        db()->prepare('UPDATE categories SET name=?, description=? WHERE id=?')
            ->execute([$name, trim($body['description'] ?? ''), $id]);
        jout(['success' => true]);
    }

    if ($method === 'DELETE' && $id) {
        db()->prepare('DELETE FROM categories WHERE id=?')->execute([$id]);
        jout(['success' => true]);
    }
}

// ─────────────────────────────────────────────────────────
// SETTINGS
// ─────────────────────────────────────────────────────────
if ($resource === 'settings') {

    if ($method === 'GET') {
        $rows = db()->query('SELECT * FROM settings ORDER BY id ASC')->fetchAll();
        jout(['success' => true, 'data' => $rows]);
    }

    if ($method === 'POST') {
        // Bulk update: body = { settings: { key: value, ... } }
        $updates = $body['settings'] ?? [];
        $stmt    = db()->prepare('UPDATE settings SET setting_value=? WHERE setting_key=?');
        foreach ($updates as $key => $val) {
            $stmt->execute([(string)$val, (string)$key]);
        }
        jout(['success' => true]);
    }
}

// ─────────────────────────────────────────────────────────
// CONTENT SECTIONS
// ─────────────────────────────────────────────────────────
if ($resource === 'content_sections') {

    if ($method === 'GET') {
        $rows = db()->query('SELECT * FROM content_sections ORDER BY sort_order ASC')->fetchAll();
        jout(['success' => true, 'data' => $rows]);
    }

    if ($method === 'POST' && !$id) {
        $key   = trim($body['section_key'] ?? '');
        $title = trim($body['title']       ?? '');
        $bdy   = $body['body']             ?? '';
        $order = (int)($body['sort_order'] ?? 0);
        if (empty($key) || empty($title)) jout(['error' => 'Key and title required'], 422);
        $stmt = db()->prepare('INSERT INTO content_sections (section_key, title, body, sort_order) VALUES (?,?,?,?)');
        $stmt->execute([$key, $title, $bdy, $order]);
        jout(['success' => true, 'id' => db()->lastInsertId()]);
    }

    if ($method === 'PUT' && $id) {
        $title  = trim($body['title']      ?? '');
        $bdy    = $body['body']            ?? '';
        $order  = (int)($body['sort_order'] ?? 0);
        $active = isset($body['is_active']) ? (int)$body['is_active'] : 1;
        db()->prepare('UPDATE content_sections SET title=?, body=?, sort_order=?, is_active=? WHERE id=?')
            ->execute([$title, $bdy, $order, $active, $id]);
        jout(['success' => true]);
    }

    if ($method === 'DELETE' && $id) {
        db()->prepare('DELETE FROM content_sections WHERE id=?')->execute([$id]);
        jout(['success' => true]);
    }
}

// ─────────────────────────────────────────────────────────
// FOOTER LINKS
// ─────────────────────────────────────────────────────────
if ($resource === 'footer_links') {

    if ($method === 'GET') {
        $rows = db()->query('SELECT * FROM footer_links ORDER BY row_number ASC, sort_order ASC')->fetchAll();
        jout(['success' => true, 'data' => $rows]);
    }

    if ($method === 'POST' && !$id) {
        $label = trim($body['label']       ?? '');
        $url   = trim($body['url']         ?? '');
        $row   = (int)($body['row_number'] ?? 1);
        $order = (int)($body['sort_order'] ?? 0);
        if (empty($label) || empty($url)) jout(['error' => 'Label and URL required'], 422);
        $stmt = db()->prepare('INSERT INTO footer_links (label, url, row_number, sort_order) VALUES (?,?,?,?)');
        $stmt->execute([$label, $url, $row, $order]);
        jout(['success' => true, 'id' => db()->lastInsertId()]);
    }

    if ($method === 'PUT' && $id) {
        $label  = trim($body['label']       ?? '');
        $url    = trim($body['url']         ?? '');
        $row    = (int)($body['row_number'] ?? 1);
        $order  = (int)($body['sort_order'] ?? 0);
        $active = isset($body['is_active'])  ? (int)$body['is_active'] : 1;
        db()->prepare('UPDATE footer_links SET label=?, url=?, row_number=?, sort_order=?, is_active=? WHERE id=?')
            ->execute([$label, $url, $row, $order, $active, $id]);
        jout(['success' => true]);
    }

    if ($method === 'DELETE' && $id) {
        db()->prepare('DELETE FROM footer_links WHERE id=?')->execute([$id]);
        jout(['success' => true]);
    }
}

// ─────────────────────────────────────────────────────────
// ANALYTICS
// ─────────────────────────────────────────────────────────
if ($resource === 'analytics') {

    if ($method === 'GET') {
        $type = $_GET['type'] ?? 'overview';

        if ($type === 'overview') {
            $totalGen  = (int)db()->query('SELECT COUNT(*) FROM generation_logs')->fetchColumn();
            $todayGen  = (int)db()->query("SELECT COUNT(*) FROM generation_logs WHERE DATE(generated_at)=CURDATE()")->fetchColumn();
            $totalPost = (int)db()->query('SELECT COUNT(*) FROM posts WHERE status="published"')->fetchColumn();
            $totalViews= (int)db()->query('SELECT SUM(views) FROM posts')->fetchColumn();

            jout(['success' => true, 'data' => [
                'total_generations'  => $totalGen,
                'today_generations'  => $todayGen,
                'published_posts'    => $totalPost,
                'total_post_views'   => $totalViews,
            ]]);
        }

        if ($type === 'generations_by_country') {
            $rows = db()->query(
                'SELECT generated_country AS country, COUNT(*) AS count
                 FROM generation_logs
                 GROUP BY generated_country
                 ORDER BY count DESC LIMIT 10'
            )->fetchAll();
            jout(['success' => true, 'data' => $rows]);
        }

        if ($type === 'generations_over_time') {
            $rows = db()->query(
                'SELECT DATE(generated_at) AS date, COUNT(*) AS count
                 FROM generation_logs
                 WHERE generated_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                 GROUP BY DATE(generated_at)
                 ORDER BY date ASC'
            )->fetchAll();
            jout(['success' => true, 'data' => $rows]);
        }

        if ($type === 'top_posts') {
            $rows = db()->query(
                'SELECT id, title, slug, views, published_at FROM posts
                 WHERE status="published"
                 ORDER BY views DESC LIMIT 10'
            )->fetchAll();
            jout(['success' => true, 'data' => $rows]);
        }
    }
}

jout(['error' => 'Unknown resource or method'], 400);
