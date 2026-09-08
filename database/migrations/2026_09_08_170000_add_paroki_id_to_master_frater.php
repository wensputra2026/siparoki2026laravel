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
        if (Schema::hasTable('master_frater')) {
            Schema::table('master_frater', function (Blueprint $table) {
                if (!Schema::hasColumn('master_frater', 'paroki_id')) {
                    $table->unsignedInteger('paroki_id')->nullable()->after('jenis_frater')->index();
                }
                if (!Schema::hasColumn('master_frater', 'paroki_tugas')) {
                    $table->string('paroki_tugas', 150)->nullable()->after('paroki_id');
                }
                if (!Schema::hasColumn('master_frater', 'jabatan')) {
                    $table->string('jabatan', 100)->nullable()->default('Frater TOP')->after('paroki_tugas');
                }
                if (!Schema::hasColumn('master_frater', 'catatan_pelayanan')) {
                    $table->string('catatan_pelayanan', 255)->nullable()->after('keterangan');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('master_frater')) {
            Schema::table('master_frater', function (Blueprint $table) {
                $cols = [];
                if (Schema::hasColumn('master_frater', 'catatan_pelayanan')) $cols[] = 'catatan_pelayanan';
                if (Schema::hasColumn('master_frater', 'jabatan')) $cols[] = 'jabatan';
                if (Schema::hasColumn('master_frater', 'paroki_tugas')) $cols[] = 'paroki_tugas';
                if (Schema::hasColumn('master_frater', 'paroki_id')) $cols[] = 'paroki_id';
                if (!empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
