<?php
declare(strict_types=1);

namespace App\Core;

use PDOException;
use Throwable;

/**
 * database/migrations/NNN_*.sql dosyalarini sirayla ve bir kez calistirir.
 * Calisanlar schema_migrations tablosuna yazilir.
 *
 * Eski kurulumlarda bazi migration'lar phpMyAdmin'den elle yuklenmis, yeni kurulumlarda ise
 * schema.sql'de zaten bulunuyor olabilir. Bu yuzden "sutun/tablo/indeks zaten var" hatalari
 * o ifade icin basarili sayilir ve devam edilir. Baska bir hata olursa durur ve o migration
 * kaydedilmez (sonraki calistirmada yeniden denenir).
 */
final class Migrator
{
    /** MySQL/MariaDB: 1050 tablo var, 1060 sutun var, 1061 indeks var, 1826 FK adi var */
    private const ALREADY_APPLIED_CODES = [1050, 1060, 1061, 1826];

    /** @return array{applied: string[], skipped: string[], error: ?string} */
    public static function run(): array
    {
        $result = ['applied' => [], 'skipped' => [], 'error' => null];

        Database::pdo()->exec(
            'CREATE TABLE IF NOT EXISTS schema_migrations (
                version    VARCHAR(100) NOT NULL PRIMARY KEY,
                applied_at DATETIME NOT NULL
            )'
        );
        $done = array_column(Database::all('SELECT version FROM schema_migrations'), 'version');

        $files = glob(BASE_PATH . '/database/migrations/[0-9][0-9][0-9]_*.sql') ?: [];
        sort($files, SORT_STRING);

        foreach ($files as $file) {
            $version = basename($file, '.sql');
            if (in_array($version, $done, true)) {
                $result['skipped'][] = $version;
                continue;
            }
            try {
                foreach (self::statements((string) file_get_contents($file)) as $sql) {
                    try {
                        Database::pdo()->exec($sql);
                    } catch (PDOException $e) {
                        if (!self::isAlreadyApplied($e)) {
                            throw $e;
                        }
                    }
                }
                Database::run(
                    'INSERT INTO schema_migrations (version, applied_at) VALUES (?, ?)',
                    [$version, date('Y-m-d H:i:s')]
                );
                $result['applied'][] = $version;
            } catch (Throwable $e) {
                $result['error'] = $version . ': ' . $e->getMessage();
                error_log('Migration hatasi ' . $result['error']);
                break;
            }
        }

        return $result;
    }

    /** SQL dosyasini ifadelere boler. Dosyalarda yalnizca duz ifadeler var (prosedur/trigger yok). */
    private static function statements(string $sql): array
    {
        $sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? '';
        $parts = preg_split('/;\s*(?:\R|$)/', $sql) ?: [];
        $out = [];
        foreach ($parts as $part) {
            $part = trim($part);
            // SET NAMES gibi oturum ayarlari PDO baglantisinda zaten utf8mb4
            if ($part === '' || preg_match('/^SET\s+NAMES\b/i', $part)) {
                continue;
            }
            $out[] = $part;
        }
        return $out;
    }

    private static function isAlreadyApplied(PDOException $e): bool
    {
        $code = (int) ($e->errorInfo[1] ?? 0);
        if (in_array($code, self::ALREADY_APPLIED_CODES, true)) {
            return true;
        }
        // SQLite (yerel test) mesajlari
        return (bool) preg_match('/duplicate column name|already exists/i', $e->getMessage());
    }
}
