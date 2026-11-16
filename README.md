# uyanisinsesi

PHP + MySQL ile yazılmış tanıtım sitesi. Framework yok: tek giriş noktası, küçük bir router, PDO ve PHP şablonları.

## Gereksinimler

- PHP 8.0 veya üzeri (`pdo_mysql`, `mbstring`); canlı site için güncel bir sürüm (8.2+) önerilir
- MySQL 8 veya MariaDB 10.4+ (XAMPP 8.0 ile gelen sürüm)
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

`.env` içinde `APP_URL` boş kalabilir; adres otomatik bulunur. Proje kökündeki `.htaccess` yalnızca `public/` klasörünü dışarıya açar; `.env`, loglar ve kod tarayıcıdan indirilemez.

### Mevcut veritabanını güncelleme

16 Kasım 2026'dan önce kurulmuş bir veritabanını güncellemek için phpMyAdmin'de şu dosyaları **sırayla ve her birini bir kez** içe aktarın:

1. `database/migrations/001_service_sections.sql`
2. `database/migrations/002_admin.sql`
3. `database/migrations/003_pages.sql`
4. İsteğe bağlı örnek içerik: `database/demo.sql`, ardından `database/demo_about.sql`

001 ve 003 sütun eklediği için ikinci kez yüklenince "Duplicate column" hatası verir; bu, dosyanın zaten yüklendiği anlamına gelir. Yeni kurulumda (`schema.sql` ile) migration'lara gerek yoktur. Eksik bir şey olup olmadığını yerelde `public/kontrol.php` sayfası gösterir.

## Canlıya alma (Plesk / cPanel / DirectAdmin)

- Sitenin kök klasörü (document root) `public/` olmalı. Plesk: Barındırma Ayarları → Belge kök dizini → `httpdocs/public`.
- PHP 8.0+ (8.2 önerilir), `pdo_mysql` ve `mbstring` açık; SSL etkin.
- Sunucudaki `.env` dosyasında **`APP_URL` mutlaka yazılmalı** (örnek: `APP_URL=https://perihanates.com`) ve `APP_DEBUG=false` olmalı. `APP_URL` boşsa güvenlik nedeniyle e-posta gönderilmez.
- `public/kontrol.php` canlıda yalnızca sunucunun kendisinden açılır; yine de canlıya yüklemeye gerek yoktur.
- **İlk yönetici:** `/yonetim/kurulum` sayfası yalnızca yerelden açılır. Canlıda SSH varsa `php bin/create-admin.php "Ad Soyad" eposta@adres`; SSH yoksa sunucudaki `.env` dosyasına en az 20 karakterlik rastgele bir `SETUP_KEY=...` satırı ekleyin; `/yonetim/kurulum` sayfası bu anahtarı isteyerek açılır. İlk yönetici oluşunca sayfa kapanır; ardından `SETUP_KEY` satırını silin.
- **Git ile dağıtım (Plesk):** Alan adı → Git → Uzak depo: `https://github.com/volkansozen1-bot/uyanisinsesi.git` (private ise SSH adresi + GitHub "Deploy key"). Dağıtım yolu `httpdocs`. Önce elle dağıtım; `.env`, `storage/logs` ve `public/uploads` repoda olmadığı için sunucuda korunur. Veritabanı değişiklikleri Git ile gelmez: migration varsa phpMyAdmin'den ayrıca içe aktarılır.

## Otomatik yayın (GitHub → FTP)

`main` dalına her gönderimde `.github/workflows/deploy.yml` siteyi FTP ile sunucuya yükler. Panel gerektirmez.

1. GitHub → repo → **Settings → Secrets and variables → Actions**
   - **Secrets** sekmesi: `FTP_SERVER` (örn. `ftp.perihanates.com`), `FTP_USERNAME`, `FTP_PASSWORD`
   - **Variables** sekmesi: `FTP_SERVER_DIR` — projenin sunucudaki klasörü, sonu `/` ile (örn. `/public_html/` ya da `/httpdocs/`; içinde `public/` klasörü olan yer). İsteğe bağlı `FTP_PROTOCOL` (`ftps` varsayılan; sunucu desteklemiyorsa `ftp`).
