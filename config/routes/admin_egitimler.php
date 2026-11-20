<?php
declare(strict_types=1);

use App\Controllers\Admin\CourseAdminController;

/** @var App\Core\Router $router */
// Egitimler. Sabit yollar ({id} ve {cid} yollarindan) once tanimlanir.
//   {kind}: modul (course_modules), secenek (course_plans), sss (faqs)
$router->get('/yonetim/egitimler', [CourseAdminController::class, 'index']);
$router->get('/yonetim/egitimler/yeni', [CourseAdminController::class, 'create']);
$router->post('/yonetim/egitimler/yeni', [CourseAdminController::class, 'store']);
$router->get('/yonetim/egitimler/{id}', [CourseAdminController::class, 'edit']);
$router->post('/yonetim/egitimler/{id}', [CourseAdminController::class, 'update']);
$router->post('/yonetim/egitimler/{id}/sil', [CourseAdminController::class, 'delete']);

$router->get('/yonetim/egitimler/{id}/{kind}/yeni', [CourseAdminController::class, 'childCreate']);
$router->post('/yonetim/egitimler/{id}/{kind}/yeni', [CourseAdminController::class, 'childStore']);
$router->get('/yonetim/egitimler/{id}/{kind}/{cid}', [CourseAdminController::class, 'childEdit']);
$router->post('/yonetim/egitimler/{id}/{kind}/{cid}', [CourseAdminController::class, 'childUpdate']);
$router->post('/yonetim/egitimler/{id}/{kind}/{cid}/sil', [CourseAdminController::class, 'childDelete']);
