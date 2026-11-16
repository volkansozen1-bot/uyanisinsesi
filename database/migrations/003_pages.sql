-- 003: Hakkimda zengin sablonu, E-kitaplar menusu
-- Mevcut kuruluma bir kez ice aktarilir (phpMyAdmin > Ice Aktar). MySQL 8 / MariaDB 10.4+

SET NAMES utf8mb4;

-- Sayfa sablonu: 'default' duz metin, 'about' Hakkimda duzeni
ALTER TABLE pages ADD COLUMN template VARCHAR(30) NOT NULL DEFAULT 'default';

-- Hakkimda: "Yolculugum" zaman cizelgesi
CREATE TABLE IF NOT EXISTS about_timeline (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    year_label VARCHAR(50) NOT NULL,
    title      VARCHAR(200) NOT NULL,
    `text`     TEXT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_about_timeline_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Hakkimda: "Yaklasimim" kartlari. icon: breath, heart, moon, circle, leaf
CREATE TABLE IF NOT EXISTS about_values (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title      VARCHAR(200) NOT NULL,
    `text`     TEXT NULL,
    icon       VARCHAR(30) NOT NULL DEFAULT 'circle',
    sort_order INT NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_about_values_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Ana menu: Iletisim sona kayar, E-Kitaplar 7. sirada (yalnizca yoksa eklenir)
UPDATE menu_items SET sort_order = 8
WHERE location = 'header' AND url = '/iletisim' AND parent_id IS NULL;

INSERT INTO menu_items (location, label, url, sort_order)
SELECT 'header', 'E-Kitaplar', '/e-kitaplar', 7 FROM (SELECT 1 AS x) AS tek
WHERE NOT EXISTS (SELECT 1 FROM menu_items WHERE location = 'header' AND url = '/e-kitaplar');

-- Alt menu "Kesfet" listesi
INSERT INTO menu_items (location, label, url, sort_order)
SELECT 'footer_explore', 'E-Kitaplar', '/e-kitaplar', 5 FROM (SELECT 1 AS x) AS tek
WHERE NOT EXISTS (SELECT 1 FROM menu_items WHERE location = 'footer_explore' AND url = '/e-kitaplar');
