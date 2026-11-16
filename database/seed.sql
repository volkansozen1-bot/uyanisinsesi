-- Baslangic verisi: ornek/yer tutucu icerik. Gercek metinler panelden girilecek.
-- Kullanim: mysql -u KULLANICI -p VERITABANI < database/seed.sql

SET NAMES utf8mb4;

INSERT INTO settings (`key`, `value`) VALUES
    ('site_name', 'Perihan Ateş'),
    ('site_tagline', 'Site sloganı buraya gelecek'),
    ('contact_email', 'bilgi@example.com'),
    ('contact_phone', ''),
    ('whatsapp_number', ''),
    ('instagram_url', ''),
    ('youtube_url', ''),
    ('spotify_url', '')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

INSERT INTO menu_items (location, label, url, sort_order) VALUES
    ('header', 'Ana Sayfa', '/', 1),
    ('header', 'Hakkımda', '/hakkimda', 2),
    ('header', 'Hizmetler', '/hizmetler', 3),
    ('header', 'Eğitimler', '/egitimler', 4),
    ('header', 'Blog', '/blog', 5),
    ('header', 'Albümler', '/albumler', 6),
    ('header', 'İletişim', '/iletisim', 7),
    ('footer_legal', 'KVKK Aydınlatma Metni', '/kvkk-aydinlatma-metni', 1),
    ('footer_legal', 'Gizlilik ve Çerez Politikası', '/gizlilik-ve-cerez-politikasi', 2);

INSERT INTO pages (slug, title, body, status) VALUES
    ('hakkimda', 'Hakkımda', '<p>Hakkımda metni buraya gelecek.</p>', 'published'),
    ('kvkk-aydinlatma-metni', 'KVKK Aydınlatma Metni', '<p>Metin hazırlanıyor.</p>', 'published'),
    ('gizlilik-ve-cerez-politikasi', 'Gizlilik ve Çerez Politikası', '<p>Metin hazırlanıyor.</p>', 'published')
ON DUPLICATE KEY UPDATE title = VALUES(title);

INSERT INTO home_blocks (type, title, subtitle, button_text, button_url, sort_order) VALUES
    ('hero', 'Ana başlık buraya gelecek', 'Kısa tanıtım cümlesi buraya gelecek.', 'Keşfet', '/hizmetler', 1),
    ('quote', NULL, 'Alıntı metni buraya gelecek.', NULL, NULL, 2),
    ('services', 'Hizmetler', NULL, NULL, NULL, 3),
    ('stats', 'Rakamlarla', NULL, NULL, NULL, 4),
    ('latest_posts', 'Son Yazılar', NULL, NULL, NULL, 5);

INSERT INTO stats (label, `value`, suffix, sort_order) VALUES
    ('Yıllık deneyim', 0, '+', 1),
    ('Seans', 0, '+', 2),
    ('Danışan', 0, '+', 3);
