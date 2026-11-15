<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\View;

final class NewsletterController
{
    public function subscribe(): void
    {
        $back = $this->backPath();

        if (!Csrf::verify()) {
            flash('newsletter_error', 'Oturum süresi doldu, lütfen yeniden deneyin.');
            redirect($back);
        }

        $name  = trim((string) ($_POST['name'] ?? ''));
        $email = mb_strtolower(trim((string) ($_POST['email'] ?? '')));

        $isBot = trim((string) ($_POST['website'] ?? '')) !== '';
        if (!$isBot && (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190)) {
            flash('newsletter_error', 'Geçerli bir e-posta adresi yazın.');
            redirect($back);
        }

        if (!$isBot) {
            $now      = date('Y-m-d H:i:s');
            $existing = Database::one('SELECT id, unsubscribed_at FROM subscribers WHERE email = ?', [$email]);

            if ($existing === null) {
                Database::run(
                    'INSERT INTO subscribers (name, email, token, consent_at, ip, created_at, updated_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?)',
                    [mb_substr($name, 0, 150) ?: null, $email, bin2hex(random_bytes(32)), $now, client_ip(), $now, $now]
                );
            } elseif ($existing['unsubscribed_at'] !== null) {
                Database::run(
                    'UPDATE subscribers SET unsubscribed_at = NULL, consent_at = ?, updated_at = ? WHERE id = ?',
                    [$now, $now, $existing['id']]
                );
            }
            // TODO: Mailer eklendiginde /bulten/onay/{token} linkini iceren onay e-postasi gonderilecek.
        }

        // Adres kayitli olsa da ayni mesaj gosterilir (kimin abone oldugu disariya sizmasin)
        flash('newsletter_success', 'Teşekkürler, bülten kaydınız alındı.');
        redirect($back);
    }

    public function confirm(string $token): void
    {
        $ok = $this->isToken($token) && Database::run(
            'UPDATE subscribers SET confirmed_at = ?, updated_at = ? WHERE token = ? AND confirmed_at IS NULL',
            [date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), $token]
        )->rowCount() > 0;

        View::render('site/message', [
            'pageTitle' => 'Bülten',
            'heading'   => $ok ? 'Aboneliğiniz onaylandı' : 'Bağlantı geçersiz',
            'text'      => $ok ? 'Bültenimize hoş geldiniz.' : 'Bu onay bağlantısı geçersiz ya da daha önce kullanılmış.',
        ]);
    }

    public function unsubscribe(string $token): void
    {
        $ok = $this->isToken($token) && Database::run(
            'UPDATE subscribers SET unsubscribed_at = ?, updated_at = ? WHERE token = ? AND unsubscribed_at IS NULL',
            [date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), $token]
        )->rowCount() > 0;

        View::render('site/message', [
            'pageTitle' => 'Bülten',
            'heading'   => $ok ? 'Abonelikten çıkıldı' : 'Bağlantı geçersiz',
            'text'      => $ok ? 'Bundan sonra size bülten göndermeyeceğiz.' : 'Bu bağlantı geçersiz ya da daha önce kullanılmış.',
        ]);
    }

    private function isToken(string $token): bool
    {
        return (bool) preg_match('/^[a-f0-9]{64}$/', $token);
    }

    /** Formun gonderildigi sayfaya geri donmek icin; yalnizca site ici yollar kabul edilir. */
    private function backPath(): string
    {
        $back = (string) ($_POST['back'] ?? '/');
        return preg_match('#^/[a-z0-9/_-]*$#i', $back) ? $back : '/';
    }
}
