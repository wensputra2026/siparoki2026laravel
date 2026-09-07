<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Reset nama_pastor_paroki_aktif on other parishes that were inadvertently mass-assigned
     * RD. Herman Hilers Penga (who is only the Pastor Paroki of Benlutu).
     */
    public function up(): void
    {
        if (!Schema::hasTable('paroki')) {
            return;
        }

        $cols = Schema::getColumnListing('paroki');
        $hasNamaPastor = in_array('nama_pastor_paroki_aktif', $cols, true);
        $hasPastorParoki = in_array('pastor_paroki', $cols, true);

        // 1. Reset pastor paroki to NULL for all parokis EXCEPT Paroki Benlutu
        // (id_paroki != 380 and nama_paroki not like '%Benlutu%')
        if ($hasNamaPastor || $hasPastorParoki) {
            $updateReset = [];
            if ($hasNamaPastor) {
                $updateReset['nama_pastor_paroki_aktif'] = null;
            }
            if ($hasPastorParoki) {
                $updateReset['pastor_paroki'] = null;
            }

            DB::table('paroki')
                ->where('id_paroki', '!=', 380)
                ->where('nama_paroki', 'not like', '%Benlutu%')
                ->where(function ($q) use ($hasNamaPastor, $hasPastorParoki) {
                    if ($hasNamaPastor) {
                        $q->where('nama_pastor_paroki_aktif', 'like', '%Herman Hilers%');
                    }
                    if ($hasPastorParoki) {
                        $q->orWhere('pastor_paroki', 'like', '%Herman Hilers%');
                    }
                })
                ->update($updateReset);
        }

        // 2. Ensure Paroki St. Vinsensius a Paulo - Benlutu correctly retains RD. Herman Hilers Penga
        $benlutuUpdate = [];
        if ($hasNamaPastor) {
            $benlutuUpdate['nama_pastor_paroki_aktif'] = 'RD. Herman Hilers Penga';
        }
        if ($hasPastorParoki) {
            $benlutuUpdate['pastor_paroki'] = 'RD. Herman Hilers Penga';
        }

        if (!empty($benlutuUpdate)) {
            DB::table('paroki')
                ->where('id_paroki', 380)
                ->orWhere('nama_paroki', 'like', '%Benlutu%')
                ->update($benlutuUpdate);
        }

        // 3. Clear relevant caches
        try {
            \Illuminate\Support\Facades\Cache::forget('active_paroki_middleware_v3');
            \Illuminate\Support\Facades\Cache::forget('active_paroki_model_v3');
            \Illuminate\Support\Facades\Cache::forget('ref_paroki_list_v2');
            \Illuminate\Support\Facades\Cache::forget('ref_paroki_with_relations');
            \Illuminate\Support\Facades\Cache::increment('global_view_data_version');
        } catch (\Throwable $e) {
            // Ignore cache errors
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
