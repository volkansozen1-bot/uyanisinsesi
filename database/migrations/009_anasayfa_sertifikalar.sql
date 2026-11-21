-- 009: Ana sayfanin sonuna "Sertifikalar (QR kodlu)" blogu. Zaten varsa bir sey yapmaz.
SET NAMES utf8mb4;
INSERT INTO home_blocks (type, title, subtitle, sort_order, is_active, created_at, updated_at)
SELECT 'certificates', 'Sertifikalarım', 'Aldığım eğitimlerin sertifikalarına QR kodu okutarak ya da butona tıklayarak ulaşabilirsiniz.',
       (SELECT COALESCE(MAX(sort_order), 0) + 1 FROM (SELECT sort_order FROM home_blocks) y), 1, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM (SELECT id FROM home_blocks WHERE type = 'certificates') x);
