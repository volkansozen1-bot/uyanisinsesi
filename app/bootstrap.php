<?php
declare(strict_types=1);

use App\Core\Env;
use App\Core\View;

define('BASE_PATH', dirname(__DIR__));

// App\Core\Router -> app/Core/Router.php
spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $file = BASE_PATH . '/app/' . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

// Composer paketleri eklendiginde otomatik yuklenir
if (is_file(BASE_PATH . '/vendor/autoload.php')) {
    require BASE_PATH . '/vendor/autoload.php';
}

require BASE_PATH . '/app/helpers.php';

Env::load(BASE_PATH . '/.env');

date_default_timezone_set(config('app.timezone'));
mb_internal_encoding('UTF-8');

// Hata yonetimi: gelistirmede ekrana, canlida yalnizca log dosyasina
error_reporting(E_ALL);
ini_set('display_errors', config('app.debug') ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/storage/logs/php-error.log');

set_exception_handler(static function (Throwable $e): void {
    error_log((string) $e);
    if (!headers_sent()) {
        http_response_code(500);
    }
    if (config('app.debug')) {
        echo '<pre>' . e((string) $e) . '</pre>';
        return;
    }
    echo View::renderFile('errors/500');
});

if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_name('uyanis_session');
    session_start();
}
