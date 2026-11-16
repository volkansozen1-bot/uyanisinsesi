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
