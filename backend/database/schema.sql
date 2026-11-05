-- =====================================================================
--  BB Caterers — database schema + seed content
--  Import once:  mysql -u root -p < schema.sql   (or run database/install.php)
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `bb_caterers` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `bb_caterers`;

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
-- Admin users
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
  `id`                   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`                 VARCHAR(100) NOT NULL,
  `username`             VARCHAR(50)  NOT NULL UNIQUE,
  `email`                VARCHAR(150) NOT NULL UNIQUE,
  `password_hash`        VARCHAR(255) NOT NULL,
  `must_change_password` TINYINT(1)   NOT NULL DEFAULT 0,
  `reset_token_hash`     CHAR(64)     NULL,
  `reset_expires_at`     DATETIME     NULL,
  `last_login_at`        DATETIME     NULL,
  `created_at`           TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`           TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `ip`           VARCHAR(45) NOT NULL,
  `attempted_at` DATETIME    NOT NULL,
  KEY `idx_ip_time` (`ip`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Key/value settings (branding, section headings, contact, SMTP ...)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `key`        VARCHAR(80) NOT NULL PRIMARY KEY,
  `value`      TEXT        NULL,
  `updated_at` TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- Content tables (all share sort_order + is_active)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `menu_items` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `label`      VARCHAR(60)  NOT NULL,
  `url`        VARCHAR(255) NOT NULL,
  `sort_order` INT          NOT NULL DEFAULT 0,
  `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `hero_slides` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `eyebrow`    VARCHAR(120) NULL,
  `title`      VARCHAR(255) NOT NULL,
  `subtitle`   TEXT         NULL,
  `image`      VARCHAR(255) NULL,
  `btn1_text`  VARCHAR(60)  NULL,
  `btn1_link`  VARCHAR(255) NULL,
  `btn2_text`  VARCHAR(60)  NULL,
  `btn2_link`  VARCHAR(255) NULL,
  `sort_order` INT          NOT NULL DEFAULT 0,
  `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `services` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title`       VARCHAR(120) NOT NULL,
  `description` VARCHAR(255) NULL,
  `image`       VARCHAR(255) NULL,
  `icon`        VARCHAR(50)  NULL,
  `sort_order`  INT          NOT NULL DEFAULT 0,
  `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `gallery` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title`      VARCHAR(120) NULL,
  `image`      VARCHAR(255) NOT NULL,
  `layout`     ENUM('normal','wide','tall','large') NOT NULL DEFAULT 'normal',
  `sort_order` INT          NOT NULL DEFAULT 0,
  `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `highlights` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title`      VARCHAR(120) NOT NULL,
  `icon`       VARCHAR(50)  NULL,
  `sort_order` INT          NOT NULL DEFAULT 0,
  `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `events` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title`      VARCHAR(120) NOT NULL,
  `image`      VARCHAR(255) NULL,
  `icon`       VARCHAR(50)  NULL,
  `sort_order` INT          NOT NULL DEFAULT 0,
  `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `features` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title`       VARCHAR(120) NOT NULL,
  `description` VARCHAR(255) NULL,
  `icon`        VARCHAR(50)  NULL,
  `sort_order`  INT          NOT NULL DEFAULT 0,
  `is_active`   TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `testimonials` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(100) NOT NULL,
  `role`       VARCHAR(120) NULL,
  `message`    TEXT         NOT NULL,
  `rating`     TINYINT      NOT NULL DEFAULT 5,
  `photo`      VARCHAR(255) NULL,
  `sort_order` INT          NOT NULL DEFAULT 0,
  `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `enquiries` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(100) NOT NULL,
  `email`      VARCHAR(150) NOT NULL,
  `phone`      VARCHAR(30)  NULL,
  `event_date` DATE         NULL,
  `event_type` VARCHAR(120) NULL,
  `guests`     INT UNSIGNED NULL,
  `message`    TEXT         NULL,
  `status`     ENUM('new','contacted','confirmed','closed') NOT NULL DEFAULT 'new',
  `admin_note` TEXT         NULL,
  `ip`         VARCHAR(45)  NULL,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================================
--  Seed data (INSERT IGNORE keeps re-imports safe)
-- =====================================================================

-- Default admin  →  username: admin   password: admin123
INSERT IGNORE INTO `admins` (`id`,`name`,`username`,`email`,`password_hash`,`must_change_password`) VALUES
(1,'Administrator','admin','hello@bblcaterers.in','$2y$10$vTDr31qvvXXy8xVjyD2D2uDU5XXlZVwEc7reK5U37v3.y6ceQsiom',0);

INSERT IGNORE INTO `settings` (`key`,`value`) VALUES
-- General / branding
('site_name','BB Caterers'),
('site_tagline','Bharatha Bhojanam'),
('logo','media/branding/logo.png'),
('favicon','media/branding/favicon.png'),
('apple_touch_icon','media/branding/apple-touch-icon.png'),
('header_cta_text','Get a Quote'),
('header_cta_link','#contact'),
('meta_title','BB Caterers — Authentic Indian Catering | Bharatha Bhojanam'),
('meta_keywords','catering services, south indian catering, indian catering, wedding catering, live dosa station, live chaat counter, podi varieties, diwali sweets and snacks, corporate catering, party catering, traditional food, BB Caterers, Bharatha Bhojanam'),
('og_image','media/hero/hero-1.webp'),
('meta_description','Authentic Indian catering — live dosa stations, live snacks, podi varieties, festive sweets and traditional feasts, freshly prepared for every celebration.'),
('theme_page_bg','media/decor/page-bg.webp'),
('theme_divider','media/decor/lotus-divider.png'),
('theme_flourish','media/decor/leaf-flourish.png'),
('theme_arch','media/decor/events-arch.webp'),
('hero_autoplay_seconds','6'),
-- About
('about_enabled','1'),
('about_eyebrow','About BB Caterers'),
('about_title','Tradition Served with Excellence.'),
('about_text','Bharatha Bhojanam means "the food of Bharat". At BB Caterers, we bring generations-old recipes to your table — slow-ground podis, crisp dosas off the tawa, and festive sweets made the way our grandmothers made them.'),
('about_text_2','Every menu is cooked fresh, served with warmth, and designed around your celebration.'),
('about_image_1','media/about/about-1.webp'),
('about_image_2','media/about/about-2.webp'),
-- Services
('services_enabled','1'),
('services_eyebrow','Our Specialities'),
('services_title','A Culinary Experience Like No Other'),
-- Gallery
('gallery_enabled','1'),
('gallery_eyebrow','Food Gallery'),
('gallery_title','A Feast for the Eyes.'),
-- Highlight banner
('highlight_enabled','1'),
('highlight_title','The Art of Authentic Indian Catering'),
('highlight_image','media/highlight/banner.webp'),
-- Events
('events_enabled','1'),
('events_title','Made for *Moments* Worth *Celebrating*'),
-- Why choose us
('features_enabled','1'),
('features_eyebrow','Why Choose Us'),
('features_title','Excellence in Every Bite'),
-- Testimonials
('testimonials_enabled','1'),
('testimonials_eyebrow','Testimonials'),
('testimonials_title','Loved for the Taste. Remembered for the Experience.'),
-- Contact
('contact_enabled','1'),
('contact_eyebrow','Contact & Enquiry'),
('contact_title','Let''s Create Something Delicious.'),
('contact_text','Tell us about your celebration and we''ll craft a menu just for you.'),
('contact_phone','840 239 9450'),
('contact_email','hello@bblcaterers.in'),
('contact_instagram','@bharatha_bhojanam_bbcaterers'),
('contact_instagram_url','https://www.instagram.com/bharatha_bhojanam_bbcaterers/'),
('contact_whatsapp','918402399450'),
('contact_address',''),
('enquiry_min_guests','10'),
('enquiry_max_guests','5000'),
('contact_button_text','Request a Catering Quote'),
('contact_success_message','Thank you! Your enquiry has been received. Our team will get back to you shortly.'),
-- CTA band
('cta_enabled','1'),
('cta_line_1','Good Food. Beautiful Moments.'),
('cta_line_2','Timeless Traditions.'),
('cta_button_text','Let''s Talk Catering'),
('cta_button_link','#contact'),
-- Footer
('footer_about','Authentic Indian catering — live stations, festive sweets and traditional feasts, freshly prepared for every celebration.'),
('footer_copyright','© {year} BB Caterers · Bharatha Bhojanam. All rights reserved.'),
('footer_credit','Designed & Developed by'),
('footer_credit_link_text','Vetri IT Systems'),
('footer_credit_url','https://vetriitsystems.com/'),
-- Floating buttons
('float_whatsapp_enabled','1'),
('float_call_enabled','1'),
('float_instagram_enabled','1'),
('float_top_enabled','1'),
('float_whatsapp_message','Hello BB Caterers, I would like to enquire about catering.'),
('social_facebook',''),
('social_youtube',''),
-- SMTP (configure in Admin → SMTP Settings)
('smtp_host',''),
('smtp_port','587'),
('smtp_encryption','tls'),
('smtp_username',''),
('smtp_password',''),
('smtp_from_email',''),
('smtp_from_name','BB Caterers'),
('smtp_notify_email','hello@bblcaterers.in'),
('smtp_autoreply','1');

INSERT IGNORE INTO `menu_items` (`id`,`label`,`url`,`sort_order`) VALUES
(1,'Home','#home',1),(2,'About','#about',2),(3,'Services','#services',3),(4,'Gallery','#gallery',4),(5,'Contact','#contact',5);

INSERT IGNORE INTO `hero_slides` (`id`,`eyebrow`,`title`,`subtitle`,`image`,`btn1_text`,`btn1_link`,`btn2_text`,`btn2_link`,`sort_order`) VALUES
(1,'Traditional Indian Feast','Authentic Indian Flavours. Crafted for Every Celebration.','Experience the richness of traditional Indian cuisine with freshly prepared delicacies.','media/hero/hero-1.webp','Explore Our Services','#services','Plan Your Event','#contact',1),
(2,'Live Food Experiences','Freshly Made. Perfectly Served.','Bring the excitement of freshly prepared live food stations to your celebrations.','media/hero/hero-2.webp','Explore Our Services','#services','Plan Your Event','#contact',2),
(3,'Festive Specials','Sweets, Snacks & Feasts for Every Festival.','Handcrafted festive treats and traditional menus made the way they always have been.','media/hero/hero-3.webp','Explore Our Services','#services','Plan Your Event','#contact',3);

INSERT IGNORE INTO `services` (`id`,`title`,`description`,`image`,`icon`,`sort_order`) VALUES
(1,'Live Dosa Stations','Crispy dosas, chutneys and sambar.','media/services/live-dosa.webp','concierge-bell',1),
(2,'Live Snacks','Traditional snacks and savouries.','media/services/live-snacks.webp','flame',2),
(3,'Podi Varieties','Authentic podi selections.','media/services/podi.webp','soup',3),
(4,'Diwali Sweets & Snacks','Traditional festive treats.','media/services/sweets.webp','laddoo',4),
(5,'Live Chaat','Pani puri, bhel puri and more.','media/services/live-chaat.webp','salad',5);

INSERT IGNORE INTO `gallery` (`id`,`title`,`image`,`layout`,`sort_order`) VALUES
(1,'Masala dosa with chutneys','media/gallery/dosa-plate.webp','large',1),
(2,'Pani puri and bhel puri','media/gallery/chaat.webp','tall',2),
(3,'Podi and idli','media/gallery/spice-bowls.webp','normal',3),
(4,'Vada, bajji and bonda','media/gallery/vadas.webp','normal',4),
(5,'Festive sweets and snacks','media/gallery/festive-sweets.webp','wide',5),
(6,'Curry Feast','media/gallery/curry-feast.webp','normal',6),
(7,'Traditional banana leaf meal','media/events/private.webp','normal',7),
(8,'Wedding buffet spread','media/gallery/buffet.webp','normal',8);
UPDATE `gallery` SET `is_active` = 0 WHERE `id` = 6 AND `created_at` = `updated_at`;  -- Figma grid shows 7 photos

INSERT IGNORE INTO `highlights` (`id`,`title`,`icon`,`sort_order`) VALUES
(1,'Authentic Traditional Flavours','chef-hat',1),
(2,'Freshly Prepared Delicacies','cooking-pot',2),
(3,'Quality Ingredients','gem',3),
(4,'Memorable Food Experiences','users',4);

INSERT IGNORE INTO `events` (`id`,`title`,`image`,`icon`,`sort_order`) VALUES
(1,'Weddings & Receptions','media/events/weddings.webp','rings',1),
(2,'Private Celebrations','media/events/private.webp','hand-platter',2),
(3,'Corporate Gatherings','media/events/corporate.webp','users',3),
(4,'Festivals & Special Occasions','media/events/festivals.webp','gift',4);

INSERT IGNORE INTO `features` (`id`,`title`,`description`,`icon`,`sort_order`) VALUES
(1,'Expert Culinary Team','Crafted by skilled chefs','chef-hat',1),
(2,'Fresh & Healthy Food','Pure ingredients, better taste','leaf',2),
(3,'Customised Menus','Tailored to your needs','concierge-bell',3),
(4,'Professional Service','With care & dedication','users',4),
(5,'Memorable Experience','For your special moments','award-star',5),
(6,'Trusted Partner','For all your catering needs','handshake',6);

INSERT IGNORE INTO `testimonials` (`id`,`name`,`role`,`message`,`rating`,`sort_order`) VALUES
(1,'Anita Sharma','Wedding Reception','Exceptional taste and excellent service!',5,1),
(2,'Ravi Kumar','Housewarming','Our guests loved the food!',5,2),
(3,'Priya Menon','Birthday Celebration','Truly a memorable experience.',5,3);
