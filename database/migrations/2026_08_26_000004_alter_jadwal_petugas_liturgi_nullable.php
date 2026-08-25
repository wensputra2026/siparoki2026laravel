<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // The live table was created with jadwal_misa_id NOT NULL, which blocks
        // standalone "Jadwal Petugas Liturgi" entries. Make it nullable so a
        // minister can be recorded without being attached to a specific Misa.
        if (Schema::hasColumn('jadwal_petugas_liturgi', 'jadwal_misa_id')) {
            DB::statement('ALTER TABLE `jadwal_petugas_liturgi` MODIFY `jadwal_misa_id` BIGINT UNSIGNED NULL');
        }
        if (Schema::hasColumn('jadwal_petugas_liturgi', 'umat_id')) {
            DB::statement('ALTER TABLE `jadwal_petugas_liturgi` MODIFY `umat_id` BIGINT UNSIGNED NULL');
        }
    }

    public function down()
    {
        if (Schema::hasColumn('jadwal_petugas_liturgi', 'jadwal_misa_id')) {
            DB::statement('ALTER TABLE `jadwal_petugas_liturgi` MODIFY `jadwal_misa_id` BIGINT UNSIGNED NOT NULL');
        }
    }
};
