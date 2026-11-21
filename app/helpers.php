<?php
declare(strict_types=1);

use App\Core\Database;

/** HTML ciktisi icin kacis. Sablonlarda her degisken bununla basilir. */
function e(mixed $value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** config('db.host') gibi noktali anahtarla ayar okur. */
function config(string $key, mixed $default = null): mixed
{
    static $config = null;
    if ($config === null) {
        $config = require BASE_PATH . '/config/config.php';
    }
    $value = $config;
    foreach (explode('.', $key) as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) {
            return $default;
        }
        $value = $value[$part];
    }
    return $value;
}

function url(string $path = '/'): string
{
    // Dis baglantilar (https://, mailto:, tel:) ve sayfa ici capalar oldugu gibi kalir
    if (preg_match('#^(https?:)?//|^(mailto|tel):|^\##i', $path)) {
        return $path;
    }
    return rtrim((string) config('app.url'), '/') . '/' . ltrim($path, '/');
}

/**
 * Istegin site ici yolu: "/", "/iletisim" gibi.
 * Site bir alt klasorde calisiyorsa (ornek: localhost/uyanisinsesi/public) o kisim atilir.
 */
function current_path(): string
{
    $path = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
    $base = rtrim(str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? ''))), '/');
    if ($base !== '' && ($path === $base || str_starts_with($path, $base . '/'))) {
        $path = substr($path, strlen($base));
    }
    if ($path === '/index.php' || str_starts_with($path, '/index.php/')) {
        $path = substr($path, strlen('/index.php'));
    }
    return '/' . trim($path, '/');
}

/** .env icinde APP_URL bos ise site adresi istekten bulunur (ornek: http://localhost/uyanisinsesi/public). */
function detect_base_url(): string
{
    $https  = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $host   = (string) ($_SERVER['HTTP_HOST'] ?? 'localhost');
    $host   = preg_match('/^[a-z0-9.\-]+(:\d+)?$/i', $host) ? $host : 'localhost';
    $base   = rtrim(str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? ''))), '/');
    return ($https ? 'https' : 'http') . '://' . $host . $base;
}

function asset(string $path): string
{
    $file    = BASE_PATH . '/public/assets/' . ltrim($path, '/');
    $version = is_file($file) ? '?v=' . filemtime($file) : '';
    return url('assets/' . ltrim($path, '/')) . $version;
}

/**
 * Site logosu: panelden yuklenmisse o gorsel, yoksa varsayilan logo-mark.svg.
 * @return array{url: string, type: string}
 */
function site_logo(): array
{
    $path = setting('site_logo');
    $presets = logo_presets();
    if (str_starts_with($path, 'preset:') && isset($presets[substr($path, 7)])) {
        $file = $presets[substr($path, 7)]['file'];
        return ['url' => asset($file), 'type' => str_ends_with($file, '.png') ? 'image/png' : 'image/svg+xml'];
    }
    if ($path !== '' && \App\Core\Upload::isUploadPath($path) && is_file(BASE_PATH . '/public/' . $path)) {
        $ext  = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $type = ['jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp', 'gif' => 'image/gif'][$ext] ?? 'image/png';
        return ['url' => url($path), 'type' => $type];
    }
    return ['url' => asset('img/logo-mark.svg'), 'type' => 'image/svg+xml'];
}

/**
 * Panelde secilebilen hazir logolar (public/assets/ altinda).
 * @return array<string, array{label: string, file: string}>
 */
