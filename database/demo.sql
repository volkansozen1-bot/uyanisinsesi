-- ORNEK ICERIK: tasarimi dolu haliyle gormek icin yer tutucu metinler.
-- Gercek metinler musteriden gelince panelden degistirilecek.
-- schema.sql ve seed.sql yuklendikten sonra calistirilir; tekrar calistirilabilir.
-- DIKKAT: home_blocks, services, courses, testimonials, posts ve alt menu kayitlarini silip yeniden yazar.
-- Kullanim: phpMyAdmin > uyanisinsesi > Ice aktar > database/demo.sql

SET NAMES utf8mb4;

UPDATE settings SET `value` = 'İçindeki sesi duymak için sakin bir alan.' WHERE `key` = 'site_tagline';

-- Ana sayfa bolumleri -------------------------------------------------------
DELETE FROM home_blocks;
INSERT INTO home_blocks (type, title, subtitle, body, button_text, button_url, extra_json, sort_order, is_active) VALUES
    ('hero', 'İçindeki sesi duymaya hazır mısın?', 'Gürültünün ardında seni bekleyen bir dinginlik var. Onu birlikte dinlemeyi öğrenelim.', NULL, 'Yolculuğa Başla', '/hizmetler', '{"eyebrow":"Uyanışın Sesi","button2_text":"Hikâyemi Oku","button2_url":"/hakkimda"}', 1, 1),
    ('intro', 'Değişim, kendine döndüğün yerde başlar', 'Hoş geldin', 'Burası; durmak, nefes almak ve kendini yeniden duymak isteyenler için açılmış bir alan. Bireysel seanslar, grup çalışmaları ve haftalık buluşmalarla bu yolda birbirimize eşlik ediyoruz.', 'Topluluğa Katıl', '/hizmetler', NULL, 2, 1),
    ('quote', NULL, 'Her uyanış, küçük bir sessizlikle başlar.', NULL, NULL, NULL, NULL, 3, 1),
    ('story', 'Bu yola nasıl çıktım?', 'Hikâyem', 'Buraya kısa bir tanıtım metni gelecek: yolculuğun nasıl başladığı, hangi çalışmalarla derinleştiği ve bugün insanlara nasıl eşlik edildiği birkaç cümleyle anlatılacak.', 'Devamını Oku', '/hakkimda', NULL, 4, 1),
    ('services', 'Birlikte neler yapıyoruz?', 'Hizmetler', NULL, NULL, NULL, NULL, 5, 1),
    ('course_promo', NULL, 'Yeni dönem başlıyor', NULL, 'Eğitimi İncele', NULL, NULL, 6, 1),
    ('testimonials', 'Katılımcılar ne diyor?', 'Geri bildirimler', NULL, NULL, NULL, NULL, 7, 1),
    ('stats', 'Rakamlarla', NULL, NULL, NULL, NULL, NULL, 8, 0),
    ('latest_posts', 'Son yazılar', 'Blog', NULL, NULL, NULL, NULL, 9, 1);

-- Hizmetler -----------------------------------------------------------------
DELETE FROM services;
INSERT INTO services (slug, title, summary, body, sort_order, is_active) VALUES
    ('bireysel-seans', 'Bireysel Seans', 'Sana özel, birebir ilerleyen çevrim içi görüşmeler.', '<p>Hizmet açıklaması buraya gelecek.</p>', 1, 1),
    ('grup-calismalari', 'Grup Çalışmaları', 'Küçük gruplarla nefes, hareket ve farkındalık pratikleri.', '<p>Hizmet açıklaması buraya gelecek.</p>', 2, 1),
    ('aylik-uyelik', 'Aylık Üyelik', 'Her hafta canlı buluşmalar ve süreklilik isteyenler için topluluk.', '<p>Hizmet açıklaması buraya gelecek.</p>', 3, 1);

-- Egitim --------------------------------------------------------------------
DELETE FROM courses;
INSERT INTO courses (slug, title, summary, body, start_date, countdown_until, sort_order, is_active) VALUES
    ('ornek-egitim', 'Örnek Eğitim Programı', 'Eğitimin kısa tanıtımı buraya gelecek: kimler için, kaç hafta sürüyor, sonunda ne kazandırıyor.', '<p>Eğitim açıklaması buraya gelecek.</p>', '2027-01-15', '2027-01-15 20:00:00', 1, 1);

-- Yorumlar (yer tutucu; gercek yorumlar danisan izniyle girilecek) -------------
DELETE FROM testimonials;
INSERT INTO testimonials (name, `text`, rating, sort_order, is_active) VALUES
    ('Örnek Katılımcı', 'Buraya bir katılımcı yorumu gelecek.', 5, 1, 1),
    ('Örnek Katılımcı', 'Buraya bir katılımcı yorumu gelecek.', 5, 2, 1),
    ('Örnek Katılımcı', 'Buraya bir katılımcı yorumu gelecek.', 5, 3, 1);

-- Blog ----------------------------------------------------------------------
DELETE FROM posts;
INSERT INTO posts (slug, title, excerpt, body, status, published_at) VALUES
    ('ornek-yazi-1', 'Örnek yazı başlığı bir', 'Yazının kısa özeti buraya gelecek; bir iki cümleyle konuyu tanıtır.', '<p>Yazı metni buraya gelecek.</p>', 'published', '2026-11-10 10:00:00'),
    ('ornek-yazi-2', 'Örnek yazı başlığı iki', 'Yazının kısa özeti buraya gelecek; bir iki cümleyle konuyu tanıtır.', '<p>Yazı metni buraya gelecek.</p>', 'published', '2026-11-03 10:00:00'),
    ('ornek-yazi-3', 'Örnek yazı başlığı üç', 'Yazının kısa özeti buraya gelecek; bir iki cümleyle konuyu tanıtır.', '<p>Yazı metni buraya gelecek.</p>', 'published', '2026-10-27 10:00:00');

-- Alt menuler ---------------------------------------------------------------
DELETE FROM menu_items WHERE location IN ('footer_explore', 'footer_services');
INSERT INTO menu_items (location, label, url, sort_order) VALUES
    ('footer_explore', 'Ana Sayfa', '/', 1),
    ('footer_explore', 'Hakkımda', '/hakkimda', 2),
    ('footer_explore', 'Blog', '/blog', 3),
    ('footer_explore', 'Albümler', '/albumler', 4),
    ('footer_services', 'Hizmetler', '/hizmetler', 1),
    ('footer_services', 'Eğitimler', '/egitimler', 2),
    ('footer_services', 'İletişim', '/iletisim', 3);
