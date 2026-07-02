<?php
// Password-reset delivery.
//
// Two modes, chosen automatically:
//  * SMTP mode  — if SMTP_HOST/SMTP_USER/SMTP_PASS env vars are set, the reset
//                 link is emailed (SMTPS over port 465 by default).
//  * Display    — otherwise (or if APP_MAIL_MODE=display), the link is returned
//                 to be shown on screen. Convenient for setup/testing; turn it
//                 off in production by configuring SMTP.
//
// Returns ['mode' => 'smtp'|'display'|'error', 'link' => ?string, 'error' => ?string]

function deliver_reset_link(string $toEmail, string $link): array {
    $mode = getenv('APP_MAIL_MODE') ?: 'auto';
    $host = getenv('SMTP_HOST');
    $user = getenv('SMTP_USER');
    $pass = getenv('SMTP_PASS');

    if ($mode === 'display' || !$host || !$user || !$pass) {
        return ['mode' => 'display', 'link' => $link, 'error' => null];
    }

    $port = (int)(getenv('SMTP_PORT') ?: 465);
    $from = getenv('SMTP_FROM') ?: $user;
    $subject = 'Reset your StorefrontZA password';
    $body = "You (or someone) asked to reset your StorefrontZA password.\r\n\r\n"
          . "Open this link to set a new password (valid for 1 hour):\r\n$link\r\n\r\n"
          . "If you didn't request this, you can ignore this email.";

    try {
        $ok = smtp_send($host, $port, $user, $pass, $from, $toEmail, $subject, $body);
        return $ok ? ['mode' => 'smtp', 'link' => null, 'error' => null]
                   : ['mode' => 'error', 'link' => null, 'error' => 'Mail server did not accept the message.'];
    } catch (Throwable $e) {
        return ['mode' => 'error', 'link' => null, 'error' => $e->getMessage()];
    }
}

// Minimal SMTPS client (implicit TLS, port 465). Good enough for a reset email.
function smtp_send(string $host, int $port, string $user, string $pass, string $from, string $to, string $subject, string $body): bool {
    $transport = $port === 465 ? "ssl://$host:$port" : "tcp://$host:$port";
    $fp = @stream_socket_client($transport, $errno, $errstr, 15);
    if (!$fp) throw new RuntimeException("Connect failed: $errstr");
    stream_set_timeout($fp, 15);

    $read = function () use ($fp) {
        $data = '';
        while ($line = fgets($fp, 512)) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') break;
        }
        return $data;
    };
    $cmd = function ($c) use ($fp, $read) { fwrite($fp, $c . "\r\n"); return $read(); };

    $read(); // greeting
    $cmd("EHLO storefrontza");
    // STARTTLS path for port 587
    if ($port !== 465) {
        $cmd("STARTTLS");
        stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        $cmd("EHLO storefrontza");
    }
    $cmd("AUTH LOGIN");
    $cmd(base64_encode($user));
    $resp = $cmd(base64_encode($pass));
    if (strpos($resp, '235') === false) { fclose($fp); throw new RuntimeException('Auth failed.'); }

    $cmd("MAIL FROM:<$from>");
    $cmd("RCPT TO:<$to>");
    $cmd("DATA");
    $headers = "From: StorefrontZA <$from>\r\nTo: <$to>\r\nSubject: $subject\r\n"
             . "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n\r\n";
    $resp = $cmd($headers . $body . "\r\n.");
    $cmd("QUIT");
    fclose($fp);
    return strpos($resp, '250') !== false;
}
