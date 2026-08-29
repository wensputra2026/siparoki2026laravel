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
        Schema::dropIfExists('chat_pesan');

        Schema::create('chat_pesan', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pengirim_id')->index();
            $table->unsignedBigInteger('penerima_id')->index();
            $table->text('pesan');
            $table->string('lampiran', 500)->nullable();
            $table->boolean('is_read')->default(false)->index();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_pesan');
    }
};
