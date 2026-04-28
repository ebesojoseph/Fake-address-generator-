-- ============================================================
-- Fake Address Generator - Complete Database Schema
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET sql_mode = '';

-- Admin Users
CREATE TABLE IF NOT EXISTS `admin_users` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(80) NOT NULL UNIQUE,
  `email` VARCHAR(180) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(120) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blog Posts
CREATE TABLE IF NOT EXISTS `posts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(280) NOT NULL UNIQUE,
  `excerpt` TEXT,
  `content` LONGTEXT,
  `thumbnail` VARCHAR(300),
  `category_id` INT UNSIGNED,
  `is_featured` TINYINT(1) DEFAULT 0,
  `is_published` TINYINT(1) DEFAULT 0,
  `view_count` INT UNSIGNED DEFAULT 0,
  `meta_title` VARCHAR(255),
  `meta_description` TEXT,
  `meta_keywords` VARCHAR(500),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Homepage Content Sections
CREATE TABLE IF NOT EXISTS `content_sections` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `section_key` VARCHAR(100) NOT NULL UNIQUE,
  `title` VARCHAR(255),
  `content` LONGTEXT,
  `is_active` TINYINT(1) DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Generation Logs
CREATE TABLE IF NOT EXISTS `generation_logs` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `session_id` VARCHAR(128) NOT NULL,
  `generated_address` JSON,
  `ip_address` VARCHAR(45),
  `country` VARCHAR(100),
  `city` VARCHAR(100),
  `session_count` INT UNSIGNED DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_session` (`session_id`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Global Settings (header/footer scripts, footer links, etc.)
CREATE TABLE IF NOT EXISTS `settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` LONGTEXT,
  `setting_type` ENUM('text','html','json','boolean') DEFAULT 'text',
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Footer Links (funnel layout)
CREATE TABLE IF NOT EXISTS `footer_links` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `label` VARCHAR(150) NOT NULL,
  `url` VARCHAR(500) NOT NULL,
  `row_number` TINYINT UNSIGNED NOT NULL DEFAULT 1,
  `sort_order` INT DEFAULT 0,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- Seed Data
-- ============================================================

-- Default admin (password: Admin@1234)
INSERT INTO `admin_users` (`username`, `email`, `password_hash`) VALUES
('admin', 'admin@example.com', '$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Default categories
INSERT INTO `categories` (`name`, `slug`) VALUES
('Software Testing', 'software-testing'),
('Privacy & Security', 'privacy-security'),
('Developer Tools', 'developer-tools'),
('Data Masking', 'data-masking');

-- Default settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `setting_type`) VALUES
('site_name', 'Fake Address Generator', 'text'),
('header_scripts', '', 'html'),
('footer_scripts', '', 'html'),
('homepage_content_title', 'What Is a Fake Address Generator?', 'text'),
('homepage_content_body', '<p>A <strong>Fake Address Generator</strong> is an online tool that creates realistic-looking but entirely fictional mailing addresses. These addresses follow the correct format for a given country, including street names, cities, states, and postal codes — but they do not correspond to any real location.</p><p>Developers, QA engineers, data scientists, and privacy-conscious users rely on these tools daily to fill forms, seed test databases, and protect their personal information from data brokers.</p>', 'html');

-- Default footer links (funnel: row 1 = widest, decreasing)
INSERT INTO `footer_links` (`label`, `url`, `row_number`, `sort_order`) VALUES
('US Address Generator', '/us-fake-address', 1, 1),
('UK Address Generator', '/uk-fake-address', 1, 2),
('Canada Address Generator', '/ca-fake-address', 1, 3),
('Australia Address Generator', '/au-fake-address', 1, 4),
('Random Name Generator', '/random-name', 1, 5),
('Privacy Policy', '/privacy', 1, 6),
('API Testing Guide', '/api-testing', 2, 1),
('Data Masking Tips', '/data-masking', 2, 2),
('Valid ZIP Codes', '/valid-zip-codes', 2, 3),
('Fake Business Address', '/business-address', 2, 4),
('Developer Docs', '/docs', 3, 1),
('About Us', '/about', 3, 2),
('Contact', '/contact', 3, 3),
('info@fakeaddressgenerator.io', 'mailto:info@fakeaddressgenerator.io', 4, 1);

-- Default content section
INSERT INTO `content_sections` (`section_key`, `title`, `content`, `sort_order`) VALUES
('why_use', 'Why Use a Fake Address Generator?', '<p>Whether you are a developer building the next big e-commerce platform or a privacy advocate trying to avoid spam, a fake address generator offers a safe, legal, and effective solution.</p>', 1),
('how_it_works', 'How It Works', '<p>Our generator uses curated datasets of real street names, city names, and postal code ranges to create addresses that look authentic — without pointing to a real person or location.</p>', 2);

-- Sample blog posts
INSERT INTO `posts` (`title`, `slug`, `excerpt`, `content`, `thumbnail`, `category_id`, `is_featured`, `is_published`, `meta_title`, `meta_description`) VALUES
('How to Use Fake Data for API Testing', 'how-to-use-fake-data-for-api-testing', 'API testing with fake data protects real users while giving developers the freedom to test edge cases thoroughly.', '<p>When building APIs that accept address data, the last thing you want is to accidentally expose or corrupt real user information during testing...</p>', NULL, 3, 1, 1, 'Fake Data for API Testing | Guide', 'Learn how to use fake address data to test your APIs safely and efficiently.'),
('5 Reasons to Protect Your Address Online', 'protect-your-address-online', 'Your home address is one of your most sensitive pieces of personal data. Here is why you should guard it carefully.', '<p>Data brokers collect and sell personal information, including your home address, without your explicit consent...</p>', NULL, 2, 1, 1, '5 Reasons to Protect Your Address Online', 'Discover why protecting your address from data brokers is critical for your digital privacy.'),
('Understanding US ZIP Code Formats', 'understanding-us-zip-code-formats', 'ZIP codes are more than just numbers — they encode geographic information critical for mail routing.', '<p>The United States ZIP code system was introduced in 1963 by the US Postal Service...</p>', NULL, 1, 0, 1, 'US ZIP Code Formats Explained', 'A deep dive into how US ZIP codes work and how they are structured.');