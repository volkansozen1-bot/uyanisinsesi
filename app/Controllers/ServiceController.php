<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;
use App\Core\View;

final class ServiceController
{
    public function index(): void
    {
        View::render('site/services', [
            'services'  => Database::all(
                'SELECT slug, title, summary, image FROM services WHERE is_active = 1 ORDER BY sort_order, id'
            ),
            'pageTitle' => 'Hizmetler',
        ]);
    }

    public function show(string $slug): void
    {
        $service = Database::one('SELECT * FROM services WHERE slug = ? AND is_active = 1', [$slug]);
        if ($service === null) {
            Router::notFound();
            return;
        }

        View::render('site/service', [
            'service'  => $service,
            'sections' => Database::all(
                'SELECT * FROM service_sections WHERE service_id = ? ORDER BY sort_order, id',
                [$service['id']]
            ),
            'packages' => Database::all(
                'SELECT * FROM service_packages WHERE service_id = ? ORDER BY sort_order, id',
                [$service['id']]
            ),
            'faqs' => Database::all(
                "SELECT question, answer FROM faqs WHERE owner_type = 'service' AND owner_id = ? ORDER BY sort_order, id",
                [$service['id']]
            ),
            'testimonials' => Database::all(
                'SELECT name, `text`, rating FROM testimonials WHERE service_id = ? AND is_active = 1 ORDER BY sort_order, id',
                [$service['id']]
            ),
            'ogImage'         => $service['image'] ?: null,
            'pageTitle'       => $service['meta_title'] ?: $service['title'],
            'metaDescription' => (string) ($service['meta_description'] ?: $service['summary']),
        ]);
    }
}
