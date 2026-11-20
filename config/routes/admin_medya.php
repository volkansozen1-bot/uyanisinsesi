<?php
declare(strict_types=1);

use App\Controllers\Admin\MediaAdminController;

/** @var App\Core\Router $router */
$router->get('/yonetim/medya', [MediaAdminController::class, 'index']);
$router->post('/yonetim/medya/yukle', [MediaAdminController::class, 'upload']);
// Zengin metin editorundeki "Gorsel ekle" dugmesi (JSON doner)
$router->post('/yonetim/medya/yukle-json', [MediaAdminController::class, 'uploadJson']);
$router->post('/yonetim/medya/{id}', [MediaAdminController::class, 'update']);
$router->post('/yonetim/medya/{id}/sil', [MediaAdminController::class, 'delete']);
