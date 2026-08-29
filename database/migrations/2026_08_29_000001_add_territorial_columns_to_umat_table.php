<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The 'umat' table is referenced throughout the app (statistics, policies,
     * panel access, KK/wilayah scoping) via wilayah_id / kub_id / lingkungan_id /
     * kapela_id, but those foreign-key columns were missing from the schema.
     * This adds them as nullable columns so existing queries stop failing with
     * "Unknown column 'wilayah_id' / 'kub_id'".
     */
    public function up(): void
    {
        if (!Schema::hasTable('umat')) {
            return;
        }

        Schema::table('umat', function (Blueprint $table) {
            if (!Schema::hasColumn('umat', 'wilayah_id')) {
                $table->unsignedBigInteger('wilayah_id')->nullable()->after('kk_id')->index();
            }
            if (!Schema::hasColumn('umat', 'kub_id')) {
                $table->unsignedBigInteger('kub_id')->nullable()->after('wilayah_id')->index();
            }
            if (!Schema::hasColumn('umat', 'lingkungan_id')) {
                $table->unsignedBigInteger('lingkungan_id')->nullable()->after('kub_id')->index();
            }
            if (!Schema::hasColumn('umat', 'kapela_id')) {
                $table->unsignedBigInteger('kapela_id')->nullable()->after('lingkungan_id')->index();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('umat')) {
            return;
        }

        Schema::table('umat', function (Blueprint $table) {
            foreach (['wilayah_id', 'kub_id', 'lingkungan_id', 'kapela_id'] as $col) {
                if (Schema::hasColumn('umat', $col)) {
                    $table->dropIndexIfExists('umat_' . $col . '_index');
                    $table->dropColumn($col);
                }
            }
        });
    }
};
