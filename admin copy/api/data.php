<?php
require_once __DIR__ . '/../../includes/bootstrap.php';

use App\Models\Post;
use App\Models\Faq;

header('Content-Type: application/json');

function jout(array $d, int $c = 200): never { http_response_code($c); echo json_encode($d); exit; }
function require_admin(): array {
    if (empty($_SESSION['admin_id'])) jout(['error' => 'Unauthorized'], 401);
    return ['id' => $_SESSION['admin_id'], 'role' => $_SESSION['admin_role']];
}

$admin    = require_admin();
$resource = $_GET['resource'] ?? '';
$method   = $_SERVER['REQUEST_METHOD'];
$id       = (int)($_GET['id'] ?? 0);
$body     = json_decode(file_get_contents('php://input'), true) ?? [];

// ── CATEGORIES ────────────────────────────────────────────
if ($resource === 'categories') {
    if ($method === 'GET') jout(['success' => true, 'data' => get_db()->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll()]);
    if ($method === 'POST') {
        $name = trim($body['name'] ?? '');
        if (!$name) jout(['error' => 'Name required'], 422);
        $slug = slugify($name); $base = $slug; $i = 1;
        while (get_db()->prepare('SELECT id FROM categories WHERE slug=?')->execute([$slug]) && get_db()->query("SELECT id FROM categories WHERE slug='$slug'")->fetch())
            $slug = $base . '-' . $i++;
        get_db()->prepare('INSERT INTO categories (name, slug, description) VALUES (?,?,?)')->execute([$name, $slug, trim($body['description'] ?? '')]);
        jout(['success' => true, 'id' => get_db()->lastInsertId()]);
    }
    if ($method === 'PUT' && $id) {
        get_db()->prepare('UPDATE categories SET name=?, description=? WHERE id=?')->execute([trim($body['name']),trim($body['description']??''),$id]);
        jout(['success' => true]);
    }
    if ($method === 'DELETE' && $id) { get_db()->prepare('DELETE FROM categories WHERE id=?')->execute([$id]); jout(['success' => true]); }
}

// ── FAQS ──────────────────────────────────────────────────
if ($resource === 'faqs') {
    if ($method === 'GET' && !$id) jout(['success' => true, 'data' => Faq::all()]);
    if ($method === 'GET' && $id)  { $f = Faq::find($id); if (!$f) jout(['error'=>'Not found'],404); jout(['success'=>true,'data'=>$f]); }
    if ($method === 'POST') {
        $q = trim($body['question'] ?? ''); $a = trim($body['answer'] ?? '');
        if (!$q) jout(['error' => 'Question required'], 422);
        if (!$a) jout(['error' => 'Answer required'],   422);
        jout(['success' => true, 'id' => Faq::create(['question'=>$q,'answer'=>$a,'sort_order'=>(int)($body['sort_order']??0),'is_active'=>(int)($body['is_active']??1)])], 201);
    }
    if ($method === 'PUT' && $id) {
        Faq::update($id, ['question'=>trim($body['question']??''),'answer'=>trim($body['answer']??''),'sort_order'=>(int)($body['sort_order']??0),'is_active'=>(int)($body['is_active']??1)]);
        jout(['success' => true]);
    }
    if ($method === 'DELETE' && $id) { Faq::delete($id); jout(['success' => true]); }
}

// ── SETTINGS ─────────────────────────────────────────────
if ($resource === 'settings') {
    if ($method === 'GET') jout(['success' => true, 'data' => get_db()->query('SELECT * FROM settings ORDER BY id ASC')->fetchAll()]);
    if ($method === 'POST') {
        $stmt = get_db()->prepare('UPDATE settings SET setting_value=? WHERE setting_key=?');
        foreach ($body['settings'] ?? [] as $k => $v) $stmt->execute([(string)$v, (string)$k]);
        jout(['success' => true]);
    }
}

