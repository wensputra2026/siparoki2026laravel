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
        if (!Schema::hasTable('notifikasi_sistem')) {
            Schema::create('notifikasi_sistem', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('role_target', 50)->nullable()->index(); // kub, wilayah, kapela, all
                $table->unsignedBigInteger('kub_id')->nullable()->index();
                $table->unsignedBigInteger('wilayah_id')->nullable()->index();
                $table->unsignedBigInteger('kapela_id')->nullable()->index();
                $table->string('tipe', 50)->default('mutasi_masuk')->index(); // mutasi_masuk, mutasi_keluar, info, kematian
                $table->string('judul');
                $table->text('pesan');
                $table->string('link')->nullable();
                $table->json('data')->nullable();
                $table->boolean('is_read')->default(false)->index();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifikasi_sistem');
    }
};
