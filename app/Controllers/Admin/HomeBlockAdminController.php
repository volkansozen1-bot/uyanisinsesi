<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;

/**
 * Ana sayfa bloklari (home_blocks) ve "Rakamlar" blogunun sayilari (stats).
 * Her blok turu views/blocks/<tur>.php ile basilir; formda yalnizca o turun kullandigi alanlar gosterilir.
 * Rotalar: config/routes/admin_ana_sayfa.php  Gorunumler: views/admin/home/
 */
final class HomeBlockAdminController extends AdminController
{
    private const BASE = '/yonetim/ana-sayfa';

    /**
     * Blok turleri. fields: form alani adi => tanim
     *   type: text | textarea | link | image | lines (her satir bir oge; dizi olarak saklanir)
     *   extra: true ise extra_json icinde saklanir (form adi "x_<anahtar>")
     *   pair: buton baglantisi icin eslesen buton yazisi alani (biri doluysa digeri de dolu olmali)
     */
    public const TYPES = [
        'hero' => [
            'label' => 'Giriş (hero)',
            'desc'  => 'Sayfanın en üstündeki karşılama alanı. Üstteki küçük yazı site adıdır (Ayarlar\'dan değişir).',
            'fields' => [
                'title'          => ['label' => 'Büyük başlık', 'type' => 'text', 'max' => 255, 'required' => true, 'wide' => true],
                'subtitle'       => ['label' => 'Alt metin', 'type' => 'textarea', 'max' => 500, 'help' => 'Başlığın altındaki bir iki cümlelik açıklama.'],
                'button_text'    => ['label' => 'Buton yazısı', 'type' => 'text', 'max' => 100, 'help' => 'Boşsa "Çalışmaları keşfet" yazar.'],
                'button_url'     => ['label' => 'Buton bağlantısı', 'type' => 'link', 'help' => 'Boşsa /hizmetler sayfasına gider.'],
                'x_button2_text' => ['label' => 'İkinci bağlantının yazısı', 'type' => 'text', 'max' => 100, 'help' => 'Butonun yanındaki ince bağlantı. Boşsa "İletişime geç" yazar.'],
                'x_button2_url'  => ['label' => 'İkinci bağlantının adresi', 'type' => 'link', 'help' => 'Boşsa /iletisim sayfasına gider.'],
                'image'          => ['label' => 'Pencere içindeki fotoğraf', 'type' => 'image', 'help' => 'Sağdaki yuvarlak pencerede görünür. Yüklenmezse ay çizimi gösterilir. Kare ya da dikey fotoğraf uygundur.'],
            ],
        ],
        'certificates' => [
            'label' => 'Sertifikalar (QR kodlu)',
            'desc'  => 'QR kodlu sertifika kartı. QR ve bağlantı boşsa sitedeki Sertifikalarım sayfası (/sertifikalar) kullanılır.',
            'fields' => [
                'title'       => ['label' => 'Başlık', 'type' => 'text', 'max' => 255, 'required' => true],
                'subtitle'    => ['label' => 'Açıklama', 'type' => 'textarea', 'max' => 600, 'rows' => 3],
                'image'       => ['label' => 'QR kod görseli', 'type' => 'image', 'help' => 'Boş bırakın: /sertifikalar sayfasına giden hazır QR kodu kullanılır.'],
                'button_text' => ['label' => 'Buton yazısı', 'type' => 'text', 'max' => 100, 'help' => 'Boşsa: Tüm Sertifikaları Gör'],
                'button_url'  => ['label' => 'Buton bağlantısı', 'type' => 'link', 'help' => 'Boşsa: /sertifikalar'],
            ],
        ],
        'hero_full' => [
            'label' => 'Tam ekran giriş (fotoğraflı)',
            'desc'  => 'Ekranı kaplayan büyük fotoğraf, üzerinde ortalanmış büyük bir söz ve buton. Kullanırsanız eski "Giriş (hero)" bloğunu gizleyin.',
            'fields' => [
                'image'       => ['label' => 'Arka plan fotoğrafı', 'type' => 'image', 'help' => 'Yatay ve en az 1920 px genişlikte bir fotoğraf seçin. Önemli kısım ortada ya da üstte olsun; yazı alt kısma gelir.'],
                'subtitle'    => ['label' => 'Büyük söz', 'type' => 'textarea', 'max' => 300, 'required' => true, 'rows' => 3, 'help' => 'Fotoğrafın üzerinde büyük ve italik yazılır. Tırnak işaretleri otomatik eklenir.'],
                'title'       => ['label' => 'Söyleyen / kaynak', 'type' => 'text', 'max' => 255, 'help' => 'İsteğe bağlı. Sözün altında yazılır (örnek: Mevlânâ).'],
                'button_text' => ['label' => 'Buton yazısı', 'type' => 'text', 'max' => 100],
                'button_url'  => ['label' => 'Buton bağlantısı', 'type' => 'link', 'pair' => 'button_text'],
            ],
        ],
        'intro' => [
            'label' => 'Karşılama',
            'desc'  => 'Ortalanmış başlık, kısa metin ve isteğe bağlı buton.',
            'fields' => [
                'subtitle'    => ['label' => 'Üst küçük yazı', 'type' => 'text', 'max' => 150, 'help' => 'Başlığın üstünde altın renkli küçük yazı. Örnek: Hoş geldin'],
                'title'       => ['label' => 'Başlık', 'type' => 'text', 'max' => 255, 'required' => true],
                'body'        => ['label' => 'Metin', 'type' => 'textarea', 'max' => 3000, 'rows' => 5],
                'button_text' => ['label' => 'Buton yazısı', 'type' => 'text', 'max' => 100],
                'button_url'  => ['label' => 'Buton bağlantısı', 'type' => 'link', 'pair' => 'button_text'],
            ],
        ],
        'quote' => [
            'label' => 'Alıntı',
            'desc'  => 'Koyu şerit üzerinde büyük yazılmış tek bir söz.',
            'fields' => [
                'subtitle' => ['label' => 'Alıntı metni', 'type' => 'textarea', 'max' => 500, 'required' => true, 'rows' => 3],
                'title'    => ['label' => 'Söyleyen / kaynak', 'type' => 'text', 'max' => 255, 'help' => 'İsteğe bağlı. Alıntının altında küçük yazılır.'],
            ],
        ],
        'story' => [
            'label' => 'Hikâye',
            'desc'  => 'Fotoğraf, başlık, metin ve buton. Fotoğraf yüklenmezse metin tek sütun olur.',
            'fields' => [
                'subtitle'    => ['label' => 'Üst küçük yazı', 'type' => 'text', 'max' => 150, 'help' => 'Örnek: Hikâyem'],
                'title'       => ['label' => 'Başlık', 'type' => 'text', 'max' => 255, 'required' => true],
                'body'        => ['label' => 'Metin', 'type' => 'textarea', 'max' => 3000, 'rows' => 6],
                'button_text' => ['label' => 'Buton yazısı', 'type' => 'text', 'max' => 100],
                'button_url'  => ['label' => 'Buton bağlantısı', 'type' => 'link', 'pair' => 'button_text'],
                'image'       => ['label' => 'Fotoğraf', 'type' => 'image'],
            ],
        ],
        'services' => [
            'label' => 'Hizmetler',
            'desc'  => 'Sitede gösterilen hizmetlerin kartları (Hizmetler bölümünden yönetilir).',
            'fields' => [
                'subtitle' => ['label' => 'Üst küçük yazı', 'type' => 'text', 'max' => 150],
                'title'    => ['label' => 'Başlık', 'type' => 'text', 'max' => 255],
            ],
        ],
        'course_promo' => [
            'label' => 'Eğitim duyurusu',
            'desc'  => 'Sıralamada ilk gelen aktif eğitimin duyurusu ve geri sayım (Eğitimler bölümünden yönetilir).',
            'fields' => [
                'subtitle'    => ['label' => 'Üst küçük yazı', 'type' => 'text', 'max' => 150, 'help' => 'Boşsa "Yeni dönem" yazar.'],
                'title'       => ['label' => 'Başlık', 'type' => 'text', 'max' => 255, 'help' => 'Boşsa eğitimin adı kullanılır.'],
                'body'        => ['label' => 'Tanıtım metni', 'type' => 'textarea', 'max' => 1000, 'rows' => 4, 'help' => 'Boşsa eğitimin kendi özeti kullanılır.'],
                'button_text' => ['label' => 'Buton yazısı', 'type' => 'text', 'max' => 100, 'help' => 'Boşsa "Eğitimi İncele" yazar.'],
                'button_url'  => ['label' => 'Buton bağlantısı', 'type' => 'link', 'help' => 'Boşsa eğitimin sayfasına gider.'],
                'image'       => ['label' => 'Fotoğraf', 'type' => 'image', 'help' => 'Yüklenirse duyurunun yanında görünür.'],
            ],
        ],
        'testimonials' => [
            'label' => 'Yorumlar',
            'desc'  => 'Katılımcı yorumları (Yorumlar bölümünden yönetilir, en fazla 6).',
            'fields' => [
                'subtitle' => ['label' => 'Üst küçük yazı', 'type' => 'text', 'max' => 150],
                'title'    => ['label' => 'Başlık', 'type' => 'text', 'max' => 255],
            ],
        ],
        'stats' => [
            'label' => 'Rakamlar',
            'desc'  => 'Sayma animasyonlu rakamlar (örnek: 10+ yıllık deneyim).',
            'fields' => [
                'title' => ['label' => 'Başlık', 'type' => 'text', 'max' => 255],
            ],
        ],
        'latest_posts' => [
            'label' => 'Son yazılar',
            'desc'  => 'Blogdaki en yeni 3 yazı.',
            'fields' => [
                'subtitle' => ['label' => 'Üst küçük yazı', 'type' => 'text', 'max' => 150],
                'title'    => ['label' => 'Başlık', 'type' => 'text', 'max' => 255],
            ],
        ],
    ];

