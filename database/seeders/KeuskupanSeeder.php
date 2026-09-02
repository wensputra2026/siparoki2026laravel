<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class KeuskupanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!Schema::hasTable('keuskupan')) {
            $this->command?->warn('Tabel keuskupan tidak ditemukan, melewati seeding master keuskupan.');
            return;
        }

        $jsonPath = database_path('data/keuskupan_master_data.json');
        if (!File::exists($jsonPath)) {
            $this->command?->error("File {$jsonPath} tidak ditemukan!");
            return;
        }

        $raw = File::get($jsonPath);
        $items = json_decode($raw, true);

        if (!is_array($items) || empty($items)) {
            $this->command?->error('Data master keuskupan kosong atau format JSON tidak valid.');
            return;
        }

        $totalUpdated = 0;
        $totalInserted = 0;

        foreach ($items as $item) {
            $kode = trim((string) ($item['kode'] ?? ''));
            $nama = trim((string) ($item['nama'] ?? ''));
            $namaLatin = isset($item['nama_latin']) ? trim((string) $item['nama_latin']) : null;
            $namaUskup = isset($item['nama_uskup']) ? trim((string) $item['nama_uskup']) : null;
            $kontak = isset($item['kontak']) && !empty($item['kontak']) ? trim((string) $item['kontak']) : null;
            $isAktif = isset($item['is_aktif']) ? (bool) $item['is_aktif'] : true;

            if (empty($nama)) {
                continue;
            }

            // Normalisasi nama keuskupan jika variasi nama (misal Keuskupan Semarang -> Keuskupan Agung Semarang)
            if ($kode === '004' && $nama === 'Keuskupan Semarang') {
                $nama = 'Keuskupan Agung Semarang';
                $namaLatin = 'Archidioecesis Semarangensis';
            }

            // Cari record keuskupan yang sesuai berdasarkan kode_keuskupan atau nama_keuskupan
            $existing = null;
            if (!empty($kode)) {
                $existing = DB::table('keuskupan')->where('kode_keuskupan', $kode)->orderBy('id_keuskupan')->first();
            }
            if (!$existing) {
                $existing = DB::table('keuskupan')->where('nama_keuskupan', $nama)->first();
            }

            $data = [
                'kode_keuskupan' => $kode,
                'nama_keuskupan' => $nama,
                'nama_latin' => $namaLatin,
                'nama_uskup' => $namaUskup,
                'status' => $isAktif ? 'Aktif' : 'Tidak Aktif',
                'is_deleted' => $isAktif ? 0 : 1,
                'updated_at' => now(),
            ];

            if ($kontak !== null) {
                $data['telepon'] = $kontak;
            }

            if ($existing) {
                DB::table('keuskupan')
                    ->where('id_keuskupan', $existing->id_keuskupan)
                    ->update($data);
                $totalUpdated++;
            } else {
                $data['created_at'] = now();
                DB::table('keuskupan')->insert($data);
                $totalInserted++;
            }
        }

        // Hapus duplikat tanpa relasi yang memiliki kode_keuskupan kembar
        $duplicateKodes = DB::table('keuskupan')
            ->select('kode_keuskupan')
            ->groupBy('kode_keuskupan')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('kode_keuskupan');

        foreach ($duplicateKodes as $dupKode) {
            $records = DB::table('keuskupan')->where('kode_keuskupan', $dupKode)->orderBy('id_keuskupan')->get();
            // Pertahankan record pertama yang mungkin memiliki relasi atau ID terkecil
            $first = $records->first();
            foreach ($records->slice(1) as $dup) {
                $hasParoki = Schema::hasTable('paroki') && DB::table('paroki')->where('keuskupan_id', $dup->id_keuskupan)->exists();
                $hasDekenat = Schema::hasTable('dekenat') && DB::table('dekenat')->where('keuskupan_id', $dup->id_keuskupan)->exists();
                if (!$hasParoki && !$hasDekenat) {
                    DB::table('keuskupan')->where('id_keuskupan', $dup->id_keuskupan)->delete();
                }
            }
        }

        // Hapus dummy / duplikat kosong jika ada (misal kode KEUSKUPAN_AGATS dummy tanpa relasi)
        $dummy = DB::table('keuskupan')->where('kode_keuskupan', 'KEUSKUPAN_AGATS')->first();
        if ($dummy) {
            $hasParoki = Schema::hasTable('paroki') && DB::table('paroki')->where('keuskupan_id', $dummy->id_keuskupan)->exists();
            $hasDekenat = Schema::hasTable('dekenat') && DB::table('dekenat')->where('keuskupan_id', $dummy->id_keuskupan)->exists();
            if (!$hasParoki && !$hasDekenat) {
                DB::table('keuskupan')->where('id_keuskupan', $dummy->id_keuskupan)->delete();
            }
        }

        $totalNow = DB::table('keuskupan')->count();
        $this->command?->info("Sinkronisasi Master Keuskupan selesai: {$totalUpdated} diproses. Total keuskupan aktif di database: {$totalNow}.");
    }
}
