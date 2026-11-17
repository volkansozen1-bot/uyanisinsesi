-- Bireysel seans hizmetine "Kadinlar icin" / "Erkekler icin" ornek bolumlerini ve randevu bilgisini ekler.
-- Yalnizca bireysel-seans hizmeti varsa ve bu bolumler henuz yoksa ekler; mevcut icerige dokunmaz.
-- Metinler yer tutucudur; panelden degistirilecek.

INSERT INTO service_sections (service_id, anchor, tab_label, eyebrow, title, body, focus_title, focus_items, sort_order)
SELECT s.id, 'kadin', 'Kadınlar için', 'Kadınlar için seans', 'Kadınlara özel seans başlığı buraya gelecek',
       '<p>Bu bölümün tanıtım metni buraya gelecek. Seansın hangi ihtiyaçlara odaklandığı, nasıl bir alan açtığı birkaç paragrafla anlatılacak.</p><p>İkinci paragraf buraya gelecek.</p>',
       'Birlikte çalışabileceğimiz alanlar',
       'Birinci çalışma alanı
İkinci çalışma alanı
Üçüncü çalışma alanı
Dördüncü çalışma alanı
Beşinci çalışma alanı
Altıncı çalışma alanı',
       1
FROM services s
WHERE s.slug = 'bireysel-seans'
  AND NOT EXISTS (SELECT 1 FROM service_sections x WHERE x.service_id = s.id AND x.anchor = 'kadin');

INSERT INTO service_sections (service_id, anchor, tab_label, eyebrow, title, body, focus_title, focus_items, sort_order)
SELECT s.id, 'erkek', 'Erkekler için', 'Erkekler için seans', 'Erkeklere özel seans başlığı buraya gelecek',
       '<p>Bu bölümün tanıtım metni buraya gelecek. Seansın hangi ihtiyaçlara odaklandığı, nasıl bir alan açtığı birkaç paragrafla anlatılacak.</p><p>İkinci paragraf buraya gelecek.</p>',
       'Birlikte çalışabileceğimiz alanlar',
       'Birinci çalışma alanı
İkinci çalışma alanı
Üçüncü çalışma alanı
Dördüncü çalışma alanı
Beşinci çalışma alanı
Altıncı çalışma alanı',
       2
FROM services s
WHERE s.slug = 'bireysel-seans'
  AND NOT EXISTS (SELECT 1 FROM service_sections x WHERE x.service_id = s.id AND x.anchor = 'erkek');

UPDATE services SET
    booking_days = 'Salı, Cuma ve Cumartesi',
    booking_hours = '15.00 – 18.00',
    booking_place = 'Çevrim içi görüntülü görüşme',
    whatsapp_message = 'Merhaba, bireysel seans için randevu almak istiyorum.'
WHERE slug = 'bireysel-seans'
  AND booking_days IS NULL AND booking_hours IS NULL AND booking_place IS NULL;
