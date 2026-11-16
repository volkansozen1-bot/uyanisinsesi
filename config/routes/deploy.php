<?php
declare(strict_types=1);

use App\Controllers\DeployController;

/** @var App\Core\Router $router */
$router->post('/_deploy/migrate', [DeployController::class, 'migrate']);
// GET'te 405 yerine 404: adresin varligi belli olmasin
$router->get('/_deploy/migrate', [DeployController::class, 'hidden']);
