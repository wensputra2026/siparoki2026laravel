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
        if (!Schema::hasTable('komentar_artikel')) {
            Schema::create('komentar_artikel', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('konten_id')->index();
                $table->unsignedBigInteger('parent_id')->nullable()->index();
                $table->string('nama', 100);
                $table->string('email', 100)->nullable();
                $table->text('pesan');
                $table->string('status', 20)->default('Disetujui')->index(); // 'Disetujui', 'Menunggu', 'Ditolak'
                $table->boolean('has_bad_words')->default(false);
                $table->string('bad_words_found')->nullable();
                $table->boolean('is_admin_reply')->default(false);
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->timestamps();

                $table->foreign('konten_id')->references('id')->on('konten')->onDelete('cascade');
                $table->foreign('parent_id')->references('id')->on('komentar_artikel')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('komentar_artikel');
    }
};
