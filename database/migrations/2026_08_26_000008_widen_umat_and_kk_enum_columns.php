<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Table umat: convert restrictive ENUMs to VARCHAR
        if (Schema::hasTable('umat')) {
            $columnsToWiden = [
                'status_menikah' => "VARCHAR(50) NULL DEFAULT 'Belum Menikah'",
                'status_tinggal' => "VARCHAR(50) NULL DEFAULT 'Tetap'",
                'status_umat' => "VARCHAR(50) NULL DEFAULT 'Aktif'",
                'golongan_darah' => "VARCHAR(20) NULL",
                'rhesus' => "VARCHAR(10) NULL",
                'kondisi_umum' => "VARCHAR(50) NULL",
                'agama_saat_ini' => "VARCHAR(100) NULL DEFAULT 'Katolik'",
                'posisi_tinggal_sekarang' => "VARCHAR(100) NULL",
                'jenis_sekolah' => "VARCHAR(100) NULL",
                'kewarganegaraan' => "VARCHAR(20) NULL DEFAULT 'WNI'",
            ];

            foreach ($columnsToWiden as $column => $definition) {
                if (Schema::hasColumn('umat', $column)) {
                    try {
                        DB::statement("ALTER TABLE `umat` MODIFY COLUMN `{$column}` {$definition}");
                    } catch (\Throwable $e) {}
                }
            }
        }

        // 2. Table kk_katolik: convert restrictive ENUMs to VARCHAR
        if (Schema::hasTable('kk_katolik')) {
            $kkColumns = [
                'status_pemilik_kk' => "VARCHAR(100) NULL",
                'jenis_rumah_tinggal' => "VARCHAR(100) NULL",
                'jenis_agama_nikah' => "VARCHAR(100) NULL",
                'status_verifikasi' => "VARCHAR(50) NULL DEFAULT 'Belum'",
                'status_kk' => "VARCHAR(50) NULL DEFAULT 'Aktif'",
            ];

            foreach ($kkColumns as $column => $definition) {
                if (Schema::hasColumn('kk_katolik', $column)) {
                    try {
                        DB::statement("ALTER TABLE `kk_katolik` MODIFY COLUMN `{$column}` {$definition}");
                    } catch (\Throwable $e) {}
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down needed
    }
};
