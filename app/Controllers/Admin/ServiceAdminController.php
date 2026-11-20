<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;

/**
 * Hizmetler: liste -> yeni/duzenle formu -> sil.
 * Duzenleme sayfasinda alt kayitlar: paketler (service_packages), kitle bolumleri (service_sections),
 * SSS (faqs, owner_type='service'). Alt kayit adresleri: /yonetim/hizmetler/{id}/{tur}/yeni|{cid}|{cid}/sil
 * Rotalar: config/routes/admin_hizmetler.php  Gorunumler: views/admin/services/
 */
final class ServiceAdminController extends AdminController
{
    private const BASE = '/yonetim/hizmetler';

    /** Sayfa icindeki bu kimlikler site sablonunda kullaniliyor; bolum capasi olamaz. */
    private const RESERVED_ANCHORS = ['ucretler', 'randevu', 'icerik'];

    /**
     * Alt kayit turleri. Adresdeki {tur} bu anahtarlardan biri olmali.
     * @var array<string, array<string, string>>
     */
    private const CHILDREN = [
        'paket' => ['table' => 'service_packages', 'view' => 'package', 'anchor' => 'paketler', 'new' => 'Yeni paket', 'edit' => 'Paketi düzenle', 'saved' => 'Paket kaydedildi.', 'deleted' => 'Paket silindi.'],
        'bolum' => ['table' => 'service_sections', 'view' => 'section', 'anchor' => 'bolumler', 'new' => 'Yeni bölüm', 'edit' => 'Bölümü düzenle', 'saved' => 'Bölüm kaydedildi.', 'deleted' => 'Bölüm silindi.'],
        'sss'   => ['table' => 'faqs', 'view' => 'faq', 'anchor' => 'sss', 'new' => 'Yeni soru', 'edit' => 'Soruyu düzenle', 'saved' => 'Soru kaydedildi.', 'deleted' => 'Soru silindi.'],
    ];

    // ------------------------------------------------------------------
    // Hizmet
    // ------------------------------------------------------------------

    public function index(): void
    {
        $this->render('services/index', [
            'pageTitle' => 'Hizmetler',
            'items'     => Database::all(
                'SELECT s.id, s.slug, s.title, s.image, s.sort_order, s.is_active,
                        (SELECT COUNT(*) FROM service_packages p WHERE p.service_id = s.id) AS package_count
                 FROM services s ORDER BY s.sort_order, s.id'
            ),
        ]);
    }

    public function create(): void
    {
        $this->form(null);
    }

    public function edit(string $id): void
    {
        $this->form($this->findService($id));
    }

    public function store(): void
    {
        $this->save(null);
    }

    public function update(string $id): void
    {
        $this->save($this->findService($id));
    }

    public function delete(string $id): void
    {
        $this->verifyCsrf(self::BASE);
        $service = $this->findService($id);
        $sid     = (int) $service['id'];

        // Paket ve bolumler ON DELETE CASCADE ile de gider; FK'si olmayan eski kurulumlar icin elle de silinir.
        // faqs tablosunda FK yok: bagli sorular elle silinir.
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            Database::run("DELETE FROM faqs WHERE owner_type = 'service' AND owner_id = ?", [$sid]);
            Database::run('DELETE FROM service_packages WHERE service_id = ?', [$sid]);
            Database::run('DELETE FROM service_sections WHERE service_id = ?', [$sid]);
            Database::run('UPDATE testimonials SET service_id = NULL WHERE service_id = ?', [$sid]);
            Database::run('DELETE FROM services WHERE id = ?', [$sid]);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        flash('admin_success', '"' . $service['title'] . '" silindi.');
        redirect(self::BASE);
    }

