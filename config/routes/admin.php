<?php
declare(strict_types=1);

use App\Controllers\Admin\AuthController;
use App\Controllers\Admin\ContentController;
use App\Controllers\Admin\CollectionController;
use App\Controllers\Admin\DashboardController;
use App\Controllers\Admin\MessageController;
use App\Controllers\Admin\PasswordController;
use App\Controllers\Admin\SettingsController;
use App\Controllers\Admin\SetupController;
use App\Controllers\Admin\SubscriberController;

/** @var App\Core\Router $router */

// Yonetim paneli. Menu: config/admin_menu.php
$router->get('/yonetim/giris', [AuthController::class, 'form']);
$router->post('/yonetim/giris', [AuthController::class, 'login']);
$router->post('/yonetim/cikis', [AuthController::class, 'logout']);

$router->get('/yonetim/kurulum', [SetupController::class, 'form']);
$router->post('/yonetim/kurulum', [SetupController::class, 'store']);

$router->get('/yonetim', [DashboardController::class, 'index']);
$router->get('/yonetim/ana-sayfa', [ContentController::class, 'home']);
$router->post('/yonetim/ana-sayfa/{id}', [ContentController::class, 'saveBlock']);
$router->get('/yonetim/sayfalar', [ContentController::class, 'pages']);
$router->post('/yonetim/sayfalar', [ContentController::class, 'createPage']);
$router->post('/yonetim/sayfalar/{id}', [ContentController::class, 'savePage']);
$router->get('/yonetim/icerik/{module}', [CollectionController::class, 'index']);
$router->post('/yonetim/icerik/{module}/{id}', [CollectionController::class, 'save']);

$router->get('/yonetim/mesajlar', [MessageController::class, 'index']);
$router->get('/yonetim/mesajlar/{id}', [MessageController::class, 'show']);
$router->post('/yonetim/mesajlar/{id}/sil', [MessageController::class, 'delete']);

$router->get('/yonetim/aboneler', [SubscriberController::class, 'index']);
$router->get('/yonetim/aboneler/disa-aktar', [SubscriberController::class, 'export']);
$router->post('/yonetim/aboneler/{id}/sil', [SubscriberController::class, 'delete']);

$router->get('/yonetim/ayarlar', [SettingsController::class, 'form']);
$router->post('/yonetim/ayarlar', [SettingsController::class, 'save']);

$router->get('/yonetim/parola', [PasswordController::class, 'form']);
$router->post('/yonetim/parola', [PasswordController::class, 'save']);
