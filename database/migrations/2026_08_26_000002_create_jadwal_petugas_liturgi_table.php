<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('jadwal_petugas_liturgi')) {
            Schema::create('jadwal_petugas_liturgi', function (Blueprint $table) {
                $table->bigIncrements('id_petugas_liturgi');
                $table->unsignedBigInteger('jadwal_misa_id')->nullable();
                $table->unsignedBigInteger('umat_id')->nullable();
                $table->string('nama_petugas')->nullable();
                $table->enum('jenis_tugas', [
                    'Lektor', 'Pemazmur', 'Misdinar', 'Prodiakon', 'Dirigen',
                    'Koor', 'Tatakrama', 'Kolektan', 'Dekorasi', 'Dokumentasi', 'Lainnya',
                ])->nullable();
                $table->string('kelompok')->nullable();
                $table->text('keterangan')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('jadwal_petugas_liturgi')) {
            Schema::dropIfExists('jadwal_petugas_liturgi');
        }
    }
};
