<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('jadwal_petugas_liturgi')) {
            if (Schema::hasColumn('jadwal_petugas_liturgi', 'jadwal_misa_id')) {
                DB::statement('ALTER TABLE `jadwal_petugas_liturgi` MODIFY `jadwal_misa_id` BIGINT UNSIGNED NULL');
            }
            if (Schema::hasColumn('jadwal_petugas_liturgi', 'umat_id')) {
                DB::statement('ALTER TABLE `jadwal_petugas_liturgi` MODIFY `umat_id` BIGINT UNSIGNED NULL');
            }
        }
    }

    public function down()
    {
        if (Schema::hasTable('jadwal_petugas_liturgi') && Schema::hasColumn('jadwal_petugas_liturgi', 'jadwal_misa_id')) {
            DB::statement('ALTER TABLE `jadwal_petugas_liturgi` MODIFY `jadwal_misa_id` BIGINT UNSIGNED NOT NULL');
        }
    }
};
