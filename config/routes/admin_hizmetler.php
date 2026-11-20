<?php
declare(strict_types=1);

use App\Controllers\Admin\ServiceAdminController;

/** @var App\Core\Router $router */
// Hizmetler. Sabit yollar ({id} ve {cid} yollarindan) once tanimlanir.
//   {kind}: paket (service_packages), bolum (service_sections), sss (faqs)
$router->get('/yonetim/hizmetler', [ServiceAdminController::class, 'index']);
$router->get('/yonetim/hizmetler/yeni', [ServiceAdminController::class, 'create']);
$router->post('/yonetim/hizmetler/yeni', [ServiceAdminController::class, 'store']);
$router->get('/yonetim/hizmetler/{id}', [ServiceAdminController::class, 'edit']);
$router->post('/yonetim/hizmetler/{id}', [ServiceAdminController::class, 'update']);
$router->post('/yonetim/hizmetler/{id}/sil', [ServiceAdminController::class, 'delete']);

$router->get('/yonetim/hizmetler/{id}/{kind}/yeni', [ServiceAdminController::class, 'childCreate']);
$router->post('/yonetim/hizmetler/{id}/{kind}/yeni', [ServiceAdminController::class, 'childStore']);
$router->get('/yonetim/hizmetler/{id}/{kind}/{cid}', [ServiceAdminController::class, 'childEdit']);
$router->post('/yonetim/hizmetler/{id}/{kind}/{cid}', [ServiceAdminController::class, 'childUpdate']);
$router->post('/yonetim/hizmetler/{id}/{kind}/{cid}/sil', [ServiceAdminController::class, 'childDelete']);
