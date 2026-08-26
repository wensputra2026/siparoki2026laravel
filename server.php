<?php

/**
 * SIPAROKI 2026 - Development Server Router (server.php)
 * Emulates Apache mod_rewrite for PHP Built-in Web Server (php -S 127.0.0.1:8000 or php artisan serve)
 */

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// If request is for an existing static file in /public, serve it directly
if ($uri !== '/' && file_exists(__DIR__ . '/public' . $uri)) {
    return false;
}

// Forward all other requests to Laravel front controller
require_once __DIR__ . '/public/index.php';
