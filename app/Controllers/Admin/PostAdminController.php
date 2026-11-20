<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;

/**
 * Blog yazilari ve kategorileri.
 * Rotalar: config/routes/admin_blog.php  Gorunumler: views/admin/posts/
 *
 * Sitede gorunme kosulu (BlogController): status = 'published' AND published_at <= simdi.
 * Bu yuzden "yayinda" secilip tarih bos birakilirsa simdiki zaman yazilir.
 */
final class PostAdminController extends AdminController
{
    private const BASE = '/yonetim/blog';
    private const CAT_BASE = '/yonetim/blog/kategoriler';

    /** Liste filtresi: adres degeri => etiket */
    public const STATUSES = [
        'taslak'      => 'Taslak',
        'yayinda'     => 'Yayında',
        'zamanlanmis' => 'Zamanlanmış',
    ];

    // ------------------------------------------------------------------
    // Yazilar
    // ------------------------------------------------------------------

    public function index(): void
    {
        [$where, $params, $filters] = $this->filters();
        $pager = $this->paginate($this->count("SELECT COUNT(*) FROM posts p WHERE $where", $params));

        $this->render('posts/index', [
            'pageTitle'  => 'Blog',
            'filters'    => $filters,
            'pager'      => $pager,
            'now'        => $this->now(),
            'categories' => $this->categoryOptions(),
            'items'      => Database::all(
                "SELECT p.id, p.slug, p.title, p.status, p.published_at, p.updated_at, c.name AS category_name
                 FROM posts p LEFT JOIN post_categories c ON c.id = p.category_id
                 WHERE $where
                 ORDER BY COALESCE(p.published_at, p.updated_at) DESC, p.id DESC
                 LIMIT " . $pager['limit'] . ' OFFSET ' . $pager['offset'],
                $params
            ),
        ]);
    }

    public function create(): void
    {
        $this->form(null);
    }

    public function edit(string $id): void
    {
        $this->form($this->findPost($id));
    }

    public function store(): void
    {
        $this->save(null);
    }

    public function update(string $id): void
    {
        $this->save($this->findPost($id));
    }

    public function delete(string $id): void
    {
        $this->verifyCsrf(self::BASE);
        $row = $this->findPost($id);
        Database::run('DELETE FROM posts WHERE id = ?', [$row['id']]);
        flash('admin_success', '"' . $row['title'] . '" yazısı silindi.');
        redirect(self::BASE);
    }

    /** Durum etiketi ve sinifi: [etiket, css sinifi] @param array<string, mixed> $row @return array{0:string,1:string} */
    public static function statusOf(array $row, string $now): array
    {
        if ($row['status'] !== 'published') {
            return ['Taslak', 'tag-off'];
        }
        if (empty($row['published_at'])) {
            return ['Tarih eksik', 'tag-wait'];
        }
        if ((string) $row['published_at'] > $now) {
            return ['Zamanlanmış', 'tag-wait'];
        }
        return ['Yayında', 'tag-ok'];
    }

    /** @return array<string, mixed> */
    private function findPost(string $id): array
    {
        $row = Database::one('SELECT * FROM posts WHERE id = ?', [$this->id($id)]);
        if ($row === null) {
            $this->notFound();
        }
        return $row;
    }

