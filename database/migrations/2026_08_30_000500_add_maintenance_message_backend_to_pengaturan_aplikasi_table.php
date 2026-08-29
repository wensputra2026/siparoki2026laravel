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
        if (Schema::hasTable('pengaturan_aplikasi') && !Schema::hasColumn('pengaturan_aplikasi', 'maintenance_message_backend')) {
            Schema::table('pengaturan_aplikasi', function (Blueprint $table) {
                $table->text('maintenance_message_backend')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('pengaturan_aplikasi') && Schema::hasColumn('pengaturan_aplikasi', 'maintenance_message_backend')) {
            Schema::table('pengaturan_aplikasi', function (Blueprint $table) {
                $table->dropColumn('maintenance_message_backend');
            });
        }
    }
};
