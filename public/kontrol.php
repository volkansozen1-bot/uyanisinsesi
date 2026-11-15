<?php
// Kurulum kontrol sayfasi. Yalnizca ayni bilgisayardan (localhost) acilir.
// Adres: http://localhost/uyanisinsesi/public/kontrol.php
// Canli siteye YUKLEMEYIN; kurulum bitince silebilirsiniz.

$ip = $_SERVER['REMOTE_ADDR'] ?? '';
if (!in_array($ip, ['127.0.0.1', '::1'], true)) {
    http_response_code(404);
    exit;
}

header('Content-Type: text/html; charset=utf-8');
$root = dirname(__DIR__);
$rows = [];
$add  = static function (string $label, bool $ok, string $detail) use (&$rows): void {
    $rows[] = [$label, $ok, $detail];
};

// 1. PHP
$add('PHP sürümü', version_compare(PHP_VERSION, '8.0.0', '>='), PHP_VERSION . ' (en az 8.0 gerekli)');
$add('pdo_mysql eklentisi', extension_loaded('pdo_mysql'), extension_loaded('pdo_mysql') ? 'yüklü' : 'php.ini içinde extension=pdo_mysql açılmalı');
$add('mbstring eklentisi', extension_loaded('mbstring'), extension_loaded('mbstring') ? 'yüklü' : 'php.ini içinde extension=mbstring açılmalı');

// 2. Dosyalar guncel mi
$helpers = @file_get_contents($root . '/app/helpers.php') ?: '';
$isNew   = strpos($helpers, 'function current_path') !== false;
$add('Dosyalar güncel', $isNew, $isNew ? 'güncel sürüm' : 'ESKİ sürüm: app/helpers.php içinde current_path yok. Dosyaları GitHub\'daki son sürümle değiştirin.');
$add('.htaccess', is_file(__DIR__ . '/.htaccess'), is_file(__DIR__ . '/.htaccess') ? 'var' : 'public/.htaccess eksik (gizli dosya kopyalanmamış olabilir)');

// 3. Veritabani
$env = [];
if (is_file($root . '/.env')) {
    foreach (file($root . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if ($line !== '' && $line[0] !== '#' && strpos($line, '=') !== false) {
            [$k, $v] = explode('=', $line, 2);
            $env[trim($k)] = trim(trim($v), "\"'");
        }
    }
}
$host = $env['DB_HOST'] ?? '127.0.0.1';
$port = $env['DB_PORT'] ?? '3306';
$name = $env['DB_NAME'] ?? 'uyanisinsesi';
$user = $env['DB_USER'] ?? 'root';
$pass = $env['DB_PASS'] ?? '';
$add('.env dosyası', true, is_file($root . '/.env') ? 'var' : 'yok (varsayılanlar kullanılıyor: veritabanı "' . $name . '", kullanıcı "' . $user . '", parola boş)');

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4", $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $add('Veritabanı bağlantısı', true, '"' . $name . '" veritabanına bağlanıldı');

    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $add('Tablolar (schema.sql)', count($tables) >= 19, count($tables) . ' tablo bulundu (19 olmalı)' . (count($tables) < 19 ? ': phpMyAdmin\'de database/schema.sql dosyasını içe aktarın' : ''));

    if (in_array('home_blocks', $tables, true)) {
        $blocks = (int) $pdo->query('SELECT COUNT(*) FROM home_blocks')->fetchColumn();
        $add('Başlangıç verisi (seed.sql)', $blocks > 0, $blocks . ' ana sayfa bölümü' . ($blocks === 0 ? ': phpMyAdmin\'de database/seed.sql dosyasını içe aktarın' : ''));
    }
} catch (Throwable $e) {
    $add('Veritabanı bağlantısı', false, $e->getMessage());
}

$allOk = !in_array(false, array_column($rows, 1), true);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="robots" content="noindex">
    <title>Kurulum kontrolü</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 52rem; margin: 2rem auto; padding: 0 1rem; }
        table { border-collapse: collapse; width: 100%; }
        td, th { border: 1px solid #ccc; padding: .5rem .75rem; text-align: left; vertical-align: top; }
        .ok { color: #1d6b3a; font-weight: 700; }
        .no { color: #a32020; font-weight: 700; }
    </style>
</head>
<body>
    <h1>Kurulum kontrolü</h1>
    <table>
        <tr><th>Kontrol</th><th>Durum</th><th>Ayrıntı</th></tr>
        <?php foreach ($rows as [$label, $ok, $detail]): ?>
        <tr>
            <td><?= htmlspecialchars($label) ?></td>
            <td class="<?= $ok ? 'ok' : 'no' ?>"><?= $ok ? 'TAMAM' : 'SORUN' ?></td>
            <td><?= htmlspecialchars($detail) ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <p><?= $allOk ? 'Her şey hazır. <a href="./">Siteyi aç</a>' : 'Kırmızı satırları düzeltip sayfayı yenileyin.' ?></p>
</body>
</html>
