<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class ParokiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!Schema::hasTable('paroki')) {
            $this->command?->warn('Tabel paroki tidak ditemukan, melewati seeding master paroki.');
            return;
        }

        $jsonPath = database_path('data/paroki_master_data.json');
        if (!File::exists($jsonPath)) {
            $this->command?->error("File {$jsonPath} tidak ditemukan!");
            return;
        }

        $items = json_decode(File::get($jsonPath), true);
        if (!is_array($items) || empty($items)) {
            $this->command?->error('Data master paroki kosong atau tidak valid.');
            return;
        }

        $validCols = Schema::getColumnListing('paroki');
        $chunks = array_chunk($items, 100);

        foreach ($chunks as $chunk) {
            foreach ($chunk as $row) {
                $id = $row['id_paroki'] ?? null;
                $filtered = array_intersect_key($row, array_flip($validCols));
                
                // Fallback default logo if empty
                if (empty($filtered['logo'])) {
                    $filtered['logo'] = '/images/logo-paroki.png';
                }

                if ($id) {
                    $exists = DB::table('paroki')->where('id_paroki', $id)->exists();
                    if ($exists) {
                        DB::table('paroki')->where('id_paroki', $id)->update($filtered);
                    } else {
                        DB::table('paroki')->insert($filtered);
                    }
                } else {
                    DB::table('paroki')->insert($filtered);
                }
            }
        }

        $this->command?->info('Master Paroki (' . count($items) . ' data) berhasil disinkronkan.');
    }
}
