<?php
declare(strict_types=1);

/*
 * Yonetici olusturur (ya da var olanin parolasini yeniler).
 *
 *   php bin/create-admin.php "Ad Soyad" eposta@ornek.com
 *
 * XAMPP (Windows):
 *   C:\xampp\php\php.exe bin\create-admin.php "Ad Soyad" eposta@ornek.com
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/app/bootstrap.php';

use App\Core\Auth;
use App\Core\Database;

function fail(string $message): void
{
    fwrite(STDERR, $message . PHP_EOL);
    exit(1);
}

/** Parolayi ekrana yazdirmadan okur; Windows'ta gizleme yapilamadigi icin normal okur. */
function ask_password(string $prompt): string
{
    echo $prompt;
    $hidden = PHP_OS_FAMILY !== 'Windows' && function_exists('shell_exec') && stream_isatty(STDIN);
    if ($hidden) {
        shell_exec('stty -echo');
    }
    $line = fgets(STDIN);
    if ($hidden) {
        shell_exec('stty echo');
        echo PHP_EOL;
    }
    return rtrim((string) $line, "\r\n");
}

$name  = trim((string) ($argv[1] ?? ''));
$email = mb_strtolower(trim((string) ($argv[2] ?? '')));

if ($name === '' || $email === '') {
    fail('Kullanim: php bin/create-admin.php "Ad Soyad" eposta@ornek.com');
}
if (mb_strlen($name) > 100) {
    fail('Ad en fazla 100 karakter olabilir.');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
    fail('Gecerli bir e-posta adresi yazin.');
}

try {
    $existing = Database::one('SELECT id FROM admins WHERE email = ?', [$email]);
} catch (Throwable $e) {
    fail('Veritabanina baglanilamadi. .env dosyasindaki DB_ ayarlarini kontrol edin.' . PHP_EOL . $e->getMessage());
}

if ($existing !== null) {
    echo "Bu e-posta ile bir yonetici zaten var. Parolasi yenilensin mi? (e/h): ";
    if (strtolower(trim((string) fgets(STDIN))) !== 'e') {
        fail('Islem iptal edildi.');
    }
}

if (PHP_OS_FAMILY === 'Windows') {
    echo 'Not: Windows komut isteminde parola yazarken ekranda gorunur.' . PHP_EOL;
}

$password = ask_password('Parola (en az ' . Auth::MIN_PASSWORD . ' karakter): ');
$confirm  = ask_password('Parola (tekrar): ');
$problem  = Auth::passwordProblem($password, $confirm);
if ($problem !== null) {
    fail($problem);
}

$now  = date('Y-m-d H:i:s');
$hash = password_hash($password, PASSWORD_DEFAULT);

if ($existing !== null) {
    Database::run('UPDATE admins SET name = ?, password_hash = ?, updated_at = ? WHERE id = ?', [$name, $hash, $now, $existing['id']]);
    echo 'Parola yenilendi: ' . $email . PHP_EOL;
} else {
    Database::run(
        "INSERT INTO admins (name, email, password_hash, role, created_at, updated_at) VALUES (?, ?, ?, 'admin', ?, ?)",
        [$name, $email, $hash, $now, $now]
    );
    echo 'Yonetici olusturuldu: ' . $email . PHP_EOL;
}
echo 'Giris adresi: /yonetim/giris' . PHP_EOL;
