<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\View;

/**
 * Tum yonetim sayfalarinin temeli. Yapici Auth::require() cagirir;
 * bu siniftan tureyen her controller giris olmadan calismaz.
 */
abstract class AdminController
{
    public const PER_PAGE = 20;

    /** @var array<string, mixed> */
    protected array $admin;

    public function __construct()
    {
        self::sendHeaders();
        $this->admin = Auth::require();
    }

    /** Yonetim sayfalari onbellege alinmaz, cerceve icinde acilmaz. */
    public static function sendHeaders(): void
    {
        header('Cache-Control: no-store, private');
        header('X-Frame-Options: DENY');
        header('X-Robots-Tag: noindex, nofollow');
        header('Referrer-Policy: same-origin');
    }

    /** @param array<string, mixed> $data */
    protected function render(string $template, array $data = []): void
    {
        View::render('admin/' . $template, $data + ['admin' => $this->admin], 'layouts/admin');
    }

    /** CSRF hataliysa mesajla geri yollar. */
    protected function verifyCsrf(string $back): void
    {
        if (!Csrf::verify()) {
            flash('admin_error', 'Oturum süresi doldu ya da form geçersiz. Lütfen yeniden deneyin.');
            redirect($back);
        }
    }

    /**
     * $_GET degerini metin olarak okur. "?q[]=x" gibi dizi gelirse varsayilan doner
     * (boylece "Array to string conversion" uyarisi cikmaz).
     */
    protected function query(string $key, string $default = ''): string
    {
        $value = $_GET[$key] ?? null;
        return is_string($value) ? $value : $default;
    }

    /** $_POST degerini metin olarak okur; dizi ya da eksikse varsayilan doner. */
    protected function post(string $key, string $default = ''): string
    {
        $value = $_POST[$key] ?? null;
        return is_string($value) ? $value : $default;
    }

