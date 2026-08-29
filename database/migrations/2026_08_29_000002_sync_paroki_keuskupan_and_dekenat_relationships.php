<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Sinkronisasi data keuskupan_id pada paroki dengan keuskupan_id pada dekenat naungannya.
     */
    public function up(): void
    {
        if (Schema::hasTable('paroki') && Schema::hasTable('dekenat')) {
            DB::table('paroki')
                ->join('dekenat', 'paroki.dekenat_id', '=', 'dekenat.id_dekenat')
                ->whereColumn('paroki.keuskupan_id', '!=', 'dekenat.keuskupan_id')
                ->update(['paroki.keuskupan_id' => DB::raw('dekenat.keuskupan_id')]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Data integrity alignment, no destructive reversal needed.
    }
};
