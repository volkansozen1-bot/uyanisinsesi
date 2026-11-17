<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;

final class ContentController extends AdminController
{
    private const HOME = '/yonetim/ana-sayfa';
    private const PAGES = '/yonetim/sayfalar';

    private const BLOCK_FIELDS = [
        'title' => ['Başlık', 255],
        'subtitle' => ['Alt başlık', 500],
        'body' => ['Metin', 0],
        'image' => ['Görsel adresi', 255],
        'button_text' => ['Düğme metni', 100],
        'button_url' => ['Düğme bağlantısı', 255],
    ];

    private const PAGE_FIELDS = [
        'title' => ['Başlık', 200],
        'body' => ['İçerik', 0],
        'hero_image' => ['Kapak görseli adresi', 255],
        'meta_title' => ['Arama sonucu başlığı', 200],
        'meta_description' => ['Arama sonucu açıklaması', 300],
    ];

    private const FIELDS_BY_BLOCK = [
        'hero' => ['title', 'subtitle', 'image', 'button_text', 'button_url'],
        'intro' => ['title', 'subtitle', 'body', 'button_text', 'button_url'],
        'story' => ['title', 'subtitle', 'body', 'image', 'button_text', 'button_url'],
        'course_promo' => ['title', 'subtitle', 'body', 'image', 'button_text', 'button_url'],
        'stats' => ['title'],
    ];

    public function home(): void
    {
        $rows = Database::all('SELECT * FROM home_blocks ORDER BY sort_order, id');
        foreach ($rows as &$row) {
            $row['editor_fields'] = self::blockFields($row['type']);
        }
        unset($row);
        $this->render('content/index', [
            'pageTitle' => 'Ana Sayfa',
            'path' => self::HOME,
            'rows' => $rows,
            'isHome' => true,
        ]);
    }

    public function pages(): void
    {
        $rows = Database::all('SELECT * FROM pages ORDER BY title, id');
        foreach ($rows as &$row) {
            $row['body'] = self::pageText((string) $row['body']);
        }
        unset($row);
        $this->render('content/index', [
            'pageTitle' => 'Sayfalar',
            'path' => self::PAGES,
            'rows' => $rows,
            'fields' => self::PAGE_FIELDS,
            'isHome' => false,
        ]);
    }

    public function saveBlock(string $id): void
    {
        $this->verifyCsrf(self::HOME);
        $id = $this->id($id);
        $block = Database::one('SELECT type FROM home_blocks WHERE id = ?', [$id]);
        if (!$block) {
            $this->notFound();
        }
        $fields = $this->readFields(self::blockFields($block['type']), self::HOME);
        $fields += array_fill_keys(array_keys(self::BLOCK_FIELDS), null);
        $order = filter_var($this->post('sort_order'), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => -2147483648, 'max_range' => 2147483647],
        ]);
        if ($order === false) {
            $this->invalid(self::HOME, 'Sıra numarası geçersiz.');
        }
        Database::run(
            'UPDATE home_blocks SET title = COALESCE(?, title), subtitle = COALESCE(?, subtitle),
             body = COALESCE(?, body), image = COALESCE(?, image), button_text = COALESCE(?, button_text),
             button_url = COALESCE(?, button_url), sort_order = ?, is_active = ? WHERE id = ?',
            [$fields['title'], $fields['subtitle'], $fields['body'], $fields['image'], $fields['button_text'],
             $fields['button_url'], $order, (int) ($this->post('active') === '1'), $id]
        );
        $this->saved(self::HOME);
    }

    public function savePage(string $id): void
    {
        $this->verifyCsrf(self::PAGES);
        $id = $this->id($id);
        $page = Database::one('SELECT body FROM pages WHERE id = ?', [$id]);
        if (!$page) {
            $this->notFound();
        }
        $fields = $this->readFields(self::PAGE_FIELDS, self::PAGES);
        if (trim($fields['title']) === '') {
            $this->invalid(self::PAGES, 'Sayfa başlığı gerekli.');
        }
        $status = $this->post('active') === '1' ? 'published' : 'draft';
        $body = $fields['body'] === self::pageText((string) $page['body'])
            ? null : nl2br(e($fields['body']), false);
        Database::run(
            'UPDATE pages SET title = ?, hero_image = ?, meta_title = ?, meta_description = ?, status = ?,
             body = COALESCE(?, body) WHERE id = ?',
            [$fields['title'], $fields['hero_image'], $fields['meta_title'], $fields['meta_description'], $status, $body, $id]
        );
        $this->saved(self::PAGES);
    }

    private static function pageText(string $html): string
    {
        $html = preg_replace('~<br\s*/?>(?:\r\n|\r|\n)?|</(?:p|div|li|h[1-6])>~i', "\n", $html);
        return str_replace(["\r\n", "\r"], "\n", html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    private static function blockFields(string $type): array
    {
        return array_intersect_key(self::BLOCK_FIELDS, array_flip(self::FIELDS_BY_BLOCK[$type] ?? ['title', 'subtitle']));
    }

    private function readFields(array $definitions, string $path): array
    {
        $fields = [];
        foreach ($definitions as $name => [$label, $max]) {
            $value = str_replace(["\r\n", "\r"], "\n", $this->post($name));
            if (!mb_check_encoding($value, 'UTF-8') || ($max && mb_strlen($value) > $max)
                || (!$max && strlen(e($value)) + 4 * substr_count($value, "\n") > 65535)) {
                $this->invalid($path, $label . ' alanı geçersiz veya çok uzun.');
            }
            if (in_array($name, ['image', 'hero_image', 'button_url'], true) && $value !== ''
                && !preg_match('~\\A(?:https?://[^/\\s]+|/(?![/\\\\])|\\#)[^\\s\\\\]*\\z~i', $value)) {
                $this->invalid($path, $label . ' için geçerli bir web adresi veya / ile başlayan site içi yol girin.');
            }
            $fields[$name] = $value;
        }
        return $fields;
    }

    private function invalid(string $path, string $message): void
    {
        flash('admin_error', $message);
        redirect($path);
    }

    private function saved(string $path): void
    {
        flash('admin_success', 'Değişiklikler kaydedildi.');
        redirect($path);
    }
}