    /** @param array<string, mixed>|null $row */
    private function form(?array $row): void
    {
        [$values, $errors] = $this->formState($row ?? ['is_active' => 1, 'sort_order' => 0]);
        $data = [
            'pageTitle' => $row ? 'Hizmeti düzenle' : 'Yeni hizmet',
            'row'       => $row,
            'values'    => $values,
            'errors'    => $errors,
            'action'    => $row ? self::BASE . '/' . $row['id'] : self::BASE . '/yeni',
            'packages'  => [],
            'sections'  => [],
            'faqs'      => [],
        ];
        if ($row) {
            $sid = (int) $row['id'];
            $data['packages'] = Database::all('SELECT * FROM service_packages WHERE service_id = ? ORDER BY sort_order, id', [$sid]);
            $data['sections'] = Database::all('SELECT id, anchor, tab_label, title, image, sort_order FROM service_sections WHERE service_id = ? ORDER BY sort_order, id', [$sid]);
            $data['faqs']     = Database::all("SELECT id, question, answer, sort_order FROM faqs WHERE owner_type = 'service' AND owner_id = ? ORDER BY sort_order, id", [$sid]);
        }
        $this->render('services/form', $data);
    }

    /** @param array<string, mixed>|null $row */
    private function save(?array $row): void
    {
        $back = $row ? self::BASE . '/' . $row['id'] : self::BASE . '/yeni';
        $this->verifyCsrf($back);

        $errors = [];
        $title  = $this->textField('title', 200, 'Başlık', true, $errors);
        $slug   = $this->textField('slug', 150, 'Adres', false, $errors);
        $data   = [
            'title'            => $title,
            'summary'          => $this->textField('summary', 500, 'Kısa özet', false, $errors),
            'body'             => $this->postHtml('body'),
            'booking_days'     => $this->textField('booking_days', 150, 'Günler', false, $errors),
            'booking_hours'    => $this->textField('booking_hours', 100, 'Saatler', false, $errors),
            'booking_place'    => $this->textField('booking_place', 150, 'Yer', false, $errors),
            'whatsapp_message' => $this->textField('whatsapp_message', 300, 'WhatsApp mesajı', false, $errors),
            'meta_title'       => $this->textField('meta_title', 200, 'Arama başlığı', false, $errors),
            'meta_description' => $this->textField('meta_description', 300, 'Arama açıklaması', false, $errors),
            'sort_order'       => $this->postInt('sort_order'),
            'is_active'        => $this->postBool('is_active'),
        ];
        $data['image'] = $this->imageAfterValidation('image', $row['image'] ?? null, $errors, (string) $title);

        if ($errors !== []) {
            $this->backWithErrors($back, $errors, $_POST);
        }

        $data['slug'] = $this->uniqueSlug('services', (string) $slug, (string) $title, $row ? (int) $row['id'] : 0);
        $id = $this->write('services', $data, $row ? (int) $row['id'] : null);

        if ($row) {
            flash('admin_success', 'Hizmet kaydedildi.');
            redirect(self::BASE);
        }
        flash('admin_success', 'Hizmet kaydedildi. Şimdi aşağıdan paket, bölüm ve soru ekleyebilirsiniz.');
        redirect(self::BASE . '/' . $id);
    }

    /** @return array<string, mixed> */
    private function findService(string $id): array
    {
        $row = Database::one('SELECT * FROM services WHERE id = ?', [$this->id($id)]);
        if ($row === null) {
            $this->notFound();
        }
        return $row;
    }

    // ------------------------------------------------------------------
    // Alt kayitlar: paket / bolum / sss
    // ------------------------------------------------------------------

    public function childCreate(string $id, string $kind): void
    {
        $service = $this->findService($id);
        $this->childForm($service, $this->kind($kind), $kind, null);
    }

    public function childStore(string $id, string $kind): void
    {
        $service = $this->findService($id);
        $this->childSave($service, $this->kind($kind), $kind, null);
    }

    public function childEdit(string $id, string $kind, string $cid): void
    {
        $service = $this->findService($id);
        $def     = $this->kind($kind);
        $this->childForm($service, $def, $kind, $this->findChild($def, $kind, (int) $service['id'], $cid));
    }

