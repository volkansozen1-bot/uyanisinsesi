<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;
use App\Core\View;

final class PageController
{
    public function show(string $slug): void
    {
        $page = Database::one(
            "SELECT * FROM pages WHERE slug = ? AND status = 'published'",
            [$slug]
        );
        if ($page === null) {
            Router::notFound();
            return;
        }

        $data = [
            'page'            => $page,
            'pageTitle'       => $page['meta_title'] ?: $page['title'],
            'metaDescription' => (string) $page['meta_description'],
        ];

        // template sutunu 003 migration'i ile gelir; yoksa duz sayfa
        if (($page['template'] ?? 'default') === 'about') {
            $data['timeline'] = Database::all(
                'SELECT year_label, title, `text` FROM about_timeline ORDER BY sort_order, id'
            );
            $data['values'] = Database::all(
                'SELECT title, `text`, icon FROM about_values ORDER BY sort_order, id'
            );
            if (!empty($page['hero_image'])) {
                $data['ogImage'] = $page['hero_image'];
            }
            View::render('site/about', $data);
            return;
        }

        View::render('site/page', $data);
    }
}
