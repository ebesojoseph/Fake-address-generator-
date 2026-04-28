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
INSERT IGNORE INTO footer_links (label, url, row_number, sort_order) VALUES
('US Address Generator',    '/fake-address/english-united-states',    1, 1),
('UK Address Generator',    '/fake-address/english-united-kingdom',   1, 2),
('Canada Address Generator','/fake-address/english-canada',           1, 3),
('Australia Address',       '/fake-address/english-australia',        1, 4),
('Germany Address',         '/fake-address/german-germany',           1, 5),
('France Address',          '/fake-address/french-france',            1, 6),
('Spain Address',           '/fake-address/spanish-spain',            2, 1),
('Brazil Address',          '/fake-address/portuguese-brazil',        2, 2),
('Japan Address',           '/fake-address/japanese-japan',           2, 3),
('China Address',           '/fake-address/chinese-simplified-china', 2, 4),
('All Country Generators',  '/fake-address',                          3, 1),
('Privacy Policy',          '/privacy',                               3, 2),
('Contact Us',              'mailto:info@example.com',                4, 1);
