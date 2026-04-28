<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/includes/bootstrap.php';

use App\AddressGenerator;
use App\LocaleRegistry;
use App\Models\Post;
use App\Models\Faq;

// Default locale
$locale      = $_GET['locale'] ?? 'en_US';
$gender      = $_GET['gender'] ?? 'random';
$localeInfo  = LocaleRegistry::get($locale) ?? LocaleRegistry::get('en_US');

// Generate initial address
$address     = AddressGenerator::generate($locale, $gender);
AddressGenerator::log($address);

// DB data
$contentSections = get_db()->query('SELECT * FROM content_sections WHERE is_active = 1 ORDER BY sort_order ASC')->fetchAll();
$latestPosts     = Post::latest(5);
$featuredPosts   = Post::featured(5);
$faqs            = Faq::all(true);
$allLocales      = LocaleRegistry::all();

$pageTitle    = get_setting('site_name', 'Fake Address Generator') . ' — Random Fake Address Generator';
$metaDesc     = 'Generate highly realistic fake addresses for any country using our free tool. Supports 300+ locales worldwide.';
$metaKeywords = 'fake address generator, random address, dummy address, test data';

require_once __DIR__ . '/templates/header.php';
?>

<section class="hero">
  <h1>Random Fake Address Generator</h1>
  <p>Generate realistic fake addresses for any country. Powered by FakerPHP — supports 300+ locales worldwide.</p>
</section>

<div class="main-layout">
  <div class="primary-content">

    <!-- Generator Widget -->
    <div class="addr-card-wrapper card">
      <div class="card-header">
        <h3>
          <?= e($localeInfo[4] ?? '') ?>
          Generated Address — <?= e($localeInfo[1] ?? $locale) ?>
          <?php if (!App\LocaleRegistry::isFakerNative($locale)): ?>
            <small style="font-size:.7rem;opacity:.7;font-weight:400">(en_US fallback)</small>
          <?php endif; ?>
        </h3>
      </div>
      <div id="addr-loading"><span class="spinner"></span> Generating…</div>

      <div class="address-display card-body" id="addr-display">
        <?php
        $labelMap = [
          'name'           => 'Full Name',
          'gender'         => 'Gender',
          'street_address' => 'Street Address',
          'city'           => 'City',
          'state'          => 'State / Region',
          'postcode'       => 'Postcode / ZIP',
          'country_name'   => 'Country',
          'phone'          => 'Phone',
        ];
        $skip = ['first_name', 'last_name', 'locale', 'faker_locale', 'country', 'country_code', 'flag', 'generated_at'];
        foreach ($labelMap as $key => $label):
          if (!isset($address[$key])) continue;
          $val = $address[$key];
          $id  = 'addr-' . str_replace('_', '-', $key);
        ?>
          <div class="addr-row">
            <span class="addr-label"><?= e($label) ?>:</span>
            <strong class="addr-value" id="<?= $id ?>"><?= e((string)$val) ?></strong>
            <button class="copy-btn" data-target="<?= $id ?>" type="button">Copy</button>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- Hidden form synced by JS -->
      <form id="addr-form" style="display:none">
        <input type="hidden" name="locale" value="<?= e($locale) ?>">
        <input type="hidden" name="gender" value="<?= e($gender) ?>">
      </form>

      <div style="padding:0 20px 16px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <button class="btn-generate" id="btn-generate" type="button">↻ Generate New Address</button>
        <button class="btn btn-outline" id="copy-all-btn" type="button">Copy All</button>
      </div>
    </div>

    <!-- Dynamic Content Sections -->
    <?php foreach ($contentSections as $section): ?>
      <section class="content-section">
        <h2><?= e($section['title']) ?></h2>
        <?= $section['body'] ?>
      </section>
    <?php endforeach; ?>

    <!-- FAQ -->
    <?php if (!empty($faqs)): ?>
      <section class="faq-section">
        <h2>Frequently Asked Questions</h2>
        <div class="accordion">
          <?php foreach ($faqs as $i => $faq): ?>
            <div class="accordion-item">
              <input type="checkbox" id="faq<?= $i ?>" class="accordion-input">
              <label for="faq<?= $i ?>" class="accordion-label"><?= e($faq['question']) ?></label>
              <div class="accordion-content">
                <p><?= e($faq['answer']) ?></p>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </section>
    <?php endif; ?>

    <!-- Blog Preview -->
    <?php if (!empty($latestPosts)): ?>
      <section class="content-section">
        <h2>Latest Guides &amp; Articles</h2>
        <div class="blog-grid">
          <?php foreach ($latestPosts as $post): ?>
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
                <h3 class="blog-card-title">
                  <a href="<?= BASE_URL ?>/blog/<?= e($post['slug']) ?>"><?= e($post['title']) ?></a>
                </h3>
                <p class="blog-card-excerpt"><?= e($post['excerpt'] ?: excerpt($post['content'])) ?></p>
                <div class="blog-card-meta">
                  <span><?= fmt_date($post['published_at'] ?? $post['created_at']) ?></span>
                  <span><?= number_format((int)$post['views']) ?> views</span>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>
        <div class="text-center mt-4">
          <a href="<?= BASE_URL ?>/blog" class="btn btn-outline">View All Articles →</a>
        </div>
      </section>
    <?php endif; ?>

  </div><!-- /primary-content -->

  <?php require __DIR__ . '/templates/sidebar.php'; ?>
</div>

<?php require __DIR__ . '/templates/footer.php'; ?>