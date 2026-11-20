<?php
declare(strict_types=1);

use App\Controllers\Admin\PostAdminController;

/** @var App\Core\Router $router */
// Blog yazilari ve kategorileri. Sabit yollar ({id}'den once): /yeni, /kategoriler
$router->get('/yonetim/blog', [PostAdminController::class, 'index']);
$router->get('/yonetim/blog/yeni', [PostAdminController::class, 'create']);
$router->post('/yonetim/blog/yeni', [PostAdminController::class, 'store']);

$router->get('/yonetim/blog/kategoriler', [PostAdminController::class, 'categories']);
$router->get('/yonetim/blog/kategoriler/yeni', [PostAdminController::class, 'createCategory']);
$router->post('/yonetim/blog/kategoriler/yeni', [PostAdminController::class, 'storeCategory']);
$router->get('/yonetim/blog/kategoriler/{id}', [PostAdminController::class, 'editCategory']);
$router->post('/yonetim/blog/kategoriler/{id}', [PostAdminController::class, 'updateCategory']);
$router->post('/yonetim/blog/kategoriler/{id}/sil', [PostAdminController::class, 'deleteCategory']);

$router->get('/yonetim/blog/{id}', [PostAdminController::class, 'edit']);
$router->post('/yonetim/blog/{id}', [PostAdminController::class, 'update']);
$router->post('/yonetim/blog/{id}/sil', [PostAdminController::class, 'delete']);
