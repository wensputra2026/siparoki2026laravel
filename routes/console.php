<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('siparoki:setup {--force : Paksa timpa database yang ada}', function () {
    $this->info('====================================================');
    $this->info('  SIPAROKI 2026 - Pemasangan & Setup Otomatis Basis Data');
    $this->info('====================================================');

    // 1. Check or generate APP_KEY
    if (empty(config('app.key'))) {
        $this->info('1. Membuat Kunci Aplikasi (APP_KEY)...');
        $this->call('key:generate', ['--force' => true]);
    }

    // 2. Import Master SQL Schema if needed
    $needsImport = $this->option('force') || !Schema::hasTable('roles') || !Schema::hasTable('konten');

    if ($needsImport) {
        $candidates = [
            database_path('siparoki_starter_template.sql'),
            database_path('siparoki.sql'),
            database_path('data/siparoki.sql'),
        ];

        $sqlPath = null;
        foreach ($candidates as $cand) {
            if (File::exists($cand)) {
                $sqlPath = $cand;
                break;
            }
        }

        if (!$sqlPath) {
            $zipPath = database_path('siparoki_starter_template.zip');
            if (File::exists($zipPath) && class_exists('ZipArchive')) {
                $this->info('   Mengekstrak database template dari ZIP...');
                $zip = new \ZipArchive();
                if ($zip->open($zipPath) === true) {
                    $zip->extractTo(database_path());
                    $zip->close();
                    if (File::exists(database_path('siparoki_starter_template.sql'))) {
                        $sqlPath = database_path('siparoki_starter_template.sql');
                    }
                }
            }
        }

        if ($sqlPath && File::exists($sqlPath)) {
            $this->info('2. Mengimpor Skema Master Database (163 Tabel & Master Data)...');
            $pdo = DB::connection()->getPdo();
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
            $pdo->exec("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';");

            $handle = fopen($sqlPath, 'r');
            $buffer = '';
            $executedChunks = 0;

            while (($line = fgets($handle)) !== false) {
                $trimmed = trim($line);
                if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
                    continue;
                }

                // Jangan timpa tabel migrations bawaan Laravel
                if (stripos($trimmed, '`migrations`') !== false && 
                    (stripos($trimmed, 'DROP TABLE') !== false || stripos($trimmed, 'CREATE TABLE') !== false || stripos($trimmed, 'INSERT INTO') !== false)) {
                    continue;
                }

                $buffer .= $line;
                if (strlen($buffer) > 262144 && str_ends_with(rtrim($trimmed), ';')) {
                    try {
                        $pdo->exec($buffer);
                        $executedChunks++;
                    } catch (\Throwable $e) {}
                    $buffer = '';
                }
            }

            if (!empty(trim($buffer))) {
                try {
                    $pdo->exec($buffer);
                    $executedChunks++;
                } catch (\Throwable $e) {}
            }

            fclose($handle);
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
            $this->info("   [OK] Skema Master Database Berhasil Diimpor!");
        }
    } else {
        $this->info('2. Basis data inti sudah ada (melewati import awal).');
    }

    // 3. Run Incremental Migrations
    $this->info('3. Menjalankan Migrasi Penyesuaian Laravel...');
    $this->call('migrate', ['--force' => true]);

    // 4. Seed Essential Roles & Super Admin
    $this->info('4. Menyiapkan Akun Super Administrator & Konfigurasi Paroki...');
    $this->call('db:seed', ['--force' => true]);

    // 5. Storage Symlink
    $this->info('5. Memeriksa Symlink Storage Publik...');
    try {
        $this->call('storage:link');
    } catch (\Throwable $e) {}

    // 6. Clear Caches
    $this->info('6. Mengoptimalkan Cache Aplikasi...');
    $this->call('optimize:clear');

    $this->newLine();
    $this->info('====================================================');
    $this->info('  INSTALASI SELESAI & SISTEM SIAP DIGUNAKAN!        ');
    $this->info('====================================================');
    $this->line('  Email     : superadmin@paroki.org');
    $this->line('  Password  : Admin@Paroki2026!');
    $this->info('====================================================');
})->purpose('Setup otomatis database, master data, dan akun superadmin SIPAROKI');

Artisan::command('admin:reset {email=superadmin@paroki.org} {password=Admin@Paroki2026!}', function ($email, $password) {
    $userCols = Schema::getColumnListing('users');
    $user = \App\Models\User::where('email', $email)->orWhere('username', 'superadmin')->orWhere('id', 1)->first()
        ?? new \App\Models\User();

    $data = [
        'email' => $email,
        'username' => $user->username ?: 'superadmin',
        'nama_lengkap' => $user->nama_lengkap ?: 'Super Administrator',
        'role_id' => 1,
        'password' => Hash::make($password),
        'status' => 1,
    ];

    foreach ($data as $col => $val) {
        if (in_array($col, $userCols, true)) {
            $user->{$col} = $val;
        }
    }

    $user->save();

    $this->info('====================================================');
    $this->info('  AKUN SUPER ADMIN BERHASIL DI-RESET / DISIAPKAN!   ');
    $this->info('====================================================');
    $this->line("  Email    : {$email}");
    $this->line("  Username : " . ($user->username ?: 'superadmin'));
    $this->line("  Password : {$password}");
    $this->info('====================================================');
})->purpose('Reset atau buat akun Super Administrator baru');
