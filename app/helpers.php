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
