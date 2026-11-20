<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Upload;
use PDOException;

/**
 * Medya kitapligi (media tablosu). Panelden yuklenen gorseller burada toplanir;
 * zengin metin editorunde gorsel/baglanti eklerken adresleri buradan kopyalanir.
 * Rotalar: config/routes/admin_medya.php  Gorunumler: views/admin/media/
 */
final class MediaAdminController extends AdminController
{
    private const BASE = '/yonetim/medya';
    private const PER_PAGE_MEDIA = 40;

    public function index(): void
    {
        $total = $this->count('SELECT COUNT(*) AS n FROM media');
        $pager = $this->paginate($total, self::PER_PAGE_MEDIA);
        $items = Database::all(
            'SELECT * FROM media ORDER BY id DESC LIMIT ' . $pager['limit'] . ' OFFSET ' . $pager['offset']
        );

        $this->render('media/index', [
            'pageTitle' => 'Medya',
            'items'     => $items,
            'pager'     => $pager,
        ]);
    }

    /** Tek dosya yukleme. Upload::image dosyayi kaydeder ve media satirini olusturur. */
    /**
     * Zengin metin editorundeki "Gorsel ekle" dugmesi icin: dosyayi yukler ve adresini JSON dondurur.
     * Sayfa yenilenmeden calisir; CSRF yine zorunlu.
     */
    public function uploadJson(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        if (!\App\Core\Csrf::verify()) {
            http_response_code(400);
            echo json_encode(['error' => 'Oturum süresi doldu. Sayfayı yenileyip yeniden deneyin.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $file = $_FILES['image'] ?? null;
        if (!is_array($file) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            http_response_code(400);
            echo json_encode(['error' => 'Yüklenecek bir görsel seçin.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        $alt  = $this->postText('alt', 255);
        $path = Upload::image($file, $alt === null ? '' : $alt);
        if ($path === null) {
            http_response_code(400);
            echo json_encode(['error' => Upload::$error !== '' ? Upload::$error : 'Görsel yüklenemedi.'], JSON_UNESCAPED_UNICODE);
            return;
        }

        echo json_encode(['url' => url($path)], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function upload(): void
    {
        $this->verifyCsrf(self::BASE);

        $file = $_FILES['image'] ?? null;
        if (!is_array($file) || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            flash('admin_error', 'Yüklenecek bir görsel seçin.');
            redirect(self::BASE);
        }

        $alt  = $this->postText('alt', 255);
        $path = Upload::image($file, $alt === null ? '' : $alt);
        if ($path === null) {
            flash('admin_error', Upload::$error !== '' ? Upload::$error : 'Görsel yüklenemedi.');
            redirect(self::BASE);
        }

        flash('admin_success', 'Görsel yüklendi. Adresini kopyalayarak yazılarda kullanabilirsiniz.');
        redirect(self::BASE);
    }

    /** Alt metni gunceller. */
    public function update(string $id): void
    {
        $this->verifyCsrf(self::BASE);
        $row = $this->find($id);

        $alt = $this->postText('alt', 255);
        if ($alt === null) {
            flash('admin_error', 'Alt metin en fazla 255 karakter olabilir.');
            redirect(self::BASE);
        }

        Database::run(
            'UPDATE media SET alt = ?, updated_at = ? WHERE id = ?',
            [$alt !== '' ? $alt : null, $this->now(), $row['id']]
        );
        flash('admin_success', 'Alt metin kaydedildi.');
        redirect(self::BASE);
    }

    /** Kullanimda degilse kaydi ve dosyayi siler. */
    public function delete(string $id): void
    {
        $this->verifyCsrf(self::BASE);
        $row  = $this->find($id);
        $path = (string) $row['path'];

        $used = self::usage($path);
        if ($used !== []) {
            flash('admin_error', 'Bu görsel silinmedi, şu an kullanılıyor: ' . implode(', ', $used)
                . '. Önce oralardan kaldırın, sonra silin.');
            redirect(self::BASE);
        }

        Database::run('DELETE FROM media WHERE id = ?', [$row['id']]);

        // Dosya yalnizca beklenen yukleme yolu bicimindeyse silinir
        if (Upload::isUploadPath($path)) {
            $file = BASE_PATH . '/public/' . $path;
            if (is_file($file)) {
                @unlink($file);
            }
        }

        flash('admin_success', 'Görsel silindi.');
        redirect(self::BASE);
    }

    /**
     * Gorselin sitede nerelerde kullanildigi. Bos dizi: hicbir yerde kullanilmiyor.
     * Hem dogrudan gorsel alanlari hem de zengin metin iceriklerindeki <img src="..."> aranir.
     * @return array<int, string>
     */
    public static function usage(string $path): array
    {
        $like  = '%' . str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $path) . '%';
        $found = [];

        $checks = [
            ['Hizmet', 'SELECT title AS name FROM services WHERE image = ? OR body LIKE ? ESCAPE \'!\'', [$path, $like]],
            ['Hizmet bölümü', 'SELECT tab_label AS name FROM service_sections WHERE image = ? OR body LIKE ? ESCAPE \'!\'', [$path, $like]],
            ['Eğitim', 'SELECT title AS name FROM courses WHERE image = ? OR body LIKE ? ESCAPE \'!\'', [$path, $like]],
            ['Yazı', 'SELECT title AS name FROM posts WHERE cover_image = ? OR body LIKE ? ESCAPE \'!\'', [$path, $like]],
            ['Sayfa', 'SELECT title AS name FROM pages WHERE hero_image = ? OR body LIKE ? ESCAPE \'!\'', [$path, $like]],
            ['Ana sayfa bloğu', 'SELECT type AS name FROM home_blocks WHERE image = ?', [$path]],
            ['Albüm', 'SELECT title AS name FROM albums WHERE cover_image = ?', [$path]],
            ['Sosyal medya', 'SELECT label AS name FROM social_links WHERE icon = ?', [$path]],
        ];

        foreach ($checks as [$label, $sql, $params]) {
            try {
                $rows = Database::all($sql, $params);
            } catch (PDOException $e) {
                // social_links gibi bir tablo henuz olusturulmamissa (guncelleme yapilmamis kurulum) atlanir
                continue;
            }
            foreach ($rows as $row) {
                $name = trim((string) $row['name']);
                $found[] = $label . ($name !== '' ? ' "' . $name . '"' : '');
            }
        }

        return $found;
    }

    /** @return array<string, mixed> */
    private function find(string $id): array
    {
        $row = Database::one('SELECT * FROM media WHERE id = ?', [$this->id($id)]);
        if ($row === null) {
            $this->notFound();
        }
        return $row;
    }
}
