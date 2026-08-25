<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Fix master_pastor column types to prevent truncation errors on flexible strings
        if (Schema::hasTable('master_pastor')) {
            try {
                DB::statement("ALTER TABLE `master_pastor` MODIFY COLUMN `jenis_imam` VARCHAR(100) NULL DEFAULT 'Diosesan'");
            } catch (\Throwable $e) {}

            try {
                DB::statement("ALTER TABLE `master_pastor` MODIFY COLUMN `status` VARCHAR(100) NULL DEFAULT 'Aktif Melayani'");
            } catch (\Throwable $e) {}

            try {
                DB::statement("ALTER TABLE `master_pastor` MODIFY COLUMN `jabatan` VARCHAR(150) NULL DEFAULT 'Pastor Paroki'");
            } catch (\Throwable $e) {}
        }

        // 2. Fix kuasi_paroki KodePos length
        if (Schema::hasTable('kuasi_paroki')) {
            try {
                DB::statement("ALTER TABLE `kuasi_paroki` MODIFY COLUMN `KodePos` VARCHAR(50) NULL");
            } catch (\Throwable $e) {}
            try {
                DB::statement("ALTER TABLE `kuasi_paroki` MODIFY COLUMN `StatusAktif` VARCHAR(50) NULL DEFAULT 'Aktif'");
            } catch (\Throwable $e) {}
        }

        // 3. Fix kevikepan status length
        if (Schema::hasTable('kevikepan')) {
            try {
                DB::statement("ALTER TABLE `kevikepan` MODIFY COLUMN `status` VARCHAR(50) NULL DEFAULT 'Aktif'");
            } catch (\Throwable $e) {}
        }

        // 4. Fix paroki status & status_paroki
        if (Schema::hasTable('paroki')) {
            try {
                DB::statement("ALTER TABLE `paroki` MODIFY COLUMN `status_paroki` VARCHAR(50) NULL DEFAULT 'Paroki'");
            } catch (\Throwable $e) {}
            try {
                DB::statement("ALTER TABLE `paroki` MODIFY COLUMN `status` VARCHAR(50) NULL DEFAULT 'Aktif'");
            } catch (\Throwable $e) {}
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down needed for column expansion
    }
};
