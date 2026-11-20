<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;

/**
 * Sayfalar (pages): Hakkimda, KVKK, gizlilik gibi /adres ile acilan sayfalar.
 * Sablonu 'about' olan sayfada ek bolumler: Yolculugum (about_timeline), Yaklasimim (about_values)
 * ve alinti (settings: about_quote, about_quote_author). Bu kayitlar sayfaya bagli degildir (tek Hakkimda sayfasi icin).
 * Rotalar: config/routes/admin_sayfalar.php  Gorunumler: views/admin/pages/
 */
final class PageAdminController extends AdminController
{
    private const BASE = '/yonetim/sayfalar';

    public const TEMPLATES = [
        'default' => 'Düz sayfa',
        'about'   => 'Hakkımda düzeni',
    ];

    /**
     * Sitenin kendi yollarinin ilk parcalari (config/routes.php ve config/routes/*.php).
     * Bu adreslerle sayfa olusturulursa sayfa hic acilmaz. Yeni modul eklenince buraya da yazin.
     */
    public const RESERVED_SLUGS = [
        'hizmetler', 'egitimler', 'blog', 'albumler', 'iletisim', 'e-kitaplar', 'yonetim', 'bulten',
        'sitemap.xml', 'robots.txt', '_deploy', 'assets', 'uploads',
    ];

    /** views/site/about.php icindeki ikon anahtarlari */
    public const ICONS = [
        'circle' => 'Daire',
        'breath' => 'Nefes (dalgalar)',
        'heart'  => 'Kalp',
        'moon'   => 'Ay',
        'leaf'   => 'Yaprak',
    ];

    private const MENU_LOCATIONS = [
        'header'          => 'üst menüde',
        'footer_explore'  => 'alt menüde, Keşfet bölümünde',
        'footer_services' => 'alt menüde, Hizmetler bölümünde',
        'footer_legal'    => 'alt menüde, yasal bağlantılarda',
    ];

    // ------------------------------------------------------------------
    // Sayfalar
    // ------------------------------------------------------------------

    public function index(): void
    {
        $items = Database::all('SELECT id, slug, title, template, status, updated_at FROM pages ORDER BY title, id');
        $menuLinks = [];
        foreach ($items as $item) {
            $menuLinks[$item['id']] = $this->menuLinks((string) $item['slug']);
        }
        $this->render('pages/index', [
            'pageTitle' => 'Sayfalar',
            'items'     => $items,
            'menuLinks' => $menuLinks,
        ]);
    }

    public function create(): void
    {
        $this->form(null);
    }

    public function edit(string $id): void
    {
        $this->form($this->findPage($id));
    }

    public function store(): void
    {
        $this->save(null);
    }

    public function update(string $id): void
    {
        $this->save($this->findPage($id));
    }

    public function delete(string $id): void
    {
        $this->verifyCsrf(self::BASE);
        $row = $this->findPage($id);
        Database::run('DELETE FROM pages WHERE id = ?', [$row['id']]);
        $message = '"' . $row['title'] . '" sayfası silindi.';
        if ($this->menuLinks((string) $row['slug']) !== []) {
            $message .= ' Menüde bu adrese (/' . $row['slug'] . ') giden bağlantı artık "sayfa bulunamadı" gösterecek. Aynı adresle yeni bir sayfa oluşturarak düzeltebilirsiniz.';
        }
        flash('admin_success', $message);
        redirect(self::BASE);
    }

    /**
     * Bu sayfaya giden menu baglantilari: ["alt menüde (yasal bağlantılar): KVKK Aydınlatma Metni", ...]
     * @return array<int, string>
     */
    private function menuLinks(string $slug): array
    {
        $out = [];
        foreach (Database::all(
            'SELECT location, label FROM menu_items WHERE url IN (?, ?) ORDER BY sort_order, id',
            ['/' . $slug, '/' . $slug . '/']
        ) as $m) {
            $out[] = (self::MENU_LOCATIONS[$m['location']] ?? 'menüde') . ': "' . $m['label'] . '"';
        }
        return $out;
    }

    /** @return array<string, mixed> */
    private function findPage(string $id): array
    {
        $row = Database::one('SELECT * FROM pages WHERE id = ?', [$this->id($id)]);
        if ($row === null) {
            $this->notFound();
        }
        return $row;
    }

    /** Sablonu 'about' olan sayfa; degilse 404 (ek bolumler yalnizca orada). @return array<string, mixed> */
    private function findAboutPage(string $id): array
    {
        $row = $this->findPage($id);
        if (($row['template'] ?? 'default') !== 'about') {
            $this->notFound();
        }
        return $row;
    }

