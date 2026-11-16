<?php
declare(strict_types=1);

namespace App\Core;

use RuntimeException;
use Throwable;

/**
 * Bagimliliksiz kucuk SMTP istemcisi.
 *
 *   Mailer::send('kisi@ornek.com', 'Konu', Mailer::render('contact_notification', [...]));
 *
 * Ayarlar .env'deki MAIL_* degerlerinden gelir (config('mail.*')).
 * MAIL_HOST bos ise e-posta gonderilmez, storage/logs/mail.log dosyasina yazilir (gelistirme).
 * Port 465: SMTPS (ssl://). Diger portlar: EHLO + STARTTLS. Sertifika her zaman dogrulanir.
 */
final class Mailer
{
    private const TIMEOUT = 10; // saniye; baglanti dahil tum SMTP konusmasi icin ust sinir

    /** @var resource|null */
    private $socket = null;
    private float $deadline = 0.0;

    /**
     * HTML e-posta gonderir; duz metin alternatifi otomatik uretilir.
     * Hata olursa exception firlatmaz: error_log'a yazar ve false doner.
     */
    public static function send(string $to, string $subject, string $htmlBody, ?string $replyTo = null): bool
    {
        try {
            $to = self::cleanHeader($to);
            if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('Gecersiz alici adresi: ' . $to);
            }
            if ($replyTo !== null) {
                $replyTo = self::cleanHeader($replyTo);
                if (!filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
                    error_log('Mailer: gecersiz Reply-To adresi yok sayildi: ' . $replyTo);
                    $replyTo = null;
                }
            }

            $cfg      = (array) config('mail', []);
            $host     = trim((string) ($cfg['host'] ?? ''));
            $from     = self::fromAddress($cfg);
            $fromName = self::cleanHeader((string) ($cfg['from_name'] ?? '')) ?: self::siteName();
            $subject  = self::cleanHeader($subject);
            $text     = self::htmlToText($htmlBody);

            if ($host === '') {
                return self::writeLog($to, $from, $fromName, $subject, $replyTo, $text, $htmlBody);
            }
            if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
                throw new RuntimeException('MAIL_FROM gecerli bir e-posta adresi olmali.');
            }

