<?php
// country.php — Country-specific address generator pages

require_once __DIR__ . '/includes/bootstrap.php';

// Detect country from URL
$path = current_path();
preg_match('#^/([a-z]{2})-fake-address#', $path, $m);
$defaultCountry = $m[1] ?? 'us';

// If /fake-address (all countries page)
if ($path === '/fake-address') {
    $defaultCountry = $_GET['country'] ?? 'us';
}

// Country display names
$countryNames = [
    'us' => 'United States', 'uk' => 'United Kingdom',
    'au' => 'Australia',     'ca' => 'Canada',
    'de' => 'Germany',       'jp' => 'Japan',
];
$countryName = $countryNames[$defaultCountry] ?? 'United States';

// Generate initial address
$initialAddress = AddressGenerator::generate($defaultCountry);
AddressGenerator::log($initialAddress);

// Fetch featured posts for sidebar
$stmt = db()->prepare('SELECT id, title, slug, thumbnail FROM posts WHERE is_featured = 1 AND status = "published" ORDER BY published_at DESC LIMIT 5');
$stmt->execute();
$featuredPosts = $stmt->fetchAll();

$pageTitle    = "Fake {$countryName} Address Generator — Generate Random {$countryName} Addresses";
$metaDesc     = "Generate realistic fake {$countryName} addresses for software testing, privacy protection, and data masking. Includes names, phone numbers, and postal codes.";
$metaKeywords = "fake {$countryName} address, random {$countryName} address generator, dummy address {$countryName}";

require_once __DIR__ . '/templates/header.php';
?>

<section class="hero">
  <h1>Fake <?= e($countryName) ?> Address Generator</h1>
  <p>Generate realistic fake <?= e($countryName) ?> addresses including names, phone numbers, and ZIP/postal codes.</p>
</section>

<div class="main-layout">
  <div class="primary-content">
    <?php require __DIR__ . '/templates/generator_widget.php'; ?>

    <section class="content-section">
      <h2>About Fake <?= e($countryName) ?> Addresses</h2>
      <div class="text-grid">
        <article>
          <h3>Realistic Format</h3>
          <p>All generated addresses follow the authentic formatting conventions used in <?= e($countryName) ?>, including correct postal code structures, regional naming, and phone number patterns.</p>
        </article>
        <article>
          <h3>Instant Generation</h3>
          <p>Generate as many addresses as you need in seconds. Each generation creates a completely new, unique random address with no repeats.</p>
        </article>
        <article>
          <h3>Privacy Safe</h3>
          <p>No real personal data is used or stored. All names, street addresses, and contact details are algorithmically generated and fictional.</p>
        </article>
        <article>
          <h3>Developer Ready</h3>
          <p>Perfect for populating test databases, filling form validation flows, or seeding staging environments with realistic-looking data.</p>
        </article>
      </div>
    </section>
  </div>

  <?php require __DIR__ . '/templates/sidebar.php'; ?>
</div>

<?php require __DIR__ . '/templates/footer.php'; ?>
