<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class MasterPastorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!Schema::hasTable('master_pastor')) {
            $this->command?->warn('Tabel master_pastor tidak ditemukan, melewati seeding master pastor.');
            return;
        }

        $jsonPath = database_path('data/master_pastor_data.json');
        if (!File::exists($jsonPath)) {
            $this->command?->error("File {$jsonPath} tidak ditemukan!");
            return;
        }

        $items = json_decode(File::get($jsonPath), true);
        if (!is_array($items) || empty($items)) {
            $this->command?->error('Data master pastor kosong atau tidak valid.');
            return;
        }

        $validCols = Schema::getColumnListing('master_pastor');

        foreach ($items as $row) {
            $id = $row['id'] ?? null;
            $filtered = array_intersect_key($row, array_flip($validCols));

            // Pastikan foto pastor tidak null / memiliki fallback gambar imam default yang valid
            if (empty($filtered['foto'])) {
                $filtered['foto'] = '/images/default-pastor.jpg';
            }

            if ($id) {
                $exists = DB::table('master_pastor')->where('id', $id)->exists();
                if ($exists) {
                    DB::table('master_pastor')->where('id', $id)->update($filtered);
                } else {
                    DB::table('master_pastor')->insert($filtered);
                }
            } else {
                DB::table('master_pastor')->insert($filtered);
            }
        }

        // Juga pastikan foto di master_pastor yang masih kosong diupdate
        DB::table('master_pastor')
            ->whereNull('foto')
            ->orWhere('foto', '')
            ->update(['foto' => '/images/default-pastor.jpg']);

        $this->command?->info('Master Pastor (' . count($items) . ' data & foto) berhasil disinkronkan.');
    }
}
