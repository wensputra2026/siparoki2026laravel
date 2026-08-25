<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('sambutan_pastor')) {
            Schema::create('sambutan_pastor', function (Blueprint $table) {
                $table->bigIncrements('id_sambutan');
                $table->string('nama_pastor')->nullable();
                $table->text('sambutan')->nullable();
                $table->string('foto')->nullable();
                $table->string('jabatan')->nullable();
                $table->string('status')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->tinyInteger('is_deleted')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('sambutan_pastor')) {
            Schema::dropIfExists('sambutan_pastor');
        }
    }
};
