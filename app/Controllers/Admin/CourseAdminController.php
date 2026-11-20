<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;

/**
 * Egitimler: liste -> yeni/duzenle formu -> sil.
 * Duzenleme sayfasinda alt kayitlar: moduller (course_modules), katilim secenekleri (course_plans),
 * SSS (faqs, owner_type='course'). Alt kayit adresleri: /yonetim/egitimler/{id}/{tur}/yeni|{cid}|{cid}/sil
 * Rotalar: config/routes/admin_egitimler.php  Gorunumler: views/admin/courses/
 */
final class CourseAdminController extends AdminController
{
    private const BASE = '/yonetim/egitimler';

    /**
     * Alt kayit turleri. Adresteki {kind} bu anahtarlardan biri olmali.
     * @var array<string, array<string, string>>
     */
    private const CHILDREN = [
        'modul'   => ['table' => 'course_modules', 'view' => 'module', 'anchor' => 'moduller', 'new' => 'Yeni modül', 'edit' => 'Modülü düzenle', 'saved' => 'Modül kaydedildi.', 'deleted' => 'Modül silindi.'],
        'secenek' => ['table' => 'course_plans', 'view' => 'plan', 'anchor' => 'secenekler', 'new' => 'Yeni katılım seçeneği', 'edit' => 'Katılım seçeneğini düzenle', 'saved' => 'Katılım seçeneği kaydedildi.', 'deleted' => 'Katılım seçeneği silindi.'],
        'sss'     => ['table' => 'faqs', 'view' => 'faq', 'anchor' => 'sss', 'new' => 'Yeni soru', 'edit' => 'Soruyu düzenle', 'saved' => 'Soru kaydedildi.', 'deleted' => 'Soru silindi.'],
    ];

    // ------------------------------------------------------------------
    // Egitim
    // ------------------------------------------------------------------

    public function index(): void
    {
        $this->render('courses/index', [
            'pageTitle' => 'Eğitimler',
            'items'     => Database::all(
                'SELECT c.id, c.slug, c.title, c.start_date, c.countdown_until, c.sort_order, c.is_active,
                        (SELECT COUNT(*) FROM course_plans p WHERE p.course_id = c.id) AS plan_count
                 FROM courses c ORDER BY c.sort_order, c.id'
            ),
        ]);
    }

    public function create(): void
    {
        $this->form(null);
    }

    public function edit(string $id): void
    {
        $this->form($this->findCourse($id));
    }

    public function store(): void
    {
        $this->save(null);
    }

    public function update(string $id): void
    {
        $this->save($this->findCourse($id));
    }

    public function delete(string $id): void
    {
        $this->verifyCsrf(self::BASE);
        $course = $this->findCourse($id);
        $cid    = (int) $course['id'];

        // Modul ve secenekler ON DELETE CASCADE ile de gider; FK'si olmayan eski kurulumlar icin elle de silinir.
        // faqs tablosunda FK yok: bagli sorular elle silinir.
        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            Database::run("DELETE FROM faqs WHERE owner_type = 'course' AND owner_id = ?", [$cid]);
            Database::run('DELETE FROM course_modules WHERE course_id = ?', [$cid]);
            Database::run('DELETE FROM course_plans WHERE course_id = ?', [$cid]);
            Database::run('DELETE FROM courses WHERE id = ?', [$cid]);
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        flash('admin_success', '"' . $course['title'] . '" silindi.');
        redirect(self::BASE);
    }

    /** @param array<string, mixed>|null $row */
    private function form(?array $row): void
    {
        [$values, $errors] = $this->formState($row ?? ['is_active' => 1, 'sort_order' => 0]);
        $data = [
            'pageTitle' => $row ? 'Eğitimi düzenle' : 'Yeni eğitim',
            'row'       => $row,
            'values'    => $values,
            'errors'    => $errors,
            'action'    => $row ? self::BASE . '/' . $row['id'] : self::BASE . '/yeni',
            'modules'   => [],
            'plans'     => [],
            'faqs'      => [],
            'isPromoted' => false,
        ];
        if ($row) {
            $cid = (int) $row['id'];
            $data['modules'] = Database::all('SELECT id, title, description, sort_order FROM course_modules WHERE course_id = ? ORDER BY sort_order, id', [$cid]);
            $data['plans']   = Database::all('SELECT * FROM course_plans WHERE course_id = ? ORDER BY sort_order, id', [$cid]);
            $data['faqs']    = Database::all("SELECT id, question, answer, sort_order FROM faqs WHERE owner_type = 'course' AND owner_id = ? ORDER BY sort_order, id", [$cid]);
            $data['isPromoted'] = $cid === $this->promotedCourseId();
        }
        $this->render('courses/form', $data);
    }

