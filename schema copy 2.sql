-- ============================================================
-- Fake Address Generator v2 — Full Schema
-- Run: mysql -u root -p fakeaddrgen < schema.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS fakeaddrgen CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fakeaddrgen;

-- ── Admin Users ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS admin_users (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(100)  NOT NULL UNIQUE,
    email         VARCHAR(255)  NOT NULL UNIQUE,
    password_hash VARCHAR(255)  NOT NULL,
    role          ENUM('superadmin','editor') DEFAULT 'editor',
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login    TIMESTAMP NULL
);

-- ── Categories ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categories (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100) NOT NULL,
    slug        VARCHAR(120) NOT NULL UNIQUE,
    description TEXT,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── Posts ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS posts (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    title            VARCHAR(255)              NOT NULL,
    slug             VARCHAR(300)              NOT NULL UNIQUE,
    excerpt          TEXT,
    content          LONGTEXT                  NOT NULL,
    thumbnail        VARCHAR(500)              NOT NULL DEFAULT '',
    category_id      INT                       DEFAULT NULL,
    author_id        INT                       DEFAULT NULL,
    status           ENUM('draft','published') NOT NULL DEFAULT 'draft',
    is_featured      TINYINT(1)               NOT NULL DEFAULT 0,
    views            INT                       NOT NULL DEFAULT 0,
    meta_title       VARCHAR(255)              DEFAULT NULL,
    meta_description TEXT                      DEFAULT NULL,
    meta_keywords    TEXT                      DEFAULT NULL,
    published_at     TIMESTAMP                 NULL DEFAULT NULL,
    created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (author_id)   REFERENCES admin_users(id) ON DELETE SET NULL
);

