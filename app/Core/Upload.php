<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Panelden gorsel yukleme.
 * - Yalnizca JPEG, PNG, WebP, GIF (icerige bakilarak dogrulanir; uzantiya guvenilmez)
 * - En fazla 8 MB (sunucunun upload_max_filesize ayari daha dusukse o gecerli)
 * - Genisligi 2000 pikselden buyukse kucultulur (GD varsa)
 * - Dosya adi rastgele uretilir: public/uploads/2026/11/a1b2c3d4e5f6.webp
 * - media tablosuna kaydedilir
 * Donen deger site icinde kullanilacak yol: "uploads/2026/11/a1b2c3d4e5f6.webp" (url() ile basilir)
 */
final class Upload
{
    public const MAX_BYTES = 8 * 1024 * 1024;
    public const MAX_WIDTH = 2000;

    private const TYPES = [
        IMAGETYPE_JPEG => ['image/jpeg', 'jpg'],
        IMAGETYPE_PNG  => ['image/png', 'png'],
        IMAGETYPE_WEBP => ['image/webp', 'webp'],
        IMAGETYPE_GIF  => ['image/gif', 'gif'],
    ];

    /** Son hatanin kullaniciya gosterilecek aciklamasi */
    public static string $error = '';

    /** Gercekte gecerli olan sinir: 8 MB ile PHP ayarlarinin kucugu. */
    public static function limitBytes(): int
    {
        $limits = [self::MAX_BYTES];
        foreach (['upload_max_filesize', 'post_max_size'] as $key) {
            $bytes = self::toBytes((string) ini_get($key));
            if ($bytes > 0) {
                $limits[] = $bytes;
            }
        }
        return min($limits);
    }

    public static function limitText(): string
    {
        $mb = self::limitBytes() / (1024 * 1024);
        return rtrim(rtrim(number_format($mb, 1, ',', '.'), '0'), ',') . ' MB';
    }

    private static function toBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }
        $unit   = strtolower(substr($value, -1));
        $number = (int) $value;
        if ($unit === 'g') {
            return $number * 1024 * 1024 * 1024;
        }
        if ($unit === 'm') {
            return $number * 1024 * 1024;
        }
        if ($unit === 'k') {
            return $number * 1024;
        }
        return $number;
    }

    /**
     * $_FILES['alan'] dizisini alir. Dosya secilmemisse null doner ($error bos kalir).
     * Hata varsa null doner ve self::$error doldurulur.
     */
    public static function image(?array $file, string $alt = ''): ?string
    {
        self::$error = '';
        if ($file === null || !isset($file['error']) || is_array($file['error'])) {
            return null;
        }
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            self::$error = 'Dosya çok büyük (en fazla ' . self::limitText() . ').';
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file((string) $file['tmp_name'])) {
            self::$error = 'Dosya yüklenemedi, lütfen yeniden deneyin.';
            return null;
        }
        if ((int) $file['size'] > self::limitBytes()) {
            self::$error = 'Dosya çok büyük (en fazla ' . self::limitText() . ').';
            return null;
        }

        $tmp  = (string) $file['tmp_name'];
        $info = @getimagesize($tmp);
        if ($info === false || !isset(self::TYPES[$info[2]])) {
            self::$error = 'Yalnızca JPG, PNG, WebP veya GIF görsel yüklenebilir.';
            return null;
        }
        [$mime, $ext] = self::TYPES[$info[2]];
        [$width, $height] = [(int) $info[0], (int) $info[1]];

        // Bozuk/sahte gorseller (ornek: icine PHP gomulmus "GIF" dosyasi) burada elenir
        if ($width < 1 || $height < 1 || $width > 12000 || $height > 12000) {
            self::$error = 'Görsel okunamadı ya da ölçüleri geçersiz.';
            return null;
        }
        if (extension_loaded('gd') && !self::openable($tmp, $info[2])) {
            self::$error = 'Görsel okunamadı. Dosya bozuk olabilir; başka bir görsel deneyin.';
            return null;
        }

        $dir = 'uploads/' . date('Y') . '/' . date('m');
        $abs = BASE_PATH . '/public/' . $dir;
        if (!is_dir($abs) && !mkdir($abs, 0775, true) && !is_dir($abs)) {
            self::$error = 'Yükleme klasörü oluşturulamadı (public/uploads yazılabilir olmalı).';
            return null;
        }
        $name = bin2hex(random_bytes(8)) . '.' . $ext;
        $dest = $abs . '/' . $name;

        // Buyuk gorseli kucult; GD yoksa ya da GIF ise oldugu gibi kaydet (GIF animasyonu bozulmasin)
        $resized = false;
        if ($width > self::MAX_WIDTH && $ext !== 'gif' && extension_loaded('gd')) {
            $resized = self::resize($tmp, $dest, $info[2], $width, $height);
            if ($resized) {
                $height = (int) round($height * self::MAX_WIDTH / $width);
                $width  = self::MAX_WIDTH;
            }
        }
        if (!$resized && !move_uploaded_file($tmp, $dest)) {
            self::$error = 'Dosya kaydedilemedi (public/uploads yazılabilir olmalı).';
            return null;
        }

        $path = $dir . '/' . $name;
        $now  = date('Y-m-d H:i:s');
        Database::run(
            'INSERT INTO media (path, alt, mime, size, width, height, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [$path, mb_substr($alt, 0, 255) ?: null, $mime, (int) filesize($dest), $width, $height, $now, $now]
        );
        return $path;
    }

    /** Gorsel gercekten acilabiliyor mu? */
    private static function openable(string $file, int $type): bool
    {
        $image = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($file),
            IMAGETYPE_PNG  => @imagecreatefrompng($file),
            IMAGETYPE_GIF  => @imagecreatefromgif($file),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file) : null,
            default        => null,
        };
        if ($image === null) {
            return true; // bu turu GD acamiyor; getimagesize dogrulamasiyla yetin
        }
        if ($image === false) {
            return false;
        }
        imagedestroy($image);
        return true;
    }

    private static function resize(string $src, string $dest, int $type, int $width, int $height): bool
    {
        $image = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($src),
            IMAGETYPE_PNG  => @imagecreatefrompng($src),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($src) : false,
            default        => false,
        };
        if (!$image) {
            return false;
        }
        $newHeight = (int) round($height * self::MAX_WIDTH / $width);
        $canvas = imagecreatetruecolor(self::MAX_WIDTH, $newHeight);
        if ($type !== IMAGETYPE_JPEG) {
            imagealphablending($canvas, false);
            imagesavealpha($canvas, true);
        }
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, self::MAX_WIDTH, $newHeight, $width, $height);

        $ok = match ($type) {
            IMAGETYPE_JPEG => imagejpeg($canvas, $dest, 85),
            IMAGETYPE_PNG  => imagepng($canvas, $dest, 6),
            IMAGETYPE_WEBP => function_exists('imagewebp') && imagewebp($canvas, $dest, 85),
            default        => false,
        };
        imagedestroy($image);
        imagedestroy($canvas);
        return (bool) $ok;
    }

    /** Site icinde kullanilan bir yukleme yolu mu (uploads/...)? Disaridan gelen yol degerlerini dogrulamak icin. */
    public static function isUploadPath(string $path): bool
    {
        return (bool) preg_match('#^uploads/\d{4}/\d{2}/[a-f0-9]{16}\.(jpg|png|webp|gif)$#', $path);
    }
}