    public function childUpdate(string $id, string $kind, string $cid): void
    {
        $service = $this->findService($id);
        $def     = $this->kind($kind);
        $this->childSave($service, $def, $kind, $this->findChild($def, $kind, (int) $service['id'], $cid));
    }

    public function childDelete(string $id, string $kind, string $cid): void
    {
        $service = $this->findService($id);
        $def     = $this->kind($kind);
        $back    = self::BASE . '/' . $service['id'];
        $this->verifyCsrf($back);
        $child = $this->findChild($def, $kind, (int) $service['id'], $cid);
        Database::run('DELETE FROM ' . $def['table'] . ' WHERE id = ?', [$child['id']]);
        flash('admin_success', $def['deleted']);
        redirect($back . '#' . $def['anchor']);
    }

    /** @return array<string, string> */
    private function kind(string $kind): array
    {
        if (!isset(self::CHILDREN[$kind])) {
            $this->notFound();
        }
        return self::CHILDREN[$kind];
    }

    /** Alt kaydi ust kayda baglayan sutunlar. @return array<string, mixed> */
    private function scope(string $kind, int $serviceId): array
    {
        return $kind === 'sss'
            ? ['owner_type' => 'service', 'owner_id' => $serviceId]
            : ['service_id' => $serviceId];
    }

    /**
     * Alt kayit yalnizca bu hizmete aitse bulunur; aksi halde 404.
     * @param array<string, string> $def
     * @return array<string, mixed>
     */
    private function findChild(array $def, string $kind, int $serviceId, string $cid): array
    {
        $scope = $this->scope($kind, $serviceId);
        $where = implode(' AND ', array_map(static function (string $col): string {
            return $col . ' = ?';
        }, array_keys($scope)));
        $row = Database::one(
            'SELECT * FROM ' . $def['table'] . ' WHERE id = ? AND ' . $where,
            array_merge([$this->id($cid)], array_values($scope))
        );
        if ($row === null) {
            $this->notFound();
        }
        return $row;
    }

    /**
     * @param array<string, mixed>      $service
     * @param array<string, string>     $def
     * @param array<string, mixed>|null $child
     */
    private function childForm(array $service, array $def, string $kind, ?array $child): void
    {
        // Veritabanindaki "7500.00" formda "7500" olarak gorunsun (hatali gonderimden donuste girilen deger korunur)
        if ($kind === 'paket' && $child) {
            $child['price'] = $this->priceInput($child['price']);
        }
        [$values, $errors] = $this->formState($child ?? ['sort_order' => $this->nextSort($def, $kind, (int) $service['id'])]);
        $base = self::BASE . '/' . $service['id'];
        $this->render('services/' . $def['view'], [
            'pageTitle' => $child ? $def['edit'] : $def['new'],
            'service'   => $service,
            'child'     => $child,
            'values'    => $values,
            'errors'    => $errors,
            'action'    => $base . '/' . $kind . '/' . ($child ? $child['id'] : 'yeni'),
            'backUrl'   => $base . '#' . $def['anchor'],
        ]);
    }

    /**
     * @param array<string, mixed>      $service
     * @param array<string, string>     $def
     * @param array<string, mixed>|null $child
     */
    private function childSave(array $service, array $def, string $kind, ?array $child): void
    {
        $serviceId = (int) $service['id'];
        $back      = self::BASE . '/' . $serviceId . '/' . $kind . '/' . ($child ? $child['id'] : 'yeni');
        $this->verifyCsrf($back);

        $errors = [];
        if ($kind === 'paket') {
            $data = $this->packageInput($errors);
        } elseif ($kind === 'bolum') {
            $data = $this->sectionInput($serviceId, $child, $errors);
        } else {
            $data = $this->faqInput($errors);
        }

        if ($errors !== []) {
            $this->backWithErrors($back, $errors, $_POST);
        }

        $this->write($def['table'], $child ? $data : $this->scope($kind, $serviceId) + $data, $child ? (int) $child['id'] : null);
        flash('admin_success', $def['saved']);
        redirect(self::BASE . '/' . $serviceId . '#' . $def['anchor']);
    }

