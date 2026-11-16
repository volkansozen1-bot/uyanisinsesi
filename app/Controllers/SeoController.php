<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;

/** Arama motorlari icin /sitemap.xml ve /robots.txt */
final class SeoController
{
    public function sitemap(): void
    {
        $entries = [['/', null]];

        // Sabit liste sayfalari
        foreach (['/hizmetler', '/egitimler', '/blog', '/albumler', '/e-kitaplar', '/iletisim'] as $path) {
            $entries[] = [$path, null];
        }

        foreach (Database::all("SELECT slug, updated_at FROM pages WHERE status = 'published' ORDER BY id") as $row) {
            $entries[] = ['/' . $row['slug'], $row['updated_at']];
        }
        foreach (Database::all('SELECT slug, updated_at FROM services WHERE is_active = 1 ORDER BY sort_order, id') as $row) {
            $entries[] = ['/hizmetler/' . $row['slug'], $row['updated_at']];
        }
        foreach (Database::all('SELECT slug, updated_at FROM courses WHERE is_active = 1 ORDER BY sort_order, id') as $row) {
            $entries[] = ['/egitimler/' . $row['slug'], $row['updated_at']];
        }
        $posts = Database::all(
            "SELECT slug, updated_at FROM posts WHERE status = 'published' AND published_at <= ? ORDER BY published_at DESC",
            [date('Y-m-d H:i:s')]
        );
        foreach ($posts as $row) {
            $entries[] = ['/blog/' . $row['slug'], $row['updated_at']];
        }

        header('Content-Type: application/xml; charset=utf-8');
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
        foreach ($entries as [$path, $updated]) {
            $xml .= "  <url>\n    <loc>" . htmlspecialchars(url($path), ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</loc>\n";
            $time = $updated ? strtotime((string) $updated) : false;
            if ($time !== false) {
                $xml .= '    <lastmod>' . date('c', $time) . "</lastmod>\n";
            }
            $xml .= "  </url>\n";
        }
        $xml .= "</urlset>\n";
        echo $xml;
    }

    public function robots(): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        if (config('app.debug')) {
            // Gelistirme/test ortami arama motorlarina kapali
            echo "User-agent: *\nDisallow: /\n";
            return;
        }
        echo "User-agent: *\nDisallow: /yonetim\n\nSitemap: " . url('/sitemap.xml') . "\n";
    }
}
