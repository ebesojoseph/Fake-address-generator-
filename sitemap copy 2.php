<?php
require_once __DIR__ . '/includes/bootstrap.php';

use App\Models\Post;
use App\LocaleRegistry;

header('Content-Type: application/xml; charset=UTF-8');

$posts = Post::all(['status' => 'published', 'limit' => 5000, 'order_by' => 'p.published_at DESC']);

$staticPages = [
    ['loc' => '/',            'priority' => '1.0',  'changefreq' => 'daily'],
    ['loc' => '/blog',        'priority' => '0.9',  'changefreq' => 'daily'],
    ['loc' => '/fake-address','priority' => '0.95', 'changefreq' => 'weekly'],
];

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

  <?php foreach ($staticPages as $p): ?>
  <url>
    <loc><?= e(BASE_URL . $p['loc']) ?></loc>
    <changefreq><?= $p['changefreq'] ?></changefreq>
    <priority><?= $p['priority'] ?></priority>
  </url>
  <?php endforeach; ?>

  <?php foreach (LocaleRegistry::all() as $code => $info): ?>
  <url>
    <loc><?= e(BASE_URL . '/fake-address/' . LocaleRegistry::toSlug($code)) ?></loc>
    <changefreq>monthly</changefreq>
    <priority>0.8</priority>
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
