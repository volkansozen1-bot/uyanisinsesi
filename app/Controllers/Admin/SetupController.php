<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\View;

/**
 * Ilk yoneticiyi tarayicidan olusturma (terminal kullanmak istemeyenler icin).
 * Yalnizca admins tablosu bosken VE istek bu bilgisayardan (127.0.0.1 / ::1) geliyorsa calisir;
 * aksi halde sayfa yokmus gibi 404 doner.
 */
final class SetupController
{
    public function __construct()
    {
        AdminController::sendHeaders();
        if (!self::allowed()) {
            http_response_code(404);
            echo View::renderFile('errors/404');
            exit;
        }
    }

    public static function allowed(): bool
    {
        $ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '');
        if (!in_array($ip, ['127.0.0.1', '::1'], true)) {
            return false;
        }
        $row = Database::one('SELECT COUNT(*) AS n FROM admins');
        return (int) ($row['n'] ?? 0) === 0;
    }

    public function form(): void
    {
        View::render('admin/auth/setup', [
            'pageTitle' => 'İlk yönetici',
            'bare'      => true,
            'errors'    => $_SESSION['_errors'] ?? [],
            'old'       => $_SESSION['_old'] ?? [],
        ], 'layouts/admin');
        unset($_SESSION['_errors'], $_SESSION['_old']);
    }

    public function store(): void
    {
        if (!Csrf::verify()) {
            flash('admin_error', 'Oturum süresi doldu, lütfen yeniden deneyin.');
            redirect('/yonetim/kurulum');
        }

        $name     = trim((string) ($_POST['name'] ?? ''));
        $email    = mb_strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');
        $confirm  = (string) ($_POST['password_confirm'] ?? '');

        $errors = [];
        if ($name === '' || mb_strlen($name) > 100) {
            $errors['name'] = 'Adınızı yazın (en fazla 100 karakter).';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
            $errors['email'] = 'Geçerli bir e-posta adresi yazın.';
        }
        $problem = Auth::passwordProblem($password, $confirm);
        if ($problem !== null) {
            $errors['password'] = $problem;
        }

        if ($errors !== []) {
            $_SESSION['_errors'] = $errors;
            $_SESSION['_old']    = ['name' => $name, 'email' => $email];
            redirect('/yonetim/kurulum');
        }

        $now  = date('Y-m-d H:i:s');
        $hash = password_hash($password, PASSWORD_DEFAULT);
        Database::run(
            "INSERT INTO admins (name, email, password_hash, role, created_at, updated_at) VALUES (?, ?, ?, 'admin', ?, ?)",
            [$name, $email, $hash, $now, $now]
        );

        Auth::login(Database::lastId(), $hash);
        flash('admin_success', 'Yönetici hesabınız oluşturuldu. Hoş geldiniz.');
        redirect('/yonetim');
    }
}
