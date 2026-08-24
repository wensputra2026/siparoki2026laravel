<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Auto-detect core Laravel root directory (Local Dev vs cPanel hosting structure)
$corePath = __DIR__ . '/..';
if (!file_exists($corePath . '/vendor/autoload.php')) {
    // If placed inside public_html while core is in sibling folder (e.g. /home/user/parokibenlutu)
    $possibleCorePaths = [
        __DIR__ . '/../parokibenlutu',
        __DIR__ . '/../parokibenlutularavel12',
        __DIR__ . '/../laravel_core',
        __DIR__ . '/../core',
        __DIR__ . '/../laravel',
    ];
    foreach ($possibleCorePaths as $path) {
        if (file_exists($path . '/vendor/autoload.php')) {
            $corePath = $path;
            break;
        }
    }
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $corePath . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $corePath . '/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $corePath . '/bootstrap/app.php';

$app->handleRequest(Request::capture());
