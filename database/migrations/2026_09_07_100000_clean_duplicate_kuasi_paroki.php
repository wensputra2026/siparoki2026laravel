<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Hapus data duplikat di tabel kuasi_paroki.
     */
    public function up(): void
    {
        // 1. Hapus duplikat nama-persis: simpan yang punya paroki_id atau ID terkecil
        $duplicateNames = DB::table('kuasi_paroki')
            ->selectRaw('NamaKuasiParoki, COUNT(*) as jumlah')
            ->groupBy('NamaKuasiParoki')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('NamaKuasiParoki');

        foreach ($duplicateNames as $nama) {
            $records = DB::table('kuasi_paroki')
                ->where('NamaKuasiParoki', $nama)
                ->orderByRaw('CASE WHEN paroki_id IS NOT NULL THEN 0 ELSE 1 END ASC')
                ->orderBy('id', 'asc')
                ->get();

            if ($records->count() > 1) {
                $keepId = $records->first()->id;
                $deleteIds = $records->pluck('id')->filter(fn($id) => $id !== $keepId)->values()->toArray();
                if (!empty($deleteIds)) {
                    DB::table('kuasi_paroki')->whereIn('id', $deleteIds)->delete();
                }
            }
        }

        // 2. Hapus yang namanya diakhiri "()" atau "(Kuasi Paroki)"
        $dupSuffix = DB::table('kuasi_paroki')
            ->where(function ($q) {
                $q->where('NamaKuasiParoki', 'REGEXP', '\\(\\)$')
                  ->orWhere('NamaKuasiParoki', 'LIKE', '%(Kuasi Paroki)');
            })
            ->get();

        foreach ($dupSuffix as $dup) {
            $cleanName = trim(preg_replace('/\s*\((?:Kuasi Paroki)?\)\s*$/', '', $dup->NamaKuasiParoki));
            $hasSibling = DB::table('kuasi_paroki')
                ->where('NamaKuasiParoki', $cleanName)
                ->where('id', '!=', $dup->id)
                ->exists();

            if ($hasSibling) {
                DB::table('kuasi_paroki')->where('id', $dup->id)->delete();
            }
        }

        // 3. Fallback: hapus sisa duplikat nama-persis jika masih ada
        $stillDup = DB::table('kuasi_paroki')
            ->selectRaw('NamaKuasiParoki, MIN(id) as keep_id, COUNT(*) as jumlah')
            ->groupBy('NamaKuasiParoki')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($stillDup as $dup) {
            DB::table('kuasi_paroki')
                ->where('NamaKuasiParoki', $dup->NamaKuasiParoki)
                ->where('id', '!=', $dup->keep_id)
                ->delete();
        }
    }

    public function down(): void
    {
        // Tidak bisa di-rollback karena data yang dihapus tidak tersimpan
    }
};
