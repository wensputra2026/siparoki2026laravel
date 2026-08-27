<?php

namespace App\Http\Controllers\Concerns;

use App\Models\DesaKelurahan;
use App\Models\Dekenat;
use App\Models\Kabupaten;
use App\Models\Kapela;
use App\Models\Kecamatan;
use App\Models\Keuskupan;
use App\Models\Kevikepan;
use App\Models\KkKatolik;
use App\Models\Kub;
use App\Models\Lingkungan;
use App\Models\Paroki;
use App\Models\Provinsi;
use App\Models\Sakramen;
use App\Models\Umat;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

trait ParokiModuleTrait
{
    protected function normalizeKuasiParokiPayload(array $data, $kuasi = null): array
    {
        $name = $data['nama_kuasi'] ?? $data['NamaKuasiParoki'] ?? $kuasi?->nama_kuasi ?? null;
        $code = $data['kode_kuasi'] ?? $data['KodeKuasiParoki'] ?? $kuasi?->kode_kuasi ?? null;
        $address = $data['lokasi'] ?? $data['AlamatKuasiParoki'] ?? $kuasi?->lokasi ?? null;
        $pastor = $this->resolvePastorDisplayName($data['pastor_administrator'] ?? $data['PastorKuasiParoki'] ?? $kuasi?->pastor_administrator ?? null);
        $status = $data['status'] ?? $kuasi?->status ?? 'Aktif';

        if ($name !== null) {
            $data['NamaKuasiParoki'] = $name;
        }
        if ($code !== null) {
            $data['KodeKuasiParoki'] = $code;
        }
        if ($address !== null) {
            $data['AlamatKuasiParoki'] = $address;
        }
        if ($pastor !== null) {
            $data['PastorKuasiParoki'] = $pastor;
        }
        if (isset($data['keterangan'])) {
            $data['Keterangan'] = $data['keterangan'];
        }

        $data['StatusAktif'] = $this->shouldPromoteKuasiParoki($data) || in_array($status, ['Nonaktif', 'N', 0, '0'], true) ? 'N' : 'Y';

        return $data;
    }


    protected function shouldPromoteKuasiParoki(array $data): bool
    {
        $status = strtolower((string) ($data['status'] ?? ''));

        return !empty($data['promote_to_paroki'])
            || str_contains($status, 'paroki')
            || str_contains($status, 'definitif');
    }


