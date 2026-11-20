-- 006: Ana sayfaya "Tam ekran giris" (hero_full) blogunu ekler, eski "Giris (hero)" blogunu gizler (silmez).
-- Tekrar calistirilabilir: zaten bir hero_full blogu varsa hicbir sey yapmaz.

SET NAMES utf8mb4;

UPDATE home_blocks
SET is_active = 0, updated_at = CURRENT_TIMESTAMP
WHERE type = 'hero'
  AND NOT EXISTS (SELECT 1 FROM (SELECT id FROM home_blocks WHERE type = 'hero_full') x);

INSERT INTO home_blocks (type, title, subtitle, button_text, button_url, sort_order, is_active, created_at, updated_at)
SELECT 'hero_full', 'Perihan Ateş', 'Öze dönüş gerçek şifadır.', 'Yolculuğa başla', '/hizmetler',
       (SELECT COALESCE(MIN(sort_order), 0) - 1 FROM (SELECT sort_order FROM home_blocks) y), 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
FROM DUAL
WHERE NOT EXISTS (SELECT 1 FROM (SELECT id FROM home_blocks WHERE type = 'hero_full') z);
