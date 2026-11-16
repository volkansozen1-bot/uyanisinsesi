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

   Tasarımı örnek içerikle dolu görmek için ayrıca `database/demo.sql` dosyasını yükleyin (yer tutucu metinler; ana sayfa bölümlerini, hizmetleri, eğitimi, yorumları ve yazıları yeniden yazar).

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

- Ana sayfa: `home_blocks` tablosundaki bölümler sırayla basılır (hero, karşılama, alıntı, hikâye, hizmetler, eğitim duyurusu ve geri sayım, yorumlar, sayaçlar, son yazılar)
- Hizmetler (`/hizmetler`, `/hizmetler/{slug}`): paketler, yorumlar, SSS
- Eğitimler (`/egitimler`, `/egitimler/{slug}`): modüller, katılım seçenekleri, geri sayım, SSS
- Blog (`/blog`, `/blog/{slug}`, `/blog/kategori/{slug}`): sayfalama ve kategoriler
- Albümler (`/albumler`)
- `pages` tablosundaki sayfalar: `/hakkimda`, yasal metinler
- İletişim formu: doğrulama, CSRF, honeypot; mesajlar `contact_messages` tablosuna yazılır
- Bülten kaydı: açık rıza kutusu; `subscribers` tablosuna yazılır; onay ve çıkış bağlantıları hazır
- Fontlar yerelden sunulur (`public/assets/fonts`, SIL OFL 1.1)

## E-posta

Site e-postaları kendi küçük SMTP istemcisiyle gönderir (`app/Core/Mailer.php`); Composer ya da ek paket gerekmez. PHP'de `openssl` eklentisi açık olmalıdır (XAMPP'ta varsayılan olarak açıktır).

- **Ayarlar:** `.env` içindeki `MAIL_HOST`, `MAIL_PORT`, `MAIL_USER`, `MAIL_PASS`, `MAIL_FROM`, `MAIL_FROM_NAME`. Port 465 ise SSL, diğer portlarda STARTTLS kullanılır; sunucu sertifikası her zaman doğrulanır. Örnek değerler `.env.example` içinde (hosting SMTP, Yandex, Gmail uygulama şifresi).
- **Geliştirme:** `MAIL_HOST` boşsa e-posta gönderilmez; konu, düz metin ve HTML içerik `storage/logs/mail.log` dosyasına yazılır.
- **Hata olursa:** sayfa yine normal tamamlanır (en fazla 10 sn bekler), hata `storage/logs/php-error.log` dosyasına yazılır.
- **Gönderilen e-postalar:**
  - İletişim formu: mesaj kaydedildikten sonra yöneticiye bildirim. Alıcı `settings` tablosundaki `admin_notify_email`, boşsa `contact_email`; ikisi de boşsa gönderilmez. E-postayı yanıtlamak doğrudan ziyaretçiye gider.
  - Bülten: çift onay. Kayıttan sonra `/bulten/onay/{token}` bağlantılı onay e-postası gider (içinde çıkış bağlantısı da vardır). Onay bekleyen adrese en fazla 10 dakikada bir yeniden gönderilir; onaylı adrese gönderilmez.
- **Şablonlar:** `views/emails/` (`layout.php` ortak çerçeve; tablo tabanlı, satır içi CSS). Kodda kullanım:

  ```php
  App\Core\Mailer::send($alici, 'Konu', App\Core\Mailer::render('sablon_adi', ['degisken' => '...']), $yanitAdresi);
  ```

## Sıradaki adımlar

- Yönetim paneli (`/yonetim`)
- E-posta gönderimi (iletişim bildirimi, bülten onay e-postası)
- Ödeme (sanal POS) ve üyelik
- Renk ve fontlar `public/assets/css/site.css` başındaki `:root` değişkenlerinden değiştirilir
