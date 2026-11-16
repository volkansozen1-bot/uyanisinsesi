<?php
declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\View;

/** Giris ve cikis. AdminController'dan turemez; giris sayfasi oturumsuz acilir. */
final class AuthController
{
    public function __construct()
    {
        AdminController::sendHeaders();
    }

    public function form(): void
    {
        if (Auth::check()) {
            redirect('/yonetim');
        }
        View::render('admin/auth/login', [
            'pageTitle' => 'Giriş',
            'bare'      => true,
            'email'     => (string) ($_SESSION['_admin_old_email'] ?? ''),
        ], 'layouts/admin');
        unset($_SESSION['_admin_old_email']);
    }

    public function login(): void
    {
        if (!Csrf::verify()) {
            flash('admin_error', 'Oturum süresi doldu, lütfen yeniden deneyin.');
            redirect('/yonetim/giris');
        }

        $email    = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $result   = Auth::attempt($email, $password);

        if ($result === 'ok') {
            $target = (string) ($_SESSION['_admin_intended'] ?? '/yonetim');
            unset($_SESSION['_admin_intended']);
            // Yalnizca panel ici adreslere don
            if ($target !== '/yonetim' && !str_starts_with($target, '/yonetim/')) {
                $target = '/yonetim';
            }
            redirect($target);
        }

        $_SESSION['_admin_old_email'] = mb_substr($email, 0, 190);
        flash('admin_error', $result === 'locked'
            ? 'Çok fazla hatalı deneme yapıldı. Lütfen 15 dakika sonra yeniden deneyin.'
            : 'E-posta veya parola hatalı.');
        redirect('/yonetim/giris');
    }

    public function logout(): void
    {
        if (Csrf::verify()) {
            Auth::logout();
            flash('admin_success', 'Çıkış yaptınız.');
        }
        redirect('/yonetim/giris');
    }
}
