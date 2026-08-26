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

trait PastorModuleTrait
{
    public function createPastor(Request $request): Response
    {
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? 'superadmin';
        $roleMap = [
            'superadmin' => 'Super Admin',
            'paroki' => 'Admin Paroki',
            'pastor' => 'Pastor',
            'wilayah' => 'Admin Wilayah',
            'kapela' => 'Admin Kapela / Stasi',
            'kub' => 'Ketua KUB',
            'bendahara' => 'Bendahara',
            'penulis' => 'Penulis',
            'umat' => 'Umat',
        ];
        $resolvedRole = $roleMap[$firstSegment] ?? auth()->user()?->role?->nama_role ?? 'Super Admin';

        $ordoList = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('master_ordo')) {
            $ordoList = \Illuminate\Support\Facades\DB::table('master_ordo')
                ->get()
                ->map(function ($o) {
                    $arr = (array)$o;
                    $kode = $arr['singkatan'] ?? $arr['kode'] ?? $arr['nama_ordo'] ?? '';
                    $nama = $arr['nama_ordo'] ?? $arr['nama'] ?? $kode;
                    $label = !empty($kode) && !str_starts_with($nama, $kode)
                        ? "{$kode} - {$nama}"
                        : $nama;
                    return [
                        'id' => $kode ?: $nama,
                        'name' => $label,
                    ];
                })
                ->toArray();
        }
        if (empty($ordoList)) {
            $ordoList = [
                ['id' => 'CMF', 'name' => 'CMF - Misionaris Claretian (Cordis Mariae Filii)'],
                ['id' => 'SVD', 'name' => 'SVD - Serikat Sabda Allah (Societas Verbi Divini)'],
                ['id' => 'OFM', 'name' => 'OFM - Ordo Saudara Dina (Fransiskan)'],
                ['id' => 'OCD', 'name' => 'OCD - Karmelit Tak Berkasut (Karmel)'],
                ['id' => 'SJ', 'name' => 'SJ - Serikat Yesus (Yesuit)'],
                ['id' => 'CSsR', 'name' => 'CSsR - Kongregasi Sang Penebus Mahakudus'],
                ['id' => 'MSF', 'name' => 'MSF - Misionaris Keluarga Kudus'],
                ['id' => 'SX', 'name' => 'SX - Serikat Misi Xaverian'],
                ['id' => 'SCJ', 'name' => 'SCJ - Imam-Imam Hati Kudus Yesus (Dehonian)'],
                ['id' => 'O.Carm', 'name' => 'O.Carm - Ordo Karmel'],
                ['id' => 'OSB', 'name' => 'OSB - Ordo Santo Benediktus (Benediktin)'],
                ['id' => 'CP', 'name' => 'CP - Kongregasi Pasionis'],
                ['id' => 'SDB', 'name' => 'SDB - Salesian Don Bosco'],
                ['id' => 'OMI', 'name' => 'OMI - Oblat Maria Imakulata'],
                ['id' => 'CICM', 'name' => 'CICM - Misi Scheut'],
                ['id' => 'CM', 'name' => 'CM - Kongregasi Misi (Vinsensian)'],
            ];
        }

