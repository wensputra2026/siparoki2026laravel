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
        if (Schema::hasTable('profil_paroki')) {
            Schema::table('profil_paroki', function (Blueprint $table) {
                if (!Schema::hasColumn('profil_paroki', 'foto_pastor')) {
                    $table->string('foto_pastor', 255)->nullable()->after('pastor_paroki');
                }
            });
        }

        if (Schema::hasTable('paroki')) {
            Schema::table('paroki', function (Blueprint $table) {
                if (!Schema::hasColumn('paroki', 'foto_pastor')) {
                    $table->string('foto_pastor', 255)->nullable()->after('nama_pastor_paroki_aktif');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('profil_paroki')) {
            Schema::table('profil_paroki', function (Blueprint $table) {
                if (Schema::hasColumn('profil_paroki', 'foto_pastor')) {
                    $table->dropColumn('foto_pastor');
                }
            });
        }

        if (Schema::hasTable('paroki')) {
            Schema::table('paroki', function (Blueprint $table) {
                if (Schema::hasColumn('paroki', 'foto_pastor')) {
                    $table->dropColumn('foto_pastor');
                }
            });
        }
    }
};
