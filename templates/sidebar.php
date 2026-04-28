<?php
// templates/sidebar.php — Shared sidebar partial
// Variables expected: $defaultCountry, $featuredPosts (array)

$defaultCountry ??= 'us';
$countries = AddressGenerator::countries();
$states    = AddressGenerator::states($defaultCountry);

// Fetch featured posts if not passed
if (!isset($featuredPosts)) {
    $stmt = db()->prepare(
        'SELECT p.id, p.title, p.slug, p.thumbnail, p.excerpt
         FROM posts p
         WHERE p.is_featured = 1 AND p.status = "published"
         ORDER BY p.published_at DESC LIMIT 5'
    );
    $stmt->execute();
    $featuredPosts = $stmt->fetchAll();
}
?>

<aside class="sidebar">

  <!-- Custom Generation Options -->
  <div class="card">
    <div class="card-header"><h3>Custom Generation</h3></div>
    <div class="card-body">
      <div class="form-group">
        <label for="sb-country">Country</label>
        <select id="sb-country" onchange="syncGeneratorCountry(this.value)">
          <?php foreach ($countries as $c): ?>
            <option value="<?= e($c['code']) ?>" <?= ($c['code'] === $defaultCountry) ? 'selected' : '' ?>><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label for="sb-gender">Gender</label>
        <select id="sb-gender" onchange="syncGeneratorGender(this.value)">
          <option value="random">Random</option>
          <option value="male">Male</option>
          <option value="female">Female</option>
        </select>
      </div>
      <div class="form-group">
        <label for="sb-state">State / Region</label>
        <select id="sb-state" onchange="syncGeneratorState(this.value)">
          <option value="">Any</option>
          <?php foreach ($states as $s): ?>
            <option value="<?= e($s) ?>"><?= e($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button class="btn btn-green" style="width:100%" onclick="document.getElementById('addr-form').dispatchEvent(new Event('submit'))">
        Apply &amp; Generate
      </button>
    </div>
  </div>

  <!-- Featured Posts -->
  <?php if (!empty($featuredPosts)): ?>
  <div class="card">
    <div class="card-header"><h3>Featured Guides</h3></div>
    <ul class="post-list-sidebar">
      <?php foreach ($featuredPosts as $post): ?>
        <li>
          <a href="<?= BASE_URL ?>/blog/<?= e($post['slug']) ?>">
            <?php if ($post['thumbnail']): ?>
              <img src="<?= BASE_URL . '/' . e($post['thumbnail']) ?>" alt="<?= e($post['title']) ?>" width="60" height="60">
            <?php endif; ?>
            <span><?= e($post['title']) ?></span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <!-- Tag Cloud -->
  <div class="card">
    <div class="card-header"><h3>Related Topics</h3></div>
    <div class="tag-cloud">
      <a href="<?= BASE_URL ?>/us-fake-address">US Address</a>
      <a href="<?= BASE_URL ?>/uk-fake-address">UK Postal Codes</a>
      <a href="<?= BASE_URL ?>/ca-fake-address">Canada Addresses</a>
      <a href="<?= BASE_URL ?>/au-fake-address">Australia Address</a>
      <a href="<?= BASE_URL ?>/de-fake-address">Germany Address</a>
      <a href="<?= BASE_URL ?>/jp-fake-address">Japan Address</a>
      <a href="<?= BASE_URL ?>/blog">Developer Guides</a>
      <a href="<?= BASE_URL ?>/blog">Privacy Tools</a>
      <a href="<?= BASE_URL ?>/blog">API Testing</a>
      <a href="<?= BASE_URL ?>/blog">Data Masking</a>
    </div>
  </div>

</aside>

<script>
function syncGeneratorCountry(val) {
  const f = document.getElementById('addr-form');
  if (f) f.querySelector('[name="country"]').value = val;
  // Reload states
  fetch('/api/states.php?country=' + encodeURIComponent(val))
    .then(r => r.json())
    .then(j => {
      const sel = document.getElementById('sb-state');
      sel.innerHTML = '<option value="">Any</option>';
      (j.states || []).forEach(s => {
        sel.insertAdjacentHTML('beforeend', `<option value="${s}">${s}</option>`);
      });
    });
}
function syncGeneratorGender(val) {
  const f = document.getElementById('addr-form');
  if (f) f.querySelector('[name="gender"]').value = val;
}
function syncGeneratorState(val) {
  const f = document.getElementById('addr-form');
  if (f) f.querySelector('[name="state"]').value = val;
}
</script>
