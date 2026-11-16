-- uyanisinsesi - veritabani semasi (Asama 1: tanitim sitesi)
-- MySQL 8 / MariaDB 10.6+
-- Kullanim: mysql -u KULLANICI -p VERITABANI < database/schema.sql

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------------
-- Yonetim
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS admins (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name          VARCHAR(100) NOT NULL,
    email         VARCHAR(190) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role          ENUM('admin','editor') NOT NULL DEFAULT 'admin',
    last_login_at DATETIME NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_admins_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS settings (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `key`      VARCHAR(100) NOT NULL,
    `value`    TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_settings_key (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS menu_items (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    parent_id  INT UNSIGNED NULL,
    location   ENUM('header','footer_explore','footer_services','footer_legal') NOT NULL DEFAULT 'header',
    label      VARCHAR(100) NOT NULL,
    url        VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active  TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_menu_location (location, sort_order),
    CONSTRAINT fk_menu_parent FOREIGN KEY (parent_id) REFERENCES menu_items (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS media (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    path       VARCHAR(255) NOT NULL,
    alt        VARCHAR(255) NULL,
    mime       VARCHAR(100) NOT NULL,
    size       INT UNSIGNED NOT NULL DEFAULT 0,
    width      INT UNSIGNED NULL,
    height     INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ---------------------------------------------------------------------------
-- Sayfalar ve ana sayfa
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS pages (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug             VARCHAR(150) NOT NULL,
    title            VARCHAR(200) NOT NULL,
    body             MEDIUMTEXT NULL,
    hero_image       VARCHAR(255) NULL,
    meta_title       VARCHAR(200) NULL,
    meta_description VARCHAR(300) NULL,
    status           ENUM('draft','published') NOT NULL DEFAULT 'draft',
    created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_pages_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- type: hero, quote, intro, services, course_promo, testimonials, stats, latest_posts, contact, custom
CREATE TABLE IF NOT EXISTS home_blocks (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    type        VARCHAR(50) NOT NULL,
    title       VARCHAR(255) NULL,
    subtitle    VARCHAR(500) NULL,
    body        TEXT NULL,
    image       VARCHAR(255) NULL,
    button_text VARCHAR(100) NULL,
    button_url  VARCHAR(255) NULL,
    extra_json  TEXT NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_home_blocks_order (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ---------------------------------------------------------------------------
-- Hizmetler
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS services (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug             VARCHAR(150) NOT NULL,
    title            VARCHAR(200) NOT NULL,
    summary          VARCHAR(500) NULL,
    body             MEDIUMTEXT NULL,
    image            VARCHAR(255) NULL,
    meta_title       VARCHAR(200) NULL,
    meta_description VARCHAR(300) NULL,
    booking_days     VARCHAR(150) NULL,
    booking_hours    VARCHAR(100) NULL,
    booking_place    VARCHAR(150) NULL,
    whatsapp_message VARCHAR(300) NULL,
    sort_order       INT NOT NULL DEFAULT 0,
    is_active        TINYINT(1) NOT NULL DEFAULT 1,
    created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_services_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Bir hizmetin hedef kitleye gore bolumleri (ornek: Kadinlar icin / Erkekler icin). Sayfada sekme olarak gorunur.
CREATE TABLE IF NOT EXISTS service_sections (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    service_id  INT UNSIGNED NOT NULL,
    anchor      VARCHAR(50) NOT NULL,
    tab_label   VARCHAR(100) NOT NULL,
    eyebrow     VARCHAR(150) NULL,
    title       VARCHAR(200) NOT NULL,
    image       VARCHAR(255) NULL,
    body        MEDIUMTEXT NULL,
    focus_title VARCHAR(200) NULL,
    focus_items TEXT NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_sections_anchor (service_id, anchor),
    CONSTRAINT fk_sections_service FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS service_packages (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    service_id  INT UNSIGNED NOT NULL,
    name        VARCHAR(150) NOT NULL,
    price       DECIMAL(10,2) NULL,
    description VARCHAR(500) NULL,
    button_text VARCHAR(100) NULL,
    button_url  VARCHAR(255) NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_packages_service (service_id, sort_order),
    CONSTRAINT fk_packages_service FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ---------------------------------------------------------------------------
-- Egitimler
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS courses (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug             VARCHAR(150) NOT NULL,
    title            VARCHAR(200) NOT NULL,
    summary          VARCHAR(500) NULL,
    body             MEDIUMTEXT NULL,
    image            VARCHAR(255) NULL,
    start_date       DATE NULL,
    countdown_until  DATETIME NULL,
    meta_title       VARCHAR(200) NULL,
    meta_description VARCHAR(300) NULL,
    sort_order       INT NOT NULL DEFAULT 0,
    is_active        TINYINT(1) NOT NULL DEFAULT 1,
    created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_courses_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS course_modules (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    course_id   INT UNSIGNED NOT NULL,
    title       VARCHAR(200) NOT NULL,
    description TEXT NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_modules_course (course_id, sort_order),
    CONSTRAINT fk_modules_course FOREIGN KEY (course_id) REFERENCES courses (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS course_plans (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    course_id   INT UNSIGNED NOT NULL,
    name        VARCHAR(150) NOT NULL,
    list_price  DECIMAL(10,2) NULL,
    price       DECIMAL(10,2) NULL,
    description VARCHAR(500) NULL,
    button_text VARCHAR(100) NULL,
    button_url  VARCHAR(255) NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_plans_course (course_id, sort_order),
    CONSTRAINT fk_plans_course FOREIGN KEY (course_id) REFERENCES courses (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ---------------------------------------------------------------------------
-- Blog
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS post_categories (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    slug       VARCHAR(150) NOT NULL,
    name       VARCHAR(150) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_post_categories_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS posts (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id      INT UNSIGNED NULL,
    slug             VARCHAR(200) NOT NULL,
    title            VARCHAR(255) NOT NULL,
    excerpt          VARCHAR(500) NULL,
    body             MEDIUMTEXT NULL,
    cover_image      VARCHAR(255) NULL,
    meta_title       VARCHAR(200) NULL,
    meta_description VARCHAR(300) NULL,
    status           ENUM('draft','published') NOT NULL DEFAULT 'draft',
    published_at     DATETIME NULL,
    created_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_posts_slug (slug),
    KEY idx_posts_published (status, published_at),
    CONSTRAINT fk_posts_category FOREIGN KEY (category_id) REFERENCES post_categories (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ---------------------------------------------------------------------------
-- Albumler, yorumlar, SSS, sayaclar
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS albums (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title       VARCHAR(200) NOT NULL,
    type        ENUM('album','ep','single') NOT NULL DEFAULT 'album',
    year        SMALLINT UNSIGNED NULL,
    track_count SMALLINT UNSIGNED NULL,
    cover_image VARCHAR(255) NULL,
    listen_url  VARCHAR(255) NULL,
    sort_order  INT NOT NULL DEFAULT 0,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS testimonials (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    service_id INT UNSIGNED NULL,
    name       VARCHAR(150) NOT NULL,
    `text`     TEXT NOT NULL,
    rating     TINYINT UNSIGNED NOT NULL DEFAULT 5,
    sort_order INT NOT NULL DEFAULT 0,
    is_active  TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    CONSTRAINT fk_testimonials_service FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- owner_type: 'service' veya 'course'; owner_id ilgili tablonun id'si
CREATE TABLE IF NOT EXISTS faqs (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    owner_type ENUM('service','course','general') NOT NULL DEFAULT 'general',
    owner_id   INT UNSIGNED NULL,
    question   VARCHAR(500) NOT NULL,
    answer     TEXT NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_faqs_owner (owner_type, owner_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS stats (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    label      VARCHAR(150) NOT NULL,
    `value`    INT UNSIGNED NOT NULL DEFAULT 0,
    suffix     VARCHAR(20) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- ---------------------------------------------------------------------------
-- Formlar
-- ---------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS subscribers (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name            VARCHAR(150) NULL,
    email           VARCHAR(190) NOT NULL,
    token           CHAR(64) NOT NULL,
    consent_at      DATETIME NOT NULL,
    confirmed_at    DATETIME NULL,
    unsubscribed_at DATETIME NULL,
    ip              VARCHAR(45) NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_subscribers_email (email),
    UNIQUE KEY uq_subscribers_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

CREATE TABLE IF NOT EXISTS contact_messages (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    first_name VARCHAR(100) NOT NULL,
    last_name  VARCHAR(100) NULL,
    email      VARCHAR(190) NOT NULL,
    phone      VARCHAR(30) NULL,
    message    TEXT NOT NULL,
    ip         VARCHAR(45) NULL,
    is_read    TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_contact_read (is_read, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

SET FOREIGN_KEY_CHECKS = 1;
