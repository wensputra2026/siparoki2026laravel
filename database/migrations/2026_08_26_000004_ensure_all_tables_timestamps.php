<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('backup_database') && !Schema::hasColumn('backup_database', 'updated_at')) {
            Schema::table('backup_database', function (Blueprint $table) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            });
        }

        if (Schema::hasTable('pengaturan_aplikasi')) {
            Schema::table('pengaturan_aplikasi', function (Blueprint $table) {
                if (!Schema::hasColumn('pengaturan_aplikasi', 'created_at')) {
                    $table->timestamp('created_at')->nullable();
                }
                if (!Schema::hasColumn('pengaturan_aplikasi', 'updated_at')) {
                    $table->timestamp('updated_at')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
    }
};
