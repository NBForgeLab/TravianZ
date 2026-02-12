<?php

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit;
}

$raw = file_get_contents('php://input');
if (!is_string($raw)) {
    $raw = '';
}
if (strlen($raw) > 65536) {
    http_response_code(413);
    exit;
}

$contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
$payload = null;
if ($raw !== '') {
    $decoded = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $payload = $decoded;
    }
}
if (!is_array($payload) && $raw !== '' && str_contains($raw, '=')) {
    $form = [];
    parse_str($raw, $form);
    if (isset($form['csp-report']) && is_string($form['csp-report']) && $form['csp-report'] !== '') {
        $decoded = json_decode($form['csp-report'], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $payload = $decoded;
        }
    }
}

$entry = [
    'ts' => gmdate('c'),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
    'ua' => $_SERVER['HTTP_USER_AGENT'] ?? null,
    'content_type' => $contentType !== '' ? $contentType : null,
];

if (is_array($payload) && isset($payload['csp-report']) && is_array($payload['csp-report'])) {
    $entry['csp-report'] = $payload['csp-report'];
} elseif (is_array($payload)) {
    $entry['report'] = $payload;
} elseif ($raw !== '') {
    $entry['raw'] = substr($raw, 0, 2048);
}

$logDir = __DIR__ . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'log';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0775, true);
}
$logFile = $logDir . DIRECTORY_SEPARATOR . 'csp-violations.log';
$line = json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n";
@file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);

http_response_code(204);
