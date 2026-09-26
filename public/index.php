<?php

declare(strict_types=1);

// When PHP's built-in server uses this file as its router, let existing
// static files be served directly instead of sending them through ultralean.
if (PHP_SAPI === 'cli-server') {
    $requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

    if (basename($requestPath) === '.htaccess') {
        http_response_code(404);
        exit;
    }

    $staticPath = realpath(__DIR__ . '/' . ltrim($requestPath, '/'));
    $publicRoot = realpath(__DIR__);

    if ($requestPath !== '/'
        && $staticPath !== false
        && $publicRoot !== false
        && str_starts_with($staticPath, $publicRoot . DIRECTORY_SEPARATOR)
        && is_file($staticPath)
    ) {
        return false;
    }
}

require dirname(__DIR__) . '/system/bootstrap.php';

\System\Core\App::run();
