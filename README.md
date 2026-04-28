# Fake Address Generator — Full-Stack PHP + MySQL Application

A production-ready web application for generating realistic fake addresses, with a full CMS admin dashboard built in React.

---

## Table of Contents

1. [Server Requirements](#server-requirements)
2. [Installation Steps](#installation-steps)
3. [Database Setup](#database-setup)
4. [Configuration](#configuration)
5. [Project Structure](#project-structure)
6. [Admin Dashboard](#admin-dashboard)
7. [Features Overview](#features-overview)
8. [Routing & URLs](#routing--urls)
9. [Security Notes](#security-notes)
10. [Customization](#customization)

---

## Server Requirements

| Requirement | Minimum Version | Notes |
|---|---|---|
| PHP | 8.1+ | PDO, JSON, FileInfo extensions required |
| MySQL | 5.7+ / MariaDB 10.3+ | utf8mb4 charset support |
| Apache | 2.4+ | mod_rewrite enabled |
| PHP Extensions | pdo_mysql, fileinfo, json, session | Standard on most hosts |

**Disk space:** ~10 MB base + uploaded images  
**Recommended:** PHP 8.2+, MySQL 8.0+, HTTPS enabled

---

## Installation Steps

### 1. Clone / Upload Files

```bash
# Clone or extract the project into your web root
cp -r fakeaddrgen/ /var/www/html/fakeaddrgen/
# or for root domain:
cp -r fakeaddrgen/* /var/www/html/
```

### 2. Set File Permissions

```bash
# Make uploads directory writable
chmod -R 755 /var/www/html/fakeaddrgen/
chmod -R 777 /var/www/html/fakeaddrgen/uploads/
# Or with proper ownership:
chown -R www-data:www-data /var/www/html/fakeaddrgen/uploads/
```

### 3. Enable Apache mod_rewrite

```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

Your Apache VirtualHost must have `AllowOverride All` set:

```apache
<Directory /var/www/html/fakeaddrgen>
    AllowOverride All
    Require all granted
</Directory>
```

### 4. Set Up the Database

```bash
# Create database and import schema
mysql -u root -p -e "CREATE DATABASE fakeaddrgen CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p fakeaddrgen < schema.sql
```

### 5. Configure Environment

Edit `config/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'fakeaddrgen');
define('DB_USER', 'your_db_user');
define('DB_PASS', 'your_db_password');
define('BASE_URL', 'https://yourdomain.com');  // No trailing slash
```

Or set environment variables (recommended for production):

```bash
export DB_HOST=localhost
export DB_NAME=fakeaddrgen
export DB_USER=dbuser
export DB_PASS=securepassword
export BASE_URL=https://yourdomain.com
```

### 6. Access the Application

- **Frontend:** `https://yourdomain.com/`
- **Admin:**    `https://yourdomain.com/admin/`
- **Default credentials:** `admin` / `Admin@1234`

> ⚠️ **Change the default admin password immediately after first login** via Settings → Change Password.

---

## Database Setup

The `schema.sql` file creates all required tables and seeds default data:

| Table | Purpose |
|---|---|
| `admin_users` | Admin authentication and roles |
| `categories` | Blog post categories |
| `posts` | Blog posts with SEO fields |
| `content_sections` | Dynamic homepage content blocks |
| `generation_logs` | Address generation tracking & analytics |
| `settings` | Key/value site configuration store |
| `footer_links` | Funnel-style footer links |

### Reset Admin Password

```sql
UPDATE admin_users
SET password_hash = '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE username = 'admin';
-- Password is: password (change immediately!)
```

To generate a new hash:

```php
echo password_hash('YourNewPassword', PASSWORD_BCRYPT, ['cost' => 12]);
```

---

## Configuration

### config/config.php — All Options

```php
// Database
define('DB_HOST',    'localhost');
define('DB_NAME',    'fakeaddrgen');
define('DB_USER',    'root');
define('DB_PASS',    '');

// Site URL (no trailing slash)
define('BASE_URL', 'http://localhost/fakeaddrgen');

// Paths
define('ROOT_PATH',   dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads');

// Session
define('SESSION_SECURE',   true);   // Set true on HTTPS
define('SESSION_HTTPONLY',  true);

// Business rules
define('MAX_FEATURED_POSTS', 5);    // Max featured posts on homepage sidebar

// Upload limits
define('MAX_UPLOAD_SIZE',   5 * 1024 * 1024);  // 5 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);
```

---

## Project Structure

```
fakeaddrgen/
│
├── index.php               # Homepage
├── blog.php                # Blog archive (/blog)
├── post.php                # Single post (/blog/{slug})
├── country.php             # Country-specific pages (/us-fake-address, etc.)
├── sitemap.php             # Dynamic XML sitemap (/sitemap.xml)
├── .htaccess               # Apache routing rules
├── schema.sql              # Database schema + seed data
│
├── config/
│   ├── config.php          # Application configuration constants
│   └── database.php        # PDO singleton connection
│
├── includes/
│   ├── bootstrap.php       # App initialization (session, autoload)
│   ├── helpers.php         # Utility functions (e, slugify, etc.)
│   └── address_generator.php  # Core address generation engine
│
├── templates/
│   ├── header.php          # Site header with navigation
│   ├── footer.php          # Site footer with funnel links
│   ├── generator_widget.php   # Address display card
│   └── sidebar.php         # Sidebar with custom gen + featured posts
│
├── api/
│   ├── generate.php        # GET — generate address (AJAX)
│   └── states.php          # GET — get states for a country (AJAX)
│
├── admin/
│   ├── index.html          # React admin SPA shell
│   ├── app.jsx             # Full React admin application
│   ├── .htaccess           # Admin-specific security rules
│   └── api/
│       ├── auth.php        # Login, logout, me, change password
│       ├── posts.php       # Blog post CRUD
│       └── data.php        # Categories, settings, content, analytics, footer links
│
├── assets/
│   ├── css/
│   │   └── main.css        # Main stylesheet
│   ├── js/
│   │   └── main.js         # Frontend JavaScript
│   └── images/
│       └── flags/          # Country flag SVGs (us.svg, uk.svg, etc.)
│
└── uploads/
    └── thumbnails/         # Blog post thumbnail uploads (auto-created)
```

---

## Admin Dashboard

Access at `/admin/`. The React-powered CMS includes:

### Blog Management
- Create, edit, delete posts
- Quill.js rich text editor
- Automatic slug generation (editable)
- Thumbnail upload (required to publish)
- Featured post toggle (max 5)
- Draft / Published status
- View count tracking

### SEO Fields Per Post
- Meta title
- Meta description
- Keywords
- Live SERP preview

### Content Sections
- Manage homepage dynamic content blocks
- Enable/disable sections
- Drag-and-drop style ordering via sort_order field

### Footer Links
- Manage funnel-layout footer links
- Assign links to rows (row 1 = most links, higher rows = fewer links)
- Toggle active/inactive

### Analytics
- Total and today's address generations
- Generations by country (with bar chart)
- Top blog posts by views
- Published post count

### Global Settings
- Site name & tagline
- Contact email
- Google Analytics tracking ID
- Custom header/footer HTML scripts

---

## Routing & URLs

| URL Pattern | Handler | Notes |
|---|---|---|
| `/` | `index.php` | Homepage with generator |
| `/blog` | `blog.php` | Blog archive with pagination |
| `/blog/{slug}` | `post.php` | Single post |
| `/us-fake-address` | `country.php` | US address generator |
| `/uk-fake-address` | `country.php` | UK address generator |
| `/au-fake-address` | `country.php` | Australia address generator |
| `/ca-fake-address` | `country.php` | Canada address generator |
| `/de-fake-address` | `country.php` | Germany address generator |
| `/jp-fake-address` | `country.php` | Japan address generator |
| `/fake-address` | `country.php` | All countries |
| `/sitemap.xml` | `sitemap.php` | Dynamic XML sitemap |
| `/api/generate.php` | API | AJAX address generation |
| `/api/states.php` | API | AJAX state list |
| `/admin/` | React SPA | Admin dashboard |
| `/admin/api/*.php` | PHP API | Admin REST endpoints |

---

## Security Notes

1. **Change the default admin password** immediately after installation
2. **Set `SESSION_SECURE = true`** in config when running HTTPS
3. **Database credentials** should be set as environment variables in production, not hard-coded
4. All database queries use **PDO prepared statements**
5. **File uploads** validate MIME type via `finfo`, not just extension
6. **CSRF protection** is implemented on all admin form submissions
7. Admin API endpoints **require an active session** — no public access
8. HTML content from Quill is **strip_tags-sanitized** on save
9. Consider adding **rate limiting** to `/api/generate.php` in production (via nginx/Apache or a PHP rate-limiter library)
10. For production, **bundle the React admin** with Vite/Webpack instead of using Babel standalone

---

## Customization

### Add a New Country

In `includes/address_generator.php`, add a new entry to the `$data` array:

```php
'fr' => [
    'country'  => 'France',
    'code'     => 'FR',
    'phone'    => '+33',
    'streets'  => ['Rue de Rivoli', 'Avenue des Champs-Élysées', ...],
    'cities'   => [
        ['city'=>'Paris', 'state'=>'Île-de-France', 'zip_prefix'=>'75'],
        ...
    ],
    'zip_fn'   => fn($prefix) => $prefix . str_pad(rand(0,999), 3, '0', STR_PAD_LEFT),
    'phone_fn' => fn() => sprintf('+33 %d %02d %02d %02d %02d', rand(1,9), rand(0,99), rand(0,99), rand(0,99), rand(0,99)),
],
```

Then add a nav link in `templates/header.php` and a rewrite rule in `.htaccess`.

### Add FAQ Items

Edit the FAQ section in `index.php` — each accordion item follows this pattern:

```html
<div class="accordion-item">
  <input type="checkbox" id="faqN" class="accordion-input">
  <label for="faqN" class="accordion-label">Your question here?</label>
  <div class="accordion-content">
    <p>Your answer here.</p>
  </div>
</div>
```

### Flag Images

Place SVG flag files in `assets/images/flags/` named as the two-letter country code (e.g., `us.svg`, `uk.svg`). Free flag SVGs are available at [flagcdn.com](https://flagcdn.com) or [country-flags GitHub repo](https://github.com/lipis/flag-icons).

---

## License

This project is provided for educational and commercial use. Do not use generated addresses for illegal purposes, fraud, or to deceive others.

---

*Built with native PHP 8.1+, MySQL, and React 18.*
