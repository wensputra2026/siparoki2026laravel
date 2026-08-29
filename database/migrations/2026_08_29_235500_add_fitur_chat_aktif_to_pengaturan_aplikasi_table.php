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
        if (Schema::hasTable('pengaturan_aplikasi') && !Schema::hasColumn('pengaturan_aplikasi', 'fitur_chat_aktif')) {
            Schema::table('pengaturan_aplikasi', function (Blueprint $table) {
                $table->boolean('fitur_chat_aktif')->default(true);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('pengaturan_aplikasi') && Schema::hasColumn('pengaturan_aplikasi', 'fitur_chat_aktif')) {
            Schema::table('pengaturan_aplikasi', function (Blueprint $table) {
                $table->dropColumn('fitur_chat_aktif');
            });
        }
    }
};
