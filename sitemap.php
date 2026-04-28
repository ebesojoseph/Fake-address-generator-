<?php
// sitemap.php — Dynamically generated XML sitemap

require_once __DIR__ . '/includes/bootstrap.php';

header('Content-Type: application/xml; charset=UTF-8');

// Fetch all published blog posts
$stmt = db()->query('SELECT slug, updated_at, published_at FROM posts WHERE status = "published" ORDER BY published_at DESC');
$posts = $stmt->fetchAll();

// Static pages
$staticPages = [
    ['loc' => '/',               'priority' => '1.0',  'changefreq' => 'daily'],
    ['loc' => '/blog',           'priority' => '0.9',  'changefreq' => 'daily'],
    ['loc' => '/us-fake-address','priority' => '0.85', 'changefreq' => 'weekly'],
    ['loc' => '/uk-fake-address','priority' => '0.85', 'changefreq' => 'weekly'],
    ['loc' => '/au-fake-address','priority' => '0.80', 'changefreq' => 'weekly'],
    ['loc' => '/ca-fake-address','priority' => '0.80', 'changefreq' => 'weekly'],
    ['loc' => '/de-fake-address','priority' => '0.75', 'changefreq' => 'weekly'],
    ['loc' => '/jp-fake-address','priority' => '0.75', 'changefreq' => 'weekly'],
    ['loc' => '/fake-address',   'priority' => '0.85', 'changefreq' => 'weekly'],
];

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

  <?php foreach ($staticPages as $page): ?>
  <url>
    <loc><?= e(BASE_URL . $page['loc']) ?></loc>
    <changefreq><?= $page['changefreq'] ?></changefreq>
    <priority><?= $page['priority'] ?></priority>
  </url>
  <?php endforeach; ?>

  <?php foreach ($posts as $post): ?>
  <url>
    <loc><?= e(BASE_URL . '/blog/' . $post['slug']) ?></loc>
    <lastmod><?= date('Y-m-d', strtotime($post['updated_at'] ?? $post['published_at'])) ?></lastmod>
    <changefreq>monthly</changefreq>
    <priority>0.7</priority>
  </url>
  <?php endforeach; ?>

</urlset>
