<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\Mailer;
use App\Core\View;

final class NewsletterController
{
    /** Ayni IP'den saatte en fazla bu kadar kayit denemesi */
    private const RATE_LIMIT = 5;

    public function subscribe(): void
    {
        $back = $this->backPath();

        if (!Csrf::verify()) {
            flash('newsletter_error', 'Oturum süresi doldu, lütfen yeniden deneyin.');
            redirect($back);
        }

        $name  = trim(input_string($_POST, 'name'));
        $email = mb_strtolower(trim(input_string($_POST, 'email')));

        $isBot = trim(input_string($_POST, 'website')) !== '';
        if (!$isBot && (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190)) {
            flash('newsletter_error', 'Geçerli bir e-posta adresi yazın.');
            redirect($back);
        }

        // Acik riza olmadan kayit alinmaz
        if (!$isBot && input_string($_POST, 'consent') === '') {
            flash('newsletter_error', 'Abone olmak için onay kutusunu işaretleyin.');
            redirect($back);
        }

        if (!$isBot && $this->tooManyAttempts()) {
            flash('newsletter_error', 'Çok fazla deneme yapıldı, lütfen biraz sonra tekrar deneyin.');
            redirect($back);
        }

        if (!$isBot) {
            $this->register(mb_substr($name, 0, 150), $email);
        }

        // Adres kayitli ya da onayli olsa da ayni mesaj gosterilir (kimin abone oldugu disariya sizmasin)
        flash('newsletter_success', 'Onay e-postası gönderdik. Kaydı tamamlamak için e-postanızdaki bağlantıya tıklayın.');
        redirect($back);
    }

    /**
     * GET: yalnizca onay dugmesi gosterir (e-posta tarayicilari baglantiyi acsa da durum degismez).
     * POST: CSRF dogrulanir ve abonelik onaylanir.
     */
    public function confirm(string $token): void
    {
        $sub   = $this->find($token);
        $state = $this->confirmState($sub);

        if ($state === 'pending' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            if (!Csrf::verify()) {
                $this->actionPage('confirm', $token, 'Oturum süresi doldu, lütfen düğmeye yeniden tıklayın.');
                return;
            }
            $now = date('Y-m-d H:i:s');
            $ok  = Database::run(
                'UPDATE subscribers SET confirmed_at = ?, updated_at = ?
                 WHERE id = ? AND confirmed_at IS NULL AND unsubscribed_at IS NULL',
                [$now, $now, $sub['id']]
            )->rowCount() > 0;
            if ($ok) {
                $this->message('Aboneliğiniz onaylandı', 'Bültenimize hoş geldiniz.');
                return;
            }
            $state = $this->confirmState($this->find($token));
        }

        if ($state === 'pending') {
            $this->actionPage('confirm', $token);
        } elseif ($state === 'confirmed') {
            $this->message('Aboneliğiniz zaten onaylı', 'Bu e-posta adresi bültenimize kayıtlı. Ayrıca bir işlem yapmanıza gerek yok.');
        } else {
            $this->message('Bağlantı geçersiz', 'Bu onay bağlantısı geçersiz ya da artık kullanılamıyor.');
        }
    }

    /** GET: "Abonelikten cik" dugmesi; POST: CSRF dogrulanir ve abonelik sonlandirilir. */
    public function unsubscribe(string $token): void
    {
        $sub = $this->find($token);
        if ($sub === null) {
            $this->message('Bağlantı geçersiz', 'Bu bağlantı geçersiz ya da artık kullanılamıyor.');
            return;
        }
        if ($sub['unsubscribed_at'] !== null) {
            $this->message('Abonelikten çıkıldı', 'Bu e-posta adresine bülten gönderilmiyor.');
            return;
        }

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            if (!Csrf::verify()) {
                $this->actionPage('unsubscribe', $token, 'Oturum süresi doldu, lütfen düğmeye yeniden tıklayın.');
                return;
            }
            $now = date('Y-m-d H:i:s');
            Database::run(
                'UPDATE subscribers SET unsubscribed_at = ?, updated_at = ? WHERE id = ? AND unsubscribed_at IS NULL',
                [$now, $now, $sub['id']]
            );
            $this->message('Abonelikten çıkıldı', 'Bundan sonra size bülten göndermeyeceğiz.');
            return;
        }

