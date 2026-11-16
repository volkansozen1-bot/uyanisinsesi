-- Marka adini "Perihan Ateş" yapar. Veritabani daha once kurulduysa bir kez calistirin.
-- Kullanim: phpMyAdmin > uyanisinsesi > Ice aktar > database/marka.sql
SET NAMES utf8mb4;
UPDATE settings SET `value` = 'Perihan Ateş' WHERE `key` = 'site_name';
UPDATE home_blocks SET extra_json = REPLACE(extra_json, 'Uyanışın Sesi', 'Perihan Ateş') WHERE type = 'hero';
