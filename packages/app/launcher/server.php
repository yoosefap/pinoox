<?php

/**
 * Router script for PHP's built-in development server (php pinx dev / pincore serve).
 */

use Pinoox\Component\Server\FrontController;

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/');
$documentRoot = rtrim(str_replace('\\', '/', (string) getcwd()), '/');

require_once $documentRoot . '/vendor/autoload.php';

$target = $documentRoot . ($uri === '/' ? '' : $uri);

if (FrontController::shouldRoute($uri, $documentRoot)) {
    FrontController::applyServerGlobals($uri);
    require $documentRoot . '/index.php';

    return;
}

if ($uri !== '/' && $uri !== '' && is_file($target)) {
    return false;
}

require $documentRoot . '/index.php';
