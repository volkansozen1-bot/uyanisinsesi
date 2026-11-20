-- 005: Sosyal medya baglantilari (social_links)
-- Mevcut kuruluma bir kez ice aktarilir (phpMyAdmin > Ice Aktar). MySQL 8 / MariaDB 10.4+
-- Ayarlar sayfasindaki instagram_url / youtube_url / spotify_url degerleri buraya tasinir.
-- Tekrar calistirilabilir: ayni platform zaten varsa yeniden eklenmez. settings satirlari silinmez.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS social_links (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    platform   VARCHAR(30) NOT NULL,
    label      VARCHAR(60) NOT NULL,
    url        VARCHAR(255) NOT NULL,
    icon       VARCHAR(255) NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active  TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_social_links_order (is_active, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

INSERT INTO social_links (platform, label, url, sort_order, is_active, created_at, updated_at)
SELECT 'instagram', 'Instagram', s.`value`, 1, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
FROM settings s
WHERE s.`key` = 'instagram_url' AND s.`value` IS NOT NULL AND s.`value` <> ''
  AND NOT EXISTS (SELECT 1 FROM social_links x WHERE x.platform = 'instagram');

INSERT INTO social_links (platform, label, url, sort_order, is_active, created_at, updated_at)
SELECT 'youtube', 'YouTube', s.`value`, 2, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
FROM settings s
WHERE s.`key` = 'youtube_url' AND s.`value` IS NOT NULL AND s.`value` <> ''
  AND NOT EXISTS (SELECT 1 FROM social_links x WHERE x.platform = 'youtube');

INSERT INTO social_links (platform, label, url, sort_order, is_active, created_at, updated_at)
SELECT 'spotify', 'Spotify', s.`value`, 3, 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
FROM settings s
WHERE s.`key` = 'spotify_url' AND s.`value` IS NOT NULL AND s.`value` <> ''
  AND NOT EXISTS (SELECT 1 FROM social_links x WHERE x.platform = 'spotify');
