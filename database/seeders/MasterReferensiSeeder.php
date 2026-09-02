<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class MasterReferensiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $refTables = [
            'master_referensi' => 'id',
            'master_referensi_item' => 'id',
            'master_ordo' => 'id',
            'master_uskup' => 'id',
            'master_coa' => 'id',
            'master_gereja_protestan' => 'id',
            'ref_hub_keluarga' => 'id',
            'kategori_konten' => 'id',
            'kategori_keuangan' => 'id',
            'kategori_aset' => 'id',
            'peran_kategorial' => 'id',
            'frontend_menus' => 'id',
        ];

        foreach ($refTables as $tbl => $pk) {
            if (!Schema::hasTable($tbl)) {
                continue;
            }

            $jsonPath = database_path("data/{$tbl}.json");
            if (!File::exists($jsonPath)) {
                continue;
            }

            $items = json_decode(File::get($jsonPath), true);
            if (!is_array($items) || empty($items)) {
                continue;
            }

            $validCols = Schema::getColumnListing($tbl);
            $pkCol = in_array($pk, $validCols, true) ? $pk : ($validCols[0] ?? 'id');

            foreach ($items as $row) {
                $id = $row[$pkCol] ?? null;
                $filtered = array_intersect_key($row, array_flip($validCols));

                if ($id) {
                    $exists = DB::table($tbl)->where($pkCol, $id)->exists();
                    if ($exists) {
                        DB::table($tbl)->where($pkCol, $id)->update($filtered);
                    } else {
                        DB::table($tbl)->insert($filtered);
                    }
                } else {
                    DB::table($tbl)->insert($filtered);
                }
            }

            $this->command?->info("Tabel {$tbl} (" . count($items) . " data) berhasil disinkronkan.");
        }
    }
}
