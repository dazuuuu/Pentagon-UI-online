<?php

/**
 * Lightweight SMTP mailer (no Composer dependency).
 * Supports AUTH LOGIN / AUTH PLAIN over TLS, SSL, or plain.
 */
class Mailer
{
    private array $smtp;
    private string $lastError = '';

    public function __construct(?array $smtp = null)
    {
        $this->smtp = $smtp ?? $this->loadSettings();
    }

    public function getLastError(): string
    {
        return $this->lastError;
    }

    public function isConfigured(): bool
    {
        return trim((string)($this->smtp['host'] ?? '')) !== ''
            && trim((string)($this->smtp['from_email'] ?? '')) !== '';
    }

    private function loadSettings(): array
    {
        $defaults = config('smtp') ?? [];
        $defaults = [
            'host' => (string)($defaults['host'] ?? ''),
            'port' => (int)($defaults['port'] ?? 587),
            'encryption' => (string)($defaults['encryption'] ?? 'tls'),
            'username' => (string)($defaults['username'] ?? ''),
            'password' => (string)($defaults['password'] ?? ''),
            'from_email' => (string)($defaults['from_email'] ?? ''),
            'from_name' => (string)($defaults['from_name'] ?? 'Pentagon Quest'),
        ];

        try {
            $rows = Database::get()
                ->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'smtp_%'")
                ->fetchAll();
            foreach ($rows as $row) {
                $key = substr($row['setting_key'], 5); // smtp_host → host
                $value = trim((string)($row['setting_value'] ?? ''));
                if ($key === 'port') {
                    if ($value !== '') {
                        $defaults['port'] = (int)$value;
                    }
                    continue;
                }
                // Don't let blank admin fields wipe a valid env/config fallback
                if ($value !== '' || !isset($defaults[$key]) || $defaults[$key] === '') {
                    $defaults[$key] = $value;
                }
            }
        } catch (Throwable $e) {
            // Settings table may not exist yet
        }

        $defaults['host'] = trim((string)$defaults['host']);
        $defaults['username'] = trim((string)$defaults['username']);
        $defaults['password'] = (string)$defaults['password']; // keep spaces inside app passwords? trim ends only
        $defaults['password'] = trim($defaults['password']);
        $defaults['from_email'] = trim((string)$defaults['from_email']);
        $defaults['from_name'] = trim((string)$defaults['from_name']) ?: 'Pentagon Quest';
        $defaults['encryption'] = strtolower(trim((string)$defaults['encryption']) ?: 'tls');
        $defaults['port'] = (int)($defaults['port'] ?: 587);

        return $defaults;
    }

    public function send(string $to, string $subject, string $htmlBody, ?string $textBody = null, array $headers = []): bool
    {
        $this->lastError = '';
        $to = trim($to);
        $fromEmail = trim((string)($this->smtp['from_email'] ?? ''));
        $fromName = (string)($this->smtp['from_name'] ?? 'Pentagon Quest');

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->lastError = 'Invalid recipient email address.';
            $this->logEmail($to, $subject, $htmlBody, 'failed', $this->lastError);
            return false;
        }

        if (!$this->isConfigured()) {
            $this->lastError = 'SMTP is not configured. Set host and from address in Admin → Settings.';
            $this->logEmail($to, $subject, $htmlBody, 'failed', $this->lastError);
            return false;
        }

        $textBody = $textBody ?? strip_tags(str_replace(['<br>', '<br/>', '<br />', '</p>'], ["\n", "\n", "\n", "\n\n"], $htmlBody));
        $boundary = 'b_' . bin2hex(random_bytes(8));

        $msgHeaders = [
            'Date: ' . date('r'),
            'From: ' . $this->encodeAddress($fromEmail, $fromName),
            'To: ' . $this->encodeAddress($to),
            'Reply-To: ' . $this->encodeAddress($fromEmail, $fromName),
            'Subject: ' . $this->encodeHeader($subject),
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
            'X-Mailer: PentagonQuest-Mailer/1.0',
        ];
        foreach ($headers as $h) {
            $msgHeaders[] = $h;
        }

        $body = "--{$boundary}\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($textBody))
            . "--{$boundary}\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($htmlBody))
            . "--{$boundary}--\r\n";

        $message = implode("\r\n", $msgHeaders) . "\r\n\r\n" . $body;
        // RFC 5321: dot-stuff any line that begins with "."
        $message = preg_replace('/^\./m', '..', $message) ?? $message;