    /** @param array<string, mixed>|null $row */
    private function form(?array $row): void
    {
        $isAbout  = $row !== null && ($row['template'] ?? 'default') === 'about';
        $defaults = $row ?? ['status' => 'draft', 'template' => 'default'];
        if ($isAbout) {
            $defaults['about_quote']        = $this->settingValue('about_quote');
            $defaults['about_quote_author'] = $this->settingValue('about_quote_author');
        }
        [$values, $errors] = $this->formState($defaults);

        $this->render('pages/form', [
            'pageTitle' => $row ? 'Sayfayı düzenle' : 'Yeni sayfa',
            'row'       => $row,
            'values'    => $values,
            'errors'    => $errors,
            'isAbout'   => $isAbout,
            'menuLinks' => $row ? $this->menuLinks((string) $row['slug']) : [],
            'timeline'  => $isAbout ? Database::all('SELECT * FROM about_timeline ORDER BY sort_order, id') : [],
            'aboutValues' => $isAbout ? Database::all('SELECT * FROM about_values ORDER BY sort_order, id') : [],
            'action'    => $row ? self::BASE . '/' . $row['id'] : self::BASE . '/yeni',
        ]);
    }

    private function settingValue(string $key): string
    {
        $r = Database::one('SELECT `value` FROM settings WHERE `key` = ?', [$key]);
        return $r ? (string) $r['value'] : '';
    }

    /** @param array<string, mixed>|null $row */
    private function save(?array $row): void
    {
        $back = $row ? self::BASE . '/' . $row['id'] : self::BASE . '/yeni';
        $this->verifyCsrf($back);

        $errors    = [];
        $title     = $this->postText('title', 200);
        $slugInput = $this->postText('slug', 150);
        $template  = $this->post('template');
        $status    = $this->post('status');
        $metaTitle = $this->postText('meta_title', 200);
        $metaDesc  = $this->postText('meta_description', 300);
        $body      = $this->postHtml('body');
        $wasAbout  = $row !== null && ($row['template'] ?? 'default') === 'about';

        if ($title === null || $title === '') {
            $errors['title'] = 'Başlık yazın (en fazla 200 karakter).';
        }
        if ($slugInput === null) {
            $errors['slug'] = 'Adres en fazla 150 karakter olabilir.';
        } else {
            $base  = slugify($slugInput !== '' ? $slugInput : (string) $title);
            $typed = mb_strtolower($slugInput);
            $clash = in_array($typed, self::RESERVED_SLUGS, true) ? $typed
                : (in_array($base, self::RESERVED_SLUGS, true) ? $base : '');
            if ($clash !== '') {
                $errors['slug'] = '"/' . $clash . '" adresini sitenin kendi bölümü kullanıyor; bu adresle açılan sayfa görünmez. Başka bir adres yazın.';
            }
        }
        if (!isset(self::TEMPLATES[$template])) {
            $errors['template'] = 'Listeden bir şablon seçin.';
        }
        if (!in_array($status, ['draft', 'published'], true)) {
            $errors['status'] = 'Durum seçin.';
        }
        if ($metaTitle === null) {
            $errors['meta_title'] = 'En fazla 200 karakter olabilir.';
        }
        if ($metaDesc === null) {
            $errors['meta_description'] = 'En fazla 300 karakter olabilir.';
        }
        if ($status === 'published' && $template === 'default' && trim(strip_tags($body)) === '' && stripos($body, '<img') === false) {
            $errors['body'] = 'Yayınlamak için sayfanın metnini girin (ya da taslak olarak kaydedin).';
        }

        // Alinti yalnizca Hakkimda duzenindeki sayfanin formunda vardir
        $quote = $quoteAuthor = null;
        if ($wasAbout) {
            $quote       = $this->postText('about_quote', 500);
            $quoteAuthor = $this->postText('about_quote_author', 150);
            if ($quote === null) {
                $errors['about_quote'] = 'Alıntı en fazla 500 karakter olabilir.';
            }
            if ($quoteAuthor === null) {
                $errors['about_quote_author'] = 'En fazla 150 karakter olabilir.';
            }
        }

        $hero = $this->imageField('hero_image', $row['hero_image'] ?? null, $errors, (string) $title);

        if ($errors !== []) {
            $this->backWithErrors($back, $errors, $_POST);
        }

        $slug = $this->uniqueSlug('pages', (string) $slugInput, (string) $title, $row ? (int) $row['id'] : 0);
        $now  = $this->now();
        $data = [$slug, $title, $body, $hero, $metaTitle !== '' ? $metaTitle : null, $metaDesc !== '' ? $metaDesc : null, $template, $status, $now];

        $pdo = Database::pdo();
        $pdo->beginTransaction();
        try {
            if ($row) {
                Database::run(
                    'UPDATE pages SET slug = ?, title = ?, body = ?, hero_image = ?, meta_title = ?, meta_description = ?,
                     template = ?, status = ?, updated_at = ? WHERE id = ?',
                    array_merge($data, [$row['id']])
                );
                $id = (int) $row['id'];
            } else {
                Database::run(
                    'INSERT INTO pages (slug, title, body, hero_image, meta_title, meta_description, template, status, updated_at, created_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                    array_merge($data, [$now])
                );
                $id = Database::lastId();
            }
            if ($wasAbout) {
                $this->saveSetting('about_quote', (string) $quote, $now);
                $this->saveSetting('about_quote_author', (string) $quoteAuthor, $now);
            }
            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }

        $message = 'Sayfa kaydedildi.';
        if ($row && $row['slug'] !== $slug && $this->menuLinks((string) $row['slug']) !== []) {
            $message .= ' Dikkat: menüdeki bağlantı hâlâ eski adrese (/' . $row['slug'] . ') gidiyor ve artık çalışmıyor. Adresi eski haline getirmeniz önerilir.';
        }
        if ($status === 'draft') {
            $message .= ' Taslak olduğu için sitede görünmez.';
        }

        // Hakkimda duzenine yeni gecildiyse ek bolumler icin duzenleme sayfasinda kal
        if ($template === 'about' && !$wasAbout) {
            flash('admin_success', $message . ' Yolculuğum, Yaklaşımım ve Alıntı bölümleri aşağıda.');
            redirect(self::BASE . '/' . $id);
        }
        flash('admin_success', $message);
        redirect(self::BASE);
    }

