<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update profil_paroki
        if (Schema::hasTable('profil_paroki')) {
            DB::table('profil_paroki')->update([
                'pastor_paroki' => 'RD. Herman Hilers Penga',
            ]);
        }

        // 2. Update paroki (Hanya untuk Paroki Benlutu)
        if (Schema::hasTable('paroki')) {
            $updateData = [];
            if (Schema::hasColumn('paroki', 'nama_pastor_paroki_aktif')) {
                $updateData['nama_pastor_paroki_aktif'] = 'RD. Herman Hilers Penga';
            }
            if (Schema::hasColumn('paroki', 'pastor_paroki')) {
                $updateData['pastor_paroki'] = 'RD. Herman Hilers Penga';
            }
            if (!empty($updateData)) {
                DB::table('paroki')
                    ->where('nama_paroki', 'like', '%Benlutu%')
                    ->orWhere('id_paroki', 380)
                    ->update($updateData);
            }
        }

        // 3. Update master_pastor
        if (Schema::hasTable('master_pastor')) {
            // Find Herman Hilers Penga or create/update
            $herman = DB::table('master_pastor')
                ->where('nama_pastor', 'like', '%Herman%')
                ->first();

            if ($herman) {
                DB::table('master_pastor')
                    ->where('id', $herman->id)
                    ->update([
                        'nama_pastor' => 'Herman Hilers Penga',
                        'gelar_depan' => 'RD.',
                        'jabatan' => 'Pastor Paroki',
                        'jenis_imam' => 'Diosesan / Projo',
                        'status' => 'Aktif Melayani',
                        'urutan' => 1,
                    ]);
            } else {
                DB::table('master_pastor')->insert([
                    'nama_pastor' => 'Herman Hilers Penga',
                    'gelar_depan' => 'RD.',
                    'jabatan' => 'Pastor Paroki',
                    'jenis_imam' => 'Diosesan / Projo',
                    'keuskupan' => 'Keuskupan Agung Kupang',
                    'status' => 'Aktif Melayani',
                    'urutan' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // If Sintus Kiik exists and is set as Pastor Paroki, change jabatan to Pastor Rekan / Vikaris
            DB::table('master_pastor')
                ->where('nama_pastor', 'like', '%Sintus%')
                ->update([
                    'jabatan' => 'Pastor Rekan',
                    'urutan' => 2,
                ]);
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
