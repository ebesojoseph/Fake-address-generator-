<?php
// templates/sidebar.php

use App\LocaleRegistry;
use App\Models\Post;

$featuredPosts ??= Post::featured(5);
$currentLocale = $_GET['locale'] ?? 'en_US';
?>
<aside class="sidebar">

  <!-- Custom Generation -->
  <div class="card">
    <div class="card-header">
      <h3>Custom Generation</h3>
    </div>
    <div class="card-body">
      <div class="form-group">
        <label>Locale / Country</label>
        <input type="text" id="sb-locale-search" placeholder="Search locale…"
          style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:6px;font-size:13px;margin-bottom:6px;">
        <select id="sb-locale" style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:6px;font-size:13px;">
          <?php foreach (LocaleRegistry::all() as $code => $info): ?>
            <option value="<?= e($code) ?>" <?= $code === $currentLocale ? 'selected' : '' ?>>
              <?= $info[4] ?> <?= e($info[1]) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Gender</label>
        <select id="sb-gender" style="width:100%;padding:8px 12px;border:1px solid var(--border);border-radius:6px;font-size:13px;">
          <option value="random">Random</option>
          <option value="male">Male</option>
          <option value="female">Female</option>
        </select>
      </div>
      <button class="btn btn-green" style="width:100%" id="sb-generate-btn" type="submit">Apply &amp; Generate</button>
    </div>
  </div>

  <!-- Featured Posts -->
  <?php if (!empty($featuredPosts)): ?>
    <div class="card">
      <div class="card-header">
        <h3>Featured Guides</h3>
      </div>
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

  <!-- Tags -->
  <div class="card">
    <div class="card-header">
      <h3>Popular Locales</h3>
    </div>
    <div class="tag-cloud">
      <?php
      $popular = ['en_US', 'en_GB', 'de_DE', 'fr_FR', 'es_ES', 'pt_BR', 'ja_JP', 'zh_CN', 'ko_KR', 'ru_RU', 'ar_SA', 'it_IT'];
      foreach ($popular as $lc):
        $info = LocaleRegistry::get($lc);
        if (!$info) continue;
        $slug = LocaleRegistry::toSlug($lc);
      ?>
        <a href="<?= BASE_URL ?>/fake-address/<?= e($slug) ?>"><?= $info[4] ?> <?= e($info[1]) ?></a>
      <?php endforeach; ?>
      <a href="<?= BASE_URL ?>/fake-address">🌍 All locales →</a>
    </div>
  </div>

</aside>

<script>
  // Sidebar locale search filter
  document.getElementById('sb-locale-search').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    const sel = document.getElementById('sb-locale');
    Array.from(sel.options).forEach(opt => {
      opt.style.display = (!q || opt.text.toLowerCase().includes(q)) ? '' : 'none';
    });
  });

  document.getElementById('sb-generate-btn').addEventListener('click', function() {
    const locale = document.getElementById('sb-locale').value;
    const gender = document.getElementById('sb-gender').value;
    const f = document.getElementById('addr-form');
    if (f) {
      f.querySelector('[name="locale"]').value = locale;
      f.querySelector('[name="gender"]').value = gender;
    }
    // Trigger the actual generate button, not a form submit
    const genBtn = document.getElementById('btn-generate');
    if (genBtn) genBtn.click();
  });

  // Nav dropdown locale search
  // const locSearch = document.getElementById('locale-search');
  // if (locSearch) {
  //   locSearch.addEventListener('input', function() {
  //     const q = this.value.toLowerCase();
  //     document.querySelectorAll('.locale-item:not(.locale-view-all)').forEach(el => {
  //       el.style.display = (el.dataset.name || '').includes(q) ? '' : 'none';
  //     });
  //   });
  // }
</script>