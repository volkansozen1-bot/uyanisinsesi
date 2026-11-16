<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Yonetim paneli oturumu.
 *   Auth::require();      // giris yoksa /yonetim/giris'e yonlendirir
 *   Auth::user()['name']; // girisli yonetici
 */
final class Auth
{
    public const IDLE_SECONDS  = 7200;  // 2 saat hareketsizlikte oturum duser
    // Kilit kurallari (pencere icindeki hatali deneme sayisi):
    //   ayni e-posta + IP: 5, ayni IP (tum e-postalar): 10, ayni e-posta (tum IP'ler): 30.
    // E-posta tek basina dusuk esikle kilitlenmez; yoksa herkes yoneticinin
    // e-postasiyla 5 kez deneyerek gercek yoneticiyi disarida birakabilirdi.
    public const MAX_ATTEMPTS       = 5;    // e-posta + IP cifti
    public const MAX_ATTEMPTS_IP    = 10;   // tek IP
    public const MAX_ATTEMPTS_EMAIL = 30;   // tek e-posta, tum IP'ler
    public const WINDOW_SECONDS     = 900;  // 15 dakika
    public const MIN_PASSWORD  = 10;

    private const KEY = '_admin';

    /** @var array<string, mixed>|null|false false: henuz bakilmadi */
    private static $user = false;

    /** @return array<string, mixed>|null */
    public static function user(): ?array
    {
        if (self::$user !== false) {
            return self::$user;
        }
        self::$user = null;

        $data = $_SESSION[self::KEY] ?? null;
        if (!is_array($data) || empty($data['id'])) {
            return null;
        }
        if (time() - (int) ($data['last'] ?? 0) > self::IDLE_SECONDS) {
            self::forget();
            flash('admin_error', 'Uzun süre işlem yapılmadığı için oturumunuz kapandı. Lütfen yeniden giriş yapın.');
            return null;
        }

        $admin = Database::one('SELECT id, name, email, password_hash, role FROM admins WHERE id = ?', [(int) $data['id']]);
        // Parola baska bir oturumda degistiyse bu oturum da gecersiz olur
        if ($admin === null || !hash_equals((string) ($data['pw'] ?? ''), self::fingerprint($admin['password_hash']))) {
            self::forget();
            return null;
        }

        $_SESSION[self::KEY]['last'] = time();
        unset($admin['password_hash']);
        self::$user = $admin;
        return $admin;
    }

    public static function check(): bool
    {
        return self::user() !== null;
    }

    /** Giris gerektiren her yonetim sayfasinin basinda cagrilir. */
    public static function require(): array
    {
        $user = self::user();
        if ($user === null) {
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
                $_SESSION['_admin_intended'] = current_path();
            }
            redirect('/yonetim/giris');
        }
        return $user;
    }

    /**
     * E-posta ve parolayi dener.
     * @return string 'ok' | 'invalid' | 'locked'
     */
    public static function attempt(string $email, string $password): string
    {
        $email = mb_strtolower(trim($email));
        $ip    = client_ip();

        if (self::isLocked($email, $ip)) {
            return 'locked';
        }

        $admin = $email === '' ? null : Database::one('SELECT id, password_hash FROM admins WHERE email = ?', [$email]);
        // Kullanici yoksa da ayni surede yanit vermek icin sahte bir hash dogrulanir
        $hash = $admin['password_hash'] ?? '$2y$10$vT1gHHfZEnl.k7jtEZUZGuo7yPeMmyJqZdlkxj3.hp0pQAzSulMK2';
        $ok   = password_verify($password, $hash) && $admin !== null;

        if (!$ok) {
            $now = date('Y-m-d H:i:s');
            Database::run(
                'INSERT INTO login_attempts (email, ip, attempted_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?)',
                [mb_substr($email, 0, 190), $ip, $now, $now, $now]
            );
            return 'invalid';
        }

        Database::run('DELETE FROM login_attempts WHERE email = ? AND ip = ?', [mb_substr($email, 0, 190), $ip]);
        // Eski kayitlari ara ara temizle
        Database::run('DELETE FROM login_attempts WHERE attempted_at < ?', [date('Y-m-d H:i:s', time() - 86400)]);

        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            Database::run('UPDATE admins SET password_hash = ?, updated_at = ? WHERE id = ?', [$hash, date('Y-m-d H:i:s'), $admin['id']]);
        }

        self::login((int) $admin['id'], $hash);
        return 'ok';
    }

    /** Uc kilit kuralindan biri asildi mi? (bkz. MAX_ATTEMPTS* sabitleri) */
    public static function isLocked(string $email, string $ip): bool
    {
        $email = mb_substr(mb_strtolower(trim($email)), 0, 190);
        $since = date('Y-m-d H:i:s', time() - self::WINDOW_SECONDS);

        $row = Database::one(
            'SELECT
                SUM(CASE WHEN ip = ? THEN 1 ELSE 0 END) AS by_ip,
                SUM(CASE WHEN email = ? THEN 1 ELSE 0 END) AS by_email,
                SUM(CASE WHEN ip = ? AND email = ? THEN 1 ELSE 0 END) AS by_pair
             FROM login_attempts WHERE attempted_at >= ? AND (ip = ? OR email = ?)',
            [$ip, $email, $ip, $email, $since, $ip, $email]
        );

        return (int) ($row['by_pair'] ?? 0) >= self::MAX_ATTEMPTS
            || (int) ($row['by_ip'] ?? 0) >= self::MAX_ATTEMPTS_IP
            || ($email !== '' && (int) ($row['by_email'] ?? 0) >= self::MAX_ATTEMPTS_EMAIL);
    }

    public static function login(int $adminId, string $passwordHash): void
    {
        session_regenerate_id(true);
        unset($_SESSION['_csrf']);
        $_SESSION[self::KEY] = [
            'id'   => $adminId,
            'pw'   => self::fingerprint($passwordHash),
            'last' => time(),
        ];
        Database::run('UPDATE admins SET last_login_at = ? WHERE id = ?', [date('Y-m-d H:i:s'), $adminId]);
        self::$user = false;
    }

    /** Parola degisince bu oturum gecerli kalir, digerleri duser. */
    public static function refreshPassword(string $passwordHash): void
    {
        session_regenerate_id(true);
        $_SESSION[self::KEY]['pw'] = self::fingerprint($passwordHash);
        self::$user = false;
    }

    public static function logout(): void
    {
        self::forget();
        session_regenerate_id(true);
        unset($_SESSION['_csrf']);
    }

    /** @return string|null hata mesaji ya da null */
    public static function passwordProblem(string $password, string $confirm): ?string
    {
        if (mb_strlen($password) < self::MIN_PASSWORD) {
            return 'Parola en az ' . self::MIN_PASSWORD . ' karakter olmalı.';
        }
        if (strlen($password) > 72) {
            return 'Parola en fazla 72 bayt olabilir.';
        }
        if ($password !== $confirm) {
            return 'Parolalar birbiriyle aynı değil.';
        }
        return null;
    }

    private static function forget(): void
    {
        unset($_SESSION[self::KEY], $_SESSION['_admin_intended']);
        self::$user = null;
    }

    private static function fingerprint(string $passwordHash): string
    {
        return substr(hash('sha256', $passwordHash), 0, 32);
    }
}
