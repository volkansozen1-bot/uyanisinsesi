<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\View;

final class HomeController
{
    public function index(): void
    {
        $blocks = Database::all(
            'SELECT * FROM home_blocks WHERE is_active = 1 ORDER BY sort_order, id'
        );

        // Yalnizca sayfada kullanilan blok turlerinin verisi cekilir
        $types = array_column($blocks, 'type');
        $data  = [];

        if (in_array('services', $types, true)) {
            $data['services'] = Database::all(
                'SELECT slug, title, summary, image FROM services WHERE is_active = 1 ORDER BY sort_order, id'
            );
        }
        if (in_array('stats', $types, true)) {
            $data['stats'] = Database::all('SELECT label, `value`, suffix FROM stats ORDER BY sort_order, id');
        }
        if (in_array('testimonials', $types, true)) {
            $data['testimonials'] = Database::all(
                'SELECT name, `text`, rating FROM testimonials WHERE is_active = 1 ORDER BY sort_order, id LIMIT 6'
            );
        }
        if (in_array('latest_posts', $types, true)) {
            $data['posts'] = Database::all(
                "SELECT slug, title, excerpt, cover_image, published_at FROM posts
                 WHERE status = 'published' AND published_at <= ?
                 ORDER BY published_at DESC LIMIT 3",
                [date('Y-m-d H:i:s')]
            );
        }

        View::render('site/home', [
            'blocks'          => $blocks,
            'data'            => $data,
            'pageTitle'       => setting('site_name', 'Uyanışın Sesi'),
            'metaDescription' => setting('site_tagline'),
        ]);
    }
}