function logo_presets(): array
{
    return [
        'perihan'    => ['label' => 'Perihan Ateş', 'file' => 'img/logos/perihan-amblem.png'],
        'varsayilan' => ['label' => 'Alev', 'file' => 'img/logo-mark.svg'],
        'alev-cizgi' => ['label' => 'Alev (altın)', 'file' => 'img/logos/alev-cizgi.svg'],
        'monogram'   => ['label' => 'Monogram', 'file' => 'img/logos/monogram.svg'],
        'lotus'      => ['label' => 'Lotus', 'file' => 'img/logos/lotus.svg'],
        'gunes'      => ['label' => 'Güneş', 'file' => 'img/logos/gunes.svg'],
        'kelebek'    => ['label' => 'Kelebek', 'file' => 'img/logos/kelebek.svg'],
        'ay'         => ['label' => 'Hilal', 'file' => 'img/logos/ay.svg'],
        'mandala'    => ['label' => 'Mandala', 'file' => 'img/logos/mandala.svg'],
        'agac'       => ['label' => 'Hayat ağacı', 'file' => 'img/logos/agac.svg'],
        'spiral'     => ['label' => 'Spiral', 'file' => 'img/logos/spiral.svg'],
        'goz'        => ['label' => 'Üçüncü göz', 'file' => 'img/logos/goz.svg'],
        'p-alev'     => ['label' => 'P + alev', 'file' => 'img/logos/p-alev.svg'],
        'dalga'      => ['label' => 'Dalga', 'file' => 'img/logos/dalga.svg'],
    ];
}

/**
 * Aktif sosyal medya baglantilari (istek basina bir kez okunur). Tablo yoksa bos dizi.
 * @return array<int, array{platform: string, label: string, url: string, icon: ?string}>
 */
function social_links(): array
{
    static $links = null;
    if ($links === null) {
        try {
            $links = Database::all('SELECT platform, label, url, icon FROM social_links WHERE is_active = 1 ORDER BY sort_order, id');
        } catch (\Throwable $e) {
            $links = [];
        }
    }
    return $links;
}

/** Platform icin sade cizgi ikon (24x24, currentColor). Marka logosu degil, genel bir isaret. */
function social_glyph(string $platform): string
{
    $paths = [
        'instagram'      => '<rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1" fill="currentColor" stroke="none"/>',
        'youtube'        => '<rect x="2.5" y="5.5" width="19" height="13" rx="4"/><path d="M10 9.2v5.6l4.8-2.8z" fill="currentColor" stroke="none"/>',
        'spotify'        => '<circle cx="12" cy="12" r="9"/><path d="M7.5 9.5c3-1 6.500-.8 9.300.8M8 12.600c2.500-.7 5.200-.5 7.400.8M8.600 15.500c1.900-.5 3.900-.3 5.600.6"/>',
        'facebook'       => '<path d="M14.500 8.500h2V5h-2.500A3.500 3.500 0 0 0 10.500 8.500V11H8v3.500h2.500V21H14v-6.500h2.500l.5-3.500h-3V9a.5.5 0 0 1 .5-.5z"/>',
        'x'              => '<path d="M4 4l16 16M20 4L4 20"/>',
        'tiktok'         => '<path d="M14 3v11.500a3.500 3.500 0 1 1-3.500-3.500"/><path d="M14 3c.5 2.500 2.300 4.200 5 4.500"/>',
        'linkedin'       => '<rect x="3.500" y="3.500" width="17" height="17" rx="3"/><path d="M8 10.500V17M8 7.500v.1M12 17v-6.500M12 13.500c0-1.700 1-3 2.500-3s2.500 1 2.500 3V17"/>',
        'telegram'       => '<path d="M21 4L3 11l6 2.500L18 7l-7 8 .5 5 3-3.500 4.500 3.500z"/>',
        'pinterest'      => '<circle cx="12" cy="12" r="9"/><path d="M10.500 20l2-8M10 13.500c-1-3 .5-6 3.500-6 2.500 0 3.500 1.800 3.500 3.500 0 2.500-1.500 4.500-3.500 4.500-1 0-1.800-.6-1.800-1.500"/>',
        'apple_podcasts' => '<rect x="9" y="3" width="6" height="11" rx="3"/><path d="M5.500 11a6.500 6.500 0 0 0 13 0M12 17.500V21"/>',
    ];
    $p = $paths[$platform] ?? '<path d="M10 14a4 4 0 0 0 5.600.4l3-3a4 4 0 0 0-5.600-5.700l-1.200 1.100"/><path d="M14 10a4 4 0 0 0-5.600-.4l-3 3a4 4 0 0 0 5.600 5.700l1.200-1.100"/>';
    return '<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
}

