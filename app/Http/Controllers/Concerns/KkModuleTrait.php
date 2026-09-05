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

trait KkModuleTrait
{
    public function createKk(Request $request)
    {
        $this->ensureKkKatolikColumns();
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? 'superadmin';
        if (in_array($firstSegment, ['wilayah', 'kapela'], true)) {
            return redirect("/{$firstSegment}/kk-katolik")->with('error', 'Akses Terbatas: Level Wilayah / Stasi hanya memiliki hak akses Lihat Data KK (Read-Only).');
        }
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

        $defaultParokiId = $this->defaultParokiIdFromProfile();
        $defaultParoki = Paroki::with(['keuskupan', 'provinsi', 'kabupaten', 'kecamatan', 'desa'])->find($defaultParokiId)
            ?? Paroki::with(['keuskupan', 'provinsi', 'kabupaten', 'kecamatan', 'desa'])->first();
        $civilRegion = $this->resolveKkCivilRegion(null, $defaultParoki);

        $rawKeuskupanCode = $defaultParoki?->keuskupan?->kode_keuskupan ?? '012';
        $numericKeuskupanCode = preg_replace('/[^0-9]/', '', (string)$rawKeuskupanCode);
        $kodeKeuskupan = str_pad(substr($numericKeuskupanCode ?: '012', 0, 3), 3, '0', STR_PAD_LEFT);

        $rawParokiCode = $defaultParoki?->kode_paroki ?? '001';
        $numericParokiCode = preg_replace('/[^0-9]/', '', (string)$rawParokiCode);
        $kodeParoki = str_pad(substr($numericParokiCode ?: '001', 0, 3), 3, '0', STR_PAD_LEFT);

        $nextSeq = str_pad((\App\Models\KkKatolik::count() + 1), 3, '0', STR_PAD_LEFT);
        $defaultNoKk = 'K' . $kodeKeuskupan . $kodeParoki . $nextSeq;

        $authUser = $request->user();
        $targetKubId = $authUser?->kub_id ?: ($firstSegment === 'kub' ? (session('simulated_kub_id') ?: $request->input('kub_id')) : null);
        if (!$targetKubId && $firstSegment === 'kub') {
            $targetKubId = \App\Models\User::whereNotNull('kub_id')->value('kub_id') ?: \App\Models\Kub::value('id');
            session(['simulated_kub_id' => $targetKubId]);
        }
        $userKub = $targetKubId ? \App\Models\Kub::find($targetKubId) : null;
        $userKubId = $userKub?->id;
        $userWilayahId = $authUser?->wilayah_id ?: $userKub?->wilayah_id ?: ($firstSegment === 'wilayah' ? (session('simulated_wilayah_id') ?: $request->input('wilayah_id')) : null);
        $userKapelaId = $authUser?->kapela_id ?: $userKub?->kapela_id ?: ($firstSegment === 'kapela' ? (session('simulated_kapela_id') ?: $request->input('kapela_id')) : null);

        return Inertia::render('Inertia/KkForm', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
            'isEdit' => false,
            'kkItem' => null,
            'defaultNoKk' => $defaultNoKk,
            'kodeKeuskupan' => $kodeKeuskupan,
            'kodeParoki' => $kodeParoki,
            'nextKkNumber' => $nextSeq,
            'namaParoki' => $defaultParoki?->nama_paroki ?? 'Paroki St. Vinsensius a Paulo Benlutu',
            'namaKeuskupan' => $defaultParoki?->keuskupan?->nama_keuskupan ?? 'Keuskupan Agung Kupang',
            'wilayahList' => \App\Models\Wilayah::orderBy('nama_wilayah')->get(['id', 'nama_wilayah']),
            'kubList' => \App\Models\Kub::orderBy('nama_kub')->get(['id', 'nama_kub', 'wilayah_id', 'kapela_id']),
            'kapelaList' => \App\Models\Kapela::orderBy('nama_kapela')->get(['id', 'nama_kapela']),
            'userKubId' => $userKubId,
            'userWilayahId' => $userWilayahId,
            'userKapelaId' => $userKapelaId,
            'defaultKubId' => $userKubId,
            'defaultWilayahId' => $userWilayahId,
            'defaultKapelaId' => $userKapelaId,
            'pastorList' => \App\Models\MasterPastor::orderBy('nama_pastor')->get(['id', 'nama_pastor', 'jabatan']),
            'parokiList' => \App\Models\Paroki::orderBy('nama_paroki')->get(['id_paroki', 'nama_paroki']),
            'provinsiList' => \App\Models\Provinsi::orderBy('nama_provinsi')->get(['id_provinsi', 'nama_provinsi']),
            'kabupatenList' => \App\Models\Kabupaten::orderBy('nama_kabupaten')->get(['id_kabupaten', 'provinsi_id', 'nama_kabupaten']),
            'kecamatanList' => \App\Models\Kecamatan::orderBy('nama_kecamatan')->get(['id_kecamatan', 'kabupaten_id', 'nama_kecamatan']),
            'desaList' => $this->desaOptionsForKecamatan($civilRegion['kecamatan_id'] ?? null),
            'civilRegion' => $civilRegion,
        ]);
    }


    public function editKk(Request $request, string|int $id): Response|\Illuminate\Http\RedirectResponse
    {
        $this->ensureKkKatolikColumns();
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? 'superadmin';
        if (in_array($firstSegment, ['wilayah', 'kapela'], true)) {
            return redirect("/{$firstSegment}/kk-katolik")->with('error', 'Akses Terbatas: Level Wilayah / Stasi hanya memiliki hak akses Lihat Data KK (Read-Only).');
        }
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

        $kkItem = \App\Models\KkKatolik::with('anggota')->whereUuidOrId($id)->first()
            ?? \App\Models\KkKatolik::with('anggota')->where('no_kk_kw', $id)->firstOrFail();
        $kkItem->hashid = encode_id($kkItem->id);
        $kkItem->iid = $kkItem->hashid;

        $defaultParokiId = $this->defaultParokiIdFromProfile();
        $defaultParoki = Paroki::with(['keuskupan', 'provinsi', 'kabupaten', 'kecamatan', 'desa'])->find($defaultParokiId)
            ?? Paroki::with(['keuskupan', 'provinsi', 'kabupaten', 'kecamatan', 'desa'])->first();
        $civilRegion = $this->resolveKkCivilRegion($kkItem, $defaultParoki);

        $rawKeuskupanCode = $defaultParoki?->keuskupan?->kode_keuskupan ?? '012';
        $numericKeuskupanCode = preg_replace('/[^0-9]/', '', (string)$rawKeuskupanCode);
        $kodeKeuskupan = str_pad(substr($numericKeuskupanCode ?: '012', 0, 3), 3, '0', STR_PAD_LEFT);

        $rawParokiCode = $defaultParoki?->kode_paroki ?? '001';
        $numericParokiCode = preg_replace('/[^0-9]/', '', (string)$rawParokiCode);
        $kodeParoki = str_pad(substr($numericParokiCode ?: '001', 0, 3), 3, '0', STR_PAD_LEFT);

        $nextSeq = str_pad((\App\Models\KkKatolik::count() + 1), 3, '0', STR_PAD_LEFT);
        $defaultNoKk = $kkItem->no_kk_kw ?: ('K' . $kodeKeuskupan . $kodeParoki . $nextSeq);

        $authUser = $request->user();
        $isSuperAdmin = (int)($authUser?->role_id ?? 0) === 1 || in_array(strtolower($authUser?->role?->nama_role ?? ''), ['super admin', 'superadmin'], true);
        $targetKubId = $authUser?->kub_id ?: ($firstSegment === 'kub' ? (session('simulated_kub_id') ?: $request->input('kub_id')) : null);

        // Proteksi Hak Akses Level KUB: Ketua KUB tidak boleh mengakses data KK di luar KUB-nya
        if ($firstSegment === 'kub' || (!$isSuperAdmin && $authUser?->kub_id)) {
            if ($targetKubId && $kkItem->kub_id && (int)$kkItem->kub_id !== (int)$targetKubId) {
                if (!$isSuperAdmin) {
                    return redirect("/{$firstSegment}/kk-katolik")->with('error', 'Anda tidak memiliki hak akses untuk mengedit data KK di luar KUB Anda.');
                } else {
                    // Jika Super Admin membuka KK tertentu dalam mode KUB, sinkronkan simulasi KUB
                    session(['simulated_kub_id' => $kkItem->kub_id]);
                    $targetKubId = $kkItem->kub_id;
                }
            }
        }

        $userKub = $targetKubId ? \App\Models\Kub::find($targetKubId) : ($kkItem->kub_id ? \App\Models\Kub::find($kkItem->kub_id) : null);
        $userKubId = $userKub?->id ?: $kkItem->kub_id;
        $userWilayahId = $userKub?->wilayah_id ?: $kkItem->wilayah_id;
        $userKapelaId = $userKub?->kapela_id ?: $kkItem->kapela_id;

        return Inertia::render('Inertia/KkForm', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
            'isEdit' => true,
            'kkItem' => $kkItem,
            'defaultNoKk' => $defaultNoKk,
            'kodeKeuskupan' => $kodeKeuskupan,
            'kodeParoki' => $kodeParoki,
            'nextKkNumber' => $nextSeq,
            'namaParoki' => $defaultParoki?->nama_paroki ?? 'Paroki St. Vinsensius a Paulo Benlutu',
            'namaKeuskupan' => $defaultParoki?->keuskupan?->nama_keuskupan ?? 'Keuskupan Agung Kupang',
            'wilayahList' => \App\Models\Wilayah::orderBy('nama_wilayah')->get(['id', 'nama_wilayah']),
            'kubList' => \App\Models\Kub::orderBy('nama_kub')->get(['id', 'nama_kub', 'wilayah_id', 'kapela_id']),
            'kapelaList' => \App\Models\Kapela::orderBy('nama_kapela')->get(['id', 'nama_kapela']),
            'userKubId' => $userKubId,
            'userWilayahId' => $userWilayahId,
            'userKapelaId' => $userKapelaId,
            'defaultKubId' => $userKubId,
            'defaultWilayahId' => $userWilayahId,
            'defaultKapelaId' => $userKapelaId,
            'pastorList' => \App\Models\MasterPastor::orderBy('nama_pastor')->get(['id', 'nama_pastor', 'jabatan']),
            'parokiList' => \App\Models\Paroki::orderBy('nama_paroki')->get(['id_paroki', 'nama_paroki']),
            'provinsiList' => \App\Models\Provinsi::orderBy('nama_provinsi')->get(['id_provinsi', 'nama_provinsi']),
            'kabupatenList' => \App\Models\Kabupaten::orderBy('nama_kabupaten')->get(['id_kabupaten', 'provinsi_id', 'nama_kabupaten']),
            'kecamatanList' => \App\Models\Kecamatan::orderBy('nama_kecamatan')->get(['id_kecamatan', 'kabupaten_id', 'nama_kecamatan']),
            'desaList' => $this->desaOptionsForKecamatan($civilRegion['kecamatan_id'] ?? null),
            'civilRegion' => $civilRegion,
        ]);
    }


    public function viewKk(Request $request, string|int $id)
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

        $kk = \App\Models\KkKatolik::with(['anggota', 'wilayah', 'kapela', 'kub'])->whereUuidOrId($id)->first()
            ?? \App\Models\KkKatolik::with(['anggota', 'wilayah', 'kapela', 'kub'])->where('no_kk_kw', $id)->first()
            ?? \App\Models\KkKatolik::with(['anggota', 'wilayah', 'kapela', 'kub'])->firstOrFail();
        $kk->hashid = encode_id($kk->id);
        $kk->iid = $kk->hashid;

        $defaultParokiId = $this->defaultParokiIdFromProfile();
        $defaultParoki = Paroki::with('keuskupan')->find($defaultParokiId)
            ?? Paroki::with('keuskupan')->first();

        $profilParoki = \App\Models\ProfilParoki::first();
        if ($defaultParoki && empty($defaultParoki->logo) && !empty($profilParoki?->logo)) {
            $defaultParoki->logo = $profilParoki->logo;
        }

        $keuskupan = $defaultParoki?->keuskupan
            ?? \App\Models\Keuskupan::find(5)
            ?? \App\Models\Keuskupan::first();

        return Inertia::render('Inertia/KkDetail', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
            'kk' => $kk,
            'paroki' => $defaultParoki,
            'keuskupan' => $keuskupan,
        ]);
    }


    public function exportKkPdf(Request $request, string|int $id)
    {
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? 'superadmin';
        if (in_array($firstSegment, ['wilayah', 'kapela'], true)) {
            return redirect("/{$firstSegment}/kk-katolik")->with('error', 'Akses Terbatas: Level Wilayah / Stasi tidak memiliki akses cetak KK Katolik.');
        }

        $kk = \App\Models\KkKatolik::with(['anggota', 'wilayah', 'kapela', 'kub'])->whereUuidOrId($id)->first()
            ?? \App\Models\KkKatolik::with(['anggota', 'wilayah', 'kapela', 'kub'])->where('no_kk_kw', $id)->first()
            ?? \App\Models\KkKatolik::with(['anggota', 'wilayah', 'kapela', 'kub'])->firstOrFail();

        $defaultParokiId = $this->defaultParokiIdFromProfile();
        $paroki = Paroki::with('keuskupan')->find($defaultParokiId)
            ?? Paroki::with('keuskupan')->first();

        // Ambil data Keuskupan dan Profil Paroki aktif
        $keuskupan = $paroki?->keuskupan
            ?? \App\Models\Keuskupan::find(5)
            ?? \App\Models\Keuskupan::first();
        $profilParoki = \App\Models\ProfilParoki::first();

        // Nama & Jabatan Pastor Paroki aktif
        $namaPastorParoki = $profilParoki?->pastor_paroki
            ?: ($paroki?->nama_pastor_paroki_aktif
            ?: ($paroki?->pastor_paroki
            ?: 'RD. Herman Hilers Penga'));

        $cleanPastorName = trim(preg_replace('/^(RD\.|Pr\.|RP\.|P\.)\s*/i', '', $namaPastorParoki));
        $pastorRecord = \Illuminate\Support\Facades\DB::table('master_pastor')
            ->where(function ($q) use ($namaPastorParoki, $cleanPastorName) {
                $q->where('nama_pastor', $namaPastorParoki)
                  ->orWhere('nama_pastor', 'like', '%' . $cleanPastorName . '%');
            })
            ->first()
            ?? \Illuminate\Support\Facades\DB::table('riwayat_pastor_paroki')
            ->where(function ($q) use ($namaPastorParoki, $cleanPastorName) {
                $q->where('nama_pastor', $namaPastorParoki)
                  ->orWhere('nama_pastor', 'like', '%' . $cleanPastorName . '%');
            })
            ->first();

        $namaPastorRekan = $profilParoki?->pastor_rekan ?: null;

        // Jabatan Pastor aktif
        $cleanPastorName = trim(preg_replace('/^(RD\.|Pr\.|RP\.|P\.)\s*/i', '', $namaPastorParoki));
        $pastorRecord = \Illuminate\Support\Facades\DB::table('master_pastor')
            ->where(function ($q) use ($namaPastorParoki, $cleanPastorName) {
                $q->where('nama_pastor', $namaPastorParoki)
                  ->orWhere('nama_pastor', 'like', '%' . $cleanPastorName . '%');
            })
            ->first()
            ?? \Illuminate\Support\Facades\DB::table('riwayat_pastor_paroki')
            ->where(function ($q) use ($namaPastorParoki, $cleanPastorName) {
                $q->where('nama_pastor', $namaPastorParoki)
                  ->orWhere('nama_pastor', 'like', '%' . $cleanPastorName . '%');
            })
            ->first();

        $jabatanPastor = $pastorRecord?->jabatan ?: 'Pastor Paroki';

        // Daftar Pastor resmi yang bertugas di Paroki ini (sesuai Profil Paroki & Master Pastor terkait)
        $pastorsInDb = \App\Models\MasterPastor::where(function ($q) {
                $q->whereNull('status')
                  ->orWhere('status', 'like', '%aktif%')
                  ->orWhere('status', 1)
                  ->orWhere('status', '1');
            })
            ->where(function ($q) use ($defaultParokiId, $paroki) {
                if ($defaultParokiId) {
                    $q->where('paroki_id', $defaultParokiId);
                }
                $namaParokiClean = preg_replace('/^Paroki\s+/i', '', $paroki?->nama_paroki ?? 'Benlutu');
                $q->orWhere('paroki_tugas', 'like', '%' . $namaParokiClean . '%');
            })
            ->get();

        $daftarPastor = collect();

        // 1. Tambah Pastor Paroki dari Profil Paroki Default
        if ($namaPastorParoki) {
            $dbMatch = $pastorsInDb->first(function ($p) use ($namaPastorParoki) {
                return stripos($p->nama_pastor, trim(preg_replace('/^(RD\.|RP\.|Pr\.|P\.)\s*/i', '', $namaPastorParoki))) !== false;
            });
            $formattedName = $dbMatch ? \App\Models\MasterPastor::formatNama($dbMatch) : $namaPastorParoki;
            $daftarPastor->push((object)[
                'id' => $dbMatch?->id ?? 1,
                'nama_pastor' => $formattedName,
                'jabatan' => 'Pastor Paroki',
            ]);
        }

        // 2. Tambah Pastor Rekan dari Profil Paroki Default
        if ($namaPastorRekan) {
            $dbMatch = $pastorsInDb->first(function ($p) use ($namaPastorRekan) {
                return stripos($p->nama_pastor, trim(preg_replace('/^(RD\.|RP\.|Pr\.|P\.)\s*/i', '', $namaPastorRekan))) !== false;
            });
            $formattedName = $dbMatch ? \App\Models\MasterPastor::formatNama($dbMatch) : $namaPastorRekan;
            if (!$daftarPastor->contains(fn($it) => stripos($it->nama_pastor, $formattedName) !== false)) {
                $daftarPastor->push((object)[
                    'id' => $dbMatch?->id ?? 2,
                    'nama_pastor' => $formattedName,
                    'jabatan' => 'Pastor Rekan',
                ]);
            }
        }

        // 3. Tambahkan pastor lain yang secara sah terdaftar bertugas di paroki ini (paroki_id = defaultParokiId)
        foreach ($pastorsInDb as $p) {
            $formatted = \App\Models\MasterPastor::formatNama($p);
            $cleanP = trim(preg_replace('/^(RD\.|RP\.|Pr\.|P\.)\s*/i', '', $p->nama_pastor));
            $alreadyExists = $daftarPastor->contains(function ($it) use ($cleanP, $formatted) {
                return stripos($it->nama_pastor, $cleanP) !== false || stripos($it->nama_pastor, $formatted) !== false;
            });
            if (!$alreadyExists) {
                $daftarPastor->push((object)[
                    'id' => $p->id,
                    'nama_pastor' => $formatted,
                    'jabatan' => $p->jabatan ?: 'Pastor Rekan',
                ]);
            }
        }

        // Dukungan parameter URL jika admin ingin langsung memilih Pastor tertentu via URL
        if ($request->filled('pastor_id')) {
            $pReq = $daftarPastor->firstWhere('id', (int) $request->query('pastor_id'));
            if ($pReq) {
                $namaPastorParoki = $pReq->nama_pastor;
                $jabatanPastor = $pReq->jabatan ?: 'Pastor Rekan';
            }
        } elseif ($request->filled('pastor')) {
            $pSearch = (string) $request->query('pastor');
            $pReq = $daftarPastor->first(function ($p) use ($pSearch) {
                return stripos($p->nama_pastor, $pSearch) !== false;
            });
            if ($pReq) {
                $namaPastorParoki = $pReq->nama_pastor;
                $jabatanPastor = $pReq->jabatan ?: 'Pastor Rekan';
            }
        }

        $keuskupanLogo = $keuskupan?->logo ?: '/images/logo-keuskupan.png';
        $parokiLogo = $paroki?->logo ?: ($profilParoki?->logo ?: '/uploads/paroki/1787494152_6a8aff08b47a5.webp');

        return response()->view('exports.kk-pdf', [
            'kk' => $kk,
            'paroki' => $paroki,
            'keuskupan' => $keuskupan,
            'profilParoki' => $profilParoki,
            'namaPastorParoki' => $namaPastorParoki,
            'cleanPastorName' => $cleanPastorName,
            'jabatanPastor' => $jabatanPastor,
            'daftarPastor' => $daftarPastor,
            'keuskupanLogo' => $keuskupanLogo,
            'parokiLogo' => $parokiLogo,
            'printedAt' => now()->format('d/m/Y H:i'),
        ]);
    }


    public function desaKelurahanOptions(Request $request)
    {
        $validated = $request->validate([
            'kecamatan_id' => ['nullable', 'integer'],
        ]);

        return response()->json([
            'data' => $this->desaOptionsForKecamatan($validated['kecamatan_id'] ?? null),
        ]);
    }


    protected function desaOptionsForKecamatan($kecamatanId)
    {
        if (!$kecamatanId) {
            return collect();
        }

        return \App\Models\DesaKelurahan::where('kecamatan_id', $kecamatanId)
            ->orderBy('nama_desa')
            ->get(['id_desa', 'kecamatan_id', 'nama_desa']);
    }


    protected function resolveKkCivilRegion(?\App\Models\KkKatolik $kkItem, ?\App\Models\Paroki $defaultParoki): array
    {
        $provinsi = $kkItem?->provinsi
            ? \App\Models\Provinsi::where('nama_provinsi', $kkItem->provinsi)->first(['id_provinsi', 'nama_provinsi'])
            : null;

        $kabupaten = $kkItem?->kota_kabupaten
            ? \App\Models\Kabupaten::when($provinsi, fn ($query) => $query->where('provinsi_id', $provinsi->id_provinsi))
                ->where('nama_kabupaten', $kkItem->kota_kabupaten)
                ->first(['id_kabupaten', 'provinsi_id', 'nama_kabupaten'])
            : null;

        $kecamatan = $kkItem?->kecamatan
            ? \App\Models\Kecamatan::when($kabupaten, fn ($query) => $query->where('kabupaten_id', $kabupaten->id_kabupaten))
                ->where('nama_kecamatan', $kkItem->kecamatan)
                ->first(['id_kecamatan', 'kabupaten_id', 'nama_kecamatan'])
            : null;

        $desa = $kkItem?->desa_kelurahan
            ? \App\Models\DesaKelurahan::when($kecamatan, fn ($query) => $query->where('kecamatan_id', $kecamatan->id_kecamatan))
                ->where('nama_desa', $kkItem->desa_kelurahan)
                ->first(['id_desa', 'kecamatan_id', 'nama_desa'])
            : null;

        $kecamatan = $kecamatan
            ?: ($desa ? \App\Models\Kecamatan::where('id_kecamatan', $desa->kecamatan_id)->first(['id_kecamatan', 'kabupaten_id', 'nama_kecamatan']) : null)
            ?: $defaultParoki?->kecamatan;

        $kabupaten = $kabupaten
            ?: ($kecamatan ? \App\Models\Kabupaten::where('id_kabupaten', $kecamatan->kabupaten_id)->first(['id_kabupaten', 'provinsi_id', 'nama_kabupaten']) : null)
            ?: $defaultParoki?->kabupaten;

        $provinsi = $provinsi
            ?: ($kabupaten ? \App\Models\Provinsi::where('id_provinsi', $kabupaten->provinsi_id)->first(['id_provinsi', 'nama_provinsi']) : null)
            ?: $defaultParoki?->provinsi;

        $desa = $desa ?: $defaultParoki?->desa;

        return [
            'provinsi_id' => $provinsi?->id_provinsi,
            'kabupaten_id' => $kabupaten?->id_kabupaten,
            'kecamatan_id' => $kecamatan?->id_kecamatan,
            'desa_id' => $desa?->id_desa,
            'provinsi' => $provinsi?->nama_provinsi,
            'kota_kabupaten' => $kabupaten?->nama_kabupaten,
            'kecamatan' => $kecamatan?->nama_kecamatan,
            'desa_kelurahan' => $desa?->nama_desa,
        ];
    }


    protected function validateKkRequest(Request $request, $item = null): void
    {
        $ignoreId = $item?->id;
        $unique = fn (string $column) => \Illuminate\Validation\Rule::unique('kk_katolik', $column)->ignore($ignoreId);

        // Sanitize incoming anggota fields before running validation
        $rawAnggota = $request->input('anggota');
        if (is_array($rawAnggota)) {
            $cleaned = [];
            foreach ($rawAnggota as $idx => $m) {
                if (!is_array($m)) continue;
                foreach ($m as $k => $v) {
                    if (is_string($v) && trim($v) === '') {
                        $m[$k] = null;
                    }
                }
                $cleaned[$idx] = $m;
            }
            $request->merge(['anggota' => $cleaned]);
        }

        // Auto-assign KUB & Wilayah if logged in as KUB role or if kub_id is provided
        $user = $request->user();
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? 'superadmin';
        $isKubRoleOrPrefix = $firstSegment === 'kub' || ($user && ($user->role?->slug === 'ketua_kub' || in_array((int)$user->role_id, [6], true) || str_contains(strtolower($user->role?->nama_role ?? ''), 'kub')));
        
        if ($isKubRoleOrPrefix) {
            $effectiveKubId = $user?->kub_id ?: ($firstSegment === 'kub' ? (session('simulated_kub_id') ?: $request->input('kub_id')) : null);
            if ($effectiveKubId) {
                $userKub = \App\Models\Kub::find($effectiveKubId);
                $reqData = ['kub_id' => $effectiveKubId];
                if ($userKub?->kapela_id) {
                    $reqData['kapela_id'] = $userKub->kapela_id;
                    $reqData['wilayah_id'] = null;
                } elseif ($userKub?->wilayah_id) {
                    $reqData['wilayah_id'] = $userKub->wilayah_id;
                    $reqData['kapela_id'] = null;
                }
                $request->merge($reqData);
            }
        } elseif ($request->input('kub_id')) {
            $selKub = \App\Models\Kub::find($request->input('kub_id'));
            if ($selKub) {
                if ($selKub->kapela_id) {
                    $request->merge(['kapela_id' => $selKub->kapela_id]);
                    if (!$request->input('wilayah_id')) {
                        $request->merge(['wilayah_id' => null]);
                    }
                } elseif ($selKub->wilayah_id) {
                    $request->merge(['wilayah_id' => $selKub->wilayah_id]);
                    if (!$request->input('kapela_id')) {
                        $request->merge(['kapela_id' => null]);
                    }
                }
            }
        }

        $request->validate([
            'no_kk_kw' => ['required', 'string', 'max:50', $unique('no_kk_kw')],
            'no_kk_dukcapil' => ['nullable', 'string', 'max:30', $unique('no_kk_dukcapil')],
            'nik_pemilik' => ['required', 'string', 'max:30', $unique('nik_pemilik')],
            'nama_baptis_pemilik' => ['required', 'string', 'max:150'],
            'nama_lahir_pemilik' => ['required', 'string', 'max:150'],
            'nama_pasangan' => ['nullable', 'string', 'max:150'],
            'wilayah_id' => ['nullable', 'integer'],
            'kub_id' => ['nullable', 'integer'],
            'kapela_id' => ['nullable', 'integer'],
            'lingkungan_id' => ['nullable', 'integer'],
            'paroki_id' => ['nullable', 'integer'],
            'alamat_sekarang' => ['required', 'string', 'max:500'],
            'handphone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'status_verifikasi' => ['nullable', 'string'],
            'status_kk' => ['nullable', 'string'],
            'anggota' => ['nullable', 'array'],
            'anggota.*.id' => ['nullable'],
            'anggota.*.nama_lengkap' => ['nullable', 'string', 'max:150'],
            'anggota.*.nama_baptis' => ['nullable', 'string', 'max:150'],
            'anggota.*.nik' => ['nullable', 'string', 'max:30'],
            'anggota.*.hubungan_keluarga' => ['nullable', 'string', 'max:50'],
            'anggota.*.jenis_kelamin' => ['nullable', 'string', 'max:30'],
            'anggota.*.tanggal_lahir' => ['nullable'],
            'anggota.*.tempat_lahir' => ['nullable', 'string', 'max:120'],
            'anggota.*.status_perkawinan' => ['nullable', 'string', 'max:80'],
        ]);

        $seenNiks = [];
        foreach (array_values($request->input('anggota', [])) as $idx => $member) {
            if (!is_array($member)) {
                continue;
            }

            $nik = preg_replace('/\D+/', '', (string) ($member['nik'] ?? ''));
            if ($nik === '') {
                continue;
            }

            if (in_array($nik, $seenNiks, true)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "anggota.{$idx}.nik" => "NIK anggota {$nik} duplikat di dalam daftar anggota KK ini.",
                ]);
            }
            $seenNiks[] = $nik;

            $memberId = !empty($member['id']) ? (decode_id($member['id']) ?: $member['id']) : null;
            $query = \App\Models\Umat::where('nik', $nik);
            if ($memberId) {
                $query->where('id', '!=', $memberId);
            }
            if ($ignoreId) {
                $query->where(function ($q) use ($ignoreId) {
                    $q->where('kk_id', '!=', $ignoreId)->orWhereNull('kk_id');
                });
            }

            if ($query->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "anggota.{$idx}.nik" => "NIK anggota {$nik} sudah digunakan oleh data umat lain di luar KK ini.",
                ]);
            }
        }
    }


    protected function normalizeKkPayload(array $data, bool $isCreate, $item = null): array
    {
        unset($data['anggota']);

        foreach (['no_kk_dukcapil', 'nik_pemilik', 'handphone'] as $field) {
            if (!empty($data[$field])) {
                $data[$field] = preg_replace('/\D+/', '', (string) $data[$field]);
            }
        }

        // Auto-assign KUB & Wilayah for KUB role user or resolve hierarchy from selected KUB
        $user = auth()->user();
        if ($user && ($user->role?->slug === 'ketua_kub' || in_array((int)$user->role_id, [6], true) || str_contains(strtolower($user->role?->nama_role ?? ''), 'kub'))) {
            if ($user->kub_id) {
                $data['kub_id'] = $user->kub_id;
                $userKub = \App\Models\Kub::find($user->kub_id);
                if ($userKub) {
                    if ($userKub->kapela_id) {
                        $data['kapela_id'] = $userKub->kapela_id;
                        $data['wilayah_id'] = null;
                    } elseif ($userKub->wilayah_id) {
                        $data['wilayah_id'] = $userKub->wilayah_id;
                        $data['kapela_id'] = null;
                    }
                }
            }
        } elseif (!empty($data['kub_id'])) {
            $selKub = \App\Models\Kub::find($data['kub_id']);
            if ($selKub) {
                if ($selKub->kapela_id) {
                    $data['kapela_id'] = $selKub->kapela_id;
                    if (empty($data['wilayah_id'])) {
                        $data['wilayah_id'] = null;
                    }
                } elseif ($selKub->wilayah_id) {
                    $data['wilayah_id'] = $selKub->wilayah_id;
                    if (empty($data['kapela_id'])) {
                        $data['kapela_id'] = null;
                    }
                }
            }
        }

        foreach (['wilayah_id', 'kub_id', 'kapela_id', 'paroki_id', 'lingkungan_id'] as $fk) {
            if (array_key_exists($fk, $data) && ($data[$fk] === '' || $data[$fk] === 'null')) {
                $data[$fk] = null;
            }
        }

        $data['status_kk'] = $data['status_kk'] ?? 'Aktif';
        $data['status_verifikasi'] = $data['status_verifikasi'] ?? 'Belum';

        if (empty($data['nama_baptis_pemilik']) && !empty($data['nama_lahir_pemilik'])) {
            $data['nama_baptis_pemilik'] = $data['nama_lahir_pemilik'];
        }
        if (empty($data['nama_lahir_pemilik']) && !empty($data['nama_baptis_pemilik'])) {
            $data['nama_lahir_pemilik'] = $data['nama_baptis_pemilik'];
        }

        foreach (['nama_lahir_pemilik', 'nama_baptis_pemilik', 'nama_pasangan'] as $nameField) {
            if (!empty($data[$nameField])) {
                $data[$nameField] = mb_convert_case(mb_strtolower(trim($data[$nameField]), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
            }
        }

        if ($isCreate && empty($data['created_by']) && auth()->id()) {
            $data['created_by'] = auth()->id();
        }
        if (!$isCreate && auth()->id()) {
            $data['updated_by'] = auth()->id();
        }

        return $data;
    }


    protected function syncKkAnggota(\App\Models\KkKatolik $kk, $members): void
    {
        if (!is_array($members)) {
            return;
        }

        $validColumns = $this->schemaColumns('umat');
        $keepIds = [];

        // Helper sanitize date to YYYY-MM-DD or null
        $cleanDate = function ($rawDate) {
            if (empty($rawDate)) return null;
            $str = trim((string)$rawDate);
            if ($str === '' || $str === 'null' || $str === '0000-00-00') return null;
            if (str_contains($str, 'T')) {
                $str = explode('T', $str)[0];
            }
            $time = strtotime($str);
            return $time !== false ? date('Y-m-d', $time) : null;
        };

        // Helper normalize gender
        $normalizeGender = function ($rawJk) {
            $jk = strtolower(trim((string)$rawJk));
            if (in_array($jk, ['l', 'laki-laki', 'pria'], true)) {
                return 'Laki-Laki';
            }
            if (in_array($jk, ['p', 'perempuan', 'wanita'], true)) {
                return 'Perempuan';
            }
            return !empty($rawJk) ? trim((string)$rawJk) : null;
        };

        foreach (array_values($members) as $idx => $member) {
            if (!is_array($member)) {
                continue;
            }

            $namaLengkap = trim((string) ($member['nama_lengkap'] ?? $member['nama_lahir'] ?? ''));
            $namaBaptis = trim((string) ($member['nama_baptis'] ?? ''));
            $nik = preg_replace('/\D+/', '', (string) ($member['nik'] ?? ''));

            if ($namaLengkap === '' && $namaBaptis === '' && $nik === '') {
                continue;
            }

            $toProperName = function ($s) {
                if (empty($s)) return null;
                return mb_convert_case(mb_strtolower(trim((string)$s), 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
            };

            $namaLengkapFormatted = $toProperName($namaLengkap ?: $namaBaptis);
            $namaBaptisFormatted = $toProperName($namaBaptis ?: $namaLengkap);

            $payload = [
                'kk_id' => $kk->id,
                'no_urut_anggota' => $idx + 1,
                'kode_anggota' => !empty($member['kode_anggota']) ? trim($member['kode_anggota']) : null,
                'suku_etnis' => !empty($member['suku_etnis']) ? trim($member['suku_etnis']) : null,
                'nik' => $nik ?: null,
                'nama_lengkap' => $namaLengkapFormatted,
                'nama_lahir' => $namaLengkapFormatted,
                'nama_baptis' => $namaBaptisFormatted,
                'no_kk_kw' => $kk->no_kk_kw,
                'nama_pemilik_kk' => $toProperName($kk->nama_lahir_pemilik ?: $kk->nama_baptis_pemilik),
                'hubungan_keluarga' => !empty($member['hubungan_keluarga']) ? trim($member['hubungan_keluarga']) : ($idx === 0 ? 'Kepala Keluarga' : 'Anak'),
                'jenis_kelamin' => $normalizeGender($member['jenis_kelamin'] ?? null),
                'tempat_lahir' => !empty($member['tempat_lahir']) ? trim($member['tempat_lahir']) : null,
                'tanggal_lahir' => $cleanDate($member['tanggal_lahir'] ?? null),
                'status_menikah' => !empty($member['status_perkawinan']) ? trim($member['status_perkawinan']) : (!empty($member['status_menikah']) ? trim($member['status_menikah']) : null),
                'status_perkawinan' => !empty($member['status_perkawinan']) ? trim($member['status_perkawinan']) : null,
                'agama_asal' => !empty($member['agama_asal']) ? trim($member['agama_asal']) : null,
                'pendidikan_saat_ini' => !empty($member['pendidikan']) ? trim($member['pendidikan']) : (!empty($member['pendidikan_saat_ini']) ? trim($member['pendidikan_saat_ini']) : null),
                'pendidikan' => !empty($member['pendidikan']) ? trim($member['pendidikan']) : (!empty($member['pendidikan_saat_ini']) ? trim($member['pendidikan_saat_ini']) : null),
                'pekerjaan' => !empty($member['pekerjaan']) ? trim($member['pekerjaan']) : null,
                'golongan_darah' => !empty($member['golongan_darah']) ? trim($member['golongan_darah']) : null,
                'talenta' => !empty($member['talenta']) ? trim($member['talenta']) : null,
                'disabilitas' => !empty($member['disabilitas']) ? trim($member['disabilitas']) : null,
                'status_baptis' => !empty($member['status_baptis']) ? trim($member['status_baptis']) : null,
                'jenis_penerimaan_baptis' => !empty($member['jenis_penerimaan_baptis']) ? trim($member['jenis_penerimaan_baptis']) : null,
                'tgl_baptis' => $cleanDate($member['tgl_baptis'] ?? null),
                'paroki_baptis' => !empty($member['paroki_baptis']) ? trim($member['paroki_baptis']) : null,
                'pastor_baptis' => $toProperName($member['pastor_baptis'] ?? null),
                'wali_baptis' => $toProperName($member['wali_baptis'] ?? null),
                'buku_baptis_vol' => !empty($member['buku_baptis_vol']) ? trim($member['buku_baptis_vol']) : null,
                'buku_baptis_hal' => !empty($member['buku_baptis_hal']) ? trim($member['buku_baptis_hal']) : null,
                'buku_baptis_no' => !empty($member['buku_baptis_no']) ? trim($member['buku_baptis_no']) : null,
                'tgl_komuni_1' => $cleanDate($member['tgl_komuni_1'] ?? null),
                'paroki_komuni_1' => !empty($member['paroki_komuni_1']) ? trim($member['paroki_komuni_1']) : null,
                'tgl_krisma' => $cleanDate($member['tgl_krisma'] ?? null),
                'paroki_krisma' => !empty($member['paroki_krisma']) ? trim($member['paroki_krisma']) : null,
                'tgl_perkawinan' => $cleanDate($member['tgl_perkawinan'] ?? null),
                'paroki_perkawinan' => !empty($member['paroki_perkawinan']) ? trim($member['paroki_perkawinan']) : null,
                'nama_pasangan' => $toProperName($member['nama_pasangan'] ?? null),
                'status_perkawinan_kanonik' => !empty($member['status_perkawinan_kanonik']) ? trim($member['status_perkawinan_kanonik']) : null,
                'peristiwa_lain' => !empty($member['peristiwa_lain']) ? trim($member['peristiwa_lain']) : null,
                'no_surat_peristiwa' => !empty($member['no_surat_peristiwa']) ? trim($member['no_surat_peristiwa']) : null,
                'status_panggilan' => !empty($member['status_panggilan']) ? trim($member['status_panggilan']) : 'Awam',
                'nama_ordo_kongregasi' => !empty($member['nama_ordo_kongregasi']) ? trim($member['nama_ordo_kongregasi']) : null,
                'tahap_panggilan' => !empty($member['tahap_panggilan']) ? trim($member['tahap_panggilan']) : null,
                'tempat_tugas_biara' => !empty($member['tempat_tugas_biara']) ? trim($member['tempat_tugas_biara']) : null,
                'tgl_tahbisan_kaul' => $cleanDate($member['tgl_tahbisan_kaul'] ?? null),
                'wilayah_id' => $kk->wilayah_id ?: null,
                'kub_id' => $kk->kub_id ?: null,
                'kapela_id' => $kk->kapela_id ?: null,
                'lingkungan_id' => $kk->lingkungan_id ?: null,
                'status_aktif' => 1,
                'status_umat' => 'Aktif',
                'handphone' => !empty($member['handphone']) ? trim($member['handphone']) : null,
                'email' => !empty($member['email']) ? trim($member['email']) : null,
                'updated_by' => auth()->id(),
            ];

            $memberId = !empty($member['id']) ? (decode_id($member['id']) ?: $member['id']) : null;
            $umat = null;
            if ($memberId) {
                $umat = \App\Models\Umat::where('id', $memberId)->first();
            }
            if (!$umat && $nik) {
                $umat = \App\Models\Umat::where('nik', $nik)->where('kk_id', $kk->id)->first();
            }

            $cleanPayload = array_intersect_key($payload, array_flip($validColumns));

            if ($umat) {
                $umat->update($cleanPayload);
            } else {
                if (in_array('created_by', $validColumns, true)) {
                    $cleanPayload['created_by'] = auth()->id();
                }
                $umat = \App\Models\Umat::create($cleanPayload);
            }

            $keepIds[] = $umat->id;
        }

        if (!empty($keepIds)) {
            $leavingMembers = \App\Models\Umat::where('kk_id', $kk->id)->whereNotIn('id', $keepIds)->get();
            foreach ($leavingMembers as $lm) {
                if (\Illuminate\Support\Facades\Schema::hasTable('riwayat_mutasi_umat')) {
                    \Illuminate\Support\Facades\DB::table('riwayat_mutasi_umat')->insert([
                        'umat_id' => $lm->id,
                        'kk_id' => $kk->id,
                        'jenis_mutasi' => 'Pecah KK / Keluar dari KK',
                        'status_sebelum' => 'Anggota Keluarga',
                        'status_sesudah' => 'Pecah KK / Menikah',
                        'kub_asal_id' => $kk->kub_id,
                        'wilayah_asal_id' => $kk->wilayah_id,
                        'tgl_mutasi' => now()->toDateString(),
                        'alasan' => 'Pecah KK / Menikah membentuk keluarga baru',
                        'created_by' => auth()->id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            \App\Models\Umat::where('kk_id', $kk->id)->whereNotIn('id', $keepIds)->update([
                'kk_id' => null,
                'tanggal_keluar_dari_kk' => now(),
                'updated_by' => auth()->id(),
            ]);
        }
    }


    protected function kkImportIdentityKeys(array $payload, array $validColumns): array
    {
        $keys = [];
        foreach (['no_kk_kw', 'no_kk_dukcapil', 'nik_pemilik'] as $column) {
            if (in_array($column, $validColumns, true) && !empty($payload[$column])) {
                $keys[] = Str::lower($column . ':' . trim((string) $payload[$column]));
            }
        }

        return array_values(array_unique($keys));
    }


    protected function findExistingKkForImport(string $modelClass, array $payload, array $validColumns)
    {
        $query = $modelClass::query();
        $hasIdentity = false;

        foreach (['no_kk_kw', 'no_kk_dukcapil', 'nik_pemilik'] as $column) {
            if (in_array($column, $validColumns, true) && !empty($payload[$column])) {
                $method = $hasIdentity ? 'orWhere' : 'where';
                $query->{$method}($column, trim((string) $payload[$column]));
                $hasIdentity = true;
            }
        }

        return $hasIdentity ? $query->first() : null;
    }


    protected function kkMemberImportHeadings(int $count = 2): array
    {
        $fields = [
            'Hubungan Keluarga',
            'NIK',
            'Nama Lengkap Sipil',
            'Nama Baptis Santo/Santa',
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Golongan Darah',
            'Agama Asal',
            'Pendidikan',
            'Pekerjaan',
            'Bidang Keahlian / Talenta Paroki',
            'Disabilitas/Kebutuhan Khusus',
            'Status Baptis',
            'Jenis Penerimaan Baptis',
            'Tanggal Baptis',
            'Paroki Tempat Baptis',
            'Pastor Pembaptis',
            'Nama Wali Baptis',
            'Buku Baptis Vol',
            'Buku Baptis Hal',
            'Buku Baptis No',
            'Tanggal Krisma',
            'Paroki Krisma',
            'Tanggal Perkawinan',
            'Paroki Perkawinan',
            'Nama Pasangan',
            'Status Perkawinan Kanonik',
            'Peristiwa Lain',
            'No Surat Peristiwa',
        ];

        $headings = [];
        for ($i = 1; $i <= $count; $i++) {
            foreach ($fields as $field) {
                $headings[] = "Anggota {$i} - {$field}";
            }
        }

        return $headings;
    }


    protected function kkMemberExportValues($kk, int $count = 2): array
    {
        $members = collect($kk->anggota ?? [])->values();
        $values = [];

        for ($i = 0; $i < $count; $i++) {
            $member = $members->get($i);
            $values = array_merge($values, [
                $member->hubungan_keluarga ?? '',
                $member->nik ?? '',
                $member->nama_lengkap ?? $member->nama_lahir ?? '',
                $member->nama_baptis ?? '',
                $member->jenis_kelamin ?? '',
                $member->tempat_lahir ?? '',
                $this->formatNullableDateForExport($member->tanggal_lahir ?? null),
                $member->golongan_darah ?? '',
                $member->agama_asal ?? $member->agama_saat_ini ?? '',
                $member->pendidikan_saat_ini ?? '',
                $member->pekerjaan ?? '',
                $member->talenta ?? '',
                $member->disabilitas ?? '',
                $member->status_baptis ?? '',
                $member->jenis_penerimaan_baptis ?? '',
                $member->tgl_baptis ?? '',
                $member->paroki_baptis ?? '',
                $member->pastor_baptis ?? '',
                $member->wali_baptis ?? '',
                $member->buku_baptis_vol ?? '',
                $member->buku_baptis_hal ?? '',
                $member->buku_baptis_no ?? '',
                $member->tgl_krisma ?? '',
                $member->paroki_krisma ?? '',
                $member->tgl_perkawinan ?? '',
                $member->paroki_perkawinan ?? '',
                $member->nama_pasangan ?? '',
                $member->status_perkawinan_kanonik ?? $member->status_perkawinan ?? '',
                $member->peristiwa_lain ?? '',
                $member->no_surat_peristiwa ?? '',
            ]);
        }

        return $values;
    }


    protected function extractKkImportMembers(array $row, array $rawHeaders): array
    {
        $members = [];
        $mapping = [
            'hubungan_keluarga' => 'hubungan_keluarga',
            'hubungan' => 'hubungan_keluarga',
            'nik' => 'nik',
            'no_ktp' => 'nik',
            'nama_lengkap_sipil' => 'nama_lengkap',
            'nama_lengkap' => 'nama_lengkap',
            'nama_lahir' => 'nama_lengkap',
            'nama' => 'nama_lengkap',
            'nama_baptis_santo_santa' => 'nama_baptis',
            'nama_baptis' => 'nama_baptis',
            'nama_santo' => 'nama_baptis',
            'jenis_kelamin' => 'jenis_kelamin',
            'jk' => 'jenis_kelamin',
            'gender' => 'jenis_kelamin',
            'tempat_lahir' => 'tempat_lahir',
            'tanggal_lahir' => 'tanggal_lahir',
            'tgl_lahir' => 'tanggal_lahir',
            'golongan_darah' => 'golongan_darah',
            'gol_darah' => 'golongan_darah',
            'agama_asal' => 'agama_asal',
            'pendidikan' => 'pendidikan_saat_ini',
            'pendidikan_saat_ini' => 'pendidikan_saat_ini',
            'pekerjaan' => 'pekerjaan',
            'bidang_keahlian_talenta_paroki' => 'talenta',
            'talenta' => 'talenta',
            'disabilitas_kebutuhan_khusus' => 'disabilitas',
            'disabilitas' => 'disabilitas',
            'status_baptis' => 'status_baptis',
            'jenis_penerimaan_baptis' => 'jenis_penerimaan_baptis',
            'tanggal_baptis' => 'tgl_baptis',
            'tgl_baptis' => 'tgl_baptis',
            'paroki_tempat_baptis' => 'paroki_baptis',
            'paroki_baptis' => 'paroki_baptis',
            'pastor_pembaptis' => 'pastor_baptis',
            'pastor_baptis' => 'pastor_baptis',
            'nama_wali_baptis' => 'wali_baptis',
            'wali_baptis' => 'wali_baptis',
            'buku_baptis_vol' => 'buku_baptis_vol',
            'buku_baptis_hal' => 'buku_baptis_hal',
            'buku_baptis_no' => 'buku_baptis_no',
            'tanggal_krisma' => 'tgl_krisma',
            'tgl_krisma' => 'tgl_krisma',
            'paroki_krisma' => 'paroki_krisma',
            'tanggal_perkawinan' => 'tgl_perkawinan',
            'tgl_perkawinan' => 'tgl_perkawinan',
            'paroki_perkawinan' => 'paroki_perkawinan',
            'nama_pasangan' => 'nama_pasangan',
            'status_perkawinan_kanonik' => 'status_perkawinan_kanonik',
            'status_menikah' => 'status_menikah',
            'status_perkawinan' => 'status_menikah',
            'peristiwa_lain' => 'peristiwa_lain',
            'no_surat_peristiwa' => 'no_surat_peristiwa',
        ];

        foreach ($rawHeaders as $column => $heading) {
            $index = null;
            $fieldKey = null;

            if (preg_match('/^anggota_(\d+)_(.+)$/', $heading, $matches)) {
                $index = (int) $matches[1] - 1;
                $fieldKey = $matches[2];
            } elseif (preg_match('/^(.+)_anggota_(\d+)$/', $heading, $matches)) {
                $index = (int) $matches[2] - 1;
                $fieldKey = $matches[1];
            }

            if ($index === null || $fieldKey === null) {
                continue;
            }

            $target = $mapping[$fieldKey] ?? null;
            if (!$target) {
                foreach ($mapping as $mk => $mt) {
                    if (str_contains($fieldKey, $mk) || str_contains($mk, $fieldKey)) {
                        $target = $mt;
                        break;
                    }
                }
            }

            if (!$target) {
                continue;
            }

            $value = $row[$column] ?? null;
            if (is_string($value)) {
                $value = trim($value);
            }
            if ($value === '') {
                $value = null;
            }

            if (in_array($target, ['nik'], true) && $value !== null) {
                $value = preg_replace('/\.0$/', '', trim((string) $value));
                $value = preg_replace('/\D+/', '', $value);
            }

            if (in_array($target, ['tanggal_lahir', 'tgl_baptis', 'tgl_krisma', 'tgl_perkawinan'], true)) {
                $value = $this->normalizeImportDateValue($value);
            }

            $members[$index][$target] = $value;
        }

        return collect($members)
            ->sortKeys()
            ->filter(function ($member) {
                return collect($member)->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty();
            })
            ->values()
            ->all();
    }


    protected function formatNullableDateForExport($value): string
    {
        if (!$value) {
            return '';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        $timestamp = strtotime((string) $value);
        return $timestamp ? date('Y-m-d', $timestamp) : (string) $value;
    }


    protected function normalizeImportDateValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        $text = trim((string) $value);
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $text, $matches)) {
            return sprintf('%04d-%02d-%02d', (int) $matches[3], (int) $matches[2], (int) $matches[1]);
        }

        $timestamp = strtotime($text);
        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }


    protected function buildEcclesiasticalReferenceData(): array
    {
        $dekenatNames = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('dekenat')) {
            $col = \Illuminate\Support\Facades\Schema::hasColumn('dekenat', 'nama_dekenat') ? 'nama_dekenat' : (\Illuminate\Support\Facades\Schema::hasColumn('dekenat', 'nama_kevikepan') ? 'nama_kevikepan' : 'nama');
            $dekenatNames = \App\Models\Dekenat::orderBy($col)->pluck($col)->filter()->values()->all();
        } elseif (\Illuminate\Support\Facades\Schema::hasTable('kevikepan')) {
            $col = \Illuminate\Support\Facades\Schema::hasColumn('kevikepan', 'nama_kevikepan') ? 'nama_kevikepan' : (\Illuminate\Support\Facades\Schema::hasColumn('kevikepan', 'nama_dekenat') ? 'nama_dekenat' : 'nama');
            $dekenatNames = \Illuminate\Support\Facades\DB::table('kevikepan')->orderBy($col)->pluck($col)->filter()->values()->all();
        }

        $wilayahNames = \Illuminate\Support\Facades\Schema::hasTable('wilayah')
            ? \App\Models\Wilayah::orderBy(\Illuminate\Support\Facades\Schema::hasColumn('wilayah', 'nama_wilayah') ? 'nama_wilayah' : 'nama')->pluck(\Illuminate\Support\Facades\Schema::hasColumn('wilayah', 'nama_wilayah') ? 'nama_wilayah' : 'nama')->filter()->values()->all()
            : [];

        $kubNames = \Illuminate\Support\Facades\Schema::hasTable('kub')
            ? \App\Models\Kub::orderBy(\Illuminate\Support\Facades\Schema::hasColumn('kub', 'nama_kub') ? 'nama_kub' : 'nama')->pluck(\Illuminate\Support\Facades\Schema::hasColumn('kub', 'nama_kub') ? 'nama_kub' : 'nama')->filter()->values()->all()
            : [];

        $kapelaNames = \Illuminate\Support\Facades\Schema::hasTable('kapela')
            ? \App\Models\Kapela::orderBy(\Illuminate\Support\Facades\Schema::hasColumn('kapela', 'nama_kapela') ? 'nama_kapela' : (\Illuminate\Support\Facades\Schema::hasColumn('kapela', 'nama_stasi_kapela') ? 'nama_stasi_kapela' : 'nama'))->pluck(\Illuminate\Support\Facades\Schema::hasColumn('kapela', 'nama_kapela') ? 'nama_kapela' : (\Illuminate\Support\Facades\Schema::hasColumn('kapela', 'nama_stasi_kapela') ? 'nama_stasi_kapela' : 'nama'))->filter()->values()->all()
            : [];

        $lingkunganNames = \Illuminate\Support\Facades\Schema::hasTable('lingkungan')
            ? \App\Models\Lingkungan::orderBy(\Illuminate\Support\Facades\Schema::hasColumn('lingkungan', 'nama_lingkungan') ? 'nama_lingkungan' : 'nama')->pluck(\Illuminate\Support\Facades\Schema::hasColumn('lingkungan', 'nama_lingkungan') ? 'nama_lingkungan' : 'nama')->filter()->values()->all()
            : [];

        $parokiNames = \Illuminate\Support\Facades\Schema::hasTable('paroki')
            ? \App\Models\Paroki::orderBy(\Illuminate\Support\Facades\Schema::hasColumn('paroki', 'nama_paroki') ? 'nama_paroki' : 'nama')->pluck(\Illuminate\Support\Facades\Schema::hasColumn('paroki', 'nama_paroki') ? 'nama_paroki' : 'nama')->filter()->values()->all()
            : [];

        return [
            'Wilayah Pastoral' => !empty($wilayahNames) ? $wilayahNames : ['Wilayah I - St. Petrus', 'Wilayah II - St. Paulus'],
            'KUB / KBG' => !empty($kubNames) ? $kubNames : ['KUB Sta. Maria', 'KUB St. Yosef'],
            'Stasi / Kapela' => !empty($kapelaNames) ? $kapelaNames : ['Kapela St. Fransiskus'],
            'Lingkungan' => !empty($lingkunganNames) ? $lingkunganNames : ['Lingkungan St. Yohanes', 'Lingkungan St. Gabriel'],
            'Paroki' => !empty($parokiNames) ? $parokiNames : ['Paroki St. Vinsensius a Paulo Benlutu'],
            'Kevikepan / Dekenat' => !empty($dekenatNames) ? $dekenatNames : ['Dekenat Timor Tengah Selatan (TTS)'],
            'Kepemilikan Rumah' => ['Milik Sendiri', 'Sewa / Kontrak', 'Ikut Orang Tua', 'Rumah Dinas'],
            'Kategori Ekonomi Pastoral' => ['Prasejahtera', 'Sejahtera / Mandiri', 'Mampu'],
            'Jenis Penerimaan Baptis (KHK 849)' => ['Baptis Bayi (Infantis)', 'Baptis Dewasa (Adultus)', 'Receptio (Penerimaan ke Katolik)'],
            'Status Perkawinan Kanonik (KHK 1055)' => ['Katolik Organik', 'Beda Agama (Dispensasi)', 'Beda Gereja (Izin)'],
            'Disabilitas / Khusus' => ['Tidak Ada', 'Rungu / Wicara', 'Netra', 'Daksa / Fisik', 'Mental', 'Lansia Perawatan'],
            'Agama Asal' => ['Katolik sejak lahir', 'Katekumen', 'Protestan', 'Islam', 'Hindu', 'Budha', 'Lainnya'],
            'Status KK' => ['Aktif', 'Pindah KUB', 'Pindah Wilayah', 'Pindah Paroki', 'Pecah KK', 'Tidak Aktif'],
            'Status Verifikasi' => ['Terverifikasi', 'Belum', 'Ditolak'],
        ];
    }


    protected function buildCivilReferenceData(): array
    {
        $provNames = \Illuminate\Support\Facades\Schema::hasTable('provinsi') ? \App\Models\Provinsi::orderBy(\Illuminate\Support\Facades\Schema::hasColumn('provinsi', 'nama_provinsi') ? 'nama_provinsi' : 'nama')->pluck(\Illuminate\Support\Facades\Schema::hasColumn('provinsi', 'nama_provinsi') ? 'nama_provinsi' : 'nama')->filter()->values()->all() : [];
        $kabNames = \Illuminate\Support\Facades\Schema::hasTable('kabupaten') ? \App\Models\Kabupaten::orderBy(\Illuminate\Support\Facades\Schema::hasColumn('kabupaten', 'nama_kabupaten') ? 'nama_kabupaten' : 'nama')->pluck(\Illuminate\Support\Facades\Schema::hasColumn('kabupaten', 'nama_kabupaten') ? 'nama_kabupaten' : 'nama')->filter()->values()->all() : [];
        $kecNames = \Illuminate\Support\Facades\Schema::hasTable('kecamatan') ? \App\Models\Kecamatan::take(100)->orderBy(\Illuminate\Support\Facades\Schema::hasColumn('kecamatan', 'nama_kecamatan') ? 'nama_kecamatan' : 'nama')->pluck(\Illuminate\Support\Facades\Schema::hasColumn('kecamatan', 'nama_kecamatan') ? 'nama_kecamatan' : 'nama')->filter()->values()->all() : [];
        $desaNames = \Illuminate\Support\Facades\Schema::hasTable('desa_kelurahan') ? \App\Models\DesaKelurahan::take(250)->orderBy(\Illuminate\Support\Facades\Schema::hasColumn('desa_kelurahan', 'nama_desa') ? 'nama_desa' : 'nama')->pluck(\Illuminate\Support\Facades\Schema::hasColumn('desa_kelurahan', 'nama_desa') ? 'nama_desa' : 'nama')->filter()->values()->all() : [];

        return [
            'Provinsi' => !empty($provNames) ? $provNames : ['Nusa Tenggara Timur'],
            'Kabupaten / Kota' => !empty($kabNames) ? $kabNames : ['Kabupaten Timor Tengah Selatan', 'Kota Kupang'],
            'Kecamatan' => !empty($kecNames) ? $kecNames : ['Kecamatan Batu Putih', 'Kecamatan Kota Soe'],
            'Desa / Kelurahan' => !empty($desaNames) ? $desaNames : ['Desa Benlutu', 'Desa Oebobo'],
            'Hubungan Keluarga' => ['Kepala Keluarga', 'Istri', 'Anak', 'Orang Tua', 'Mertua', 'Menantu', 'Cucu', 'Famili Lain'],
            'Pekerjaan' => ['PNS / ASN', 'TNI / Polri', 'Karyawan Swasta', 'Wiraswasta / Pedagang', 'Petani / Pekebun', 'Peternak', 'Nelayan', 'Guru / Dosen', 'Tenaga Medis / Perawat / Dokter', 'Tukang / Buruh Bangunan', 'Pelajar / Mahasiswa', 'Ibu Rumah Tangga', 'Pensiunan', 'Belum / Tidak Bekerja', 'Lainnya'],
            'Pendidikan' => ['Tidak / Belum Sekolah', 'SD / Sederajat', 'SMP / Sederajat', 'SMA / SMK / Sederajat', 'Diploma (D1-D3)', 'Sarjana (S1)', 'Magister (S2)', 'Doktoral (S3)'],
            'Golongan Darah' => ['A', 'B', 'AB', 'O', 'Tidak Tahu'],
            'Status Perkawinan' => ['Belum Menikah', 'Menikah Katolik', 'Menikah Campur Beda Agama', 'Menikah Campur Beda Gereja', 'Duda', 'Janda'],
            'Penghasilan / Ekonomi' => ['< Rp 1.000.000', 'Rp 1.000.000 - Rp 3.000.000', 'Rp 3.000.000 - Rp 5.000.000', 'Rp 5.000.000 - Rp 10.000.000', '> Rp 10.000.000'],
            'Suku / Etnis' => ['Timor / Dawan', 'Rote', 'Sabu', 'Flores / Manggarai', 'Sumba', 'Jawa', 'Tionghoa', 'Lainnya'],
        ];
    }


    protected function ensureKkKatolikColumns(): void
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('kk_katolik')) {
                \Illuminate\Support\Facades\Schema::table('kk_katolik', function ($table) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('kk_katolik', 'status_kepemilikan_rumah')) {
                        $table->string('status_kepemilikan_rumah', 50)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('kk_katolik', 'kategori_ekonomi')) {
                        $table->string('kategori_ekonomi', 50)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('kk_katolik', 'bantuan_pastoral')) {
                        $table->text('bantuan_pastoral')->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('kk_katolik', 'pekerjaan')) {
                        $table->string('pekerjaan', 150)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('kk_katolik', 'pendidikan')) {
                        $table->string('pendidikan', 100)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('kk_katolik', 'golongan_darah')) {
                        $table->string('golongan_darah', 20)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('kk_katolik', 'penghasilan')) {
                        $table->string('penghasilan', 100)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('kk_katolik', 'lingkungan_id')) {
                        $table->unsignedInteger('lingkungan_id')->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('kk_katolik', 'kub_id')) {
                        $table->unsignedInteger('kub_id')->nullable();
                    }
                });
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('umat')) {
                \Illuminate\Support\Facades\Schema::table('umat', function ($table) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'kode_anggota')) {
                        $table->string('kode_anggota', 50)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'suku_etnis')) {
                        $table->string('suku_etnis', 100)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'agama_asal')) {
                        $table->string('agama_asal', 50)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'talenta')) {
                        $table->string('talenta', 255)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'disabilitas')) {
                        $table->string('disabilitas', 50)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'status_baptis')) {
                        $table->string('status_baptis', 50)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'jenis_penerimaan_baptis')) {
                        $table->string('jenis_penerimaan_baptis', 50)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'tgl_baptis')) {
                        $table->date('tgl_baptis')->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'paroki_baptis')) {
                        $table->string('paroki_baptis', 150)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'pastor_baptis')) {
                        $table->string('pastor_baptis', 150)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'wali_baptis')) {
                        $table->string('wali_baptis', 150)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'buku_baptis_vol')) {
                        $table->string('buku_baptis_vol', 20)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'buku_baptis_hal')) {
                        $table->string('buku_baptis_hal', 20)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'buku_baptis_no')) {
                        $table->string('buku_baptis_no', 20)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'tgl_komuni_1')) {
                        $table->date('tgl_komuni_1')->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'paroki_komuni_1')) {
                        $table->string('paroki_komuni_1', 150)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'tgl_krisma')) {
                        $table->date('tgl_krisma')->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'paroki_krisma')) {
                        $table->string('paroki_krisma', 150)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'tgl_perkawinan')) {
                        $table->date('tgl_perkawinan')->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'paroki_perkawinan')) {
                        $table->string('paroki_perkawinan', 150)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'nama_pasangan')) {
                        $table->string('nama_pasangan', 150)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'status_perkawinan_kanonik')) {
                        $table->string('status_perkawinan_kanonik', 50)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'no_surat_peristiwa')) {
                        $table->string('no_surat_peristiwa', 100)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'status_panggilan')) {
                        $table->string('status_panggilan', 100)->nullable()->default('Awam');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'nama_ordo_kongregasi')) {
                        $table->string('nama_ordo_kongregasi', 150)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'tahap_panggilan')) {
                        $table->string('tahap_panggilan', 100)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'tempat_tugas_biara')) {
                        $table->string('tempat_tugas_biara', 255)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'tgl_tahbisan_kaul')) {
                        $table->date('tgl_tahbisan_kaul')->nullable();
                    }
                });
            }
            \Illuminate\Support\Facades\Cache::forget('schema_columns_kk_katolik');
            \Illuminate\Support\Facades\Cache::forget('schema_columns_umat');
        } catch (\Throwable $e) {
            // Silently continue
        }
    }

}
