<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin — Fake Address Generator</title>
  <meta name="robots" content="noindex, nofollow" />
  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <!-- Quill Rich Text Editor -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.snow.min.css" rel="stylesheet" />
  <base href="<?php echo BASE_URL ?>">
  <style>
    :root {
      --brand:       #5d83f1;
      --brand-dark:  #4a6edb;
      --green:       #8cc63f;
      --sidebar-bg:  #0f172a;
      --sidebar-w:   240px;
      --header-h:    60px;
      --text:        #1e293b;
      --text-muted:  #64748b;
      --border:      #e2e8f0;
      --bg:          #f8fafc;
      --card:        #ffffff;
      --danger:      #ef4444;
      --success:     #22c55e;
      --warning:     #f59e0b;
      --radius:      8px;
      --shadow:      0 1px 3px rgba(0,0,0,.1), 0 1px 2px rgba(0,0,0,.06);
      --shadow-md:   0 4px 6px rgba(0,0,0,.07), 0 2px 4px rgba(0,0,0,.06);
    }
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body, #root { height: 100%; }
    body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); font-size: 14px; line-height: 1.6; }
    #root { display: flex; flex-direction: column; }
    /* Global admin utility classes */
    .hidden { display: none !important; }
    .spin { animation: spin .7s linear infinite; }
    @keyframes spin { to { transform: rotate(360deg); } }
  </style>
</head>
<body>
  <div id="root"></div>

  <!-- Dependencies (CDN — production should bundle) -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/react/18.2.0/umd/react.production.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/react-dom/18.2.0/umd/react-dom.production.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/babel-standalone/7.23.2/babel.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/quill/1.3.7/quill.min.js"></script>

  <!-- Admin App -->
  <script type="text/babel" src="./app.jsx"></script>
</body>
</html>
