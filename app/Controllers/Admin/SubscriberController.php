<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;

/** Bulten aboneleri: liste, arama, CSV, silme (KVKK silme talebi). */
final class SubscriberController extends AdminController
{
    /** URL'deki durum degeri => SQL kosulu */
    private const STATUSES = [
        'bekliyor' => 'confirmed_at IS NULL AND unsubscribed_at IS NULL',
        'onayli'   => 'confirmed_at IS NOT NULL AND unsubscribed_at IS NULL',
        'cikmis'   => 'unsubscribed_at IS NOT NULL',
    ];

    public function index(): void
    {
        [$where, $params, $filters] = $this->filters();
        $pager = $this->paginate($this->count("SELECT COUNT(*) FROM subscribers WHERE $where", $params));

        $this->render('subscribers/index', [
            'pageTitle'   => 'Bülten Aboneleri',
            'filters'     => $filters,
            'pager'       => $pager,
            'subscribers' => Database::all(
                "SELECT id, name, email, consent_at, confirmed_at, unsubscribed_at, created_at FROM subscribers
                 WHERE $where ORDER BY created_at DESC, id DESC LIMIT " . $pager['limit'] . ' OFFSET ' . $pager['offset'],
                $params
            ),
        ]);
    }

    /** Excel'in Turkce karakterleri dogru acmasi icin UTF-8 BOM ve ";" ayrac. */
    public function export(): void
    {
        [$where, $params] = $this->filters();
        $rows = Database::all(
            "SELECT name, email, consent_at, confirmed_at, unsubscribed_at, created_at FROM subscribers
             WHERE $where ORDER BY created_at DESC, id DESC",
            $params
        );

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="bulten-aboneleri-' . date('Y-m-d') . '.csv"');

        $out = fopen('php://output', 'wb');
        fwrite($out, "\xEF\xBB\xBF");
        fputcsv($out, ['E-posta', 'Ad', 'Durum', 'Rıza tarihi', 'Onay tarihi', 'Çıkış tarihi', 'Kayıt tarihi'], ';', '"', '');
        foreach ($rows as $row) {
            fputcsv($out, array_map([self::class, 'csvCell'], [
                $row['email'],
                $row['name'],
                self::statusLabel($row),
                $row['consent_at'],
                $row['confirmed_at'],
                $row['unsubscribed_at'],
                $row['created_at'],
            ]), ';', '"', '');
        }
        fclose($out);
        exit;
    }

    public function delete(string $id): void
    {
        $this->verifyCsrf('/yonetim/aboneler');
        $deleted = Database::run('DELETE FROM subscribers WHERE id = ?', [$this->id($id)])->rowCount() > 0;
        flash($deleted ? 'admin_success' : 'admin_error', $deleted ? 'Abone kaydı kalıcı olarak silindi.' : 'Kayıt bulunamadı.');
        redirect('/yonetim/aboneler');
    }

    /** @param array<string, mixed> $row */
    public static function statusLabel(array $row): string
    {
        if (!empty($row['unsubscribed_at'])) {
            return 'Çıkmış';
        }
        return !empty($row['confirmed_at']) ? 'Onaylı' : 'Onay bekliyor';
    }

    /** Excel'de formul olarak calismasin diye =,+,-,@ ile baslayan hucrelerin basina ' eklenir. */
    public static function csvCell($value): string
    {
        $value = (string) ($value ?? '');
        return $value !== '' && strpbrk($value[0], "=+-@\t\r") !== false ? "'" . $value : $value;
    }

    /** @return array{0:string, 1:array<int, string>, 2:array<string, string>} */
    private function filters(): array
    {
        $q      = mb_substr(trim($this->query('q')), 0, 190);
        $status = $this->query('durum');
        $where  = ['1 = 1'];
        $params = [];

        if ($q !== '') {
            // % ve _ joker karakter sayilmasin
            $like     = '%' . strtr(mb_strtolower($q), ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
            $where[]  = "(email LIKE ? ESCAPE '!' OR name LIKE ? ESCAPE '!')";
            $params[] = $like;
            $params[] = $like;
        }
        if (isset(self::STATUSES[$status])) {
            $where[] = self::STATUSES[$status];
        } else {
            $status = '';
        }

        return [implode(' AND ', $where), $params, ['q' => $q, 'durum' => $status]];
    }
}
