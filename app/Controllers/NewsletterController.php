<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\Mailer;
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

        // Acik riza olmadan kayit alinmaz
        if (!$isBot && empty($_POST['consent'])) {
            flash('newsletter_error', 'Abone olmak için onay kutusunu işaretleyin.');
            redirect($back);
        }

        if (!$isBot) {
            $this->register(mb_substr($name, 0, 150), $email);
        }

        // Adres kayitli ya da onayli olsa da ayni mesaj gosterilir (kimin abone oldugu disariya sizmasin)
        flash('newsletter_success', 'Onay e-postası gönderdik. Kaydı tamamlamak için e-postanızdaki bağlantıya tıklayın.');
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

    /**
     * Kaydi olusturur/yeniler ve gerekiyorsa onay e-postasi gonderir.
     * - Yeni adres: kayit + onay e-postasi
     * - Cikmis adres: yeni token, onay sifirlanir + onay e-postasi
     * - Onayli adres: hicbir sey yapilmaz
     * - Onay bekleyen adres: ayni token ile yeniden gonderilir, ama 10 dakikada en fazla bir kez
     */
    private function register(string $name, string $email): void
    {
        $now      = date('Y-m-d H:i:s');
        $existing = Database::one(
            'SELECT id, name, token, confirmed_at, unsubscribed_at FROM subscribers WHERE email = ?',
            [$email]
        );

        if ($existing === null) {
            $token = $this->newToken();
            Database::run(
                'INSERT INTO subscribers (name, email, token, consent_at, ip, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?)',
                [$name ?: null, $email, $token, $now, client_ip(), $now, $now]
            );
        } elseif ($existing['unsubscribed_at'] !== null) {
            $token = $this->newToken();
            Database::run(
                'UPDATE subscribers
                 SET name = COALESCE(?, name), token = ?, unsubscribed_at = NULL, confirmed_at = NULL,
                     consent_at = ?, ip = ?, updated_at = ?
                 WHERE id = ?',
                [$name ?: null, $token, $now, client_ip(), $now, $existing['id']]
            );
        } elseif ($existing['confirmed_at'] !== null) {
            return;
        } else {
            // Kosullu guncelleme: ayni anda gelen iki istekten yalnizca biri e-posta gonderir
            $limit = date('Y-m-d H:i:s', time() - 600);
            $sent  = Database::run(
                'UPDATE subscribers SET name = COALESCE(?, name), consent_at = ?, updated_at = ?
                 WHERE id = ? AND confirmed_at IS NULL AND unsubscribed_at IS NULL AND updated_at <= ?',
                [$name ?: null, $now, $now, $existing['id'], $limit]
            )->rowCount() > 0;
            if (!$sent) {
                return;
            }
            $token = (string) $existing['token'];
        }

        if ($name === '' && $existing !== null) {
            $name = (string) ($existing['name'] ?? '');
        }

        Mailer::send(
            $email,
            'Bülten kaydınızı onaylayın',
            Mailer::render('newsletter_confirm', [
                'title'          => 'Bülten kaydınızı onaylayın',
                'preheader'      => 'Kaydı tamamlamak için e-postadaki bağlantıya tıklayın.',
                'name'           => $name,
                'confirmUrl'     => url('/bulten/onay/' . $token),
                'unsubscribeUrl' => url('/bulten/cik/' . $token),
            ])
        );
    }

    private function newToken(): string
    {
        return bin2hex(random_bytes(32));
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
