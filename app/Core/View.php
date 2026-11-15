<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/** PHP sablonlarini views/ klasorunden yukler. */
final class View
{
    /** Sablonu layout icinde ekrana basar. */
    public static function render(string $template, array $data = [], string $layout = 'layouts/site'): void
    {
        $content = self::renderFile($template, $data);
        echo self::renderFile($layout, $data + ['content' => $content]);
    }

    /** Sablonu calistirir ve ciktisini metin olarak dondurur (layout'suz). */
    public static function renderFile(string $template, array $data = []): string
    {
        $file = BASE_PATH . '/views/' . $template . '.php';
        if (!is_file($file)) {
            throw new RuntimeException('Sablon bulunamadi: ' . $template);
        }
        // Sablon ayri bir kapsamda calisir; boylece $data, $file gibi adlar sablon degiskenleriyle cakismaz
        $run = static function (string $__file, array $__vars): void {
            extract($__vars);
            require $__file;
        };
        ob_start();
        try {
            $run($file, $data);
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
        return (string) ob_get_clean();
    }
}
