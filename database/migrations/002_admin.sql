-- 002_admin: yonetim paneli
-- Giris denemeleri (kaba kuvvet korumasi) ve iletisim formu bildirim adresi ayari.
-- Mevcut kuruluma phpMyAdmin'den ice aktarilir; tekrar calistirmak zararsizdir.

SET NAMES utf8mb4;

-- Yalnizca hatali denemeler yazilir; basarili giristen sonra o e-posta ve IP icin silinir.
CREATE TABLE IF NOT EXISTS login_attempts (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    email        VARCHAR(190) NOT NULL,
    ip           VARCHAR(45) NOT NULL,
    attempted_at DATETIME NOT NULL,
    created_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_login_attempts_ip (ip, attempted_at),
    KEY idx_login_attempts_email (email, attempted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- Iletisim formu bildirimlerinin gidecegi adres (bos ise contact_email kullanilir)
INSERT INTO settings (`key`, `value`)
SELECT 'admin_notify_email', '' WHERE NOT EXISTS (SELECT 1 FROM settings WHERE `key` = 'admin_notify_email');
