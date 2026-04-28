<?php
// admin/api/posts.php — Blog posts CRUD

require_once __DIR__ . '/../../includes/bootstrap.php';

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

function json_out(array $data, int $code = 200): never
{
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function admin_auth(): array
{
    if (empty($_SESSION['admin_id'])) json_out(['error' => 'Unauthorized'], 401);
    return ['id' => $_SESSION['admin_id'], 'role' => $_SESSION['admin_role']];
}

// ── Sanitize HTML from Quill (basic allow-list) ──────────
function sanitize_html(string $html): string
{
    // Allow safe tags; strip anything dangerous
    return strip_tags($html,
        '<p><br><b><strong><i><em><u><s><strike><h1><h2><h3><h4><h5><h6>'
      . '<ul><ol><li><a><img><blockquote><pre><code><span><div>'
      . '<table><thead><tbody><tr><th><td><hr><figure><figcaption>'
    );
}

$method = $_SERVER['REQUEST_METHOD'];
$admin  = admin_auth();
$id     = (int)($_GET['id'] ?? 0);

// ── LIST ─────────────────────────────────────────────────
if ($method === 'GET' && !$id) {
    $page    = max(1, (int)($_GET['page']    ?? 1));
    $perPage = min(50, max(1, (int)($_GET['per_page'] ?? 20)));
    $search  = trim($_GET['search'] ?? '');
    $status  = $_GET['status'] ?? '';
    $cat     = (int)($_GET['category'] ?? 0);

    $where  = '1=1';
    $params = [];
    if ($search) { $where .= ' AND (p.title LIKE ? OR p.excerpt LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }
    if ($status) { $where .= ' AND p.status = ?'; $params[] = $status; }
    if ($cat)    { $where .= ' AND p.category_id = ?'; $params[] = $cat; }

    $total = db()->prepare("SELECT COUNT(*) FROM posts p WHERE $where");
    $total->execute($params);
    $total = (int)$total->fetchColumn();

    $offset = ($page - 1) * $perPage;
    $stmt = db()->prepare(
        "SELECT p.id, p.title, p.slug, p.status, p.is_featured, p.views, p.published_at, p.created_at,
                c.name AS category_name, p.thumbnail
         FROM posts p
         LEFT JOIN categories c ON c.id = p.category_id
         WHERE $where
         ORDER BY p.created_at DESC
         LIMIT $perPage OFFSET $offset"
    );
    $stmt->execute($params);
    $posts = $stmt->fetchAll();
    json_out(['success' => true, 'data' => $posts, 'total' => $total, 'page' => $page, 'per_page' => $perPage]);
}

// ── GET ONE ──────────────────────────────────────────────
if ($method === 'GET' && $id) {
    $stmt = db()->prepare('SELECT * FROM posts WHERE id = ?');
    $stmt->execute([$id]);
    $post = $stmt->fetch();
    if (!$post) json_out(['error' => 'Not found'], 404);
    json_out(['success' => true, 'data' => $post]);
}

// ── CREATE ───────────────────────────────────────────────
if ($method === 'POST') {
    // Handle multipart (file + JSON fields)
    $title       = trim($_POST['title']            ?? '');
    $slug        = trim($_POST['slug']             ?? '');
    $excerpt     = trim($_POST['excerpt']          ?? '');
    $content     = $_POST['content']               ?? '';
    $categoryId  = (int)($_POST['category_id']     ?? 0) ?: null;
    $status      = in_array($_POST['status'] ?? '', ['draft','published']) ? $_POST['status'] : 'draft';
    $isFeatured  = (int)($_POST['is_featured']     ?? 0);
    $metaTitle   = trim($_POST['meta_title']       ?? '');
    $metaDesc    = trim($_POST['meta_description'] ?? '');
    $metaKw      = trim($_POST['meta_keywords']    ?? '');

    if (empty($title))   json_out(['error' => 'Title is required'], 422);
    if (empty($content)) json_out(['error' => 'Content is required'], 422);

    // Thumbnail required
    if (empty($_FILES['thumbnail']['tmp_name'])) {
        json_out(['error' => 'Thumbnail is required to publish a post'], 422);
    }

    // Auto-generate slug
    if (empty($slug)) $slug = slugify($title);
    // Ensure uniqueness
    $checkSlug = $slug;
    $i = 1;
    while (true) {
        $s = db()->prepare('SELECT id FROM posts WHERE slug = ?');
        $s->execute([$checkSlug]);
        if (!$s->fetch()) break;
        $checkSlug = $slug . '-' . $i++;
    }
    $slug = $checkSlug;

    // Check featured limit
    if ($isFeatured) {
        $count = (int)db()->query('SELECT COUNT(*) FROM posts WHERE is_featured = 1 AND status = "published"')->fetchColumn();
        if ($count >= MAX_FEATURED_POSTS) {
            json_out(['error' => 'Maximum of ' . MAX_FEATURED_POSTS . ' featured posts reached. Please unfeature another post first.'], 422);
        }
    }

    try {
        $thumbPath = upload_thumbnail($_FILES['thumbnail']);
    } catch (RuntimeException $e) {
        json_out(['error' => $e->getMessage()], 422);
    }

    $content = sanitize_html($content);
    $publishedAt = ($status === 'published') ? date('Y-m-d H:i:s') : null;

    $stmt = db()->prepare(
        'INSERT INTO posts (title, slug, excerpt, content, thumbnail, category_id, author_id, status, is_featured,
                            meta_title, meta_description, meta_keywords, published_at)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)'
    );
    $stmt->execute([
        $title, $slug, $excerpt, $content, $thumbPath, $categoryId,
        $admin['id'], $status, $isFeatured,
        $metaTitle, $metaDesc, $metaKw, $publishedAt,
    ]);
    $newId = (int)db()->lastInsertId();
    json_out(['success' => true, 'id' => $newId, 'slug' => $slug], 201);
}

