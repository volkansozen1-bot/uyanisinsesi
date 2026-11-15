<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Kucuk bir router. Ornek:
 *   $router->get('/blog/{slug}', [BlogController::class, 'show']);
 * {slug} gibi parametreler metoda sirayla arguman olarak gecer.
 */
final class Router
{
    /** @var array<int, array{method:string, regex:string, handler:array{0:class-string,1:string}}> */
    private array $routes = [];

    public function get(string $pattern, array $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, array $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    private function add(string $method, string $pattern, array $handler): void
    {
        $regex = preg_replace('#\{[a-z_]+\}#', '([^/]+)', $pattern);
        $this->routes[] = [
            'method'  => $method,
            'regex'   => '#^' . $regex . '$#u',
            'handler' => $handler,
        ];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = rawurldecode((string) parse_url($uri, PHP_URL_PATH));
        $path = '/' . trim($path, '/');
        if ($method === 'HEAD') {
            $method = 'GET';
        }

        $pathMatched = false;
        foreach ($this->routes as $route) {
            if (!preg_match($route['regex'], $path, $matches)) {
                continue;
            }
            $pathMatched = true;
            if ($route['method'] !== $method) {
                continue;
            }
            array_shift($matches);
            [$class, $action] = $route['handler'];
            (new $class())->{$action}(...$matches);
            return;
        }

        if ($pathMatched) {
            http_response_code(405);
            echo View::renderFile('errors/404');
            return;
        }
        self::notFound();
    }

    public static function notFound(): void
    {
        http_response_code(404);
        echo View::renderFile('errors/404');
    }
}
