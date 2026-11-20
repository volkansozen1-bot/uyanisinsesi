<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;

/**
 * Albumler (albums). Sitede /albumler sayfasinda gorunur.
 * Rotalar: config/routes/admin_albumler.php  Gorunumler: views/admin/albums/
 */
final class AlbumAdminController extends AdminController
{
    private const BASE = '/yonetim/albumler';

    public const TYPES = ['album' => 'Albüm', 'ep' => 'EP', 'single' => 'Single'];

    public function index(): void
    {
        $this->render('albums/index', [
            'pageTitle' => 'Albümler',
            'items'     => Database::all('SELECT * FROM albums ORDER BY sort_order, id'),
        ]);
    }

    public function create(): void
    {
        $this->form(null);
    }

    public function edit(string $id): void
    {
        $this->form($this->find($id));
    }

    public function store(): void
    {
        $this->save(null);
    }

    public function update(string $id): void
    {
        $this->save($this->find($id));
    }

    public function delete(string $id): void
    {
        $this->verifyCsrf(self::BASE);
        $row = $this->find($id);
        Database::run('DELETE FROM albums WHERE id = ?', [$row['id']]);
        flash('admin_success', 'Albüm silindi.');
        redirect(self::BASE);
    }

    /** @return array<string, mixed> */
    private function find(string $id): array
    {
        $row = Database::one('SELECT * FROM albums WHERE id = ?', [$this->id($id)]);
        if ($row === null) {
            $this->notFound();
        }
        return $row;
    }

    /** @param array<string, mixed>|null $row */
    private function form(?array $row): void
    {
        $defaults = $row;
        if ($defaults === null) {
            $max = Database::one('SELECT MAX(sort_order) AS m FROM albums');
            $defaults = ['is_active' => 1, 'type' => 'album', 'year' => date('Y'), 'sort_order' => (int) ($max['m'] ?? 0) + 1];
        }
        [$values, $errors] = $this->formState($defaults);
        $this->render('albums/form', [
            'pageTitle' => $row ? 'Albümü düzenle' : 'Yeni albüm',
            'row'       => $row,
            'values'    => $values,
            'errors'    => $errors,
            'types'     => self::TYPES,
            'action'    => $row ? self::BASE . '/' . $row['id'] : self::BASE . '/yeni',
        ]);
    }

    /** @param array<string, mixed>|null $row */
    private function save(?array $row): void
    {
        $back = $row ? self::BASE . '/' . $row['id'] : self::BASE . '/yeni';
        $this->verifyCsrf($back);

        $errors = [];
        $title  = $this->postText('title', 200);
        $type   = $this->post('type');
        $year   = $this->optionalInt('year', 1900, (int) date('Y') + 2);
        $tracks = $this->optionalInt('track_count', 1, 999);
        $listen = trim($this->post('listen_url'));

        if ($title === null || $title === '') {
            $errors['title'] = 'Albüm adını yazın (en fazla 200 karakter).';
        }
        if (!isset(self::TYPES[$type])) {
            $errors['type'] = 'Tür seçin.';
        }
        if ($year === false) {
            $errors['year'] = 'Dört haneli bir yıl yazın (örnek: ' . date('Y') . ').';
        }
        if ($tracks === false) {
            $errors['track_count'] = '1 ile 999 arasında bir sayı yazın ya da boş bırakın.';
        }
        if ($listen !== '' && (mb_strlen($listen) > 255 || !preg_match('#^https://#i', $listen) || !filter_var($listen, FILTER_VALIDATE_URL))) {
            $errors['listen_url'] = 'Adres https:// ile başlamalı (örnek: https://open.spotify.com/album/...).';
        }

        $cover = $this->imageField('cover_image', $row['cover_image'] ?? null, $errors, (string) $title);

        if ($errors !== []) {
            $this->backWithErrors($back, $errors, $_POST);
        }

        $data = [
            $title, $type, $year, $tracks, $cover, $listen !== '' ? $listen : null,
            $this->postInt('sort_order'), $this->postBool('is_active'), $this->now(),
        ];
        if ($row) {
            Database::run(
                'UPDATE albums SET title = ?, type = ?, year = ?, track_count = ?, cover_image = ?, listen_url = ?, sort_order = ?, is_active = ?, updated_at = ? WHERE id = ?',
                array_merge($data, [$row['id']])
            );
        } else {
            Database::run(
                'INSERT INTO albums (title, type, year, track_count, cover_image, listen_url, sort_order, is_active, updated_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                array_merge($data, [$this->now()])
            );
        }

        flash('admin_success', 'Albüm kaydedildi.');
        redirect(self::BASE);
    }

    /**
     * Bos ise null, aralikta tam sayi ise sayi, aksi halde false.
     * @return int|null|false
     */
    private function optionalInt(string $key, int $min, int $max)
    {
        $raw = trim($this->post($key));
        if ($raw === '') {
            return null;
        }
        $v = filter_var($raw, FILTER_VALIDATE_INT, ['options' => ['min_range' => $min, 'max_range' => $max]]);
        return $v === false ? false : $v;
    }
}