    /** home_blocks tablosundaki sutunlar (geri kalan alanlar extra_json'a gider) */
    private const COLUMNS = ['title', 'subtitle', 'body', 'image', 'button_text', 'button_url'];

    /** Tanimsiz eski turler icin genel alanlar */
    private const FALLBACK = [
        'label' => '',
        'desc'  => 'Bu blok türünün özel bir düzenleme formu yok.',
        'fields' => [
            'subtitle' => ['label' => 'Alt başlık', 'type' => 'text', 'max' => 500],
            'title'    => ['label' => 'Başlık', 'type' => 'text', 'max' => 255],
            'body'     => ['label' => 'Metin', 'type' => 'textarea', 'max' => 3000],
        ],
    ];

    /** @return array<string, mixed> */
    public static function typeInfo(string $type): array
    {
        return self::TYPES[$type] ?? ['label' => $type] + self::FALLBACK;
    }

    public function index(): void
    {
        $this->render('home/index', [
            'pageTitle' => 'Ana Sayfa',
            'items'     => Database::all('SELECT * FROM home_blocks ORDER BY sort_order, id'),
        ]);
    }

    /** Once tur secimi, sonra o turun formu (?tur=hero) */
    public function create(): void
    {
        $type = $this->query('tur');
        if (!isset(self::TYPES[$type])) {
            $this->render('home/choose', ['pageTitle' => 'Yeni blok', 'types' => self::TYPES]);
            return;
        }
        $this->form(null, $type);
    }

