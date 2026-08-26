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
        if (Schema::hasTable('umat')) {
            try {
                if (Schema::hasColumn('umat', 'kk_id')) {
                    DB::statement("ALTER TABLE `umat` MODIFY COLUMN `kk_id` INT NULL DEFAULT NULL");
                }
                if (Schema::hasColumn('umat', 'nama_lahir')) {
                    DB::statement("ALTER TABLE `umat` MODIFY COLUMN `nama_lahir` VARCHAR(150) NULL DEFAULT NULL");
                }
            } catch (\Throwable $e) {}
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
