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
  `alt_text`    VARCHAR(255) NULL,
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
  `alt_text`   VARCHAR(255) NULL,
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
  `description` VARCHAR(255) NULL,
  `image`      VARCHAR(255) NULL,
  `alt_text`   VARCHAR(255) NULL,
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

CREATE TABLE IF NOT EXISTS `faqs` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `question`   VARCHAR(255) NOT NULL,
  `answer`     TEXT         NOT NULL,
  `sort_order` INT          NOT NULL DEFAULT 0,
  `is_active`  TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
('meta_title','Indian Catering Services in Fontana, CA | BB Caterers'),
('meta_keywords','catering services, south indian catering, indian catering, wedding catering, live dosa station, live chaat counter, podi varieties, diwali sweets and snacks, corporate catering, party catering, traditional food, BB Caterers, Bharatha Bhojanam'),
('og_image','media/hero/hero-1.webp'),
('meta_description','BB Caterers offers authentic Indian catering in Fontana, CA: live dosa stations, chaat, snacks and sweets for weddings, parties and corporate events.'),
('theme_page_bg','media/decor/page-bg.webp'),
('theme_divider','media/decor/lotus-divider.png'),
('theme_flourish','media/decor/leaf-flourish.png'),
('theme_arch','media/decor/events-arch.webp'),
('hero_autoplay_seconds','6'),
-- About
('about_enabled','1'),
('about_eyebrow','About BB Caterers'),
('about_title','Tradition Served with Excellence.'),
('about_text','Bharatha Bhojanam means “the food of Bharat”. BB Caterers is a Fontana, CA caterer bringing generations-old recipes to your table: slow-ground podis, crisp dosas off the tawa, and festive sweets made the way our grandmothers made them.'),
('about_text_2','As an Indian food catering service focused on South Indian catering, we cook every menu fresh, serve it with warmth and design it around your celebration, whether it is a wedding, a birthday party or a corporate event.'),
('about_image_1','media/about/about-1.webp'),
('about_image_2','media/about/about-2.webp'),
-- Services
('services_enabled','1'),
('services_eyebrow','Our Specialities'),
('services_title','South Indian Flavours, Made Fresh for Your Celebration'),
-- Gallery
('gallery_enabled','1'),
('gallery_eyebrow','Our Gallery'),
('gallery_title','A Taste of Our Catering, Served Fresh'),
-- Highlight banner
('highlight_enabled','1'),
('highlight_title','The Art of Authentic Indian Catering'),
('highlight_image','media/highlight/banner.webp'),
-- Events
('events_enabled','1'),
('events_title','Made for *moments* worth *celebrating*.'),
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
('contact_text','Tell us about your wedding, party or corporate event and we’ll craft a catering menu just for you in Fontana, CA.'),
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
('footer_about','Authentic Indian catering in Fontana, CA, featuring live dosa stations, chaat, festive sweets and traditional South Indian favourites for weddings, parties and special celebrations.'),
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