    /** Ana sayfadaki egitim duyurusunda gosterilen egitim: siradaki ilk aktif egitim. */
    private function promotedCourseId(): int
    {
        $row = Database::one('SELECT id FROM courses WHERE is_active = 1 ORDER BY sort_order, id LIMIT 1');
        return $row ? (int) $row['id'] : 0;
    }

    /** @param array<string, mixed>|null $row */
    private function save(?array $row): void
    {
        $back = $row ? self::BASE . '/' . $row['id'] : self::BASE . '/yeni';
        $this->verifyCsrf($back);

        $errors = [];
        $title  = $this->textField('title', 200, 'Başlık', true, $errors);
        $slug   = $this->textField('slug', 150, 'Adres', false, $errors);

        $startDate = $this->postDate('start_date');
        if ($startDate === false) {
            $errors['start_date'] = 'Tarihi gün/ay/yıl olarak seçin (örnek: 2026-12-01).';
        }
        $countdown = $this->postDate('countdown_until', true);
        if ($countdown === false) {
            $errors['countdown_until'] = 'Tarih ve saati seçin (örnek: 2026-12-01 20:00).';
        }

        $data = [
            'title'            => $title,
            'summary'          => $this->textField('summary', 500, 'Kısa özet', false, $errors),
            'body'             => $this->postHtml('body'),
            'start_date'       => $startDate === false ? null : $startDate,
            'countdown_until'  => $countdown === false ? null : $countdown,
            'meta_title'       => $this->textField('meta_title', 200, 'Arama başlığı', false, $errors),
            'meta_description' => $this->textField('meta_description', 300, 'Arama açıklaması', false, $errors),
            'sort_order'       => $this->postInt('sort_order'),
            'is_active'        => $this->postBool('is_active'),
        ];
        $data['image'] = $this->imageAfterValidation('image', $row['image'] ?? null, $errors, (string) $title);

        if ($errors !== []) {
            $this->backWithErrors($back, $errors, $_POST);
        }

        $data['slug'] = $this->uniqueSlug('courses', (string) $slug, (string) $title, $row ? (int) $row['id'] : 0);
        $id = $this->write('courses', $data, $row ? (int) $row['id'] : null);

        if ($row) {
            flash('admin_success', 'Eğitim kaydedildi.');
            redirect(self::BASE);
        }
        flash('admin_success', 'Eğitim kaydedildi. Şimdi aşağıdan modül, katılım seçeneği ve soru ekleyebilirsiniz.');
        redirect(self::BASE . '/' . $id);
    }

    /** @return array<string, mixed> */
    private function findCourse(string $id): array
    {
        $row = Database::one('SELECT * FROM courses WHERE id = ?', [$this->id($id)]);
        if ($row === null) {
            $this->notFound();
        }
        return $row;
    }

    // ------------------------------------------------------------------
    // Alt kayitlar: modul / secenek / sss
    // ------------------------------------------------------------------

    public function childCreate(string $id, string $kind): void
    {
        $this->childForm($this->findCourse($id), $this->kind($kind), $kind, null);
    }

    public function childStore(string $id, string $kind): void
    {
        $this->childSave($this->findCourse($id), $this->kind($kind), $kind, null);
    }

    public function childEdit(string $id, string $kind, string $cid): void
    {
        $course = $this->findCourse($id);
        $def    = $this->kind($kind);
        $this->childForm($course, $def, $kind, $this->findChild($def, $kind, (int) $course['id'], $cid));
    }

    public function childUpdate(string $id, string $kind, string $cid): void
    {
        $course = $this->findCourse($id);
        $def    = $this->kind($kind);
        $this->childSave($course, $def, $kind, $this->findChild($def, $kind, (int) $course['id'], $cid));
    }