// ── CONTENT SECTIONS ─────────────────────────────────────
if ($resource === 'content_sections') {
    if ($method === 'GET') jout(['success' => true, 'data' => get_db()->query('SELECT * FROM content_sections ORDER BY sort_order ASC')->fetchAll()]);
    if ($method === 'POST' && !$id) {
        $k = trim($body['section_key']??''); $t = trim($body['title']??'');
        if (!$k||!$t) jout(['error'=>'Key and title required'],422);
        get_db()->prepare('INSERT INTO content_sections (section_key,title,body,sort_order) VALUES (?,?,?,?)')->execute([$k,$t,$body['body']??'',(int)($body['sort_order']??0)]);
        jout(['success'=>true,'id'=>get_db()->lastInsertId()]);
    }
    if ($method === 'PUT' && $id) {
        get_db()->prepare('UPDATE content_sections SET title=?,body=?,sort_order=?,is_active=? WHERE id=?')
            ->execute([trim($body['title']??''),$body['body']??'',(int)($body['sort_order']??0),(int)($body['is_active']??1),$id]);
        jout(['success'=>true]);
    }
    if ($method === 'DELETE' && $id) { get_db()->prepare('DELETE FROM content_sections WHERE id=?')->execute([$id]); jout(['success'=>true]); }
}

// ── FOOTER LINKS ─────────────────────────────────────────
if ($resource === 'footer_links') {
    if ($method === 'GET') jout(['success'=>true,'data'=>get_db()->query('SELECT * FROM footer_links ORDER BY row_number ASC, sort_order ASC')->fetchAll()]);
    if ($method === 'POST' && !$id) {
        $l=trim($body['label']??''); $u=trim($body['url']??'');
        if (!$l||!$u) jout(['error'=>'Label and URL required'],422);
        get_db()->prepare('INSERT INTO footer_links (label,url,row_number,sort_order) VALUES (?,?,?,?)')->execute([$l,$u,(int)($body['row_number']??1),(int)($body['sort_order']??0)]);
        jout(['success'=>true,'id'=>get_db()->lastInsertId()]);
    }
    if ($method === 'PUT' && $id) {
        get_db()->prepare('UPDATE footer_links SET label=?,url=?,row_number=?,sort_order=?,is_active=? WHERE id=?')
            ->execute([trim($body['label']??''),trim($body['url']??''),(int)($body['row_number']??1),(int)($body['sort_order']??0),(int)($body['is_active']??1),$id]);
        jout(['success'=>true]);
    }
    if ($method === 'DELETE' && $id) { get_db()->prepare('DELETE FROM footer_links WHERE id=?')->execute([$id]); jout(['success'=>true]); }
}

// ── ANALYTICS ─────────────────────────────────────────────
if ($resource === 'analytics') {
    $type = $_GET['type'] ?? 'overview';
    if ($type === 'overview') {
        jout(['success'=>true,'data'=>[
            'total_generations' => (int)get_db()->query('SELECT COUNT(*) FROM generation_logs')->fetchColumn(),
            'today_generations' => (int)get_db()->query("SELECT COUNT(*) FROM generation_logs WHERE DATE(generated_at)=CURDATE()")->fetchColumn(),
            'published_posts'   => Post::count(['status'=>'published']),
            'total_post_views'  => (int)get_db()->query('SELECT COALESCE(SUM(views),0) FROM posts')->fetchColumn(),
        ]]);
    }
    if ($type === 'generations_by_locale') {
        jout(['success'=>true,'data'=>get_db()->query('SELECT generated_locale AS locale, COUNT(*) AS count FROM generation_logs GROUP BY generated_locale ORDER BY count DESC LIMIT 15')->fetchAll()]);
    }
    if ($type === 'top_posts') {
        jout(['success'=>true,'data'=>Post::all(['status'=>'published','limit'=>10,'order_by'=>'p.views DESC'])]);
    }
}

jout(['error' => 'Unknown resource'], 400);
