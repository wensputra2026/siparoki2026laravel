<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Secara otomatis menginisialisasi seluruh 163 tabel inti SIPAROKI
     * jika dijalankan pada database yang masih bersih / kosong.
     */
    public function up(): void
    {
        // 1. Cek jika tabel inti sudah ada, lewati agar tidak menimpa data
        if (Schema::hasTable('roles') && Schema::hasTable('konten') && Schema::hasTable('umat')) {
            return;
        }

        // 2. Cari file skema master SQL starter
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

        // Jika hanya ada file zip, ekstrak otomatis
        if (!$sqlPath) {
            $zipPath = database_path('siparoki_starter_template.zip');
            if (File::exists($zipPath) && class_exists('ZipArchive')) {
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

        if (!$sqlPath || !File::exists($sqlPath)) {
            return;
        }

        // 3. Eksekusi skema master database dengan aman via PDO
        $pdo = DB::connection()->getPdo();
        $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, true);
        $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $pdo->exec("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';");

        $handle = fopen($sqlPath, 'r');
        if ($handle) {
            $buffer = '';
            while (($line = fgets($handle)) !== false) {
                $trimmed = trim($line);
                
                // Lewati komentar & baris kosong
                if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
                    continue;
                }

                $buffer .= $line;

                // Eksekusi setiap ~256KB untuk efisiensi tinggi tanpa membebani memori
                if (strlen($buffer) > 262144 && str_ends_with(rtrim($trimmed), ';')) {
                    try {
                        $pdo->exec($buffer);
                    } catch (\Throwable $e) {
                        // Fallback statement-by-statement agar tabel lain tidak terlewat jika ada error parsial
                        $statements = preg_split('/;\s*[\r\n]+/', $buffer);
                        foreach ($statements as $stmt) {
                            $s = trim($stmt);
                            if (!empty($s)) {
                                try {
                                    $pdo->exec($s);
                                } catch (\Throwable $se) {}
                            }
                        }
                    }
                    $buffer = '';
                }
            }

            if (!empty(trim($buffer))) {
                try {
                    $pdo->exec($buffer);
                } catch (\Throwable $e) {
                    $statements = preg_split('/;\s*[\r\n]+/', $buffer);
                    foreach ($statements as $stmt) {
                        $s = trim($stmt);
                        if (!empty($s)) {
                            try {
                                $pdo->exec($s);
                            } catch (\Throwable $se) {}
                        }
                    }
                }
            }

            fclose($handle);

            // 4. Daftarkan seluruh migrasi bawaan skema master agar tidak dijalankan ulang
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
        }

        $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Sengaja dibiarkan kosong agar rollback tidak menghapus data umat
    }
};