    public function childDelete(string $id, string $kind, string $cid): void
    {
        $course = $this->findCourse($id);
        $def    = $this->kind($kind);
        $back   = self::BASE . '/' . $course['id'];
        $this->verifyCsrf($back);
        $child = $this->findChild($def, $kind, (int) $course['id'], $cid);
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
    private function scope(string $kind, int $courseId): array
    {
        return $kind === 'sss'
            ? ['owner_type' => 'course', 'owner_id' => $courseId]
            : ['course_id' => $courseId];
    }

    /**
     * Alt kayit yalnizca bu egitime aitse bulunur; aksi halde 404.
     * @param array<string, string> $def
     * @return array<string, mixed>
     */
    private function findChild(array $def, string $kind, int $courseId, string $cid): array
    {
        $scope = $this->scope($kind, $courseId);
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
     * @param array<string, mixed>      $course
     * @param array<string, string>     $def
     * @param array<string, mixed>|null $child
     */
    private function childForm(array $course, array $def, string $kind, ?array $child): void
    {
        // Veritabanindaki "7500.00" formda "7500" olarak gorunsun
        if ($kind === 'secenek' && $child) {
            $child['price']      = $this->priceInput($child['price']);
            $child['list_price'] = $this->priceInput($child['list_price']);
        }
        [$values, $errors] = $this->formState($child ?? ['sort_order' => $this->nextSort($def, $kind, (int) $course['id'])]);
        $base = self::BASE . '/' . $course['id'];
        $this->render('courses/' . $def['view'], [
            'pageTitle' => $child ? $def['edit'] : $def['new'],
            'course'    => $course,
            'child'     => $child,
            'values'    => $values,
            'errors'    => $errors,
            'action'    => $base . '/' . $kind . '/' . ($child ? $child['id'] : 'yeni'),
            'backUrl'   => $base . '#' . $def['anchor'],
        ]);
    }

    /**
     * @param array<string, mixed>      $course
     * @param array<string, string>     $def
     * @param array<string, mixed>|null $child
     */
    private function childSave(array $course, array $def, string $kind, ?array $child): void
    {
        $courseId = (int) $course['id'];
        $back     = self::BASE . '/' . $courseId . '/' . $kind . '/' . ($child ? $child['id'] : 'yeni');
        $this->verifyCsrf($back);

        $errors = [];
        if ($kind === 'modul') {
            $data = $this->moduleInput($errors);
        } elseif ($kind === 'secenek') {
            $data = $this->planInput($errors);
        } else {
            $data = $this->faqInput($errors);
        }

        if ($errors !== []) {
            $this->backWithErrors($back, $errors, $_POST);
        }

        $this->write($def['table'], $child ? $data : $this->scope($kind, $courseId) + $data, $child ? (int) $child['id'] : null);
        flash('admin_success', $def['saved']);
        redirect(self::BASE . '/' . $courseId . '#' . $def['anchor']);
    }

    /**
     * @param array<string, string> $errors
     * @return array<string, mixed>
     */
    private function moduleInput(array &$errors): array
    {
        return [
            'title'       => $this->textField('title', 200, 'Başlık', true, $errors),
            'description' => $this->textField('description', 5000, 'Açıklama', false, $errors),
            'sort_order'  => $this->postInt('sort_order'),
        ];
    }

    /**
     * @param array<string, string> $errors
     * @return array<string, mixed>
     */
    private function planInput(array &$errors): array
    {
        $price = $this->postPrice('price');
        if ($price === false) {
            $errors['price'] = 'Fiyatı yalnızca rakamla yazın: 7500 ya da 7.500,50 gibi.';
        }
        $listPrice = $this->postPrice('list_price');
        if ($listPrice === false) {
            $errors['list_price'] = 'Fiyatı yalnızca rakamla yazın: 7500 ya da 7.500,50 gibi.';
        }
        // Uzeri cizili liste fiyati indirimli fiyattan buyuk olmali, yoksa sitede anlamsiz gorunur.
        if ($price !== false && $listPrice !== false && $price !== null && $listPrice !== null
            && (float) $listPrice <= (float) $price) {
            $errors['list_price'] = 'Liste fiyatı indirimli fiyattan büyük olmalı. Üzeri çizili fiyat göstermek istemiyorsanız bu alanı boş bırakın.';
        }
        if ($price === null && $listPrice !== false && $listPrice !== null) {
            $errors['price'] = 'Liste fiyatı girdiyseniz indirimli fiyatı da yazın (yoksa liste fiyatını boş bırakın).';
        }
        $url = $this->postLink('button_url');
        if ($url === null) {
            $errors['button_url'] = 'Bağlantı https:// ile ya da site içi bir yol için / ile başlamalı (en fazla 255 karakter, boşluksuz).';
        }
        return [
            'name'        => $this->textField('name', 150, 'Seçenek adı', true, $errors),
            'list_price'  => $listPrice === false ? null : $listPrice,
            'price'       => $price === false ? null : $price,
            'description' => $this->textField('description', 500, 'Açıklama', false, $errors),
            'button_text' => $this->textField('button_text', 100, 'Buton yazısı', false, $errors),
            'button_url'  => $url === '' ? null : $url,
            'sort_order'  => $this->postInt('sort_order'),
        ];
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
    private function nextSort(array $def, string $kind, int $courseId): int
    {
        $scope = $this->scope($kind, $courseId);
        $where = implode(' AND ', array_map(static function (string $col): string {
            return $col . ' = ?';
        }, array_keys($scope)));
        $row = Database::one('SELECT MAX(sort_order) AS m FROM ' . $def['table'] . ' WHERE ' . $where, array_values($scope));
        return $row && $row['m'] !== null ? (int) $row['m'] + 1 : 1;
    }

    // ------------------------------------------------------------------
    // Ortak yardimcilar (ServiceAdminController ile ayni kalip)
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