    public function edit(string $id): void
    {
        $row = $this->find($id);
        $this->form($row, (string) $row['type']);
    }

    public function store(): void
    {
        $type = $this->post('type');
        if (!isset(self::TYPES[$type])) {
            $this->verifyCsrf(self::BASE . '/yeni');
            flash('admin_error', 'Geçerli bir blok türü seçin.');
            redirect(self::BASE . '/yeni');
        }
        $this->save(null, $type);
    }

    public function update(string $id): void
    {
        $row = $this->find($id);
        $this->save($row, (string) $row['type']);
    }

    public function delete(string $id): void
    {
        $this->verifyCsrf(self::BASE);
        $row = $this->find($id);
        Database::run('DELETE FROM home_blocks WHERE id = ?', [$row['id']]);
        flash('admin_success', 'Blok silindi.');
        redirect(self::BASE);
    }

    public function toggle(string $id): void
    {
        $this->verifyCsrf(self::BASE);
        $row = $this->find($id);
        $active = $row['is_active'] ? 0 : 1;
        Database::run('UPDATE home_blocks SET is_active = ?, updated_at = ? WHERE id = ?', [$active, $this->now(), $row['id']]);
        flash('admin_success', $active ? 'Blok sitede gösteriliyor.' : 'Blok gizlendi.');
        redirect(self::BASE);
    }

