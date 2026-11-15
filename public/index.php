<?php
declare(strict_types=1);

// Tek giris noktasi: tum istekler .htaccess ile buraya yonlenir.

// PHP'nin yerlesik sunucusunda (php -S) css/js/gorsel gibi gercek dosyalar dogrudan sunulur
if (PHP_SAPI === 'cli-server') {
    $file = __DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_file($file)) {
        return false;
    }
}

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Router;

$router = new Router();
require BASE_PATH . '/config/routes.php';

$router->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/');
