<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\View;

final class AlbumController
{
    public function index(): void
    {
        View::render('site/albums', [
            'albums' => Database::all(
                'SELECT title, type, year, track_count, cover_image, listen_url FROM albums
                 WHERE is_active = 1 ORDER BY sort_order, id'
            ),
            'pageTitle' => 'Albümler',
        ]);
    }
}
