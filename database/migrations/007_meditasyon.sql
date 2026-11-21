-- 007: "Albümler" menu ve baslik adlari "Meditasyon" oldu (adres /albumler ayni kalir).
SET NAMES utf8mb4;
UPDATE menu_items SET label = 'Meditasyon' WHERE label = 'Albümler';
UPDATE home_blocks SET title = 'Meditasyon' WHERE title = 'Albümler';