    protected function promoteKuasiParokiToParoki(\App\Models\KuasiParoki $kuasi, array $data): Paroki
    {
        $kuasi->loadMissing('paroki.dekenat');

        $rawName = trim((string) ($data['NamaKuasiParoki'] ?? $data['nama_kuasi'] ?? $kuasi->nama_kuasi ?? 'Paroki Baru'));
        $cleanName = trim(preg_replace('/^Kuasi\s+Paroki\s+/i', '', $rawName));
        $newParokiName = Str::startsWith(strtolower($cleanName), 'paroki ')
            ? $cleanName
            : 'Paroki ' . $cleanName;

        $oldCode = $data['KodeKuasiParoki'] ?? $data['kode_kuasi'] ?? $kuasi->kode_kuasi;
        $newCode = trim((string) ($data['kode_paroki_baru'] ?? '')) ?: $this->generateParokiCode($oldCode);
        $skNumber = trim((string) ($data['no_sk_elevasi'] ?? ''));
        $promotedAt = now();
        $actor = auth()->user()?->name ?? auth()->user()?->nama_lengkap ?? auth()->user()?->email ?? 'Sistem';
        $parentParoki = $kuasi->paroki;
        $parentParokiLabel = $parentParoki
            ? trim($parentParoki->nama_paroki . ' (ID: ' . $parentParoki->getKey() . ', Kode: ' . ($parentParoki->kode_paroki ?: '-') . ')')
            : '-';
        $dekenatId = $data['dekenat_id'] ?? $kuasi->dekenat_id ?? $parentParoki?->dekenat_id;
        $keuskupanId = $data['keuskupan_id'] ?? $kuasi->keuskupan_id ?? $parentParoki?->keuskupan_id;
        $address = $data['AlamatKuasiParoki'] ?? $data['lokasi'] ?? $kuasi->lokasi;
        $pastor = $this->resolvePastorDisplayName($data['pastor_administrator'] ?? $data['PastorKuasiParoki'] ?? $kuasi->pastor_administrator);

        $historyBlock = implode("\n", array_filter([
            '[ELEVASI-KUASI-PAROKI] ' . $promotedAt->format('Y-m-d H:i:s') . ' WITA',
            'Status: Kuasi Paroki dinaikkan menjadi Paroki definitif.',
            'Nama kuasi asal: ' . $rawName,
            'Kode kuasi asal: ' . ($oldCode ?: '-'),
            'Nama paroki definitif: ' . $newParokiName,
            'Kode paroki definitif: ' . $newCode,
            'Paroki induk saat masih kuasi: ' . $parentParokiLabel,
            'Nomor SK/Dekret: ' . ($skNumber ?: '-'),
            'Pastor administrator terakhir: ' . ($pastor ?: '-'),
            'Diproses oleh: ' . $actor,
        ]));

        $existingParoki = Paroki::where('kode_paroki', $newCode)
            ->orWhere('nama_paroki', $newParokiName)
            ->first();

        $parokiPayload = [
            'keuskupan_id' => $keuskupanId,
            'dekenat_id' => $dekenatId,
            'kode_paroki' => $newCode,
            'nama_paroki' => $newParokiName,
            'pelindung_paroki' => $data['pelindung'] ?? $kuasi->pelindung ?? '',
            'status_paroki' => 'Mandiri',
            'status' => 'Aktif',
            'nama_pastor_paroki_aktif' => $pastor ?: '',
            'alamat' => $address ?: '',
            'provinsi_id' => $data['provinsi_id'] ?? $kuasi->provinsi_id,
            'kabupaten_id' => $data['kabupaten_id'] ?? $kuasi->kabupaten_id,
            'kecamatan_id' => $data['kecamatan_id'] ?? $kuasi->kecamatan_id,
            'desa_id' => $data['desa_id'] ?? $kuasi->desa_id,
            'telepon' => $data['Telepon'] ?? $kuasi->Telepon ?? null,
            'email' => $data['Email'] ?? $kuasi->Email ?? null,
            'website' => $data['Website'] ?? $kuasi->Website ?? null,
            'latitude' => $data['Latitude'] ?? $kuasi->Latitude ?? null,
            'longitude' => $data['Longitude'] ?? $kuasi->Longitude ?? null,
            'keterangan' => trim(($existingParoki?->keterangan ? $existingParoki->keterangan . "\n\n" : '') . $historyBlock),
        ];

        if ($existingParoki) {
            $existingParoki->update($parokiPayload);
            $paroki = $existingParoki;
        } else {
            $paroki = Paroki::create($parokiPayload);
        }

        $kuasiHistory = trim(($kuasi->Keterangan ? $kuasi->Keterangan . "\n\n" : '') . $historyBlock . "\nParoki definitif ID: " . $paroki->getKey());
        $kuasi->forceFill([
            'StatusAktif' => 'N',
            'Keterangan' => $kuasiHistory,
            'UpdatedAt' => now(),
            'UpdatedBy' => auth()->id(),
        ])->save();

        return $paroki;
    }


    protected function generateParokiCode(?string $oldCode): string
    {
        $next = ((int) Paroki::max('id_paroki')) + 1;
        do {
            $code = 'PAR-' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
            $next++;
        } while (Paroki::where('kode_paroki', $code)->exists());

        return $code;
    }


