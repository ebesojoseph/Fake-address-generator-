<?php
// blog/index.php
require_once __DIR__ . '/../includes/bootstrap.php';

$current_page = max(1, (int)($_GET['page'] ?? 1));
$total = PostModel::count_published();
$pager = paginate($total, POSTS_PER_PAGE, $current_page);
$posts = PostModel::get_published_posts(POSTS_PER_PAGE, $pager['offset']);
$featured_posts = PostModel::get_featured();

$meta_title = 'Blog — Fake Address Generator Guides & Articles';
$meta_description = 'Developer guides, privacy tips, and tutorials about fake data, address generators, API testing, and data masking.';

include __DIR__ . '/../templates/header.php';
?>
<main class="blog-archive">
  <h1>Blog &amp; Guides</h1>

  <?php if (empty($posts)): ?>
    <p style="color: var(--text-muted);">No posts published yet.</p>
  <?php else: ?>
    <div class="blog-grid">
      <?php foreach ($posts as $post): ?>
      <div class="blog-card">
        <a href="/blog/<?= e($post['slug']) ?>">
          <div class="blog-card-img">
            <?php if ($post['thumbnail']): ?>
              <img src="/uploads/thumbnails/<?= e($post['thumbnail']) ?>" alt="<?= e($post['title']) ?>" loading="lazy" />
            <?php endif; ?>
          </div>
        </a>
        <div class="blog-card-body">
          <?php if ($post['category_name']): ?>
            <span class="blog-card-cat"><?= e($post['category_name']) ?></span>
          <?php endif; ?>
          <a href="/blog/<?= e($post['slug']) ?>">
            <div class="blog-card-title"><?= e($post['title']) ?></div>
          </a>
          <?php if ($post['excerpt']): ?>
            <p class="blog-card-excerpt"><?= e(mb_substr(strip_tags($post['excerpt']), 0, 120)) ?>...</p>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($pager['total_pages'] > 1): ?>
    <nav class="pagination">
      <?php if ($pager['has_prev']): ?>
        <a href="/blog?page=<?= $pager['current_page'] - 1 ?>">&laquo; Prev</a>
      <?php endif; ?>
      <?php for ($p = 1; $p <= $pager['total_pages']; $p++): ?>
        <?php if ($p === $pager['current_page']): ?>
          <span class="active"><?= $p ?></span>
        <?php else: ?>
          <a href="/blog?page=<?= $p ?>"><?= $p ?></a>
        <?php endif; ?>
      <?php endfor; ?>
      <?php if ($pager['has_next']): ?>
        <a href="/blog?page=<?= $pager['current_page'] + 1 ?>">Next &raquo;</a>
      <?php endif; ?>
    </nav>
    <?php endif; ?>
  <?php endif; ?>
</main>

<?php include __DIR__ . '/../templates/footer.php'; ?>