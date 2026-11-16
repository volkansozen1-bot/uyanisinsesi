-- Hizmet sayfasi: hedef kitle bolumleri (sekmeler) ve randevu bilgileri.
-- 16 Kasim 2026'dan once kurulmus veritabanlarinda bir kez calistirin.
SET NAMES utf8mb4;

ALTER TABLE services ADD COLUMN booking_days VARCHAR(150) NULL AFTER meta_description;
ALTER TABLE services ADD COLUMN booking_hours VARCHAR(100) NULL AFTER booking_days;
ALTER TABLE services ADD COLUMN booking_place VARCHAR(150) NULL AFTER booking_hours;
ALTER TABLE services ADD COLUMN whatsapp_message VARCHAR(300) NULL AFTER booking_place;

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
