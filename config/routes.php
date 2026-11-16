<?php
declare(strict_types=1);

use App\Controllers\AlbumController;
use App\Controllers\BlogController;
use App\Controllers\ContactController;
use App\Controllers\CourseController;
use App\Controllers\HomeController;
use App\Controllers\NewsletterController;
use App\Controllers\PageController;
use App\Controllers\ServiceController;

/** @var App\Core\Router $router */

$router->get('/', [HomeController::class, 'index']);

$router->get('/iletisim', [ContactController::class, 'form']);
$router->post('/iletisim', [ContactController::class, 'send']);

$router->post('/bulten', [NewsletterController::class, 'subscribe']);
$router->get('/bulten/onay/{token}', [NewsletterController::class, 'confirm']);
$router->get('/bulten/cik/{token}', [NewsletterController::class, 'unsubscribe']);

$router->get('/hizmetler', [ServiceController::class, 'index']);
$router->get('/hizmetler/{slug}', [ServiceController::class, 'show']);

$router->get('/egitimler', [CourseController::class, 'index']);
$router->get('/egitimler/{slug}', [CourseController::class, 'show']);

$router->get('/blog', [BlogController::class, 'index']);
$router->get('/blog/kategori/{slug}', [BlogController::class, 'category']);
$router->get('/blog/{slug}', [BlogController::class, 'show']);

$router->get('/albumler', [AlbumController::class, 'index']);

// Modul rotalari: config/routes/*.php (yonetim paneli, SEO vb.). Alfabetik sirayla yuklenir.
foreach (glob(BASE_PATH . '/config/routes/*.php') ?: [] as $routeFile) {
    require $routeFile;
}

// En sonda kalmali: pages tablosundaki sayfalar (/hakkimda, yasal metinler)
$router->get('/{slug}', [PageController::class, 'show']);