// ── UPDATE ───────────────────────────────────────────────
if ($method === 'PUT' || ($method === 'POST' && isset($_POST['_method']) && $_POST['_method'] === 'PUT')) {
    if (!$id) json_out(['error' => 'ID required'], 400);

    $stmt = db()->prepare('SELECT * FROM posts WHERE id = ?');
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) json_out(['error' => 'Not found'], 404);

    $title      = trim($_POST['title']            ?? $existing['title']);
    $slug       = trim($_POST['slug']             ?? $existing['slug']);
    $excerpt    = trim($_POST['excerpt']          ?? $existing['excerpt']);
    $content    = sanitize_html($_POST['content'] ?? $existing['content']);
    $categoryId = (int)($_POST['category_id']     ?? $existing['category_id']) ?: null;
    $status     = in_array($_POST['status'] ?? '', ['draft','published']) ? $_POST['status'] : $existing['status'];
    $isFeatured = (int)($_POST['is_featured']     ?? $existing['is_featured']);
    $metaTitle  = trim($_POST['meta_title']       ?? $existing['meta_title']);
    $metaDesc   = trim($_POST['meta_description'] ?? $existing['meta_description']);
    $metaKw     = trim($_POST['meta_keywords']    ?? $existing['meta_keywords']);

    // Slug uniqueness (exclude self)
    if ($slug !== $existing['slug']) {
        $base = slugify($slug ?: $title);
        $check = $base; $i = 1;
        while (true) {
            $s = db()->prepare('SELECT id FROM posts WHERE slug = ? AND id != ?');
            $s->execute([$check, $id]);
            if (!$s->fetch()) break;
            $check = $base . '-' . $i++;
        }
        $slug = $check;
    }

    // Featured limit check (only if newly featuring)
    if ($isFeatured && !$existing['is_featured']) {
        $count = (int)db()->query('SELECT COUNT(*) FROM posts WHERE is_featured = 1 AND status = "published"')->fetchColumn();
        if ($count >= MAX_FEATURED_POSTS) {
            json_out(['error' => 'Maximum featured posts (' . MAX_FEATURED_POSTS . ') reached.'], 422);
        }
    }

    // Thumbnail update (optional)
    $thumbPath = $existing['thumbnail'];
    if (!empty($_FILES['thumbnail']['tmp_name'])) {
        try {
            $thumbPath = upload_thumbnail($_FILES['thumbnail']);
        } catch (RuntimeException $e) {
            json_out(['error' => $e->getMessage()], 422);
        }
    }

    // If publishing for first time, set published_at
    $publishedAt = $existing['published_at'];
    if ($status === 'published' && empty($publishedAt)) {
        $publishedAt = date('Y-m-d H:i:s');
    }

    $stmt = db()->prepare(
        'UPDATE posts SET title=?, slug=?, excerpt=?, content=?, thumbnail=?, category_id=?, status=?,
         is_featured=?, meta_title=?, meta_description=?, meta_keywords=?, published_at=?, updated_at=NOW()
         WHERE id=?'
    );
    $stmt->execute([
        $title, $slug, $excerpt, $content, $thumbPath, $categoryId,
        $status, $isFeatured, $metaTitle, $metaDesc, $metaKw, $publishedAt, $id,
    ]);
    json_out(['success' => true, 'slug' => $slug]);
}

// ── DELETE ───────────────────────────────────────────────
if ($method === 'DELETE') {
    if (!$id) json_out(['error' => 'ID required'], 400);
    $stmt = db()->prepare('SELECT thumbnail FROM posts WHERE id = ?');
    $stmt->execute([$id]);
    $post = $stmt->fetch();
    if (!$post) json_out(['error' => 'Not found'], 404);

    // Remove thumbnail file
    $filePath = ROOT_PATH . '/' . $post['thumbnail'];
    if (file_exists($filePath)) @unlink($filePath);

    db()->prepare('DELETE FROM posts WHERE id = ?')->execute([$id]);
    json_out(['success' => true]);
}

json_out(['error' => 'Method not allowed'], 405);
