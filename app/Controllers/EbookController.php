<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\View;

/** E-kitaplar: urunler eklenene kadar "cok yakinda" sayfasi. */
final class EbookController
{
    public function index(): void
    {
        View::render('site/ebooks', [
            'pageTitle'       => 'E-Kitaplar',
            'metaDescription' => 'E-kitaplar hazırlanıyor. Yayımlandığında haberdar olmak için bültene abone olabilirsiniz.',
        ]);
    }
}
