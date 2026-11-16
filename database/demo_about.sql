-- Hakkimda sayfasi icin ornek icerik (yer tutucu metinler). 003_pages.sql migration'indan SONRA ice aktarilir.
-- year_label serbest metindir: "2012", "2015-2018" ya da "Bugün" gibi.
-- DIKKAT: about_timeline ve about_values kayitlarini silip yeniden yazar.

SET NAMES utf8mb4;

UPDATE pages SET
    template = 'about',
    title = 'Hakkımda',
    body = '<p>Giriş paragrafı buraya gelecek: kim olduğun, bu işe neden gönül verdiğin ve kimlere eşlik ettiğin iki üç cümleyle anlatılacak.</p><p>İkinci paragraf buraya gelecek: çalışma biçimini, nelerden beslendiğini ve danışanların bu yolculukta neler bulabileceğini kısaca tanıtır.</p>',
    meta_description = 'Hakkımda sayfası için kısa açıklama buraya gelecek.'
WHERE slug = 'hakkimda';

DELETE FROM about_timeline;
INSERT INTO about_timeline (year_label, title, `text`, sort_order) VALUES
    ('Başlangıç', 'İlk adım', 'Yolculuğun başladığı dönem buraya gelecek; seni bu alana yönelten deneyim bir iki cümleyle anlatılacak.', 1),
    ('Derinleşme', 'Eğitim yılları', 'Alınan eğitimler ve öğretmenler buraya gelecek; hangi çalışmaların derinleştiği kısaca yazılacak.', 2),
    ('Paylaşma', 'İlk çalışmalar', 'İlk bireysel seanslar ve grup buluşmaları buraya gelecek; nelerin öğrenildiği anlatılacak.', 3),
    ('Bugün', 'Bugünkü çalışmalar', 'Bugün sürdürülen çalışmalar, eğitimler ve topluluk buluşmaları buraya gelecek.', 4);

DELETE FROM about_values;
INSERT INTO about_values (title, `text`, icon, sort_order) VALUES
    ('Nefesle başlamak', 'Bu ilkenin kısa açıklaması buraya gelecek; çalışmalarda nasıl yer aldığı bir iki cümleyle anlatılacak.', 'breath', 1),
    ('Yargısız alan', 'Bu ilkenin kısa açıklaması buraya gelecek; danışanla kurulan ilişkinin nasıl olduğu anlatılacak.', 'heart', 2),
    ('Kendi ritminde', 'Bu ilkenin kısa açıklaması buraya gelecek; sürecin acele etmeden nasıl ilerlediği anlatılacak.', 'moon', 3);

INSERT INTO settings (`key`, `value`) VALUES
    ('about_quote', 'Hakkımda sayfasındaki alıntı metni buraya gelecek.'),
    ('about_quote_author', 'Perihan Ateş')
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