    public function moveUp(string $id): void
    {
        $this->move($id, -1);
    }

    public function moveDown(string $id): void
    {
        $this->move($id, 1);
    }

    /**
     * Blogu komsusuyla yer degistirir. Siralar esit olabilecegi icin tum bloklar 10, 20, 30... diye
     * yeniden numaralanir; boylece takas her zaman gorunur bir sonuc verir.
     */
    private function move(string $id, int $dir): void
    {
        $this->verifyCsrf(self::BASE);
        $row = $this->find($id);
        $ids = array_map('intval', array_column(Database::all('SELECT id FROM home_blocks ORDER BY sort_order, id'), 'id'));
        $pos = array_search((int) $row['id'], $ids, true);
        $to  = $pos + $dir;
        if ($pos === false || $to < 0 || $to >= count($ids)) {
            redirect(self::BASE);
        }
        [$ids[$pos], $ids[$to]] = [$ids[$to], $ids[$pos]];

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            foreach ($ids as $i => $blockId) {
                Database::run('UPDATE home_blocks SET sort_order = ? WHERE id = ?', [($i + 1) * 10, $blockId]);
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
        redirect(self::BASE . '#blok-' . $row['id']);
    }

    // ------------------------------------------------------------------
    // Rakamlar (stats) - yalnizca "stats" turundeki blogun sayfasindan
    // ------------------------------------------------------------------

    public function statCreate(string $id): void
    {
        $this->statForm($this->findStatsBlock($id), null);
    }

    public function statEdit(string $id, string $sid): void
    {
        $block = $this->findStatsBlock($id);
        $this->statForm($block, $this->findStat($sid));
    }

    public function statStore(string $id): void
    {
        $this->statSave($this->findStatsBlock($id), null);
    }

    public function statUpdate(string $id, string $sid): void
    {
        $block = $this->findStatsBlock($id);
        $this->statSave($block, $this->findStat($sid));
    }

    public function statDelete(string $id, string $sid): void
    {
        $block = $this->findStatsBlock($id);
        $back  = self::BASE . '/' . $block['id'];
        $this->verifyCsrf($back);
        $stat = $this->findStat($sid);
        Database::run('DELETE FROM stats WHERE id = ?', [$stat['id']]);
        flash('admin_success', 'Rakam silindi.');
        redirect($back);
    }

    /** @param array<string, mixed> $block @param array<string, mixed>|null $stat */
    private function statForm(array $block, ?array $stat): void
    {
        $base = self::BASE . '/' . $block['id'] . '/rakam';
        [$values, $errors] = $this->formState($stat ?? ['sort_order' => $this->nextOrder('stats'), 'suffix' => '+']);
        $this->render('home/stat_form', [
            'pageTitle' => $stat ? 'Rakamı düzenle' : 'Yeni rakam',
            'block'     => $block,
            'stat'      => $stat,
            'values'    => $values,
            'errors'    => $errors,
            'action'    => $stat ? $base . '/' . $stat['id'] : $base . '/yeni',
        ]);
    }

    /** @param array<string, mixed> $block @param array<string, mixed>|null $stat */
    private function statSave(array $block, ?array $stat): void
    {
        $base = self::BASE . '/' . $block['id'] . '/rakam';
        $back = $stat ? $base . '/' . $stat['id'] : $base . '/yeni';
        $this->verifyCsrf($back);

        $errors = [];
        $label  = $this->postText('label', 150);
        $suffix = $this->postText('suffix', 20);
        $rawVal = trim(str_replace(['.', ' '], '', $this->post('value')));
        $value  = filter_var($rawVal, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 999999999]]);

