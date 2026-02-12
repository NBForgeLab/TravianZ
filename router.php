<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$full = __DIR__ . $path;

if (is_file($full)) {
    return false;
}

if (is_dir($full) && is_file($full . DIRECTORY_SEPARATOR . 'index.php')) {
    return false;
}

http_response_code(404);
header('Content-Type: text/plain; charset=utf-8');
echo '404 Not Found';
