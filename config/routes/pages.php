<?php
declare(strict_types=1);

use App\Controllers\EbookController;
use App\Controllers\SeoController;

/** @var App\Core\Router $router */

$router->get('/e-kitaplar', [EbookController::class, 'index']);
$router->get('/sitemap\.xml', [SeoController::class, 'sitemap']);
$router->get('/robots\.txt', [SeoController::class, 'robots']);
