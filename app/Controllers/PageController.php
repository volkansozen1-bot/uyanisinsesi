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

        View::render('site/page', [
            'page'            => $page,
            'pageTitle'       => $page['meta_title'] ?: $page['title'],
            'metaDescription' => (string) $page['meta_description'],
        ]);
    }
}
