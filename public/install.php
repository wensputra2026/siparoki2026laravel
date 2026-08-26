<?php
/**
 * SIPAROKI - Standalone Web Installation Wizard (Universal: Local, VPS, Hosting)
 * Full Modern Responsive Tailwind CSS Edition
 * Supports Laravel 12 on Localhost, Shared Hosting (cPanel), and Linux VPS (Nginx/Apache)
 */
session_start();

// Dynamic Base Paths
$base_dir = is_dir(__DIR__ . '/../storage') ? realpath(__DIR__ . '/..') : __DIR__;
$storage_dir = $base_dir . '/storage';
$bootstrap_cache_dir = $base_dir . '/bootstrap/cache';
$lock_file = $storage_dir . '/installed.lock';
$public_lock_file = __DIR__ . '/installed.lock';
$env_file = $base_dir . '/.env';

// Master data JSON file
$master_json_file = __DIR__ . '/installer/master_keuskupan_paroki.json';
if (!file_exists($master_json_file)) {
    $master_json_file = $base_dir . '/database/data/master_keuskupan_paroki.json';
}
$embedded_master_json = file_exists($master_json_file) ? file_get_contents($master_json_file) : '{"keuskupan":[],"dekenat":[],"paroki":[]}';

// Dynamic Base URL Detection
$is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    || (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] === 'on')
    || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);

$protocol = $is_https ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script_dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$root_app_url = rtrim($protocol . $host . preg_replace('#/public$#', '', $script_dir), '/');
if (empty($root_app_url)) $root_app_url = $protocol . $host;

function siparoki_table_exists(PDO $pdo, $table) {
    $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
    $stmt->execute([$table]);
    return (bool) $stmt->fetchColumn();
}

function siparoki_table_columns(PDO $pdo, $table) {
    if (!siparoki_table_exists($pdo, $table)) {
        return [];
    }

    $columns = [];
    $cols_stmt = $pdo->query("SHOW COLUMNS FROM `{$table}`");
    while ($col = $cols_stmt->fetch(PDO::FETCH_ASSOC)) {
        $columns[] = $col['Field'];
    }

    return $columns;
}

// 1. Check if already installed
if (file_exists($lock_file) || file_exists($public_lock_file) || file_exists($storage_dir . '/installed')) {
    if (!isset($_GET['force'])) {
        $vendor_installed = file_exists($base_dir . '/vendor/autoload.php') || file_exists(__DIR__ . '/../vendor/autoload.php');
        $login_url = htmlspecialchars($root_app_url . '/login');
        $home_url = htmlspecialchars($root_app_url . '/');
        $superadmin_url = htmlspecialchars($root_app_url . '/superadmin');
        $force_url = htmlspecialchars($_SERVER['PHP_SELF'] ?? 'install.php') . '?force=1';
        $folder_name = basename($base_dir);
        
        die('<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIPAROKI 2026 - Aplikasi Siap Digunakan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>body{font-family:\'Poppins\',sans-serif;}</style>
</head>
<body class="bg-slate-950 text-white min-h-screen flex items-center justify-center p-4">
    <div class="max-w-lg w-full bg-slate-900 border border-slate-800 rounded-3xl p-8 text-center shadow-2xl space-y-6">
        <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-amber-500 to-amber-400 text-slate-950 flex items-center justify-center text-3xl mx-auto shadow-lg shadow-amber-500/20 font-black">
            <i class="fa-solid fa-church"></i>
        </div>
        
        <div class="space-y-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-bold">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Instalasi Database Selesai & Aktif</span>
            </span>
            <h2 class="text-2xl font-black text-white">SIPAROKI Siap Digunakan</h2>
            <p class="text-slate-400 text-xs leading-relaxed max-w-sm mx-auto">
                Sistem Informasi Manajemen Paroki telah berhasil dipasang dan terhubung ke database.
            </p>
        </div>

        ' . (!$vendor_installed ? '
        <!-- NOTIFIKASI WAJIB COMPOSER INSTALL -->
        <div class="p-4 rounded-2xl bg-amber-500/15 border-2 border-amber-500/50 text-left space-y-2.5">
            <div class="flex items-center gap-2 text-amber-300 font-bold text-xs">
                <i class="fa-solid fa-triangle-exclamation text-amber-400 text-base"></i>
                <span>1 Langkah Terakhir di Terminal:</span>
            </div>
            <p class="text-[11.5px] text-slate-200 leading-relaxed">
                Pustaka Laravel (<code class="text-amber-300">vendor/</code>) belum di-install di folder ini. Buka <strong>PowerShell / Terminal</strong> di folder proyek Anda dan ketik:
            </p>
            <div class="bg-slate-950 p-2.5 rounded-xl border border-slate-800 font-mono text-xs text-amber-400 flex items-center justify-between">
                <span>composer install</span>
                <span class="text-[10px] text-slate-400 font-sans">Ketik di PowerShell</span>
            </div>
            <p class="text-[11px] text-slate-400">
                Setelah proses composer selesai, klik tombol di bawah untuk langsung membuka login.
            </p>
        </div>' : '') . '

        <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800/80 text-xs text-left space-y-2 text-slate-300">
            <div class="flex items-center justify-between text-slate-400">
                <span>Email Login:</span>
                <span class="font-mono text-amber-400 font-bold">superadmin@paroki.org</span>
            </div>
            <div class="flex items-center justify-between text-slate-400">
                <span>Password Login:</span>
                <span class="font-mono text-amber-400 font-bold">Admin@Paroki2026!</span>
            </div>
        </div>

        <div class="space-y-3">
            <a href="' . $login_url . '" id="btn-login" class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white font-bold py-3.5 px-6 rounded-xl transition shadow-lg shadow-amber-500/25 text-sm cursor-pointer">
                <i class="fa-solid fa-right-to-bracket"></i>
                <span>' . ($vendor_installed ? 'Masuk ke Halaman Login' : 'Buka Halaman Login (Setelah Composer Selesai)') . '</span>
            </a>
            
            <div class="grid grid-cols-2 gap-2">
                <a href="' . $superadmin_url . '" class="flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold py-2.5 px-4 rounded-xl transition text-xs">
                    <i class="fa-solid fa-gauge text-amber-400"></i>
                    <span>Dashboard</span>
                </a>
                <a href="' . $home_url . '" class="flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold py-2.5 px-4 rounded-xl transition text-xs">
                    <i class="fa-solid fa-globe text-sky-400"></i>
                    <span>Situs Paroki</span>
                </a>
            </div>
        </div>

        <div class="pt-2 border-t border-slate-800/60 text-[11px] text-slate-500 flex items-center justify-between">
            <span>SIPAROKI &copy; 2026</span>
            <a href="' . $force_url . '" class="text-amber-400/80 hover:text-amber-300 hover:underline">Pasang Ulang / Reset &rarr;</a>
        </div>
    </div>
</body>
</html>');
    }
}

// 2. AJAX: Test DB Connection
if (isset($_GET['action']) && $_GET['action'] === 'test_db') {
    header('Content-Type: application/json');
    $db_host = trim($_POST['db_host'] ?? '127.0.0.1');
    $db_port = (int) ($_POST['db_port'] ?? 3306);
    $db_user = trim($_POST['db_user'] ?? 'root');
    $db_pass = $_POST['db_pass'] ?? '';
    $db_name = trim($_POST['db_name'] ?? 'siparoki_db');

    if (!preg_match('/^[A-Za-z0-9_]+$/', $db_name)) {
        echo json_encode(['status' => 'error', 'message' => 'Nama database hanya boleh memakai huruf, angka, dan underscore.']);
        exit;
    }

    try {
        $dsn = "mysql:host={$db_host};port={$db_port};charset=utf8mb4";
        $pdo = new PDO($dsn, $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5
        ]);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        echo json_encode(['status' => 'success', 'message' => "Koneksi database ke server MySQL ({$db_host}:{$db_port}) berhasil terhubung!"]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => "Gagal terhubung ke MySQL: " . $e->getMessage()]);
    }
    exit;
}