    /**
     * @param array<string, string> $errors
     * @return array<string, mixed>
     */
    private function packageInput(array &$errors): array
    {
        $price = $this->postPrice('price');
        if ($price === false) {
            $errors['price'] = 'Fiyatı yalnızca rakamla yazın: 7500 ya da 7.500,50 gibi.';
        }
        $url = $this->postLink('button_url');
        if ($url === null) {
            $errors['button_url'] = 'Bağlantı https:// ile ya da site içi bir yol için / ile başlamalı (en fazla 255 karakter, boşluksuz).';
        }
        return [
            'name'        => $this->textField('name', 150, 'Paket adı', true, $errors),
            'price'       => $price === false ? null : $price,
            'description' => $this->textField('description', 500, 'Açıklama', false, $errors),
            'button_text' => $this->textField('button_text', 100, 'Buton yazısı', false, $errors),
            'button_url'  => $url === '' ? null : $url,
            'sort_order'  => $this->postInt('sort_order'),
        ];
    }

    /**
     * @param array<string, mixed>|null $child
     * @param array<string, string>     $errors
     * @return array<string, mixed>
     */
    private function sectionInput(int $serviceId, ?array $child, array &$errors): array
    {
        $tabLabel = $this->textField('tab_label', 100, 'Sekme adı', true, $errors);
        $title    = $this->textField('title', 200, 'Başlık', true, $errors);

        // Capa: bos ise sekme adindan uretilir ve gerekirse -2, -3 eklenir; elle yazildiysa bicimi ve benzersizligi denetlenir.
        $anchorRaw = trim($this->post('anchor'));
        $exceptId  = $child ? (int) $child['id'] : 0;
        $anchor    = '';
        if ($anchorRaw === '') {
            $base = substr(slugify((string) $tabLabel), 0, 45);
            if ($base === '' || in_array($base, self::RESERVED_ANCHORS, true)) {
                $base = $base === '' ? 'bolum' : $base . '-bolum';
            }
            $anchor = $base;
            for ($i = 2; $this->anchorTaken($serviceId, $anchor, $exceptId); $i++) {
                $anchor = $base . '-' . $i;
            }
        } elseif (strlen($anchorRaw) > 50) {
            $errors['anchor'] = 'Çapa en fazla 50 karakter olabilir.';
        } elseif (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $anchorRaw)) {
            $errors['anchor'] = 'Yalnızca küçük harf (Türkçe karakter olmadan), rakam ve tire kullanın. Örnek: kadinlar-icin';
        } elseif (in_array($anchorRaw, self::RESERVED_ANCHORS, true)) {
            $errors['anchor'] = '"' . $anchorRaw . '" sayfada başka bir bölümde kullanılıyor; başka bir çapa yazın.';
        } elseif ($this->anchorTaken($serviceId, $anchorRaw, $exceptId)) {
            $errors['anchor'] = 'Bu hizmette aynı çapayı kullanan başka bir bölüm var.';
        } else {
            $anchor = $anchorRaw;
        }