    /** "?sayfa=3" -> 3 */
    protected function currentPage(): int
    {
        $page = filter_var($this->query('sayfa', '1'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        return $page === false ? 1 : $page;
    }

    /**
     * Sayfalama bilgisi: ['page', 'pages', 'total', 'offset', 'limit']
     * @return array<string, int>
     */
    protected function paginate(int $total, int $perPage = self::PER_PAGE): array
    {
        $pages = max(1, (int) ceil($total / $perPage));
        $page  = min($this->currentPage(), $pages);
        return [
            'page'   => $page,
            'pages'  => $pages,
            'total'  => $total,
            'limit'  => $perPage,
            'offset' => ($page - 1) * $perPage,
        ];
    }

    protected function count(string $sql, array $params = []): int
    {
        $row = Database::one($sql, $params);
        return (int) ($row ? reset($row) : 0);
    }

    // ------------------------------------------------------------------
    // Icerik modulleri icin ortak yardimcilar (Hizmetler, Blog vb.)
    // Ornek kullanim: TestimonialController
    // ------------------------------------------------------------------

    /**
     * Hatali form gonderiminden sonra geri donuldugunde eski degerler ve hatalar.
     * @param array<string, mixed> $defaults veritabanindaki mevcut degerler
     * @return array{0: array<string, mixed>, 1: array<string, string>}
     */
    protected function formState(array $defaults = []): array
    {
        $old    = $_SESSION['_old'] ?? [];
        $errors = $_SESSION['_errors'] ?? [];
        unset($_SESSION['_old'], $_SESSION['_errors']);
        return [(is_array($old) ? $old : []) + $defaults, is_array($errors) ? $errors : []];
    }

    /**
     * Hatalari ve girilen degerleri saklayip forma geri yollar.
     * @param array<string, string> $errors
     * @param array<string, mixed>  $input
     */
    protected function backWithErrors(string $back, array $errors, array $input): void
    {
        $_SESSION['_errors'] = $errors;
        $_SESSION['_old']    = $input;
        flash('admin_error', 'Kaydedilmedi. İşaretli alanları düzeltin.');
        redirect($back);
    }

    /** Bos olmayan, kirpilmis metin; en fazla $max karakter (asarsa hata icin null). */
    protected function postText(string $key, int $max = 255): ?string
    {
        $value = trim($this->post($key));
        return mb_strlen($value) > $max ? null : $value;
    }

    protected function postBool(string $key): int
    {
        return in_array($this->post($key), ['1', 'on', 'true'], true) ? 1 : 0;
    }

    protected function postInt(string $key, int $default = 0): int
    {
        $v = filter_var($this->post($key), FILTER_VALIDATE_INT);
        return $v === false ? $default : $v;
    }

    /**
     * Fiyat: "7500", "7.500", "7.500,50", "7500.5" kabul edilir. Bos ise null.
     * Hatali ise false.
     * @return string|null|false
     */
    protected function postPrice(string $key)
    {
        $raw = str_replace([' ', 'TL', '₺'], '', trim($this->post($key)));
        if ($raw === '') {
            return null;
        }
        if (preg_match('/^\d{1,3}(\.\d{3})+(,\d{1,2})?$/', $raw)) {
            $raw = str_replace(['.', ','], ['', '.'], $raw);   // 7.500,50 -> 7500.50
        } else {
            $raw = str_replace(',', '.', $raw);                 // 7500,5 -> 7500.5
        }
        if (!preg_match('/^\d{1,8}(\.\d{1,2})?$/', $raw)) {
            return false;
        }
        return number_format((float) $raw, 2, '.', '');
    }

    /** "2026-11-17" ya da "2026-11-17T14:30" -> veritabani bicimi; bos ise null, hatali ise false. @return string|null|false */
    protected function postDate(string $key, bool $withTime = false)
    {
        $raw = trim($this->post($key));
        if ($raw === '') {
            return null;
        }
        $format = $withTime ? 'Y-m-d\TH:i' : 'Y-m-d';
        $dt = \DateTime::createFromFormat('!' . $format, $raw);
        if ($dt === false || $dt->format($format) !== $raw) {
            return false;
        }
        return $dt->format($withTime ? 'Y-m-d H:i:s' : 'Y-m-d');
    }

    /** Site ici yol ya da https adresi (buton baglantilari icin). Bos ise ''. Hatali ise null. */
    protected function postLink(string $key): ?string
    {
        $v = trim($this->post($key));
        if ($v === '') {
            return '';
        }
        if (mb_strlen($v) > 255) {
            return null;
        }
        if (preg_match('#^/(?!/)[^\s<>"]*$#', $v) || preg_match('#^https://[^\s<>"]+$#i', $v)) {
            return $v;
        }
        return null;
    }

    /** Zengin metin alanini temizleyerek okur. */
    protected function postHtml(string $key): string
    {
        return \App\Core\HtmlSanitizer::clean($this->post($key));
    }

    /**
     * Gorsel alani: yeni dosya yuklendiyse onun yolu, "kaldir" isaretliyse null, aksi halde mevcut yol.
     * Hata olursa $errors[$name] doldurulur ve mevcut yol korunur.
     * @param array<string, string> $errors
     */
    protected function imageField(string $name, ?string $current, array &$errors, string $alt = ''): ?string
    {
        $file = $_FILES[$name] ?? null;
        $path = \App\Core\Upload::image(is_array($file) ? $file : null, $alt);
        if ($path !== null) {
            return $path;
        }
        if (\App\Core\Upload::$error !== '') {
            $errors[$name] = \App\Core\Upload::$error;
            return $current;
        }
        if ($this->post($name . '_remove') === '1') {
            return null;
        }
        return $current;
    }

    /**
     * Adres kisaltmasi: bos ise basliktan uretir, tabloda benzersiz yapar (sonuna -2, -3 ekler).
     * Gecersiz karakter iceren elle girilmis degerler de slugify'dan gecer.
     */
    protected function uniqueSlug(string $table, string $slug, string $fallback, int $exceptId = 0): string
    {
        $base = slugify($slug !== '' ? $slug : $fallback);
        if ($base === '') {
            $base = 'icerik';
        }
        $base = substr($base, 0, 140);
        $candidate = $base;
        for ($i = 2; ; $i++) {
            $exists = Database::one(
                'SELECT id FROM ' . $table . ' WHERE slug = ? AND id <> ?',
                [$candidate, $exceptId]
            );
            if ($exists === null) {
                return $candidate;
            }
            $candidate = $base . '-' . $i;
        }
    }

    protected function now(): string
    {
        return date('Y-m-d H:i:s');
    }

    /** "/yonetim/mesajlar/12" gibi yollardaki id; sayi degilse 404. */
    protected function id(string $value): int
    {
        if (!ctype_digit($value) || (int) $value < 1) {
            $this->notFound();
        }
        return (int) $value;
    }

    protected function notFound(): void
    {
        http_response_code(404);
        echo View::renderFile('errors/404');
        exit;
    }
}
