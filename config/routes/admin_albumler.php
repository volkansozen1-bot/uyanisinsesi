<?php
declare(strict_types=1);

use App\Controllers\Admin\AlbumAdminController;

/** @var App\Core\Router $router */
$router->get('/yonetim/albumler', [AlbumAdminController::class, 'index']);
$router->get('/yonetim/albumler/yeni', [AlbumAdminController::class, 'create']);
$router->post('/yonetim/albumler/yeni', [AlbumAdminController::class, 'store']);
$router->get('/yonetim/albumler/{id}', [AlbumAdminController::class, 'edit']);
$router->post('/yonetim/albumler/{id}', [AlbumAdminController::class, 'update']);
$router->post('/yonetim/albumler/{id}/sil', [AlbumAdminController::class, 'delete']);
