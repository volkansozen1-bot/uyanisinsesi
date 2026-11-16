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
    ('hero', 'İçindeki sesi duymaya hazır mısın?', 'Gürültünün ardında seni bekleyen bir dinginlik var. Onu birlikte dinlemeyi öğrenelim.', NULL, 'Yolculuğa Başla', '/hizmetler', '{"eyebrow":"Perihan Ateş","button2_text":"Hikâyemi Oku","button2_url":"/hakkimda","keywords":["Nefes","Farkındalık","Sessizlik","Dönüşüm","Beden","Topluluk"],"note_title":"Haftalık buluşmalar","note_text":"Her hafta çevrim içi, canlı ve birlikte."}', 1, 1),
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
    ('footer_explore', 'E-Kitaplar', '/e-kitaplar', 5),
    ('footer_services', 'Hizmetler', '/hizmetler', 1),
    ('footer_services', 'Eğitimler', '/egitimler', 2),
    ('footer_services', 'İletişim', '/iletisim', 3);

-- Hizmet paketleri (fiyatlar bos: gercek fiyatlar panelden girilecek) -------------
INSERT INTO service_packages (service_id, name, price, description, button_text, button_url, sort_order)
SELECT id, 'Tek Seans', NULL, 'Tek bir konuya odaklanan görüşme.', 'Bilgi Al', '/iletisim', 1 FROM services WHERE slug = 'bireysel-seans';
INSERT INTO service_packages (service_id, name, price, description, button_text, button_url, sort_order)
SELECT id, '3 Seanslık Paket', NULL, 'Yaklaşık bir aya yayılan kısa süreç.', 'Bilgi Al', '/iletisim', 2 FROM services WHERE slug = 'bireysel-seans';
INSERT INTO service_packages (service_id, name, price, description, button_text, button_url, sort_order)
SELECT id, '6 Seanslık Paket', NULL, 'Daha derin çalışmak isteyenler için.', 'Bilgi Al', '/iletisim', 3 FROM services WHERE slug = 'bireysel-seans';
INSERT INTO service_packages (service_id, name, price, description, button_text, button_url, sort_order)
SELECT id, 'Aylık Üyelik', NULL, 'Haftalık canlı buluşmaların tamamı.', 'Bilgi Al', '/iletisim', 1 FROM services WHERE slug = 'aylik-uyelik';

-- Sik sorulan sorular -------------------------------------------------------
DELETE FROM faqs;
INSERT INTO faqs (owner_type, owner_id, question, answer, sort_order)
SELECT 'service', id, 'Seanslar nasıl yapılıyor?', 'Cevap metni buraya gelecek.', 1 FROM services WHERE slug = 'bireysel-seans';
INSERT INTO faqs (owner_type, owner_id, question, answer, sort_order)
SELECT 'service', id, 'Bir seans ne kadar sürüyor?', 'Cevap metni buraya gelecek.', 2 FROM services WHERE slug = 'bireysel-seans';
INSERT INTO faqs (owner_type, owner_id, question, answer, sort_order)
SELECT 'service', id, 'Randevumu değiştirebilir miyim?', 'Cevap metni buraya gelecek.', 3 FROM services WHERE slug = 'bireysel-seans';
INSERT INTO faqs (owner_type, owner_id, question, answer, sort_order)
SELECT 'course', id, 'Eğitim canlı mı, kayıttan mı?', 'Cevap metni buraya gelecek.', 1 FROM courses WHERE slug = 'ornek-egitim';
INSERT INTO faqs (owner_type, owner_id, question, answer, sort_order)
SELECT 'course', id, 'Katılım belgesi veriliyor mu?', 'Cevap metni buraya gelecek.', 2 FROM courses WHERE slug = 'ornek-egitim';

