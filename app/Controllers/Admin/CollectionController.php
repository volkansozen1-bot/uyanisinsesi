<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use PDOException;

final class CollectionController extends AdminController
{
    public function index(string $module): void
    {
        [$table, $title, $fields] = $this->module($module);
        $this->render('content/collection', [
            'pageTitle' => $title,
            'fields' => $fields,
            'path' => '/yonetim/icerik/' . $module,
            'rows' => Database::all("SELECT * FROM $table ORDER BY id DESC"),
        ]);
    }

    public function save(string $module, string $id = 'new'): void
    {
        [$table, , $fields] = $this->module($module);
        $path = '/yonetim/icerik/' . $module;
        $this->verifyCsrf($path);
        $creating = $id === 'new';
        $row = $creating ? [] : Database::one("SELECT * FROM $table WHERE id = ?", [$this->id($id)]);
        if ($row === null) {
            $this->notFound();
        }
        $values = [];
        foreach ($fields as $key => [$label, $limit]) {
            $value = trim(str_replace(["\r\n", "\r"], "\n", $this->post($key)));
            if (!mb_check_encoding($value, 'UTF-8') || ($limit && mb_strlen($value) > $limit)
                || (!$limit && strlen($value) > 16000)
                || (in_array($key, ['title', 'name', 'text'], true) && $value === '')) {
                $this->invalid($path, $label . ' geçersiz.');
            }
            if ($key === 'slug' && !preg_match('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', $value)) {
                $this->invalid($path, 'Adres küçük harf, sayı ve kısa çizgiden oluşmalı.');
            }
            if (in_array($key, ['image', 'cover_image', 'listen_url'], true) && $value !== ''
                && !preg_match('~\A(?:https?://[^/\s]+|/(?![/\\\\]))[^\s\\\\]*\z~i', $value)) {
                $this->invalid($path, 'Bağlantıyı https:// ile ya da / ile başlayan site içi yol olarak girin.');
            }
            if ($key === 'rating' && !in_array($value, ['1', '2', '3', '4', '5'], true)) {
                $this->invalid($path, 'Puan 1 ile 5 arasında olmalı.');
            }
            if ($key === 'start_date' && $value !== '') {
                $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
                if (!$date || $date->format('Y-m-d') !== $value) {
                    $this->invalid($path, 'Başlangıç tarihi geçersiz.');
                }
            }
            if ($key === 'body') {
                $original = (string) ($row[$key] ?? '');
                $value = $value === self::plainBody($original) ? $original : nl2br(e($value), false);
            }
            $values[$key] = $value === '' ? null : $value;
        }
        $active = $this->post('active') === '1';
        if ($table === 'posts') {
            $values['status'] = $active ? 'published' : 'draft';
            $values['published_at'] = ($row['published_at'] ?? null) ?: ($active ? date('Y-m-d H:i:s') : null);
        } else {
            $values['is_active'] = (int) $active;
        }
        $columns = array_keys($values);
        try {
            if ($creating) {
                $names = implode(', ', array_map(static fn ($key) => "`$key`", $columns));
                $slots = implode(', ', array_fill(0, count($columns), '?'));
                Database::run("INSERT INTO $table ($names) VALUES ($slots)", array_values($values));
            } else {
                $assignments = implode(', ', array_map(static fn ($key) => "`$key` = ?", $columns));
                Database::run("UPDATE $table SET $assignments WHERE id = ?", [...array_values($values), (int) $id]);
            }
        } catch (PDOException $exception) {
            if ((int) ($exception->errorInfo[1] ?? 0) !== 1062) {
                throw $exception;
            }
            $this->invalid($path, 'Bu adres daha önce kullanılmış.');
        }
        flash('admin_success', 'İçerik kaydedildi.');
        redirect($path);
    }

    public static function plainBody(string $html): string
    {
        $html = preg_replace('~<br\s*/?>(?:\r\n|\r|\n)?|</(?:p|div|li|h[1-6])>~i', "\n", $html);
        return trim(str_replace(["\r\n", "\r"], "\n", html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    }

    private function module(string $key): array
    {
        $modules = require BASE_PATH . '/config/editor_modules.php';
        return $modules[$key] ?? $this->unknownModule();
    }

    private function unknownModule(): array
    {
        $this->notFound();
        return [];
    }

    private function invalid(string $path, string $message): void
    {
        flash('admin_error', $message);
        redirect($path);
    }
}
