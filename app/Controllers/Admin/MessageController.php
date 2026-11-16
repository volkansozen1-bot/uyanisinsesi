<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Database;

/** Iletisim formundan gelen mesajlar. */
final class MessageController extends AdminController
{
    public function index(): void
    {
        $pager = $this->paginate($this->count('SELECT COUNT(*) FROM contact_messages'));

        $this->render('messages/index', [
            'pageTitle' => 'Gelen Mesajlar',
            'pager'     => $pager,
            'messages'  => Database::all(
                'SELECT id, first_name, last_name, email, message, is_read, created_at FROM contact_messages
                 ORDER BY created_at DESC, id DESC LIMIT ' . $pager['limit'] . ' OFFSET ' . $pager['offset']
            ),
        ]);
    }

    public function show(string $id): void
    {
        $message = Database::one('SELECT * FROM contact_messages WHERE id = ?', [$this->id($id)]);
        if ($message === null) {
            $this->notFound();
        }
        if ((int) $message['is_read'] === 0) {
            Database::run('UPDATE contact_messages SET is_read = 1, updated_at = ? WHERE id = ?', [date('Y-m-d H:i:s'), $message['id']]);
        }

        $this->render('messages/show', [
            'pageTitle' => 'Mesaj',
            'message'   => $message,
        ]);
    }

    public function delete(string $id): void
    {
        $this->verifyCsrf('/yonetim/mesajlar');
        $deleted = Database::run('DELETE FROM contact_messages WHERE id = ?', [$this->id($id)])->rowCount() > 0;
        flash($deleted ? 'admin_success' : 'admin_error', $deleted ? 'Mesaj silindi.' : 'Mesaj bulunamadı.');
        redirect('/yonetim/mesajlar');
    }
}