    /** Ayar upsert: MySQL ve SQLite'ta ayni calissin diye iki adimda (SettingsController ile ayni yontem). */
    private function saveSetting(string $key, string $value, string $now): void
    {
        $updated = Database::run('UPDATE settings SET `value` = ?, updated_at = ? WHERE `key` = ?', [$value, $now, $key])->rowCount();
        if ($updated === 0 && Database::one('SELECT id FROM settings WHERE `key` = ?', [$key]) === null) {
            Database::run(
                'INSERT INTO settings (`key`, `value`, created_at, updated_at) VALUES (?, ?, ?, ?)',
                [$key, $value, $now, $now]
            );
        }
    }

    // ------------------------------------------------------------------
    // Yolculugum (about_timeline)
    // ------------------------------------------------------------------

    public function createTimeline(string $id): void
    {
        $this->timelineForm($this->findAboutPage($id), null);
    }

    public function editTimeline(string $id, string $tid): void
    {
        $page = $this->findAboutPage($id);
        $this->timelineForm($page, $this->findChild('about_timeline', $tid));
    }

    public function storeTimeline(string $id): void
    {
        $this->saveTimeline($this->findAboutPage($id), null);
    }

    public function updateTimeline(string $id, string $tid): void
    {
        $page = $this->findAboutPage($id);
        $this->saveTimeline($page, $this->findChild('about_timeline', $tid));
    }

    public function deleteTimeline(string $id, string $tid): void
    {
        $page = $this->findAboutPage($id);
        $this->verifyCsrf(self::BASE . '/' . $page['id']);
        $item = $this->findChild('about_timeline', $tid);
        Database::run('DELETE FROM about_timeline WHERE id = ?', [$item['id']]);
        flash('admin_success', 'Yolculuk adımı silindi.');
        redirect(self::BASE . '/' . $page['id'] . '#yolculuk');
    }

    /** @param array<string, mixed> $page @param array<string, mixed>|null $item */
    private function timelineForm(array $page, ?array $item): void
    {
        [$values, $errors] = $this->formState($item ?? ['sort_order' => $this->nextOrder('about_timeline')]);
        $this->render('pages/timeline_form', [
            'pageTitle' => $item ? 'Yolculuk adımını düzenle' : 'Yeni yolculuk adımı',
            'page'      => $page,
            'item'      => $item,
            'values'    => $values,
            'errors'    => $errors,
            'action'    => self::BASE . '/' . $page['id'] . '/yolculuk/' . ($item ? $item['id'] : 'yeni'),
        ]);
    }

    /** @param array<string, mixed> $page @param array<string, mixed>|null $item */
    private function saveTimeline(array $page, ?array $item): void
    {
        $back = self::BASE . '/' . $page['id'] . '/yolculuk/' . ($item ? $item['id'] : 'yeni');
        $this->verifyCsrf($back);

        $errors = [];
        $year   = $this->postText('year_label', 50);
        $title  = $this->postText('title', 200);
        $text   = $this->postText('text', 2000);
        $order  = $this->postInt('sort_order');
        if ($year === null || $year === '') {
            $errors['year_label'] = 'Yıl ya da dönem yazın (en fazla 50 karakter). Örnek: 2015, 2015-2018, Bugün.';
        }
        if ($title === null || $title === '') {
            $errors['title'] = 'Başlık yazın (en fazla 200 karakter).';
        }
        if ($text === null) {
            $errors['text'] = 'Metin en fazla 2000 karakter olabilir.';
        }
        if ($errors !== []) {
            $this->backWithErrors($back, $errors, $_POST);
        }

        $data = [$year, $title, $text !== '' ? $text : null, $order, $this->now()];
        if ($item) {
            Database::run('UPDATE about_timeline SET year_label = ?, title = ?, `text` = ?, sort_order = ?, updated_at = ? WHERE id = ?', array_merge($data, [$item['id']]));
        } else {
            Database::run('INSERT INTO about_timeline (year_label, title, `text`, sort_order, updated_at, created_at) VALUES (?, ?, ?, ?, ?, ?)', array_merge($data, [$this->now()]));
        }
        flash('admin_success', 'Yolculuk adımı kaydedildi.');
        redirect(self::BASE . '/' . $page['id'] . '#yolculuk');
    }

