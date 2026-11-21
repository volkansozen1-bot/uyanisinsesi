-- 008: Sertifikalar sayfasi (/sertifikalar) ve Hakkimda'daki sertifika kartinin varsayilanlari.
-- Tekrar calistirilabilir: var olan sayfaya ve dolu ayarlara dokunmaz.
SET NAMES utf8mb4;

INSERT INTO pages (slug, title, body, status, template, created_at, updated_at)
SELECT 'sertifikalar', 'Sertifikalarım',
       '<p>Aldığım eğitimlere ait sertifikalar. Panelde Sayfalar → Sertifikalarım sayfasının metnine sertifika görsellerini ekleyebilirsiniz.</p>',
       'published', 'default', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM (SELECT id FROM pages WHERE slug = 'sertifikalar') x);

INSERT INTO settings (`key`, `value`, created_at, updated_at)
SELECT 'about_cert_title', 'Sertifikalarım', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM (SELECT id FROM settings WHERE `key` = 'about_cert_title') x);
UPDATE settings SET `value` = 'Sertifikalarım' WHERE `key` = 'about_cert_title' AND `value` = '';
