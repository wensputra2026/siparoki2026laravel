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

                // JANGAN timpa / hapus tabel 'migrations' bawaan Laravel
                if (stripos($trimmed, '`migrations`') !== false && 
                    (stripos($trimmed, 'DROP TABLE') !== false || stripos($trimmed, 'CREATE TABLE') !== false || stripos($trimmed, 'INSERT INTO') !== false)) {
                    continue;
                }

                $buffer .= $line;

                // Eksekusi setiap ~256KB untuk efisiensi tinggi tanpa membebani memori
                if (strlen($buffer) > 262144 && str_ends_with(rtrim($trimmed), ';')) {
                    try {
                        $pdo->exec($buffer);
                    } catch (\Throwable $e) {
                        // Abaikan error non-kritis duplicate/drop
                    }
                    $buffer = '';
                }
            }

            if (!empty(trim($buffer))) {
                try {
                    $pdo->exec($buffer);
                } catch (\Throwable $e) {
                    // Abaikan error non-kritis
                }
            }

            fclose($handle);
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
