<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\View;

final class ContactController
{
    public function form(): void
    {
        View::render('site/contact', [
            'pageTitle' => 'İletişim',
            'errors'    => $_SESSION['_errors'] ?? [],
            'old'       => $_SESSION['_old'] ?? [],
        ]);
        unset($_SESSION['_errors'], $_SESSION['_old']);
    }

    public function send(): void
    {
        if (!Csrf::verify()) {
            flash('error', 'Oturum süresi doldu, lütfen formu yeniden gönderin.');
            redirect('/iletisim');
        }

        // Honeypot: gercek kullanicinin gormedigi alan doluysa bot kabul edilir
        if (trim((string) ($_POST['website'] ?? '')) !== '') {
            flash('success', 'Mesajınız alındı. En kısa sürede dönüş yapacağız.');
            redirect('/iletisim');
        }

        $input = [
            'first_name' => trim((string) ($_POST['first_name'] ?? '')),
            'last_name'  => trim((string) ($_POST['last_name'] ?? '')),
            'email'      => trim((string) ($_POST['email'] ?? '')),
            'phone'      => trim((string) ($_POST['phone'] ?? '')),
            'message'    => trim((string) ($_POST['message'] ?? '')),
        ];

        $errors = [];
        if ($input['first_name'] === '' || mb_strlen($input['first_name']) > 100) {
            $errors['first_name'] = 'Adınızı yazın.';
        }
        if (mb_strlen($input['last_name']) > 100) {
            $errors['last_name'] = 'Soyad çok uzun.';
        }
        if (!filter_var($input['email'], FILTER_VALIDATE_EMAIL) || strlen($input['email']) > 190) {
            $errors['email'] = 'Geçerli bir e-posta adresi yazın.';
        }
        if (mb_strlen($input['phone']) > 30) {
            $errors['phone'] = 'Telefon numarası çok uzun.';
        }
        if (mb_strlen($input['message']) < 10 || mb_strlen($input['message']) > 5000) {
            $errors['message'] = 'Mesajınız 10 ile 5000 karakter arasında olmalı.';
        }
        if (empty($_POST['consent'])) {
            $errors['consent'] = 'Devam etmek için aydınlatma metnini onaylayın.';
        }

        if ($errors !== []) {
            $_SESSION['_errors'] = $errors;
            $_SESSION['_old']    = $input;
            redirect('/iletisim');
        }

        Database::run(
            'INSERT INTO contact_messages (first_name, last_name, email, phone, message, ip, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            [
                $input['first_name'],
                $input['last_name'] ?: null,
                $input['email'],
                $input['phone'] ?: null,
                $input['message'],
                client_ip(),
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s'),
            ]
        );

        // TODO: Mailer eklendiginde yoneticiye bildirim e-postasi gonderilecek.

        flash('success', 'Mesajınız alındı. En kısa sürede dönüş yapacağız.');
        redirect('/iletisim');
    }
}