    protected function syncParokiToGlobalSettings($paroki): void
    {
        if (!$paroki) return;

        // Sync to profil_paroki if table exists
        if (\Illuminate\Support\Facades\Schema::hasTable('profil_paroki')) {
            $cols = \Illuminate\Support\Facades\Schema::getColumnListing('profil_paroki');
            $payload = [];
            $map = [
                'nama_paroki' => $paroki->nama_paroki,
                'alamat' => $paroki->alamat,
                'alamat_paroki' => $paroki->alamat,
                'telepon' => $paroki->telepon ?? $paroki->whatsapp,
                'telepon_paroki' => $paroki->telepon ?? $paroki->whatsapp,
                'email' => $paroki->email,
                'email_paroki' => $paroki->email,
                'logo' => $paroki->logo,
                'pastor_paroki' => $paroki->nama_pastor_paroki_aktif,
                'pelindung' => $paroki->pelindung_paroki,
            ];
            foreach ($map as $k => $v) {
                if (in_array($k, $cols, true) && $v !== null) {
                    $payload[$k] = $v;
                }
            }
            if (in_array('updated_at', $cols, true)) {
                $payload['updated_at'] = now();
            }
            if (!empty($payload)) {
                $firstRow = \Illuminate\Support\Facades\DB::table('profil_paroki')->first();
                if ($firstRow) {
                    $pkCol = in_array('id', $cols, true) ? 'id' : (in_array('id_profil', $cols, true) ? 'id_profil' : $cols[0]);
                    \Illuminate\Support\Facades\DB::table('profil_paroki')->where($pkCol, $firstRow->$pkCol)->update($payload);
                } else {
                    \Illuminate\Support\Facades\DB::table('profil_paroki')->insert($payload);
                }
            }
        }

        // Sync to pengaturan_aplikasi if table exists
        if (\Illuminate\Support\Facades\Schema::hasTable('pengaturan_aplikasi')) {
            $cols = \Illuminate\Support\Facades\Schema::getColumnListing('pengaturan_aplikasi');
            $payload = [];
            $map = [
                'nama_paroki' => $paroki->nama_paroki,
                'alamat' => $paroki->alamat,
                'alamat_paroki' => $paroki->alamat,
                'telepon' => $paroki->telepon ?? $paroki->whatsapp,
                'telepon_paroki' => $paroki->telepon ?? $paroki->whatsapp,
                'email' => $paroki->email,
                'email_paroki' => $paroki->email,
                'logo' => $paroki->logo,
            ];
            foreach ($map as $k => $v) {
                if (in_array($k, $cols, true) && $v !== null) {
                    $payload[$k] = $v;
                }
            }
            if (in_array('updated_at', $cols, true)) {
                $payload['updated_at'] = now();
            }
            if (!empty($payload)) {
                $firstRow = \Illuminate\Support\Facades\DB::table('pengaturan_aplikasi')->first();
                if ($firstRow) {
                    $pkCol = in_array('id', $cols, true) ? 'id' : (in_array('id_pengaturan', $cols, true) ? 'id_pengaturan' : $cols[0]);
                    \Illuminate\Support\Facades\DB::table('pengaturan_aplikasi')->where($pkCol, $firstRow->$pkCol)->update($payload);
                } else {
                    \Illuminate\Support\Facades\DB::table('pengaturan_aplikasi')->insert($payload);
                }
            }
        }
    }