        $ok = $this->smtpSend($fromEmail, $to, $message);
        $this->logEmail($to, $subject, $htmlBody, $ok ? 'sent' : 'failed', $ok ? null : $this->lastError);
        return $ok;
    }

    public function sendTemplate(string $to, string $subject, string $title, string $contentHtml, ?string $ctaLabel = null, ?string $ctaUrl = null): bool
    {
        $brand = e(config('app_name', 'Pentagon Quest'));
        $cta = '';
        if ($ctaLabel && $ctaUrl) {
            $cta = '<p style="margin:28px 0"><a href="' . e($ctaUrl) . '" style="background:#1a5c3a;color:#fff;padding:12px 22px;text-decoration:none;border-radius:4px;display:inline-block;font-weight:600">' . e($ctaLabel) . '</a></p>';
        }
        $html = '<!DOCTYPE html><html><body style="margin:0;background:#f4f6f4;font-family:Georgia,serif">'
            . '<div style="max-width:600px;margin:24px auto;background:#fff;border-top:4px solid #c9a227">'
            . '<div style="padding:24px 28px;border-bottom:1px solid #eee"><strong style="color:#1a5c3a;font-size:20px">' . $brand . '</strong></div>'
            . '<div style="padding:28px"><h1 style="margin:0 0 12px;font-size:22px;color:#111">' . e($title) . '</h1>'
            . '<div style="color:#333;line-height:1.6;font-size:15px">' . $contentHtml . '</div>' . $cta
            . '</div><div style="padding:16px 28px;background:#111;color:#c9a227;font-size:12px">&copy; ' . date('Y') . ' ' . $brand . '</div></div></body></html>';
        return $this->send($to, $subject, $html);
    }

    private function ehloName(): string
    {
        $from = (string)($this->smtp['from_email'] ?? '');
        if (str_contains($from, '@')) {
            return substr(strrchr($from, '@'), 1) ?: 'localhost';
        }
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return preg_replace('/:\d+$/', '', $host) ?: 'localhost';
    }

    private function smtpSend(string $from, string $to, string $data): bool
    {
        $host = $this->smtp['host'];
        $port = (int)($this->smtp['port'] ?? 587);
        $enc = strtolower((string)($this->smtp['encryption'] ?? 'tls'));
        $user = (string)($this->smtp['username'] ?? '');
        $pass = (string)($this->smtp['password'] ?? '');
        $ehlo = $this->ehloName();

        // Auto-correct common port/encryption mismatches
        if ($port === 465 && $enc === 'tls') {
            $enc = 'ssl';
        }
        if ($port === 587 && $enc === 'ssl') {
            $enc = 'tls';
        }

        $remote = ($enc === 'ssl' ? 'ssl://' : '') . $host;
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true,
                'crypto_method' => STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT
                    | (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT') ? STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT : 0),
            ],
        ]);

        $errno = 0;
        $errstr = '';
        $fp = @stream_socket_client(
            "{$remote}:{$port}",
            $errno,
            $errstr,
            30,
            STREAM_CLIENT_CONNECT,
            $context
        );
        if (!$fp) {
            $this->lastError = "Connection failed to {$host}:{$port} — {$errstr} ({$errno})";
            return false;
        }
        stream_set_timeout($fp, 30);

        try {
            $this->expect($fp, [220]);
            $this->command($fp, 'EHLO ' . $ehlo, [250]);

            if ($enc === 'tls') {
                $this->command($fp, 'STARTTLS', [220]);
                $crypto = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT
                    | (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT') ? STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT : 0);
                if (!@stream_socket_enable_crypto($fp, true, $crypto)) {
                    // Fallback for older/odd OpenSSL builds
                    if (!@stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_ANY_CLIENT)) {
                        throw new RuntimeException('STARTTLS negotiation failed');
                    }
                }
                $this->command($fp, 'EHLO ' . $ehlo, [250]);
            }

            if ($user !== '') {
                $authed = false;
                $authError = '';
                try {
                    $this->command($fp, 'AUTH LOGIN', [334]);
                    $this->command($fp, base64_encode($user), [334]);
                    $this->command($fp, base64_encode($pass), [235]);
                    $authed = true;
                } catch (Throwable $e) {
                    $authError = $e->getMessage();
                }
                if (!$authed) {
                    // AUTH PLAIN fallback (many providers accept both)
                    try {
                        $this->command($fp, 'AUTH PLAIN ' . base64_encode("\0{$user}\0{$pass}"), [235]);
                        $authed = true;
                    } catch (Throwable $e) {
                        throw new RuntimeException(
                            'SMTP authentication failed. Check username/password (use an app password if required). '
                            . $authError . ' / ' . $e->getMessage()
                        );
                    }
                }
            }

            $this->command($fp, 'MAIL FROM:<' . $from . '>', [250]);
            $this->command($fp, 'RCPT TO:<' . $to . '>', [250, 251]);
            $this->command($fp, 'DATA', [354]);
            fwrite($fp, $data . "\r\n.\r\n");
            $this->expect($fp, [250]);
            try {
                $this->command($fp, 'QUIT', [221]);
            } catch (Throwable $e) {
                // Some servers drop the socket after a successful send
            }
            fclose($fp);
            return true;
        } catch (Throwable $e) {
            $this->lastError = $e->getMessage();
            if (is_resource($fp)) {
                @fclose($fp);
            }
            return false;
        }
    }

    private function command($fp, string $cmd, array $codes): void
    {
        fwrite($fp, $cmd . "\r\n");
        $this->expect($fp, $codes);
    }

    private function expect($fp, array $codes): string
    {
        $response = '';
        while (($line = fgets($fp, 515)) !== false) {
            $response .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
            // Timeout / empty read protection
            $meta = stream_get_meta_data($fp);
            if (!empty($meta['timed_out'])) {
                throw new RuntimeException('SMTP timeout while waiting for response');
            }
        }
        if ($response === '') {
            throw new RuntimeException('SMTP server closed the connection or returned an empty response');
        }
        $code = (int)substr($response, 0, 3);
        if (!in_array($code, $codes, true)) {
            throw new RuntimeException('SMTP error: ' . trim($response));
        }
        return $response;
    }

    private function encodeAddress(string $email, ?string $name = null): string
    {
        if ($name) {
            return $this->encodeHeader($name) . ' <' . $email . '>';
        }
        return $email;
    }

    private function encodeHeader(string $text): string
    {
        if (preg_match('/[^\x20-\x7E]/', $text)) {
            return '=?UTF-8?B?' . base64_encode($text) . '?=';
        }
        return $text;
    }

    private function logEmail(string $to, string $subject, string $body, string $status, ?string $error): void
    {
        try {
            Database::get()->prepare(
                'INSERT INTO email_logs (recipient, subject, body_html, status, error_message, sent_by_admin_id, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?)'
            )->execute([
                $to,
                $subject,
                $body,
                $status,
                $error,
                Auth::admin()['id'] ?? null,
                date('Y-m-d H:i:s'),
            ]);
        } catch (Throwable $e) {
            // ignore logging failures
        }
    }
}