        return Inertia::render('Inertia/PastorForm', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
            'isEdit' => false,
            'pastorItem' => null,
            'keuskupanList' => \App\Models\Keuskupan::orderBy('nama_keuskupan')->get(['id_keuskupan', 'nama_keuskupan']),
            'parokiList' => \App\Models\Paroki::orderBy('nama_paroki')->get(['id_paroki', 'nama_paroki', 'kode_paroki']),
            'ordoList' => $ordoList,
        ]);
    }


    public function editPastor(Request $request, string|int $id): Response
    {
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? 'superadmin';
        $roleMap = [
            'superadmin' => 'Super Admin',
            'paroki' => 'Admin Paroki',
            'pastor' => 'Pastor',
            'wilayah' => 'Admin Wilayah',
            'kapela' => 'Admin Kapela / Stasi',
            'kub' => 'Ketua KUB',
            'bendahara' => 'Bendahara',
            'penulis' => 'Penulis',
            'umat' => 'Umat',
        ];
        $resolvedRole = $roleMap[$firstSegment] ?? auth()->user()?->role?->nama_role ?? 'Super Admin';

        $pastorItem = \App\Models\MasterPastor::findOrFail($id);

        $ordoList = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('master_ordo')) {
            $ordoList = \Illuminate\Support\Facades\DB::table('master_ordo')
                ->get()
                ->map(function ($o) {
                    $arr = (array)$o;
                    $kode = $arr['singkatan'] ?? $arr['kode'] ?? $arr['nama_ordo'] ?? '';
                    $nama = $arr['nama_ordo'] ?? $arr['nama'] ?? $kode;
                    $label = !empty($kode) && !str_starts_with($nama, $kode)
                        ? "{$kode} - {$nama}"
                        : $nama;
                    return [
                        'id' => $kode ?: $nama,
                        'name' => $label,
                    ];
                })
                ->toArray();
        }
        if (empty($ordoList)) {
            $ordoList = [
                ['id' => 'CMF', 'name' => 'CMF - Misionaris Claretian (Cordis Mariae Filii)'],
                ['id' => 'SVD', 'name' => 'SVD - Serikat Sabda Allah (Societas Verbi Divini)'],
                ['id' => 'OFM', 'name' => 'OFM - Ordo Saudara Dina (Fransiskan)'],
                ['id' => 'OCD', 'name' => 'OCD - Karmelit Tak Berkasut (Karmel)'],
                ['id' => 'SJ', 'name' => 'SJ - Serikat Yesus (Yesuit)'],
                ['id' => 'CSsR', 'name' => 'CSsR - Kongregasi Sang Penebus Mahakudus'],
                ['id' => 'MSF', 'name' => 'MSF - Misionaris Keluarga Kudus'],
                ['id' => 'SX', 'name' => 'SX - Serikat Misi Xaverian'],
                ['id' => 'SCJ', 'name' => 'SCJ - Imam-Imam Hati Kudus Yesus (Dehonian)'],
                ['id' => 'O.Carm', 'name' => 'O.Carm - Ordo Karmel'],
                ['id' => 'OSB', 'name' => 'OSB - Ordo Santo Benediktus (Benediktin)'],
                ['id' => 'CP', 'name' => 'CP - Kongregasi Pasionis'],
                ['id' => 'SDB', 'name' => 'SDB - Salesian Don Bosco'],
                ['id' => 'OMI', 'name' => 'OMI - Oblat Maria Imakulata'],
                ['id' => 'CICM', 'name' => 'CICM - Misi Scheut'],
                ['id' => 'CM', 'name' => 'CM - Kongregasi Misi (Vinsensian)'],
            ];
        }

        return Inertia::render('Inertia/PastorForm', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
            'isEdit' => true,
            'pastorItem' => $pastorItem,
            'keuskupanList' => \App\Models\Keuskupan::orderBy('nama_keuskupan')->get(['id_keuskupan', 'nama_keuskupan']),
            'parokiList' => \App\Models\Paroki::orderBy('nama_paroki')->get(['id_paroki', 'nama_paroki', 'kode_paroki']),
            'ordoList' => $ordoList,
        ]);
    }


    public function storePastor(Request $request)
    {
        $data = $request->all();
        if (\Illuminate\Support\Facades\Schema::hasTable('master_pastor')) {
            $existingCols = \Illuminate\Support\Facades\Schema::getColumnListing('master_pastor');
            \Illuminate\Support\Facades\Schema::table('master_pastor', function ($table) use ($existingCols) {
                if (!in_array('nama_singkat', $existingCols, true)) $table->string('nama_singkat', 100)->nullable();
                if (!in_array('jenis_tempat_tugas', $existingCols, true)) $table->string('jenis_tempat_tugas', 100)->nullable();
                if (!in_array('periode_mulai', $existingCols, true)) $table->string('periode_mulai', 50)->nullable();
                if (!in_array('periode_selesai', $existingCols, true)) $table->string('periode_selesai', 50)->nullable();
                if (!in_array('tampil_frontend', $existingCols, true)) $table->string('tampil_frontend', 20)->default('Tidak');
                if (!in_array('urutan', $existingCols, true)) $table->integer('urutan')->default(0);
                if (!in_array('catatan_pelayanan', $existingCols, true)) $table->text('catatan_pelayanan')->nullable();
                if (!in_array('riwayat_tambahan', $existingCols, true)) $table->longText('riwayat_tambahan')->nullable();
            });
        }

        $validColumns = $this->schemaColumns('master_pastor');
        $cleanData = [];

        // Handle file upload if any
        if ($request->hasFile('foto_file')) {
            $file = $request->file('foto_file');
            $filename = 'pastor_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/pastor'), $filename);
            $cleanData['foto'] = 'uploads/pastor/' . $filename;
        }

        if (isset($data['riwayat_tambahan']) && is_array($data['riwayat_tambahan'])) {
            $data['riwayat_tambahan'] = json_encode($data['riwayat_tambahan']);
        }

        foreach ($data as $k => $v) {
            if ($k === 'is_deleted' || $k === 'id' || $k === 'foto_file') continue;
            if (in_array($k, $validColumns, true) && !in_array($k, ['created_at', 'updated_at'], true)) {
                $cleanData[$k] = $v;
            }
        }
        if (in_array('created_by', $validColumns, true)) {
            $cleanData['created_by'] = auth()->id();
        }

        $created = \App\Models\MasterPastor::create($cleanData);
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? 'superadmin';
        $redirectUrl = str_contains($request->header('referer', ''), 'master-referensi') || str_contains($request->path(), 'master-referensi')
            ? '/admin/master-referensi/pastor'
            : "/{$firstSegment}/master-pastor";

        $this->clearFastAccessCache();

        return redirect($redirectUrl)->with('success', 'Data Pastor / Imam baru berhasil disimpan.');
    }


    public function updatePastor(Request $request, $id)
    {
        $pastor = \App\Models\MasterPastor::findOrFail($id);
        $data = $request->all();

        // Ensure schema columns exist for advanced pastor fields
        if (\Illuminate\Support\Facades\Schema::hasTable('master_pastor')) {
            $existingCols = \Illuminate\Support\Facades\Schema::getColumnListing('master_pastor');
            \Illuminate\Support\Facades\Schema::table('master_pastor', function ($table) use ($existingCols) {
                if (!in_array('nama_singkat', $existingCols, true)) $table->string('nama_singkat', 100)->nullable();
                if (!in_array('jenis_tempat_tugas', $existingCols, true)) $table->string('jenis_tempat_tugas', 100)->nullable();
                if (!in_array('periode_mulai', $existingCols, true)) $table->string('periode_mulai', 50)->nullable();
                if (!in_array('periode_selesai', $existingCols, true)) $table->string('periode_selesai', 50)->nullable();
                if (!in_array('tampil_frontend', $existingCols, true)) $table->string('tampil_frontend', 20)->default('Tidak');
                if (!in_array('urutan', $existingCols, true)) $table->integer('urutan')->default(0);
                if (!in_array('catatan_pelayanan', $existingCols, true)) $table->text('catatan_pelayanan')->nullable();
                if (!in_array('riwayat_tambahan', $existingCols, true)) $table->longText('riwayat_tambahan')->nullable();
            });
        }

        $validColumns = $this->schemaColumns('master_pastor');
        $cleanData = [];

        // Handle file upload if any
        if ($request->hasFile('foto_file')) {
            $file = $request->file('foto_file');
            $filename = 'pastor_' . time() . '_' . Str::random(8) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/pastor'), $filename);
            $cleanData['foto'] = 'uploads/pastor/' . $filename;
        }

        if (isset($data['riwayat_tambahan']) && is_array($data['riwayat_tambahan'])) {
            $data['riwayat_tambahan'] = json_encode($data['riwayat_tambahan']);
        }

        foreach ($data as $k => $v) {
            if ($k === 'is_deleted' || $k === 'id' || $k === 'foto_file') continue;
            if (in_array($k, $validColumns, true) && !in_array($k, ['created_at', 'updated_at'], true)) {
                $cleanData[$k] = $v;
            }
        }
        if (in_array('updated_by', $validColumns, true)) {
            $cleanData['updated_by'] = auth()->id();
        }

        $pastor->update($cleanData);
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? 'superadmin';
        $redirectUrl = str_contains($request->header('referer', ''), 'master-referensi') || str_contains($request->path(), 'master-referensi')
            ? '/admin/master-referensi/pastor'
            : "/{$firstSegment}/master-pastor";

        $this->clearFastAccessCache();

        return redirect($redirectUrl)->with('success', 'Data Pastor / Imam berhasil diperbarui.');
    }


    protected function resolvePastorDisplayName($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value) && \Illuminate\Support\Facades\Schema::hasTable('master_pastor')) {
            $pastor = \App\Models\MasterPastor::find($value);
            if ($pastor) {
                return trim(implode(' ', array_filter([
                    $pastor->gelar_depan ?? null,
                    $pastor->nama_pastor ?? null,
                ])) . (!empty($pastor->gelar_belakang) ? ', ' . trim($pastor->gelar_belakang) : ''));
            }
        }

        return (string) $value;
    }

}
