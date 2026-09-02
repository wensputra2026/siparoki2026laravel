<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class KuasiParokiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!Schema::hasTable('kuasi_paroki')) {
            $this->command?->warn('Tabel kuasi_paroki tidak ditemukan, melewati seeding kuasi paroki.');
            return;
        }

        $jsonPath = database_path('data/kuasi_paroki_master_data.json');
        if (!File::exists($jsonPath)) {
            $this->command?->error("File {$jsonPath} tidak ditemukan!");
            return;
        }

        $items = json_decode(File::get($jsonPath), true);
        if (!is_array($items) || empty($items)) {
            $this->command?->error('Data master kuasi paroki kosong atau tidak valid.');
            return;
        }

        $validCols = Schema::getColumnListing('kuasi_paroki');

        foreach ($items as $row) {
            $id = $row['id'] ?? null;
            $filtered = array_intersect_key($row, array_flip($validCols));

            if ($id) {
                $exists = DB::table('kuasi_paroki')->where('id', $id)->exists();
                if ($exists) {
                    DB::table('kuasi_paroki')->where('id', $id)->update($filtered);
                } else {
                    DB::table('kuasi_paroki')->insert($filtered);
                }
            } else {
                DB::table('kuasi_paroki')->insert($filtered);
            }
        }

        $this->command?->info('Master Kuasi Paroki (' . count($items) . ' data) berhasil disinkronkan.');
    }
}
