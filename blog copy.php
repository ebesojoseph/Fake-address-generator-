<?php
require_once __DIR__ . '/includes/bootstrap.php';

use App\Models\Post;

$perPage  = 9;
$page     = max(1, (int)($_GET['page'] ?? 1));
$catFilter= (int)($_GET['cat'] ?? 0);
$search   = trim($_GET['q'] ?? '');

$opts  = ['status' => 'published', 'limit' => $perPage, 'offset' => ($page - 1) * $perPage];
if ($catFilter) $opts['category'] = $catFilter;
if ($search)    $opts['search']   = $search;

$posts = Post::all($opts);
$total = Post::count(array_diff_key($opts, ['limit'=>1,'offset'=>1]));
$pages = (int)ceil($total / $perPage);
$cats  = get_db()->query('SELECT * FROM categories ORDER BY name ASC')->fetchAll();

$featuredPosts = Post::featured(5);
$pageTitle     = 'Blog — Guides & Tutorials';
$metaDesc      = 'Developer guides, privacy tips, and tutorials about fake address generation and software testing.';

require __DIR__ . '/templates/header.php';
?>
<section class="hero" style="padding:28px 20px;">
  <h1>Guides &amp; Articles</h1>
  <p>Tutorials, tips, and resources for developers and privacy-conscious users.</p>
</section>

<div class="main-layout">
  <div class="primary-content">

    <!-- Filters -->
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px;align-items:center;">
      <form method="get" action="<?= BASE_URL ?>/blog" style="display:flex;gap:8px;flex:1;max-width:360px;">
        <input name="q" value="<?= e($search) ?>" placeholder="Search articles…"
          style="flex:1;padding:8px 12px;border:1px solid var(--border);border-radius:6px;font-size:13px;">
        <?php if ($catFilter): ?><input type="hidden" name="cat" value="<?= $catFilter ?>"><?php endif; ?>
        <button type="submit" class="btn btn-primary" style="padding:8px 14px;">Search</button>
      </form>

      <a href="<?= BASE_URL ?>/blog" class="btn <?= !$catFilter ? 'btn-primary' : 'btn-outline' ?>" style="padding:7px 14px;font-size:.85rem;">All</a>
      <?php foreach ($cats as $c): ?>
        <a href="<?= BASE_URL ?>/blog?cat=<?= $c['id'] ?><?= $search ? '&q='.urlencode($search) : '' ?>"
          class="btn <?= $catFilter===$c['id'] ? 'btn-primary' : 'btn-outline' ?>" style="padding:7px 14px;font-size:.85rem;">
          <?= e($c['name']) ?>
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
              <h2 class="blog-card-title" style="font-size:.95rem;">
                <a href="<?= BASE_URL ?>/blog/<?= e($post['slug']) ?>"><?= e($post['title']) ?></a>
              </h2>
              <p class="blog-card-excerpt"><?= e($post['excerpt'] ?: excerpt($post['content'])) ?></p>
              <div class="blog-card-meta">
                <span><?= fmt_date($post['published_at'] ?? $post['created_at']) ?></span>
                <span><?= number_format((int)$post['views']) ?> views</span>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <?php if ($pages > 1): ?>
        <nav class="blog-pagination">
          <?php
          $base = BASE_URL . '/blog?' . ($catFilter?"cat=$catFilter&":'') . ($search?'q='.urlencode($search).'&':'') . 'page=';
          if ($page > 1) echo "<a href='{$base}".($page-1)."'>← Prev</a>";
          for ($i = max(1,$page-2); $i <= min($pages,$page+2); $i++)
            echo $i===$page ? "<span class='active'>$i</span>" : "<a href='{$base}$i'>$i</a>";
          if ($page < $pages) echo "<a href='{$base}".($page+1)."'>Next →</a>";
          ?>
        </nav>
      <?php endif; ?>
    <?php endif; ?>
  </div>

  <?php require __DIR__ . '/templates/sidebar.php'; ?>
</div>

<?php require __DIR__ . '/templates/footer.php'; ?>
