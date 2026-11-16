<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Router;
use App\Core\View;

final class CourseController
{
    public function index(): void
    {
        View::render('site/courses', [
            'courses'   => Database::all(
                'SELECT slug, title, summary, image, start_date FROM courses WHERE is_active = 1 ORDER BY sort_order, id'
            ),
            'pageTitle' => 'Eğitimler',
        ]);
    }

    public function show(string $slug): void
    {
        $course = Database::one('SELECT * FROM courses WHERE slug = ? AND is_active = 1', [$slug]);
        if ($course === null) {
            Router::notFound();
            return;
        }

        View::render('site/course', [
            'course'  => $course,
            'modules' => Database::all(
                'SELECT title, description FROM course_modules WHERE course_id = ? ORDER BY sort_order, id',
                [$course['id']]
            ),
            'plans' => Database::all(
                'SELECT * FROM course_plans WHERE course_id = ? ORDER BY sort_order, id',
                [$course['id']]
            ),
            'faqs' => Database::all(
                "SELECT question, answer FROM faqs WHERE owner_type = 'course' AND owner_id = ? ORDER BY sort_order, id",
                [$course['id']]
            ),
            'pageTitle'       => $course['meta_title'] ?: $course['title'],
            'metaDescription' => (string) ($course['meta_description'] ?: $course['summary']),
            'ogImage'         => $course['image'] ?: null,
        ]);
    }
}
