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
            'kub' => 'Admin KUB',
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

        $jabatanList = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('master_referensi_item')) {
            $jabatanList = \Illuminate\Support\Facades\DB::table('master_referensi_item')
                ->where('referensi_id', 10)
                ->where('status', 1)
                ->orderBy('urutan')
                ->orderBy('id')
                ->get()
                ->map(fn($it) => [
                    'id' => $it->nilai,
                    'name' => $it->nilai,
                ])
                ->toArray();
        }

        return Inertia::render('Inertia/PastorForm', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
            'isEdit' => false,
            'pastorItem' => null,
            'keuskupanList' => \App\Models\Keuskupan::orderBy('nama_keuskupan')->get(['id_keuskupan', 'nama_keuskupan']),
            'dekenatList' => \App\Models\Dekenat::orderBy('nama_dekenat')->get(['id_dekenat', 'nama_dekenat', 'keuskupan_id']),
            'parokiList' => \App\Models\Paroki::orderBy('nama_paroki')->get(['id_paroki', 'nama_paroki', 'kode_paroki', 'dekenat_id', 'keuskupan_id']),
            'ordoList' => $ordoList,
            'jabatanList' => $jabatanList,
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
            'kub' => 'Admin KUB',
            'bendahara' => 'Bendahara',
            'penulis' => 'Penulis',
            'umat' => 'Umat',
        ];
        $resolvedRole = $roleMap[$firstSegment] ?? auth()->user()?->role?->nama_role ?? 'Super Admin';

        $pastorItem = \App\Models\MasterPastor::findByUuidOrIdOrFail($id);
        $pastorItem->hashid = encode_id($pastorItem->id);
        $pastorItem->iid = $pastorItem->hashid;

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

        $jabatanList = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('master_referensi_item')) {
            $jabatanList = \Illuminate\Support\Facades\DB::table('master_referensi_item')
                ->where('referensi_id', 10)
                ->where('status', 1)
                ->orderBy('urutan')
                ->orderBy('id')
                ->get()
                ->map(fn($it) => [
                    'id' => $it->nilai,
                    'name' => $it->nilai,
                ])
                ->toArray();
        }

        return Inertia::render('Inertia/PastorForm', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
            'isEdit' => true,
            'pastorItem' => $pastorItem,
            'keuskupanList' => \App\Models\Keuskupan::orderBy('nama_keuskupan')->get(['id_keuskupan', 'nama_keuskupan']),
            'dekenatList' => \App\Models\Dekenat::orderBy('nama_dekenat')->get(['id_dekenat', 'nama_dekenat', 'keuskupan_id']),
            'parokiList' => \App\Models\Paroki::orderBy('nama_paroki')->get(['id_paroki', 'nama_paroki', 'kode_paroki', 'dekenat_id', 'keuskupan_id']),
            'ordoList' => $ordoList,
            'jabatanList' => $jabatanList,
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
            if ($k === 'foto' && !empty($cleanData['foto'])) continue;
            if (in_array($k, $validColumns, true) && !in_array($k, ['created_at', 'updated_at'], true)) {
                $cleanData[$k] = $v;
            }
        }
        if (in_array('created_by', $validColumns, true)) {
            $cleanData['created_by'] = auth()->id();
        }

        // Standardize status
        if (isset($cleanData['status'])) {
            if ($cleanData['status'] === '1' || $cleanData['status'] === 1) $cleanData['status'] = 'Aktif';
            if ($cleanData['status'] === '0' || $cleanData['status'] === 0) $cleanData['status'] = 'Nonaktif';
        }

        // Auto-fill paroki_tugas & keuskupan if ID is supplied
        if (!empty($cleanData['paroki_id']) && empty($cleanData['paroki_tugas']) && \Illuminate\Support\Facades\Schema::hasTable('paroki')) {
            $pModel = \Illuminate\Support\Facades\DB::table('paroki')->where('id_paroki', $cleanData['paroki_id'])->first();
            if ($pModel) $cleanData['paroki_tugas'] = $pModel->nama_paroki;
        }
        if (!empty($cleanData['keuskupan_id']) && empty($cleanData['keuskupan']) && \Illuminate\Support\Facades\Schema::hasTable('keuskupan')) {
            $kModel = \Illuminate\Support\Facades\DB::table('keuskupan')->where('id_keuskupan', $cleanData['keuskupan_id'])->first();
            if ($kModel) $cleanData['keuskupan'] = $kModel->nama_keuskupan;
        }

        $created = \App\Models\MasterPastor::create($cleanData);

        // Otomatis sinkronkan nama Pastor Paroki ke paroki, profil, dan sambutan jika jabatannya Pastor Paroki
        if (str_contains(strtolower($created->jabatan ?? ''), 'pastor paroki')) {
            $formattedName = \App\Models\MasterPastor::formatNama($created);
            $photoPath = $created->foto;
            if (\Illuminate\Support\Facades\Schema::hasTable('paroki') && !empty($created->paroki_id)) {
                $pUpdate = ['nama_pastor_paroki_aktif' => $formattedName];
                if (!empty($photoPath) && \Illuminate\Support\Facades\Schema::hasColumn('paroki', 'foto_pastor')) {
                    $pUpdate['foto_pastor'] = $photoPath;
                }
                \Illuminate\Support\Facades\DB::table('paroki')->where('id_paroki', $created->paroki_id)->update($pUpdate);
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('profil_paroki')) {
                $profUpdate = ['pastor_paroki' => $formattedName];
                if (!empty($photoPath) && \Illuminate\Support\Facades\Schema::hasColumn('profil_paroki', 'foto_pastor')) {
                    $profUpdate['foto_pastor'] = $photoPath;
                }
                \Illuminate\Support\Facades\DB::table('profil_paroki')->update($profUpdate);
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('sambutan_pastor')) {
                $sambCols = \Illuminate\Support\Facades\Schema::getColumnListing('sambutan_pastor');
                $sambUpdate = ['nama_pastor' => $formattedName];
                if (!empty($photoPath) && in_array('foto_pastor', $sambCols, true)) $sambUpdate['foto_pastor'] = $photoPath;
                if (!empty($photoPath) && in_array('foto', $sambCols, true)) $sambUpdate['foto'] = $photoPath;
                \Illuminate\Support\Facades\DB::table('sambutan_pastor')->update($sambUpdate);
            }
        }

        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? 'superadmin';
        $redirectUrl = str_contains($request->header('referer', ''), 'master-referensi') || str_contains($request->path(), 'master-referensi')
            ? '/admin/master-referensi/pastor'
            : "/{$firstSegment}/master-pastor";

        $this->clearFastAccessCache();

        return redirect($redirectUrl)->with('success', 'Data Pastor / Imam baru berhasil disimpan.');
    }


    public function updatePastor(Request $request, $id)
    {
        $pastor = \App\Models\MasterPastor::findByUuidOrIdOrFail($id);
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
            if ($k === 'foto' && !empty($cleanData['foto'])) continue;
            if (in_array($k, $validColumns, true) && !in_array($k, ['created_at', 'updated_at'], true)) {
                $cleanData[$k] = $v;
            }
        }
        if (in_array('updated_by', $validColumns, true)) {
            $cleanData['updated_by'] = auth()->id();
        }

        // Standardize status
        if (isset($cleanData['status'])) {
            if ($cleanData['status'] === '1' || $cleanData['status'] === 1) $cleanData['status'] = 'Aktif';
            if ($cleanData['status'] === '0' || $cleanData['status'] === 0) $cleanData['status'] = 'Nonaktif';
        }

        // Auto-fill paroki_tugas & keuskupan if ID is supplied
        if (!empty($cleanData['paroki_id']) && empty($cleanData['paroki_tugas']) && \Illuminate\Support\Facades\Schema::hasTable('paroki')) {
            $pModel = \Illuminate\Support\Facades\DB::table('paroki')->where('id_paroki', $cleanData['paroki_id'])->first();
            if ($pModel) $cleanData['paroki_tugas'] = $pModel->nama_paroki;
        }
        if (!empty($cleanData['keuskupan_id']) && empty($cleanData['keuskupan']) && \Illuminate\Support\Facades\Schema::hasTable('keuskupan')) {
            $kModel = \Illuminate\Support\Facades\DB::table('keuskupan')->where('id_keuskupan', $cleanData['keuskupan_id'])->first();
            if ($kModel) $cleanData['keuskupan'] = $kModel->nama_keuskupan;
        }

        $pastor->update($cleanData);
        $pastor->refresh();

        $photoPath = !empty($cleanData['foto']) ? $cleanData['foto'] : $pastor->foto;

        // Sync photo to other tables if photo exists
        if (!empty($photoPath)) {
            $isPastorParoki = str_contains(strtolower($pastor->jabatan ?? ''), 'pastor paroki') || str_contains(strtolower((string)($pastor->status ?? '')), 'aktif') || (string)$pastor->status === '1';
            if ($isPastorParoki) {
                if (\Illuminate\Support\Facades\Schema::hasTable('profil_paroki') && \Illuminate\Support\Facades\Schema::hasColumn('profil_paroki', 'foto_pastor')) {
                    \Illuminate\Support\Facades\DB::table('profil_paroki')->update(['foto_pastor' => $photoPath]);
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('paroki') && !empty($pastor->paroki_id) && \Illuminate\Support\Facades\Schema::hasColumn('paroki', 'foto_pastor')) {
                    \Illuminate\Support\Facades\DB::table('paroki')->where('id_paroki', $pastor->paroki_id)->update(['foto_pastor' => $photoPath]);
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('riwayat_pastor_paroki') && \Illuminate\Support\Facades\Schema::hasColumn('riwayat_pastor_paroki', 'foto')) {
                    \Illuminate\Support\Facades\DB::table('riwayat_pastor_paroki')
                        ->where(function($q) use ($pastor) {
                            $q->where('pastor_id', $pastor->id)
                              ->orWhere('nama_pastor', 'like', '%' . $pastor->nama_pastor . '%')
                              ->orWhere('status', 'like', '%aktif%')
                              ->orWhere('status_pelayanan', 'like', '%aktif%')
                              ->orWhere('tahun_selesai', 'Sekarang')
                              ->orWhereNull('periode_selesai');
                        })
                        ->update(['foto' => $photoPath]);
                }
                if (\Illuminate\Support\Facades\Schema::hasTable('sambutan_pastor')) {
                    $sambutanCols = \Illuminate\Support\Facades\Schema::getColumnListing('sambutan_pastor');
                    $sambutanData = [];
                    if (in_array('foto_pastor', $sambutanCols, true)) $sambutanData['foto_pastor'] = $photoPath;
                    if (in_array('foto', $sambutanCols, true)) $sambutanData['foto'] = $photoPath;
                    if (!empty($sambutanData)) {
                        \Illuminate\Support\Facades\DB::table('sambutan_pastor')
                            ->where(function($q) use ($pastor) {
                                if (!empty($pastor->id)) $q->where('pastor_id', $pastor->id);
                                if (!empty($pastor->nama_pastor)) $q->orWhere('nama_pastor', 'like', '%' . $pastor->nama_pastor . '%');
                            })
                            ->update($sambutanData);
                    }
                }
            }
        }

        // Otomatis sinkronkan nama & foto Pastor Paroki ke paroki, profil, dan sambutan jika jabatannya Pastor Paroki (ALWAYS RUN)
        if (str_contains(strtolower($pastor->jabatan ?? ''), 'pastor paroki')) {
            $formattedName = \App\Models\MasterPastor::formatNama($pastor);
            if (\Illuminate\Support\Facades\Schema::hasTable('paroki') && !empty($pastor->paroki_id)) {
                $parokiUpdate = ['nama_pastor_paroki_aktif' => $formattedName];
                if (!empty($photoPath) && \Illuminate\Support\Facades\Schema::hasColumn('paroki', 'foto_pastor')) {
                    $parokiUpdate['foto_pastor'] = $photoPath;
                }
                \Illuminate\Support\Facades\DB::table('paroki')->where('id_paroki', $pastor->paroki_id)->update($parokiUpdate);
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('profil_paroki')) {
                $profilUpdate = ['pastor_paroki' => $formattedName];
                if (!empty($photoPath) && \Illuminate\Support\Facades\Schema::hasColumn('profil_paroki', 'foto_pastor')) {
                    $profilUpdate['foto_pastor'] = $photoPath;
                }
                \Illuminate\Support\Facades\DB::table('profil_paroki')->update($profilUpdate);
            }
            if (\Illuminate\Support\Facades\Schema::hasTable('sambutan_pastor')) {
                $sambCols = \Illuminate\Support\Facades\Schema::getColumnListing('sambutan_pastor');
                $sambUpdate = ['nama_pastor' => $formattedName];
                if (!empty($photoPath) && in_array('foto_pastor', $sambCols, true)) $sambUpdate['foto_pastor'] = $photoPath;
                if (!empty($photoPath) && in_array('foto', $sambCols, true)) $sambUpdate['foto'] = $photoPath;
                \Illuminate\Support\Facades\DB::table('sambutan_pastor')->update($sambUpdate);
            }
        }
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
