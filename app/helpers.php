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
        return ['url' => asset($presets[substr($path, 7)]['file']), 'type' => 'image/svg+xml'];
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
        'varsayilan' => ['label' => 'Alev', 'file' => 'img/logo-mark.svg'],
        'alev-cizgi' => ['label' => 'Alev (altın)', 'file' => 'img/logos/alev-cizgi.svg'],
        'monogram'   => ['label' => 'Monogram', 'file' => 'img/logos/monogram.svg'],
        'lotus'      => ['label' => 'Lotus', 'file' => 'img/logos/lotus.svg'],
        'gunes'      => ['label' => 'Güneş', 'file' => 'img/logos/gunes.svg'],
    ];
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