/** Sertifika gorselleri (settings.certificates JSON). @return array<int, array{path: string, title: string}> */
function certificates(): array
{
    $list = json_decode(setting('certificates'), true);
    return is_array($list) ? array_values(array_filter($list, fn ($x) => is_array($x) && \App\Core\Upload::isUploadPath((string) ($x['path'] ?? '')))) : [];
}

/** settings tablosundan deger okur; tum ayarlar istek basina bir kez cekilir. */
function setting(string $key, string $default = ''): string
{
    static $settings = null;
    if ($settings === null) {
        $settings = [];
        foreach (Database::all('SELECT `key`, `value` FROM settings') as $row) {
            $settings[$row['key']] = (string) $row['value'];
        }
    }
    return ($settings[$key] ?? '') !== '' ? $settings[$key] : $default;
}

/** @return array<int, array<string, mixed>> */
function menu(string $location): array
{
    return Database::all(
        'SELECT label, url FROM menu_items WHERE location = ? AND is_active = 1 AND parent_id IS NULL ORDER BY sort_order, id',
        [$location]
    );
}

function redirect(string $path): void
{
    header('Location: ' . url($path), true, 302);
    exit;
}

/** Bir sonraki istekte bir kez gosterilecek mesaj. */
function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['_flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);
    return $value;
}

function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
}

/** Turkce karakterleri de ceviren URL kisaltmasi: "Gölge Nedir?" -> "golge-nedir" */
function slugify(string $text): string
{
    $map  = ['ç' => 'c', 'ğ' => 'g', 'ı' => 'i', 'ö' => 'o', 'ş' => 's', 'ü' => 'u',
             'Ç' => 'c', 'Ğ' => 'g', 'İ' => 'i', 'I' => 'i', 'Ö' => 'o', 'Ş' => 's', 'Ü' => 'u'];
    $text = mb_strtolower(strtr($text, $map));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text) ?? '';
    return trim($text, '-');
}

/** "2026-11-10 10:00:00" -> "10 Kasım 2026" */
function tr_date(?string $datetime): string
{
    $time = $datetime ? strtotime($datetime) : false;
    if ($time === false) {
        return '';
    }
    $months = [1 => 'Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];
    return (int) date('j', $time) . ' ' . $months[(int) date('n', $time)] . ' ' . date('Y', $time);
}

/** 7500.00 -> "7.500 TL" */
function tr_price($amount): string
{
    $amount = (float) $amount;
    $decimals = fmod($amount, 1.0) === 0.0 ? 0 : 2;
    return number_format($amount, $decimals, ',', '.') . ' TL';
}

/**
 * E-postalardaki mutlak baglantilar icin guvenilir site adresi.
 * APP_URL doluysa o kullanilir. Bossa Host basligi saldirgan kontrolunde olabilecegi icin
 * yalnizca yerel gelistirmede (localhost / 127.0.0.1 / ::1) istekten bulunur; aksi halde null.
 */
function trusted_base_url(): ?string
{
    $configured = trim((string) App\Core\Env::get('APP_URL', ''));
    if ($configured !== '') {
        return rtrim($configured, '/');
    }
    $host = strtolower((string) ($_SERVER['HTTP_HOST'] ?? ''));
    if (preg_match('/^(localhost|127\.0\.0\.1|\[::1\])(:\d{1,5})?$/', $host) || $host === '::1') {
        return rtrim(detect_base_url(), '/');
    }
    return null;
}

/** trusted_base_url() + yol; guvenilir adres yoksa null. */
function trusted_url(string $path = '/'): ?string
{
    $base = trusted_base_url();
    return $base === null ? null : $base . '/' . ltrim($path, '/');
}

/** $_POST/$_GET degerini metin olarak okur; dizi gibi beklenmeyen tipler bos metin sayilir. */
function input_string(array $src, string $key): string
{
    $value = $src[$key] ?? '';
    return is_string($value) ? $value : '';
}
