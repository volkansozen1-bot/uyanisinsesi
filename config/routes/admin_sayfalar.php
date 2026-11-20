<?php
declare(strict_types=1);

use App\Controllers\Admin\PageAdminController;

/** @var App\Core\Router $router */
// Sayfalar. Hakkimda duzenindeki sayfanin ek bolumleri: /yolculuk (about_timeline), /yaklasim (about_values)
$router->get('/yonetim/sayfalar', [PageAdminController::class, 'index']);
$router->get('/yonetim/sayfalar/yeni', [PageAdminController::class, 'create']);
$router->post('/yonetim/sayfalar/yeni', [PageAdminController::class, 'store']);
$router->get('/yonetim/sayfalar/{id}', [PageAdminController::class, 'edit']);
$router->post('/yonetim/sayfalar/{id}', [PageAdminController::class, 'update']);
$router->post('/yonetim/sayfalar/{id}/sil', [PageAdminController::class, 'delete']);

$router->get('/yonetim/sayfalar/{id}/yolculuk/yeni', [PageAdminController::class, 'createTimeline']);
$router->post('/yonetim/sayfalar/{id}/yolculuk/yeni', [PageAdminController::class, 'storeTimeline']);
$router->get('/yonetim/sayfalar/{id}/yolculuk/{tid}', [PageAdminController::class, 'editTimeline']);
$router->post('/yonetim/sayfalar/{id}/yolculuk/{tid}', [PageAdminController::class, 'updateTimeline']);
$router->post('/yonetim/sayfalar/{id}/yolculuk/{tid}/sil', [PageAdminController::class, 'deleteTimeline']);

$router->get('/yonetim/sayfalar/{id}/yaklasim/yeni', [PageAdminController::class, 'createValue']);
$router->post('/yonetim/sayfalar/{id}/yaklasim/yeni', [PageAdminController::class, 'storeValue']);
$router->get('/yonetim/sayfalar/{id}/yaklasim/{vid}', [PageAdminController::class, 'editValue']);
$router->post('/yonetim/sayfalar/{id}/yaklasim/{vid}', [PageAdminController::class, 'updateValue']);
$router->post('/yonetim/sayfalar/{id}/yaklasim/{vid}/sil', [PageAdminController::class, 'deleteValue']);