-- SEO content (BB_Caterers_SEO_Content document)
INSERT IGNORE INTO `settings` (`key`,`value`) VALUES
('logo_alt','BB Caterers (Bharatha Bhojanam) – Indian catering services in Fontana, CA'),
('services_intro','Discover Indian catering services built around South Indian favourites: live dosa stations, snacks, podi varieties, festive sweets and chaat, all prepared fresh for your celebration in Fontana, CA.'),
('services_intro_2','Whether you''re planning a family function or a large gathering, we customise every menu around your guests, with vegetarian and South Indian options available.'),
('highlight_text','Indian catering built on traditional recipes, quality ingredients and freshly prepared food.'),
('highlight_image_alt','Plated Indian feast with a colourful variety of traditional dishes'),
('faq_enabled','1'),
('faq_eyebrow','Frequently Asked Questions'),
('faq_title','Catering Questions, Answered.'),
('faq_text','Planning a wedding, birthday party or corporate event? Find answers about our menus, live stations and quotes below, or contact our team for help.'),
('contact_event_types','Wedding / Reception
Birthday / Private Party
Corporate Event
Festival / Special Occasion
Other'),
('float_whatsapp_label','Chat with BB Caterers on WhatsApp'),
('float_instagram_label','BB Caterers on Instagram'),
('float_call_label','Call BB Caterers on 840 239 9450');

INSERT IGNORE INTO `menu_items` (`id`,`label`,`url`,`sort_order`) VALUES
(1,'Home','#home',1),(2,'About','#about',2),(3,'Services','#services',3),(4,'Gallery','#gallery',4),(5,'Contact','#contact',5);

INSERT IGNORE INTO `hero_slides` (`id`,`eyebrow`,`title`,`subtitle`,`image`,`btn1_text`,`btn1_link`,`btn2_text`,`btn2_link`,`sort_order`) VALUES
(1,'Traditional Indian Feast','Authentic Indian Catering. Crafted for Every Celebration','Traditional Indian dishes, live food stations and festive favourites, freshly prepared for weddings, parties and corporate events.','media/hero/hero-1.webp','Explore Our Services','#services','Plan Your Event','#contact',1),
(2,'Live Food Experiences','Freshly Made. Perfectly Served.','Bring the excitement of a live dosa station and freshly prepared snacks to your celebration, cooked fresh and served hot to your guests.','media/hero/hero-2.webp','Explore Our Services','#services','Plan Your Event','#contact',2),
(3,'Festive Specials','Traditional Indian Sweets, Snacks & Festive Feasts','Handcrafted festive treats and traditional menus made the way they always have been, from Diwali sweets to full festive feasts.','media/hero/hero-3.webp','Explore Our Services','#services','Plan Your Event','#contact',3);

INSERT IGNORE INTO `services` (`id`,`title`,`description`,`image`,`alt_text`,`icon`,`sort_order`) VALUES
(1,'Live Dosa Stations','Fresh dosas made live and served hot with chutneys and sambar.','media/services/live-dosa.webp','Masala dosa with chutneys and sambar – live dosa station catering','concierge-bell',1),
(2,'Live Snacks','Fresh South Indian snacks and savouries, prepared at your event.','media/services/live-snacks.webp','Brass bowls of traditional South Indian snacks and savouries','flame',2),
(3,'Podi Varieties','Traditional podis made to complement idli, dosa and rice.','media/services/podi.webp','Brass bowls of colourful podi and spice powders','soup',3),
(4,'Diwali Sweets & Snacks','Handcrafted Indian sweets and festive favourites for every celebration.','media/services/sweets.webp','Laddus and murukku in brass bowls – Indian sweets and snacks for Diwali','laddoo',4),
(5,'Live Chaat','Fresh pani puri, bhel puri and more at a live chaat counter.','media/services/live-chaat.webp','Colourful bowl of chaat – live chaat catering','salad',5);

INSERT IGNORE INTO `gallery` (`id`,`title`,`image`,`alt_text`,`layout`,`sort_order`) VALUES
(1,'Masala dosa with chutneys','media/gallery/dosa-plate.webp','Crisp masala dosa served with chutneys and sambar – dosa catering by BB Caterers','large',1),
(2,'Pani puri and bhel puri','media/gallery/chaat.webp','Live chaat with bhel and pani puri – chaat catering for parties and weddings','tall',2),
(3,'Podi and idli','media/gallery/spice-bowls.webp','Brass bowls of podi and spice powders – traditional podi varieties','normal',3),
(4,'Vada, bajji and bonda','media/gallery/vadas.webp','Medu vada on a banana leaf with chutney – South Indian snacks catering','normal',4),
(5,'Festive sweets and snacks','media/gallery/festive-sweets.webp','Laddus and murukku in brass bowls – Indian sweets catering for festivals','wide',5),
(6,'Curry Feast','media/gallery/curry-feast.webp',NULL,'normal',6),
(7,'Traditional banana leaf meal','media/events/private.webp','Traditional South Indian meal on a banana leaf with rice, papad and sides – vegetarian catering','normal',7),
(8,'Wedding buffet spread','media/gallery/buffet.webp','Wedding buffet spread in brass serving vessels with marigold garlands – wedding catering in Fontana, CA','normal',8);
UPDATE `gallery` SET `is_active` = 0 WHERE `id` = 6 AND `created_at` = `updated_at`;  -- Figma grid shows 7 photos

INSERT IGNORE INTO `highlights` (`id`,`title`,`icon`,`sort_order`) VALUES
(1,'Authentic Traditional Flavours','chef-hat',1),
(2,'Freshly Prepared Delicacies','cooking-pot',2),
(3,'Quality Ingredients','gem',3),
(4,'Memorable Food Experiences','users',4);

INSERT IGNORE INTO `events` (`id`,`title`,`description`,`image`,`alt_text`,`icon`,`sort_order`) VALUES
(1,'Weddings & Receptions','Wedding catering with traditional menus, live stations and festive sweets, planned around your celebration and guests.','media/events/weddings.webp','Wedding catering buffet with brass serving vessels and marigold garlands','rings',1),
(2,'Private Celebrations','Birthday and private party catering with live snacks, chaat and traditional South Indian favourites.','media/events/private.webp','Traditional South Indian meal served on a banana leaf for a private celebration','hand-platter',2),
(3,'Corporate Gatherings','Corporate catering with live stations and traditional menus for team events, celebrations and business gatherings.','media/events/corporate.webp','Vada, bonda and banana chips on a banana leaf for a corporate gathering','users',3),
(4,'Festivals & Special Occasions','Indian sweets, snacks and traditional feasts, freshly prepared for Diwali and special celebrations.','media/events/festivals.webp','Laddus, murukku and festive sweets in brass bowls for Diwali and special occasions','gift',4);

INSERT IGNORE INTO `features` (`id`,`title`,`description`,`icon`,`sort_order`) VALUES
(1,'Expert Culinary Team','Skilled chefs preparing authentic South Indian dishes from traditional recipes.','chef-hat',1),
(2,'Fresh & Healthy Food','Pure ingredients, better taste. Every dish is prepared fresh for your event.','leaf',2),
(3,'Customised Menus','Menus tailored to your needs, your guests and your occasion, including vegetarian options.','concierge-bell',3),
(4,'Professional Service','Professional, attentive service from enquiry to final serving.','users',4),
(5,'Memorable Experience','For your special moments, from weddings to birthday parties.','award-star',5),
(6,'Trusted Partner','A trusted local caterer for all your catering needs in Fontana, CA.','handshake',6);

INSERT IGNORE INTO `faqs` (`id`,`question`,`answer`,`sort_order`) VALUES
(1,'What types of events do you cater?','BB Caterers provides Indian catering for weddings and receptions, birthday and private parties, corporate gatherings, and festivals and special occasions in Fontana, CA and nearby areas.',1),
(2,'Do you offer Indian wedding catering?','Yes. Our Indian wedding catering includes traditional South Indian menus, live food stations and festive sweets, planned around your ceremony, reception and guest list.',2),
(3,'What is a live dosa station, and can I add one to my event?','A live dosa station is a counter where our team prepares dosas fresh and serves them hot with chutneys and sambar. It is a popular choice for weddings, parties and corporate events.',3),
(4,'Do you offer vegetarian catering?','Yes. Our South Indian menus include a variety of vegetarian dishes, and we can create a fully vegetarian menu based on your event and guest preferences.',4),
(5,'How do I get a catering quote?','Fill in the enquiry form or contact BB Caterers with your event date, event type, number of guests and menu preferences. We recommend contacting us early for weddings and festival seasons.',5);

INSERT IGNORE INTO `testimonials` (`id`,`name`,`role`,`message`,`rating`,`sort_order`) VALUES
(1,'Anita Sharma','Wedding Reception','Exceptional taste and excellent service!',5,1),
(2,'Ravi Kumar','Housewarming','Our guests loved the food!',5,2),
(3,'Priya Menon','Birthday Celebration','Truly a memorable experience.',5,3);
