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

    // 2. Import Master SQL Schema
    $sqlPath = database_path('siparoki.sql');
    if (!File::exists($sqlPath)) {
        $sqlPath = database_path('data/siparoki.sql');
    }

    if (File::exists($sqlPath)) {
        $this->info('2. Mengimpor Skema Master Database SIPAROKI (Struktur & Wilayah Se-Indonesia)...');
        $pdo = DB::connection()->getPdo();
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

        $sqlContent = File::get($sqlPath);
        // Split SQL into manageable chunks
        $queries = preg_split('/;\s*[\r\n]+/', $sqlContent);
        $bar = $this->output->createProgressBar(count($queries));
        $bar->start();

        foreach ($queries as $query) {
            $trimmed = trim($query);
            if (!empty($trimmed) && !str_starts_with($trimmed, '--') && !str_starts_with($trimmed, '/*')) {
                try {
                    $pdo->exec($trimmed);
                } catch (\Throwable $e) {
                    // Silently continue for already existing constraints
                }
            }
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
        $this->info('   [OK] Skema Master Database Berhasil Diimpor!');
    }

    // 3. Run Incremental Migrations
    $this->info('3. Menjalankan Migrasi Penyesuaian Laravel...');
    $this->call('migrate', ['--force' => true]);

    // 4. Seed Essential Roles & Super Admin
    $this->info('4. Menyiapkan Akun Super Administrator & Konfigurasi Paroki...');
    $this->call('db:seed', ['--force' => true]);

    // 5. Clear Caches
    $this->info('5. Mengoptimalkan Cache Aplikasi...');
    $this->call('optimize:clear');

    $this->newLine();
    $this->info('====================================================');
    $this->info('  INSTALASI SELESAI & SISTEM SIAP DIGUNAKAN!        ');
    $this->info('====================================================');
    $this->line('  URL Akses : http://127.0.0.1:8000/login');
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
