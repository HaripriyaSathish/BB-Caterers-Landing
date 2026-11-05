<?php
declare(strict_types=1);

/**
 * Minimal dependency-free SMTP client (SSL / STARTTLS / plain, AUTH LOGIN).
 * Settings are read from the `settings` table (Admin → SMTP Settings).
 */
final class Mailer
{
    private $socket = null;
    private array $log = [];

    public function __construct(private array $cfg = [])
    {
        if (!$this->cfg) {
            $this->cfg = [
                'host'       => setting('smtp_host'),
                'port'       => (int) setting('smtp_port', 587),
                'encryption' => setting('smtp_encryption', 'tls'),
                'username'   => setting('smtp_username'),
                'password'   => decrypt_secret(setting('smtp_password')),
                'from_email' => setting('smtp_from_email') ?: setting('smtp_username'),
                'from_name'  => setting('smtp_from_name', setting('site_name', 'BB Caterers')),
            ];
        }
    }

    public static function isConfigured(): bool
    {
        return setting('smtp_host') !== '' && (setting('smtp_from_email') !== '' || setting('smtp_username') !== '');
    }

    public function lastLog(): string
    {
        return implode("\n", $this->log);
    }

    /**
     * @throws RuntimeException with a readable reason on failure
     */
    public function send(string $to, string $subject, string $html, ?string $replyTo = null, ?string $replyName = null): void
    {
        if (empty($this->cfg['host'])) {
            throw new RuntimeException('SMTP is not configured yet. Add your mail server in Admin → SMTP Settings.');
        }
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Invalid recipient email address.');
        }
        $from = $this->cfg['from_email'];
        if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('The "From email" in SMTP settings is not a valid email address.');
        }

        $enc = strtolower((string) $this->cfg['encryption']);
        $host = $this->cfg['host'];
        $port = (int) ($this->cfg['port'] ?: ($enc === 'ssl' ? 465 : 587));
        $remote = ($enc === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;

        $ctx = stream_context_create(['ssl' => ['verify_peer' => true, 'verify_peer_name' => true, 'SNI_enabled' => true]]);
        $this->socket = @stream_socket_client($remote, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $ctx);
        if (!$this->socket) {
            throw new RuntimeException("Could not connect to $host:$port — $errstr ($errno)");
        }
        stream_set_timeout($this->socket, 20);

        try {
            $this->expect(220);
            $ehloHost = preg_replace('/[^a-z0-9.-]/i', '', $_SERVER['SERVER_NAME'] ?? 'localhost') ?: 'localhost';
            $this->cmd("EHLO $ehloHost", 250);

            if ($enc === 'tls') {
                $this->cmd('STARTTLS', 220);
                if (!stream_socket_enable_crypto($this->socket, true, STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT | STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT)) {
                    throw new RuntimeException('STARTTLS negotiation failed.');
                }
                $this->cmd("EHLO $ehloHost", 250);
            }

            if (!empty($this->cfg['username'])) {
                $this->cmd('AUTH LOGIN', 334);
                $this->cmd(base64_encode($this->cfg['username']), 334, true);
                $this->cmd(base64_encode((string) $this->cfg['password']), 235, true);
            }

            $this->cmd("MAIL FROM:<$from>", 250);
            $this->cmd("RCPT TO:<$to>", [250, 251]);
            $this->cmd('DATA', 354);

            $this->write($this->buildMessage($from, $to, $subject, $html, $replyTo, $replyName) . "\r\n.");
            $this->expect(250);
            $this->cmd('QUIT', 221);
        } finally {
            if (is_resource($this->socket)) {
                fclose($this->socket);
            }
        }
    }

    private function buildMessage(string $from, string $to, string $subject, string $html, ?string $replyTo, ?string $replyName): string
    {
        $boundary = 'bb_' . bin2hex(random_bytes(8));
        $domain = substr(strrchr($from, '@'), 1) ?: 'localhost';
        $text = trim(html_entity_decode(strip_tags(preg_replace('#<br\s*/?>|</p>|</tr>#i', "\n", $html)), ENT_QUOTES, 'UTF-8'));

        $headers = [
            'Date: ' . date('r'),
            'From: ' . $this->encodeName((string) $this->cfg['from_name']) . " <$from>",
            "To: <$to>",
            'Subject: ' . $this->encodeName($subject),
            'Message-ID: <' . bin2hex(random_bytes(12)) . "@$domain>",
            'MIME-Version: 1.0',
            "Content-Type: multipart/alternative; boundary=\"$boundary\"",
        ];
        if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) {
            $headers[] = 'Reply-To: ' . ($replyName ? $this->encodeName($replyName) . ' ' : '') . "<$replyTo>";
        }

        $body = "--$boundary\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($text))
            . "--$boundary\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($html))
            . "--$boundary--";

        return implode("\r\n", $headers) . "\r\n\r\n" . $body;
    }

    private function encodeName(string $s): string
    {
        // Strip CR/LF to prevent header injection, then RFC 2047 encode.
        $s = str_replace(["\r", "\n"], '', $s);
        return '=?UTF-8?B?' . base64_encode($s) . '?=';
    }

    private function cmd(string $line, $expect, bool $secret = false): string
    {
        $this->log[] = 'C: ' . ($secret ? '********' : $line);
        $this->write($line);
        return $this->expect($expect);
    }

    private function write(string $data): void
    {
        fwrite($this->socket, $data . "\r\n");
    }

    private function expect($codes): string
    {
        $codes = (array) $codes;
        $response = '';
        while (($line = fgets($this->socket, 515)) !== false) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        $this->log[] = 'S: ' . trim($response);
        $code = (int) substr($response, 0, 3);
        if (!in_array($code, $codes, true)) {
            $msg = trim($response) ?: 'no response (timeout)';
            if ($code === 535) {
                $msg .= ' — check the SMTP username/password (Gmail needs an App Password).';
            }
            throw new RuntimeException('SMTP error: ' . $msg);
        }
        return $response;
    }
}

/** Branded HTML email wrapper used for all outgoing mail. */
function email_template(string $heading, string $bodyHtml): string
{
    $site = e(setting('site_name', 'BB Caterers'));
    $tag = e(setting('site_tagline', 'Bharatha Bhojanam'));
    $logo = media_url(setting('logo'));
    $logoHtml = $logo ? '<img src="' . e($logo) . '" alt="' . $site . '" height="56" style="display:block;margin:0 auto 8px">' : '';
    return <<<HTML
<!doctype html><html><body style="margin:0;background:#f7efdc;font-family:Arial,Helvetica,sans-serif;color:#2b2b2b">
<table width="100%" cellpadding="0" cellspacing="0" style="background:#f7efdc;padding:24px 12px"><tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#fffaf0;border-radius:14px;overflow:hidden;border:1px solid #ead9b4">
<tr><td style="background:#214A2B;padding:22px;text-align:center">{$logoHtml}
<div style="font-family:Georgia,serif;font-size:22px;color:#E5A93C">{$site}</div>
<div style="font-size:11px;letter-spacing:3px;color:#d9e4cf;text-transform:uppercase">{$tag}</div></td></tr>
<tr><td style="padding:28px 28px 8px"><h2 style="font-family:Georgia,serif;color:#214A2B;margin:0 0 14px;font-weight:normal">{$heading}</h2>{$bodyHtml}</td></tr>
<tr><td style="padding:18px 28px 26px;font-size:12px;color:#8a7a5a;border-top:1px solid #f0e3c4">{$site} · {$tag}</td></tr>
</table></td></tr></table></body></html>
HTML;
}