            $message = self::buildMessage($to, $from, $fromName, $subject, $replyTo, $text, $htmlBody);
            (new self())->deliver($cfg, $host, $from, $to, $message);
            return true;
        } catch (Throwable $e) {
            error_log('Mailer: e-posta gonderilemedi (' . $to . '): ' . $e->getMessage());
            return false;
        }
    }

    /** views/emails/<sablon>.php dosyasini views/emails/layout.php icinde HTML olarak dondurur. */
    public static function render(string $template, array $data = []): string
    {
        $content = View::renderFile('emails/' . $template, $data);
        return View::renderFile('emails/layout', $data + ['content' => $content]);
    }

    // --- SMTP -------------------------------------------------------------

    private function deliver(array $cfg, string $host, string $from, string $to, string $message): void
    {
        $port   = (int) ($cfg['port'] ?? 587) ?: 587;
        $user   = (string) ($cfg['user'] ?? '');
        $pass   = (string) ($cfg['pass'] ?? '');
        $secure = $port === 465;

        $this->deadline = microtime(true) + self::TIMEOUT;
        $context = stream_context_create(['ssl' => [
            'verify_peer'       => true,
            'verify_peer_name'  => true,
            'allow_self_signed' => false,
            'peer_name'         => $host,
            'SNI_enabled'       => true,
        ]]);

        $errno  = 0;
        $errstr = '';
        $socket = @stream_socket_client(
            ($secure ? 'ssl://' : 'tcp://') . $host . ':' . $port,
            $errno,
            $errstr,
            self::TIMEOUT,
            STREAM_CLIENT_CONNECT,
            $context
        );
        if ($socket === false) {
            throw new RuntimeException("SMTP sunucusuna baglanilamadi ($host:$port): $errstr ($errno)");
        }
        $this->socket = $socket;

        try {
            $this->expect([220]);
            $ext = $this->ehlo();

            if (!$secure) {
                if (preg_match('/^STARTTLS\b/mi', $ext)) {
                    $this->command('STARTTLS', [220]);
                    $this->refreshTimeout();
                    if (@stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT) !== true) {
                        throw new RuntimeException('STARTTLS basarisiz (sertifika dogrulanamadi olabilir).');
                    }
                    $ext = $this->ehlo();
                } elseif ($user !== '') {
                    // Sifre sifrelenmemis baglantida gonderilmez
                    throw new RuntimeException('Sunucu STARTTLS desteklemiyor; kimlik bilgileri sifresiz gonderilmedi.');
                }
            }

            if ($user !== '') {
                $this->authenticate($ext, $user, $pass);
            }

            $this->command('MAIL FROM:<' . $from . '>', [250]);
            $this->command('RCPT TO:<' . $to . '>', [250, 251]);
            $this->command('DATA', [354]);

            // Satir basindaki nokta ikilenir (RFC 5321 4.5.2)
            $data = preg_replace('/^\./m', '..', $message) ?? $message;
            $this->write($data . "\r\n.\r\n");
            $this->expect([250]);

            try {
                $this->command('QUIT', [221]);
            } catch (Throwable $e) {
                // Mesaj teslim edildi; QUIT yaniti onemli degil
            }
        } finally {
            fclose($this->socket);
            $this->socket = null;
        }
    }

    private function ehlo(): string
    {
        return $this->command('EHLO ' . self::heloName(), [250]);
    }

    private function authenticate(string $ext, string $user, string $pass): void
    {
        $methods = preg_match('/^AUTH[ =](.*)$/mi', $ext, $m) ? strtoupper($m[1]) : '';
        if (preg_match('/\bPLAIN\b/', $methods)) {
            $this->command('AUTH PLAIN ' . base64_encode("\0" . $user . "\0" . $pass), [235], true);
            return;
        }
        $this->command('AUTH LOGIN', [334]);
        $this->command(base64_encode($user), [334], true);
        $this->command(base64_encode($pass), [235], true);
    }

    /** Komutu gonderir, yanit kodunu kontrol eder ve yanit metnini doner. */
    private function command(string $line, array $codes, bool $secret = false): string
    {
        $this->write($line . "\r\n");
        try {
            return $this->expect($codes);
        } catch (RuntimeException $e) {
            $shown = $secret ? '[gizli]' : $line;
            throw new RuntimeException($shown . ' -> ' . $e->getMessage());
        }
    }

    /** Cok satirli yaniti okur ("250-..." ... "250 ..."). */
    private function expect(array $codes): string
    {
        $response = '';
        while (true) {
            $this->refreshTimeout();
            $line = fgets($this->socket, 1024);
            if ($line === false) {
                $meta = stream_get_meta_data($this->socket);
                throw new RuntimeException(!empty($meta['timed_out']) ? 'SMTP zaman asimi' : 'SMTP baglantisi kapandi');
            }
            $response .= $line;
            if (strlen($line) < 4 || $line[3] !== '-') {
                break;
            }
        }
        $code = (int) substr($response, 0, 3);
        if (!in_array($code, $codes, true)) {
            throw new RuntimeException('Beklenmeyen SMTP yaniti: ' . trim($response));
        }
        // Kod ve ayraci atarak yalnizca metni don (EHLO uzantilarini aramak icin)
        return (string) preg_replace('/^\d{3}[ -]/m', '', $response);
    }

    private function write(string $data): void
    {
        while ($data !== '') {
            $this->refreshTimeout();
            $written = @fwrite($this->socket, $data);
            if ($written === false || $written === 0) {
                throw new RuntimeException('SMTP sunucusuna yazilamadi');
            }
            $data = (string) substr($data, $written);
        }
    }

    /** Kalan sureyi sokete uygular; toplam sure 10 sn'yi gecemez. */
    private function refreshTimeout(): void
    {
        $left = $this->deadline - microtime(true);
        if ($left <= 0) {
            throw new RuntimeException('SMTP zaman asimi');
        }
        stream_set_timeout($this->socket, (int) floor($left), (int) (fmod($left, 1.0) * 1000000));
    }

    // --- Mesaj --------------------------------------------------------------

    private static function buildMessage(
        string $to,
        string $from,
        string $fromName,
        string $subject,
        ?string $replyTo,
        string $text,
        string $html
    ): string {
        $domain   = substr((string) strrchr($from, '@'), 1) ?: 'localhost';
        $boundary = '=_alt_' . bin2hex(random_bytes(12));

        $headers = [
            'Date: ' . date('r'),
            'Message-ID: <' . bin2hex(random_bytes(16)) . '@' . $domain . '>',
            'From: ' . self::address($from, $fromName),
            'To: <' . $to . '>',
        ];
        if ($replyTo !== null) {
            $headers[] = 'Reply-To: <' . $replyTo . '>';
        }
        $headers[] = 'Subject: ' . self::encodeHeader($subject);
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: multipart/alternative; boundary="' . $boundary . '"';

        $part = static function (string $type, string $body) use ($boundary): string {
            return '--' . $boundary . "\r\n"
                . 'Content-Type: ' . $type . '; charset=UTF-8' . "\r\n"
                . 'Content-Transfer-Encoding: base64' . "\r\n\r\n"
                . chunk_split(base64_encode($body), 76, "\r\n");
        };

        return implode("\r\n", $headers) . "\r\n\r\n"
            . 'Bu e-posta MIME bicimindedir.' . "\r\n\r\n"
            . $part('text/plain', $text)
            . $part('text/html', $html)
            . '--' . $boundary . '--';
    }

    private static function address(string $email, string $name): string
    {
        return $name === '' ? '<' . $email . '>' : self::encodeHeader($name, true) . ' <' . $email . '>';
    }

    /** UTF-8 basliklar icin =?UTF-8?B?...?= kodlamasi; her parca 75 karakteri gecmez. */
    private static function encodeHeader(string $value, bool $phrase = false): string
    {
        $safe = $phrase ? '/^[A-Za-z0-9 ]*$/' : '/^[\x20-\x7E]*$/';
        if (preg_match($safe, $value) && strlen($value) <= 900) {
            return $value;
        }
        $words = [];
        $chunk = '';
        foreach (mb_str_split($value, 1, 'UTF-8') as $char) {
            if (strlen($chunk . $char) > 39) { // 39 bayt -> 52 karakter base64; "Subject: " ile 78 alti
                $words[] = $chunk;
                $chunk   = '';
            }
            $chunk .= $char;
        }
        if ($chunk !== '') {
            $words[] = $chunk;
        }
        return implode("\r\n ", array_map(static function (string $w): string {
            return '=?UTF-8?B?' . base64_encode($w) . '?=';
        }, $words));
    }

    /** Baslik enjeksiyonuna karsi CR/LF ve kontrol karakterlerini temizler. */
    private static function cleanHeader(string $value): string
    {
        return trim((string) preg_replace('/[\x00-\x1F\x7F]+/', ' ', $value));
    }

    /** HTML'den okunabilir duz metin uretir; baglantilar "metin (adres)" olur. */
    public static function htmlToText(string $html): string
    {
        $html = (string) preg_replace('#<(head|style|script)\b.*?</\1>#is', '', $html);
        // Gizli on metin (preheader) duz metinde tekrar etmesin
        $html = (string) preg_replace('#<(div|span)\b[^>]*display:\s*none[^>]*>.*?</\1>#is', '', $html);
        // HTML'deki satir sonlari bosluktur; satirlar asagida etiketlerden uretilir
        $html = (string) preg_replace('/\s+/u', ' ', $html);
        $html = (string) preg_replace('#</t[dh]>\s*<t[dh]\b#i', ': $0', $html);
        $html = (string) preg_replace_callback(
            '#<a\b[^>]*href=("|\')(.*?)\1[^>]*>(.*?)</a>#is',
            static function (array $m): string {
                $label = trim(strip_tags($m[3]));
                $href  = html_entity_decode($m[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                if ($label === '' || $label === $href || 'mailto:' . $label === $href) {
                    return $label !== '' ? $label : $href;
                }
                return $label . ' (' . $href . ')';
            },
            $html
        );
        $html = (string) preg_replace('#<br\s*/?>#i', "\n", $html);
        $html = (string) preg_replace('#</(tr|li)>#i', "\n", $html);
        $html = (string) preg_replace('#</(p|div|h[1-6]|table|blockquote)>#i', "\n\n", $html);
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = (string) preg_replace('/[ \t\x{00A0}]+/u', ' ', $text);
        $text = (string) preg_replace('/ *\n */', "\n", $text);
        $text = (string) preg_replace("/\n{3,}/", "\n\n", $text);
        return trim($text) . "\n";
    }

    // --- Yardimcilar --------------------------------------------------------

    private static function fromAddress(array $cfg): string
    {
        $from = self::cleanHeader((string) ($cfg['from'] ?? ''));
        if ($from === '' && filter_var((string) ($cfg['user'] ?? ''), FILTER_VALIDATE_EMAIL)) {
            $from = (string) $cfg['user'];
        }
        return $from !== '' ? $from : 'noreply@' . self::heloName();
    }

    private static function heloName(): string
    {
        $host = (string) parse_url((string) config('app.url'), PHP_URL_HOST);
        return preg_match('/^[a-z0-9.-]+$/i', $host) ? $host : 'localhost';
    }

    private static function siteName(): string
    {
        try {
            return self::cleanHeader(setting('site_name'));
        } catch (Throwable $e) {
            return '';
        }
    }

    /** Gelistirme: e-posta gonderilmez, okunur bicimde storage/logs/mail.log dosyasina eklenir. */
    private static function writeLog(
        string $to,
        string $from,
        string $fromName,
        string $subject,
        ?string $replyTo,
        string $text,
        string $html
    ): bool {
        $entry = str_repeat('=', 72) . "\n"
            . 'Tarih: ' . date('Y-m-d H:i:s') . "  (MAIL_HOST bos: e-posta gonderilmedi)\n"
            . 'Kimden: ' . ($fromName !== '' ? $fromName . ' ' : '') . '<' . $from . ">\n"
            . 'Kime: ' . $to . "\n"
            . ($replyTo !== null ? 'Yanit: ' . $replyTo . "\n" : '')
            . 'Konu: ' . $subject . "\n"
            . "--- metin ---\n" . $text
            . "--- html ---\n" . $html . "\n";

        $dir = BASE_PATH . '/storage/logs';
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new RuntimeException('Log klasoru olusturulamadi: ' . $dir);
        }
        if (@file_put_contents($dir . '/mail.log', $entry, FILE_APPEND | LOCK_EX) === false) {
            throw new RuntimeException('mail.log dosyasina yazilamadi');
        }
        return true;
    }
}
