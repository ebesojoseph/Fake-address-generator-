<?php
// templates/header.php

use App\LocaleRegistry;

$siteName      = get_setting('site_name', 'Fake Address Generator');
$headerScripts = get_setting('header_scripts', '');
$gaId          = get_setting('ga_tracking_id', '');
$pageTitle    ??= $siteName;
$metaDesc     ??= get_setting('site_tagline', 'Generate realistic fake addresses for any country.');
$metaKeywords ??= 'fake address generator, random address, test data';
$currentPath   = current_path();
$allLocales    = LocaleRegistry::all();

// The 7 locales shown as inline flags
$flagLocales = [
  'en_US' => '🇺🇸',
  'en_GB' => '🇬🇧',
  'en_CA' => '🇨🇦',
  'de_DE' => '🇩🇪',
  'fr_FR' => '🇫🇷',
  'ja_JP' => '🇯🇵',
  'zh_CN' => '🇨🇳',
];

// Popular locales used in the Quick Generate select
$popularLocales = [
  'en_US',
  'en_GB',
  'en_CA',
  'en_AU',
  'de_DE',
  'fr_FR',
  'es_ES',
  'es_MX',
  'it_IT',
  'pt_BR',
  'nl_NL',
  'ja_JP',
  'zh_CN',
  'ko_KR',
  'ru_RU',
  'ar_SA',
  'hi_IN',
  'id_ID',
  'pl_PL',
  'tr_TR',
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?><?= $pageTitle !== $siteName ? ' — ' . e($siteName) : '' ?></title>
  <meta name="description" content="<?= e($metaDesc) ?>">
  <meta name="keywords" content="<?= e($metaKeywords) ?>">
  <meta name="robots" content="index, follow">
  <link rel="canonical" href="<?= e(BASE_URL . $currentPath) ?>">
  <meta property="og:title" content="<?= e($pageTitle) ?>">
  <meta property="og:description" content="<?= e($metaDesc) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css?v=2.1">
  <?php if ($gaId): ?>
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($gaId) ?>"></script>
    <script>
      window.dataLayer = window.dataLayer || [];

      function gtag() {
        dataLayer.push(arguments);
      }
      gtag('js', new Date());
      gtag('config', '<?= e($gaId) ?>');
    </script>
  <?php endif; ?>
  <?= $headerScripts ?>
</head>

<body>

  <header class="site-header">
    <div class="header-inner">
      <a href="<?= BASE_URL ?>/" class="nav-logo">Fake <span>Address Generator</span></a>
    </div>
  </header>

  <nav class="nav-primary">
    <ul>
      <li><a href="<?= BASE_URL ?>/" <?= $currentPath === '/' ? 'class="active"' : '' ?>>Home</a></li>
      <li><a href="<?= BASE_URL ?>/fake-address" <?= str_starts_with($currentPath, '/fake-address') ? 'class="active"' : '' ?>>All Countries</a></li>
      <li><a href="<?= BASE_URL ?>/blog" <?= str_starts_with($currentPath, '/blog') ? 'class="active"' : '' ?>>Blog</a></li>
    </ul>
  </nav>

  <!-- Quick generate bar with inline flag links -->
  <div class="nav-generator-bar">
    <div class="nav-generator-inner">
      <!-- Flag strip -->
      <div class="flag-strip">
        <?php foreach ($flagLocales as $lc => $flag):
          $info = LocaleRegistry::get($lc);
          if (!$info) continue;
          $slug = LocaleRegistry::toSlug($lc);
          $isActive = ($currentPath === "/fake-address/{$slug}") ? ' flag-active' : '';
        ?>
          <a href="<?= BASE_URL ?>/fake-address/<?= e($slug) ?>" class="flag-link<?= $isActive ?>" title="<?= e($info[1]) ?>">
            <span class="flag-emoji"><?= $flag ?></span>
          </a>
        <?php endforeach; ?>
      </div>

      <!-- Quick generate form -->
      <form id="header-gen-form" method="get"
        action="<?= str_starts_with($currentPath, '/fake-address/')
                  ? BASE_URL . $currentPath
                  : BASE_URL . '/' ?>">
        <label style="font-size:13px;font-weight:600;color:#555">Quick Generate:</label>
        <select name="locale" id="header-locale-select">
          <?php foreach ($popularLocales as $lc):
            $info = LocaleRegistry::get($lc);
            if (!$info) continue;
            $sel = (($_GET['locale'] ?? 'en_US') === $lc) ? 'selected' : '';
          ?>
            <option value="<?= e($lc) ?>" <?= $sel ?>><?= $info[4] ?> <?= e($info[1]) ?></option>
          <?php endforeach; ?>
        </select>
        <select name="gender">
          <option value="random">Any Gender</option>
          <option value="male">Male</option>
          <option value="female">Female</option>
        </select>
        <button type="submit" class="btn btn-primary">Generate</button>
      </form>
    </div>
  </div>