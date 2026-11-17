<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Panelden gorsel yukleme.
 * - Yalnizca JPEG, PNG, WebP, GIF (icerige bakilarak dogrulanir; uzantiya guvenilmez)
 * - En fazla 8 MB; genisligi 2000 pikselden buyukse kucultulur (GD varsa)
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
            self::$error = 'Dosya çok büyük (en fazla 8 MB).';
            return null;
        }
        if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file((string) $file['tmp_name'])) {
            self::$error = 'Dosya yüklenemedi, lütfen yeniden deneyin.';
            return null;
        }
        if ((int) $file['size'] > self::MAX_BYTES) {
            self::$error = 'Dosya çok büyük (en fazla 8 MB).';
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