-- ── FAQs ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS faqs (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    question   VARCHAR(500) NOT NULL,
    answer     TEXT         NOT NULL,
    sort_order INT          NOT NULL DEFAULT 0,
    is_active  TINYINT(1)  NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ── Content Sections ─────────────────────────────────────
CREATE TABLE IF NOT EXISTS content_sections (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    section_key VARCHAR(100) NOT NULL UNIQUE,
    title       VARCHAR(255),
    body        LONGTEXT,
    is_active   TINYINT(1)  NOT NULL DEFAULT 1,
    sort_order  INT         NOT NULL DEFAULT 0,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ── Generation Logs ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS generation_logs (
    id               BIGINT AUTO_INCREMENT PRIMARY KEY,
    session_id       VARCHAR(128),
    ip_address       VARCHAR(45),
    country          VARCHAR(100),
    region           VARCHAR(100),
    city             VARCHAR(100),
    country_code     CHAR(2),
    generated_locale VARCHAR(20),
    generated_data   JSON,
    generated_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_session      (session_id),
    INDEX idx_ip           (ip_address),
    INDEX idx_generated_at (generated_at),
    INDEX idx_locale       (generated_locale)
);

-- ── Settings ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS settings (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    setting_key   VARCHAR(100) NOT NULL UNIQUE,
    setting_value LONGTEXT,
    setting_type  ENUM('text','html','json','boolean') DEFAULT 'text',
    label         VARCHAR(200),
    updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- ── Footer Links ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS footer_links (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    label      VARCHAR(200) NOT NULL,
    url        VARCHAR(500) NOT NULL,
    row_number INT          NOT NULL DEFAULT 1,
    sort_order INT          NOT NULL DEFAULT 0,
    is_active  TINYINT(1)  NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================
-- Seed Data
-- ============================================================

-- Default admin (password: Admin@1234 — change immediately)
INSERT IGNORE INTO admin_users (username, email, password_hash, role) VALUES
('admin','admin@example.com','$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi','superadmin');

-- Categories
INSERT IGNORE INTO categories (name, slug) VALUES
('Software Testing','software-testing'),
('Privacy & Security','privacy-security'),
('Developer Tools','developer-tools'),
('Tutorials','tutorials');

-- Settings
INSERT IGNORE INTO settings (setting_key, setting_value, setting_type, label) VALUES
('site_name',        'Fake Address Generator',                        'text', 'Site Name'),
('site_tagline',     'Generate realistic fake addresses for any country worldwide', 'text', 'Site Tagline'),
('header_scripts',   '',                                              'html', 'Header Scripts'),
('footer_scripts',   '',                                              'html', 'Footer Scripts'),
('ga_tracking_id',   '',                                              'text', 'Google Analytics ID'),
('contact_email',    'info@example.com',                              'text', 'Contact Email');

-- Content Sections
INSERT IGNORE INTO content_sections (section_key, title, body, sort_order) VALUES
('homepage_why_use',
 'Why Use a Fake Address Generator?',
 '<div class="text-grid"><article><h3>Software Testing & QA</h3><p>Developers need dummy data to test registration forms or shipping APIs without risking real user privacy.</p></article><article><h3>Privacy Protection</h3><p>Protect your identity from data brokers when signing up for non-essential digital services.</p></article><article><h3>300+ Locales</h3><p>Generate realistic addresses for any country in the world, with native formatting per locale.</p></article><article><h3>Powered by FakerPHP</h3><p>Built on the industry-standard FakerPHP library for maximum realism and reliability.</p></article></div>',
 1),
('homepage_how_it_works',
 'How It Works',
 '<p>Select any country or locale from the dropdown, choose an optional gender filter, then click <strong>Generate</strong>. A complete address — including name, street, city, postcode, phone, email, and more — appears instantly. Every field is contextually appropriate for the selected locale.</p>',
 2);

-- FAQs
INSERT IGNORE INTO faqs (question, answer, sort_order) VALUES
('What is a Fake Address Generator used for?',
 'A fake address generator is primarily used by developers and QA testers to fill registration forms, test shipping APIs, and generate realistic dummy datasets without using real personal information. It is also useful for writers, game designers, and privacy-conscious users.',
 1),
('Are these addresses real?',
 'No. All addresses generated by this tool are entirely fictional and randomly constructed. They are not tied to any real person, property, or postal record. Names, street addresses, phone numbers and emails follow realistic formatting for each locale but do not correspond to real individuals.',
 2),
('Which countries and locales are supported?',
 'We support 300+ locales from the ICU locale list, covering virtually every country in the world. Locales with native FakerPHP support produce highly realistic data; others fall back to English formatting while still using the correct country context.',
 3),
('Is it legal to use fake addresses?',
 'Using fake addresses for software testing, data masking, form validation, and similar technical purposes is entirely legal. Never use fake addresses to deceive others, commit fraud, or violate a platform terms of service.',
 4),
('What fields are generated?',
 'Fields vary by locale and include: full name, gender, street address, city, state/region, postcode, country, phone number, mobile number, email, username, company name, job title, time zone, and coordinates. Fields that are not supported for a given locale are omitted automatically.',
 5);

-- Footer links (funnel layout)
INSERT INTO footer_links (label, url, funnel_row, sort_order, is_active) VALUES

-- Row 1: Major English-speaking countries
('US Address Generator',          '/fake-address/english-united-states',           1, 1,  1),
('UK Address Generator',          '/fake-address/english-united-kingdom',          1, 2,  1),
('Canada Address Generator',      '/fake-address/english-canada',                  1, 3,  1),
('Australia Address Generator',   '/fake-address/english-australia',               1, 4,  1),
('New Zealand Address Generator', '/fake-address/english-new-zealand',             1, 5,  1),
('Ireland Address Generator',     '/fake-address/irish-ireland',                   1, 6,  1),
('South Africa Address Generator','/fake-address/english-south-africa',            1, 7,  1),
('Nigeria Address Generator',     '/fake-address/english-nigeria',                 1, 8,  1),
('Singapore Address Generator',   '/fake-address/english-singapore',               1, 9,  1),
('Philippines Address Generator', '/fake-address/english-philippines',             1, 10, 1),

-- Row 2: Western Europe
('Germany Address Generator',     '/fake-address/german-germany',                  2, 1,  1),
('France Address Generator',      '/fake-address/french-france',                   2, 2,  1),
('Spain Address Generator',       '/fake-address/spanish-spain',                   2, 3,  1),
('Italy Address Generator',       '/fake-address/italian-italy',                   2, 4,  1),
('Netherlands Address Generator', '/fake-address/dutch-netherlands',               2, 5,  1),
('Belgium Address Generator',     '/fake-address/dutch-belgium',                   2, 6,  1),
('Switzerland Address Generator', '/fake-address/german-switzerland',              2, 7,  1),
('Portugal Address Generator',    '/fake-address/portuguese-portugal',             2, 8,  1),
('Sweden Address Generator',      '/fake-address/swedish-sweden',                  2, 9,  1),
('Norway Address Generator',      '/fake-address/norwegian-bokmal-norway',         2, 10, 1),
('Denmark Address Generator',     '/fake-address/danish-denmark',                  2, 11, 1),
('Finland Address Generator',     '/fake-address/finnish-finland',                 2, 12, 1),
('Austria Address Generator',     '/fake-address/german-austria',                  2, 13, 1),
('Greece Address Generator',      '/fake-address/greek-greece',                    2, 14, 1),

-- Row 3: Eastern Europe
('Russia Address Generator',      '/fake-address/russian-russia',                  3, 1,  1),
('Poland Address Generator',      '/fake-address/polish-poland',                   3, 2,  1),
('Ukraine Address Generator',     '/fake-address/ukrainian-ukraine',               3, 3,  1),
('Czech Republic Address Generator','/fake-address/czech-czechia',                 3, 4,  1),
('Romania Address Generator',     '/fake-address/romanian-romania',                3, 5,  1),
('Hungary Address Generator',     '/fake-address/hungarian-hungary',               3, 6,  1),
('Bulgaria Address Generator',    '/fake-address/bulgarian-bulgaria',              3, 7,  1),
('Serbia Address Generator',      '/fake-address/serbian-serbia',                  3, 8,  1),
('Croatia Address Generator',     '/fake-address/croatian-croatia',                3, 9,  1),
('Slovakia Address Generator',    '/fake-address/slovak-slovakia',                 3, 10, 1),
('Slovenia Address Generator',    '/fake-address/slovenian-slovenia',              3, 11, 1),
('Estonia Address Generator',     '/fake-address/estonian-estonia',                3, 12, 1),
('Latvia Address Generator',      '/fake-address/latvian-latvia',                  3, 13, 1),
('Lithuania Address Generator',   '/fake-address/lithuanian-lithuania',            3, 14, 1),
('Moldova Address Generator',     '/fake-address/romanian-moldova',                3, 15, 1),
('Belarus Address Generator',     '/fake-address/belarusian-belarus',              3, 16, 1),
('Montenegro Address Generator',  '/fake-address/montenegrin-montenegro',          3, 17, 1),
('Albania Address Generator',     '/fake-address/albanian-albania',                3, 18, 1),

-- Row 4: Asia
('Japan Address Generator',       '/fake-address/japanese-japan',                  4, 1,  1),
('China Address Generator',       '/fake-address/chinese-simplified-china',        4, 2,  1),
('South Korea Address Generator', '/fake-address/korean-south-korea',              4, 3,  1),
('India Address Generator',       '/fake-address/hindi-india',                     4, 4,  1),
('Indonesia Address Generator',   '/fake-address/indonesian-indonesia',            4, 5,  1),
('Malaysia Address Generator',    '/fake-address/malay-malaysia',                  4, 6,  1),
('Thailand Address Generator',    '/fake-address/thai-thailand',                   4, 7,  1),
('Vietnam Address Generator',     '/fake-address/vietnamese-vietnam',              4, 8,  1),
('Philippines Address Generator', '/fake-address/english-philippines',             4, 9,  1),
('Bangladesh Address Generator',  '/fake-address/bengali-bangladesh',              4, 10, 1),
('Taiwan Address Generator',      '/fake-address/chinese-traditional-taiwan',      4, 11, 1),
('Kazakhstan Address Generator',  '/fake-address/kazakh-kazakhstan',               4, 12, 1),
('Nepal Address Generator',       '/fake-address/nepali-nepal',                    4, 13, 1),
('Azerbaijan Address Generator',  '/fake-address/azerbaijani-azerbaijan',          4, 14, 1),
('Armenia Address Generator',     '/fake-address/armenian-armenia',                4, 15, 1),
('Georgia Address Generator',     '/fake-address/georgian-georgia',                4, 16, 1),

-- Row 5: Middle East
('Saudi Arabia Address Generator','/fake-address/arabic-saudi-arabia',             5, 1,  1),
('UAE Address Generator',         '/fake-address/arabic-united-arab-emirates',     5, 2,  1),
('Israel Address Generator',      '/fake-address/hebrew-israel',                   5, 3,  1),
('Iran Address Generator',        '/fake-address/persian-iran',                    5, 4,  1),
('Jordan Address Generator',      '/fake-address/arabic-jordan',                   5, 5,  1),
('Cyprus Address Generator',      '/fake-address/greek-cyprus',                    5, 6,  1),

-- Row 6: Latin America
('Brazil Address Generator',      '/fake-address/portuguese-brazil',               6, 1,  1),
('Mexico Address Generator',      '/fake-address/spanish-mexico',                  6, 2,  1),
('Argentina Address Generator',   '/fake-address/spanish-argentina',               6, 3,  1),
('Colombia Address Generator',    '/fake-address/spanish-colombia',                6, 4,  1),
('Venezuela Address Generator',   '/fake-address/spanish-venezuela',               6, 5,  1),
('Chile Address Generator',       '/fake-address/spanish-chile',                   6, 6,  1),
('Peru Address Generator',        '/fake-address/spanish-peru',                    6, 7,  1),
('Ecuador Address Generator',     '/fake-address/spanish-ecuador',                 6, 8,  1),
('Cuba Address Generator',        '/fake-address/spanish-cuba',                    6, 9,  1),
('Costa Rica Address Generator',  '/fake-address/spanish-costa-rica',              6, 10, 1),
('Bolivia Address Generator',     '/fake-address/spanish-bolivia',                 6, 11, 1),
('Dominican Republic Address Generator','/fake-address/spanish-dominican-republic',6, 12, 1),

-- Row 7: Africa
('Algeria Address Generator',     '/fake-address/arabic-algeria',                  7, 1,  1),
('Egypt Address Generator',       '/fake-address/arabic-egypt',                    7, 2,  1),
('Morocco Address Generator',     '/fake-address/french-morocco',                  7, 3,  1),
('Ethiopia Address Generator',    '/fake-address/amharic-ethiopia',                7, 4,  1),
('Cameroon Address Generator',    '/fake-address/french-cameroon',                 7, 5,  1),
('Gabon Address Generator',       '/fake-address/french-gabon',                    7, 6,  1),
('Burkina Faso Address Generator','/fake-address/french-burkina-faso',             7, 7,  1),
('Burundi Address Generator',     '/fake-address/rundi-burundi',                   7, 8,  1),
('Botswana Address Generator',    '/fake-address/english-botswana',                7, 9,  1),
('Uganda Address Generator',      '/fake-address/english-uganda',                  7, 10, 1),
('Cape Verde Address Generator',  '/fake-address/portuguese-cape-verde',           7, 11, 1),
('Angola Address Generator',      '/fake-address/portuguese-angola',               7, 12, 1),
('Eritrea Address Generator',     '/fake-address/tigrinya-eritrea',                7, 13, 1),
('Congo DRC Address Generator',   '/fake-address/french-congo-kinshasa',           7, 14, 1),
('Republic of Congo Address Generator','/fake-address/french-congo-brazzaville',   7, 15, 1),
('Central African Rep. Address Generator','/fake-address/french-central-african-republic', 7, 16, 1),

-- Row 8: Oceania & smaller nations
('Fiji Address Generator',        '/fake-address/english-fiji',                    8, 1,  1),
('Bermuda Address Generator',     '/fake-address/english-bermuda',                 8, 2,  1),
('Barbados Address Generator',    '/fake-address/english-barbados',                8, 3,  1),
('Bahamas Address Generator',     '/fake-address/english-bahamas',                 8, 4,  1),
('Belize Address Generator',      '/fake-address/english-belize',                  8, 5,  1),
('Grenada Address Generator',     '/fake-address/english-grenada',                 8, 6,  1),
('Antigua and Barbuda Address Generator','/fake-address/english-antigua-barbuda',  8, 7,  1),
('Andorra Address Generator',     '/fake-address/catalan-andorra',                 8, 8,  1),
('Iceland Address Generator',     '/fake-address/icelandic-iceland',               8, 9,  1),
('Bhutan Address Generator',      '/fake-address/dzongkha-bhutan',                 8, 10, 1),
('Brunei Address Generator',      '/fake-address/malay-brunei',                    8, 11, 1),
('Caribbean Netherlands Address Generator','/fake-address/dutch-caribbean-netherlands', 8, 12, 1),

-- Row 9: Navigation
('All Country Generators',        '/fake-address',                                 9, 1,  1),
('Privacy Policy',                '/privacy',                                      9, 2,  1),
('Contact Us',                    'mailto:info@example.com',                       9, 3,  1);