        $this->actionPage('unsubscribe', $token);
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
        // Host basligina guvenilmez: APP_URL yoksa (ve yerel degilse) kayit da e-posta da yapilmaz
        if (trusted_base_url() === null) {
            error_log('Bulten: APP_URL tanimli degil; onay e-postasi gonderilmedi (.env icine APP_URL yazin).');
            return;
        }

        $now      = date('Y-m-d H:i:s');
        $existing = Database::one(
            'SELECT id, token, confirmed_at, unsubscribed_at FROM subscribers WHERE email = ?',
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
                'UPDATE subscribers SET name = COALESCE(?, name), consent_at = ?, ip = ?, updated_at = ?
                 WHERE id = ? AND confirmed_at IS NULL AND unsubscribed_at IS NULL AND updated_at <= ?',
                [$name ?: null, $now, client_ip(), $now, $existing['id'], $limit]
            )->rowCount() > 0;
            if (!$sent) {
                return;
            }
            $token = (string) $existing['token'];
        }

        // Ad e-postada kullanilmaz: formdan gelen metin oltalama/spam icerigi tasiyabilir.
        // Cikis baglantisi da yok: kisi henuz abone degil.
        Mailer::send(
            $email,
            'Bülten kaydınızı onaylayın',
            Mailer::render('newsletter_confirm', [
                'title'      => 'Bülten kaydınızı onaylayın',
                'preheader'  => 'Kaydı tamamlamak için e-postadaki bağlantıya tıklayın.',
                'confirmUrl' => (string) trusted_url('/bulten/onay/' . $token),
            ])
        );
    }

    /** Son bir saatte bu IP'den olusturulan/yenilenen kayit sayisi sinira ulasti mi? */
    private function tooManyAttempts(): bool
    {
        $since = date('Y-m-d H:i:s', time() - 3600);
        $row   = Database::one(
            'SELECT COUNT(*) AS n FROM subscribers WHERE ip = ? AND (created_at >= ? OR updated_at >= ?)',
            [client_ip(), $since, $since]
        );
        return (int) ($row['n'] ?? 0) >= self::RATE_LIMIT;
    }

    /** @return array<string, mixed>|null */
    private function find(string $token): ?array
    {
        if (!$this->isToken($token)) {
            return null;
        }
        return Database::one(
            'SELECT id, confirmed_at, unsubscribed_at FROM subscribers WHERE token = ?',
            [$token]
        );
    }

    /** 'pending' | 'confirmed' | 'invalid' */
    private function confirmState(?array $sub): string
    {
        if ($sub === null || $sub['unsubscribed_at'] !== null) {
            return 'invalid';
        }
        return $sub['confirmed_at'] !== null ? 'confirmed' : 'pending';
    }

    private function actionPage(string $action, string $token, ?string $error = null): void
    {
        $confirm = $action === 'confirm';
        View::render('site/newsletter_action', [
            'pageTitle' => 'Bülten',
            'heading'   => $confirm ? 'Bülten aboneliğinizi onaylayın' : 'Bülten aboneliğinden çıkın',
            'text'      => $confirm
                ? 'Kaydı tamamlamak için aşağıdaki düğmeye tıklayın.'
                : 'Bülten e-postalarını artık almak istemiyorsanız aşağıdaki düğmeye tıklayın.',
            'action'    => $confirm ? '/bulten/onay/' . $token : '/bulten/cik/' . $token,
            'button'    => $confirm ? 'Aboneliği onayla' : 'Abonelikten çık',
            'error'     => $error,
        ]);
    }

    private function message(string $heading, string $text): void
    {
        View::render('site/message', [
            'pageTitle' => 'Bülten',
            'heading'   => $heading,
            'text'      => $text,
        ]);
    }

    private function newToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    private function isToken(string $token): bool
    {
        return (bool) preg_match('/^[a-f0-9]{64}$/', $token);
    }

    /** Formun gonderildigi sayfaya geri donmek icin; yalnizca site ici yollar ("//host" degil) kabul edilir. */
    private function backPath(): string
    {
        $back = input_string($_POST, 'back');
        return preg_match('#^/(?!/)[a-z0-9/_-]*$#i', $back) ? $back : '/';
    }
}
