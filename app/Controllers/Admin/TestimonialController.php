<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;

/**
 * Yorumlar (testimonials). Diger icerik modulleri icin ORNEK modul:
 *   liste -> yeni/duzenle formu -> kaydet (dogrulama + hata ile geri donus) -> sil
 * Rotalar: config/routes/admin_yorumlar.php  Gorunumler: views/admin/testimonials/
 */
final class TestimonialController extends AdminController
{
    private const BASE = '/yonetim/yorumlar';

    public function index(): void
    {
        $this->render('testimonials/index', [
            'pageTitle' => 'Yorumlar',
            'items'     => Database::all(
                'SELECT t.*, s.title AS service_title FROM testimonials t
                 LEFT JOIN services s ON s.id = t.service_id
                 ORDER BY t.sort_order, t.id'
            ),
        ]);
    }

    public function create(): void
    {
        $this->form(null);
    }

    public function edit(string $id): void
    {
        $row = Database::one('SELECT * FROM testimonials WHERE id = ?', [$this->id($id)]);
        if ($row === null) {
            $this->notFound();
        }
        $this->form($row);
    }

    public function store(): void
    {
        $this->save(null);
    }

    public function update(string $id): void
    {
        $row = Database::one('SELECT * FROM testimonials WHERE id = ?', [$this->id($id)]);
        if ($row === null) {
            $this->notFound();
        }
        $this->save($row);
    }

    public function delete(string $id): void
    {
        $this->verifyCsrf(self::BASE);
        Database::run('DELETE FROM testimonials WHERE id = ?', [$this->id($id)]);
        flash('admin_success', 'Yorum silindi.');
        redirect(self::BASE);
    }

    /** @param array<string, mixed>|null $row */
    private function form(?array $row): void
    {
        [$values, $errors] = $this->formState($row ?? ['is_active' => 1, 'rating' => 5, 'sort_order' => 0]);
        $this->render('testimonials/form', [
            'pageTitle' => $row ? 'Yorumu düzenle' : 'Yeni yorum',
            'row'       => $row,
            'values'    => $values,
            'errors'    => $errors,
            'services'  => array_column(Database::all('SELECT id, title FROM services ORDER BY sort_order, id'), 'title', 'id'),
            'action'    => $row ? self::BASE . '/' . $row['id'] : self::BASE . '/yeni',
        ]);
    }

    /** @param array<string, mixed>|null $row */
    private function save(?array $row): void
    {
        $back = $row ? self::BASE . '/' . $row['id'] : self::BASE . '/yeni';
        $this->verifyCsrf($back);

        $errors = [];
        $input  = [
            'name'       => $this->postText('name', 150),
            'text'       => $this->postText('text', 2000),
            'rating'     => $this->postInt('rating', 5),
            'service_id' => $this->post('service_id'),
            'sort_order' => $this->postInt('sort_order'),
            'is_active'  => $this->postBool('is_active'),
        ];

        if ($input['name'] === null || $input['name'] === '') {
            $errors['name'] = 'Ad yazın (en fazla 150 karakter).';
        }
        if ($input['text'] === null || $input['text'] === '') {
            $errors['text'] = 'Yorum metnini yazın (en fazla 2000 karakter).';
        }
        if ($input['rating'] < 1 || $input['rating'] > 5) {
            $errors['rating'] = '1 ile 5 arasında olmalı.';
        }
        $serviceId = null;
        if ($input['service_id'] !== '') {
            $serviceId = filter_var($input['service_id'], FILTER_VALIDATE_INT) ?: null;
            if ($serviceId === null || Database::one('SELECT id FROM services WHERE id = ?', [$serviceId]) === null) {
                $errors['service_id'] = 'Geçerli bir hizmet seçin.';
            }
        }

        if ($errors !== []) {
            $this->backWithErrors($back, $errors, $_POST);
        }

        $data = [$input['name'], $input['text'], $input['rating'], $serviceId, $input['sort_order'], $input['is_active'], $this->now()];
        if ($row) {
            Database::run(
                'UPDATE testimonials SET name = ?, `text` = ?, rating = ?, service_id = ?, sort_order = ?, is_active = ?, updated_at = ? WHERE id = ?',
                array_merge($data, [$row['id']])
            );
        } else {
            Database::run(
                'INSERT INTO testimonials (name, `text`, rating, service_id, sort_order, is_active, updated_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                array_merge($data, [$this->now()])
            );
        }

        flash('admin_success', 'Yorum kaydedildi.');
        redirect(self::BASE);
    }
}
