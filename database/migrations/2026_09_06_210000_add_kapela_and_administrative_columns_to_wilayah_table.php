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
        if (Schema::hasTable('wilayah')) {
            Schema::table('wilayah', function (Blueprint $table) {
                if (!Schema::hasColumn('wilayah', 'kapela_id')) {
                    $table->unsignedBigInteger('kapela_id')->nullable()->after('paroki_id');
                }
                if (!Schema::hasColumn('wilayah', 'alamat')) {
                    $table->text('alamat')->nullable()->after('no_hp');
                }
                if (!Schema::hasColumn('wilayah', 'provinsi_id')) {
                    $table->unsignedBigInteger('provinsi_id')->nullable();
                }
                if (!Schema::hasColumn('wilayah', 'kabupaten_id')) {
                    $table->unsignedBigInteger('kabupaten_id')->nullable();
                }
                if (!Schema::hasColumn('wilayah', 'kecamatan_id')) {
                    $table->unsignedBigInteger('kecamatan_id')->nullable();
                }
                if (!Schema::hasColumn('wilayah', 'desa_id')) {
                    $table->unsignedBigInteger('desa_id')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('wilayah')) {
            Schema::table('wilayah', function (Blueprint $table) {
                $columns = ['kapela_id', 'alamat', 'provinsi_id', 'kabupaten_id', 'kecamatan_id', 'desa_id'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('wilayah', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }
};
