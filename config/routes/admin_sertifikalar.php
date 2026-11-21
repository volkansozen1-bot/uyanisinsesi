<?php
declare(strict_types=1);

use App\Controllers\Admin\CertificateAdminController;

/** @var App\Core\Router $router */
$router->get('/yonetim/sertifikalar', [CertificateAdminController::class, 'index']);
$router->post('/yonetim/sertifikalar/ekle', [CertificateAdminController::class, 'store']);
$router->post('/yonetim/sertifikalar', [CertificateAdminController::class, 'update']);
