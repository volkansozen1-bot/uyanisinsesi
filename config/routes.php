<?php
declare(strict_types=1);

use App\Controllers\ContactController;
use App\Controllers\HomeController;
use App\Controllers\NewsletterController;
use App\Controllers\PageController;

/** @var App\Core\Router $router */

$router->get('/', [HomeController::class, 'index']);

$router->get('/iletisim', [ContactController::class, 'form']);
$router->post('/iletisim', [ContactController::class, 'send']);

$router->post('/bulten', [NewsletterController::class, 'subscribe']);
$router->get('/bulten/onay/{token}', [NewsletterController::class, 'confirm']);
$router->get('/bulten/cik/{token}', [NewsletterController::class, 'unsubscribe']);

// Sonraki adimlarda eklenecek: /hizmetler, /egitimler, /blog, /albumler, /yonetim

// En sonda kalmali: pages tablosundaki sayfalar (/hakkimda, yasal metinler)
$router->get('/{slug}', [PageController::class, 'show']);