        $data = [
            'tab_label'   => $tabLabel,
            'anchor'      => $anchor,
            'eyebrow'     => $this->textField('eyebrow', 150, 'Üst başlık', false, $errors),
            'title'       => $title,
            'body'        => $this->postHtml('body'),
            'focus_title' => $this->textField('focus_title', 200, 'Odak başlığı', false, $errors),
            'focus_items' => $this->focusItems($errors),
            'sort_order'  => $this->postInt('sort_order'),
        ];
        $data['image'] = $this->imageAfterValidation('image', $child['image'] ?? null, $errors, (string) $title);
        return $data;
    }

    private function anchorTaken(int $serviceId, string $anchor, int $exceptId): bool
    {
        return Database::one(
            'SELECT id FROM service_sections WHERE service_id = ? AND anchor = ? AND id <> ?',
            [$serviceId, $anchor, $exceptId]
        ) !== null;
    }

    /** Her satir bir madde; bos satirlar atilir. @param array<string, string> $errors */
    private function focusItems(array &$errors): ?string
    {
        $lines = preg_split('/\R/', $this->post('focus_items')) ?: [];
        $items = array_values(array_filter(array_map('trim', $lines), static function (string $l): bool {
            return $l !== '';
        }));
        foreach ($items as $item) {
            if (mb_strlen($item) > 300) {
                $errors['focus_items'] = 'Her madde en fazla 300 karakter olabilir.';
                break;
            }
        }
        $text = implode("\n", $items);
        if (mb_strlen($text) > 5000) {
            $errors['focus_items'] = 'Maddelerin toplamı en fazla 5000 karakter olabilir.';
        }
        return $text === '' ? null : $text;
    }

    /**
     * @param array<string, string> $errors
     * @return array<string, mixed>
     */
    private function faqInput(array &$errors): array
    {
        return [
            'question'   => $this->textField('question', 500, 'Soru', true, $errors),
            'answer'     => $this->textField('answer', 5000, 'Cevap', true, $errors),
            'sort_order' => $this->postInt('sort_order'),
        ];
    }

    /** @param array<string, string> $def */
    private function nextSort(array $def, string $kind, int $serviceId): int
    {
        $scope = $this->scope($kind, $serviceId);
        $where = implode(' AND ', array_map(static function (string $col): string {
            return $col . ' = ?';
        }, array_keys($scope)));
        $row = Database::one('SELECT MAX(sort_order) AS m FROM ' . $def['table'] . ' WHERE ' . $where, array_values($scope));
        return $row && $row['m'] !== null ? (int) $row['m'] + 1 : 1;
    }

    // ------------------------------------------------------------------
    // Ortak yardimcilar
    // ------------------------------------------------------------------

    /**
     * Kirpilmis metin; bos ise null. Zorunlu alan bossa ya da sinir asildiysa $errors doldurulur.
     * @param array<string, string> $errors
     */
    private function textField(string $key, int $max, string $label, bool $required, array &$errors): ?string
    {
        $value = $this->postText($key, $max);
        if ($value === null) {
            $errors[$key] = $label . ' en fazla ' . $max . ' karakter olabilir (şu an ' . mb_strlen(trim($this->post($key))) . ').';
            return null;
        }
        if ($value === '') {
            if ($required) {
                $errors[$key] = $label . ' boş bırakılamaz.';
            }
            return null;
        }
        return $value;
    }

    /**
     * Gorsel yalnizca diger alanlar gecerliyse islenir (hatali formda yuklenen dosya bosa gitmesin).
     * @param array<string, string> $errors
     */

    /** "7500.00" -> "7500", "7500.50" -> "7500,50" (formda gosterim icin) */
    private function priceInput($price): string
    {
        $s = (string) ($price ?? '');
        if ($s === '') {
            return '';
        }
        return str_replace('.', ',', preg_replace('/\.00$/', '', $s) ?? $s);
    }

    /**
     * @param array<string, mixed> $data sutun => deger
     * @return int kaydin id'si
     */
    private function write(string $table, array $data, ?int $id): int
    {
        $data['updated_at'] = $this->now();
        $cols = array_keys($data);
        if ($id !== null) {
            $set = implode(', ', array_map(static function (string $c): string {
                return $c . ' = ?';
            }, $cols));
            Database::run('UPDATE ' . $table . ' SET ' . $set . ' WHERE id = ?', array_merge(array_values($data), [$id]));
            return $id;
        }
        $data['created_at'] = $this->now();
        $cols = array_keys($data);
        Database::run(
            'INSERT INTO ' . $table . ' (' . implode(', ', $cols) . ') VALUES (' . implode(', ', array_fill(0, count($cols), '?')) . ')',
            array_values($data)
        );
        return Database::lastId();
    }
}
