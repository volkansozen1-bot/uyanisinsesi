<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;

/**
 * Sosyal medya baglantilari (social_links). Alt bilgide ve Albumler sayfasindaki
 * Spotify dugmesinde kullanilir. Ikonlar yalnizca kullanicinin yukledigi gorsellerdir;
 * projede hazir marka logosu bulunmaz.
 * Rotalar: config/routes/admin_sosyal.php  Gorunumler: views/admin/social/
 */
final class SocialAdminController extends AdminController
{
    private const BASE = '/yonetim/sosyal-medya';

    /** platform anahtari => gorunen ad (select sirasi) */
    public const PLATFORMS = [
        'instagram'       => 'Instagram',
        'youtube'         => 'YouTube',
        'spotify'         => 'Spotify',
        'facebook'        => 'Facebook',
        'x'               => 'X (Twitter)',
        'tiktok'          => 'TikTok',
        'linkedin'        => 'LinkedIn',
        'telegram'        => 'Telegram',
        'pinterest'       => 'Pinterest',
        'apple_podcasts'  => 'Apple Podcasts',
        'other'           => 'Diğer',
    ];

    public function index(): void
    {
        $this->render('social/index', [
            'pageTitle' => 'Sosyal Medya',
            'items'     => Database::all('SELECT * FROM social_links ORDER BY sort_order, id'),
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
        Database::run('DELETE FROM social_links WHERE id = ?', [$row['id']]);
        flash('admin_success', 'Bağlantı silindi.');
        redirect(self::BASE);
    }

    /** @return array<string, mixed> */
    private function find(string $id): array
    {
        $row = Database::one('SELECT * FROM social_links WHERE id = ?', [$this->id($id)]);
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
            $max = Database::one('SELECT MAX(sort_order) AS m FROM social_links');
            $defaults = ['is_active' => 1, 'platform' => 'instagram', 'sort_order' => (int) ($max['m'] ?? 0) + 1];
        }
        [$values, $errors] = $this->formState($defaults);
        $this->render('social/form', [
            'pageTitle' => $row ? 'Bağlantıyı düzenle' : 'Yeni bağlantı',
            'row'       => $row,
            'values'    => $values,
            'errors'    => $errors,
            'platforms' => self::PLATFORMS,
            'action'    => $row ? self::BASE . '/' . $row['id'] : self::BASE . '/yeni',
        ]);
    }

    /** @param array<string, mixed>|null $row */
    private function save(?array $row): void
    {
        $back = $row ? self::BASE . '/' . $row['id'] : self::BASE . '/yeni';
        $this->verifyCsrf($back);

        $errors   = [];
        $platform = $this->post('platform');
        $label    = $this->postText('label', 60);
        $url      = trim($this->post('url'));

        if (!isset(self::PLATFORMS[$platform])) {
            $errors['platform'] = 'Listeden bir platform seçin.';
            $platform = '';
        }
        if ($label === null) {
            $errors['label'] = 'Görünen ad en fazla 60 karakter olabilir.';
            $label = '';
        } elseif ($label === '') {
            if ($platform === 'other') {
                $errors['label'] = '"Diğer" seçtiğinizde görünen adı yazmanız gerekir.';
            } elseif ($platform !== '') {
                $label = self::PLATFORMS[$platform];
            }
        }
        if ($url === '') {
            $errors['url'] = 'Adresi yazın (örnek: https://www.instagram.com/kullaniciadi).';
        } elseif (mb_strlen($url) > 255) {
            $errors['url'] = 'Adres en fazla 255 karakter olabilir.';
        } elseif (!preg_match('#^https://#i', $url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            $errors['url'] = 'Adres https:// ile başlamalı (örnek: https://www.instagram.com/kullaniciadi).';
        }

        $icon = $this->imageField('icon', $row['icon'] ?? null, $errors, (string) $label);

        if ($errors !== []) {
            $this->backWithErrors($back, $errors, $_POST);
        }

        $data = [$platform, $label, $url, $icon, $this->postInt('sort_order'), $this->postBool('is_active'), $this->now()];
        if ($row) {
            Database::run(
                'UPDATE social_links SET platform = ?, label = ?, url = ?, icon = ?, sort_order = ?, is_active = ?, updated_at = ? WHERE id = ?',
                array_merge($data, [$row['id']])
            );
        } else {
            Database::run(
                'INSERT INTO social_links (platform, label, url, icon, sort_order, is_active, updated_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
                array_merge($data, [$this->now()])
            );
        }

        flash('admin_success', 'Bağlantı kaydedildi.');
        redirect(self::BASE);
    }
}
