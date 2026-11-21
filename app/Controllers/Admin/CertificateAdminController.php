<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Upload;

/**
 * Sertifikalar: gorseller settings.certificates icinde JSON liste olarak tutulur.
 * Sitede /sertifikalar sayfasinda izgara + tiklayinca buyuyen gorunum (views/site/page.php).
 */
final class CertificateAdminController extends AdminController
{
    private const BASE = '/yonetim/sertifikalar';

    public function index(): void
    {
        $this->render('certificates/index', ['pageTitle' => 'Sertifikalar', 'items' => certificates()]);
    }

    /** Birden fazla gorsel ayni anda yuklenebilir (input name="files[]"). */
    public function store(): void
    {
        $this->verifyCsrf(self::BASE);
        $items = certificates();
        $f = $_FILES['files'] ?? null;
        $added = 0;
        $errors = [];
        if (is_array($f) && is_array($f['name'] ?? null)) {
            foreach (array_keys($f['name']) as $i) {
                $one  = ['name' => $f['name'][$i], 'type' => $f['type'][$i], 'tmp_name' => $f['tmp_name'][$i], 'error' => $f['error'][$i], 'size' => $f['size'][$i]];
                $path = Upload::image($one, 'Sertifika');
                if ($path !== null) {
                    $items[] = ['path' => $path, 'title' => pathinfo((string) $f['name'][$i], PATHINFO_FILENAME)];
                    $added++;
                } elseif (Upload::$error !== '') {
                    $errors[] = $f['name'][$i] . ': ' . Upload::$error;
                }
            }
        }
        $this->save($items);
        flash($errors ? 'admin_error' : 'admin_success', $added . ' sertifika eklendi.' . ($errors ? ' ' . implode(' ', $errors) : ''));
        redirect(self::BASE);
    }

    /** Basliklari ve sirayi kaydeder; "sil" isaretlileri listeden cikarir. */
    public function update(): void
    {
        $this->verifyCsrf(self::BASE);
        $items  = certificates();
        $titles = is_array($_POST['title'] ?? null) ? $_POST['title'] : [];
        $orders = is_array($_POST['order'] ?? null) ? $_POST['order'] : [];
        $del    = is_array($_POST['delete'] ?? null) ? $_POST['delete'] : [];
        $keep   = [];
        foreach ($items as $i => $item) {
            if (isset($del[$i])) {
                continue;
            }
            $item['title'] = mb_substr(trim((string) ($titles[$i] ?? $item['title'])), 0, 150);
            $keep[] = $item + ['_o' => (int) ($orders[$i] ?? $i)];
        }
        usort($keep, fn ($a, $b) => $a['_o'] <=> $b['_o']);
        $this->save(array_map(fn ($x) => ['path' => $x['path'], 'title' => $x['title']], $keep));
        flash('admin_success', 'Sertifikalar kaydedildi.');
        redirect(self::BASE);
    }

    /** @param array<int, array{path: string, title: string}> $items */
    private function save(array $items): void
    {
        $json = json_encode(array_values($items), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $now  = date('Y-m-d H:i:s');
        if (Database::run('UPDATE settings SET `value` = ?, updated_at = ? WHERE `key` = ?', [$json, $now, 'certificates'])->rowCount() === 0
            && Database::one('SELECT id FROM settings WHERE `key` = ?', ['certificates']) === null) {
            Database::run('INSERT INTO settings (`key`, `value`, created_at, updated_at) VALUES (?, ?, ?, ?)', ['certificates', $json, $now, $now]);
        }
    }
}
