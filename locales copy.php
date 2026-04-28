<?php
// locales.php — /fake-address — browse all locales
require_once __DIR__ . '/includes/bootstrap.php';

use App\LocaleRegistry;

$search  = trim($_GET['q'] ?? '');
$locales = $search ? LocaleRegistry::search($search) : LocaleRegistry::all();
$grouped = [];
foreach ($locales as $code => $info) {
    $grouped[strtoupper($info[1][0])][$code] = $info;
}
ksort($grouped);

$pageTitle = 'All Country Address Generators — ' . get_setting('site_name','Fake Address Generator');
$metaDesc  = 'Browse fake address generators for every country and locale worldwide. 300+ locales supported.';

require __DIR__ . '/templates/header.php';
?>

<section class="hero">
  <h1>All Country Address Generators</h1>
  <p>Browse 300+ locales. Click any country to generate a realistic fake address.</p>
</section>

<div style="max-width:1200px;margin:28px auto;padding:0 20px;">

  <!-- Search -->
  <div style="margin-bottom:28px;display:flex;gap:10px;align-items:center;">
    <form method="get" action="<?= BASE_URL ?>/fake-address" style="display:flex;gap:10px;flex:1;max-width:480px;">
      <input name="q" value="<?= e($search) ?>" placeholder="Search country or language…"
        style="flex:1;padding:10px 14px;border:1px solid var(--border);border-radius:6px;font-size:14px;">
      <button type="submit" class="btn btn-primary">Search</button>
      <?php if ($search): ?>
        <a href="<?= BASE_URL ?>/fake-address" class="btn btn-outline">Clear</a>
      <?php endif; ?>
    </form>
    <span style="color:var(--text-muted);font-size:13px;"><?= count($locales) ?> locales</span>
  </div>

  <?php if (empty($locales)): ?>
    <div class="alert alert-info">No locales found for "<?= e($search) ?>".</div>
  <?php endif; ?>

  <?php foreach ($grouped as $letter => $items): ?>
    <div style="margin-bottom:32px;">
      <h2 style="font-size:1.1rem;font-weight:700;color:var(--blue);border-bottom:2px solid var(--green-light);padding-bottom:8px;margin-bottom:14px;"><?= $letter ?></h2>
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:10px;">
        <?php foreach ($items as $code => $info): ?>
          <?php $slug = LocaleRegistry::toSlug($code); ?>
          <a href="<?= BASE_URL ?>/fake-address/<?= e($slug) ?>"
            style="display:flex;align-items:center;gap:10px;padding:10px 14px;border:1px solid var(--border);border-radius:6px;background:#fff;color:var(--text);transition:all .15s;text-decoration:none;"
            onmouseover="this.style.borderColor='var(--blue)';this.style.background='#f0f4ff'"
            onmouseout="this.style.borderColor='var(--border)';this.style.background='#fff'">
            <span style="font-size:1.4rem;line-height:1;"><?= $info[4] ?></span>
            <div>
              <div style="font-weight:500;font-size:.875rem;"><?= e($info[1]) ?></div>
              <div style="font-size:.75rem;color:var(--text-muted);"><?= e($code) ?></div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<?php require __DIR__ . '/templates/footer.php'; ?>
