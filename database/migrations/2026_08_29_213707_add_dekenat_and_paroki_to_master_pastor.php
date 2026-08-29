<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('master_pastor')) return;

        Schema::table('master_pastor', function (Blueprint $table) {
            if (!Schema::hasColumn('master_pastor', 'dekenat_id')) {
                $table->unsignedBigInteger('dekenat_id')->nullable()->after('keuskupan_id');
            }
            if (!Schema::hasColumn('master_pastor', 'paroki_id')) {
                $table->unsignedBigInteger('paroki_id')->nullable()->after('dekenat_id');
            }
        });

        // Auto-sync paroki_id dan dekenat_id dari paroki_tugas yang sudah ada
        if (Schema::hasColumn('master_pastor', 'paroki_tugas')) {
            $pastors = DB::table('master_pastor')
                ->whereNull('paroki_id')
                ->whereNotNull('paroki_tugas')
                ->where('paroki_tugas', '!=', '')
                ->get(['id', 'paroki_tugas', 'keuskupan_id']);

            foreach ($pastors as $p) {
                $paroki = DB::table('paroki')
                    ->where('nama_paroki', 'like', '%' . trim($p->paroki_tugas) . '%')
                    ->first(['id_paroki', 'dekenat_id', 'keuskupan_id']);
                if ($paroki) {
                    DB::table('master_pastor')->where('id', $p->id)->update([
                        'paroki_id'    => $paroki->id_paroki,
                        'dekenat_id'   => $paroki->dekenat_id ?? null,
                        'keuskupan_id' => $p->keuskupan_id ?: ($paroki->keuskupan_id ?? null),
                    ]);
                }
            }
        }

        \Illuminate\Support\Facades\Cache::forget('schema_columns_master_pastor');
    }

    public function down(): void
    {
        if (!Schema::hasTable('master_pastor')) return;

        Schema::table('master_pastor', function (Blueprint $table) {
            if (Schema::hasColumn('master_pastor', 'paroki_id')) {
                $table->dropColumn('paroki_id');
            }
            if (Schema::hasColumn('master_pastor', 'dekenat_id')) {
                $table->dropColumn('dekenat_id');
            }
        });

        \Illuminate\Support\Facades\Cache::forget('schema_columns_master_pastor');
    }
};
