# uyanisinsesi

PHP + MySQL ile yazılmış tanıtım sitesi. Framework yok: tek giriş noktası, küçük bir router, PDO ve PHP şablonları.

## Gereksinimler

- PHP 8.0 veya üzeri (`pdo_mysql`, `mbstring`); canlı site için güncel bir sürüm (8.2+) önerilir
- MySQL 8 veya MariaDB 10.6+
- Apache (`mod_rewrite`) ya da geliştirme için PHP'nin yerleşik sunucusu

## Kurulum

1. Veritabanını oluşturun:

   ```sql
   CREATE DATABASE uyanisinsesi CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;
   ```

2. Tabloları ve başlangıç verisini yükleyin:

   ```bash
   mysql -u root -p uyanisinsesi < database/schema.sql
   mysql -u root -p uyanisinsesi < database/seed.sql
   ```

3. `.env.example` dosyasını `.env` adıyla kopyalayın, veritabanı bilgilerini yazın.

4. Geliştirme sunucusunu başlatın:

   ```bash
   php -S localhost:8000 -t public public/index.php
   ```

   Tarayıcıda `http://localhost:8000` adresini açın.

### XAMPP ile

Projeyi `C:\xampp\htdocs\uyanisinsesi` klasörüne koyun, Apache ve MySQL'i başlatın, 1–3. adımları phpMyAdmin'den yapın ve tarayıcıda şu adresi açın:

```
http://localhost/uyanisinsesi/public/
```

`.env` içinde `APP_URL` boş kalabilir; adres otomatik bulunur. Canlı sitede document root `public/` klasörü olmalı ve `APP_URL` yazılmalıdır.

## Klasör yapısı

```
public/      web kökü: index.php, .htaccess, assets/, uploads/
app/Core/    Router, Database (PDO), View, Csrf, Env
app/Controllers/
app/helpers.php   e(), url(), asset(), setting(), menu(), flash(), slugify()
views/       layouts/, partials/, blocks/ (ana sayfa bölümleri), site/, errors/
config/      config.php (.env'den okur), routes.php
database/    schema.sql (19 tablo), seed.sql
storage/     logs/, cache/
```

## Şu an çalışanlar

- Ana sayfa: `home_blocks` tablosundaki bölümler sırayla basılır (hero, alıntı, hizmetler, sayaçlar, son yazılar)
- `pages` tablosundaki sayfalar: `/hakkimda`, yasal metinler
- İletişim formu: doğrulama, CSRF, honeypot; mesajlar `contact_messages` tablosuna yazılır
- Bülten kaydı: `subscribers` tablosuna yazılır; onay ve çıkış bağlantıları hazır

## Sıradaki adımlar

- Hizmetler, eğitimler, blog ve albümler sayfaları
- Yönetim paneli (`/yonetim`)
- E-posta gönderimi (iletişim bildirimi, bülten onay e-postası)
- Tasarım: renk ve fontlar `public/assets/css/site.css` başındaki `:root` değişkenlerinden değiştirilir
