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

## Sıradaki adımlar

- Yönetim paneli içerik modülleri: ana sayfa, sayfalar, hizmetler, eğitimler, blog, albümler, yorumlar, medya
- E-posta gönderimi (iletişim bildirimi, bülten onay e-postası)
- Ödeme (sanal POS) ve üyelik
- Renk ve fontlar `public/assets/css/site.css` başındaki `:root` değişkenlerinden değiştirilir
