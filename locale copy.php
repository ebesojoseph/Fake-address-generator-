<?php
// locale.php — handles /fake-address/{country-name-slug}

require_once __DIR__ . '/includes/bootstrap.php';

use App\AddressGenerator;
use App\LocaleRegistry;
use App\Models\Post;
use App\Models\Faq;

// Resolve slug from URL
$path = current_path();
preg_match('#^/fake-address/(.+)$#', $path, $m);
$slug = $m[1] ?? '';

if (empty($slug)) {
    // /fake-address — show all locales browser
    require __DIR__ . '/locales.php';
    exit;
}

// Find the locale from the slug
$localeData = LocaleRegistry::fromSlug($slug);

if (!$localeData) {
    http_response_code(404);
    $pageTitle = '404 — Locale Not Found';
    require __DIR__ . '/templates/header.php';
    echo '<div class="main-layout"><div class="primary-content">';
    echo '<div class="alert alert-error">Locale not found. <a href="' . BASE_URL . '/fake-address">Browse all locales →</a></div>';
    echo '</div></div>';
    require __DIR__ . '/templates/footer.php';
    exit;
}

$locale      = $localeData['locale'];
$gender      = $_GET['gender'] ?? 'random';
$address     = AddressGenerator::generate($locale, $gender);
AddressGenerator::log($address);

$featuredPosts = Post::featured(5);
$faqs          = Faq::all(true);

$isFallback   = !LocaleRegistry::isFakerNative($locale);
$countryName  = $localeData['english_name'];
$nativeName   = $localeData['native_name'];
$flag         = $localeData['flag'];

$pageTitle    = "Fake {$countryName} Address Generator";
$metaDesc     = "Generate realistic fake {$countryName} addresses including names, phone numbers, and postal codes. Free online tool.";
$metaKeywords = "fake {$countryName} address, random {$countryName} address generator, dummy address";

require __DIR__ . '/templates/header.php';
?>

<section class="hero">
  <h1><?= $flag ?> Fake <?= e($countryName) ?> Address Generator</h1>
  <p>
    Generate realistic fake <?= e($countryName) ?> addresses instantly.
    <?php if ($isFallback): ?>
      <span style="opacity:.7;font-size:.9em">(Uses en_US data — native locale not yet in Faker)</span>
    <?php endif; ?>
  </p>
</section>

<div class="main-layout">
  <div class="primary-content">

    <!-- Generator Widget -->
    <div class="addr-card-wrapper card">
      <div class="card-header">
        <h3><?= $flag ?> <?= e($countryName) ?> Address <small style="font-size:.7rem;opacity:.7">locale: <?= e($locale) ?></small></h3>
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
          // 'mobile'         => 'Mobile',
          // 'email'          => 'Email',
          // 'username'       => 'Username',
          // 'company'        => 'Company',
          // 'job_title'      => 'Job Title',
          // 'time_zone'      => 'Time Zone',
          // 'ssn'            => 'SSN / NIN',
          // 'latitude'       => 'Latitude',
          // 'longitude'      => 'Longitude',
        ];
        foreach ($labelMap as $key => $label):
          if (!isset($address[$key])) continue;
          $id = 'addr-' . str_replace('_', '-', $key);
        ?>
          <div class="addr-row">
            <span class="addr-label"><?= e($label) ?>:</span>
            <strong class="addr-value" id="<?= $id ?>"><?= e((string)$address[$key]) ?></strong>
            <button class="copy-btn" data-target="<?= $id ?>" type="button">Copy</button>
          </div>
        <?php endforeach; ?>
      </div>

      <form id="addr-form" style="display:none">
        <input type="hidden" name="locale" value="<?= e($locale) ?>">
        <input type="hidden" name="gender" value="<?= e($gender) ?>">
      </form>

      <div style="padding:0 20px 16px;display:flex;gap:10px;flex-wrap:wrap;">
        <button class="btn-generate" id="btn-generate" type="button">↻ Generate New Address</button>
        <button class="btn btn-outline" id="copy-all-btn" type="button">Copy All</button>
      </div>
    </div>

    <!-- About this locale -->
    <section class="content-section">
      <h2>About <?= e($countryName) ?> Addresses</h2>
      <div class="text-grid">
        <article>
          <h3>Native Name</h3>
          <p><?= e($nativeName) ?></p>
        </article>
        <article>
          <h3>Locale Code</h3>
          <p><?= e($locale) ?> <?= $isFallback ? '(falls back to en_US)' : '(native Faker support)' ?></p>
        </article>
        <article>
          <h3>Realistic Format</h3>
          <p>All generated addresses follow authentic formatting conventions including correct postal code structures and phone number patterns.</p>
        </article>
        <article>
          <h3>Privacy Safe</h3>
          <p>No real personal data is used or stored. All names and contact details are algorithmically generated and fictional.</p>
        </article>
      </div>
    </section>

    <!-- FAQs -->
    <?php if (!empty($faqs)): ?>
    <section class="faq-section">
      <h2>Frequently Asked Questions</h2>
      <div class="accordion">
        <?php foreach ($faqs as $i => $faq): ?>
          <div class="accordion-item">
            <input type="checkbox" id="faq<?= $i ?>" class="accordion-input">
            <label for="faq<?= $i ?>" class="accordion-label"><?= e($faq['question']) ?></label>
            <div class="accordion-content"><p><?= e($faq['answer']) ?></p></div>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

  </div>

  <?php require __DIR__ . '/templates/sidebar.php'; ?>
</div>

<?php require __DIR__ . '/templates/footer.php'; ?>
