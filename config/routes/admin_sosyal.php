<?php
declare(strict_types=1);

use App\Controllers\Admin\SocialAdminController;

/** @var App\Core\Router $router */
$router->get('/yonetim/sosyal-medya', [SocialAdminController::class, 'index']);
$router->get('/yonetim/sosyal-medya/yeni', [SocialAdminController::class, 'create']);
$router->post('/yonetim/sosyal-medya/yeni', [SocialAdminController::class, 'store']);
$router->get('/yonetim/sosyal-medya/{id}', [SocialAdminController::class, 'edit']);
$router->post('/yonetim/sosyal-medya/{id}', [SocialAdminController::class, 'update']);
$router->post('/yonetim/sosyal-medya/{id}/sil', [SocialAdminController::class, 'delete']);
