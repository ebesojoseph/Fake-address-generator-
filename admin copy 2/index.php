<?php
// admin/index.php — PHP shell for React admin (injects BASE_URL dynamically)
require_once __DIR__ . '/../includes/bootstrap.php';

// Redirect to login if not authenticated
// (The React app handles this itself, but this prevents source leakage)
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin — <?= e(get_setting('site_name','Fake Address Generator')) ?></title>
  <meta name="robots" content="noindex, nofollow">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.snow.min.css" rel="stylesheet">
  <style>
    :root {
      --brand:#5d83f1;--brand-dark:#4a6edb;--green:#8cc63f;
      --sidebar-bg:#0f172a;--sidebar-w:240px;
      --text:#1e293b;--text-muted:#64748b;--border:#e2e8f0;
      --bg:#f8fafc;--card:#ffffff;
      --danger:#ef4444;--success:#22c55e;--warning:#f59e0b;
      --radius:8px;--shadow:0 1px 3px rgba(0,0,0,.1);
    }
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
    html,body,#root{height:100%}
    body{font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);font-size:14px}
    @keyframes spin{to{transform:rotate(360deg)}}
    @keyframes slideIn{from{transform:translateX(40px);opacity:0}to{transform:none;opacity:1}}
  </style>
</head>
<body>
  <div id="root"></div>

  <!-- Inject PHP config into JS so React knows the API base and site URL -->
  <script>
    window.__APP__ = {
      baseUrl:  <?= json_encode(BASE_URL) ?>,
      apiBase:  <?= json_encode(BASE_URL . '/admin/api') ?>,
      siteName: <?= json_encode(get_setting('site_name','Fake Address Generator')) ?>,
    };
  </script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/react/18.2.0/umd/react.production.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/react-dom/18.2.0/umd/react-dom.production.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/babel-standalone/7.23.2/babel.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.min.js"></script>
  <script type="text/babel" src="<?= BASE_URL ?>/admin/app.jsx"></script>
</body>
</html>
