<?php
// config/app.php

define('APP_NAME', 'Fake Address Generator');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost/fake-address-gen/');
define('UPLOAD_DIR', __DIR__ . '/../uploads/thumbnails/');
// define('UPLOAD_URL', APP_URL . '/uploads/thumbnails/');
// define('MAX_FEATURED_POSTS', 5);
define('POSTS_PER_PAGE', 10);
define('BLOG_PREVIEW_COUNT', 5);
define('ADMIN_SESSION_NAME', 'fag_admin_session');

// Security
define('CSRF_TOKEN_NAME', 'csrf_token');

// IP Geolocation API (free tier)
define('GEO_API_URL', 'http://ip-api.com/json/');