<?php
declare(strict_types=1);

use App\Controllers\Admin\HomeBlockAdminController as C;

/** @var App\Core\Router $router */
// Ana sayfa bloklari ve "Rakamlar" blogunun sayilari
$router->get('/yonetim/ana-sayfa', [C::class, 'index']);
$router->get('/yonetim/ana-sayfa/yeni', [C::class, 'create']);
$router->post('/yonetim/ana-sayfa/yeni', [C::class, 'store']);
$router->get('/yonetim/ana-sayfa/{id}', [C::class, 'edit']);
$router->post('/yonetim/ana-sayfa/{id}', [C::class, 'update']);
$router->post('/yonetim/ana-sayfa/{id}/sil', [C::class, 'delete']);
$router->post('/yonetim/ana-sayfa/{id}/gorunurluk', [C::class, 'toggle']);
$router->post('/yonetim/ana-sayfa/{id}/yukari', [C::class, 'moveUp']);
$router->post('/yonetim/ana-sayfa/{id}/asagi', [C::class, 'moveDown']);

$router->get('/yonetim/ana-sayfa/{id}/rakam/yeni', [C::class, 'statCreate']);
$router->post('/yonetim/ana-sayfa/{id}/rakam/yeni', [C::class, 'statStore']);
$router->get('/yonetim/ana-sayfa/{id}/rakam/{sid}', [C::class, 'statEdit']);
$router->post('/yonetim/ana-sayfa/{id}/rakam/{sid}', [C::class, 'statUpdate']);
$router->post('/yonetim/ana-sayfa/{id}/rakam/{sid}/sil', [C::class, 'statDelete']);
