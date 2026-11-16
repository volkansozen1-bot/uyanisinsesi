<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;
use App\Core\View;

final class BlogController
{
    private const PER_PAGE = 9;

    public function index(): void
    {
        $this->listing(null);
    }

    public function category(string $slug): void
    {
        $category = Database::one('SELECT id, slug, name FROM post_categories WHERE slug = ?', [$slug]);
        if ($category === null) {
            Router::notFound();
            return;
        }
        $this->listing($category);
    }

    /** @param array<string, mixed>|null $category */
    private function listing(?array $category): void
    {
        $now    = date('Y-m-d H:i:s');
        $where  = "status = 'published' AND published_at <= ?";
        $params = [$now];
        if ($category !== null) {
            $where   .= ' AND category_id = ?';
            $params[] = $category['id'];
        }

        $total = (int) Database::one("SELECT COUNT(*) AS c FROM posts WHERE $where", $params)['c'];
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page  = min($pages, max(1, (int) ($_GET['sayfa'] ?? 1)));

        // LIMIT/OFFSET tam sayiya cevrildigi icin sorguya dogrudan yazilabilir
        $offset = ($page - 1) * self::PER_PAGE;
        $posts  = Database::all(
            "SELECT slug, title, excerpt, cover_image, published_at FROM posts
             WHERE $where ORDER BY published_at DESC LIMIT " . self::PER_PAGE . " OFFSET $offset",
            $params
        );

        View::render('site/blog', [
            'posts'      => $posts,
            'category'   => $category,
            'categories' => Database::all('SELECT slug, name FROM post_categories ORDER BY name'),
            'page'       => $page,
            'pages'      => $pages,
            'basePath'   => $category ? '/blog/kategori/' . $category['slug'] : '/blog',
            'pageTitle'  => $category ? $category['name'] . ' | Blog' : 'Blog',
        ]);
    }

    public function show(string $slug): void
    {
        $post = Database::one(
            "SELECT p.*, c.name AS category_name, c.slug AS category_slug
             FROM posts p LEFT JOIN post_categories c ON c.id = p.category_id
             WHERE p.slug = ? AND p.status = 'published' AND p.published_at <= ?",
            [$slug, date('Y-m-d H:i:s')]
        );
        if ($post === null) {
            Router::notFound();
            return;
        }

        View::render('site/post', [
            'post'   => $post,
            'others' => Database::all(
                "SELECT slug, title, published_at FROM posts
                 WHERE status = 'published' AND published_at <= ? AND id <> ?
                 ORDER BY published_at DESC LIMIT 3",
                [date('Y-m-d H:i:s'), $post['id']]
            ),
            'pageTitle'       => $post['meta_title'] ?: $post['title'],
            'metaDescription' => (string) ($post['meta_description'] ?: $post['excerpt']),
        ]);
    }
}
