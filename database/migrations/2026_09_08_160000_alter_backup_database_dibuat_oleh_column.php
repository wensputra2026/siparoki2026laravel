<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('backup_database')) {
            try {
                DB::statement("ALTER TABLE `backup_database` MODIFY COLUMN `dibuat_oleh` VARCHAR(191) NULL DEFAULT NULL");
            } catch (\Throwable $e) {
                // Fallback using Schema builder
                try {
                    Schema::table('backup_database', function (Blueprint $table) {
                        $table->string('dibuat_oleh', 191)->nullable()->change();
                    });
                } catch (\Throwable $ex) {}
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('backup_database')) {
            try {
                DB::statement("ALTER TABLE `backup_database` MODIFY COLUMN `dibuat_oleh` INT NULL DEFAULT NULL");
            } catch (\Throwable $e) {}
        }
    }
};
