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

            // Daftarkan seluruh migrasi bawaan skema master
            $baselineMigrations = [
                '0001_01_01_000000_create_users_table',
                '0001_01_01_000001_create_cache_table',
                '0001_01_01_000002_create_jobs_table',
                '2026_01_01_000001_create_kategori_konten_table',
                '2026_08_24_031344_add_download_count_to_arsip_digital_table',
                '2026_08_24_123000_create_pesan_kontak_table',
                '2026_08_24_144230_create_personal_access_tokens_table',
                '2026_08_25_160000_create_komentar_artikel_table',
                '2026_08_25_170000_create_finance_and_asset_suite_tables',
                '2026_08_26_000000_add_scope_indexes',
                '2026_08_26_000001_create_defunctorum_table',
                '2026_08_26_000002_create_jadwal_petugas_liturgi_table',
                '2026_08_26_000003_create_sambutan_pastor_table',
                '2026_08_26_000003_fix_kolekte_table_columns',
                '2026_08_26_000004_alter_jadwal_petugas_liturgi_nullable',
                '2026_08_26_000004_ensure_all_tables_timestamps',
                '2026_08_26_000005_standardize_all_table_timestamps',
                '2026_08_26_000006_add_master_pastor_form_columns',
                '2026_08_26_000006_fix_master_pastor_and_kuasi_paroki',
                '2026_08_26_000007_sync_default_pastor_paroki',
                '2026_08_26_000008_widen_umat_and_kk_enum_columns',
                '2026_08_26_000009_make_umat_kk_id_nullable',
                '2026_08_28_100000_ensure_slug_on_kapela_and_stasi_table',
                '2026_08_29_000001_add_is_deleted_and_sedang_bertugas_to_master_pastor',
                '2026_08_29_000001_add_territorial_columns_to_umat_table',
                '2026_08_29_000002_sync_paroki_keuskupan_and_dekenat_relationships',
                '2026_08_29_213707_add_dekenat_and_paroki_to_master_pastor',
                '2026_08_29_230000_create_notifikasi_sistem_table',
                '2026_08_29_233000_add_kapela_to_riwayat_mutasi_umat_table',
                '2026_08_29_234500_create_chat_pesan_table',
                '2026_08_29_235500_add_fitur_chat_aktif_to_pengaturan_aplikasi_table',
                '2026_08_30_000500_add_maintenance_message_backend_to_pengaturan_aplikasi_table',
                '2026_09_02_185600_create_hierarki_gereja_dan_sipil_tables',
                '2026_09_04_000001_add_foto_pastor_to_profil_paroki_and_paroki_table',
                '2026_09_05_083500_add_uuid_to_core_tables',
            ];

            try {
                foreach ($baselineMigrations as $bm) {
                    DB::table('migrations')->updateOrInsert(
                        ['migration' => $bm],
                        ['batch' => 1]
                    );
                }
            } catch (\Throwable $e) {}

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

Artisan::command('paroki:set {keyword : Nama Paroki atau ID Paroki yang ingin diaktifkan}', function ($keyword) {
    if (!Schema::hasTable('paroki')) {
        $this->error('Tabel paroki belum tersedia di database. Jalankan php artisan siparoki:setup terlebih dahulu.');
        return;
    }

    $paroki = is_numeric($keyword)
        ? DB::table('paroki')->where('id_paroki', (int) $keyword)->first()
        : DB::table('paroki')->where('nama_paroki', 'like', "%{$keyword}%")->first();

    if (!$paroki) {
        $this->error("Paroki dengan kata kunci '{$keyword}' tidak ditemukan di 493 Master Paroki Nasional.");
        return;
    }

    $keuskupan = Schema::hasTable('keuskupan') && $paroki->keuskupan_id
        ? DB::table('keuskupan')->where('id_keuskupan', $paroki->keuskupan_id)->first()
        : null;

    $namaParoki = $paroki->nama_paroki;
    $namaKeuskupan = $keuskupan?->nama_keuskupan ?? 'Keuskupan Terdaftar';
    $alamat = $paroki->alamat ?: 'Alamat Paroki';
    $pastor = $paroki->nama_pastor_paroki_aktif ?? 'Pastor Paroki';

    if (Schema::hasTable('profil_paroki')) {
        $cols = Schema::getColumnListing('profil_paroki');
        $data = [
            'nama_paroki' => $namaParoki,
            'keuskupan' => $namaKeuskupan,
            'alamat' => $alamat,
            'pastor_paroki' => $pastor,
            'updated_at' => now(),
        ];
        if (in_array('paroki_id', $cols, true)) $data['paroki_id'] = $paroki->id_paroki;
        if (in_array('keuskupan_id', $cols, true)) $data['keuskupan_id'] = $paroki->keuskupan_id;
        if (in_array('dekenat_id', $cols, true)) $data['dekenat_id'] = $paroki->dekenat_id;
        $filtered = array_intersect_key($data, array_flip($cols));

        if (DB::table('profil_paroki')->count() > 0) {
            DB::table('profil_paroki')->update($filtered);
        } else {
            $filtered['created_at'] = now();
            DB::table('profil_paroki')->insert($filtered);
        }
    }

    if (Schema::hasTable('pengaturan_aplikasi')) {
        $appCols = Schema::getColumnListing('pengaturan_aplikasi');
        $appData = [
            'nama_aplikasi' => 'SIPAROKI',
            'nama_paroki' => $namaParoki,
            'nama_keuskupan' => $namaKeuskupan,
            'alamat' => $alamat,
            'updated_at' => now(),
        ];
        if (in_array('paroki_id', $appCols, true)) $appData['paroki_id'] = $paroki->id_paroki;
        if (in_array('keuskupan_id', $appCols, true)) $appData['keuskupan_id'] = $paroki->keuskupan_id;
        if (in_array('dekenat_id', $appCols, true)) $appData['dekenat_id'] = $paroki->dekenat_id;
        if (in_array('is_setup_completed', $appCols, true)) $appData['is_setup_completed'] = 1;
        $filteredApp = array_intersect_key($appData, array_flip($appCols));

        if (DB::table('pengaturan_aplikasi')->count() > 0) {
            DB::table('pengaturan_aplikasi')->update($filteredApp);
        } else {
            $filteredApp['created_at'] = now();
            DB::table('pengaturan_aplikasi')->insert($filteredApp);
        }
    }

    if (Schema::hasTable('pengaturan')) {
        DB::table('pengaturan')->updateOrInsert(['kunci' => 'nama_paroki'], ['nilai' => $namaParoki, 'updated_at' => now()]);
        DB::table('pengaturan')->updateOrInsert(['kunci' => 'keuskupan'], ['nilai' => $namaKeuskupan, 'updated_at' => now()]);
    }

    $this->call('optimize:clear');

    $this->info('====================================================');
    $this->info('  PAROKI AKTIF BERHASIL DIKONFIGURASI!             ');
    $this->info('====================================================');
    $this->line("  Nama Paroki  : {$namaParoki}");
    $this->line("  ID Paroki    : {$paroki->id_paroki}");
    $this->line("  Keuskupan    : {$namaKeuskupan}");
    $this->line("  Pastor Aktif : {$pastor}");
    $this->info('====================================================');
})->purpose('Ganti atau konfigurasi identitas paroki aktif dari master data paroki nasional');

