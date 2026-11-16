<?php
declare(strict_types=1);

use App\Controllers\NewsletterController;

/** @var App\Core\Router $router */

// GET rotalari config/routes.php icinde; GET yalnizca dugmeli sayfa gosterir, islem POST ile yapilir.
$router->post('/bulten/onay/{token}', [NewsletterController::class, 'confirm']);
$router->post('/bulten/cik/{token}', [NewsletterController::class, 'unsubscribe']);
