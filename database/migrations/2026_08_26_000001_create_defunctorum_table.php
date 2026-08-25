<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('defunctorum')) {
            Schema::create('defunctorum', function (Blueprint $table) {
                $table->bigIncrements('id_defunctorum');
                $table->unsignedBigInteger('umat_id')->nullable();
                $table->unsignedBigInteger('kk_id')->nullable();
                $table->unsignedBigInteger('wilayah_id')->nullable();
                $table->unsignedBigInteger('kapela_id')->nullable();
                $table->unsignedBigInteger('kub_id')->nullable();
                $table->string('nama_lengkap')->nullable();
                $table->string('nama_baptis')->nullable();
                $table->string('jenis_kelamin', 1)->nullable();
                $table->string('tempat_lahir')->nullable();
                $table->date('tanggal_lahir')->nullable();
                $table->date('tanggal_meninggal')->nullable();
                $table->string('tempat_meninggal')->nullable();
                $table->text('sakramen_diterima')->nullable();
                $table->string('status')->nullable();
                $table->text('keterangan')->nullable();
                $table->string('foto')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->tinyInteger('is_deleted')->default(0);
                $table->timestamps();

                $table->index(['wilayah_id', 'kapela_id', 'kub_id', 'is_deleted']);
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('defunctorum')) {
            Schema::dropIfExists('defunctorum');
        }
    }
};
