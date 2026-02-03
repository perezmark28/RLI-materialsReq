<?php
/**
 * SMS Helper Functions
 * httpSMS integration
 */

if (!defined('HTTPSMS_API_KEY')) {
    define('HTTPSMS_API_KEY', 'uk_pH5RTTDf9a5k0ThGnqJvTTq1vOOzlCMAOuhJkAkhPUR_eHRKqMffvDvxiVIo8qOA');
}

if (!defined('HTTPSMS_FROM')) {
    define('HTTPSMS_FROM', '+639763717916');
}

function app_url(string $path): string {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ((int)($_SERVER['SERVER_PORT'] ?? 80) === 443);
    $scheme = $isHttps ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $base = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? '/'), '/\\');
    $path = ltrim($path, '/');
    return $scheme . '://' . $host . ($base ? $base . '/' : '/') . $path;
}

function try_shorten_url(string $url): string {
    $endpoint = 'https://tinyurl.com/api-create.php?url=' . urlencode($url);
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 5,
        ],
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ]
    ]);
    $short = @file_get_contents($endpoint, false, $ctx);
    if (is_string($short) && trim($short) !== '' && str_starts_with(trim($short), 'http')) {
        return trim($short);
    }
    return $url;
}

function httpsms_send(string $to, string $message): array {
    $key = trim((string)HTTPSMS_API_KEY);
    if ($key === '') {
        return ['success' => false, 'error' => 'httpSMS API key is not configured (HTTPSMS_API_KEY).', 'raw' => null];
    }

    $from = trim((string)HTTPSMS_FROM);
    if ($from === '') {
        return ['success' => false, 'error' => 'httpSMS "from" is required. Set HTTPSMS_FROM in E.164 format (example: +18005550100).', 'raw' => null];
    }

    $payload = json_encode([
        'from' => $from,
        'to' => $to,
        'content' => $message,
    ]);

    $ch = curl_init('https://api.httpsms.com/v1/messages/send');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-api-key: ' . $key,
        ],
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_TIMEOUT => 10,
    ]);

    $raw = curl_exec($ch);
    $err = curl_error($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($raw === false) {
        return ['success' => false, 'error' => 'cURL error: ' . $err, 'raw' => null];
    }
    if ($code < 200 || $code >= 300) {
        return ['success' => false, 'error' => 'httpSMS HTTP ' . $code . ': ' . $raw, 'raw' => $raw];
    }

    return ['success' => true, 'error' => null, 'raw' => $raw];
}
