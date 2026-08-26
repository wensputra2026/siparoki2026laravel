<?php
/**
 * Root Installer Forwarder
 * Automatically routes install.php to modern Laravel /installer route with subfolder support
 */
$base_dir = __DIR__;
$isInstalled = file_exists($base_dir . '/storage/installed') 
    || file_exists($base_dir . '/storage/installed.lock')
    || file_exists($base_dir . '/storage/framework/installed')
    || file_exists($base_dir . '/public/installed.lock');

if ($isInstalled && !isset($_GET['force'])) {
    // If already installed, forward directly to Laravel front controller
    require_once __DIR__ . '/public/index.php';
    exit;
}

$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
    || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

$protocol = $is_https ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script_dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$target = rtrim($protocol . $host . $script_dir, '/') . '/installer';

header("Location: {$target}");
exit;