        if ($label === null || $label === '') {
            $errors['label'] = 'Etiket yazın (en fazla 150 karakter).';
        }
        if ($value === false) {
            $errors['value'] = 'Sıfır ya da pozitif bir tam sayı yazın (örnek: 250).';
        }
        if ($suffix === null) {
            $errors['suffix'] = 'En fazla 20 karakter.';
        }
        if ($errors !== []) {
            $this->backWithErrors($back, $errors, $_POST);
        }

        $data = [$label, $value, $suffix !== '' ? $suffix : null, $this->postInt('sort_order'), $this->now()];
        if ($stat) {
            Database::run(
                'UPDATE stats SET label = ?, `value` = ?, suffix = ?, sort_order = ?, updated_at = ? WHERE id = ?',
                array_merge($data, [$stat['id']])
            );
        } else {
            Database::run(
                'INSERT INTO stats (label, `value`, suffix, sort_order, updated_at, created_at) VALUES (?, ?, ?, ?, ?, ?)',
                array_merge($data, [$this->now()])
            );
        }
        flash('admin_success', 'Rakam kaydedildi.');
        redirect(self::BASE . '/' . $block['id']);
    }

    // ------------------------------------------------------------------

    /** @return array<string, mixed> */
    private function find(string $id): array
    {
        $row = Database::one('SELECT * FROM home_blocks WHERE id = ?', [$this->id($id)]);
        if ($row === null) {
            $this->notFound();
        }
        return $row;
    }

    /** @return array<string, mixed> */
    private function findStatsBlock(string $id): array
    {
        $row = $this->find($id);
        if ($row['type'] !== 'stats') {
            $this->notFound();
        }
        return $row;
    }

    /** @return array<string, mixed> */
    private function findStat(string $sid): array
    {
        $row = Database::one('SELECT * FROM stats WHERE id = ?', [$this->id($sid)]);
        if ($row === null) {
            $this->notFound();
        }
        return $row;
    }

    private function nextOrder(string $table): int
    {
        $row = Database::one('SELECT MAX(sort_order) AS m FROM ' . $table);
        return (int) ($row['m'] ?? 0) + 10;
    }

    /**
     * Veritabani satirini form degerlerine cevirir (extra_json -> x_* alanlari).
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function toFormValues(array $row): array
    {
        $values = $row;
        $extra  = json_decode((string) ($row['extra_json'] ?? ''), true);
        if (is_array($extra)) {
            foreach ($extra as $key => $value) {
                $values['x_' . $key] = is_array($value) ? implode("\n", array_filter($value, 'is_string')) : (is_scalar($value) ? (string) $value : '');
            }
        }
        return $values;
    }

    /** @param array<string, mixed>|null $row */
    private function form(?array $row, string $type): void
    {
        $info = self::typeInfo($type);
        [$values, $errors] = $this->formState($row ? $this->toFormValues($row) : ['is_active' => 1]);
        $this->render('home/form', [
            'pageTitle' => $row ? 'Bloğu düzenle' : 'Yeni blok',
            'row'       => $row,
            'type'      => $type,
            'info'      => $info,
            'values'    => $values,
            'errors'    => $errors,
            'stats'     => $type === 'stats' && $row ? Database::all('SELECT * FROM stats ORDER BY sort_order, id') : [],
            'action'    => $row ? self::BASE . '/' . $row['id'] : self::BASE . '/yeni',
        ]);
    }

    /** @param array<string, mixed>|null $row */
    private function save(?array $row, string $type): void
    {
        $back = $row ? self::BASE . '/' . $row['id'] : self::BASE . '/yeni?tur=' . rawurlencode($type);
        $this->verifyCsrf($back);

        $info    = self::typeInfo($type);
        $errors  = [];
        $columns = array_fill_keys(self::COLUMNS, null);
        if ($row) {
            // Bu turde formda olmayan sutunlar oldugu gibi kalir
            foreach (self::COLUMNS as $col) {
                $columns[$col] = $row[$col];
            }
        }
        $extra = $row ? json_decode((string) ($row['extra_json'] ?? ''), true) : [];
        $extra = is_array($extra) ? $extra : [];

        $values     = [];
        $imageNames = [];
        foreach ($info['fields'] as $name => $field) {
            $label = $field['label'];
            switch ($field['type']) {
                case 'image':
                    // Gorsel en sona birakilir: once diger alanlar dogrulanir, hata varsa dosya yuklenmez
                    $imageNames[] = $name;
                    $value = $row[$name] ?? null;
                    break;
                case 'link':
                    $value = $this->postLink($name);
                    if ($value === null) {
                        $errors[$name] = 'Site içi bir yol (/hizmetler gibi) ya da https:// ile başlayan bir adres yazın.';
                        $value = '';
                    }
                    break;
                case 'lines':
                    $text  = $this->postText($name, (int) $field['max']);
                    if ($text === null) {
                        $errors[$name] = 'En fazla ' . $field['max'] . ' karakter.';
                        $text = '';
                    }
                    $lines = array_map('trim', preg_split('/\R/u', $text) ?: []);
                    $value = array_values(array_filter($lines, static function (string $l): bool {
                        return $l !== '';
                    }));
                    foreach ($value as $line) {
                        if (mb_strlen($line) > 60) {
                            $errors[$name] = 'Her satır en fazla 60 karakter olabilir.';
                        }
                    }
                    break;
                default:
                    $value = $this->postText($name, (int) $field['max']);
                    if ($value === null) {
                        $errors[$name] = 'En fazla ' . $field['max'] . ' karakter olabilir.';
                        $value = '';
                    } elseif ($value === '' && !empty($field['required'])) {
                        $errors[$name] = $label . ' boş bırakılamaz.';
                    }
            }
            $values[$name] = $value;
        }

        // Buton: yazisi ve baglantisi birlikte dolu olmali (yoksa sitede buton gorunmez)
        foreach ($info['fields'] as $name => $field) {
            if (empty($field['pair']) || isset($errors[$name])) {
                continue;
            }
            $textField = $field['pair'];
            if (($values[$textField] ?? '') !== '' && $values[$name] === '') {
                $errors[$name] = 'Butonun görünmesi için bağlantıyı da yazın.';
            } elseif (($values[$textField] ?? '') === '' && $values[$name] !== '') {
                $errors[$textField] = 'Butonun görünmesi için buton yazısını da yazın.';
            }
        }

        foreach ($imageNames as $name) {
            $values[$name] = $this->imageAfterValidation($name, $row[$name] ?? null, $errors, (string) $this->post('title'));
        }

        if ($errors !== []) {
            $this->backWithErrors($back, $errors, $_POST);
        }

        foreach ($values as $name => $value) {
            if (str_starts_with($name, 'x_')) {
                $key = substr($name, 2);
                if ($value === '' || $value === []) {
                    unset($extra[$key]);
                } else {
                    $extra[$key] = $value;
                }
            } else {
                $columns[$name] = $value === '' ? null : $value;
            }
        }
        $extraJson = $extra ? json_encode($extra, JSON_UNESCAPED_UNICODE) : null;
        $active    = $this->postBool('is_active');

        $data = array_merge(array_values($columns), [$extraJson, $active, $this->now()]);
        if ($row) {
            Database::run(
                'UPDATE home_blocks SET title = ?, subtitle = ?, body = ?, image = ?, button_text = ?, button_url = ?,
                 extra_json = ?, is_active = ?, updated_at = ? WHERE id = ?',
                array_merge($data, [$row['id']])
            );
        } else {
            Database::run(
                'INSERT INTO home_blocks (title, subtitle, body, image, button_text, button_url, extra_json, is_active, updated_at, type, sort_order, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                array_merge($data, [$type, $this->nextOrder('home_blocks'), $this->now()])
            );
            if ($type === 'stats') {
                // Rakamlar sayfasina yonlendir ki sayilar hemen eklenebilsin
                flash('admin_success', 'Blok eklendi. Aşağıdan rakamları düzenleyebilirsiniz.');
                redirect(self::BASE . '/' . Database::lastId());
            }
        }

        flash('admin_success', 'Blok kaydedildi.');
        redirect(self::BASE);
    }
}