    protected function ensureRiwayatPastorParokiTableAndData(): void
    {
        try {
            $checkTable = \Illuminate\Support\Facades\DB::select("SHOW TABLES LIKE 'riwayat_pastor_paroki'");
            $needsSync = empty($checkTable);

            if (!$needsSync) {
                $count = \Illuminate\Support\Facades\DB::table('riwayat_pastor_paroki')->count();
                $hasDamasus = \Illuminate\Support\Facades\DB::table('riwayat_pastor_paroki')->where('nama_pastor', 'like', '%Damasus%')->exists();
                if ($count < 5 || !$hasDamasus) {
                    $needsSync = true;
                }
            }

            if ($needsSync) {
                try {
                    \Illuminate\Support\Facades\DB::statement("DROP TABLE IF EXISTS riwayat_pastor_paroki");
                    \Illuminate\Support\Facades\DB::statement("CREATE TABLE riwayat_pastor_paroki LIKE parokibenlutuci31.riwayat_pastor_paroki");
                    \Illuminate\Support\Facades\DB::statement("INSERT INTO riwayat_pastor_paroki SELECT * FROM parokibenlutuci31.riwayat_pastor_paroki");
                } catch (\Throwable $ex) {
                    \Illuminate\Support\Facades\Schema::create('riwayat_pastor_paroki', function ($table) {
                        $table->increments('id');
                        $table->unsignedInteger('paroki_id')->nullable();
                        $table->string('nama_pastor', 150);
                        $table->string('gelar', 50)->nullable();
                        $table->string('jabatan', 100)->nullable();
                        $table->string('periode_mulai', 50)->nullable();
                        $table->string('periode_selesai', 50)->nullable();
                        $table->string('tahun_mulai', 10)->nullable();
                        $table->string('tahun_selesai', 10)->nullable();
                        $table->string('foto', 255)->nullable();
                        $table->string('status', 50)->default('Aktif');
                        $table->string('status_pelayanan', 50)->nullable();
                        $table->integer('urutan')->default(1);
                        $table->text('keterangan')->nullable();
                        $table->text('karya_pelayanan')->nullable();
                    });

                    \Illuminate\Support\Facades\DB::table('riwayat_pastor_paroki')->insert([
                        ['nama_pastor' => 'P. Damasus Sumardi', 'jabatan' => 'Pastor Paroki', 'periode_mulai' => '2002', 'periode_selesai' => '2007', 'tahun_mulai' => '2002', 'tahun_selesai' => '2007', 'status' => 'Purna Tugas', 'status_pelayanan' => 'Purna Tugas', 'urutan' => 1, 'foto' => 'uploads/pastor/damasus.jpg'],
                        ['nama_pastor' => 'P. Siprianus Asa', 'jabatan' => 'Pastor Paroki', 'periode_mulai' => '2007', 'periode_selesai' => '2010', 'tahun_mulai' => '2007', 'tahun_selesai' => '2010', 'status' => 'Purna Tugas', 'status_pelayanan' => 'Purna Tugas', 'urutan' => 2, 'foto' => null],
                        ['nama_pastor' => 'P. Walburga Poca', 'jabatan' => 'Pastor Paroki', 'periode_mulai' => '2010', 'periode_selesai' => '2012', 'tahun_mulai' => '2010', 'tahun_selesai' => '2012', 'status' => 'Purna Tugas', 'status_pelayanan' => 'Purna Tugas', 'urutan' => 3, 'foto' => null],
                        ['nama_pastor' => 'P. Damianus Lamak Tasaeb', 'jabatan' => 'Pastor Paroki', 'periode_mulai' => '2012', 'periode_selesai' => '2016', 'tahun_mulai' => '2012', 'tahun_selesai' => '2016', 'status' => 'Purna Tugas', 'status_pelayanan' => 'Purna Tugas', 'urutan' => 4, 'foto' => null],
                        ['nama_pastor' => 'RD. Herman Hillers Penga', 'jabatan' => 'Pastor Paroki', 'periode_mulai' => '2026', 'periode_selesai' => 'Sekarang', 'tahun_mulai' => '2026', 'tahun_selesai' => 'Sekarang', 'status' => 'Aktif', 'status_pelayanan' => 'Aktif', 'urutan' => 5, 'foto' => null],
                    ]);
                }
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('riwayat_pastor_paroki')) {
                $existingCols = \Illuminate\Support\Facades\Schema::getColumnListing('riwayat_pastor_paroki');
                \Illuminate\Support\Facades\Schema::table('riwayat_pastor_paroki', function ($table) use ($existingCols) {
                    if (!in_array('foto', $existingCols, true)) $table->string('foto', 255)->nullable();
                    if (!in_array('status_pelayanan', $existingCols, true)) $table->string('status_pelayanan', 50)->nullable();
                    if (!in_array('periode_mulai', $existingCols, true)) $table->string('periode_mulai', 50)->nullable();
                    if (!in_array('periode_selesai', $existingCols, true)) $table->string('periode_selesai', 50)->nullable();
                    if (!in_array('tahun_mulai', $existingCols, true)) $table->string('tahun_mulai', 50)->nullable();
                    if (!in_array('tahun_selesai', $existingCols, true)) $table->string('tahun_selesai', 50)->nullable();
                    if (!in_array('urutan', $existingCols, true)) $table->integer('urutan')->default(1);
                    if (!in_array('keterangan', $existingCols, true)) $table->text('keterangan')->nullable();
                });
            }
        } catch (\Throwable $e) {
            // Silently continue
        }
    }

}