2. **Actions** sekmesi → "Siteyi yayinla" → **Run workflow** ile ilk yüklemeyi elle başlatın ve sonucu kontrol edin.
3. Sorunsuzsa Variables'a `DEPLOY_ENABLED` = `true` ekleyin; bundan sonra her gönderim otomatik yayınlanır.

`.env`, `storage/logs`, `public/uploads` ve `public/kontrol.php` gönderilmez, sunucudakiler korunur.

**Veritabanı güncellemeleri de otomatik:** sunucudaki `.env` dosyasına en az 32 karakterlik rastgele bir `DEPLOY_TOKEN=...` yazın; aynı değeri GitHub'da **Secrets**'a `DEPLOY_TOKEN` olarak, sitenin adresini (`https://perihanates.com`) **Variables**'a `SITE_URL` olarak ekleyin. Yüklemeden sonra iş akışı `database/migrations/` altındaki yeni dosyaları bir kez çalıştırır (`schema_migrations` tablosunda takip edilir). Elle yüklenmiş ya da zaten var olan değişiklikler atlanır. Yerelde aynı işi `php bin/migrate.php` yapar. Sunucudaki dosyaları elle değiştirmeyin, sonraki yayında üzerine yazılır.

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

## Yönetim paneli

Adres: `/yonetim` (XAMPP'ta `http://localhost/uyanisinsesi/public/yonetim`). Arama motorlarına kapalıdır (`noindex`).

### Kurulum

1. Mevcut bir veritabanına `database/migrations/002_admin.sql` dosyasını içe aktarın (giriş denemeleri tablosu ve bildirim e-postası ayarı).
2. İlk yöneticiyi oluşturun. İki yol var:
   - **Komut satırı:**

     ```bash
     php bin/create-admin.php "Ad Soyad" eposta@ornek.com
     ```

     XAMPP (Windows), proje klasöründeyken:

     ```
     C:\xampp\php\php.exe bin\create-admin.php "Ad Soyad" eposta@ornek.com
     ```

     Parola sorulur (en az 10 karakter). Windows komut isteminde yazılan parola ekranda görünür. Aynı e-postayla yeniden çalıştırılırsa parolayı yeniler; parola unutulduğunda da bu yol kullanılır.
   - **Tarayıcı:** henüz hiç yönetici yoksa `/yonetim/kurulum` sayfası ilk hesabı oluşturur. Bu sayfa yalnızca siteyi çalıştıran bilgisayardan (`localhost`) açıldığında çalışır; bir yönetici oluşturulunca kapanır (404).
3. `/yonetim/giris` adresinden giriş yapın.

### Güvenlik

- Aynı IP ya da e-posta için 15 dakikada 5 hatalı denemeden sonra giriş 15 dakika kapanır (`login_attempts` tablosu).
- Oturum 2 saat işlem yapılmazsa kapanır. Parola değiştirilince diğer cihazlardaki oturumlar düşer.
- Tüm formlar CSRF korumalıdır.

### Modüller

Panel özeti, gelen mesajlar, bülten aboneleri (arama, CSV dışa aktarım, KVKK silme), ayarlar, parola değiştirme. Hizmet, eğitim, blog gibi içerik modülleri sonraki adımda eklenecek; menüde "yakında" olarak görünürler.

Yeni modül eklemek için:

1. `app/Controllers/Admin/` altında `AdminController`'dan türeyen bir sınıf yazın (giriş kontrolü yapıcıda otomatik yapılır; `$this->render('klasor/sablon', [...])`, `$this->verifyCsrf('/geri/donus')`, `$this->paginate($toplam)` hazır).
2. Rotaları `config/routes/admin.php` dosyasına ekleyin.
3. Menüye `config/admin_menu.php` içinde tek satır ekleyin (`url` değeri `null` olan öğeler pasif görünür).

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

- Yönetim paneli içerik modülleri: ana sayfa, sayfalar, hizmetler, eğitimler, blog, albümler, yorumlar, medya
- Ödeme (sanal POS) ve üyelik
- Renk ve fontlar `public/assets/css/site.css` başındaki `:root` değişkenlerinden değiştirilir
