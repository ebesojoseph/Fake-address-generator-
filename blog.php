<?php
// blog.php — Blog archive (/blog)

require_once __DIR__ . '/includes/bootstrap.php';

$perPage     = 9;
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$catFilter   = (int)($_GET['cat'] ?? 0);

// Count total matching posts
$where  = 'p.status = "published"';
$params = [];
if ($catFilter > 0) {
    $where   .= ' AND p.category_id = ?';
    $params[] = $catFilter;
}

$totalStmt = get_db()->prepare("SELECT COUNT(*) FROM posts p WHERE $where");
$totalStmt->execute($params);
$total = (int)$totalStmt->fetchColumn();

$pager  = paginate($total, $perPage, $currentPage);

// Fetch posts
$stmt = get_db()->prepare(
    "SELECT p.*, c.name AS category_name, c.slug AS category_slug
     FROM posts p
     LEFT JOIN categories c ON c.id = p.category_id
     WHERE $where
     ORDER BY p.published_at DESC
     LIMIT {$perPage} OFFSET {$pager['offset']}"
);
$stmt->execute($params);
$posts = $stmt->fetchAll();

// All categories for filter bar
$cats = get_db()->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();

// Featured posts for sidebar
$stmt = get_db()->prepare('SELECT id, title, slug, thumbnail FROM posts WHERE is_featured = 1 AND status = "published" ORDER BY published_at DESC LIMIT 5');
$stmt->execute();
$featuredPosts = $stmt->fetchAll();

$pageTitle    = 'Blog — Guides & Tutorials';
$metaDesc     = 'Developer guides, privacy tips, and tutorials about fake address generation, data masking, and software testing.';
$metaKeywords = 'fake address blog, developer testing guides, privacy tutorials';

require_once __DIR__ . '/templates/header.php';
?>

<section class="hero" style="padding:28px 20px;">
  <h1>Guides &amp; Articles</h1>
  <p class="subtitle">Tutorials, tips, and resources for developers and privacy-conscious users.</p>
</section>

<div class="main-layout">
  <div class="primary-content">

    <!-- Category filter -->
    <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:20px;">
      <a href="<?= BASE_URL ?>/blog" class="btn btn-<?= $catFilter === 0 ? 'primary' : 'outline' ?>" style="padding:6px 14px; font-size:.85rem;">All</a>
      <?php foreach ($cats as $cat): ?>
        <a href="<?= BASE_URL ?>/blog?cat=<?= $cat['id'] ?>" class="btn btn-<?= $catFilter === (int)$cat['id'] ? 'primary' : 'outline' ?>" style="padding:6px 14px; font-size:.85rem;">
          <?= e($cat['name']) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <?php if (empty($posts)): ?>
      <div class="alert alert-info">No posts found.</div>
    <?php else: ?>
      <div class="blog-grid">
        <?php foreach ($posts as $post): ?>
          <article class="blog-card">
            <?php if ($post['thumbnail']): ?>
            <div class="blog-card-thumb">
              <a href="<?= BASE_URL ?>/blog/<?= e($post['slug']) ?>">
                <img src="<?= BASE_URL . '/' . e($post['thumbnail']) ?>" alt="<?= e($post['title']) ?>" loading="lazy">
              </a>
            </div>
            <?php endif; ?>
            <div class="blog-card-body">
              <?php if ($post['category_name']): ?>
                <span class="blog-card-cat"><?= e($post['category_name']) ?></span>
              <?php endif; ?>
              <h2 class="blog-card-title" style="font-size:1rem;">
                <a href="<?= BASE_URL ?>/blog/<?= e($post['slug']) ?>"><?= e($post['title']) ?></a>
              </h2>
              <p class="blog-card-excerpt"><?= e($post['excerpt'] ?: excerpt($post['content'])) ?></p>
              <div class="blog-card-meta">
                <span><?= fmt_date($post['published_at'] ?? $post['created_at']) ?></span>
                <span><?= (int)$post['views'] ?> views</span>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <!-- Pagination -->
      <?php if ($pager['total_pages'] > 1): ?>
      <nav class="blog-pagination" aria-label="Blog pagination">
        <?php
        $base = BASE_URL . '/blog' . ($catFilter ? '?cat=' . $catFilter . '&page=' : '?page=');
        if ($pager['current_page'] > 1):
        ?>
          <a href="<?= $base . ($pager['current_page'] - 1) ?>">&larr; Prev</a>
        <?php endif;
        for ($i = 1; $i <= $pager['total_pages']; $i++):
          if ($i === $pager['current_page']): ?>
            <span class="active"><?= $i ?></span>
          <?php else: ?>
            <a href="<?= $base . $i ?>"><?= $i ?></a>
          <?php endif;
        endfor;
        if ($pager['current_page'] < $pager['total_pages']): ?>
          <a href="<?= $base . ($pager['current_page'] + 1) ?>">Next &rarr;</a>
        <?php endif; ?>
      </nav>
      <?php endif; ?>
    <?php endif; ?>

  </div><!-- /primary-content -->

  <?php require __DIR__ . '/templates/sidebar.php'; ?>
</div>

<?php require __DIR__ . '/templates/footer.php'; ?>
