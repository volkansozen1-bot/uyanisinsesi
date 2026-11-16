<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Database;

final class PasswordController extends AdminController
{
    public function form(): void
    {
        $this->render('password', [
            'pageTitle' => 'Parolam',
            'errors'    => $_SESSION['_errors'] ?? [],
        ]);
        unset($_SESSION['_errors']);
    }

    public function save(): void
    {
        $this->verifyCsrf('/yonetim/parola');

        $current  = $this->post('current_password');
        $password = $this->post('password');
        $confirm  = $this->post('password_confirm');

        $row    = Database::one('SELECT password_hash FROM admins WHERE id = ?', [$this->admin['id']]);
        $errors = [];
        if ($row === null || !password_verify($current, $row['password_hash'])) {
            $errors['current_password'] = 'Mevcut parola hatalı.';
        }
        $problem = Auth::passwordProblem($password, $confirm);
        if ($problem !== null) {
            $errors['password'] = $problem;
        } elseif ($password === $current) {
            $errors['password'] = 'Yeni parola eskisinden farklı olmalı.';
        }

        if ($errors !== []) {
            $_SESSION['_errors'] = $errors;
            redirect('/yonetim/parola');
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        Database::run('UPDATE admins SET password_hash = ?, updated_at = ? WHERE id = ?', [$hash, date('Y-m-d H:i:s'), $this->admin['id']]);
        Auth::refreshPassword($hash);

        flash('admin_success', 'Parolanız değiştirildi. Diğer cihazlardaki oturumlar kapandı.');
        redirect('/yonetim/parola');
    }
}