-- Egitim modulleri ve planlari ---------------------------------------------
INSERT INTO course_modules (course_id, title, description, sort_order)
SELECT id, 'Birinci modül başlığı', 'Modülün kısa açıklaması buraya gelecek.', 1 FROM courses WHERE slug = 'ornek-egitim';
INSERT INTO course_modules (course_id, title, description, sort_order)
SELECT id, 'İkinci modül başlığı', 'Modülün kısa açıklaması buraya gelecek.', 2 FROM courses WHERE slug = 'ornek-egitim';
INSERT INTO course_modules (course_id, title, description, sort_order)
SELECT id, 'Üçüncü modül başlığı', 'Modülün kısa açıklaması buraya gelecek.', 3 FROM courses WHERE slug = 'ornek-egitim';
INSERT INTO course_modules (course_id, title, description, sort_order)
SELECT id, 'Dördüncü modül başlığı', 'Modülün kısa açıklaması buraya gelecek.', 4 FROM courses WHERE slug = 'ornek-egitim';
INSERT INTO course_plans (course_id, name, list_price, price, description, button_text, button_url, sort_order)
SELECT id, 'Katılımcı', NULL, NULL, 'Tüm modüllere katılım.', 'Kayıt İçin Yaz', '/iletisim', 1 FROM courses WHERE slug = 'ornek-egitim';
INSERT INTO course_plans (course_id, name, list_price, price, description, button_text, button_url, sort_order)
SELECT id, 'Katılımcı + Birebir Görüşme', NULL, NULL, 'Tüm modüller ve ek bireysel görüşmeler.', 'Kayıt İçin Yaz', '/iletisim', 2 FROM courses WHERE slug = 'ornek-egitim';

-- Albumler ------------------------------------------------------------------
DELETE FROM albums;
INSERT INTO albums (title, type, year, track_count, listen_url, sort_order) VALUES
    ('Örnek Albüm', 'album', 2026, 9, NULL, 1),
    ('Örnek EP', 'ep', 2025, 4, NULL, 2),
    ('Örnek Single', 'single', 2025, 1, NULL, 3);

-- Bireysel seans: hedef kitle sekmeleri ve randevu bilgisi ------------------
UPDATE services SET
    body = '<p>Seansların genel tanıtımı buraya gelecek: kimler için uygun, bir görüşme nasıl ilerler, ne beklenmeli.</p>',
    booking_days = 'Salı, Cuma ve Cumartesi',
    booking_hours = '15.00 – 18.00',
    booking_place = 'Çevrim içi görüntülü görüşme',
    whatsapp_message = 'Merhaba, bireysel seans için randevu almak istiyorum.'
WHERE slug = 'bireysel-seans';

DELETE FROM service_sections;
INSERT INTO service_sections (service_id, anchor, tab_label, eyebrow, title, body, focus_title, focus_items, sort_order)
SELECT id, 'kadin', 'Kadınlar için', 'Kadınlar için seans', 'Kadınlara özel seans başlığı buraya gelecek',
       '<p>Bu bölümün tanıtım metni buraya gelecek. Seansın hangi ihtiyaçlara odaklandığı, nasıl bir alan açtığı birkaç paragrafla anlatılacak.</p><p>İkinci paragraf buraya gelecek.</p>',
       'Birlikte çalışabileceğimiz alanlar',
       'Birinci çalışma alanı
İkinci çalışma alanı
Üçüncü çalışma alanı
Dördüncü çalışma alanı
Beşinci çalışma alanı
Altıncı çalışma alanı',
       1 FROM services WHERE slug = 'bireysel-seans';
INSERT INTO service_sections (service_id, anchor, tab_label, eyebrow, title, body, focus_title, focus_items, sort_order)
SELECT id, 'erkek', 'Erkekler için', 'Erkekler için seans', 'Erkeklere özel seans başlığı buraya gelecek',
       '<p>Bu bölümün tanıtım metni buraya gelecek. Seansın hangi ihtiyaçlara odaklandığı, nasıl bir alan açtığı birkaç paragrafla anlatılacak.</p><p>İkinci paragraf buraya gelecek.</p>',
       'Birlikte çalışabileceğimiz alanlar',
       'Birinci çalışma alanı
İkinci çalışma alanı
Üçüncü çalışma alanı
Dördüncü çalışma alanı
Beşinci çalışma alanı
Altıncı çalışma alanı',
       2 FROM services WHERE slug = 'bireysel-seans';
