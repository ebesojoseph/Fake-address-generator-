<?php
// post.php — Single blog post (/blog/{slug})

require_once __DIR__ . '/includes/bootstrap.php';

// Extract slug from URL
$slug = trim(str_replace('/blog/', '', current_path()), '/');
if (empty($slug)) {
    header('Location: ' . BASE_URL . '/blog');
    exit;
}

// Fetch post
$stmt = db()->prepare(
    'SELECT p.*, c.name AS category_name, c.slug AS category_slug
     FROM posts p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE p.slug = ? AND p.status = "published"
     LIMIT 1'
);
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    $pageTitle = '404 Not Found';
    require_once __DIR__ . '/templates/header.php';
    echo '<div class="main-layout"><div class="primary-content"><div class="alert alert-error">Post not found.</div><a href="' . BASE_URL . '/blog" class="btn btn-primary">← Back to Blog</a></div></div>';
    require_once __DIR__ . '/templates/footer.php';
    exit;
}

// Increment view count (fire-and-forget)
try {
    db()->prepare('UPDATE posts SET views = views + 1 WHERE id = ?')->execute([$post['id']]);
} catch (Throwable) {}

// Fetch featured posts for sidebar
$stmt = db()->prepare('SELECT id, title, slug, thumbnail FROM posts WHERE is_featured = 1 AND status = "published" ORDER BY published_at DESC LIMIT 5');
$stmt->execute();
$featuredPosts = $stmt->fetchAll();

// Related posts (same category, excluding current)
$relatedPosts = [];
if ($post['category_id']) {
    $stmt = db()->prepare('SELECT id, title, slug, thumbnail FROM posts WHERE category_id = ? AND id != ? AND status = "published" ORDER BY published_at DESC LIMIT 3');
    $stmt->execute([$post['category_id'], $post['id']]);
    $relatedPosts = $stmt->fetchAll();
}

$pageTitle    = $post['meta_title']       ?: $post['title'];
$metaDesc     = $post['meta_description'] ?: excerpt($post['content'], 30);
$metaKeywords = $post['meta_keywords']    ?: '';

require_once __DIR__ . '/templates/header.php';
?>

<div class="main-layout">
  <article class="primary-content">
    <div class="post-single">

      <nav style="font-size:.85rem; color:#aaa; margin-bottom:16px;">
        <a href="<?= BASE_URL ?>/">Home</a> &rsaquo;
        <a href="<?= BASE_URL ?>/blog">Blog</a>
        <?php if ($post['category_name']): ?>
          &rsaquo; <a href="<?= BASE_URL ?>/blog?cat=<?= $post['category_id'] ?>"><?= e($post['category_name']) ?></a>
        <?php endif; ?>
      </nav>

      <?php if ($post['thumbnail']): ?>
        <img class="post-hero-img" src="<?= BASE_URL . '/' . e($post['thumbnail']) ?>" alt="<?= e($post['title']) ?>">
      <?php endif; ?>

      <?php if ($post['category_name']): ?>
        <div class="post-category">
          <span class="blog-card-cat"><?= e($post['category_name']) ?></span>
        </div>
      <?php endif; ?>

      <h1 class="post-title"><?= e($post['title']) ?></h1>

      <div class="post-meta">
        <span>📅 <?= fmt_date($post['published_at'] ?? $post['created_at']) ?></span>
        <span>👁 <?= number_format((int)$post['views']) ?> views</span>
      </div>

      <div class="post-content">
        <?= $post['content'] /* HTML from Quill — sanitized on save */ ?>
      </div>

      <?php if (!empty($relatedPosts)): ?>
        <hr style="margin:32px 0; border:none; border-top:1px solid #eee;">
        <h3 style="margin-bottom:16px;">Related Articles</h3>
        <div class="blog-grid" style="grid-template-columns:repeat(auto-fill,minmax(220px,1fr));">
          <?php foreach ($relatedPosts as $rp): ?>
            <article class="blog-card">
              <?php if ($rp['thumbnail']): ?>
                <div class="blog-card-thumb">
                  <a href="<?= BASE_URL ?>/blog/<?= e($rp['slug']) ?>">
                    <img src="<?= BASE_URL . '/' . e($rp['thumbnail']) ?>" alt="<?= e($rp['title']) ?>" loading="lazy">
                  </a>
                </div>
              <?php endif; ?>
              <div class="blog-card-body">
                <h3 class="blog-card-title" style="font-size:.9rem;">
                  <a href="<?= BASE_URL ?>/blog/<?= e($rp['slug']) ?>"><?= e($rp['title']) ?></a>
                </h3>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <div style="margin-top:28px;">
        <a href="<?= BASE_URL ?>/blog" class="btn btn-outline">← Back to Blog</a>
      </div>

    </div>
  </article>

  <?php require __DIR__ . '/templates/sidebar.php'; ?>
</div>

<?php require __DIR__ . '/templates/footer.php'; ?>
