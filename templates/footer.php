<?php
// templates/footer.php — Site footer partial

$footerScripts = get_setting('footer_scripts', '');
$contactEmail  = get_setting('contact_email', 'info@example.com');
$siteName      = get_setting('site_name', 'Fake Address Generator');

// Fetch footer links grouped by row_number
$stmt = get_db()->query('SELECT * FROM footer_links WHERE is_active = 1 ORDER BY `row_number` ASC, sort_order ASC');
$links  = $stmt->fetchAll();
$groups = [];
foreach ($links as $lnk) {
    $groups[(int)$lnk['row_number']][] = $lnk;
}
ksort($groups);
?>

<footer class="site-footer">
  <div class="footer-container">
    <div class="footer-funnel">
      <h4>Explore More Tools</h4>

      <?php foreach ($groups as $rowNum => $rowLinks): ?>
        <div class="funnel-row" style="--items:<?= count($rowLinks) ?>">
          <?php foreach ($rowLinks as $lnk): ?>
            <a href="<?= e($lnk['url']) ?>"><?= e($lnk['label']) ?></a>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>

    <hr class="footer-divider">

    <div class="footer-bottom">
      <p>&copy; <?= date('Y') ?> <?= e($siteName) ?>. All rights reserved. For testing &amp; educational use only.</p>
      <ul class="footer-legal">
        <li><a href="<?= BASE_URL ?>/privacy">Privacy Policy</a></li>
        <li><a href="<?= BASE_URL ?>/terms">Terms of Use</a></li>
        <li><a href="mailto:<?= e($contactEmail) ?>">Contact</a></li>
      </ul>
    </div>
  </div>
</footer>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<?= $footerScripts ?>
</body>
</html>
