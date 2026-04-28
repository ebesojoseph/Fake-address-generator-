<?php
// blog/post.php — routed via .htaccess to /blog/{slug}
require_once __DIR__ . '/../includes/bootstrap.php';

$slug = trim($_GET['slug'] ?? '', '/');
if (!$slug || !preg_match('/^[a-z0-9\-]+$/', $slug)) {
    http_response_code(404);
    include __DIR__ . '/../templates/404.php';
    exit;
}

$post = PostModel::get_by_slug($slug);
if (!$post) {
    http_response_code(404);
    include __DIR__ . '/../templates/404.php';
    exit;
}

PostModel::increment_view($post['id']);

$meta_title       = $post['meta_title'] ?: $post['title'];
$meta_description = $post['meta_description'] ?: mb_substr(strip_tags($post['excerpt']), 0, 160);
$meta_keywords    = $post['meta_keywords'] ?? '';
$featured_posts   = PostModel::get_featured();

include __DIR__ . '/../templates/header.php';
?>
<article class="single-post">
  <h1><?= e($post['title']) ?></h1>
  <div class="post-meta">
    <?php if ($post['category_name']): ?>
      <span class="category-badge"><?= e($post['category_name']) ?></span>
    <?php endif; ?>
    <span class="date">📅 <?= date('F j, Y', strtotime($post['created_at'])) ?></span>
    <span class="views">👁 <?= number_format($post['view_count']) ?> views</span>
  </div>

  <?php if ($post['thumbnail']): ?>
    <img class="post-thumbnail" src="/uploads/thumbnails/<?= e($post['thumbnail']) ?>" alt="<?= e($post['title']) ?>" />
  <?php endif; ?>

  <div class="post-content">
    <?= $post['content'] ?>
  </div>
</article>

<?php include __DIR__ . '/../templates/footer.php'; ?>