    /** @return array{0:string, 1:array<int, mixed>, 2:array<string, string>} */
    private function filters(): array
    {
        $q        = mb_substr(trim($this->query('q')), 0, 190);
        $status   = $this->query('durum');
        $category = $this->query('kategori');
        $where    = ['1 = 1'];
        $params   = [];

        if ($q !== '') {
            // % ve _ joker karakter sayilmasin
            $where[]  = "p.title LIKE ? ESCAPE '!'";
            $params[] = '%' . strtr($q, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
        }

        $now = $this->now();
        if ($status === 'taslak') {
            $where[] = "p.status = 'draft'";
        } elseif ($status === 'yayinda') {
            $where[]  = "p.status = 'published' AND p.published_at <= ?";
            $params[] = $now;
        } elseif ($status === 'zamanlanmis') {
            $where[]  = "p.status = 'published' AND p.published_at > ?";
            $params[] = $now;
        } else {
            $status = '';
        }

        if ($category === 'yok') {
            $where[] = 'p.category_id IS NULL';
        } elseif ($category !== '' && ctype_digit($category)) {
            $where[]  = 'p.category_id = ?';
            $params[] = (int) $category;
        } else {
            $category = '';
        }

        return [implode(' AND ', $where), $params, ['q' => $q, 'durum' => $status, 'kategori' => $category]];
    }

    /** @return array<int, string> */
    private function categoryOptions(): array
    {
        return array_column(Database::all('SELECT id, name FROM post_categories ORDER BY name'), 'name', 'id');
    }

    /** @param array<string, mixed>|null $row */
    private function form(?array $row): void
    {
        [$values, $errors] = $this->formState($row ?? ['status' => 'draft']);
        $this->render('posts/form', [
            'pageTitle'  => $row ? 'Yazıyı düzenle' : 'Yeni yazı',
            'row'        => $row,
            'values'     => $values,
            'errors'     => $errors,
            'now'        => $this->now(),
            'categories' => $this->categoryOptions(),
            'action'     => $row ? self::BASE . '/' . $row['id'] : self::BASE . '/yeni',
        ]);
    }

    /** @param array<string, mixed>|null $row */
    private function save(?array $row): void
    {
        $back = $row ? self::BASE . '/' . $row['id'] : self::BASE . '/yeni';
        $this->verifyCsrf($back);

        $errors = [];
        $title  = $this->postText('title', 255);
        $slug   = $this->postText('slug', 200);
        $excerpt = $this->postText('excerpt', 500);
        $metaTitle = $this->postText('meta_title', 200);
        $metaDesc  = $this->postText('meta_description', 300);
        $status = $this->post('status');
        $publishedAt = $this->postDate('published_at', true);
        $body   = $this->postHtml('body');

        if ($title === null || $title === '') {
            $errors['title'] = 'Başlık yazın (en fazla 255 karakter).';
        }
        if ($slug === null) {
            $errors['slug'] = 'Adres en fazla 200 karakter olabilir.';
        }
        if ($excerpt === null) {
            $errors['excerpt'] = 'Özet en fazla 500 karakter olabilir.';
        }
        if ($metaTitle === null) {
            $errors['meta_title'] = 'En fazla 200 karakter olabilir.';
        }
        if ($metaDesc === null) {
            $errors['meta_description'] = 'En fazla 300 karakter olabilir.';
        }
        if (!in_array($status, ['draft', 'published'], true)) {
            $errors['status'] = 'Durum seçin.';
        }
        if ($publishedAt === false) {
            $errors['published_at'] = 'Tarihi ve saati takvimden seçin.';
        }
        if ($status === 'published' && trim(strip_tags($body)) === '' && stripos($body, '<img') === false) {
            $errors['body'] = 'Yayınlamak için yazının metnini girin (ya da taslak olarak kaydedin).';
        }

        $categoryId = null;
        $rawCategory = $this->post('category_id');
        if ($rawCategory !== '') {
            $categoryId = ctype_digit($rawCategory) ? (int) $rawCategory : 0;
            if ($categoryId < 1 || Database::one('SELECT id FROM post_categories WHERE id = ?', [$categoryId]) === null) {
                $errors['category_id'] = 'Listeden bir kategori seçin.';
            }
        }

        $cover = $this->imageAfterValidation('cover_image', $row['cover_image'] ?? null, $errors, (string) $title);

        if ($errors !== []) {
            $this->backWithErrors($back, $errors, $_POST);
        }

        if ($status === 'published' && $publishedAt === null) {
            $publishedAt = $this->now();
        }
        $slug = $this->uniqueSlug('posts', (string) $slug, (string) $title, $row ? (int) $row['id'] : 0);

        $data = [
            $categoryId, $slug, $title, $excerpt !== '' ? $excerpt : null, $body, $cover,
            $metaTitle !== '' ? $metaTitle : null, $metaDesc !== '' ? $metaDesc : null,
            $status, $publishedAt, $this->now(),
        ];
        if ($row) {
            Database::run(
                'UPDATE posts SET category_id = ?, slug = ?, title = ?, excerpt = ?, body = ?, cover_image = ?,
                 meta_title = ?, meta_description = ?, status = ?, published_at = ?, updated_at = ? WHERE id = ?',
                array_merge($data, [$row['id']])
            );
        } else {
            Database::run(
                'INSERT INTO posts (category_id, slug, title, excerpt, body, cover_image, meta_title, meta_description,
                 status, published_at, updated_at, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                array_merge($data, [$this->now()])
            );
        }

        $message = 'Yazı kaydedildi.';
        if ($status === 'published' && $publishedAt > $this->now()) {
            $message .= ' ' . tr_date($publishedAt) . ' ' . substr((string) $publishedAt, 11, 5) . ' itibarıyla sitede görünecek.';
        } elseif ($status === 'draft') {
            $message .= ' Taslak olduğu için sitede görünmez.';
        }
        flash('admin_success', $message);
        redirect(self::BASE);
    }

    // ------------------------------------------------------------------
    // Kategoriler
    // ------------------------------------------------------------------

    public function categories(): void
    {
        $this->render('posts/categories', [
            'pageTitle' => 'Blog kategorileri',
            'items'     => Database::all(
                'SELECT c.id, c.slug, c.name, COUNT(p.id) AS post_count
                 FROM post_categories c LEFT JOIN posts p ON p.category_id = c.id
                 GROUP BY c.id, c.slug, c.name ORDER BY c.name'
            ),
        ]);
    }

    public function createCategory(): void
    {
        $this->categoryForm(null);
    }

    public function editCategory(string $id): void
    {
        $this->categoryForm($this->findCategory($id));
    }

    public function storeCategory(): void
    {
        $this->saveCategory(null);
    }

    public function updateCategory(string $id): void
    {
        $this->saveCategory($this->findCategory($id));
    }

    public function deleteCategory(string $id): void
    {
        $this->verifyCsrf(self::CAT_BASE);
        $row = $this->findCategory($id);
        // Semada ON DELETE SET NULL var; FK kapali kurulumlar (SQLite vb.) icin ayrica elle yapilir
        Database::run('UPDATE posts SET category_id = NULL WHERE category_id = ?', [$row['id']]);
        Database::run('DELETE FROM post_categories WHERE id = ?', [$row['id']]);
        flash('admin_success', '"' . $row['name'] . '" kategorisi silindi. Bu kategorideki yazılar kategorisiz kaldı.');
        redirect(self::CAT_BASE);
    }

    /** @return array<string, mixed> */
    private function findCategory(string $id): array
    {
        $row = Database::one('SELECT * FROM post_categories WHERE id = ?', [$this->id($id)]);
        if ($row === null) {
            $this->notFound();
        }
        return $row;
    }

    /** @param array<string, mixed>|null $row */
    private function categoryForm(?array $row): void
    {
        [$values, $errors] = $this->formState($row ?? []);
        $this->render('posts/category_form', [
            'pageTitle' => $row ? 'Kategoriyi düzenle' : 'Yeni kategori',
            'row'       => $row,
            'values'    => $values,
            'errors'    => $errors,
            'action'    => $row ? self::CAT_BASE . '/' . $row['id'] : self::CAT_BASE . '/yeni',
        ]);
    }

    /** @param array<string, mixed>|null $row */
    private function saveCategory(?array $row): void
    {
        $back = $row ? self::CAT_BASE . '/' . $row['id'] : self::CAT_BASE . '/yeni';
        $this->verifyCsrf($back);

        $errors = [];
        $name = $this->postText('name', 150);
        $slug = $this->postText('slug', 150);
        if ($name === null || $name === '') {
            $errors['name'] = 'Kategori adını yazın (en fazla 150 karakter).';
        }
        if ($slug === null) {
            $errors['slug'] = 'Adres en fazla 150 karakter olabilir.';
        }
        if ($errors !== []) {
            $this->backWithErrors($back, $errors, $_POST);
        }

        $slug = $this->uniqueSlug('post_categories', (string) $slug, (string) $name, $row ? (int) $row['id'] : 0);
        if ($row) {
            Database::run('UPDATE post_categories SET name = ?, slug = ?, updated_at = ? WHERE id = ?', [$name, $slug, $this->now(), $row['id']]);
        } else {
            Database::run(
                'INSERT INTO post_categories (name, slug, updated_at, created_at) VALUES (?, ?, ?, ?)',
                [$name, $slug, $this->now(), $this->now()]
            );
        }
        flash('admin_success', 'Kategori kaydedildi.');
        redirect(self::CAT_BASE);
    }
}
