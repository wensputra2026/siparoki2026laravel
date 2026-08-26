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
        if (!Schema::hasTable('arsip_digital')) {
            Schema::create('arsip_digital', function (Blueprint $table) {
                $table->id();
                $table->string('judul_arsip');
                $table->string('kategori')->nullable();
                $table->string('file_path')->nullable();
                $table->string('file_size')->nullable();
                $table->unsignedInteger('download_count')->default(0);
                $table->text('keterangan')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        } elseif (!Schema::hasColumn('arsip_digital', 'download_count')) {
            Schema::table('arsip_digital', function (Blueprint $table) {
                $table->unsignedInteger('download_count')->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('arsip_digital') && Schema::hasColumn('arsip_digital', 'download_count')) {
            Schema::table('arsip_digital', function (Blueprint $table) {
                $table->dropColumn('download_count');
            });
        }
    }
};
