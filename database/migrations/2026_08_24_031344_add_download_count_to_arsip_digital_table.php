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
        if (!Schema::hasColumn('arsip_digital', 'download_count')) {
            Schema::table('arsip_digital', function (Blueprint $table) {
                $table->unsignedInteger('download_count')->default(0)->after('file_size');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('arsip_digital', 'download_count')) {
            Schema::table('arsip_digital', function (Blueprint $table) {
                $table->dropColumn('download_count');
            });
        }
    }
};
