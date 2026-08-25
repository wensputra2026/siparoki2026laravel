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
if (!file_exists($corePath . '/vendor/autoload.php')) {
    if (file_exists(__DIR__ . '/install.php')) {
        require_once __DIR__ . '/install.php';
        exit;
    }
    die('<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPAROKI 2026 — Langkah Awal Instalasi</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #0f172a; color: #f8fafc; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; margin: 0; }
        .card { background: #1e293b; border: 1px solid #334155; border-radius: 20px; max-width: 600px; width: 100%; padding: 36px; box-shadow: 0 20px 40px rgba(0,0,0,0.5); text-align: center; }
        .icon { width: 64px; height: 64px; background: rgba(245,158,11,0.15); border: 1px solid rgba(245,158,11,0.3); border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 20px; color: #fbbf24; }
        h1 { font-size: 22px; font-weight: 800; margin: 0 0 10px; color: #ffffff; }
        p { font-size: 14px; color: #94a3b8; line-height: 1.6; margin: 0 0 20px; }
        .code-box { background: #090e1a; border: 1px solid #1e293b; border-radius: 12px; padding: 14px 18px; text-align: left; font-family: "Courier New", monospace; font-size: 13px; color: #38bdf8; margin-bottom: 24px; overflow-x: auto; }
        .btn { background: #00897b; color: #ffffff; padding: 12px 28px; border-radius: 10px; text-decoration: none; font-weight: 700; font-size: 14px; display: inline-block; transition: 0.2s; }
        .btn:hover { background: #004d40; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">📦</div>
        <h1>Dependensi Composer Belum Terpasang</h1>
        <p>Folder <code>vendor/</code> belum terpasang di server Anda. Harap jalankan perintah berikut di terminal / command prompt pada folder proyek ini:</p>
        <div class="code-box">
            composer install --no-dev --optimize-autoloader
        </div>
        <p style="font-size: 12.5px; color: #64748b;">Setelah proses composer install selesai, muat ulang (refresh) halaman ini untuk langsung memulai Web Installer.</p>
        <a href="javascript:location.reload()" class="btn">Muat Ulang Halaman (Refresh)</a>
    </div>
</body>
</html>');
}

require $corePath . '/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $corePath . '/bootstrap/app.php';

$app->handleRequest(Request::capture());
