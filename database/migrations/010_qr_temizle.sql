-- 010: QR alanina yanlislikla yuklenen sertifika gorselini temizler; hazir QR kodu geri gelir.
-- Ana sayfa sertifika blogunda yuklenen gorsel de temizlenir.
UPDATE settings SET `value` = '' WHERE `key` = 'about_cert_qr';
UPDATE home_blocks SET image = NULL WHERE type = 'certificates';
