<?php
// templates/header.php — Site header partial
// Variables expected: $pageTitle (string), $metaDesc (string), $metaKeywords (string)

$siteName    = get_setting('site_name', 'Fake Address Generator');
$headerScripts = get_setting('header_scripts', '');
$gaId        = get_setting('ga_tracking_id', '');

$pageTitle    ??= $siteName;
$metaDesc     ??= get_setting('site_tagline', 'Generate realistic fake addresses for testing.');
$metaKeywords ??= 'fake address generator, random address, dummy address';

$countries = AddressGenerator::countries();
$currentPath = current_path();
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($pageTitle) ?><?= ($pageTitle !== $siteName) ? ' — ' . e($siteName) : '' ?></title>
  <meta name="description" content="<?= e($metaDesc) ?>">
  <meta name="keywords"    content="<?= e($metaKeywords) ?>">
  <meta name="robots"      content="index, follow">
  <link rel="canonical"    href="<?= e(BASE_URL . $currentPath) ?>">
  <!-- Open Graph -->
  <meta property="og:title"       content="<?= e($pageTitle) ?>">
  <meta property="og:description" content="<?= e($metaDesc) ?>">
  <meta property="og:type"        content="website">
  <meta property="og:url"         content="<?= e(BASE_URL . $currentPath) ?>">
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@300;400;500;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <!-- Styles -->
  <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/main.css?v=1.2.0">
  <?php if ($gaId): ?>
  <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($gaId) ?>"></script>
  <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= e($gaId) ?>');</script>
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
    <li><a href="<?= BASE_URL ?>/us-fake-address" <?= str_contains($currentPath,'us-fake') ? 'class="active"' : '' ?>>USA</a></li>
    <li><a href="<?= BASE_URL ?>/uk-fake-address" <?= str_contains($currentPath,'uk-fake') ? 'class="active"' : '' ?>>United Kingdom</a></li>
    <li><a href="<?= BASE_URL ?>/au-fake-address" <?= str_contains($currentPath,'au-fake') ? 'class="active"' : '' ?>>Australia</a></li>
    <li><a href="<?= BASE_URL ?>/ca-fake-address" <?= str_contains($currentPath,'ca-fake') ? 'class="active"' : '' ?>>Canada</a></li>
    <li><a href="<?= BASE_URL ?>/fake-address"    <?= $currentPath === '/fake-address' ? 'class="active"' : '' ?>>All Countries</a></li>
    <li><a href="<?= BASE_URL ?>/blog"             <?= str_starts_with($currentPath,'/blog') ? 'class="active"' : '' ?>>Blog</a></li>
  </ul>
</nav>

<div class="nav-flags">
  <div class="nav-flags-inner">
    <?php
    $flagLinks = [
      'us' => ['href'=>'/us-fake-address','label'=>'USA'],
      'uk' => ['href'=>'/uk-fake-address','label'=>'UK'],
      'ca' => ['href'=>'/ca-fake-address','label'=>'CA'],
      'au' => ['href'=>'/au-fake-address','label'=>'AU'],
      'de' => ['href'=>'/de-fake-address','label'=>'DE'],
      'jp' => ['href'=>'/jp-fake-address','label'=>'JP'],
    ];
    foreach ($flagLinks as $code => $fl): ?>
      <a href="<?= BASE_URL . $fl['href'] ?>">
        <img src="<?= BASE_URL ?>/assets/images/flags/<?= $code ?>.svg" alt="<?= $fl['label'] ?>" width="22" height="14" onerror="this.style.display='none'">
        <?= $fl['label'] ?>
      </a>
    <?php endforeach; ?>
  </div>
</div>

<div class="nav-generator-bar">
  <form id="header-gen-form" method="get" action="./">
    <select name="country">
      <?php foreach ($countries as $c): ?>
        <option value="<?= e($c['code']) ?>"><?= e($c['name']) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary">Generate Address</button>
  </form>
</div>
