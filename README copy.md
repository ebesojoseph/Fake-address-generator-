# Fake Address Generator v2 — FakerPHP Edition

## Quick Start

```bash
# 1. Install dependencies (run locally, then upload vendor/ to cPanel)
composer install --no-dev --optimize-autoloader

# 2. Import database
mysql -u root -p fakeaddrgen < schema.sql

# 3. Configure
# Edit config/config.php  OR  set environment variables on cPanel:
#   APP_BASE_URL = https://yourdomain.com/your-subfolder
#   DB_HOST, DB_NAME, DB_USER, DB_PASS
```

## cPanel Deployment

1. Run `composer install` locally
2. Upload ALL files including the `vendor/` folder to your cPanel subdirectory
3. In cPanel → MySQL, create database and import `schema.sql`
4. Set `APP_BASE_URL` in cPanel → Environment Variables, or edit `config/config.php`
5. Visit `yourdomain.com/your-folder/admin/` and log in with `admin` / `Admin@1234`
6. **Change your password immediately** in Settings → Change Password

## Key Files Changed from v1

| File | What changed |
|---|---|
| `includes/AddressGenerator.php` | Now uses FakerPHP — all fields dynamic per locale |
| `includes/LocaleRegistry.php` | 300+ ICU locales mapped to Faker locales |
| `models/Post.php` | New — Post model with static methods |
| `models/Faq.php` | New — FAQ model |
| `locale.php` | New — `/fake-address/united-states` style pages |
| `locales.php` | New — browse all 300+ locales |
| `admin/index.php` | Now PHP — injects BASE_URL into React app |
| `admin/app.jsx` | FAQs view added, uses `window.__APP__.apiBase` |

## URL Structure

| URL | Page |
|---|---|
| `/` | Homepage with generator |
| `/fake-address` | Browse all 300+ locales |
| `/fake-address/english-united-states` | US address generator |
| `/fake-address/german-germany` | German address generator |
| `/fake-address/japanese-japan` | Japanese address generator |
| `/blog` | Blog archive |
| `/blog/{slug}` | Single post |
| `/sitemap.xml` | Dynamic sitemap (includes all locale pages) |
| `/admin/` | React admin dashboard |

## Default Admin Credentials
- **Username:** admin
- **Password:** Admin@1234 ← change this immediately

## Composer Note
FakerPHP requires the `vendor/` directory. If your cPanel host has SSH:
```bash
cd /path/to/your/app
composer install --no-dev --optimize-autoloader
```
Otherwise, run `composer install` locally and upload the `vendor/` folder.