    // ------------------------------------------------------------------
    // Yaklasimim (about_values)
    // ------------------------------------------------------------------

    public function createValue(string $id): void
    {
        $this->valueForm($this->findAboutPage($id), null);
    }

    public function editValue(string $id, string $vid): void
    {
        $page = $this->findAboutPage($id);
        $this->valueForm($page, $this->findChild('about_values', $vid));
    }

    public function storeValue(string $id): void
    {
        $this->saveValue($this->findAboutPage($id), null);
    }

    public function updateValue(string $id, string $vid): void
    {
        $page = $this->findAboutPage($id);
        $this->saveValue($page, $this->findChild('about_values', $vid));
    }

    public function deleteValue(string $id, string $vid): void
    {
        $page = $this->findAboutPage($id);
        $this->verifyCsrf(self::BASE . '/' . $page['id']);
        $item = $this->findChild('about_values', $vid);
        Database::run('DELETE FROM about_values WHERE id = ?', [$item['id']]);
        flash('admin_success', 'Yaklaşım kartı silindi.');
        redirect(self::BASE . '/' . $page['id'] . '#yaklasim');
    }

    /** @param array<string, mixed> $page @param array<string, mixed>|null $item */
    private function valueForm(array $page, ?array $item): void
    {
        [$values, $errors] = $this->formState($item ?? ['icon' => 'circle', 'sort_order' => $this->nextOrder('about_values')]);
        $this->render('pages/value_form', [
            'pageTitle' => $item ? 'Yaklaşım kartını düzenle' : 'Yeni yaklaşım kartı',
            'page'      => $page,
            'item'      => $item,
            'values'    => $values,
            'errors'    => $errors,
            'action'    => self::BASE . '/' . $page['id'] . '/yaklasim/' . ($item ? $item['id'] : 'yeni'),
        ]);
    }

    /** @param array<string, mixed> $page @param array<string, mixed>|null $item */
    private function saveValue(array $page, ?array $item): void
    {
        $back = self::BASE . '/' . $page['id'] . '/yaklasim/' . ($item ? $item['id'] : 'yeni');
        $this->verifyCsrf($back);

        $errors = [];
        $title  = $this->postText('title', 200);
        $text   = $this->postText('text', 2000);
        $icon   = $this->post('icon');
        $order  = $this->postInt('sort_order');
        if ($title === null || $title === '') {
            $errors['title'] = 'Başlık yazın (en fazla 200 karakter).';
        }
        if ($text === null) {
            $errors['text'] = 'Metin en fazla 2000 karakter olabilir.';
        }
        if (!isset(self::ICONS[$icon])) {
            $errors['icon'] = 'Listeden bir ikon seçin.';
        }
        if ($errors !== []) {
            $this->backWithErrors($back, $errors, $_POST);
        }

        $data = [$title, $text !== '' ? $text : null, $icon, $order, $this->now()];
        if ($item) {
            Database::run('UPDATE about_values SET title = ?, `text` = ?, icon = ?, sort_order = ?, updated_at = ? WHERE id = ?', array_merge($data, [$item['id']]));
        } else {
            Database::run('INSERT INTO about_values (title, `text`, icon, sort_order, updated_at, created_at) VALUES (?, ?, ?, ?, ?, ?)', array_merge($data, [$this->now()]));
        }
        flash('admin_success', 'Yaklaşım kartı kaydedildi.');
        redirect(self::BASE . '/' . $page['id'] . '#yaklasim');
    }

    // ------------------------------------------------------------------

    /** @return array<string, mixed> */
    private function findChild(string $table, string $id): array
    {
        $row = Database::one('SELECT * FROM ' . $table . ' WHERE id = ?', [$this->id($id)]);
        if ($row === null) {
            $this->notFound();
        }
        return $row;
    }

    /** Yeni satir icin onerilen sira: en buyuk + 1 */
    private function nextOrder(string $table): int
    {
        $r = Database::one('SELECT MAX(sort_order) AS m FROM ' . $table);
        return (int) ($r['m'] ?? 0) + 1;
    }
}
