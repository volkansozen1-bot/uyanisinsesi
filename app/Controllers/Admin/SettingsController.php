<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;
use App\Core\Upload;

/** settings tablosundaki genel site ayarlari. */
final class SettingsController extends AdminController
{
    /**
     * Formdaki alanlar: anahtar => [etiket, tur, aciklama]
     * tur: text | email | tel | url
     * Sosyal medya adresleri burada degil, Sosyal Medya bolumunde (social_links) yonetilir.
     */
    public const FIELDS = [
        'site_name'          => ['Site adı', 'text', 'Tarayıcı sekmesinde, logoda ve alt bilgide görünür.'],
        'site_tagline'       => ['Slogan', 'text', 'Alt bilgide ve ana sayfa açıklamasında kullanılır.'],
        'contact_email'      => ['İletişim e-postası', 'email', 'Sitede ziyaretçilere gösterilir.'],
        'contact_phone'      => ['Telefon', 'tel', 'Örnek: 0532 000 00 00'],
        'whatsapp_number'    => ['WhatsApp numarası', 'tel', 'Ülke koduyla, örnek: 90 532 000 00 00. Boş bırakılırsa WhatsApp düğmesi görünmez.'],
        'admin_notify_email' => ['Bildirim e-postası', 'email', 'İletişim formundan gelen mesajların bildirimi bu adrese gider. Boşsa iletişim e-postası kullanılır.'],
    ];

    public function form(): void
    {
        $values = [];
        foreach (Database::all('SELECT `key`, `value` FROM settings') as $row) {
            $values[$row['key']] = (string) $row['value'];
        }

        $this->render('settings/form', [
            'pageTitle' => 'Ayarlar',
            'fields'    => self::FIELDS,
            'values'    => ($_SESSION['_old'] ?? []) + $values,
            'errors'    => $_SESSION['_errors'] ?? [],
        ]);
        unset($_SESSION['_old'], $_SESSION['_errors']);
    }

    public function save(): void
    {
        $this->verifyCsrf('/yonetim/ayarlar');

        $input  = [];
        $errors = [];
        foreach (self::FIELDS as $key => [$label, $type]) {
            $value = trim($this->post($key));
            $input[$key] = $value;

            if (mb_strlen($value) > 255) {
                $errors[$key] = 'En fazla 255 karakter olabilir.';
                continue;
            }
            if ($value === '') {
                if ($key === 'site_name') {
                    $errors[$key] = 'Site adı boş bırakılamaz.';
                }
                continue;
            }
            if ($type === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$key] = 'Geçerli bir e-posta adresi yazın.';
            } elseif ($type === 'url' && (!str_starts_with(strtolower($value), 'https://') || !filter_var($value, FILTER_VALIDATE_URL))) {
                $errors[$key] = 'Adres https:// ile başlamalı (örnek: https://www.instagram.com/kullaniciadi).';
            } elseif ($type === 'tel' && !preg_match('/^\+?[0-9 ()\-]{7,20}$/', $value)) {
                $errors[$key] = 'Yalnızca rakam, boşluk ve + ( ) - kullanın.';
            }
        }

        // Logo: diger alanlar hataliysa yuklenmez; dosya secilmemisse mevcut logo kalir
        $currentLogo = $this->settingRow('site_logo');
        $logo = $this->imageAfterValidation('site_logo', $currentLogo !== '' ? $currentLogo : null, $errors, 'Site logosu');
        $input['site_logo'] = $logo ?? '';

        if ($errors !== []) {
            unset($input['site_logo']);
            $_SESSION['_old']    = $input;
            $_SESSION['_errors'] = $errors;
            flash('admin_error', 'Ayarlar kaydedilmedi. İşaretli alanları düzeltin.');
            redirect('/yonetim/ayarlar');
        }

        // Upsert: MySQL ve SQLite'ta ayni calissin diye iki adimda (varsa guncelle, yoksa ekle)
        $now = date('Y-m-d H:i:s');
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            foreach ($input as $key => $value) {
                $updated = Database::run('UPDATE settings SET `value` = ?, updated_at = ? WHERE `key` = ?', [$value, $now, $key])->rowCount();
                if ($updated === 0 && Database::one('SELECT id FROM settings WHERE `key` = ?', [$key]) === null) {
                    Database::run(
                        'INSERT INTO settings (`key`, `value`, created_at, updated_at) VALUES (?, ?, ?, ?)',
                        [$key, $value, $now, $now]
                    );
                }
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        flash('admin_success', 'Ayarlar kaydedildi.');
        redirect('/yonetim/ayarlar');
    }

    private function settingRow(string $key): string
    {
        $row = Database::one('SELECT `value` FROM settings WHERE `key` = ?', [$key]);
        $value = $row === null ? '' : (string) $row['value'];
        return Upload::isUploadPath($value) ? $value : '';
    }
}
