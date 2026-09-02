<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class DekenatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (!Schema::hasTable('dekenat')) {
            $this->command?->warn('Tabel dekenat tidak ditemukan, melewati seeding master dekenat.');
            return;
        }

        $jsonPath = database_path('data/dekenat_master_data.json');
        if (!File::exists($jsonPath)) {
            $this->command?->error("File {$jsonPath} tidak ditemukan!");
            return;
        }

        $raw = File::get($jsonPath);
        $items = json_decode($raw, true);

        if (!is_array($items) || empty($items)) {
            $this->command?->error('Data master dekenat kosong atau format JSON tidak valid.');
            return;
        }

        // Ambil mapping kode_keuskupan -> id_keuskupan
        $keuskupanMap = [];
        if (Schema::hasTable('keuskupan')) {
            $keuskupanMap = DB::table('keuskupan')
                ->whereNotNull('kode_keuskupan')
                ->where('kode_keuskupan', '!=', '')
                ->pluck('id_keuskupan', 'kode_keuskupan')
                ->toArray();
        }

        $totalUpdated = 0;
        $totalInserted = 0;

        foreach ($items as $item) {
            $kode = trim((string) ($item['kode'] ?? ''));
            $nama = trim((string) ($item['nama'] ?? ''));
            $namaVikep = isset($item['nama_vikep']) && !empty($item['nama_vikep']) ? trim((string) $item['nama_vikep']) : null;
            $isAktif = isset($item['is_aktif']) ? (bool) $item['is_aktif'] : true;

            if (empty($nama)) {
                continue;
            }

            // Tentukan keuskupan_id dari prefix kode (contoh '012.02' -> prefix '012')
            $kodePrefix = explode('.', $kode)[0] ?? '';
            $keuskupanId = $keuskupanMap[$kodePrefix] ?? ($item['keuskupan_id'] ?? null);

            // Cari record yang cocok di tabel dekenat
            $existing = null;
            if (!empty($kode)) {
                $existing = DB::table('dekenat')->where('kode_dekenat', $kode)->where('nama_dekenat', $nama)->first();
                if (!$existing) {
                    $existing = DB::table('dekenat')->where('kode_dekenat', $kode)->first();
                }
            }
            if (!$existing && $keuskupanId) {
                $existing = DB::table('dekenat')
                    ->where('keuskupan_id', $keuskupanId)
                    ->where('nama_dekenat', $nama)
                    ->first();
            }
            if (!$existing) {
                $existing = DB::table('dekenat')->where('nama_dekenat', $nama)->first();
            }

            $payload = [
                'kode_dekenat' => $kode,
                'nama_dekenat' => $nama,
                'status' => $isAktif ? 'Aktif' : 'Tidak Aktif',
                'is_deleted' => $isAktif ? 0 : 1,
                'updated_at' => now(),
            ];

            if ($keuskupanId) {
                $payload['keuskupan_id'] = $keuskupanId;
            }

            if ($namaVikep !== null) {
                $payload['deken'] = $namaVikep;
            }

            if ($existing) {
                DB::table('dekenat')
                    ->where('id_dekenat', $existing->id_dekenat)
                    ->update($payload);
                $totalUpdated++;
            } else {
                $payload['created_at'] = now();
                DB::table('dekenat')->insert($payload);
                $totalInserted++;
            }

            // Sinkronisasi opsional ke tabel kevikepan jika ada
            if (Schema::hasTable('kevikepan')) {
                $kevikepanPayload = [
                    'nama_kevikepan' => $nama,
                    'status' => $isAktif ? 'Aktif' : 'Tidak Aktif',
                    'updated_at' => now(),
                ];
                if ($keuskupanId) {
                    $kevikepanPayload['keuskupan_id'] = $keuskupanId;
                }
                if ($namaVikep !== null) {
                    $kevikepanPayload['vikep'] = $namaVikep;
                }

                $existingKevikepan = DB::table('kevikepan')
                    ->where('nama_kevikepan', $nama)
                    ->first();

                if ($existingKevikepan) {
                    DB::table('kevikepan')->where('id', $existingKevikepan->id)->update($kevikepanPayload);
                } else {
                    $kevikepanPayload['created_at'] = now();
                    DB::table('kevikepan')->insert($kevikepanPayload);
                }
            }
        }

        // Bersihkan duplikat lawas tanpa kode_dekenat yang tidak memiliki relasi
        $unassigned = DB::table('dekenat')->whereNull('kode_dekenat')->orWhere('kode_dekenat', '')->get();
        foreach ($unassigned as $u) {
            $hasParoki = Schema::hasTable('paroki') && Schema::hasColumn('paroki', 'dekenat_id') && DB::table('paroki')->where('dekenat_id', $u->id_dekenat)->exists();
            $hasPastor = Schema::hasTable('master_pastor') && Schema::hasColumn('master_pastor', 'dekenat_id') && DB::table('master_pastor')->where('dekenat_id', $u->id_dekenat)->exists();
            if (!$hasParoki && !$hasPastor) {
                DB::table('dekenat')->where('id_dekenat', $u->id_dekenat)->delete();
            }
        }

        $totalNow = DB::table('dekenat')->count();
        $this->command?->info("Sinkronisasi Master Dekenat selesai: {$totalUpdated} diperbarui, {$totalInserted} ditambahkan. Total dekenat di database: {$totalNow}.");
    }
}
