<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;

final class DashboardController extends AdminController
{
    public function index(): void
    {
        $now = date('Y-m-d H:i:s');

        $this->render('dashboard', [
            'pageTitle' => 'Panel',
            'stats'     => [
                'unread'      => $this->count('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0'),
                'messages'    => $this->count('SELECT COUNT(*) FROM contact_messages'),
                'confirmed'   => $this->count('SELECT COUNT(*) FROM subscribers WHERE confirmed_at IS NOT NULL AND unsubscribed_at IS NULL'),
                'subscribers' => $this->count('SELECT COUNT(*) FROM subscribers'),
                'posts'       => $this->count(
                    "SELECT COUNT(*) FROM posts WHERE status = 'published' AND published_at <= ?",
                    [$now]
                ),
                'services'    => $this->count('SELECT COUNT(*) FROM services WHERE is_active = 1'),
            ],
            'latest'    => Database::all(
                'SELECT id, first_name, last_name, email, message, is_read, created_at
                 FROM contact_messages ORDER BY created_at DESC, id DESC LIMIT 5'
            ),
        ]);
    }
}
