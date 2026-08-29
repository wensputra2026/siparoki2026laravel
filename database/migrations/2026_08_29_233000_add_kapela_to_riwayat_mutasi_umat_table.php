<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('riwayat_mutasi_umat')) {
            Schema::table('riwayat_mutasi_umat', function (Blueprint $table) {
                if (!Schema::hasColumn('riwayat_mutasi_umat', 'kapela_asal_id')) {
                    $table->unsignedBigInteger('kapela_asal_id')->nullable()->after('wilayah_asal_id')->index();
                }
                if (!Schema::hasColumn('riwayat_mutasi_umat', 'kapela_tujuan_id')) {
                    $table->unsignedBigInteger('kapela_tujuan_id')->nullable()->after('wilayah_tujuan_id')->index();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('riwayat_mutasi_umat')) {
            Schema::table('riwayat_mutasi_umat', function (Blueprint $table) {
                if (Schema::hasColumn('riwayat_mutasi_umat', 'kapela_asal_id')) {
                    $table->dropColumn('kapela_asal_id');
                }
                if (Schema::hasColumn('riwayat_mutasi_umat', 'kapela_tujuan_id')) {
                    $table->dropColumn('kapela_tujuan_id');
                }
            });
        }
    }
};
