<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

use App\Models\Post;

header('Content-Type: application/json');

function jout(array $d, int $c = 200): never { http_response_code($c); echo json_encode($d); exit; }
function require_admin(): array {
    if (empty($_SESSION['admin_id'])) jout(['error' => 'Unauthorized'], 401);
    return ['id' => $_SESSION['admin_id'], 'role' => $_SESSION['admin_role']];
}

function sanitize_html(string $html): string {
    return strip_tags($html,
        '<p><br><b><strong><i><em><u><s><h1><h2><h3><h4><h5><h6>'
      . '<ul><ol><li><a><img><blockquote><pre><code><span><div>'
      . '<table><thead><tbody><tr><th><td><hr><figure><figcaption>');
}

$admin  = require_admin();
$method = $_SERVER['REQUEST_METHOD'];
$id     = (int)($_GET['id'] ?? 0);

// LIST
if ($method === 'GET' && !$id) {
    $page    = max(1, (int)($_GET['page']    ?? 1));
    $perPage = min(50, (int)($_GET['per_page'] ?? 20));
    $posts = Post::all([
        'status'   => $_GET['status']   ?? null,
        'category' => (int)($_GET['category'] ?? 0),
        'search'   => trim($_GET['search'] ?? ''),
        'limit'    => $perPage,
        'offset'   => ($page - 1) * $perPage,
    ]);
    $total = Post::count([
        'status'   => $_GET['status']   ?? null,
        'category' => (int)($_GET['category'] ?? 0),
        'search'   => trim($_GET['search'] ?? ''),
    ]);
    jout(['success' => true, 'data' => $posts, 'total' => $total, 'page' => $page]);
}

// GET ONE
if ($method === 'GET' && $id) {
    $post = Post::find($id);
    if (!$post) jout(['error' => 'Not found'], 404);
    jout(['success' => true, 'data' => $post]);
}

// CREATE
if ($method === 'POST' && !isset($_POST['_method'])) {
    $title      = trim($_POST['title']   ?? '');
    $content    = sanitize_html($_POST['content'] ?? '');
    $status     = in_array($_POST['status'] ?? '', ['draft','published']) ? $_POST['status'] : 'draft';
    $isFeatured = (int)($_POST['is_featured'] ?? 0);

    if (!$title)   jout(['error' => 'Title is required'], 422);
    if (!$content) jout(['error' => 'Content is required'], 422);
    if (empty($_FILES['thumbnail']['tmp_name'])) jout(['error' => 'Thumbnail is required'], 422);

    if ($isFeatured && Post::featuredCount() >= MAX_FEATURED_POSTS)
        jout(['error' => 'Max ' . MAX_FEATURED_POSTS . ' featured posts reached'], 422);

    try { $thumb = upload_thumbnail($_FILES['thumbnail']); }
    catch (\RuntimeException $e) { jout(['error' => $e->getMessage()], 422); }

    $slug = Post::uniqueSlug(slugify($_POST['slug'] ?? $title));
    $newId = Post::create([
        'title'            => $title,
        'slug'             => $slug,
        'excerpt'          => trim($_POST['excerpt'] ?? ''),
        'content'          => $content,
        'thumbnail'        => $thumb,
        'category_id'      => (int)($_POST['category_id'] ?? 0) ?: null,
        'author_id'        => $admin['id'],
        'status'           => $status,
        'is_featured'      => $isFeatured,
        'meta_title'       => trim($_POST['meta_title']       ?? ''),
        'meta_description' => trim($_POST['meta_description'] ?? ''),
        'meta_keywords'    => trim($_POST['meta_keywords']    ?? ''),
    ]);
    jout(['success' => true, 'id' => $newId, 'slug' => $slug], 201);
}

// UPDATE
if ($method === 'POST' && ($_POST['_method'] ?? '') === 'PUT') {
    if (!$id) jout(['error' => 'ID required'], 400);
    $existing = Post::find($id);
    if (!$existing) jout(['error' => 'Not found'], 404);

    $title      = trim($_POST['title']   ?? $existing['title']);
    $content    = sanitize_html($_POST['content'] ?? $existing['content']);
    $status     = in_array($_POST['status'] ?? '', ['draft','published']) ? $_POST['status'] : $existing['status'];
    $isFeatured = (int)($_POST['is_featured'] ?? $existing['is_featured']);

    if ($isFeatured && !$existing['is_featured'] && Post::featuredCount() >= MAX_FEATURED_POSTS)
        jout(['error' => 'Max featured posts reached'], 422);

    $rawSlug = trim($_POST['slug'] ?? $existing['slug']);
    $slug    = Post::uniqueSlug(slugify($rawSlug ?: $title), $id);

    $thumb = $existing['thumbnail'];
    if (!empty($_FILES['thumbnail']['tmp_name'])) {
        try { $thumb = upload_thumbnail($_FILES['thumbnail']); }
        catch (\RuntimeException $e) { jout(['error' => $e->getMessage()], 422); }
    }

    Post::update($id, [
        'title'            => $title,
        'slug'             => $slug,
        'excerpt'          => trim($_POST['excerpt']          ?? $existing['excerpt']),
        'content'          => $content,
        'thumbnail'        => $thumb,
        'category_id'      => (int)($_POST['category_id']     ?? $existing['category_id']) ?: null,
        'status'           => $status,
        'is_featured'      => $isFeatured,
        'meta_title'       => trim($_POST['meta_title']       ?? $existing['meta_title']),
        'meta_description' => trim($_POST['meta_description'] ?? $existing['meta_description']),
        'meta_keywords'    => trim($_POST['meta_keywords']    ?? $existing['meta_keywords']),
    ]);
    jout(['success' => true, 'slug' => $slug]);
}

// DELETE
if ($method === 'DELETE') {
    if (!$id) jout(['error' => 'ID required'], 400);
    Post::delete($id);
    jout(['success' => true]);
}

jout(['error' => 'Method not allowed'], 405);
