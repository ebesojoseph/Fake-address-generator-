<?php
require_once __DIR__ . '/includes/bootstrap.php';

use App\Models\Post;
use App\Models\Faq;

// Extract slug from URL path
$path  = current_path();
$slug  = preg_replace('#^/blog/#', '', $path);

$post  = Post::findBySlug($slug);

if (!$post) {
    http_response_code(404);
    $pageTitle = '404 — Post Not Found';
    require __DIR__ . '/templates/header.php';
    echo '<div class="main-layout"><div class="primary-content"><p class="alert alert-error">Post not found. <a href="' . BASE_URL . '/blog">← Back to Blog</a></p></div></div>';
    require __DIR__ . '/templates/footer.php';
    exit;
}

Post::incrementViews($post['id']);

$related       = Post::related($post['id'], (int)$post['category_id']);
$featuredPosts = Post::featured(5);
$faqs          = Faq::all(true);

$pageTitle    = $post['meta_title']       ?: $post['title'];
$metaDesc     = $post['meta_description'] ?: excerpt($post['content'], 30);
$metaKeywords = $post['meta_keywords']    ?: '';

require __DIR__ . '/templates/header.php';
?>

<div class="main-layout">
  <article class="primary-content">
    <div style="max-width:760px;">

      <!-- Breadcrumb -->
      <nav style="font-size:.82rem;color:#aaa;margin-bottom:18px;">
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
        <span class="blog-card-cat"><?= e($post['category_name']) ?></span>
      <?php endif; ?>

      <h1 class="post-title" style="margin-top:10px;"><?= e($post['title']) ?></h1>

      <div class="post-meta">
        <span>📅 <?= fmt_date($post['published_at'] ?? $post['created_at']) ?></span>
        <span>👁 <?= number_format((int)$post['views']) ?> views</span>
        <?php if ($post['author_name']): ?>
          <span>✍ <?= e($post['author_name']) ?></span>
        <?php endif; ?>
      </div>

      <div class="post-content"><?= $post['content'] ?></div>

      <?php if (!empty($related)): ?>
        <hr style="margin:36px 0;border:none;border-top:1px solid var(--border);">
        <h3 style="margin-bottom:16px;">Related Articles</h3>
        <div class="blog-grid" style="grid-template-columns:repeat(auto-fill,minmax(200px,1fr));">
          <?php foreach ($related as $rp): ?>
            <article class="blog-card">
              <?php if ($rp['thumbnail']): ?>
                <div class="blog-card-thumb">
                  <a href="<?= BASE_URL ?>/blog/<?= e($rp['slug']) ?>">
                    <img src="<?= BASE_URL . '/' . e($rp['thumbnail']) ?>" alt="<?= e($rp['title']) ?>" loading="lazy">
                  </a>
                </div>
              <?php endif; ?>
              <div class="blog-card-body">
                <h3 class="blog-card-title" style="font-size:.875rem;">
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