// 3. AJAX: Execute Full Installation
if (isset($_GET['action']) && $_GET['action'] === 'process_install') {
    header('Content-Type: application/json');
    @ini_set('memory_limit', '1024M');
    @ini_set('max_execution_time', 600);
    if (function_exists('set_time_limit')) {
        @set_time_limit(600);
    }

    $db_host       = trim($_POST['db_host'] ?? '127.0.0.1');
    $db_port       = (int) ($_POST['db_port'] ?? 3306);
    $db_user       = trim($_POST['db_user'] ?? 'root');
    $db_pass       = $_POST['db_pass'] ?? '';
    $db_name       = trim($_POST['db_name'] ?? 'siparoki_db');

    $app_name      = trim($_POST['app_name'] ?? 'SIPAROKI');
    $paroki_name   = trim($_POST['paroki_name'] ?? 'Paroki Baru');
    $keuskupan     = trim($_POST['keuskupan_name'] ?? 'Keuskupan Agung Kupang');
    $alamat        = trim($_POST['alamat_paroki'] ?? '');
    $paroki_id     = !empty($_POST['selected_paroki_id']) ? (int)$_POST['selected_paroki_id'] : null;
    $keuskupan_id  = !empty($_POST['selected_keuskupan_id']) ? (int)$_POST['selected_keuskupan_id'] : null;
    $dekenat_id    = !empty($_POST['selected_dekenat_id']) ? (int)$_POST['selected_dekenat_id'] : null;

    $admin_name    = trim($_POST['admin_name'] ?? 'Administrator Paroki');
    $admin_email   = trim($_POST['admin_email'] ?? 'admin@paroki.org');
    $admin_user    = trim($_POST['admin_user'] ?? 'admin');
    $admin_pass    = $_POST['admin_pass'] ?? 'password';

    if (empty($admin_pass)) {
        $admin_pass = 'password';
    }

    try {
        // Step A: Connect & Create Database
        $dsn = "mysql:host={$db_host};port={$db_port};charset=utf8mb4";
        $pdo = new PDO($dsn, $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 30
        ]);
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$db_name}`");

        // Step B: Import SQL Baseline if available
        $sql_file = __DIR__ . '/installer/database/siparoki.sql';
        if (!file_exists($sql_file)) {
            $sql_file = $base_dir . '/database/data/siparoki.sql';
        }

        if (file_exists($sql_file) && filesize($sql_file) > 0) {
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
            $pdo->exec("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';");
            
            $fp = fopen($sql_file, 'r');
            if ($fp) {
                $query = '';
                while (!feof($fp)) {
                    $line = fgets($fp);
                    $trimmed = trim($line);
                    if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*') || str_starts_with($trimmed, '#')) {
                        continue;
                    }
                    $query .= $line;
                    if (str_ends_with($trimmed, ';')) {
                        try {
                            $pdo->exec($query);
                        } catch (Exception $e) {
                            // Silently continue for minor SQL notices
                        }
                        $query = '';
                    }
                }
                fclose($fp);
            }
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
        }

        // Step B2: Ensure Laravel System Tables (sessions, cache, cache_locks)
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS `sessions` (
              `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `user_id` bigint unsigned DEFAULT NULL,
              `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `user_agent` text COLLATE utf8mb4_unicode_ci,
              `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
              `last_activity` int NOT NULL,
              PRIMARY KEY (`id`),
              KEY `sessions_user_id_index` (`user_id`),
              KEY `sessions_last_activity_index` (`last_activity`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `cache` (
              `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
              `expiration` int NOT NULL,
              PRIMARY KEY (`key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

            $pdo->exec("CREATE TABLE IF NOT EXISTS `cache_locks` (
              `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `expiration` int NOT NULL,
              PRIMARY KEY (`key`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        } catch (Exception $e) {}

        // Step C: Ensure Paroki Record & Defaults
        $paroki_cols = siparoki_table_columns($pdo, 'paroki');
        if (!empty($paroki_cols)) {
            if ($paroki_id) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM `paroki` WHERE `id_paroki` = ?");
                $stmt->execute([$paroki_id]);
                if ((int)$stmt->fetchColumn() > 0) {
                    $pdo->prepare("UPDATE `paroki` SET `nama_paroki` = ?, `alamat` = ?, `status` = 'Aktif' WHERE `id_paroki` = ?")
                        ->execute([$paroki_name, $alamat, $paroki_id]);
                } else {
                    $pdo->prepare("INSERT INTO `paroki` (`id_paroki`, `keuskupan_id`, `nama_paroki`, `alamat`, `status`) VALUES (?, ?, ?, ?, 'Aktif')")
                        ->execute([$paroki_id, $keuskupan_id, $paroki_name, $alamat]);
                }
            } else {
                $stmt = $pdo->query("SELECT MAX(`id_paroki`) FROM `paroki`");
                $new_paroki_id = ((int) $stmt->fetchColumn()) + 1;
                $kode_paroki = 'PAR-' . str_pad((string)$new_paroki_id, 3, '0', STR_PAD_LEFT);
                $pdo->prepare("INSERT INTO `paroki` (`id_paroki`, `keuskupan_id`, `kode_paroki`, `nama_paroki`, `alamat`, `status`) VALUES (?, ?, ?, ?, ?, 'Aktif')")
                    ->execute([$new_paroki_id, $keuskupan_id, $kode_paroki, $paroki_name, $alamat]);
                $paroki_id = $new_paroki_id;
            }
        }

        // Step D: Update profil_paroki
        $profil_cols = siparoki_table_columns($pdo, 'profil_paroki');
        if (!empty($profil_cols)) {
            $pk_col = in_array('id', $profil_cols, true) ? 'id' : (in_array('id_profil', $profil_cols, true) ? 'id_profil' : $profil_cols[0]);
            $first_profil = $pdo->query("SELECT * FROM `profil_paroki` LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            $profil_data = [
                'nama_paroki' => $paroki_name,
                'keuskupan' => $keuskupan,
                'alamat' => $alamat,
            ];
            if (in_array('paroki_id', $profil_cols, true) && $paroki_id) $profil_data['paroki_id'] = $paroki_id;
            if (in_array('keuskupan_id', $profil_cols, true) && $keuskupan_id) $profil_data['keuskupan_id'] = $keuskupan_id;
            if (in_array('dekenat_id', $profil_cols, true) && $dekenat_id) $profil_data['dekenat_id'] = $dekenat_id;

            if ($first_profil) {
                $sets = [];
                $params = [];
                foreach ($profil_data as $col => $val) {
                    if (in_array($col, $profil_cols, true)) {
                        $sets[] = "`{$col}` = ?";
                        $params[] = $val;
                    }
                }
                $params[] = $first_profil[$pk_col];
                $pdo->prepare("UPDATE `profil_paroki` SET " . implode(', ', $sets) . " WHERE `{$pk_col}` = ?")->execute($params);
            } else {
                $cols_sql = '`' . implode('`, `', array_keys($profil_data)) . '`';
                $placeholders = implode(', ', array_fill(0, count($profil_data), '?'));
                $pdo->prepare("INSERT INTO `profil_paroki` ($cols_sql) VALUES ($placeholders)")->execute(array_values($profil_data));
            }
        }

        // Step E: Update pengaturan_aplikasi
        $pengaturan_cols = siparoki_table_columns($pdo, 'pengaturan_aplikasi');
        if (!empty($pengaturan_cols)) {
            $pk_col = in_array('id', $pengaturan_cols, true) ? 'id' : (in_array('id_pengaturan', $pengaturan_cols, true) ? 'id_pengaturan' : $pengaturan_cols[0]);
            $first_pengaturan = $pdo->query("SELECT * FROM `pengaturan_aplikasi` LIMIT 1")->fetch(PDO::FETCH_ASSOC);
            $pengaturan_data = [
                'nama_aplikasi' => $app_name,
                'nama_paroki' => $paroki_name,
                'alamat_paroki' => $alamat,
                'is_setup_completed' => 1,
            ];
            if (in_array('paroki_id', $pengaturan_cols, true) && $paroki_id) $pengaturan_data['paroki_id'] = $paroki_id;
            if (in_array('keuskupan_id', $pengaturan_cols, true) && $keuskupan_id) $pengaturan_data['keuskupan_id'] = $keuskupan_id;
            if (in_array('dekenat_id', $pengaturan_cols, true) && $dekenat_id) $pengaturan_data['dekenat_id'] = $dekenat_id;

            if ($first_pengaturan) {
                $sets = [];
                $params = [];
                foreach ($pengaturan_data as $col => $val) {
                    if (in_array($col, $pengaturan_cols, true)) {
                        $sets[] = "`{$col}` = ?";
                        $params[] = $val;
                    }
                }
                $params[] = $first_pengaturan[$pk_col];
                $pdo->prepare("UPDATE `pengaturan_aplikasi` SET " . implode(', ', $sets) . " WHERE `{$pk_col}` = ?")->execute($params);
            } else {
                $cols_sql = '`' . implode('`, `', array_keys($pengaturan_data)) . '`';
                $placeholders = implode(', ', array_fill(0, count($pengaturan_data), '?'));
                $pdo->prepare("INSERT INTO `pengaturan_aplikasi` ($cols_sql) VALUES ($placeholders)")->execute(array_values($pengaturan_data));
            }
        }

        // Step F: Create / Update Super Admin Account
        $user_cols = siparoki_table_columns($pdo, 'users');
        if (!empty($user_cols)) {
            $hashed_password = password_hash($admin_pass, PASSWORD_BCRYPT);
            $role_id = 1;

            $stmt = $pdo->prepare("SELECT `id` FROM `users` WHERE `email` = ? OR `username` = ? OR `id` = 1 LIMIT 1");
            $stmt->execute([$admin_email, $admin_user]);
            $existing_user_id = $stmt->fetchColumn();

            $user_payload = [
                'nama_lengkap' => $admin_name,
                'name' => $admin_name,
                'username' => $admin_user,
                'email' => $admin_email,
                'password' => $hashed_password,
                'role_id' => $role_id,
                'status_aktif' => 1,
                'status_user' => 'Aktif',
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            // Clean payload based on available columns
            $clean_user = [];
            foreach ($user_payload as $k => $v) {
                if (in_array($k, $user_cols, true)) {
                    $clean_user[$k] = $v;
                }
            }

            if ($existing_user_id) {
                $sets = [];
                $params = [];
                foreach ($clean_user as $col => $val) {
                    $sets[] = "`{$col}` = ?";
                    $params[] = $val;
                }
                $params[] = $existing_user_id;
                $pdo->prepare("UPDATE `users` SET " . implode(', ', $sets) . " WHERE `id` = ?")->execute($params);
            } else {
                if (in_array('created_at', $user_cols, true)) {
                    $clean_user['created_at'] = date('Y-m-d H:i:s');
                }
                $cols_sql = '`' . implode('`, `', array_keys($clean_user)) . '`';
                $placeholders = implode(', ', array_fill(0, count($clean_user), '?'));
                $pdo->prepare("INSERT INTO `users` ($cols_sql) VALUES ($placeholders)")->execute(array_values($clean_user));
            }
        }

        // Step G: Ensure Storage Directory Structure (Local / VPS / Hosting)
        $required_dirs = [
            $storage_dir,
            $storage_dir . '/app',
            $storage_dir . '/app/public',
            $storage_dir . '/framework',
            $storage_dir . '/framework/cache',
            $storage_dir . '/framework/cache/data',
            $storage_dir . '/framework/sessions',
            $storage_dir . '/framework/views',
            $storage_dir . '/logs',
            $bootstrap_cache_dir,
            __DIR__ . '/uploads',
            __DIR__ . '/uploads/paroki',
        ];
        foreach ($required_dirs as $dir) {
            if (!is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }
        }

        // Step H: Generate & Write .env File
        $app_key = 'base64:' . base64_encode(random_bytes(32));
        $env_content = "APP_NAME=\"" . addslashes($app_name) . "\"\n"
            . "APP_ENV=local\n"
            . "APP_KEY={$app_key}\n"
            . "APP_DEBUG=true\n"
            . "APP_URL=" . addslashes($root_app_url) . "\n\n"
            . "LOG_CHANNEL=stack\n"
            . "LOG_DEPRECATIONS_CHANNEL=null\n"
            . "LOG_LEVEL=debug\n\n"
            . "DB_CONNECTION=mysql\n"
            . "DB_HOST={$db_host}\n"
            . "DB_PORT={$db_port}\n"
            . "DB_DATABASE={$db_name}\n"
            . "DB_USERNAME={$db_user}\n"
            . "DB_PASSWORD=\"{$db_pass}\"\n\n"
            . "SESSION_DRIVER=file\n"
            . "SESSION_LIFETIME=120\n"
            . "SESSION_ENCRYPT=false\n"
            . "SESSION_PATH=/\n"
            . "SESSION_DOMAIN=null\n\n"
            . "CACHE_STORE=file\n"
            . "QUEUE_CONNECTION=sync\n";

        @file_put_contents($env_file, $env_content);

        // Step I: Write Lock Files
        @file_put_contents($lock_file, date('Y-m-d H:i:s') . " - Paroki: {$paroki_name} ({$keuskupan})\n");
        @file_put_contents($public_lock_file, date('Y-m-d H:i:s') . " - Paroki: {$paroki_name} ({$keuskupan})\n");

        echo json_encode([
            'status' => 'success',
            'message' => 'Instalasi SIPAROKI berhasil diselesaikan!',
            'paroki' => $paroki_name,
            'redirect' => $root_app_url . '/login'
        ]);

    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => "Proses instalasi gagal: " . $e->getMessage()]);
    }
    exit;
}

// 4. System Requirements & Permissions Check
$php_version = phpversion();
$php_ok = version_compare($php_version, '8.2.0', '>=');

$reqs = [
    'PHP >= 8.2.0' => $php_ok,
    'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
    'OpenSSL Extension' => extension_loaded('openssl'),
    'Mbstring Extension' => extension_loaded('mbstring'),
    'Tokenizer Extension' => extension_loaded('tokenizer'),
    'XML Extension' => extension_loaded('xml'),
    'Ctype Extension' => extension_loaded('ctype'),
    'JSON Extension' => extension_loaded('json'),
    'BCMath Extension' => extension_loaded('bcmath'),
    'Fileinfo Extension' => extension_loaded('fileinfo'),
    'GD / Image Extension' => extension_loaded('gd'),
];

$all_reqs_passed = !in_array(false, $reqs, true);

$perms = [
    'storage/' => is_writable($storage_dir) || @mkdir($storage_dir, 0775, true),
    'bootstrap/cache/' => is_writable($bootstrap_cache_dir) || @mkdir($bootstrap_cache_dir, 0775, true),
    'public/uploads/' => is_writable(__DIR__ . '/uploads') || @mkdir(__DIR__ . '/uploads', 0775, true),
];

$all_perms_passed = !in_array(false, $perms, true);
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SIPAROKI - Web Setup &amp; Installation Wizard</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        amber: {
                            50: '#fffbeb',
                            100: '#fef3c7',
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                        }
                    },
                    fontFamily: {
                        sans: ['Poppins', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Google Fonts Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        .step-content { display: none; }
        .step-content.active { display: block; animation: fadeIn 0.3s ease-out; }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: rgba(15, 23, 42, 0.4); border-radius: 9999px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(245, 158, 11, 0.3); border-radius: 9999px; }

        /* Select2 Dark Theme Customization for SIPAROKI Installer */
        .select2-container { width: 100% !important; }
        .select2-container--default .select2-selection--single {
            height: 42px !important;
            padding: 6px 14px !important;
            border-radius: 0.75rem !important;
            border: 1px solid #334155 !important;
            background-color: #020617 !important;
            font-size: 0.75rem !important;
            display: flex !important;
            align-items: center !important;
            transition: all 0.2s ease !important;
        }
        .select2-container--default.select2-container--open .select2-selection--single,
        .select2-container--default .select2-selection--single:focus {
            border-color: #f59e0b !important;
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.2) !important;
            background-color: #020617 !important;
            outline: none !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #ffffff !important;
            font-weight: 600 !important;
            line-height: normal !important;
            padding-left: 0 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            right: 12px !important;
        }
        .select2-dropdown {
            border: 1px solid #334155 !important;
            border-radius: 0.75rem !important;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6) !important;
            overflow: hidden !important;
            background: #0f172a !important;
            z-index: 9999 !important;
        }
        .select2-search--dropdown {
            padding: 8px 10px !important;
            background: #020617 !important;
            border-bottom: 1px solid #1e293b !important;
        }
        .select2-search--dropdown .select2-search__field {
            border: 1px solid #334155 !important;
            border-radius: 0.5rem !important;
            padding: 7px 10px !important;
            font-size: 0.75rem !important;
            outline: none !important;
            background: #0f172a !important;
            color: #ffffff !important;
        }
        .select2-search--dropdown .select2-search__field:focus {
            border-color: #f59e0b !important;
        }
        .select2-results__option {
            padding: 8px 12px !important;
            font-size: 0.75rem !important;
            color: #cbd5e1 !important;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #f59e0b !important;
            color: #020617 !important;
            font-weight: 700 !important;
        }
        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: rgba(245, 158, 11, 0.15) !important;
            color: #fbbf24 !important;
            font-weight: 700 !important;
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-between selection:bg-amber-500 selection:text-white relative font-sans">
    
    <!-- Ambient Glow -->
    <div class="absolute -top-32 -left-32 w-80 h-80 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -right-32 w-80 h-80 bg-amber-600/10 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Header Banner -->
    <header class="w-full border-b border-slate-800/80 bg-slate-900/90 backdrop-blur-md sticky top-0 z-30 px-4 sm:px-8 py-3.5 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-amber-400 flex items-center justify-center text-slate-950 shadow-lg shadow-amber-500/20 font-black text-xl border border-amber-300/40">
                <i class="fa-solid fa-church"></i>
            </div>
            <div>
                <h1 class="text-base sm:text-lg font-black tracking-tight text-white flex items-center gap-2">
                    SIPAROKI <span class="text-xs px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 font-semibold border border-amber-500/30">Universal Installer</span>
                </h1>
                <p class="text-[11px] text-slate-400">Siap untuk Localhost, Shared Hosting (cPanel), dan Linux VPS</p>
            </div>
        </div>
        <span class="text-xs font-mono text-slate-500">v2.0 &bull; Laravel 12</span>
    </header>

    <!-- Main Container -->
    <main class="flex-1 max-w-4xl w-full mx-auto p-4 sm:p-6 lg:p-8 flex flex-col justify-center">
        
        <!-- Stepper Pills -->
        <div class="mb-6">
            <div class="grid grid-cols-4 gap-2 max-w-xl mx-auto text-center">
                <div id="pill-1" class="step-pill py-2 px-3 rounded-xl text-xs font-bold transition-all bg-amber-500 text-slate-950 shadow-md">
                    1. Syarat
                </div>
                <div id="pill-2" class="step-pill py-2 px-3 rounded-xl text-xs font-semibold transition-all bg-slate-800 text-slate-400">
                    2. Database
                </div>
                <div id="pill-3" class="step-pill py-2 px-3 rounded-xl text-xs font-semibold transition-all bg-slate-800 text-slate-400">
                    3. Paroki
                </div>
                <div id="pill-4" class="step-pill py-2 px-3 rounded-xl text-xs font-semibold transition-all bg-slate-800 text-slate-400">
                    4. Selesai
                </div>
            </div>
        </div>

        <!-- Wizard Card Box -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-2xl backdrop-blur-md relative">

            <!-- STEP 1: PERSYARATAN SERVER -->
            <div class="step-content active" id="step-1">
                <div class="space-y-5">
                    <div>
                        <h2 class="text-xl font-black text-white flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-amber-500/20 text-amber-400 text-sm flex items-center justify-center font-bold">1</span>
                            <span>Pemeriksaan Lingkungan &amp; Server</span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-1">Memastikan versi PHP dan izin folder memenuhi kriteria operasional SIPAROKI.</p>
                    </div>

                    <!-- Petunjuk Ringkas Langkah 1 -->
                    <div class="p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-slate-300 text-xs flex items-start gap-3">
                        <i class="fa-solid fa-circle-info text-amber-400 text-base mt-0.5 shrink-0"></i>
                        <div class="space-y-1">
                            <p class="font-bold text-amber-300">Petunjuk Pra-Instalasi:</p>
                            <p class="text-[11px] text-slate-300 leading-relaxed">
                                Pastikan server lokal (Laragon/XAMPP) atau hosting Anda menggunakan <strong>PHP 8.2</strong> atau lebih baru. Seluruh ekstensi penting (seperti PDO MySQL, OpenSSL, Mbstring) harus dalam status <span class="text-emerald-400 font-semibold">Tersedia</span>.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-h-[260px] overflow-y-auto pr-1 custom-scrollbar">
                        <?php foreach ($reqs as $label => $pass): ?>
                        <div class="p-3 rounded-xl border <?= $pass ? 'bg-emerald-500/10 border-emerald-500/30' : 'bg-rose-500/10 border-rose-500/30' ?> flex items-center justify-between">
                            <span class="text-xs font-semibold text-slate-200"><?= htmlspecialchars($label) ?></span>
                            <span class="text-xs font-bold <?= $pass ? 'text-emerald-400' : 'text-rose-400' ?>">
                                <i class="fa-solid <?= $pass ? 'fa-check-circle' : 'fa-times-circle' ?>"></i> <?= $pass ? 'Tersedia' : 'Kurang' ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="pt-4 border-t border-slate-800 flex items-center justify-end">
                        <button type="button" onclick="goToStep(2)" class="px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-lg shadow-amber-500/20 transition flex items-center gap-2 <?= (!$all_reqs_passed) ? 'opacity-50 cursor-not-allowed' : '' ?>" <?= (!$all_reqs_passed) ? 'disabled' : '' ?>>
                            <span>Lanjut ke Database</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- STEP 2: KONEKSI DATABASE -->
            <div class="step-content" id="step-2">
                <div class="space-y-5">
                    <div>
                        <h2 class="text-xl font-black text-white flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-amber-500/20 text-amber-400 text-sm flex items-center justify-center font-bold">2</span>
                            <span>Konfigurasi Database MySQL</span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-1">Masukkan kredensial koneksi database MySQL / MariaDB (Localhost, cPanel, atau VPS).</p>
                    </div>

                    <!-- Petunjuk Pengisian Database -->
                    <div class="p-3.5 rounded-2xl bg-sky-500/10 border border-sky-500/30 text-slate-300 text-xs flex items-start gap-3">
                        <i class="fa-solid fa-lightbulb text-sky-400 text-base mt-0.5 shrink-0"></i>
                        <div class="space-y-1">
                            <p class="font-bold text-sky-300">Panduan Pengisian Database:</p>
                            <ul class="text-[11px] text-slate-300 list-disc list-inside space-y-0.5 leading-relaxed">
                                <li><strong>Laragon / XAMPP Lokal:</strong> Gunakan User <code class="bg-slate-950 px-1 py-0.5 rounded text-amber-300">root</code> dan <strong>kosongkan kolom password</strong>.</li>
                                <li><strong>Pembuatan Otomatis:</strong> Database <code class="bg-slate-950 px-1 py-0.5 rounded text-amber-300">siparoki_db</code> akan dibuatkan otomatis jika belum ada di MySQL Anda.</li>
                                <li><strong>cPanel Hosting:</strong> Buat database dan user di cPanel MySQL Wizard terlebih dahulu, lalu masukkan nama database dan password-nya di sini.</li>
                            </ul>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-300">Host Database</label>
                            <input type="text" id="db_host" value="127.0.0.1" placeholder="127.0.0.1 atau localhost" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:border-amber-500 outline-none" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-300">Port Database</label>
                            <input type="number" id="db_port" value="3306" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:border-amber-500 outline-none" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-300">Nama Database</label>
                            <input type="text" id="db_name" value="siparoki_db" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:border-amber-500 outline-none font-semibold text-amber-400" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-300">Username Database</label>
                            <input type="text" id="db_user" value="root" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:border-amber-500 outline-none" />
                        </div>
                        <div class="space-y-1 sm:col-span-2">
                            <label class="text-xs font-bold text-slate-300">Password Database</label>
                            <input type="password" id="db_pass" placeholder="Kosongkan jika tanpa password (default Laragon/XAMPP)" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:border-amber-500 outline-none" />
                        </div>
                    </div>

                    <div id="db_alert" class="hidden p-3 rounded-xl text-xs"></div>

                    <div class="pt-4 border-t border-slate-800 flex items-center justify-between">
                        <button type="button" onclick="goToStep(1)" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition">
                            <i class="fa-solid fa-arrow-left"></i> Kembali
                        </button>
                        <button type="button" id="btn_test_db" onclick="testDatabase()" class="px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-lg shadow-amber-500/20 transition flex items-center gap-2">
                            <i class="fa-solid fa-plug"></i>
                            <span>Uji Koneksi &amp; Lanjut</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- STEP 3: PILIH KEUSKUPAN & PAROKI DEFAULT -->
            <div class="step-content" id="step-3">
                <div class="space-y-5">
                    <div>
                        <h2 class="text-xl font-black text-white flex items-center gap-2">
                            <span class="w-7 h-7 rounded-lg bg-amber-500/20 text-amber-400 text-sm flex items-center justify-center font-bold">3</span>
                            <span>Pilih Keuskupan, Paroki &amp; Administrator</span>
                        </h2>
                        <p class="text-xs text-slate-400 mt-1">Pilih Keuskupan &amp; Paroki Anda dari daftar master nasional atau daftarkan nama baru.</p>
                    </div>

                    <!-- Petunjuk Identitas Paroki -->
                    <div class="p-3.5 rounded-2xl bg-indigo-500/10 border border-indigo-500/30 text-slate-300 text-xs flex items-start gap-3">
                        <i class="fa-solid fa-church text-indigo-400 text-base mt-0.5 shrink-0"></i>
                        <div class="space-y-1">
                            <p class="font-bold text-indigo-300">Petunjuk Identitas Paroki:</p>
                            <p class="text-[11px] text-slate-300 leading-relaxed">
                                Pilih <strong>Keuskupan</strong> dan <strong>Paroki</strong> Anda dari dropdown master KWI se-Indonesia. Nama paroki dan alamat akan terisi otomatis. Anda juga dapat mengubah nama paroki secara bebas sesuai dokumen resmi.
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Pilih Keuskupan -->
                        <div class="space-y-1 sm:col-span-2">
                            <label class="text-xs font-bold text-slate-300 flex items-center justify-between">
                                <span>Pilih Keuskupan <span class="text-rose-400">*</span></span>
                                <span class="text-[10px] text-slate-500">Master Data KWI Indonesia</span>
                            </label>
                            <select id="sel_keuskupan" onchange="onKeuskupanChange()" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:border-amber-500 outline-none">
                                <option value="">-- Memuat Daftar Keuskupan... --</option>
                            </select>
                        </div>

                        <!-- Pilih Dekenat -->
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-300">Dekenat / Kevikepan</label>
                            <select id="sel_dekenat" onchange="onDekenatChange()" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:border-amber-500 outline-none">
                                <option value="">-- Semua Dekenat --</option>
                            </select>
                        </div>

                        <!-- Pilih Paroki Terdaftar -->
                        <div class="space-y-1">
                            <label class="text-xs font-bold text-slate-300">Pilih Paroki Terdaftar</label>
                            <select id="sel_paroki" onchange="onParokiSelect()" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:border-amber-500 outline-none">
                                <option value="">-- Pilih dari Paroki Terdaftar --</option>
                            </select>
                        </div>

                        <!-- Nama Paroki -->
                        <div class="space-y-1 sm:col-span-2">
                            <label class="text-xs font-bold text-slate-300">Nama Paroki (Resmi)</label>
                            <input type="text" id="paroki_name" placeholder="Contoh: Paroki St. Vinsensius a Paulo - Benlutu" class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:border-amber-500 outline-none font-bold" />
                        </div>

                        <!-- Alamat Paroki -->
                        <div class="space-y-1 sm:col-span-2">
                            <label class="text-xs font-bold text-slate-300">Alamat Paroki</label>
                            <input type="text" id="alamat_paroki" placeholder="Jl. Utama Paroki, Desa/Kelurahan, Kecamatan, Kabupaten..." class="w-full px-3.5 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-xs text-white focus:border-amber-500 outline-none" />
                        </div>

                        <!-- Akun Administrator -->
                        <div class="sm:col-span-2 pt-3 border-t border-slate-800">
                            <div class="flex items-center justify-between mb-2">
                                <h3 class="text-xs font-bold text-amber-400 uppercase tracking-wider flex items-center gap-1.5">
                                    <i class="fa-solid fa-shield-halved"></i>
                                    <span>Akun Super Administrator</span>
                                </h3>
                                <span class="text-[10px] text-slate-400">Hak Akses Penuh Sistem</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="space-y-1">
                                    <label class="text-[11px] font-semibold text-slate-400">Nama Lengkap</label>
                                    <input type="text" id="admin_name" value="Administrator Paroki" class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white outline-none" />
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[11px] font-semibold text-slate-400">Email Login</label>
                                    <input type="email" id="admin_email" value="superadmin@paroki.org" class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white outline-none font-semibold text-amber-400" />
                                </div>
                                <div class="space-y-1">
                                    <label class="text-[11px] font-semibold text-slate-400">Password Login</label>
                                    <input type="text" id="admin_pass" value="Admin@Paroki2026!" class="w-full px-3 py-2 bg-slate-950 border border-slate-700 rounded-lg text-xs text-white outline-none font-mono font-bold text-amber-400" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-800 flex items-center justify-between">
                        <button type="button" onclick="goToStep(2)" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 font-bold text-xs transition">
                            <i class="fa-solid fa-arrow-left"></i> Kembali
                        </button>
                        <button type="button" onclick="executeInstallation()" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 text-slate-950 font-black text-xs shadow-lg shadow-amber-500/25 transition flex items-center gap-2">
                            <i class="fa-solid fa-bolt"></i>
                            <span>Mulai Proses Instalasi</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- STEP 4: PROSES EKSEKUSI & SELESAI -->
            <div class="step-content" id="step-4">
                <div class="text-center py-8 space-y-6">
                    <div id="install_loading">
                        <div class="w-16 h-16 rounded-2xl bg-amber-500/20 border border-amber-500/40 text-amber-400 flex items-center justify-center text-3xl mx-auto shadow-xl animate-pulse">
                            <i class="fa-solid fa-spinner fa-spin"></i>
                        </div>
                        <div class="space-y-1 mt-4">
                            <h3 class="text-lg font-black text-white">Sedang Memproses Instalasi...</h3>
                            <p class="text-xs text-slate-400">Mengimpor master data, mengatur paroki default, dan menyusun konfigurasi sistem.</p>
                        </div>
                    </div>

                    <div id="install_success" class="hidden space-y-5">
                        <div class="w-16 h-16 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 flex items-center justify-center text-3xl mx-auto shadow-xl">
                            <i class="fa-solid fa-check"></i>
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-xl font-black text-white">Instalasi Berhasil!</h3>
                            <p class="text-xs text-slate-400 max-w-md mx-auto">SIPAROKI telah siap digunakan. Seluruh konfigurasi dan master data paroki telah berhasil diterapkan.</p>
                        </div>
                        <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 max-w-sm mx-auto text-left text-xs space-y-1 font-mono">
                            <p class="text-slate-400">Paroki: <span id="res_paroki" class="text-amber-400 font-bold"></span></p>
                            <p class="text-slate-400">Email: <span id="res_email" class="text-slate-200"></span></p>
                            <p class="text-slate-400">Password: <span id="res_pass" class="text-slate-200"></span></p>
                        </div>
                        <div class="space-y-2 pt-2">
                            <p id="countdown-text-standalone" class="text-xs text-amber-400 font-bold"><i class="fa-solid fa-clock"></i> Mengarahkan ke halaman login dalam <span id="countdown-sec-standalone">6</span> detik...</p>
                            <div class="flex items-center justify-center gap-3">
                                <a id="btn_go_login" href="<?= htmlspecialchars($root_app_url) ?>/login" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs shadow-xl shadow-amber-500/20 transition">
                                    <span>Masuk ke Dashboard</span>
                                    <i class="fa-solid fa-arrow-right"></i>
                                </a>
                                <a id="btn_go_home" href="<?= htmlspecialchars($root_app_url) ?>/" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-200 font-bold text-xs transition">
                                    <i class="fa-solid fa-globe"></i>
                                    <span>Website Publik</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <div id="install_error" class="hidden space-y-4">
                        <div class="w-16 h-16 rounded-2xl bg-rose-500/20 border border-rose-500/40 text-rose-400 flex items-center justify-center text-3xl mx-auto shadow-xl">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div class="space-y-1">
                            <h3 class="text-lg font-black text-rose-400">Instalasi Terkendala</h3>
                            <p id="err_msg" class="text-xs text-slate-400 max-w-md mx-auto"></p>
                        </div>
                        <button type="button" onclick="goToStep(3)" class="px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition">
                            Coba Ulangi
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full border-t border-slate-800/80 py-4 px-4 sm:px-8 text-center text-xs text-slate-500">
        <p>SIPAROKI &copy; <?= date('Y') ?> &bull; Sistem Informasi Manajemen Pastoral Paroki</p>
    </footer>

    <!-- Script Logic -->
    <script>
        // Embedded master data fallback (instant load on any hosting/vps without AJAX mime block)
        let masterData = <?= $embedded_master_json ?>;

        $(document).ready(function() {
            // Initialize Select2
            $('#sel_keuskupan').select2({
                placeholder: '-- Pilih Keuskupan (Cari Nama) --',
                width: '100%'
            });
            $('#sel_dekenat').select2({
                placeholder: '-- Semua Dekenat / Kevikepan --',
                width: '100%'
            });
            $('#sel_paroki').select2({
                placeholder: '-- Pilih / Cari Paroki Terdaftar --',
                width: '100%'
            });

            populateKeuskupan();
            if ($('#sel_keuskupan').val()) {
                onKeuskupanChange();
            }

            $('#sel_keuskupan').on('change', onKeuskupanChange);
            $('#sel_dekenat').on('change', onDekenatChange);
            $('#sel_paroki').on('change', onParokiSelect);
        });

        function goToStep(num) {
            $('.step-content').removeClass('active');
            $('#step-' + num).addClass('active');
            
            $('.step-pill').removeClass('bg-amber-500 text-slate-950 font-bold').addClass('bg-slate-800 text-slate-400 font-semibold');
            $('#pill-' + num).removeClass('bg-slate-800 text-slate-400 font-semibold').addClass('bg-amber-500 text-slate-950 font-bold');
        }

        function populateKeuskupan() {
            let html = '<option value="">-- Pilih Keuskupan (39 Keuskupan KWI) --</option>';
            if (masterData.keuskupan && masterData.keuskupan.length > 0) {
                masterData.keuskupan.forEach(function(k) {
                    let isSelected = (parseInt(k.id_keuskupan) === 8 || parseInt(k.id_keuskupan) === 5 || k.nama_keuskupan === 'Keuskupan Agung Kupang') ? ' selected' : '';
                    html += `<option value="${k.id_keuskupan}"${isSelected}>${k.nama_keuskupan} (Regio ${k.regio || 'Indonesia'})</option>`;
                });
            } else {
                html += '<option value="8" selected>Keuskupan Agung Kupang</option>';
            }
            $('#sel_keuskupan').html(html).trigger('change.select2');
        }

        function onKeuskupanChange() {
            let kId = parseInt($('#sel_keuskupan').val());
            let dekenatHtml = '<option value="">-- Semua Dekenat / Kevikepan --</option>';
            let parokiHtml = '<option value="">-- Pilih dari Paroki Terdaftar --</option>';

            if (masterData.dekenat) {
                masterData.dekenat.filter(d => d.keuskupan_id == kId).forEach(function(d) {
                    dekenatHtml += `<option value="${d.id}">${d.nama_dekenat}</option>`;
                });
            }
            $('#sel_dekenat').html(dekenatHtml).trigger('change.select2');

            if (masterData.paroki) {
                masterData.paroki.filter(p => p.keuskupan_id == kId).forEach(function(p) {
                    parokiHtml += `<option value="${p.id_paroki}" data-name="${p.nama_paroki}" data-alamat="${p.alamat || ''}">${p.nama_paroki}</option>`;
                });
            }
            $('#sel_paroki').html(parokiHtml).trigger('change.select2');
        }

        function onDekenatChange() {
            let kId = parseInt($('#sel_keuskupan').val());
            let dId = parseInt($('#sel_dekenat').val());
            let parokiHtml = '<option value="">-- Pilih dari Paroki Terdaftar --</option>';

            if (masterData.paroki) {
                let filtered = masterData.paroki.filter(p => p.keuskupan_id == kId);
                if (dId) {
                    filtered = filtered.filter(p => p.dekenat_id == dId);
                }
                filtered.forEach(function(p) {
                    parokiHtml += `<option value="${p.id_paroki}" data-name="${p.nama_paroki}" data-alamat="${p.alamat || ''}">${p.nama_paroki}</option>`;
                });
            }
            $('#sel_paroki').html(parokiHtml).trigger('change.select2');
        }

        function onParokiSelect() {
            let sel = $('#sel_paroki option:selected');
            let name = sel.data('name');
            let alamat = sel.data('alamat');
            if (name) {
                $('#paroki_name').val(name);
                if (alamat) $('#alamat_paroki').val(alamat);
            }
        }

        function testDatabase() {
            $('#btn_test_db').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Menguji...');
            $('#db_alert').addClass('hidden');

            $.ajax({
                url: window.location.href.split('?')[0] + '?action=test_db',
                type: 'POST',
                data: {
                    db_host: $('#db_host').val(),
                    db_port: $('#db_port').val(),
                    db_name: $('#db_name').val(),
                    db_user: $('#db_user').val(),
                    db_pass: $('#db_pass').val(),
                },
                dataType: 'json',
                success: function(res) {
                    $('#btn_test_db').prop('disabled', false).html('<span>Uji Koneksi &amp; Lanjut</span> <i class="fa-solid fa-arrow-right"></i>');
                    if (res.status === 'success') {
                        goToStep(3);
                    } else {
                        $('#db_alert').removeClass('hidden bg-emerald-500/20 text-emerald-300').addClass('bg-rose-500/20 text-rose-300 border border-rose-500/30').html(res.message);
                    }
                },
                error: function(xhr) {
                    $('#btn_test_db').prop('disabled', false).html('<span>Uji Koneksi &amp; Lanjut</span> <i class="fa-solid fa-arrow-right"></i>');
                    $('#db_alert').removeClass('hidden bg-emerald-500/20 text-emerald-300').addClass('bg-rose-500/20 text-rose-300 border border-rose-500/30').html('Gagal menghubungkan ke server MySQL: ' + (xhr.responseJSON?.message || xhr.statusText));
                }
            });
        }

        function executeInstallation() {
            if (!$('#paroki_name').val().trim()) {
                alert('Nama Paroki wajib diisi.');
                return;
            }

            goToStep(4);
            $('#install_loading').removeClass('hidden');
            $('#install_success').addClass('hidden');
            $('#install_error').addClass('hidden');

            let postData = {
                db_host: $('#db_host').val(),
                db_port: $('#db_port').val(),
                db_name: $('#db_name').val(),
                db_user: $('#db_user').val(),
                db_pass: $('#db_pass').val(),
                app_name: 'SIPAROKI ' + $('#paroki_name').val(),
                paroki_name: $('#paroki_name').val(),
                keuskupan_name: $('#sel_keuskupan option:selected').text(),
                alamat_paroki: $('#alamat_paroki').val(),
                selected_keuskupan_id: $('#sel_keuskupan').val(),
                selected_dekenat_id: $('#sel_dekenat').val(),
                selected_paroki_id: $('#sel_paroki').val(),
                admin_name: $('#admin_name').val(),
                admin_email: $('#admin_email').val(),
                admin_pass: $('#admin_pass').val(),
            };

            $.ajax({
                url: window.location.href.split('?')[0] + '?action=process_install',
                type: 'POST',
                data: postData,
                dataType: 'json',
                success: function(res) {
                    $('#install_loading').addClass('hidden');
                    if (res.status === 'success') {
                        $('#res_paroki').text(res.paroki || postData.paroki_name);
                        $('#res_email').text(postData.admin_email);
                        $('#res_pass').text(postData.admin_pass);
                        const redirectUrl = res.redirect || '<?= htmlspecialchars($root_app_url) ?>/login';
                        $('#btn_go_login').attr('href', redirectUrl);
                        $('#install_success').removeClass('hidden');

                        let sec = 6;
                        const timer = setInterval(() => {
                            sec--;
                            $('#countdown-sec-standalone').text(sec);
                            if (sec <= 0) {
                                clearInterval(timer);
                                window.location.href = redirectUrl;
                            }
                        }, 1000);
                    } else {
                        $('#err_msg').text(res.message);
                        $('#install_error').removeClass('hidden');
                    }
                },
                error: function(xhr) {
                    $('#install_loading').addClass('hidden');
                    $('#err_msg').text('Terjadi kesalahan saat memproses data: ' + (xhr.responseJSON?.message || xhr.responseText));
                    $('#install_error').removeClass('hidden');
                }
            });
        }
    </script>
</body>
</html>
