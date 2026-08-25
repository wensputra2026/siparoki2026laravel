<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('kolekte')) {
            Schema::table('kolekte', function (Blueprint $table) {
                if (!Schema::hasColumn('kolekte', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable()->after('created_at');
                }
                if (!Schema::hasColumn('kolekte', 'lokasi_misa')) {
                    $table->string('lokasi_misa', 255)->nullable()->after('petugas_penghitung');
                }
                if (!Schema::hasColumn('kolekte', 'keterangan')) {
                    $table->text('keterangan')->nullable()->after('lokasi_misa');
                }
                if (!Schema::hasColumn('kolekte', 'wilayah_id')) {
                    $table->unsignedInteger('wilayah_id')->nullable()->after('keterangan');
                }
                if (!Schema::hasColumn('kolekte', 'kapela_id')) {
                    $table->unsignedInteger('kapela_id')->nullable()->after('wilayah_id');
                }
                if (!Schema::hasColumn('kolekte', 'kub_id')) {
                    $table->unsignedInteger('kub_id')->nullable()->after('kapela_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('kolekte')) {
            Schema::table('kolekte', function (Blueprint $table) {
                // Keep columns for safety
            });
        }
    }
};
