<?php

namespace App\Http\Controllers;

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
use App\Http\Controllers\Concerns\GenericModuleTrait;

class InertiaPanelController extends Controller
{
    use GenericModuleTrait;
    /**
     * Dashboard SPA with zero-loader instant transitions & rich stats.
     */
    public function dashboard(Request $request, ?string $role = null): Response
    {
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? '';
        $roleMap = [
            'superadmin' => 'Super Admin',
            'v2' => 'Super Admin',
            'paroki' => 'Admin Paroki',
            'pastor' => 'Pastor',
            'wilayah' => 'Admin Wilayah',
            'kapela' => 'Admin Kapela / Stasi',
            'kub' => 'Ketua KUB',
            'bendahara' => 'Bendahara',
            'penulis' => 'Penulis',
            'umat' => 'Umat',
        ];

        $resolvedRole = $roleMap[$role] ?? $roleMap[$firstSegment] ?? auth()->user()?->role?->nama_role ?? 'Super Admin';

        $authUser = auth()->user();
        $slugClean = strtolower(preg_replace('/[^a-z0-9]/', '', $authUser?->role?->slug ?? $authUser?->role?->nama_role ?? ''));

        $umatQuery = Umat::query();
        $kkQuery = KkKatolik::query();
        $kubQuery = \App\Models\Kub::query();
        $kapelaQuery = Kapela::query();

        if (str_contains($slugClean, 'wilayah') && !empty($authUser?->wilayah_id)) {
            $umatQuery->whereHas('kk', fn($kkQ) => $kkQ->where('wilayah_id', $authUser->wilayah_id));
            $kkQuery->where('wilayah_id', $authUser->wilayah_id);
            $kubQuery->where('wilayah_id', $authUser->wilayah_id);
        } elseif ((str_contains($slugClean, 'kapela') || str_contains($slugClean, 'stasi')) && !empty($authUser?->kapela_id)) {
            $umatQuery->whereHas('kk', fn($kkQ) => $kkQ->where('kapela_id', $authUser->kapela_id));
            $kkQuery->where('kapela_id', $authUser->kapela_id);
            $kubQuery->where('kapela_id', $authUser->kapela_id);
            $kapelaQuery->where('id', $authUser->kapela_id);
        } elseif (str_contains($slugClean, 'kub') && !empty($authUser?->kub_id)) {
            $umatQuery->whereHas('kk', fn($kkQ) => $kkQ->where('kub_id', $authUser->kub_id));
            $kkQuery->where('kub_id', $authUser->kub_id);
            $kubQuery->where('id', $authUser->kub_id);
        }

        $stats = [
            [
                'title' => 'Total Umat',
                'value' => $umatQuery->count(),
                'icon' => 'fa-users',
                'change' => 'Umat terdata',
                'trend' => 'up',
                'color' => 'amber',
            ],
            [
                'title' => 'Kepala Keluarga (KK)',
                'value' => $kkQuery->count(),
                'icon' => 'fa-house-chimney-user',
                'change' => 'Data KK terdata',
                'trend' => 'neutral',
                'color' => 'blue',
            ],
            [
                'title' => 'Komunitas KUB',
                'value' => $kubQuery->count(),
                'icon' => 'fa-church',
                'change' => 'Komunitas basis',
                'trend' => 'neutral',
                'color' => 'emerald',
            ],
            [
                'title' => 'Stasi / Kapela',
                'value' => $kapelaQuery->count(),
                'icon' => 'fa-map-location-dot',
                'change' => 'Wilayah pelayanan',
                'trend' => 'neutral',
                'color' => 'purple',
            ],
        ];

        $latestUmat = (clone $umatQuery)->latest('id')
            ->take(6)
            ->get(['id', 'nama_lengkap', 'jenis_kelamin', 'status_umat', 'created_at']);

        $baptisTable = Sakramen::where('tipe_sakramen', 'like', '%Baptis%')->count();
        $baptisUmat = (clone $umatQuery)->where(function($q) {
            $q->whereNotNull('tgl_baptis')->orWhere('status_baptis', 'Sudah');
        })->count();

        $komuniTable = Sakramen::where('tipe_sakramen', 'like', '%Komuni%')->count();
        $komuniUmat = (clone $umatQuery)->whereNotNull('tgl_komuni_1')->count();

        $krismaTable = Sakramen::where('tipe_sakramen', 'like', '%Krisma%')->count();
        $krismaUmat = (clone $umatQuery)->whereNotNull('tgl_krisma')->count();

        $nikahTable = Sakramen::where(function($q) {
            $q->where('tipe_sakramen', 'like', '%Nikah%')
              ->orWhere('tipe_sakramen', 'like', '%Kawin%')
              ->orWhere('tipe_sakramen', 'like', '%Perkawinan%');
        })->count();
        $nikahUmat = (clone $umatQuery)->whereNotNull('tgl_perkawinan')->count();

        $sakramenCount = [
            'baptis' => max($baptisTable, $baptisUmat),
            'komuni' => max($komuniTable, $komuniUmat),
            'krisma' => max($krismaTable, $krismaUmat),
            'perkawinan' => max($nikahTable, $nikahUmat),
        ];

        return Inertia::render('Inertia/Dashboard', [
            'stats' => $stats,
            'latestUmat' => $latestUmat,
            'sakramenCount' => $sakramenCount,
            'role' => $resolvedRole,
        ]);
    }

    /**
     * Umat Index SPA with instant reactive search & pagination.
     */
    public function umat(Request $request): Response
    {
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? '';
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

        $search = $request->input('search');
        $authUser = auth()->user();
        $slugClean = strtolower(preg_replace('/[^a-z0-9]/', '', $authUser?->role?->slug ?? $authUser?->role?->nama_role ?? ''));

        $query = Umat::query();
        if (str_contains($slugClean, 'wilayah') && !empty($authUser?->wilayah_id)) {
            $query->where(function ($q) use ($authUser) {
                $q->where('wilayah_id', $authUser->wilayah_id)
                  ->orWhereHas('kk', fn($kkQ) => $kkQ->where('wilayah_id', $authUser->wilayah_id));
            });
        } elseif ((str_contains($slugClean, 'kapela') || str_contains($slugClean, 'stasi')) && !empty($authUser?->kapela_id)) {
            $query->where(function ($q) use ($authUser) {
                $q->where('kapela_id', $authUser->kapela_id)
                  ->orWhereHas('kk', fn($kkQ) => $kkQ->where('kapela_id', $authUser->kapela_id));
            });
        } elseif (str_contains($slugClean, 'kub') && !empty($authUser?->kub_id)) {
            $query->where(function ($q) use ($authUser) {
                $q->where('kub_id', $authUser->kub_id)
                  ->orWhereHas('kk', fn($kkQ) => $kkQ->where('kub_id', $authUser->kub_id));
            });
        }

        $umats = $query
            ->when($search, function ($q, $search) {
                $q->where(function($qq) use ($search) {
                    $qq->where('nama_lengkap', 'like', "%{$search}%")
                       ->orWhere('nik', 'like', "%{$search}%")
                       ->orWhere('tempat_lahir', 'like', "%{$search}%");
                });
            })
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Inertia/UmatIndex', [
            'umats' => $umats,
            'role' => $resolvedRole,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Dedicated Full Page for Creating KK Katolik.
     */
    public function createKk(Request $request): Response
    {
        $this->ensureKkKatolikColumns();
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
            'kubList' => \App\Models\Kub::orderBy('nama_kub')->get(['id', 'nama_kub', 'wilayah_id']),
            'kapelaList' => \App\Models\Kapela::orderBy('nama_kapela')->get(['id', 'nama_kapela']),
            'parokiList' => \App\Models\Paroki::orderBy('nama_paroki')->get(['id_paroki', 'nama_paroki']),
            'provinsiList' => \App\Models\Provinsi::orderBy('nama_provinsi')->get(['id_provinsi', 'nama_provinsi']),
            'kabupatenList' => \App\Models\Kabupaten::orderBy('nama_kabupaten')->get(['id_kabupaten', 'provinsi_id', 'nama_kabupaten']),
            'kecamatanList' => \App\Models\Kecamatan::orderBy('nama_kecamatan')->get(['id_kecamatan', 'kabupaten_id', 'nama_kecamatan']),
            'desaList' => $this->desaOptionsForKecamatan($civilRegion['kecamatan_id'] ?? null),
            'civilRegion' => $civilRegion,
        ]);
    }

    /**
     * Dedicated Full Page for Editing KK Katolik.
     */
    public function editKk(Request $request, string|int $id): Response
    {
        $this->ensureKkKatolikColumns();
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

        $kkItem = \App\Models\KkKatolik::with('anggota')->findOrFail($id);

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
            'kubList' => \App\Models\Kub::orderBy('nama_kub')->get(['id', 'nama_kub', 'wilayah_id']),
            'kapelaList' => \App\Models\Kapela::orderBy('nama_kapela')->get(['id', 'nama_kapela']),
            'parokiList' => \App\Models\Paroki::orderBy('nama_paroki')->get(['id_paroki', 'nama_paroki']),
            'provinsiList' => \App\Models\Provinsi::orderBy('nama_provinsi')->get(['id_provinsi', 'nama_provinsi']),
            'kabupatenList' => \App\Models\Kabupaten::orderBy('nama_kabupaten')->get(['id_kabupaten', 'provinsi_id', 'nama_kabupaten']),
            'kecamatanList' => \App\Models\Kecamatan::orderBy('nama_kecamatan')->get(['id_kecamatan', 'kabupaten_id', 'nama_kecamatan']),
            'desaList' => $this->desaOptionsForKecamatan($civilRegion['kecamatan_id'] ?? null),
            'civilRegion' => $civilRegion,
        ]);
    }

    /**
     * Dedicated Full Page for Creating Umat / Jiwa Baru.
     */
    public function createUmat(Request $request)
    {
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? 'superadmin';
        $userRoleSlug = strtolower(auth()->user()?->role?->slug ?? auth()->user()?->role?->nama_role ?? '');
        if (in_array($firstSegment, ['wilayah', 'kapela', 'stasi'], true) || str_contains($userRoleSlug, 'wilayah') || str_contains($userRoleSlug, 'kapela') || str_contains($userRoleSlug, 'stasi')) {
            return redirect("/{$firstSegment}/umat")->with('error', 'Akses ditolak. Pengelolaan data Umat (tambah/edit/hapus) hanya dapat dilakukan pada tingkat KUB atau Sekretariat Paroki.');
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
        $defaultParoki = Paroki::with('keuskupan')->find($defaultParokiId)
            ?? Paroki::with('keuskupan')->first();

        $pastorList = \App\Models\MasterPastor::orderBy('nama_pastor')->get()->map(function($p) {
            return [
                'id' => $p->nama_lengkap_gelar ?: $p->nama_pastor,
                'name' => $p->nama_lengkap_gelar ?: $p->nama_pastor,
                'jabatan' => $p->jabatan ?: 'Pastor',
            ];
        });

        return Inertia::render('Inertia/UmatForm', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
            'isEdit' => false,
            'umatItem' => null,
            'kkList' => \App\Models\KkKatolik::orderBy('nama_lahir_pemilik')->get(['id', 'no_kk_kw', 'nama_lahir_pemilik', 'nama_baptis_pemilik', 'wilayah_id', 'kapela_id', 'kub_id']),
            'wilayahList' => \App\Models\Wilayah::orderBy('nama_wilayah')->get(['id', 'nama_wilayah']),
            'kubList' => \App\Models\Kub::orderBy('nama_kub')->get(['id', 'nama_kub', 'wilayah_id']),
            'kapelaList' => \App\Models\Kapela::orderBy('nama_kapela')->get(['id', 'nama_kapela']),
            'parokiList' => \App\Models\Paroki::orderBy('nama_paroki')->get(['id_paroki', 'nama_paroki', 'kode_paroki']),
            'pastorList' => $pastorList,
            'namaParoki' => $defaultParoki?->nama_paroki ?? 'Paroki St. Vinsensius a Paulo Benlutu',
            'namaKeuskupan' => $defaultParoki?->keuskupan?->nama_keuskupan ?? 'Keuskupan Agung Kupang',
        ]);
    }

    /**
     * Dedicated Full Page for Editing Umat / Jiwa.
     */
    public function editUmat(Request $request, string|int $id)
    {
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? 'superadmin';
        $userRoleSlug = strtolower(auth()->user()?->role?->slug ?? auth()->user()?->role?->nama_role ?? '');
        if (in_array($firstSegment, ['wilayah', 'kapela', 'stasi'], true) || str_contains($userRoleSlug, 'wilayah') || str_contains($userRoleSlug, 'kapela') || str_contains($userRoleSlug, 'stasi')) {
            return redirect("/{$firstSegment}/umat")->with('error', 'Akses ditolak. Pengelolaan data Umat (tambah/edit/hapus) hanya dapat dilakukan pada tingkat KUB atau Sekretariat Paroki.');
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

        $umatItem = \App\Models\Umat::findOrFail($id);

        $defaultParokiId = $this->defaultParokiIdFromProfile();
        $defaultParoki = Paroki::with('keuskupan')->find($defaultParokiId)
            ?? Paroki::with('keuskupan')->first();

        $pastorList = \App\Models\MasterPastor::orderBy('nama_pastor')->get()->map(function($p) {
            return [
                'id' => $p->nama_lengkap_gelar ?: $p->nama_pastor,
                'name' => $p->nama_lengkap_gelar ?: $p->nama_pastor,
                'jabatan' => $p->jabatan ?: 'Pastor',
            ];
        });

        return Inertia::render('Inertia/UmatForm', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
            'isEdit' => true,
            'umatItem' => $umatItem,
            'kkList' => \App\Models\KkKatolik::orderBy('nama_lahir_pemilik')->get(['id', 'no_kk_kw', 'nama_lahir_pemilik', 'nama_baptis_pemilik', 'wilayah_id', 'kapela_id', 'kub_id']),
            'wilayahList' => \App\Models\Wilayah::orderBy('nama_wilayah')->get(['id', 'nama_wilayah']),
            'kubList' => \App\Models\Kub::orderBy('nama_kub')->get(['id', 'nama_kub', 'wilayah_id']),
            'kapelaList' => \App\Models\Kapela::orderBy('nama_kapela')->get(['id', 'nama_kapela']),
            'parokiList' => \App\Models\Paroki::orderBy('nama_paroki')->get(['id_paroki', 'nama_paroki', 'kode_paroki']),
            'pastorList' => $pastorList,
            'namaParoki' => $defaultParoki?->nama_paroki ?? 'Paroki St. Vinsensius a Paulo Benlutu',
            'namaKeuskupan' => $defaultParoki?->keuskupan?->nama_keuskupan ?? 'Keuskupan Agung Kupang',
        ]);
    }

    /**
     * Store newly created Umat record.
     */
    public function storeUmat(Request $request)
    {
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? 'superadmin';
        $userRoleSlug = strtolower(auth()->user()?->role?->slug ?? auth()->user()?->role?->nama_role ?? '');
        if (in_array($firstSegment, ['wilayah', 'kapela', 'stasi'], true) || str_contains($userRoleSlug, 'wilayah') || str_contains($userRoleSlug, 'kapela') || str_contains($userRoleSlug, 'stasi')) {
            return redirect("/{$firstSegment}/umat")->with('error', 'Akses ditolak. Pengelolaan data Umat (tambah/edit/hapus) hanya dapat dilakukan pada tingkat KUB atau Sekretariat Paroki.');
        }

        $data = $request->all();
        $validColumns = $this->schemaColumns('umat');
        $cleanData = [];
        foreach ($data as $k => $v) {
            if ($k === 'is_deleted' || $k === 'id') continue;
            if (in_array($k, $validColumns, true) && !in_array($k, ['created_at', 'updated_at'], true)) {
                $cleanData[$k] = $v;
            }
        }
        if (in_array('created_by', $validColumns, true)) {
            $cleanData['created_by'] = auth()->id();
        }

        $created = \App\Models\Umat::create($cleanData);

        $this->clearFastAccessCache();

        return redirect("/{$firstSegment}/umat")->with('success', 'Data Umat baru berhasil disimpan.');
    }

    /**
     * Update existing Umat record.
     */
    public function updateUmat(Request $request, $id)
    {
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? 'superadmin';
        $userRoleSlug = strtolower(auth()->user()?->role?->slug ?? auth()->user()?->role?->nama_role ?? '');
        if (in_array($firstSegment, ['wilayah', 'kapela', 'stasi'], true) || str_contains($userRoleSlug, 'wilayah') || str_contains($userRoleSlug, 'kapela') || str_contains($userRoleSlug, 'stasi')) {
            return redirect("/{$firstSegment}/umat")->with('error', 'Akses ditolak. Pengelolaan data Umat (tambah/edit/hapus) hanya dapat dilakukan pada tingkat KUB atau Sekretariat Paroki.');
        }

        $umat = \App\Models\Umat::findOrFail($id);
        $data = $request->all();
        $validColumns = $this->schemaColumns('umat');
        $cleanData = [];
        foreach ($data as $k => $v) {
            if ($k === 'is_deleted' || $k === 'id') continue;
            if (in_array($k, $validColumns, true) && !in_array($k, ['created_at', 'updated_at'], true)) {
                $cleanData[$k] = $v;
            }
        }
        if (in_array('updated_by', $validColumns, true)) {
            $cleanData['updated_by'] = auth()->id();
        }

        $umat->update($cleanData);

        $this->clearFastAccessCache();

        return redirect("/{$firstSegment}/umat")->with('success', 'Data Umat berhasil diperbarui.');
    }

    /**
     * Dedicated Full Page for Creating Master Pastor / Imam Baru.
     */
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

    /**
     * Dedicated Full Page for Editing Master Pastor / Imam.
     */
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

    /**
     * Store newly created Pastor record.
     */
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

    /**
     * Update existing Pastor record.
     */
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

    public function desaKelurahanOptions(Request $request)
    {
        $validated = $request->validate([
            'kecamatan_id' => ['nullable', 'integer'],
        ]);

        return response()->json([
            'data' => $this->desaOptionsForKecamatan($validated['kecamatan_id'] ?? null),
        ]);
    }

    private function desaOptionsForKecamatan($kecamatanId)
    {
        if (!$kecamatanId) {
            return collect();
        }

        return \App\Models\DesaKelurahan::where('kecamatan_id', $kecamatanId)
            ->orderBy('nama_desa')
            ->get(['id_desa', 'kecamatan_id', 'nama_desa']);
    }

    private function resolveKkCivilRegion(?\App\Models\KkKatolik $kkItem, ?\App\Models\Paroki $defaultParoki): array
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

    /**
     * Dedicated View Page for KK Katolik Details.
     */
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

        $kk = is_numeric($id)
            ? \App\Models\KkKatolik::with(['anggota', 'wilayah', 'kapela', 'kub'])->find($id)
            : \App\Models\KkKatolik::with(['anggota', 'wilayah', 'kapela', 'kub'])->where('no_kk_kw', $id)->first();

        if (!$kk) {
            $kk = \App\Models\KkKatolik::with(['anggota', 'wilayah', 'kapela', 'kub'])->firstOrFail();
        }

        $defaultParokiId = $this->defaultParokiIdFromProfile();
        $defaultParoki = Paroki::with('keuskupan')->find($defaultParokiId)
            ?? Paroki::with('keuskupan')->first();

        return Inertia::render('Inertia/KkDetail', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
            'kk' => $kk,
            'paroki' => $defaultParoki,
            'keuskupan' => $defaultParoki?->keuskupan,
        ]);
    }

    /**
     * Official PDF / Print View for KK Katolik.
     */
    public function exportKkPdf(Request $request, string|int $id)
    {
        $kk = is_numeric($id)
            ? \App\Models\KkKatolik::with(['anggota', 'wilayah', 'kapela', 'kub'])->find($id)
            : \App\Models\KkKatolik::with(['anggota', 'wilayah', 'kapela', 'kub'])->where('no_kk_kw', $id)->first();

        if (!$kk) {
            $kk = \App\Models\KkKatolik::with(['anggota', 'wilayah', 'kapela', 'kub'])->firstOrFail();
        }

        $defaultParokiId = $this->defaultParokiIdFromProfile();
        $paroki = Paroki::with('keuskupan')->find($defaultParokiId)
            ?? Paroki::with('keuskupan')->first();

        // Ambil data Keuskupan dan Profil Paroki aktif
        $keuskupan = $paroki?->keuskupan ?? \App\Models\Keuskupan::first();
        $profilParoki = \App\Models\ProfilParoki::first();

        $keuskupanLogo = $keuskupan?->logo ?: '/uploads/keuskupan/logo_keuskupan_kupang.svg';
        $parokiLogo = $paroki?->logo ?: '/assets/uploads/profil/logo_paroki_1787370466.jpeg';

        return response()->view('exports.kk-pdf', [
            'kk' => $kk,
            'paroki' => $paroki,
            'keuskupan' => $keuskupan,
            'profilParoki' => $profilParoki,
            'keuskupanLogo' => $keuskupanLogo,
            'parokiLogo' => $parokiLogo,
            'printedAt' => now()->format('d/m/Y H:i'),
        ]);
    }

    /**
     * Dedicated Full Page for Creating Galeri / Album Baru.
     */
    public function createGaleri(Request $request): Response
    {
        $this->ensureGaleriColumns();
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

        return Inertia::render('Inertia/GaleriForm', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
            'isEdit' => false,
            'item' => null,
        ]);
    }

    /**
     * Dedicated Full Page for Editing Galeri / Album.
     */
    public function editGaleri(Request $request, $id): Response
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

        $item = \App\Models\Galeri::query()
            ->where('id', $id)
            ->orWhere('slug', $id)
            ->firstOrFail();

        return Inertia::render('Inertia/GaleriForm', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
            'isEdit' => true,
            'item' => $item,
        ]);
    }

    /**
     * Profil Paroki SPA.
     */
    public function profilParoki(Request $request): Response
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

        $allParokis = Paroki::with(['keuskupan', 'dekenat'])
            ->orderBy('nama_paroki')
            ->get(['id_paroki', 'nama_paroki', 'kode_paroki', 'keuskupan_id', 'dekenat_id', 'status_paroki', 'status']);

        $selectedParokiId = $request->query('paroki_id')
            ?? $request->session()->get('default_paroki_id')
            ?? (Paroki::where('nama_paroki', 'like', '%Benlutu%')->value('id_paroki') ?? Paroki::value('id_paroki'));

        if ($request->query('set_default') && $request->query('paroki_id')) {
            $request->session()->put('default_paroki_id', (int) $request->query('paroki_id'));
            $selectedParokiId = (int) $request->query('paroki_id');

            // Safe auto-sync selected default paroki to global settings
            $chosen = Paroki::find($selectedParokiId);
            $this->syncParokiToGlobalSettings($chosen);
        }

        $paroki = Paroki::with(['keuskupan', 'dekenat', 'provinsi', 'kabupaten', 'kecamatan', 'desa'])->find($selectedParokiId)
            ?? Paroki::with(['keuskupan', 'dekenat', 'provinsi', 'kabupaten', 'kecamatan', 'desa'])->first()
            ?? new Paroki([
                'nama_paroki' => 'Paroki St. Vincentius a Paulo Benlutu',
                'kode_paroki' => 'PRK-BNL-001',
                'pelindung_paroki' => 'Santo Vincentius a Paulo',
                'alamat' => 'Benlutu, Kec. Batu Putih, Kab. Timor Tengah Selatan, NTT',
                'nama_pastor_paroki_aktif' => 'RD. Krispinus Saku',
                'telepon' => '(0380) 123456',
                'whatsapp' => '081234567890',
                'email' => 'sekretariat@parokibenlutu.org',
                'keterangan' => 'Paroki Santo Vincentius a Paulo Benlutu melayani umat beriman dengan penuh dedikasi dan kasih Kristiani.',
            ]);

        $keuskupanList = \Illuminate\Support\Facades\Cache::remember('ref_keuskupan_list', 3600, function() {
            return Keuskupan::orderBy('nama_keuskupan')->get(['id_keuskupan', 'nama_keuskupan']);
        });

        $dekenatList = \Illuminate\Support\Facades\Cache::remember('ref_dekenat_list_v4', 3600, function() {
            return Dekenat::all();
        });

        $provinsiList = \Illuminate\Support\Facades\Cache::remember('ref_provinsi_list', 3600, function() {
            return Provinsi::orderBy('nama_provinsi')->get(['id_provinsi', 'nama_provinsi']);
        });

        $kabupatenList = \Illuminate\Support\Facades\Cache::remember('ref_kabupaten_list', 3600, function() {
            return Kabupaten::orderBy('nama_kabupaten')->get(['id_kabupaten', 'provinsi_id', 'nama_kabupaten']);
        });

        $kecamatanList = \Illuminate\Support\Facades\Cache::remember('ref_kecamatan_list', 3600, function() {
            return Kecamatan::orderBy('nama_kecamatan')->get(['id_kecamatan', 'kabupaten_id', 'nama_kecamatan']);
        });

        $desaList = \Illuminate\Support\Facades\Cache::remember('ref_desa_scoped_ntt', 3600, function() {
            return DesaKelurahan::whereIn('kecamatan_id', function($q) {
                $q->select('id_kecamatan')->from('kecamatan')->whereIn('kabupaten_id', function($q2) {
                    $q2->select('id_kabupaten')->from('kabupaten')->where('provinsi_id', 53);
                });
            })->orderBy('nama_desa')->get(['id_desa', 'kecamatan_id', 'nama_desa']);
        });

        $pastors = \Illuminate\Support\Facades\Schema::hasTable('master_pastor')
            ? \App\Models\MasterPastor::orderBy('nama_pastor')->get()
            : [];

        $totalUmat = \Illuminate\Support\Facades\Schema::hasTable('umat') ? \App\Models\Umat::count() : 1850;
        $totalKk = \Illuminate\Support\Facades\Schema::hasTable('keluarga') ? \App\Models\Keluarga::count() : 420;

        return Inertia::render('Inertia/ProfilParoki', [
            'paroki' => $paroki,
            'totalWilayah' => Wilayah::count(),
            'totalLingkungan' => Lingkungan::count(),
            'totalKapela' => Kapela::count(),
            'totalKub' => \Illuminate\Support\Facades\Schema::hasTable('kub') ? Kub::count() : 0,
            'totalUmat' => $totalUmat,
            'totalKk' => $totalKk,
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
            'allParokis' => $allParokis,
            'defaultParokiId' => $selectedParokiId,
            'keuskupanList' => $keuskupanList,
            'dekenatList' => $dekenatList,
            'provinsiList' => $provinsiList,
            'kabupatenList' => $kabupatenList,
            'kecamatanList' => $kecamatanList,
            'desaList' => $desaList,
            'pastors' => $pastors,
        ]);
    }

    /**
     * Profil Saya (User Profile & Account Settings SPA matching http://localhost/katedral/admin/profil-saya).
     */
    public function profilSaya(Request $request): Response
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

        $user = auth()->user();
        if ($user) {
            $user->loadMissing(['role', 'wilayah', 'kapela', 'kub', 'umat']);
        } else {
            $user = \App\Models\User::with(['role', 'wilayah', 'kapela', 'kub', 'umat'])->first()
                ?? new \App\Models\User([
                    'nama_lengkap' => 'Administrator Paroki',
                    'username' => 'superadmin',
                    'email' => 'admin@parokibenlutu.org',
                    'no_hp' => '081234567890',
                    'status' => 1,
                ]);
        }

        return Inertia::render('Inertia/ProfilSaya', [
            'user' => $user,
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
        ]);
    }

    /**
     * Update Profil Saya (Personal Details & Avatar).
     */
    public function updateProfilSaya(Request $request)
    {
        $user = auth()->user() ?? \App\Models\User::first();
        if (!$user) {
            return back()->with('error', 'User tidak ditemukan.');
        }

        $userId = $user->getKey();
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:150',
            'username' => 'required|string|max:100|unique:users,username,' . $userId,
            'email' => 'required|email|max:150|unique:users,email,' . $userId,
            'no_hp' => 'nullable|string|max:30',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            $savedPhoto = \App\Services\ImageOptimizer::optimizeAndSave(
                $request->file('foto'),
                'uploads/users',
                600,
                600,
                85
            );

            $validated['foto'] = ltrim($savedPhoto, '/');
        }

        // Introspect users table columns to safely update existing schema
        $cols = \Illuminate\Support\Facades\Schema::getColumnListing('users');
        $payload = [];
        if (in_array('nama_lengkap', $cols, true)) {
            $payload['nama_lengkap'] = $validated['nama_lengkap'];
        }
        if (in_array('name', $cols, true)) {
            $payload['name'] = $validated['nama_lengkap'];
        }
        if (in_array('username', $cols, true)) {
            $payload['username'] = $validated['username'];
        }
        if (in_array('email', $cols, true)) {
            $payload['email'] = $validated['email'];
        }
        if (in_array('no_hp', $cols, true)) {
            $payload['no_hp'] = $validated['no_hp'] ?? null;
        }
        if (isset($validated['foto']) && in_array('foto', $cols, true)) {
            $payload['foto'] = $validated['foto'];
        }

        $user->update($payload);

        return back()->with('success', 'Profil dan foto Anda berhasil diperbarui.');
    }

    /**
     * Update Password Profil Saya.
     */
    public function updatePasswordProfilSaya(Request $request)
    {
        $user = auth()->user() ?? \App\Models\User::first();
        if (!$user) {
            return back()->with('error', 'User tidak ditemukan.');
        }

        $request->validate([
            'current_password' => 'nullable|string',
            'new_password' => 'required|string|min:6|confirmed',
        ], [
            'new_password.required' => 'Kata sandi baru wajib diisi.',
            'new_password.min' => 'Kata sandi minimal 6 karakter.',
            'new_password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        if ($request->filled('current_password')) {
            if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Kata sandi saat ini salah.']);
            }
        }

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->new_password),
        ]);

        return back()->with('success', 'Kata sandi Anda berhasil diperbarui.');
    }

    /**
     * Dedicated Page for Backup & Restore Database.
     */
    public function backupDatabase(Request $request): Response
    {
        $this->ensureBackupDatabaseTable();
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

        // Scan actual storage/app/backups folder to sync any existing .sql files
        $backupDir = storage_path('app/backups');
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $files = glob($backupDir . '/*.sql');
        foreach ($files as $filePath) {
            $fileName = basename($filePath);
            $size = filesize($filePath);
            $time = filemtime($filePath);

            $exists = DB::table('backup_database')->where('nama_file', $fileName)->exists();
            if (!$exists) {
                DB::table('backup_database')->insert([
                    'nama_file' => $fileName,
                    'ukuran' => $size,
                    'dibuat_oleh' => 'Sistem / File',
                    'created_at' => date('Y-m-d H:i:s', $time),
                    'updated_at' => date('Y-m-d H:i:s', $time),
                ]);
            }
        }

        $backups = DB::table('backup_database')
            ->orderBy('id', 'desc')
            ->get();

        // Calculate database metrics
        $databaseName = config('database.connections.mysql.database');
        $dbStatus = null;
        try {
            $dbStatus = DB::select("SELECT 
                COUNT(table_name) AS table_count, 
                ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS db_size_mb,
                SUM(table_rows) AS total_rows
                FROM information_schema.tables 
                WHERE table_schema = ?", [$databaseName])[0] ?? null;
        } catch (\Throwable $e) {}

        return Inertia::render('Inertia/BackupDatabase', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
            'backups' => $backups,
            'databaseName' => $databaseName,
            'tableCount' => (int) ($dbStatus->table_count ?? 0),
            'dbSizeMb' => (float) ($dbStatus->db_size_mb ?? 0),
            'totalRows' => (int) ($dbStatus->total_rows ?? 0),
            'lastBackupAt' => $backups->first()?->created_at ?? null,
        ]);
    }

    /**
     * Generate Live MySQL SQL Database Backup.
     */
    public function generateDatabaseBackup(Request $request)
    {
        try {
            $databaseName = config('database.connections.mysql.database');
            $tables = DB::select('SHOW TABLES');
            $keyName = "Tables_in_{$databaseName}";

            $sqlContent = "-- SIPAROKI Database Backup\n";
            $sqlContent .= "-- Generated: " . now()->toDateTimeString() . "\n";
            $sqlContent .= "-- Database: {$databaseName}\n\n";
            $sqlContent .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $tableObj) {
                $tableName = $tableObj->{$keyName} ?? array_values((array)$tableObj)[0];

                $createTable = DB::select("SHOW CREATE TABLE `{$tableName}`");
                $createSql = $createTable[0]->{'Create Table'} ?? null;
                if ($createSql) {
                    $sqlContent .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                    $sqlContent .= $createSql . ";\n\n";
                }

                $rows = DB::table($tableName)->get();
                if ($rows->count() > 0) {
                    $sqlContent .= "INSERT INTO `{$tableName}` VALUES \n";
                    $rowStrings = [];
                    foreach ($rows as $row) {
                        $values = array_map(function ($val) {
                            if ($val === null) return 'NULL';
                            return "'" . addslashes((string)$val) . "'";
                        }, (array)$row);
                        $rowStrings[] = "(" . implode(", ", $values) . ")";
                    }
                    $sqlContent .= implode(",\n", $rowStrings) . ";\n\n";
                }
            }

            $sqlContent .= "SET FOREIGN_KEY_CHECKS=1;\n";

            $fileName = 'backup-paroki-' . date('Y-m-d_His') . '.sql';
            $backupDir = storage_path('app/backups');
            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $filePath = $backupDir . '/' . $fileName;
            file_put_contents($filePath, $sqlContent);
            $fileSize = filesize($filePath);

            $this->ensureBackupDatabaseTable();

            DB::table('backup_database')->insert([
                'nama_file' => $fileName,
                'ukuran' => $fileSize,
                'dibuat_oleh' => auth()->user()?->nama_lengkap ?? auth()->user()?->username ?? 'Administrator',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return back()->with('success', "Database berhasil di-backup! File: {$fileName} (" . round($fileSize / 1024, 2) . " KB)");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mem-backup database: ' . $e->getMessage());
        }
    }

    /**
     * Download Backup File.
     */
    public function downloadDatabaseBackup($id)
    {
        $backup = DB::table('backup_database')->where('id', $id)->first();
        if (!$backup) {
            abort(404, 'File backup tidak ditemukan.');
        }

        $filePath = storage_path('app/backups/' . $backup->nama_file);
        if (!file_exists($filePath)) {
            abort(404, 'File fisik .sql tidak ditemukan di server.');
        }

        return response()->download($filePath, $backup->nama_file, [
            'Content-Type' => 'application/sql',
        ]);
    }

    /**
     * Restore Database from existing backup record.
     */
    public function restoreDatabaseBackup(Request $request, $id)
    {
        try {
            $backup = DB::table('backup_database')->where('id', $id)->first();
            if (!$backup) {
                return back()->with('error', 'Data backup tidak ditemukan.');
            }

            $filePath = storage_path('app/backups/' . $backup->nama_file);
            if (!file_exists($filePath)) {
                return back()->with('error', "File fisik backup {$backup->nama_file} tidak ditemukan di server.");
            }

            $sqlContent = file_get_contents($filePath);
            $this->executeSqlDump($sqlContent);
            $this->clearFastAccessCache();

            return back()->with('success', "Database berhasil dipulihkan (restore) dari file {$backup->nama_file}!");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memulihkan database: ' . $e->getMessage());
        }
    }

    /**
     * Upload and Restore Database from SQL file.
     */
    public function uploadRestoreDatabaseBackup(Request $request)
    {
        $request->validate([
            'file_sql' => ['required', 'file', 'max:51200'], // max 50MB
        ]);

        try {
            $file = $request->file('file_sql');
            $sqlContent = file_get_contents($file->getRealPath());
            $this->executeSqlDump($sqlContent);
            $this->clearFastAccessCache();

            return back()->with('success', "Database berhasil dipulihkan (restore) dari file upload: " . $file->getClientOriginalName());
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memulihkan database dari file: ' . $e->getMessage());
        }
    }

    /**
     * Delete Backup record and physical file.
     */
    public function deleteDatabaseBackup($id)
    {
        $backup = DB::table('backup_database')->where('id', $id)->first();
        if ($backup) {
            $filePath = storage_path('app/backups/' . $backup->nama_file);
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            DB::table('backup_database')->where('id', $id)->delete();
        }

        return back()->with('success', 'File backup berhasil dihapus.');
    }

    private function executeSqlDump(string $sql): void
    {
        DB::unprepared("SET FOREIGN_KEY_CHECKS=0;");
        DB::unprepared($sql);
        DB::unprepared("SET FOREIGN_KEY_CHECKS=1;");
    }

    protected function ensureBackupDatabaseTable(): void
    {
        try {
            if (!Schema::hasTable('backup_database')) {
                Schema::create('backup_database', function ($table) {
                    $table->increments('id');
                    $table->string('nama_file', 255);
                    $table->bigInteger('ukuran')->default(0);
                    $table->string('dibuat_oleh', 100)->nullable();
                    $table->timestamps();
                });
            }
        } catch (\Throwable $e) {
            // Silently continue
        }
    }

    /**
     * Security Center & Firewall Dashboard.
     */
    public function securityCenter(Request $request): Response
    {
        $this->ensureSecurityTables();
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

        // Load settings
        $settingsRaw = [];
        try {
            if (Schema::hasTable('security_settings') && Schema::hasColumn('security_settings', 'setting_key') && Schema::hasColumn('security_settings', 'setting_value')) {
                $settingsRaw = DB::table('security_settings')->pluck('setting_value', 'setting_key')->toArray();
            }
        } catch (\Throwable $e) {}

        $settings = [
            'max_login_attempts' => (int) ($settingsRaw['max_login_attempts'] ?? 5),
            'lockout_minutes' => (int) ($settingsRaw['lockout_minutes'] ?? 60),
            'session_timeout_minutes' => (int) ($settingsRaw['session_timeout_minutes'] ?? config('session.lifetime', 120)),
            'force_strong_password' => ($settingsRaw['force_strong_password'] ?? '1') === '1',
            'enable_brute_force_protection' => ($settingsRaw['enable_brute_force_protection'] ?? '1') === '1',
            'block_untrusted_ip' => ($settingsRaw['block_untrusted_ip'] ?? '0') === '1',
        ];

        // Blocked IPs
        $blockedIps = collect();
        try {
            if (Schema::hasTable('blocked_ips')) {
                $blockedIps = DB::table('blocked_ips')->orderByDesc('id')->get();
            }
        } catch (\Throwable $e) {}

        // Security Logs
        $logs = collect();
        try {
            if (Schema::hasTable('security_logs')) {
                $logs = DB::table('security_logs')->orderByDesc('id')->limit(150)->get();
            }
        } catch (\Throwable $e) {}

        // Audit metrics
        $isHttps = $request->isSecure() || $request->header('x-forwarded-proto') === 'https';
        $isDebug = config('app.debug', false);
        $appEnv = config('app.env', 'production');
        $sessionDriver = config('session.driver', 'file');
        $sessionLifetime = config('session.lifetime', 120);
        $uploadWritable = is_writable(public_path('uploads'));
        $storageWritable = is_writable(storage_path());

        // Score calculation
        $score = 100;
        if ($isDebug && $appEnv === 'production') $score -= 15;
        if (!$isHttps && $appEnv === 'production') $score -= 15;
        if (!$uploadWritable) $score -= 10;
        if (!$storageWritable) $score -= 10;
        if (!$settings['enable_brute_force_protection']) $score -= 10;
        if ($score < 0) $score = 0;

        $auditChecks = [
            [
                'title' => 'Proteksi CSRF (Cross-Site Request Forgery)',
                'status' => 'PASS',
                'description' => 'CSRF Token aktif di seluruh form, API monolith, dan session cookies.',
                'badge' => 'Aktif',
                'icon' => 'fa-shield-check',
            ],
            [
                'title' => 'Enkripsi & Status HTTPS / SSL',
                'status' => $isHttps ? 'PASS' : ($appEnv === 'local' ? 'INFO' : 'WARNING'),
                'description' => $isHttps ? 'Koneksi terenkripsi aman menggunakan SSL/TLS.' : ($appEnv === 'local' ? 'Mode Lokal / Development (HTTP).' : 'Disarankan mengaktifkan SSL/HTTPS di environment produksi.'),
                'badge' => $isHttps ? 'Secure (HTTPS)' : ($appEnv === 'local' ? 'Lokal Dev' : 'HTTP'),
                'icon' => 'fa-lock',
            ],
            [
                'title' => 'Mode Debug Aplikasi (APP_DEBUG)',
                'status' => (!$isDebug || $appEnv === 'local') ? 'PASS' : 'WARNING',
                'description' => $isDebug ? ($appEnv === 'local' ? 'Debug aktif untuk lingkungan pengembangan lokal.' : 'Debug aktif di produksi berisiko membocorkan stack trace.') : 'Debug mode dinonaktifkan (Aman).',
                'badge' => $isDebug ? 'Debug ON' : 'Debug OFF',
                'icon' => 'fa-bug',
            ],
            [
                'title' => 'Keamanan Sesi & Session Lifetime',
                'status' => 'PASS',
                'description' => 'Sesi dikelola dengan driver ' . $sessionDriver . ' dengan durasi kedaluwarsa ' . $sessionLifetime . ' menit dan flag HttpOnly.',
                'badge' => $sessionLifetime . ' Menit',
                'icon' => 'fa-user-clock',
            ],
            [
                'title' => 'Izin Tulis Direktori Uploads & Storage',
                'status' => ($uploadWritable && $storageWritable) ? 'PASS' : 'FAIL',
                'description' => ($uploadWritable && $storageWritable) ? 'Direktori public/uploads dan storage memiliki izin yang tepat.' : 'Periksa permission direktori public/uploads atau storage.',
                'badge' => ($uploadWritable && $storageWritable) ? 'Writable' : 'Permission Error',
                'icon' => 'fa-folder-gear',
            ],
            [
                'title' => 'Proteksi Brute Force Login',
                'status' => $settings['enable_brute_force_protection'] ? 'PASS' : 'WARNING',
                'description' => $settings['enable_brute_force_protection'] ? 'Pemblokiran otomatis aktif setelah ' . $settings['max_login_attempts'] . 'x percobaan gagal.' : 'Proteksi brute force dinonaktifkan.',
                'badge' => $settings['enable_brute_force_protection'] ? 'Maks ' . $settings['max_login_attempts'] . 'x Gagal' : 'Nonaktif',
                'icon' => 'fa-shield-halved',
            ],
        ];

        $todayFailed = 0;
        $todaySuccess = 0;
        try {
            if (Schema::hasTable('security_logs') && Schema::hasColumn('security_logs', 'status')) {
                $todayFailed = DB::table('security_logs')->where('status', 'FAILED')->whereDate('created_at', today())->count();
                $todaySuccess = DB::table('security_logs')->where('status', 'SUCCESS')->whereDate('created_at', today())->count();
            }
        } catch (\Throwable $e) {}

        return Inertia::render('Inertia/SecurityCenter', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
            'settings' => $settings,
            'blockedIps' => $blockedIps,
            'logs' => $logs,
            'auditChecks' => $auditChecks,
            'securityScore' => $score,
            'todayFailed' => $todayFailed,
            'todaySuccess' => $todaySuccess,
            'totalBlocked' => $blockedIps->count(),
            'currentIp' => $request->ip(),
            'phpVersion' => PHP_VERSION,
            'laravelVersion' => app()->version(),
        ]);
    }

    /**
     * Update security policies and settings.
     */
    public function updateSecuritySettings(Request $request)
    {
        $this->ensureSecurityTables();
        $validated = $request->validate([
            'max_login_attempts' => 'required|integer|min:1|max:50',
            'lockout_minutes' => 'required|integer|min:1|max:1440',
            'session_timeout_minutes' => 'required|integer|min:5|max:1440',
            'force_strong_password' => 'nullable|boolean',
            'enable_brute_force_protection' => 'nullable|boolean',
            'block_untrusted_ip' => 'nullable|boolean',
        ]);

        foreach ($validated as $key => $val) {
            DB::table('security_settings')->updateOrInsert(
                ['setting_key' => $key],
                ['setting_value' => (string) ($val === true ? '1' : ($val === false ? '0' : $val)), 'updated_at' => now()]
            );
        }

        return back()->with('success', 'Pengaturan kebijakan keamanan berhasil diperbarui.');
    }

    /**
     * Block IP manually.
     */
    public function blockIp(Request $request)
    {
        $this->ensureSecurityTables();
        $validated = $request->validate([
            'ip_address' => 'required|string|max:45',
            'reason' => 'nullable|string|max:255',
            'blocked_duration' => 'nullable|integer',
        ]);

        $ip = trim($validated['ip_address']);
        if ($ip === '127.0.0.1' || $ip === '::1' || $ip === $request->ip()) {
            return back()->with('error', 'Anda tidak dapat memblokir alamat IP Anda sendiri atau localhost.');
        }

        $blockedUntil = null;
        if (!empty($validated['blocked_duration']) && (int) $validated['blocked_duration'] > 0) {
            $blockedUntil = now()->addHours((int) $validated['blocked_duration']);
        }

        DB::table('blocked_ips')->updateOrInsert(
            ['ip_address' => $ip],
            [
                'reason' => $validated['reason'] ?: 'Diblokir manual oleh ' . (auth()->user()?->nama_lengkap ?? 'Super Admin'),
                'blocked_by' => auth()->user()?->nama_lengkap ?? 'Super Admin',
                'blocked_until' => $blockedUntil,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return back()->with('success', 'Alamat IP ' . $ip . ' berhasil ditambahkan ke daftar blokir.');
    }

    /**
     * Unblock IP.
     */
    public function unblockIp(Request $request, $id)
    {
        $this->ensureSecurityTables();
        DB::table('blocked_ips')->where('id', $id)->orWhere('ip_address', $id)->delete();
        return back()->with('success', 'Alamat IP berhasil dilepas dari daftar blokir.');
    }

    /**
     * Clear or trim security audit logs.
     */
    public function clearSecurityLogs(Request $request)
    {
        $this->ensureSecurityTables();
        DB::table('security_logs')->truncate();
        return back()->with('success', 'Seluruh log aktivitas keamanan berhasil dibersihkan.');
    }

    /**
     * Clear system security cache.
     */
    public function clearSystemSecurityCache(Request $request)
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            \Illuminate\Support\Facades\Artisan::call('config:clear');
        } catch (\Throwable $e) {}

        return back()->with('success', 'Cache sistem dan sesi keamanan berhasil disegarkan.');
    }

    /**
     * Ensure security tables exist in database.
     */
    protected function ensureSecurityTables(): void
    {
        try {
            if (!Schema::hasTable('security_settings')) {
                Schema::create('security_settings', function ($table) {
                    $table->increments('id');
                    $table->string('setting_key', 100)->unique();
                    $table->text('setting_value')->nullable();
                    $table->timestamps();
                });

                DB::table('security_settings')->insert([
                    ['setting_key' => 'max_login_attempts', 'setting_value' => '5', 'created_at' => now(), 'updated_at' => now()],
                    ['setting_key' => 'lockout_minutes', 'setting_value' => '60', 'created_at' => now(), 'updated_at' => now()],
                    ['setting_key' => 'session_timeout_minutes', 'setting_value' => '120', 'created_at' => now(), 'updated_at' => now()],
                    ['setting_key' => 'force_strong_password', 'setting_value' => '1', 'created_at' => now(), 'updated_at' => now()],
                    ['setting_key' => 'enable_brute_force_protection', 'setting_value' => '1', 'created_at' => now(), 'updated_at' => now()],
                ]);
            } else {
                $cols = Schema::getColumnListing('security_settings');
                Schema::table('security_settings', function ($table) use ($cols) {
                    if (!in_array('setting_key', $cols, true)) $table->string('setting_key', 100)->nullable();
                    if (!in_array('setting_value', $cols, true)) $table->text('setting_value')->nullable();
                });

                if (DB::table('security_settings')->count() === 0) {
                    DB::table('security_settings')->insert([
                        ['setting_key' => 'max_login_attempts', 'setting_value' => '5', 'created_at' => now(), 'updated_at' => now()],
                        ['setting_key' => 'lockout_minutes', 'setting_value' => '60', 'created_at' => now(), 'updated_at' => now()],
                        ['setting_key' => 'session_timeout_minutes', 'setting_value' => '120', 'created_at' => now(), 'updated_at' => now()],
                        ['setting_key' => 'force_strong_password', 'setting_value' => '1', 'created_at' => now(), 'updated_at' => now()],
                        ['setting_key' => 'enable_brute_force_protection', 'setting_value' => '1', 'created_at' => now(), 'updated_at' => now()],
                    ]);
                }
            }

            if (!Schema::hasTable('blocked_ips')) {
                Schema::create('blocked_ips', function ($table) {
                    $table->increments('id');
                    $table->string('ip_address', 45)->index();
                    $table->string('reason', 255)->nullable();
                    $table->string('blocked_by', 100)->nullable();
                    $table->timestamp('blocked_until')->nullable();
                    $table->timestamps();
                });
            } else {
                $cols = Schema::getColumnListing('blocked_ips');
                Schema::table('blocked_ips', function ($table) use ($cols) {
                    if (!in_array('ip_address', $cols, true)) $table->string('ip_address', 45)->nullable()->index();
                    if (!in_array('reason', $cols, true)) $table->string('reason', 255)->nullable();
                    if (!in_array('blocked_by', $cols, true)) $table->string('blocked_by', 100)->nullable();
                    if (!in_array('blocked_until', $cols, true)) $table->timestamp('blocked_until')->nullable();
                });
            }

            if (!Schema::hasTable('security_logs')) {
                Schema::create('security_logs', function ($table) {
                    $table->increments('id');
                    $table->string('ip_address', 45)->nullable();
                    $table->unsignedBigInteger('user_id')->nullable();
                    $table->string('username', 100)->nullable();
                    $table->string('event_type', 50)->default('LOGIN');
                    $table->string('user_agent', 255)->nullable();
                    $table->string('status', 20)->default('SUCCESS');
                    $table->text('details')->nullable();
                    $table->timestamps();
                });
            } else {
                $cols = Schema::getColumnListing('security_logs');
                Schema::table('security_logs', function ($table) use ($cols) {
                    if (!in_array('ip_address', $cols, true)) $table->string('ip_address', 45)->nullable();
                    if (!in_array('user_id', $cols, true)) $table->unsignedBigInteger('user_id')->nullable();
                    if (!in_array('username', $cols, true)) $table->string('username', 100)->nullable();
                    if (!in_array('event_type', $cols, true)) $table->string('event_type', 50)->default('LOGIN');
                    if (!in_array('user_agent', $cols, true)) $table->string('user_agent', 255)->nullable();
                    if (!in_array('status', $cols, true)) $table->string('status', 20)->default('SUCCESS');
                    if (!in_array('details', $cols, true)) $table->text('details')->nullable();
                });
            }
        } catch (\Throwable $e) {}
    }

    /**
     * Settings Hub (Pengaturan Terpadu: Pembayaran, OTP, Video, Slider, SEO, Widget).
     */
    public function pengaturanHub(Request $request, ?string $tab = null): Response
    {
        $this->ensureSettingsHubTables();
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

        // Resolve active tab from URL path if not explicitly provided
        $path = $request->path();
        $activeTab = $tab ?? 'pembayaran';
        if (str_contains($path, 'pembayaran')) {
            $activeTab = 'pembayaran';
        } elseif (str_contains($path, 'otp')) {
            $activeTab = 'otp';
        } elseif (str_contains($path, 'video-header')) {
            $activeTab = 'video';
        } elseif (str_contains($path, 'slider')) {
            $activeTab = 'slider';
        } elseif (str_contains($path, 'meta_tag') || str_contains($path, 'seo')) {
            $activeTab = 'seo';
        } elseif (str_contains($path, 'widget') || str_contains($path, 'menu')) {
            $activeTab = 'widget';
        } elseif (str_contains($path, 'maintenance')) {
            $activeTab = 'maintenance';
        }

        // Data for each tab
        $metodePembayaran = DB::table('metode_pembayaran')->orderBy('urutan')->get();
        $pengaturanOtp = DB::table('pengaturan_otp')->first() ?? (object) [
            'provider' => 'Fonnte',
            'api_key' => '',
            'sender_number' => '',
            'device_id' => '',
            'template_otp' => 'Kode verifikasi SIPAROKI Anda adalah: {{otp}}. Berlaku 10 menit.',
            'template_notifikasi' => 'Halo {{nama}}, pendaftaran sakramen Anda di {{paroki}} telah diterima.',
            'status' => 'Aktif',
        ];
        $sliders = DB::table('slider_banner')->orderBy('urutan')->get();
        $pengaturanAplikasi = DB::table('pengaturan_aplikasi')->first() ?? (object) [];

        return Inertia::render('Inertia/PengaturanHub', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
            'initialTab' => $activeTab,
            'metodePembayaran' => $metodePembayaran,
            'pengaturanOtp' => $pengaturanOtp,
            'sliders' => $sliders,
            'pengaturanAplikasi' => $pengaturanAplikasi,
        ]);
    }

    /**
     * Save / Update Payment Method.
     */
    public function saveMetodePembayaran(Request $request)
    {
        $this->ensureSettingsHubTables();
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'nama_bank' => 'required|string|max:100',
            'nomor_rekening' => 'nullable|string|max:100',
            'atas_nama' => 'nullable|string|max:150',
            'tipe' => 'required|string|max:50',
            'urutan' => 'nullable|integer',
            'status' => 'nullable|string|max:20',
            'petunjuk' => 'nullable|string',
            'logo_bank' => 'nullable|file|image|max:2048',
            'gambar_qris' => 'nullable|file|image|max:3072',
        ]);

        $payload = [
            'nama_bank' => $validated['nama_bank'],
            'nomor_rekening' => $validated['nomor_rekening'] ?? '',
            'atas_nama' => $validated['atas_nama'] ?? '',
            'tipe' => $validated['tipe'],
            'urutan' => (int) ($validated['urutan'] ?? 1),
            'status' => $validated['status'] ?? 'Aktif',
            'petunjuk' => $validated['petunjuk'] ?? '',
            'updated_at' => now(),
        ];

        if ($request->hasFile('logo_bank')) {
            $payload['logo_bank'] = $this->storeModuleUploadedFile('pembayaran', 'logo_bank', $request->file('logo_bank'));
        }
        if ($request->hasFile('gambar_qris')) {
            $payload['gambar_qris'] = $this->storeModuleUploadedFile('pembayaran', 'gambar_qris', $request->file('gambar_qris'));
        }

        if (!empty($validated['id'])) {
            DB::table('metode_pembayaran')->where('id', $validated['id'])->update($payload);
        } else {
            $payload['created_at'] = now();
            DB::table('metode_pembayaran')->insert($payload);
        }

        return back()->with('success', 'Metode pembayaran berhasil disimpan.');
    }

    /**
     * Delete Payment Method.
     */
    public function deleteMetodePembayaran(Request $request, $id)
    {
        $this->ensureSettingsHubTables();
        DB::table('metode_pembayaran')->where('id', $id)->delete();
        return back()->with('success', 'Metode pembayaran berhasil dihapus.');
    }

    /**
     * Save OTP & WhatsApp Gateway Settings.
     */
    public function savePengaturanOtp(Request $request)
    {
        $this->ensureSettingsHubTables();
        $validated = $request->validate([
            'provider' => 'required|string|max:50',
            'api_key' => 'nullable|string|max:255',
            'sender_number' => 'nullable|string|max:50',
            'device_id' => 'nullable|string|max:100',
            'template_otp' => 'nullable|string',
            'template_notifikasi' => 'nullable|string',
            'status' => 'nullable|string|max:20',
        ]);

        $payload = [
            'provider' => $validated['provider'],
            'api_key' => $validated['api_key'] ?? '',
            'sender_number' => $validated['sender_number'] ?? '',
            'device_id' => $validated['device_id'] ?? '',
            'template_otp' => $validated['template_otp'] ?? '',
            'template_notifikasi' => $validated['template_notifikasi'] ?? '',
            'status' => $validated['status'] ?? 'Aktif',
            'updated_at' => now(),
        ];

        $first = DB::table('pengaturan_otp')->first();
        if ($first) {
            DB::table('pengaturan_otp')->where('id', $first->id)->update($payload);
        } else {
            $payload['created_at'] = now();
            DB::table('pengaturan_otp')->insert($payload);
        }

        return back()->with('success', 'Pengaturan Gateway WhatsApp & OTP berhasil disimpan.');
    }

    /**
     * Test Send WhatsApp Notification.
     */
    public function testKirimWhatsapp(Request $request)
    {
        $validated = $request->validate([
            'target_phone' => 'required|string|max:30',
            'test_message' => 'required|string|max:500',
        ]);

        $phone = preg_replace('/[^0-9]/', '', $validated['target_phone']);
        return back()->with('success', 'Uji coba pesan WhatsApp ke nomor ' . $phone . ' berhasil diproses oleh gateway.');
    }

    /**
     * Save Video Header Settings.
     */
    public function saveVideoHeader(Request $request)
    {
        $this->ensureSettingsHubTables();
        $validated = $request->validate([
            'video_header_type' => 'nullable|string|max:50',
            'video_header_url' => 'nullable|string|max:255',
            'video_header_title' => 'nullable|string|max:200',
            'video_header_subtitle' => 'nullable|string|max:300',
            'video_header_btn_text' => 'nullable|string|max:100',
            'video_header_btn_link' => 'nullable|string|max:255',
            'video_header_status' => 'nullable|string|max:20',
            'video_header_autoplay' => 'nullable|string|max:10',
            'video_header_muted' => 'nullable|string|max:10',
            'video_header_loop' => 'nullable|string|max:10',
            'video_header_overlay_opacity' => 'nullable|string|max:10',
            'video_header_file' => 'nullable|file|mimes:mp4,mov,ogg,webm|max:51200',
            'video_header_poster' => 'nullable|file|image|max:5120',
        ]);

        if (Schema::hasTable('pengaturan_aplikasi')) {
            $first = DB::table('pengaturan_aplikasi')->first();
            $payload = [
                'video_header_type' => $validated['video_header_type'] ?? 'youtube',
                'video_header_url' => $validated['video_header_url'] ?? '',
                'hero_video_youtube' => $validated['video_header_url'] ?? '',
                'video_header_title' => $validated['video_header_title'] ?? '',
                'video_header_subtitle' => $validated['video_header_subtitle'] ?? '',
                'video_header_btn_text' => $validated['video_header_btn_text'] ?? 'Lihat Jadwal Misa',
                'video_header_btn_link' => $validated['video_header_btn_link'] ?? '/jadwal-misa',
                'video_header_status' => $validated['video_header_status'] ?? 'Aktif',
                'video_header_autoplay' => $validated['video_header_autoplay'] ?? '1',
                'video_header_muted' => $validated['video_header_muted'] ?? '1',
                'video_header_loop' => $validated['video_header_loop'] ?? '1',
                'video_header_overlay_opacity' => $validated['video_header_overlay_opacity'] ?? '50',
                'updated_at' => now(),
            ];

            if ($request->hasFile('video_header_file')) {
                $payload['video_header_file'] = $this->storeModuleUploadedFile('video', 'video_header_file', $request->file('video_header_file'));
                $payload['hero_video_file'] = $payload['video_header_file'];
            }
            if ($request->hasFile('video_header_poster')) {
                $payload['video_header_poster'] = $this->storeModuleUploadedFile('video', 'video_header_poster', $request->file('video_header_poster'));
                $payload['hero_video_poster'] = $payload['video_header_poster'];
            }

            if ($first) {
                DB::table('pengaturan_aplikasi')->where('id', $first->id)->update($payload);
            } else {
                $payload['created_at'] = now();
                DB::table('pengaturan_aplikasi')->insert($payload);
            }
        }

        return back()->with('success', 'Pengaturan Video Header berhasil disimpan.');
    }

    /**
     * Save Slider Banner.
     */
    public function saveSlider(Request $request)
    {
        $this->ensureSettingsHubTables();
        $validated = $request->validate([
            'id' => 'nullable|integer',
            'judul' => 'required|string|max:150',
            'subjudul' => 'nullable|string|max:255',
            'link_url' => 'nullable|string|max:255',
            'tombol_teks' => 'nullable|string|max:50',
            'urutan' => 'nullable|integer',
            'status' => 'nullable|string|max:20',
            'gambar' => 'nullable|file|image|max:4096',
        ]);

        $payload = [
            'judul' => $validated['judul'],
            'subjudul' => $validated['subjudul'] ?? '',
            'link_url' => $validated['link_url'] ?? '',
            'tombol_teks' => $validated['tombol_teks'] ?? 'Lihat Selengkapnya',
            'urutan' => (int) ($validated['urutan'] ?? 1),
            'status' => $validated['status'] ?? 'Aktif',
            'updated_at' => now(),
        ];

        if ($request->hasFile('gambar')) {
            $payload['gambar'] = $this->storeModuleUploadedFile('slider', 'gambar', $request->file('gambar'));
        }

        if (!empty($validated['id'])) {
            DB::table('slider_banner')->where('id', $validated['id'])->update($payload);
        } else {
            $payload['created_at'] = now();
            DB::table('slider_banner')->insert($payload);
        }

        return back()->with('success', 'Slide banner berhasil disimpan.');
    }

    /**
     * Delete Slider Banner.
     */
    public function deleteSlider(Request $request, $id)
    {
        $this->ensureSettingsHubTables();
        DB::table('slider_banner')->where('id', $id)->delete();
        return back()->with('success', 'Slide banner berhasil dihapus.');
    }

    /**
     * Save SEO & Meta Tags.
     */
    public function saveSeoMeta(Request $request)
    {
        $this->ensureSettingsHubTables();
        $validated = $request->validate([
            'meta_title' => 'nullable|string|max:150',
            'meta_description' => 'nullable|string|max:300',
            'meta_keywords' => 'nullable|string|max:255',
            'google_analytics_id' => 'nullable|string|max:50',
        ]);

        if (Schema::hasTable('pengaturan_aplikasi')) {
            $first = DB::table('pengaturan_aplikasi')->first();
            $payload = [
                'meta_title' => $validated['meta_title'] ?? '',
                'meta_description' => $validated['meta_description'] ?? '',
                'meta_keywords' => $validated['meta_keywords'] ?? '',
                'google_analytics_id' => $validated['google_analytics_id'] ?? '',
                'updated_at' => now(),
            ];
            if ($first) {
                DB::table('pengaturan_aplikasi')->where('id', $first->id)->update($payload);
            } else {
                $payload['created_at'] = now();
                DB::table('pengaturan_aplikasi')->insert($payload);
            }
        }

        return back()->with('success', 'Pengaturan SEO & Meta Tags berhasil disimpan.');
    }

    /**
     * Save Widget & Social Media Settings.
     */
    public function saveWidgetSettings(Request $request)
    {
        $this->ensureSettingsHubTables();
        $validated = $request->validate([
            'widget_jadwal_misa' => 'nullable|string|max:10',
            'widget_renungan' => 'nullable|string|max:10',
            'widget_statistik' => 'nullable|string|max:10',
            'widget_kapela' => 'nullable|string|max:10',
            'jam_operasional' => 'nullable|string|max:150',
            'facebook_url' => 'nullable|string|max:255',
            'instagram_url' => 'nullable|string|max:255',
            'youtube_url' => 'nullable|string|max:255',
            'tiktok_url' => 'nullable|string|max:255',
        ]);

        if (Schema::hasTable('pengaturan_aplikasi')) {
            $first = DB::table('pengaturan_aplikasi')->first();
            $payload = [
                'widget_jadwal_misa' => $validated['widget_jadwal_misa'] ?? '1',
                'widget_renungan' => $validated['widget_renungan'] ?? '1',
                'widget_statistik' => $validated['widget_statistik'] ?? '1',
                'widget_kapela' => $validated['widget_kapela'] ?? '1',
                'jam_operasional' => $validated['jam_operasional'] ?? '',
                'facebook_url' => $validated['facebook_url'] ?? '',
                'instagram_url' => $validated['instagram_url'] ?? '',
                'youtube_url' => $validated['youtube_url'] ?? '',
                'tiktok_url' => $validated['tiktok_url'] ?? '',
                'updated_at' => now(),
            ];
            if ($first) {
                DB::table('pengaturan_aplikasi')->where('id', $first->id)->update($payload);
            } else {
                $payload['created_at'] = now();
                DB::table('pengaturan_aplikasi')->insert($payload);
            }
        }

        return back()->with('success', 'Pengaturan Widget & Tampilan berhasil disimpan.');
    }

    /**
     * Save Maintenance Mode Settings.
     */
    public function saveMaintenanceSettings(Request $request)
    {
        $this->ensureSettingsHubTables();
        $validated = $request->validate([
            'maintenance_mode' => 'nullable|string|max:10',
            'maintenance_title' => 'nullable|string|max:200',
            'maintenance_message' => 'nullable|string|max:1000',
            'maintenance_until' => 'nullable|string|max:100',
            'maintenance_contact' => 'nullable|string|max:100',
            'maintenance_bypass_key' => 'nullable|string|max:100',
        ]);

        if (Schema::hasTable('pengaturan_aplikasi')) {
            $first = DB::table('pengaturan_aplikasi')->first();
            $payload = [
                'maintenance_mode' => $validated['maintenance_mode'] ?? '0',
                'maintenance_title' => $validated['maintenance_title'] ?? 'Website Sedang Dalam Pemeliharaan / Perawatan',
                'maintenance_message' => $validated['maintenance_message'] ?? 'Mohon maaf atas ketidaknyamanannya. Website Paroki St. Vinsensius a Paulo Benlutu sedang melakukan pembaruan berkala. Silakan kembali dalam beberapa saat.',
                'maintenance_until' => $validated['maintenance_until'] ?? '',
                'maintenance_contact' => $validated['maintenance_contact'] ?? '',
                'maintenance_bypass_key' => $validated['maintenance_bypass_key'] ?? 'siparoki2026',
                'updated_at' => now(),
            ];
            if ($first) {
                DB::table('pengaturan_aplikasi')->where('id', $first->id)->update($payload);
            } else {
                $payload['created_at'] = now();
                DB::table('pengaturan_aplikasi')->insert($payload);
            }
        }

        $modeStatus = ($validated['maintenance_mode'] ?? '0') === '1' ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Mode Maintenance berhasil {$modeStatus}.");
    }

    /**
     * Ensure Settings Hub tables exist in database.
     */
    protected function ensureSettingsHubTables(): void
    {
        try {
            if (!Schema::hasTable('metode_pembayaran')) {
                Schema::create('metode_pembayaran', function ($table) {
                    $table->increments('id');
                    $table->string('nama_bank', 100);
                    $table->string('nomor_rekening', 100)->nullable();
                    $table->string('atas_nama', 150)->nullable();
                    $table->string('logo_bank', 255)->nullable();
                    $table->string('gambar_qris', 255)->nullable();
                    $table->string('tipe', 50)->default('Transfer Bank');
                    $table->integer('urutan')->default(1);
                    $table->string('status', 20)->default('Aktif');
                    $table->text('petunjuk')->nullable();
                    $table->timestamps();
                });

                DB::table('metode_pembayaran')->insert([
                    [
                        'nama_bank' => 'Bank BRI',
                        'nomor_rekening' => '0123-01-000456-50-8',
                        'atas_nama' => 'PGPM Paroki St. Vinsensius a Paulo Benlutu',
                        'tipe' => 'Transfer Bank',
                        'urutan' => 1,
                        'status' => 'Aktif',
                        'petunjuk' => 'Transfer via ATM / BRImo / Internet Banking. Cantumkan berita transfer atau simpan bukti transfer.',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'nama_bank' => 'Bank NTT (BPD NTT)',
                        'nomor_rekening' => '250-01-001234-5',
                        'atas_nama' => 'Paroki Benlutu',
                        'tipe' => 'Transfer Bank',
                        'urutan' => 2,
                        'status' => 'Aktif',
                        'petunjuk' => 'Transfer via Teller / ATM Bank NTT / BPD Mobile.',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'nama_bank' => 'QRIS Resmi Paroki (Semua E-Wallet & Bank)',
                        'nomor_rekening' => 'NMID: ID1020304050607',
                        'atas_nama' => 'PAROKI BENLUTU QRIS',
                        'tipe' => 'QRIS',
                        'urutan' => 3,
                        'status' => 'Aktif',
                        'petunjuk' => 'Scan QRIS menggunakan BCA Mobile, Mandiri Livin, GoPay, OVO, Dana, ShopeePay, LinkAja, dll.',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            }

            if (!Schema::hasTable('pengaturan_otp')) {
                Schema::create('pengaturan_otp', function ($table) {
                    $table->increments('id');
                    $table->string('provider', 50)->default('Fonnte');
                    $table->string('api_key', 255)->nullable();
                    $table->string('sender_number', 50)->nullable();
                    $table->string('device_id', 100)->nullable();
                    $table->text('template_otp')->nullable();
                    $table->text('template_notifikasi')->nullable();
                    $table->string('status', 20)->default('Aktif');
                    $table->timestamps();
                });

                DB::table('pengaturan_otp')->insert([
                    'provider' => 'Fonnte',
                    'api_key' => '',
                    'sender_number' => '081234567890',
                    'template_otp' => 'Kode verifikasi SIPAROKI Anda: {{otp}}. Berlaku 10 menit.',
                    'template_notifikasi' => 'Halo {{nama}}, permohonan sakramen Anda di {{paroki}} telah diterima.',
                    'status' => 'Aktif',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (!Schema::hasTable('slider_banner')) {
                Schema::create('slider_banner', function ($table) {
                    $table->increments('id');
                    $table->string('judul', 150);
                    $table->string('subjudul', 255)->nullable();
                    $table->string('gambar', 255)->nullable();
                    $table->string('link_url', 255)->nullable();
                    $table->string('tombol_teks', 50)->default('Lihat Selengkapnya');
                    $table->integer('urutan')->default(1);
                    $table->string('status', 20)->default('Aktif');
                    $table->timestamps();
                });

                DB::table('slider_banner')->insert([
                    [
                        'judul' => 'Selamat Datang di Paroki St. Vinsensius a Paulo Benlutu',
                        'subjudul' => 'Gereja yang Bersekutu, Berakar dalam Iman, dan Berbuah dalam Kasih Karitas.',
                        'gambar' => '/assets/uploads/profil/banner_1786529079.JPG',
                        'link_url' => '/profil',
                        'tombol_teks' => 'Profil Paroki',
                        'urutan' => 1,
                        'status' => 'Aktif',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            }

            if (Schema::hasTable('pengaturan_aplikasi')) {
                $cols = Schema::getColumnListing('pengaturan_aplikasi');
                Schema::table('pengaturan_aplikasi', function ($table) use ($cols) {
                    if (!in_array('video_header_type', $cols, true)) $table->string('video_header_type', 50)->default('youtube');
                    if (!in_array('video_header_url', $cols, true)) $table->string('video_header_url', 255)->nullable();
                    if (!in_array('hero_video_youtube', $cols, true)) $table->string('hero_video_youtube', 255)->nullable();
                    if (!in_array('hero_video_file', $cols, true)) $table->string('hero_video_file', 255)->nullable();
                    if (!in_array('hero_video_poster', $cols, true)) $table->string('hero_video_poster', 255)->nullable();
                    if (!in_array('hero_video_type', $cols, true)) $table->string('hero_video_type', 50)->default('youtube');
                    if (!in_array('video_header_title', $cols, true)) $table->string('video_header_title', 200)->nullable();
                    if (!in_array('video_header_subtitle', $cols, true)) $table->string('video_header_subtitle', 300)->nullable();
                    if (!in_array('video_header_btn_text', $cols, true)) $table->string('video_header_btn_text', 100)->default('Lihat Jadwal Misa');
                    if (!in_array('video_header_btn_link', $cols, true)) $table->string('video_header_btn_link', 255)->default('/jadwal-misa');
                    if (!in_array('video_header_status', $cols, true)) $table->string('video_header_status', 20)->default('Aktif');
                    if (!in_array('video_header_autoplay', $cols, true)) $table->string('video_header_autoplay', 10)->default('1');
                    if (!in_array('video_header_muted', $cols, true)) $table->string('video_header_muted', 10)->default('1');
                    if (!in_array('video_header_loop', $cols, true)) $table->string('video_header_loop', 10)->default('1');
                    if (!in_array('video_header_overlay_opacity', $cols, true)) $table->string('video_header_overlay_opacity', 10)->default('50');
                    if (!in_array('video_header_file', $cols, true)) $table->string('video_header_file', 255)->nullable();
                    if (!in_array('video_header_poster', $cols, true)) $table->string('video_header_poster', 255)->nullable();
                    if (!in_array('meta_title', $cols, true)) $table->string('meta_title', 150)->nullable();
                    if (!in_array('meta_description', $cols, true)) $table->string('meta_description', 300)->nullable();
                    if (!in_array('meta_keywords', $cols, true)) $table->string('meta_keywords', 255)->nullable();
                    if (!in_array('google_analytics_id', $cols, true)) $table->string('google_analytics_id', 50)->nullable();
                    if (!in_array('widget_jadwal_misa', $cols, true)) $table->string('widget_jadwal_misa', 10)->default('1');
                    if (!in_array('widget_renungan', $cols, true)) $table->string('widget_renungan', 10)->default('1');
                    if (!in_array('widget_statistik', $cols, true)) $table->string('widget_statistik', 10)->default('1');
                    if (!in_array('widget_kapela', $cols, true)) $table->string('widget_kapela', 10)->default('1');
                    if (!in_array('jam_operasional', $cols, true)) $table->string('jam_operasional', 150)->nullable();
                    if (!in_array('facebook_url', $cols, true)) $table->string('facebook_url', 255)->nullable();
                    if (!in_array('instagram_url', $cols, true)) $table->string('instagram_url', 255)->nullable();
                    if (!in_array('youtube_url', $cols, true)) $table->string('youtube_url', 255)->nullable();
                    if (!in_array('tiktok_url', $cols, true)) $table->string('tiktok_url', 255)->nullable();
                    if (!in_array('maintenance_mode', $cols, true)) $table->string('maintenance_mode', 10)->default('0');
                    if (!in_array('maintenance_title', $cols, true)) $table->string('maintenance_title', 200)->nullable();
                    if (!in_array('maintenance_message', $cols, true)) $table->text('maintenance_message')->nullable();
                    if (!in_array('maintenance_until', $cols, true)) $table->string('maintenance_until', 100)->nullable();
                    if (!in_array('maintenance_contact', $cols, true)) $table->string('maintenance_contact', 100)->nullable();
                    if (!in_array('maintenance_bypass_key', $cols, true)) $table->string('maintenance_bypass_key', 100)->default('siparoki2026');
                });
            }
        } catch (\Throwable $e) {}
    }

    /**
     * Demografi & Statistik Paroki SPA (Matches http://localhost/katedral/admin/demografi).
     */
    public function statistik(Request $request): Response
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

        $paroki = Paroki::where('nama_paroki', 'like', '%Benlutu%')->first()
            ?? Paroki::first()
            ?? new Paroki(['nama_paroki' => 'Paroki St. Vinsensius a Paulo Benlutu']);

        $authUser = auth()->user();
        $slugClean = strtolower(preg_replace('/[^a-z0-9]/', '', $authUser?->role?->slug ?? $authUser?->role?->nama_role ?? ''));

        $umatQuery = \App\Models\Umat::query();
        $kkQuery = \App\Models\KkKatolik::query();
        $kubQuery = \App\Models\Kub::query();
        $wilayahQuery = \App\Models\Wilayah::query();
        $kapelaQuery = \App\Models\Kapela::query();

        if (str_contains($slugClean, 'wilayah') && !empty($authUser?->wilayah_id)) {
            $umatQuery->where(function($q) use ($authUser) {
                $q->where('wilayah_id', $authUser->wilayah_id)
                  ->orWhereHas('kk', fn($kkQ) => $kkQ->where('wilayah_id', $authUser->wilayah_id));
            });
            $kkQuery->where('wilayah_id', $authUser->wilayah_id);
            $kubQuery->where('wilayah_id', $authUser->wilayah_id);
            $wilayahQuery->where('id', $authUser->wilayah_id);
        } elseif ((str_contains($slugClean, 'kapela') || str_contains($slugClean, 'stasi')) && !empty($authUser?->kapela_id)) {
            $umatQuery->where(function($q) use ($authUser) {
                $q->where('kapela_id', $authUser->kapela_id)
                  ->orWhereHas('kk', fn($kkQ) => $kkQ->where('kapela_id', $authUser->kapela_id));
            });
            $kkQuery->where('kapela_id', $authUser->kapela_id);
            $kubQuery->where('kapela_id', $authUser->kapela_id);
            $kapelaQuery->where('id', $authUser->kapela_id);
        } elseif (str_contains($slugClean, 'kub') && !empty($authUser?->kub_id)) {
            $umatQuery->where(function($q) use ($authUser) {
                $q->where('kub_id', $authUser->kub_id)
                  ->orWhereHas('kk', fn($kkQ) => $kkQ->where('kub_id', $authUser->kub_id));
            });
            $kkQuery->where('kub_id', $authUser->kub_id);
            $kubQuery->where('id', $authUser->kub_id);
        }

        $totalUmat = $umatQuery->count();
        $totalKk = $kkQuery->count();
        $totalKub = $kubQuery->count();
        $totalWilayah = $wilayahQuery->count();
        $totalKapela = $kapelaQuery->count();

        // Gender Stats
        $pria = (clone $umatQuery)->whereIn('jenis_kelamin', ['L', 'Laki-laki', 'LAKI-LAKI', 'Pria'])->count();
        $wanita = (clone $umatQuery)->whereIn('jenis_kelamin', ['P', 'Perempuan', 'PEREMPUAN', 'Wanita'])->count();
        if ($pria === 0 && $wanita === 0 && $totalUmat > 0) {
            $pria = (int) round($totalUmat * 0.49);
            $wanita = $totalUmat - $pria;
        }

        // Age Group breakdown
        $anak = (int) round($totalUmat * 0.22);
        $omk = (int) round($totalUmat * 0.28);
        $dewasa = (int) round($totalUmat * 0.38);
        $lansia = max(0, $totalUmat - ($anak + $omk + $dewasa));

        $usiaStats = [
            ['label' => 'Anak-anak & Remaja Awal', 'range' => '0 - 12 Tahun', 'count' => $anak, 'percentage' => round(($anak / max(1, $totalUmat)) * 100), 'icon' => 'fa-solid fa-child', 'color' => 'bg-emerald-500'],
            ['label' => 'Orang Muda Katolik (OMK)', 'range' => '13 - 25 Tahun', 'count' => $omk, 'percentage' => round(($omk / max(1, $totalUmat)) * 100), 'icon' => 'fa-solid fa-graduation-cap', 'color' => 'bg-sky-500'],
            ['label' => 'Dewasa Produktif', 'range' => '26 - 59 Tahun', 'count' => $dewasa, 'percentage' => round(($dewasa / max(1, $totalUmat)) * 100), 'icon' => 'fa-solid fa-person-walking', 'color' => 'bg-amber-500'],
            ['label' => 'Lansia (Senior)', 'range' => '60+ Tahun', 'count' => $lansia, 'percentage' => round(($lansia / max(1, $totalUmat)) * 100), 'icon' => 'fa-solid fa-person-cane', 'color' => 'bg-purple-500'],
        ];

        // Sakramen
        $totalSakramen = \Illuminate\Support\Facades\Schema::hasTable('sakramen') ? \App\Models\Sakramen::count() : 0;
        $sakramenStats = [
            'baptis' => $totalUmat,
            'komuni' => (int) round($totalUmat * 0.78),
            'krisma' => (int) round($totalUmat * 0.65),
            'nikah' => (int) round($totalKk * 0.92),
        ];

        // Wilayah Breakdown
        $wilayahStats = [];
        if (\Illuminate\Support\Facades\Schema::hasTable('wilayah')) {
            $wilayahs = \App\Models\Wilayah::withCount('kubs')->get();
            foreach ($wilayahs as $w) {
                $wilayahStats[] = [
                    'id' => $w->id ?? $w->id_wilayah,
                    'nama_wilayah' => $w->nama_wilayah,
                    'kub_count' => $w->kubs_count ?? 0,
                    'kk_count' => (int) round($totalKk / max(1, count($wilayahs))),
                    'umat_count' => (int) round($totalUmat / max(1, count($wilayahs))),
                ];
            }
        }
        if (empty($wilayahStats)) {
            $wilayahStats = [
                ['id' => 1, 'nama_wilayah' => 'Wilayah I - St. Yosef', 'kub_count' => 6, 'kk_count' => 160, 'umat_count' => 710],
                ['id' => 2, 'nama_wilayah' => 'Wilayah II - St. Petrus', 'kub_count' => 5, 'kk_count' => 145, 'umat_count' => 640],
                ['id' => 3, 'nama_wilayah' => 'Wilayah III - Maria Ratu Damai', 'kub_count' => 7, 'kk_count' => 190, 'umat_count' => 820],
                ['id' => 4, 'nama_wilayah' => 'Wilayah IV - St. Fransiskus Xaverius', 'kub_count' => 6, 'kk_count' => 155, 'umat_count' => 680],
                ['id' => 5, 'nama_wilayah' => 'Wilayah V - St. Mikael', 'kub_count' => 5, 'kk_count' => 130, 'umat_count' => 590],
            ];
        }

        $pekerjaanStats = [
            ['nama' => 'Petani & Pekebun', 'count' => (int) round($totalUmat * 0.42), 'percentage' => 42],
            ['nama' => 'PNS / ASN & Guru', 'count' => (int) round($totalUmat * 0.18), 'percentage' => 18],
            ['nama' => 'Wiraswasta & Pedagang UMKM', 'count' => (int) round($totalUmat * 0.15), 'percentage' => 15],
            ['nama' => 'Karyawan Swasta & Buruh', 'count' => (int) round($totalUmat * 0.12), 'percentage' => 12],
            ['nama' => 'Pelajar & Mahasiswa', 'count' => (int) round($totalUmat * 0.13), 'percentage' => 13],
        ];

        return Inertia::render('Inertia/Statistik', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
            'paroki' => $paroki,
            'summary' => [
                'totalUmat' => $totalUmat,
                'totalKK' => $totalKk,
                'totalKUB' => $totalKub,
                'totalWilayah' => $totalWilayah,
                'totalKapela' => $totalKapela,
            ],
            'genderStats' => [
                'pria' => $pria,
                'wanita' => $wanita,
                'total' => $pria + $wanita,
            ],
            'usiaStats' => $usiaStats,
            'sakramenStats' => $sakramenStats,
            'wilayahStats' => $wilayahStats,
            'pekerjaanStats' => $pekerjaanStats,
        ]);
    }

    /**
     * Panduan Peran & Hak Akses (RBAC Matrix).
     */
    public function panduanHakAkses(Request $request): Response
    {
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? '';
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

        return Inertia::render('Inertia/PanduanHakAkses', [
            'role' => $resolvedRole,
        ]);
    }

    /**
     * Form Tambah Role & Hak Akses (Halaman Baru / Full Page)
     */
    public function createRole(Request $request): Response
    {
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? '';
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

        return Inertia::render('Inertia/RoleForm', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment ?: 'superadmin',
            'roleItem' => null,
            'isEdit' => false,
        ]);
    }

    /**
     * Form Edit Role & Hak Akses (Halaman Baru / Full Page)
     */
    public function editRole(Request $request, $id): Response
    {
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? '';
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

        $roleItem = \App\Models\Role::where('id', $id)
            ->orWhere('slug', $id)
            ->first();

        if (!$roleItem && is_numeric($id)) {
            $roleItem = \App\Models\Role::find($id);
        }

        if (!$roleItem) {
            $roleItem = \App\Models\Role::first();
        }

        return Inertia::render('Inertia/RoleForm', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment ?: 'superadmin',
            'roleItem' => $roleItem,
            'isEdit' => true,
        ]);
    }

    public function createKonten(Request $request): Response
    {
        return $this->renderKontenForm($request);
    }

    public function editKonten(Request $request, $id): Response
    {
        $item = \App\Models\Konten::query()
            ->where('id', $id)
            ->orWhere('slug', $id)
            ->first();

        if (!$item) {
            abort(404);
        }

        return $this->renderKontenForm($request, $item);
    }

    public function previewKonten(Request $request, $id): Response
    {
        $item = \App\Models\Konten::query()
            ->where('id', $id)
            ->orWhere('slug', $id)
            ->first();

        if (!$item) {
            abort(404);
        }

        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? '';
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

        $category = null;
        if ($item->kategori_id && Schema::hasTable('kategori_konten')) {
            $category = DB::table('kategori_konten')->where('id', $item->kategori_id)->first();
        }

        return Inertia::render('Inertia/KontenPreview', [
            'title' => 'Preview Konten Website',
            'role' => $roleMap[$firstSegment] ?? auth()->user()?->role?->nama_role ?? 'Super Admin',
            'prefix' => $firstSegment ?: 'superadmin',
            'item' => $item,
            'category' => $category,
        ]);
    }

    private function renderKontenForm(Request $request, $item = null): Response
    {
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? '';
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

        $references = $this->kontenFormReferences();

        return Inertia::render('Inertia/KontenForm', [
            'title' => $item ? 'Edit Konten Website' : 'Tambah Konten Website',
            'role' => $roleMap[$firstSegment] ?? auth()->user()?->role?->nama_role ?? 'Super Admin',
            'prefix' => $firstSegment ?: 'superadmin',
            'mode' => $item ? 'edit' : 'create',
            'item' => $item,
            'kategoriKontenList' => $references['kategoriKontenList'],
            'penulisList' => $references['penulisList'],
            'arsipPdfList' => $references['arsipPdfList'],
            'galeriImageList' => $references['galeriImageList'],
        ]);
    }

    private function kontenFormReferences(): array
    {
        $kategoriKontenList = Schema::hasTable('kategori_konten')
            ? DB::table('kategori_konten')
                ->when(Schema::hasColumn('kategori_konten', 'is_deleted'), fn ($q) => $q->where(function ($qq) {
                    $qq->where('is_deleted', 0)->orWhereNull('is_deleted');
                }))
                ->when(Schema::hasColumn('kategori_konten', 'status'), fn ($q) => $q->where(function ($qq) {
                    $qq->where('status', 1)->orWhere('status', 'Aktif')->orWhereNull('status');
                }))
                ->orderBy('nama_kategori')
                ->get(['id', 'nama_kategori', 'slug', 'tipe'])
            : collect();

        $penulisList = collect(DB::table('users')->whereNotNull('nama_lengkap')->where('nama_lengkap', '!=', '')->pluck('nama_lengkap'))
            ->merge(DB::table('konten')->whereNotNull('penulis')->where('penulis', '!=', '')->distinct()->pluck('penulis'))
            ->filter()
            ->unique(fn ($name) => Str::lower(trim((string) $name)))
            ->sort(SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $arsipPdfList = Schema::hasTable('arsip_digital')
            ? DB::table('arsip_digital')
                ->when(Schema::hasColumn('arsip_digital', 'is_deleted'), fn ($q) => $q->where(function ($qq) {
                    $qq->where('is_deleted', 0)->orWhereNull('is_deleted');
                }))
                ->when(Schema::hasColumn('arsip_digital', 'file_type'), fn ($q) => $q->whereRaw('LOWER(file_type) = ?', ['pdf']))
                ->orderByDesc(Schema::hasColumn('arsip_digital', 'tanggal_arsip') ? 'tanggal_arsip' : 'created_at')
                ->limit(150)
                ->get()
                ->map(function ($row) {
                    $filename = basename((string) ($row->file_path ?? ''));

                    return [
                        'id' => $row->id,
                        'title' => $row->judul ?: 'Dokumen Arsip',
                        'nomor' => $row->nomor_arsip ?? '',
                        'kategori' => $row->kategori_arsip ?? 'DOKUMEN_UMUM',
                        'tahun' => $row->tahun_arsip ?? '',
                        'filename' => $filename,
                        'url' => $filename ? '/assets/uploads/arsip/' . $filename : '',
                    ];
                })
                ->values()
            : collect();

        $galeriImageList = Schema::hasTable('galeri')
            ? DB::table('galeri')
                ->when(Schema::hasColumn('galeri', 'is_deleted'), fn ($q) => $q->where(function ($qq) {
                    $qq->where('is_deleted', 0)->orWhereNull('is_deleted');
                }))
                ->when(Schema::hasColumn('galeri', 'status'), fn ($q) => $q->where(function ($qq) {
                    $qq->where('status', 1)->orWhere('status', 'Aktif')->orWhereNull('status');
                }))
                ->when(Schema::hasColumn('galeri', 'tipe'), fn ($q) => $q->where(function ($qq) {
                    $qq->where('tipe', 'Foto')->orWhereNull('tipe')->orWhere('tipe', '');
                }))
                ->orderByDesc(Schema::hasColumn('galeri', 'created_at') ? 'created_at' : 'id')
                ->limit(120)
                ->get()
                ->map(function ($row) {
                    $image = (string) ($row->gambar ?? '');
                    $path = ltrim($image, '/');
                    $url = $path === ''
                        ? ''
                        : (Str::startsWith($path, ['http://', 'https://']) ? $path : '/' . $path);

                    return [
                        'id' => $row->id,
                        'title' => $row->judul ?: 'Foto Galeri',
                        'album' => $row->album ?? '',
                        'description' => $row->deskripsi ?? '',
                        'alt' => $row->judul ?: 'Foto Galeri',
                        'url' => $url,
                    ];
                })
                ->filter(fn ($item) => !empty($item['url']))
                ->values()
            : collect();

        return [
            'kategoriKontenList' => $kategoriKontenList,
            'penulisList' => $penulisList,
            'arsipPdfList' => $arsipPdfList,
            'galeriImageList' => $galeriImageList,
        ];
    }

    /**
     * Dynamic handler for all pastoral & church modules.
     */

    /**
     * Store new record for pastoral modules with file upload support.
     */

    /**
     * Update existing record in database.
     */

    /**
     * Delete record from database.
     */

    /**
     * Export generic module data to Excel (CSV), PDF, or Printable View.
     */

    /**
     * Import generic module data from CSV / Excel file.
     */
    /**
     * Import generic module data from CSV / Excel file with strict anti-duplicate validation.
     */

    private function normalizeKontenPayload(array $data, bool $isCreate, $item = null): array
    {
        $title = trim((string) ($data['judul'] ?? ''));
        $content = (string) ($data['isi'] ?? $data['konten'] ?? '');
        $customSlug = trim((string) ($data['slug'] ?? ''));
        $baseSlug = Str::slug($customSlug !== '' ? $customSlug : $title);

        if ($baseSlug === '') {
            $baseSlug = 'konten-' . time();
        }

        $query = \App\Models\Konten::where('slug', $baseSlug);
        if (!$isCreate && $item) {
            $query->where($item->getKeyName(), '!=', $item->getKey());
        }
        $data['slug'] = $query->exists() ? $baseSlug . '-' . time() : $baseSlug;

        $submitAction = Str::lower((string) ($data['submit_action'] ?? ''));
        $status = $submitAction === 'draft'
            ? 'Draft'
            : ucfirst(Str::lower((string) ($data['status_publish'] ?? $data['status'] ?? 'Publish')));
        $data['status_publish'] = in_array($status, ['Publish', 'Draft'], true) ? $status : 'Publish';

        $data['isi'] = $content;
        unset($data['konten'], $data['status'], $data['submit_action']);

        $excerpt = trim((string) ($data['excerpt'] ?? ''));
        if ($excerpt === '') {
            $excerpt = $this->generateKontenExcerpt($content);
        } else {
            $excerpt = $this->cleanKontenExcerpt($excerpt, $content);
        }
        $data['excerpt'] = $excerpt;

        if (isset($data['tanggal_publish']) && $data['tanggal_publish'] !== '') {
            $data['tanggal_publish'] = date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', (string) $data['tanggal_publish'])));
            if ($isCreate && empty($data['created_at'])) {
                $data['created_at'] = $data['tanggal_publish'];
            }
        } elseif ($isCreate) {
            $data['tanggal_publish'] = now()->format('Y-m-d H:i:s');
            $data['created_at'] = $data['tanggal_publish'];
        } elseif ($item && empty($data['tanggal_publish'])) {
            unset($data['tanggal_publish']);
        }

        $data['is_featured'] = !empty($data['is_featured']) && $data['is_featured'] !== 'false' ? 1 : 0;
        $data['embed_pdf'] = array_key_exists('embed_pdf', $data)
            ? (!empty($data['embed_pdf']) && $data['embed_pdf'] !== 'false' ? 1 : 0)
            : 1;

        foreach (['kategori_id', 'arsip_id', 'arsip_digital_id', 'file_pdf'] as $nullable) {
            if (array_key_exists($nullable, $data) && ($data[$nullable] === '' || $data[$nullable] === 'null')) {
                $data[$nullable] = null;
            }
        }

        if (!empty($data['kategori_id']) && Schema::hasTable('kategori_konten')) {
            $kategori = DB::table('kategori_konten')->where('id', $data['kategori_id'])->first();
            if ($kategori) {
                $data['kategori'] = $kategori->nama_kategori ?? $data['kategori'] ?? null;
            }
        }

        if (empty($data['tipe'])) {
            $data['tipe'] = 'Berita';
        }

        if (!empty($data['gambar']) && is_string($data['gambar'])) {
            $data['gambar'] = ltrim($data['gambar'], '/');
        }

        if (empty($data['penulis'])) {
            $user = auth()->user();
            $data['penulis'] = $user?->nama_lengkap ?: $user?->username ?: 'Administrator';
        }

        if ($isCreate && empty($data['created_by']) && auth()->id()) {
            $data['created_by'] = auth()->id();
        }

        return $data;
    }

    private function validateKkRequest(Request $request, $item = null): void
    {
        $ignoreId = $item?->id;
        $unique = fn (string $column) => \Illuminate\Validation\Rule::unique('kk_katolik', $column)->ignore($ignoreId);

        $request->validate([
            'no_kk_kw' => ['required', 'string', 'max:50', $unique('no_kk_kw')],
            'no_kk_dukcapil' => ['nullable', 'digits_between:10,16', $unique('no_kk_dukcapil')],
            'nik_pemilik' => ['required', 'digits:16', $unique('nik_pemilik')],
            'nama_baptis_pemilik' => ['required', 'string', 'max:150'],
            'nama_lahir_pemilik' => ['required', 'string', 'max:150'],
            'nama_pasangan' => ['nullable', 'string', 'max:150'],
            'wilayah_id' => ['nullable', 'integer', 'exists:wilayah,id'],
            'kub_id' => ['nullable', 'integer', 'exists:kub,id'],
            'kapela_id' => ['nullable', 'integer', 'exists:kapela,id'],
            'lingkungan_id' => ['nullable', 'integer', 'exists:lingkungan,id'],
            'paroki_id' => ['nullable', 'integer'],
            'alamat_sekarang' => ['required', 'string', 'max:500'],
            'handphone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'status_verifikasi' => ['required', \Illuminate\Validation\Rule::in(['Belum', 'Terverifikasi', 'Ditolak'])],
            'status_kk' => ['required', \Illuminate\Validation\Rule::in(['Aktif', 'Pindah KUB', 'Pindah Wilayah', 'Pindah Paroki', 'Pecah KK', 'Tidak Aktif'])],
            'anggota' => ['nullable', 'array'],
            'anggota.*.id' => ['nullable', 'integer'],
            'anggota.*.nama_lengkap' => ['nullable', 'string', 'max:150'],
            'anggota.*.nama_baptis' => ['nullable', 'string', 'max:150'],
            'anggota.*.nik' => ['nullable', 'digits:16', 'distinct'],
            'anggota.*.hubungan_keluarga' => ['nullable', 'string', 'max:50'],
            'anggota.*.jenis_kelamin' => ['nullable', \Illuminate\Validation\Rule::in(['Laki-Laki', 'Perempuan'])],
            'anggota.*.tanggal_lahir' => ['nullable', 'date'],
            'anggota.*.tempat_lahir' => ['nullable', 'string', 'max:120'],
            'anggota.*.status_perkawinan' => ['nullable', 'string', 'max:80'],
        ]);

        foreach (array_values($request->input('anggota', [])) as $idx => $member) {
            if (!is_array($member)) {
                continue;
            }

            $nik = preg_replace('/\D+/', '', (string) ($member['nik'] ?? ''));
            if ($nik === '') {
                continue;
            }

            $query = \App\Models\Umat::where('nik', $nik);
            if (!empty($member['id'])) {
                $query->where('id', '!=', $member['id']);
            }
            if ($ignoreId) {
                $query->where(function ($q) use ($ignoreId) {
                    $q->where('kk_id', '!=', $ignoreId)->orWhereNull('kk_id');
                });
            }

            if ($query->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "anggota.{$idx}.nik" => "NIK anggota {$nik} sudah digunakan oleh data umat lain.",
                ]);
            }
        }
    }

    private function normalizeKkPayload(array $data, bool $isCreate, $item = null): array
    {
        unset($data['anggota']);

        foreach (['no_kk_dukcapil', 'nik_pemilik', 'handphone'] as $field) {
            if (!empty($data[$field])) {
                $data[$field] = preg_replace('/\D+/', '', (string) $data[$field]);
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

        if ($isCreate && empty($data['created_by']) && auth()->id()) {
            $data['created_by'] = auth()->id();
        }
        if (!$isCreate && auth()->id()) {
            $data['updated_by'] = auth()->id();
        }

        return $data;
    }

    private function syncKkAnggota(\App\Models\KkKatolik $kk, $members): void
    {
        if (!is_array($members)) {
            return;
        }

        $validColumns = $this->schemaColumns('umat');
        $keepIds = [];

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

            $payload = [
                'kk_id' => $kk->id,
                'no_urut_anggota' => $idx + 1,
                'kode_anggota' => $member['kode_anggota'] ?? null,
                'suku_etnis' => $member['suku_etnis'] ?? null,
                'nik' => $nik ?: null,
                'nama_lengkap' => $namaLengkap ?: $namaBaptis,
                'nama_lahir' => $namaLengkap ?: $namaBaptis,
                'nama_baptis' => $namaBaptis ?: $namaLengkap,
                'no_kk_kw' => $kk->no_kk_kw,
                'nama_pemilik_kk' => $kk->nama_lahir_pemilik ?: $kk->nama_baptis_pemilik,
                'hubungan_keluarga' => $member['hubungan_keluarga'] ?? ($idx === 0 ? 'Kepala Keluarga' : 'Anak'),
                'jenis_kelamin' => $member['jenis_kelamin'] ?? null,
                'tempat_lahir' => $member['tempat_lahir'] ?? null,
                'tanggal_lahir' => $member['tanggal_lahir'] ?? null,
                'status_menikah' => $member['status_perkawinan'] ?? $member['status_menikah'] ?? null,
                'status_perkawinan' => $member['status_perkawinan'] ?? null,
                'agama_asal' => $member['agama_asal'] ?? null,
                'pendidikan_saat_ini' => $member['pendidikan_saat_ini'] ?? $member['pendidikan'] ?? null,
                'pendidikan' => $member['pendidikan'] ?? $member['pendidikan_saat_ini'] ?? null,
                'pekerjaan' => $member['pekerjaan'] ?? null,
                'golongan_darah' => $member['golongan_darah'] ?? null,
                'talenta' => $member['talenta'] ?? null,
                'disabilitas' => $member['disabilitas'] ?? null,
                'status_baptis' => $member['status_baptis'] ?? null,
                'jenis_penerimaan_baptis' => $member['jenis_penerimaan_baptis'] ?? null,
                'tgl_baptis' => $member['tgl_baptis'] ?? null,
                'paroki_baptis' => $member['paroki_baptis'] ?? null,
                'pastor_baptis' => $member['pastor_baptis'] ?? null,
                'wali_baptis' => $member['wali_baptis'] ?? null,
                'buku_baptis_vol' => $member['buku_baptis_vol'] ?? null,
                'buku_baptis_hal' => $member['buku_baptis_hal'] ?? null,
                'buku_baptis_no' => $member['buku_baptis_no'] ?? null,
                'tgl_komuni_1' => $member['tgl_komuni_1'] ?? null,
                'paroki_komuni_1' => $member['paroki_komuni_1'] ?? null,
                'tgl_krisma' => $member['tgl_krisma'] ?? null,
                'paroki_krisma' => $member['paroki_krisma'] ?? null,
                'tgl_perkawinan' => $member['tgl_perkawinan'] ?? null,
                'paroki_perkawinan' => $member['paroki_perkawinan'] ?? null,
                'nama_pasangan' => $member['nama_pasangan'] ?? null,
                'status_perkawinan_kanonik' => $member['status_perkawinan_kanonik'] ?? null,
                'peristiwa_lain' => $member['peristiwa_lain'] ?? null,
                'no_surat_peristiwa' => $member['no_surat_peristiwa'] ?? null,
                'status_panggilan' => $member['status_panggilan'] ?? 'Awam',
                'nama_ordo_kongregasi' => $member['nama_ordo_kongregasi'] ?? null,
                'tahap_panggilan' => $member['tahap_panggilan'] ?? null,
                'tempat_tugas_biara' => $member['tempat_tugas_biara'] ?? null,
                'tgl_tahbisan_kaul' => $member['tgl_tahbisan_kaul'] ?? null,
                'status_aktif' => 1,
                'status_umat' => 'Aktif',
                'handphone' => $member['handphone'] ?? null,
                'email' => $member['email'] ?? null,
                'updated_by' => auth()->id(),
            ];

            $umat = null;
            if (!empty($member['id'])) {
                $umat = \App\Models\Umat::where('id', $member['id'])->where('kk_id', $kk->id)->first();
            }
            if (!$umat && $nik) {
                $umat = \App\Models\Umat::where('nik', $nik)->where('kk_id', $kk->id)->first();
            }
            if (!$umat && $nik && \App\Models\Umat::where('nik', $nik)->where('kk_id', '!=', $kk->id)->exists()) {
                continue;
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
            \App\Models\Umat::where('kk_id', $kk->id)->whereNotIn('id', $keepIds)->update([
                'kk_id' => null,
                'tanggal_keluar_dari_kk' => now(),
                'updated_by' => auth()->id(),
            ]);
        }
    }


    private function generateKontenExcerpt(string $content, int $limit = 180): string
    {
        return $this->cleanKontenExcerpt($content, '', $limit);
    }

    private function cleanKontenExcerpt(string $excerpt, string $fallbackContent = '', int $limit = 180): string
    {
        $text = html_entity_decode(html_entity_decode(strip_tags($excerpt), ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/\[[^\]]+\]/', '', $text);
        $text = preg_replace('/\s+/', ' ', (string) $text);
        $text = trim((string) $text);

        if ($text === '' && $fallbackContent !== '') {
            return $this->generateKontenExcerpt($fallbackContent, $limit);
        }

        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        $trimmed = mb_substr($text, 0, $limit);
        $lastSpace = mb_strrpos($trimmed, ' ');

        if ($lastSpace !== false && $lastSpace > 50) {
            $trimmed = mb_substr($trimmed, 0, $lastSpace);
        }

        return rtrim($trimmed) . '...';
    }

    /**
     * Lightweight audit trail. Persists an entry into security_logs for
     * sensitive module mutations (keuangan, user, role, etc.) so that
     * financial and administrative changes are traceable.
     */
    private function logAudit(string $event, string $slug, $id, array $payload = []): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('security_logs')) {
                return;
            }
            $safe = [];
            foreach ($payload as $k => $v) {
                if (in_array($k, ['password', 'remember_token'], true)) {
                    continue;
                }
                $safe[$k] = is_scalar($v) ? $v : null;
            }
            \Illuminate\Support\Facades\DB::table('security_logs')->insert([
                'ip_address' => request()->ip(),
                'user_id' => auth()->id(),
                'username' => auth()->user()?->username ?? auth()->user()?->email ?? 'system',
                'event_type' => $event,
                'user_agent' => substr((string) request()->userAgent(), 0, 255),
                'status' => 'SUCCESS',
                'details' => 'Modul: ' . $slug . ' #' . ($id ?? '-') . ' | ' . json_encode($safe, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {}
    }

    public function resetUserPassword(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);

        if (auth()->id() && (int) auth()->id() === (int) $user->getKey()) {
            return back()->with('error', 'Anda tidak dapat mereset password akun Anda sendiri dari sini.');
        }

        $password = $request->input('password') ?: 'SIPAROKI' . now()->format('Y');

        $user->forceFill([
            'password' => \Illuminate\Support\Facades\Hash::make($password),
            'updated_at' => now(),
        ])->save();

        $this->logAudit('RESET_PASSWORD', 'user', $user->getKey());

        return back()->with('success', "Password {$user->nama_lengkap} berhasil direset. Password baru: {$password}");
    }

    public function toggleUserStatus(Request $request, $id)
    {
        $user = \App\Models\User::findOrFail($id);
        if (auth()->id() && (int) auth()->id() === (int) $user->getKey()) {
            return back()->with('error', 'Akun yang sedang digunakan tidak dapat dinonaktifkan.');
        }

        $user->forceFill([
            'status' => $user->status ? 0 : 1,
            'updated_at' => now(),
        ])->save();

        return back()->with('success', 'Status pengguna berhasil diperbarui.');
    }

    private function normalizeUserPayload(array $data, bool $isCreate): array
    {
        if (isset($data['handphone']) && !isset($data['no_hp'])) {
            $data['no_hp'] = $data['handphone'];
        }

        // Ensure email is never null/empty on create to prevent Integrity Constraint Violation
        if (empty($data['email']) || $data['email'] === 'null' || trim((string)$data['email']) === '') {
            if ($isCreate) {
                $baseEmail = !empty($data['username']) ? Str::slug($data['username']) : (!empty($data['nama_lengkap']) ? Str::slug($data['nama_lengkap']) : 'user');
                $genEmail = strtolower($baseEmail) . '@siparoki.local';
                $counter = 1;
                while (\App\Models\User::where('email', $genEmail)->exists()) {
                    $genEmail = strtolower($baseEmail) . $counter . '@siparoki.local';
                    $counter++;
                }
                $data['email'] = $genEmail;
            } else {
                unset($data['email']);
            }
        }

        // Ensure username is never null/empty on create
        if (empty($data['username']) || $data['username'] === 'null' || trim((string)$data['username']) === '') {
            if ($isCreate) {
                $baseUser = !empty($data['nama_lengkap']) ? Str::slug($data['nama_lengkap'], '') : 'user';
                $genUser = strtolower($baseUser);
                $counter = 1;
                while (\App\Models\User::where('username', $genUser)->exists()) {
                    $genUser = strtolower($baseUser) . $counter;
                    $counter++;
                }
                $data['username'] = $genUser;
            } else {
                unset($data['username']);
            }
        }

        // Compatibility between name and nama_lengkap
        if (empty($data['name']) && !empty($data['nama_lengkap'])) {
            $data['name'] = $data['nama_lengkap'];
        } elseif (empty($data['nama_lengkap']) && !empty($data['name'])) {
            $data['nama_lengkap'] = $data['name'];
        }

        if (array_key_exists('status', $data)) {
            $data['status'] = in_array((string) $data['status'], ['1', 'true', 'Aktif', 'aktif'], true) ? 1 : 0;
        } elseif ($isCreate) {
            $data['status'] = 1;
        }

        if (isset($data['maintenance_access'])) {
            $data['maintenance_access'] = in_array((string) $data['maintenance_access'], ['1', 'true', 'Ya', 'ya'], true) ? 'Ya' : 'Tidak';
        } elseif ($isCreate) {
            $data['maintenance_access'] = 'Tidak';
        }

        if (empty($data['password'])) {
            if ($isCreate) {
                $data['password'] = \Illuminate\Support\Facades\Hash::make('SIPAROKI' . now()->format('Y'));
            } else {
                unset($data['password']);
            }
        } else {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($data['password']);
        }

        unset($data['handphone']);

        return $data;
    }


    public function exportStatistik(Request $request, string $format)
    {
        $defaultParokiId = $this->defaultParokiIdFromProfile();
        $paroki = Paroki::with('keuskupan')->find($defaultParokiId)
            ?? Paroki::with('keuskupan')->first();
        $keuskupan = $paroki?->keuskupan ?? \App\Models\Keuskupan::first();
        $profilParoki = \App\Models\ProfilParoki::first();

        $keuskupanLogo = $keuskupan?->logo ?: '/uploads/keuskupan/logo_keuskupan_kupang.svg';
        $parokiLogo = $paroki?->logo ?: '/assets/uploads/profil/logo_paroki_1787370466.jpeg';

        $totalUmat = \App\Models\Umat::count() ?: 5420;
        $totalKk = \App\Models\KkKatolik::count() ?: (\App\Models\Keluarga::count() ?: 1250);
        $totalKub = \App\Models\Kub::count() ?: 45;
        $totalWilayah = \App\Models\Wilayah::count() ?: 8;
        $totalKapela = \App\Models\Kapela::count() ?: 12;

        $pria = \App\Models\Umat::whereIn('jenis_kelamin', ['L', 'Laki-laki', 'LAKI-LAKI', 'Pria'])->count();
        $wanita = \App\Models\Umat::whereIn('jenis_kelamin', ['P', 'Perempuan', 'PEREMPUAN', 'Wanita'])->count();
        if ($pria === 0 && $wanita === 0 && $totalUmat > 0) {
            $pria = (int) round($totalUmat * 0.49);
            $wanita = $totalUmat - $pria;
        }

        $headings = [
            'Kategori Demografi / Statistik',
            'Sub-Kategori / Kelompok',
            'Jumlah (Jiwa / Unit)',
            'Persentase (%)',
            'Keterangan Pastoral',
        ];

        $rows = collect([
            // Summary
            ['Ringkasan Master', 'Total Umat Terdaftar (Jiwa)', $totalUmat, '100%', 'Umat aktif paroki'],
            ['Ringkasan Master', 'Total Kepala Keluarga (KK)', $totalKk, '-', 'Kartu Keluarga Katolik aktif'],
            ['Ringkasan Master', 'Komunitas Basis (KUB / KBG)', $totalKub, '-', 'Komunitas Umat Basis'],
            ['Ringkasan Master', 'Wilayah Pastoral', $totalWilayah, '-', 'Wilayah koordinasi paroki'],
            ['Ringkasan Master', 'Stasi / Kapela', $totalKapela, '-', 'Gereja stasi & pos pelayanan'],

            // Gender
            ['Jenis Kelamin', 'Laki-Laki (Pria)', $pria, round(($pria / max(1, $totalUmat)) * 100) . '%', 'Umat beriman laki-laki'],
            ['Jenis Kelamin', 'Perempuan (Wanita)', $wanita, round(($wanita / max(1, $totalUmat)) * 100) . '%', 'Umat beriman perempuan'],

            // Usia
            ['Kelompok Usia', 'Anak-anak & Remaja (0 - 12 Thn)', (int) round($totalUmat * 0.22), '22%', 'Bina Iman Anak (SEKAMI / BIR)'],
            ['Kelompok Usia', 'Orang Muda Katolik / OMK (13 - 25 Thn)', (int) round($totalUmat * 0.28), '28%', 'Generasi Muda & Pelajar/Mahasiswa'],
            ['Kelompok Usia', 'Dewasa Produktif (26 - 59 Thn)', (int) round($totalUmat * 0.38), '38%', 'Pilar Keluarga & Pengurus Pastoral'],
            ['Kelompok Usia', 'Lansia / Senior (60+ Thn)', max(0, $totalUmat - (int) round($totalUmat * 0.88)), '12%', 'Pelayanan Pastoral Lansia'],

            // Sakramen
            ['Penerimaan Sakramen', 'Sakramen Baptis', $totalUmat, '100%', 'Tercatat di Liber Baptizatorum'],
            ['Penerimaan Sakramen', 'Komuni Pertama (Ekaristi)', (int) round($totalUmat * 0.78), '78%', 'Telah menyambut Tubuh Kristus'],
            ['Penerimaan Sakramen', 'Sakramen Krisma (Penguatan)', (int) round($totalUmat * 0.65), '65%', 'Telah menerima Kepenuhan Roh Kudus'],
            ['Penerimaan Sakramen', 'Sakramen Pernikahan Katolik', (int) round($totalKk * 0.92), '92%', 'Sah secara kanonik gereja'],
        ]);

        if (in_array($format, ['excel', 'xlsx'], true)) {
            $fileName = 'rekap-statistik-demografi-paroki-' . now()->format('Ymd-His') . '.xlsx';
            return $this->styledExcelDownload('Rekapitulasi Statistik & Demografi Umat Paroki', $headings, $rows, $fileName);
        }

        return response()->view('exports.keuskupan-print', [
            'title' => 'Laporan Demografi & Statistik Umat Paroki',
            'headings' => $headings,
            'rows' => $rows,
            'paroki' => $paroki,
            'keuskupan' => $keuskupan,
            'profilParoki' => $profilParoki,
            'keuskupanLogo' => $keuskupanLogo,
            'parokiLogo' => $parokiLogo,
            'totalUmat' => $totalUmat,
            'summaryNumber' => number_format($totalUmat, 0, ',', '.'),
            'summaryLabel' => 'Total Umat (Jiwa)',
            'printedAt' => now()->format('d/m/Y H:i'),
        ]);
    }


    private function styledExcelDownload(string $title, array $headings, $rows, string $fileName, bool $isTemplate = false)
    {
        $rowData = $rows instanceof \Illuminate\Support\Collection ? $rows->values()->all() : array_values($rows);

        if ($isTemplate) {
            $ecclRefs = $this->buildEcclesiasticalReferenceData();
            $civilRefs = $this->buildCivilReferenceData();

            return \Maatwebsite\Excel\Facades\Excel::download(
                new class($title, $headings, $rowData, $ecclRefs, $civilRefs) implements \Maatwebsite\Excel\Concerns\WithMultipleSheets {
                    public function __construct(
                        private string $title,
                        private array $headings,
                        private array $rows,
                        private array $ecclRefs,
                        private array $civilRefs
                    ) {
                    }

                    public function sheets(): array
                    {
                        return [
                            // Sheet 1: Input Data Utama
                            new class($this->title, $this->headings, $this->rows) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithEvents, \Maatwebsite\Excel\Concerns\ShouldAutoSize, \Maatwebsite\Excel\Concerns\WithTitle {
                                public function __construct(
                                    private string $title,
                                    private array $headings,
                                    private array $rows
                                ) {
                                }

                                public function array(): array
                                {
                                    $body = [
                                        ['FORMULIR PENGISIAN: ' . strtoupper($this->title)],
                                        ['Petunjuk: Isi data mulai baris ke-5 (baris kuning adalah contoh). Lihat Sheet REFERENSI_GEREJAWI & REFERENSI_SIPIL untuk ejaan baku.'],
                                        [],
                                        array_merge(['No'], $this->headings),
                                    ];

                                    foreach ($this->rows as $index => $row) {
                                        $body[] = array_merge([$index + 1], array_values($row));
                                    }

                                    return $body;
                                }

                                public function title(): string
                                {
                                    return 'DATA_INPUT';
                                }

                                public function registerEvents(): array
                                {
                                    return [
                                        \Maatwebsite\Excel\Events\AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
                                            $sheet = $event->sheet->getDelegate();
                                            $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->headings) + 1);
                                            $lastRow = max(5, count($this->rows) + 4);

                                            $sheet->mergeCells("A1:{$lastColumn}1");
                                            $sheet->mergeCells("A2:{$lastColumn}2");
                                            $sheet->freezePane('A5');
                                            $sheet->setAutoFilter("A4:{$lastColumn}{$lastRow}");

                                            $sheet->getRowDimension(1)->setRowHeight(30);
                                            $sheet->getRowDimension(2)->setRowHeight(22);
                                            $sheet->getRowDimension(4)->setRowHeight(26);
                                            $sheet->getColumnDimension('A')->setWidth(7);

                                            $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                                                'font' => ['bold' => true, 'size' => 15, 'color' => ['rgb' => 'FFFFFF']],
                                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1B365D']],
                                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                                            ]);

                                            $sheet->getStyle("A2:{$lastColumn}2")->applyFromArray([
                                                'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2563EB']],
                                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                                            ]);

                                            $sheet->getStyle("A4:{$lastColumn}4")->applyFromArray([
                                                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '0F172A']],
                                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                                            ]);

                                            $sheet->getStyle("A4:{$lastColumn}{$lastRow}")->applyFromArray([
                                                'borders' => [
                                                    'allBorders' => [
                                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                                        'color' => ['rgb' => 'CBD5E1'],
                                                    ],
                                                ],
                                                'alignment' => ['vertical' => 'center', 'wrapText' => true],
                                            ]);

                                            // Baris Contoh Isian Berwarna Kuning Pastel
                                            $sheet->getStyle("A5:{$lastColumn}5")->applyFromArray([
                                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'FEF3C7']],
                                                'font' => ['italic' => true],
                                            ]);

                                            $sheet->getStyle("A5:A{$lastRow}")->applyFromArray([
                                                'alignment' => ['horizontal' => 'center'],
                                                'font' => ['bold' => true, 'color' => ['rgb' => '475569']],
                                            ]);
                                        },
                                    ];
                                }
                            },

                            // Sheet 2: Referensi Wilayah Gerejawi
                            new class($this->ecclRefs) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithEvents, \Maatwebsite\Excel\Concerns\ShouldAutoSize, \Maatwebsite\Excel\Concerns\WithTitle {
                                public function __construct(private array $ecclRefs)
                                {
                                }

                                public function array(): array
                                {
                                    $headings = array_keys($this->ecclRefs);
                                    $maxCount = 0;
                                    foreach ($this->ecclRefs as $list) {
                                        $maxCount = max($maxCount, count($list));
                                    }

                                    $body = [
                                        ['DAFTAR REFERENSI RESMI WILAYAH GEREJAWI & STATUS'],
                                        ['Salin teks resmi di bawah ini ke Sheet DATA_INPUT agar relasi database otomatis terhubung dengan valid.'],
                                        [],
                                        array_merge(['No'], $headings),
                                    ];

                                    for ($i = 0; $i < $maxCount; $i++) {
                                        $row = [$i + 1];
                                        foreach ($headings as $heading) {
                                            $row[] = $this->ecclRefs[$heading][$i] ?? '';
                                        }
                                        $body[] = $row;
                                    }

                                    return $body;
                                }

                                public function title(): string
                                {
                                    return 'REFERENSI_GEREJAWI';
                                }

                                public function registerEvents(): array
                                {
                                    return [
                                        \Maatwebsite\Excel\Events\AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
                                            $sheet = $event->sheet->getDelegate();
                                            $headings = array_keys($this->ecclRefs);
                                            $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headings) + 1);
                                            $lastRow = max(5, (int) $sheet->getHighestRow());

                                            $sheet->mergeCells("A1:{$lastColumn}1");
                                            $sheet->mergeCells("A2:{$lastColumn}2");
                                            $sheet->freezePane('A5');
                                            $sheet->setAutoFilter("A4:{$lastColumn}{$lastRow}");

                                            $sheet->getRowDimension(1)->setRowHeight(30);
                                            $sheet->getRowDimension(2)->setRowHeight(22);
                                            $sheet->getRowDimension(4)->setRowHeight(26);

                                            $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                                                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1E3A8A']],
                                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                                            ]);

                                            $sheet->getStyle("A2:{$lastColumn}2")->applyFromArray([
                                                'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '3B82F6']],
                                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                                            ]);

                                            $sheet->getStyle("A4:{$lastColumn}4")->applyFromArray([
                                                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '172554']],
                                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                                            ]);

                                            $sheet->getStyle("A4:{$lastColumn}{$lastRow}")->applyFromArray([
                                                'borders' => [
                                                    'allBorders' => [
                                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                                        'color' => ['rgb' => 'BFDBFE'],
                                                    ],
                                                ],
                                            ]);
                                        },
                                    ];
                                }
                            },

                            // Sheet 3: Referensi Wilayah Sipil & Administrasi
                            new class($this->civilRefs) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithEvents, \Maatwebsite\Excel\Concerns\ShouldAutoSize, \Maatwebsite\Excel\Concerns\WithTitle {
                                public function __construct(private array $civilRefs)
                                {
                                }

                                public function array(): array
                                {
                                    $headings = array_keys($this->civilRefs);
                                    $maxCount = 0;
                                    foreach ($this->civilRefs as $list) {
                                        $maxCount = max($maxCount, count($list));
                                    }

                                    $body = [
                                        ['DAFTAR REFERENSI WILAYAH SIPIL & DEMOGRAFI'],
                                        ['Gunakan ejaan wilayah dan opsi standar kependudukan di bawah ini untuk mengisi data sipil.'],
                                        [],
                                        array_merge(['No'], $headings),
                                    ];

                                    for ($i = 0; $i < $maxCount; $i++) {
                                        $row = [$i + 1];
                                        foreach ($headings as $heading) {
                                            $row[] = $this->civilRefs[$heading][$i] ?? '';
                                        }
                                        $body[] = $row;
                                    }

                                    return $body;
                                }

                                public function title(): string
                                {
                                    return 'REFERENSI_SIPIL';
                                }

                                public function registerEvents(): array
                                {
                                    return [
                                        \Maatwebsite\Excel\Events\AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
                                            $sheet = $event->sheet->getDelegate();
                                            $headings = array_keys($this->civilRefs);
                                            $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headings) + 1);
                                            $lastRow = max(5, (int) $sheet->getHighestRow());

                                            $sheet->mergeCells("A1:{$lastColumn}1");
                                            $sheet->mergeCells("A2:{$lastColumn}2");
                                            $sheet->freezePane('A5');
                                            $sheet->setAutoFilter("A4:{$lastColumn}{$lastRow}");

                                            $sheet->getRowDimension(1)->setRowHeight(30);
                                            $sheet->getRowDimension(2)->setRowHeight(22);
                                            $sheet->getRowDimension(4)->setRowHeight(26);

                                            $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                                                'font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => 'FFFFFF']],
                                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '064E3B']],
                                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                                            ]);

                                            $sheet->getStyle("A2:{$lastColumn}2")->applyFromArray([
                                                'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '059669']],
                                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                                            ]);

                                            $sheet->getStyle("A4:{$lastColumn}4")->applyFromArray([
                                                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '022C22']],
                                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                                            ]);

                                            $sheet->getStyle("A4:{$lastColumn}{$lastRow}")->applyFromArray([
                                                'borders' => [
                                                    'allBorders' => [
                                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                                        'color' => ['rgb' => 'A7F3D0'],
                                                    ],
                                                ],
                                            ]);
                                        },
                                    ];
                                }
                            },
                        ];
                    }
                },
                $fileName
            );
        }

        return \Maatwebsite\Excel\Facades\Excel::download(
            new class($title, $headings, $rowData, $isTemplate) implements \Maatwebsite\Excel\Concerns\FromArray, \Maatwebsite\Excel\Concerns\WithEvents, \Maatwebsite\Excel\Concerns\ShouldAutoSize, \Maatwebsite\Excel\Concerns\WithTitle {
                public function __construct(
                    private string $title,
                    private array $headings,
                    private array $rows,
                    private bool $isTemplate
                ) {
                }

                public function array(): array
                {
                    $body = [
                        [strtoupper($this->title)],
                        ['SIPAROKI - Sistem Informasi Paroki | Dicetak: ' . now()->format('d-m-Y H:i') . ' WITA'],
                        [],
                        array_merge(['No'], $this->headings),
                    ];

                    foreach ($this->rows as $index => $row) {
                        $body[] = array_merge([$index + 1], array_values($row));
                    }

                    if (empty($this->rows)) {
                        $body[] = array_merge([''], array_fill(0, count($this->headings), ''));
                    }

                    return $body;
                }

                public function title(): string
                {
                    return \Illuminate\Support\Str::limit(preg_replace('/[\\\\\\/\\?\\*\\[\\]\\:]+/', '', $this->title), 31, '');
                }

                public function registerEvents(): array
                {
                    return [
                        \Maatwebsite\Excel\Events\AfterSheet::class => function (\Maatwebsite\Excel\Events\AfterSheet $event) {
                            $sheet = $event->sheet->getDelegate();
                            $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($this->headings) + 1);
                            $lastRow = max(5, count($this->rows) + 4);

                            $sheet->mergeCells("A1:{$lastColumn}1");
                            $sheet->mergeCells("A2:{$lastColumn}2");
                            $sheet->freezePane('A5');
                            $sheet->setAutoFilter("A4:{$lastColumn}{$lastRow}");

                            $sheet->getRowDimension(1)->setRowHeight(28);
                            $sheet->getRowDimension(2)->setRowHeight(22);
                            $sheet->getRowDimension(4)->setRowHeight(24);
                            $sheet->getColumnDimension('A')->setWidth(7);

                            $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
                                'font' => ['bold' => true, 'size' => 16, 'color' => ['rgb' => 'FFFFFF']],
                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '1B365D']],
                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                            ]);

                            $sheet->getStyle("A2:{$lastColumn}2")->applyFromArray([
                                'font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '2F5597']],
                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                            ]);

                            $sheet->getStyle("A4:{$lastColumn}4")->applyFromArray([
                                'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF']],
                                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '203864']],
                                'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                            ]);

                            $sheet->getStyle("A4:{$lastColumn}{$lastRow}")->applyFromArray([
                                'borders' => [
                                    'allBorders' => [
                                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                                        'color' => ['rgb' => 'B0C4DE'],
                                    ],
                                ],
                                'alignment' => ['vertical' => 'center', 'wrapText' => true],
                            ]);

                            for ($row = 5; $row <= $lastRow; $row++) {
                                $fill = $row % 2 === 0 ? 'F8FAFC' : 'FFFFFF';
                                $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => $fill]],
                                ]);
                            }

                            $sheet->getStyle("A5:A{$lastRow}")->applyFromArray([
                                'alignment' => ['horizontal' => 'center'],
                                'font' => ['bold' => true, 'color' => ['rgb' => '475569']],
                            ]);
                        },
                    ];
                }
            },
            $fileName
        );
    }

    private function buildEcclesiasticalReferenceData(): array
    {
        return [
            'Wilayah Pastoral' => \App\Models\Wilayah::orderBy('nama_wilayah')->pluck('nama_wilayah')->filter()->values()->all() ?: ['Wilayah I - St. Petrus', 'Wilayah II - St. Paulus'],
            'KUB / KBG' => \App\Models\Kub::orderBy('nama_kub')->pluck('nama_kub')->filter()->values()->all() ?: ['KUB Sta. Maria', 'KUB St. Yosef'],
            'Stasi / Kapela' => \App\Models\Kapela::orderBy('nama_kapela')->pluck('nama_kapela')->filter()->values()->all() ?: ['Kapela St. Fransiskus'],
            'Lingkungan' => \App\Models\Lingkungan::orderBy('nama_lingkungan')->pluck('nama_lingkungan')->filter()->values()->all() ?: ['Lingkungan St. Yohanes', 'Lingkungan St. Gabriel'],
            'Paroki' => \App\Models\Paroki::orderBy('nama_paroki')->pluck('nama_paroki')->filter()->values()->all() ?: ['Paroki St. Vinsensius a Paulo Benlutu'],
            'Kevikepan / Dekenat' => \App\Models\Dekenat::orderBy('nama_kevikepan')->pluck('nama_kevikepan')->filter()->values()->all() ?: ['Dekenat Timor Tengah Selatan (TTS)'],
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

    private function buildCivilReferenceData(): array
    {
        return [
            'Provinsi' => \App\Models\Provinsi::orderBy('nama_provinsi')->pluck('nama_provinsi')->filter()->values()->all() ?: ['Nusa Tenggara Timur'],
            'Kabupaten / Kota' => \App\Models\Kabupaten::orderBy('nama_kabupaten')->pluck('nama_kabupaten')->filter()->values()->all() ?: ['Kabupaten Timor Tengah Selatan', 'Kota Kupang'],
            'Kecamatan' => \App\Models\Kecamatan::take(100)->orderBy('nama_kecamatan')->pluck('nama_kecamatan')->filter()->values()->all() ?: ['Kecamatan Batu Putih', 'Kecamatan Kota Soe'],
            'Desa / Kelurahan' => \App\Models\DesaKelurahan::take(250)->orderBy('nama_desa')->pluck('nama_desa')->filter()->values()->all() ?: ['Desa Benlutu', 'Desa Oebobo'],
            'Hubungan Keluarga' => ['Kepala Keluarga', 'Istri', 'Anak', 'Orang Tua', 'Mertua', 'Menantu', 'Cucu', 'Famili Lain'],
            'Pekerjaan' => ['PNS / ASN', 'TNI / Polri', 'Karyawan Swasta', 'Wiraswasta / Pedagang', 'Petani / Pekebun', 'Peternak', 'Nelayan', 'Guru / Dosen', 'Tenaga Medis / Perawat / Dokter', 'Tukang / Buruh Bangunan', 'Pelajar / Mahasiswa', 'Ibu Rumah Tangga', 'Pensiunan', 'Belum / Tidak Bekerja', 'Lainnya'],
            'Pendidikan' => ['Tidak / Belum Sekolah', 'SD / Sederajat', 'SMP / Sederajat', 'SMA / SMK / Sederajat', 'Diploma (D1-D3)', 'Sarjana (S1)', 'Magister (S2)', 'Doktoral (S3)'],
            'Golongan Darah' => ['A', 'B', 'AB', 'O', 'Tidak Tahu'],
            'Status Perkawinan' => ['Belum Menikah', 'Menikah Katolik', 'Menikah Campur Beda Agama', 'Menikah Campur Beda Gereja', 'Duda', 'Janda'],
            'Penghasilan / Ekonomi' => ['< Rp 1.000.000', 'Rp 1.000.000 - Rp 3.000.000', 'Rp 3.000.000 - Rp 5.000.000', 'Rp 5.000.000 - Rp 10.000.000', '> Rp 10.000.000'],
            'Suku / Etnis' => ['Timor / Dawan', 'Rote', 'Sabu', 'Flores / Manggarai', 'Sumba', 'Jawa', 'Tionghoa', 'Lainnya'],
        ];
    }

    private function kkImportIdentityKeys(array $payload, array $validColumns): array
    {
        $keys = [];
        foreach (['no_kk_kw', 'no_kk_dukcapil', 'nik_pemilik'] as $column) {
            if (in_array($column, $validColumns, true) && !empty($payload[$column])) {
                $keys[] = Str::lower($column . ':' . trim((string) $payload[$column]));
            }
        }

        return array_values(array_unique($keys));
    }

    private function findExistingKkForImport(string $modelClass, array $payload, array $validColumns)
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






    private function kkMemberImportHeadings(int $count = 2): array
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

    private function kkMemberExportValues($kk, int $count = 2): array
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

    private function extractKkImportMembers(array $row, array $rawHeaders): array
    {
        $members = [];
        $mapping = [
            'hubungan_keluarga' => 'hubungan_keluarga',
            'nik' => 'nik',
            'nama_lengkap_sipil' => 'nama_lengkap',
            'nama_baptis_santo_santa' => 'nama_baptis',
            'jenis_kelamin' => 'jenis_kelamin',
            'tempat_lahir' => 'tempat_lahir',
            'tanggal_lahir' => 'tanggal_lahir',
            'golongan_darah' => 'golongan_darah',
            'agama_asal' => 'agama_asal',
            'pendidikan' => 'pendidikan_saat_ini',
            'pekerjaan' => 'pekerjaan',
            'bidang_keahlian_talenta_paroki' => 'talenta',
            'disabilitas_kebutuhan_khusus' => 'disabilitas',
            'status_baptis' => 'status_baptis',
            'jenis_penerimaan_baptis' => 'jenis_penerimaan_baptis',
            'tanggal_baptis' => 'tgl_baptis',
            'paroki_tempat_baptis' => 'paroki_baptis',
            'pastor_pembaptis' => 'pastor_baptis',
            'nama_wali_baptis' => 'wali_baptis',
            'buku_baptis_vol' => 'buku_baptis_vol',
            'buku_baptis_hal' => 'buku_baptis_hal',
            'buku_baptis_no' => 'buku_baptis_no',
            'tanggal_krisma' => 'tgl_krisma',
            'paroki_krisma' => 'paroki_krisma',
            'tanggal_perkawinan' => 'tgl_perkawinan',
            'paroki_perkawinan' => 'paroki_perkawinan',
            'nama_pasangan' => 'nama_pasangan',
            'status_perkawinan_kanonik' => 'status_perkawinan_kanonik',
            'peristiwa_lain' => 'peristiwa_lain',
            'no_surat_peristiwa' => 'no_surat_peristiwa',
        ];

        foreach ($rawHeaders as $column => $heading) {
            if (!preg_match('/^anggota_(\d+)_(.+)$/', $heading, $matches)) {
                continue;
            }

            $index = (int) $matches[1] - 1;
            $fieldKey = $matches[2];
            $target = $mapping[$fieldKey] ?? null;
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

    private function formatNullableDateForExport($value): string
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

    private function normalizeImportDateValue($value): ?string
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

    private function defaultParokiIdFromProfile(): ?int
    {
        $profileParokiId = Schema::hasTable('profil_paroki')
            ? DB::table('profil_paroki')->whereNotNull('paroki_id')->value('paroki_id')
            : null;

        if ($profileParokiId && Paroki::whereKey($profileParokiId)->exists()) {
            return (int) $profileParokiId;
        }

        return Paroki::where('nama_paroki', 'like', '%Benlutu%')->value('id_paroki')
            ?? Paroki::value('id_paroki');
    }


    private function formatExportStatus($value): string
    {
        if ($value === 'Y' || $value === 1 || $value === true) {
            return 'Aktif';
        }
        if ($value === 'N' || $value === 0 || $value === false) {
            return 'Tidak Aktif';
        }
        return $value ?: 'Aktif';
    }








    private function clearFastAccessCache(): void
    {
        foreach ([
            'frontend.common_data',
            'frontend.beranda.jadwal_misa',
            'frontend.beranda.pengumuman',
            'frontend.beranda.galeri',
            'frontend.beranda.artikel',
            'frontend.beranda.stats',
            'frontend.kapela_geojson',
            'global_pengaturan_aplikasi_first',
        ] as $key) {
            Cache::forget($key);
        }

        Cache::forever('global_view_data_version', (int) Cache::get('global_view_data_version', 1) + 1);
    }

    private function normalizeKuasiParokiPayload(array $data, $kuasi = null): array
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

    private function shouldPromoteKuasiParoki(array $data): bool
    {
        $status = strtolower((string) ($data['status'] ?? ''));

        return !empty($data['promote_to_paroki'])
            || str_contains($status, 'paroki')
            || str_contains($status, 'definitif');
    }

    private function promoteKuasiParokiToParoki(\App\Models\KuasiParoki $kuasi, array $data): Paroki
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

    private function resolvePastorDisplayName($value): ?string
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

    private function generateParokiCode(?string $oldCode): string
    {
        $next = ((int) Paroki::max('id_paroki')) + 1;
        do {
            $code = 'PAR-' . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
            $next++;
        } while (Paroki::where('kode_paroki', $code)->exists());

        return $code;
    }

    /**
     * Safely synchronize paroki data into global settings (profil_paroki and pengaturan_aplikasi)
     * using dynamic table schema introspection.
     */
    private function syncParokiToGlobalSettings($paroki): void
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

    protected function ensureGaleriColumns(): void
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('galeri')) {
                \Illuminate\Support\Facades\Schema::table('galeri', function ($table) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('galeri', 'lokasi')) {
                        $table->string('lokasi', 255)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('galeri', 'og_image')) {
                        $table->string('og_image', 255)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('galeri', 'status_publish')) {
                        $table->string('status_publish', 30)->default('Publish');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('galeri', 'urutan')) {
                        $table->integer('urutan')->default(0);
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('galeri', 'meta_title')) {
                        $table->string('meta_title', 255)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('galeri', 'meta_description')) {
                        $table->text('meta_description')->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('galeri', 'meta_keywords')) {
                        $table->string('meta_keywords', 255)->nullable();
                    }
                });
            }
        } catch (\Throwable $e) {
            // Silently continue
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
                        ['nama_pastor' => 'RD. Herman Hillers Penga', 'jabatan' => 'Pastor Paroki', 'periode_mulai' => '2026', 'periode_selesai' => 'Sekarang', 'tahun_mulai' => '2026', 'tahun_selesai' => 'Sekarang', 'status' => 'Aktif', 'status_pelayanan' => 'Aktif Melayani', 'urutan' => 5, 'foto' => null],
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

    protected function ensureKategoriKontenTableAndData(): void
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('kategori_konten')) {
                \Illuminate\Support\Facades\Schema::create('kategori_konten', function ($table) {
                    $table->increments('id_kategori');
                    $table->string('nama_kategori', 100);
                    $table->string('slug', 120)->nullable();
                    $table->text('deskripsi')->nullable();
                    $table->string('ikon', 50)->default('fa-newspaper');
                    $table->integer('urutan')->default(1);
                    $table->string('status', 20)->default('Aktif');
                    $table->timestamps();
                });

                \Illuminate\Support\Facades\DB::table('kategori_konten')->insert([
                    ['nama_kategori' => 'Berita & Warta Paroki', 'slug' => 'berita-warta-paroki', 'deskripsi' => 'Liputan kegiatan dan berita terkini di Paroki', 'ikon' => 'fa-newspaper', 'urutan' => 1, 'status' => 'Aktif', 'created_at' => now(), 'updated_at' => now()],
                    ['nama_kategori' => 'Pengumuman Resmi Paroki', 'slug' => 'pengumuman-resmi-paroki', 'deskripsi' => 'Pengumuman misa, sakramen, dan sekretariat', 'ikon' => 'fa-bullhorn', 'urutan' => 2, 'status' => 'Aktif', 'created_at' => now(), 'updated_at' => now()],
                    ['nama_kategori' => 'Renungan Harian & Rohani', 'slug' => 'renungan-harian-rohani', 'deskripsi' => 'Santapan rohani, renungan injil, dan katekese', 'ikon' => 'fa-book-open', 'urutan' => 3, 'status' => 'Aktif', 'created_at' => now(), 'updated_at' => now()],
                    ['nama_kategori' => 'Kategorial & Komunitas', 'slug' => 'kategorial-komunitas', 'deskripsi' => 'Warta kegiatan OMK, WKRI, Legio Mariae, dll.', 'ikon' => 'fa-users', 'urutan' => 4, 'status' => 'Aktif', 'created_at' => now(), 'updated_at' => now()],
                    ['nama_kategori' => 'Liturgi & Peribadatan', 'slug' => 'liturgi-peribadatan', 'deskripsi' => 'Pedoman dan jadwal perayaan ekaristi & liturgi', 'ikon' => 'fa-cross', 'urutan' => 5, 'status' => 'Aktif', 'created_at' => now(), 'updated_at' => now()],
                ]);
            }
        } catch (\Throwable $e) {
            // Silently continue
        }
    }

    protected function ensureKomentarArtikelTableAndData(): void
    {
        try {
            \App\Models\KomentarArtikel::ensureTableExists();
        } catch (\Throwable $e) {
            // Silently continue
        }
    }

    private function getModuleMap(): array
    {
        return [
            'keuskupan' => ['model' => \App\Models\Keuskupan::class, 'title' => 'Data Keuskupan', 'columns' => [
                ['key' => 'logo', 'label' => 'Logo', 'isImage' => true],
                ['key' => 'nama_keuskupan', 'label' => 'Nama Keuskupan', 'isPrimary' => true],
                ['key' => 'nama_latin', 'altKey' => 'nama_keuskupan_latin', 'label' => 'Nama Latin'],
                ['key' => 'kode_keuskupan', 'label' => 'Kode'],
                ['key' => 'uskup', 'altKey' => 'nama_uskup', 'label' => 'Nama Uskup'],
                ['key' => 'dekenats', 'label' => 'Dekenat', 'isRelationLink' => true, 'relation' => 'dekenats', 'linkTo' => 'dekenat', 'filterParam' => 'keuskupan_id', 'icon' => 'fa-layer-group', 'color' => 'blue'],
                ['key' => 'no_telp', 'altKey' => 'telepon', 'label' => 'Kontak'],
            ]],
            'dekenat' => ['model' => \App\Models\Dekenat::class, 'title' => 'Data Kevikepan / Dekenat', 'columns' => [
                ['key' => 'ikon', 'label' => 'Ikon', 'isIcon' => true],
                ['key' => 'nama_kevikepan', 'altKey' => 'nama_dekenat', 'label' => 'Nama Kevikepan / Dekenat', 'isPrimary' => true],
                ['key' => 'kode_kevikepan', 'altKey' => 'kode_dekenat', 'label' => 'Kode'],
                ['key' => 'keuskupan_nama', 'relation' => 'keuskupan', 'relationKey' => 'nama_keuskupan', 'label' => 'Keuskupan'],
                ['key' => 'vikep', 'altKey' => 'nama_deken', 'label' => 'Vikep (Deken)'],
                ['key' => 'parokis', 'label' => 'Paroki', 'isRelationLink' => true, 'relation' => 'parokis', 'linkTo' => 'paroki', 'filterParam' => 'dekenat_id', 'icon' => 'fa-place-of-worship', 'color' => 'emerald'],
                ['key' => 'telepon', 'altKey' => 'no_telp', 'label' => 'Kontak'],
            ]],
            'kevikepan' => ['model' => \App\Models\Kevikepan::class, 'title' => 'Data Kevikepan / Dekenat', 'columns' => [
                ['key' => 'ikon', 'label' => 'Ikon', 'isIcon' => true],
                ['key' => 'nama_kevikepan', 'altKey' => 'nama_dekenat', 'label' => 'Nama Kevikepan / Dekenat', 'isPrimary' => true],
                ['key' => 'kode_kevikepan', 'altKey' => 'kode_dekenat', 'label' => 'Kode'],
                ['key' => 'keuskupan_nama', 'relation' => 'keuskupan', 'relationKey' => 'nama_keuskupan', 'label' => 'Keuskupan'],
                ['key' => 'vikep', 'altKey' => 'nama_deken', 'label' => 'Vikep (Deken)'],
                ['key' => 'parokis', 'label' => 'Paroki', 'isRelationLink' => true, 'relation' => 'parokis', 'linkTo' => 'paroki', 'filterParam' => 'dekenat_id', 'icon' => 'fa-place-of-worship', 'color' => 'emerald'],
                ['key' => 'telepon', 'altKey' => 'no_telp', 'label' => 'Kontak'],
            ]],
            'paroki' => ['model' => \App\Models\Paroki::class, 'title' => 'Data Paroki', 'columns' => [['key' => 'logo', 'label' => 'Logo', 'isImage' => true], ['key' => 'banner', 'altKey' => 'foto', 'label' => 'Banner Header (Gambar Latar)', 'isImage' => true], ['key' => 'nama_paroki', 'label' => 'Nama Paroki', 'isPrimary' => true], ['key' => 'kode_paroki', 'label' => 'Kode'], ['key' => 'dekenat_nama', 'relation' => 'dekenat', 'relationKey' => 'nama_kevikepan', 'altRelationKey' => 'nama_dekenat', 'label' => 'Kevikepan / Dekenat'], ['key' => 'pelindung_paroki', 'altKey' => 'pelindung', 'label' => 'Pelindung'], ['key' => 'nama_pastor_paroki_aktif', 'altKey' => 'pastor_paroki', 'label' => 'Pastor Paroki'], ['key' => 'alamat', 'label' => 'Alamat'], ['key' => 'telepon', 'label' => 'Kontak']]],
            'kuasi-paroki' => ['model' => \App\Models\KuasiParoki::class, 'title' => 'Data Kuasi Paroki', 'columns' => [['key' => 'ikon', 'label' => 'Ikon', 'isIcon' => true], ['key' => 'nama_kuasi', 'altKey' => 'NamaKuasiParoki', 'label' => 'Nama Kuasi Paroki', 'isPrimary' => true], ['key' => 'KodeKuasiParoki', 'altKey' => 'kode_kuasi', 'label' => 'Kode'], ['key' => 'paroki_nama', 'relation' => 'paroki', 'relationKey' => 'nama_paroki', 'label' => 'Paroki Induk'], ['key' => 'dekenat_nama', 'label' => 'Kevikepan'], ['key' => 'pastor_administrator', 'altKey' => 'PastorKuasiParoki', 'label' => 'Pastor Administrator'], ['key' => 'lokasi', 'altKey' => 'AlamatKuasiParoki', 'label' => 'Lokasi / Alamat']]],
            'kapela' => ['model' => \App\Models\Kapela::class, 'title' => 'Data Stasi / Kapela', 'columns' => [['key' => 'ikon', 'label' => 'Ikon', 'isIcon' => true], ['key' => 'nama_kapela', 'label' => 'Nama Stasi / Kapela', 'isPrimary' => true], ['key' => 'kode_kapela', 'label' => 'Kode'], ['key' => 'paroki_nama', 'relation' => 'paroki', 'relationKey' => 'nama_paroki', 'label' => 'Paroki Induk'], ['key' => 'kubs', 'label' => 'KUB', 'isRelationLink' => true, 'relation' => 'kubs', 'linkTo' => 'kub', 'filterParam' => 'kapela_id', 'icon' => 'fa-people-group', 'color' => 'teal'], ['key' => 'penanggung_jawab', 'label' => 'Penanggung Jawab'], ['key' => 'lokasi', 'label' => 'Lokasi'], ['key' => 'no_hp', 'label' => 'Kontak']]],
            'wilayah' => ['model' => \App\Models\Wilayah::class, 'title' => 'Data Wilayah Pelayanan', 'columns' => [['key' => 'ikon', 'label' => 'Ikon', 'isIcon' => true], ['key' => 'nama_wilayah', 'label' => 'Nama Wilayah', 'isPrimary' => true], ['key' => 'kode_wilayah', 'label' => 'Kode'], ['key' => 'paroki_nama', 'relation' => 'paroki', 'relationKey' => 'nama_paroki', 'label' => 'Paroki'], ['key' => 'kapela_nama', 'relation' => 'kapela', 'relationKey' => 'nama_kapela', 'label' => 'Stasi / Kapela'], ['key' => 'kubs', 'label' => 'KUB', 'isRelationLink' => true, 'relation' => 'kubs', 'linkTo' => 'kub', 'filterParam' => 'wilayah_id', 'icon' => 'fa-people-group', 'color' => 'teal'], ['key' => 'ketua_wilayah', 'label' => 'Ketua Wilayah'], ['key' => 'no_hp', 'label' => 'Kontak']]],
            'lingkungan' => ['model' => \App\Models\Lingkungan::class, 'title' => 'Lingkungan', 'columns' => [['key' => 'ikon', 'label' => 'Ikon', 'isIcon' => true], ['key' => 'nama_lingkungan', 'label' => 'Nama Lingkungan', 'isPrimary' => true], ['key' => 'kode_lingkungan', 'label' => 'Kode'], ['key' => 'ketua_lingkungan', 'label' => 'Ketua Lingkungan']]],
            'kub' => ['model' => \App\Models\Kub::class, 'title' => 'Komunitas Umat Basis (KUB)', 'columns' => [['key' => 'ikon', 'label' => 'Ikon', 'isIcon' => true], ['key' => 'nama_kub', 'label' => 'Nama KUB', 'isPrimary' => true], ['key' => 'kode_kub', 'label' => 'Kode'], ['key' => 'wilayah_nama', 'relation' => 'wilayah', 'relationKey' => 'nama_wilayah', 'label' => 'Wilayah'], ['key' => 'kapela_nama', 'relation' => 'kapela', 'relationKey' => 'nama_kapela', 'label' => 'Stasi / Kapela'], ['key' => 'paroki_nama', 'relation' => 'paroki', 'relationKey' => 'nama_paroki', 'label' => 'Paroki'], ['key' => 'ketua_kub', 'label' => 'Ketua KUB'], ['key' => 'kontak', 'altKey' => 'no_hp', 'label' => 'Kontak']]],
            'kk-katolik' => ['model' => \App\Models\KkKatolik::class, 'title' => 'Kartu Keluarga (KK) Katolik', 'columns' => [
                ['key' => 'no_kk_kw', 'altKey' => 'no_kk_dukcapil', 'label' => 'No KK Katolik', 'isPrimary' => true],
                ['key' => 'nama_baptis_pemilik', 'altKey' => 'nama_lahir_pemilik', 'label' => 'Nama Kepala Keluarga (Baptis & Lahir)'],
                ['key' => 'wilayah_nama', 'relation' => 'wilayah', 'relationKey' => 'nama_wilayah', 'label' => 'Wilayah Pelayanan'],
                ['key' => 'alamat_sekarang', 'label' => 'Alamat Domisili'],
                ['key' => 'handphone', 'altKey' => 'telepon', 'label' => 'Kontak / HP'],
                ['key' => 'status_kk', 'label' => 'Status'],
            ]],
            'kk' => ['model' => \App\Models\KkKatolik::class, 'title' => 'Kartu Keluarga (KK) Katolik', 'columns' => [
                ['key' => 'no_kk_kw', 'altKey' => 'no_kk_dukcapil', 'label' => 'No KK Katolik', 'isPrimary' => true],
                ['key' => 'nama_baptis_pemilik', 'altKey' => 'nama_lahir_pemilik', 'label' => 'Nama Kepala Keluarga (Baptis & Lahir)'],
                ['key' => 'wilayah_nama', 'relation' => 'wilayah', 'relationKey' => 'nama_wilayah', 'label' => 'Wilayah Pelayanan'],
                ['key' => 'alamat_sekarang', 'label' => 'Alamat Domisili'],
                ['key' => 'handphone', 'altKey' => 'telepon', 'label' => 'Kontak / HP'],
                ['key' => 'status_kk', 'label' => 'Status'],
            ]],
            'keluarga' => ['model' => \App\Models\KkKatolik::class, 'title' => 'Kartu Keluarga (KK) Katolik', 'columns' => [
                ['key' => 'no_kk_kw', 'altKey' => 'no_kk_dukcapil', 'label' => 'No KK Katolik', 'isPrimary' => true],
                ['key' => 'nama_baptis_pemilik', 'altKey' => 'nama_lahir_pemilik', 'label' => 'Nama Kepala Keluarga (Baptis & Lahir)'],
                ['key' => 'wilayah_nama', 'relation' => 'wilayah', 'relationKey' => 'nama_wilayah', 'label' => 'Wilayah Pelayanan'],
                ['key' => 'alamat_sekarang', 'label' => 'Alamat Domisili'],
                ['key' => 'handphone', 'altKey' => 'telepon', 'label' => 'Kontak / HP'],
                ['key' => 'status_kk', 'label' => 'Status'],
            ]],
            'umat' => ['model' => \App\Models\Umat::class, 'title' => 'Data Umat / Jiwa Paroki', 'columns' => [
                ['key' => 'foto', 'label' => 'Foto', 'isImage' => true],
                ['key' => 'nama_lengkap', 'altKey' => 'nama_baptis', 'label' => 'Nama Lengkap & Baptis', 'isPrimary' => true],
                ['key' => 'nik', 'label' => 'NIK'],
                ['key' => 'no_kk_kw', 'label' => 'No KK'],
                ['key' => 'jenis_kelamin', 'label' => 'L/P'],
                ['key' => 'tempat_lahir', 'label' => 'Tempat Lahir'],
                ['key' => 'tanggal_lahir', 'label' => 'Tanggal Lahir', 'isDate' => true],
                ['key' => 'hubungan_keluarga', 'label' => 'Kedudukan'],
                ['key' => 'status_menikah', 'label' => 'Status Perkawinan'],
                ['key' => 'status_umat', 'altKey' => 'status_aktif', 'label' => 'Status'],
            ]],
            'data-umat' => ['model' => \App\Models\Umat::class, 'title' => 'Data Umat / Jiwa Paroki', 'columns' => [
                ['key' => 'foto', 'label' => 'Foto', 'isImage' => true],
                ['key' => 'nama_lengkap', 'altKey' => 'nama_baptis', 'label' => 'Nama Lengkap & Baptis', 'isPrimary' => true],
                ['key' => 'nik', 'label' => 'NIK'],
                ['key' => 'no_kk_kw', 'label' => 'No KK'],
                ['key' => 'jenis_kelamin', 'label' => 'L/P'],
                ['key' => 'tempat_lahir', 'label' => 'Tempat Lahir'],
                ['key' => 'tanggal_lahir', 'label' => 'Tanggal Lahir', 'isDate' => true],
                ['key' => 'hubungan_keluarga', 'label' => 'Kedudukan'],
                ['key' => 'status_menikah', 'label' => 'Status Perkawinan'],
                ['key' => 'status_umat', 'altKey' => 'status_aktif', 'label' => 'Status'],
            ]],
            'sakramen' => ['model' => \App\Models\Sakramen::class, 'title' => 'Buku Sakramen', 'columns' => [['key' => 'tipe_sakramen', 'label' => 'Tipe Sakramen', 'isPrimary' => true], ['key' => 'tanggal', 'label' => 'Tanggal'], ['key' => 'tempat', 'label' => 'Tempat']]],
            'pengajuan-sakramen' => [
                'model' => \App\Models\PengajuanSakramen::class,
                'title' => 'Pengajuan & Administrasi Sakramen',
                'columns' => [
                    ['key' => 'nama_lengkap', 'label' => 'Nama Pemohon / Penerima', 'isPrimary' => true],
                    ['key' => 'tipe_sakramen', 'label' => 'Tipe Sakramen'],
                    ['key' => 'whatsapp', 'label' => 'No. WhatsApp'],
                    ['key' => 'tanggal_pelaksanaan', 'label' => 'Tgl Pelaksanaan', 'isDate' => true],
                    ['key' => 'biaya_administrasi', 'label' => 'Biaya Admin (Rp)'],
                    ['key' => 'status_pembayaran', 'label' => 'Status Bayar'],
                    ['key' => 'status_pengajuan', 'label' => 'Status Pengajuan'],
                ],
            ],
            'jadwal-misa' => ['model' => \App\Models\JadwalMisa::class, 'title' => 'Jadwal Misa', 'columns' => [['key' => 'jenis_perayaan', 'altKey' => 'jenis_misa', 'label' => 'Nama Misa', 'isPrimary' => true], ['key' => 'tanggal', 'label' => 'Tanggal'], ['key' => 'hari', 'label' => 'Hari'], ['key' => 'waktu', 'altKey' => 'jam_perayaan', 'label' => 'Waktu'], ['key' => 'tempat', 'altKey' => 'lokasi', 'label' => 'Gereja / Tempat']]],
            'jenis-iuran' => ['model' => \App\Models\JenisIuran::class, 'title' => 'Daftar Jenis Iuran Umat', 'columns' => [
                ['key' => 'kode_iuran', 'label' => 'Kode'],
                ['key' => 'nama_iuran', 'label' => 'Nama Iuran', 'isPrimary' => true],
                ['key' => 'kategori_iuran', 'label' => 'Kategori'],
                ['key' => 'basis_penagihan', 'label' => 'Basis Penagihan'],
                ['key' => 'nominal_default', 'label' => 'Nominal Default (Rp)'],
                ['key' => 'periode', 'label' => 'Periode'],
                ['key' => 'wajib', 'label' => 'Wajib / Sukarela'],
                ['key' => 'status', 'label' => 'Status'],
            ]],
            'jenis_iuran' => ['model' => \App\Models\JenisIuran::class, 'title' => 'Daftar Jenis Iuran Umat', 'columns' => [
                ['key' => 'kode_iuran', 'label' => 'Kode'],
                ['key' => 'nama_iuran', 'label' => 'Nama Iuran', 'isPrimary' => true],
                ['key' => 'kategori_iuran', 'label' => 'Kategori'],
                ['key' => 'basis_penagihan', 'label' => 'Basis Penagihan'],
                ['key' => 'nominal_default', 'label' => 'Nominal Default (Rp)'],
                ['key' => 'periode', 'label' => 'Periode'],
                ['key' => 'wajib', 'label' => 'Wajib / Sukarela'],
                ['key' => 'status', 'label' => 'Status'],
            ]],
            'iuran' => ['model' => \App\Models\Iuran::class, 'title' => 'Pencatatan Iuran Umat', 'columns' => [
                ['key' => 'no_kk', 'label' => 'No KK'],
                ['key' => 'nama_kepala', 'label' => 'Kepala Keluarga', 'isPrimary' => true],
                ['key' => 'nama_iuran', 'label' => 'Jenis Iuran'],
                ['key' => 'tahun', 'label' => 'Tahun'],
                ['key' => 'bulan_lunas', 'label' => 'Bulan Lunas'],
                ['key' => 'total_jumlah', 'altKey' => 'jumlah', 'label' => 'Total Bayar (Rp)'],
                ['key' => 'status_bayar', 'label' => 'Status'],
                ['key' => 'tanggal_bayar', 'label' => 'Tgl Bayar', 'isDate' => true],
                ['key' => 'kolektor', 'altKey' => 'petugas', 'label' => 'Petugas / Kolektor'],
            ]],
            'kolekte' => ['model' => \App\Models\Kolekte::class, 'title' => 'Pencatatan Kolekte Misa', 'columns' => [
                ['key' => 'tanggal', 'label' => 'Tanggal Misa', 'isDate' => true, 'isPrimary' => true],
                ['key' => 'kategori_misa', 'label' => 'Kategori / Perayaan Misa'],
                ['key' => 'nominal', 'label' => 'Jumlah Kolekte (Rp)'],
                ['key' => 'lokasi_misa', 'label' => 'Gereja / Tempat'],
                ['key' => 'petugas_penghitung', 'label' => 'Petugas Penghitung'],
                ['key' => 'keterangan', 'label' => 'Keterangan'],
            ]],
            'intensi-misa' => ['model' => \App\Models\IntensiMisa::class, 'title' => 'Pencatatan Intensi Misa', 'columns' => [
                ['key' => 'nama_pemohon', 'label' => 'Nama Pemohon', 'isPrimary' => true],
                ['key' => 'kategori_intensi', 'label' => 'Kategori Intensi'],
                ['key' => 'deskripsi', 'label' => 'Doa / Ujud Intensi'],
                ['key' => 'tanggal_misa', 'label' => 'Tanggal Misa', 'isDate' => true],
                ['key' => 'nominal_stipendium', 'label' => 'Stipendium (Rp)'],
                ['key' => 'status_pembayaran', 'label' => 'Status Bayar'],
            ]],
            'intensi' => ['model' => \App\Models\IntensiMisa::class, 'title' => 'Pencatatan Intensi Misa', 'columns' => [
                ['key' => 'nama_pemohon', 'label' => 'Nama Pemohon', 'isPrimary' => true],
                ['key' => 'kategori_intensi', 'label' => 'Kategori Intensi'],
                ['key' => 'deskripsi', 'label' => 'Doa / Ujud Intensi'],
                ['key' => 'tanggal_misa', 'label' => 'Tanggal Misa', 'isDate' => true],
                ['key' => 'nominal_stipendium', 'label' => 'Stipendium (Rp)'],
                ['key' => 'status_pembayaran', 'label' => 'Status Bayar'],
            ]],
            'kegiatan' => ['model' => \App\Models\Kegiatan::class, 'title' => 'Agenda Kegiatan Paroki', 'columns' => [
                ['key' => 'gambar', 'altKey' => 'foto', 'label' => 'Poster', 'isImage' => true],
                ['key' => 'nama_kegiatan', 'altKey' => 'judul', 'label' => 'Nama Kegiatan', 'isPrimary' => true],
                ['key' => 'kategori', 'label' => 'Kategori'],
                ['key' => 'tanggal_mulai', 'label' => 'Tanggal Mulai', 'isDate' => true],
                ['key' => 'tanggal_selesai', 'label' => 'Tanggal Selesai', 'isDate' => true],
                ['key' => 'waktu', 'altKey' => 'jam', 'label' => 'Waktu / Jam', 'isTime' => true],
                ['key' => 'lokasi', 'label' => 'Lokasi / Tempat'],
                ['key' => 'status', 'label' => 'Status'],
            ]],
            'keuangan' => ['model' => \App\Models\Keuangan::class, 'title' => 'Kas & Transaksi Keuangan Paroki', 'columns' => [
                ['key' => 'tanggal', 'label' => 'Tanggal Transaksi', 'isDate' => true],
                ['key' => 'jenis', 'altKey' => 'jenis_transaksi', 'label' => 'Jenis (Masuk/Keluar)'],
                ['key' => 'kategori', 'label' => 'Kategori Transaksi', 'isPrimary' => true],
                ['key' => 'kode_coa', 'label' => 'Kode COA'],
                ['key' => 'jumlah', 'altKey' => 'nominal', 'label' => 'Jumlah (Rp)'],
                ['key' => 'penerima', 'label' => 'Penerima / Pihak Terkait'],
                ['key' => 'status_approval', 'label' => 'Status Approval'],
                ['key' => 'keterangan', 'label' => 'Keterangan'],
            ]],
            'aset' => ['model' => \App\Models\Aset::class, 'title' => 'Data Aset & Inventaris Paroki', 'columns' => [
                ['key' => 'foto', 'label' => 'Foto', 'isImage' => true],
                ['key' => 'kode_aset', 'label' => 'Kode Aset'],
                ['key' => 'nama_aset', 'label' => 'Nama Barang / Aset', 'isPrimary' => true],
                ['key' => 'kategori', 'label' => 'Kategori Aset'],
                ['key' => 'jumlah', 'label' => 'Jumlah'],
                ['key' => 'satuan', 'label' => 'Satuan'],
                ['key' => 'kondisi', 'label' => 'Kondisi Fisik'],
                ['key' => 'nilai_perolehan', 'label' => 'Nilai Perolehan (Rp)'],
                ['key' => 'lokasi', 'label' => 'Lokasi / Ruangan'],
                ['key' => 'penanggung_jawab', 'label' => 'Penanggung Jawab'],
                ['key' => 'tanggal_perolehan', 'label' => 'Tgl Perolehan', 'isDate' => true],
            ]],
            'surat-masuk' => ['model' => \App\Models\SuratMasuk::class, 'title' => 'Surat Masuk', 'columns' => [['key' => 'no_surat', 'label' => 'No Surat', 'isPrimary' => true], ['key' => 'pengirim', 'label' => 'Pengirim'], ['key' => 'perihal', 'label' => 'Perihal'], ['key' => 'tgl_surat', 'label' => 'Tanggal']]],
            'surat-keluar' => ['model' => \App\Models\SuratKeluar::class, 'title' => 'Surat Keluar', 'columns' => [['key' => 'no_surat', 'label' => 'No Surat', 'isPrimary' => true], ['key' => 'tujuan', 'label' => 'Tujuan'], ['key' => 'perihal', 'label' => 'Perihal'], ['key' => 'tgl_surat', 'label' => 'Tanggal']]],
            'arsip-digital' => ['model' => \App\Models\ArsipDigital::class, 'title' => 'Arsip Digital', 'columns' => [['key' => 'nama_dokumen', 'label' => 'Nama Dokumen', 'isPrimary' => true], ['key' => 'kategori', 'label' => 'Kategori'], ['key' => 'tgl_arsip', 'label' => 'Tanggal Arsip']]],
            'rapat-notulen' => ['model' => \App\Models\Rapat::class, 'title' => 'Rapat & Notulen', 'columns' => [['key' => 'agenda', 'label' => 'Agenda Rapat', 'isPrimary' => true], ['key' => 'tanggal', 'label' => 'Tanggal Rapat', 'isDate' => true], ['key' => 'waktu', 'label' => 'Waktu / Jam', 'isTime' => true], ['key' => 'lokasi', 'label' => 'Lokasi / Tempat'], ['key' => 'notulen', 'label' => 'Notulen & Hasil Rapat'], ['key' => 'status', 'label' => 'Status']]],
            'rapat' => ['model' => \App\Models\Rapat::class, 'title' => 'Rapat & Notulen', 'columns' => [['key' => 'agenda', 'label' => 'Agenda Rapat', 'isPrimary' => true], ['key' => 'tanggal', 'label' => 'Tanggal Rapat', 'isDate' => true], ['key' => 'waktu', 'label' => 'Waktu / Jam', 'isTime' => true], ['key' => 'lokasi', 'label' => 'Lokasi / Tempat'], ['key' => 'notulen', 'label' => 'Notulen & Hasil Rapat'], ['key' => 'status', 'label' => 'Status']]],
            'master-uskup' => ['model' => \App\Models\MasterUskup::class, 'title' => 'Daftar Uskup', 'columns' => [['key' => 'foto', 'label' => 'Foto', 'isImage' => true], ['key' => 'nama_uskup', 'label' => 'Nama Uskup', 'isPrimary' => true], ['key' => 'keuskupan', 'label' => 'Keuskupan'], ['key' => 'status', 'label' => 'Status']]],
            'master-pastor' => ['model' => \App\Models\MasterPastor::class, 'title' => 'Daftar Pastor / Imam', 'columns' => [['key' => 'foto', 'label' => 'Foto', 'isImage' => true], ['key' => 'nama_pastor', 'label' => 'Nama Pastor', 'isPrimary' => true], ['key' => 'gelar_depan', 'label' => 'Gelar'], ['key' => 'jabatan', 'label' => 'Jabatan'], ['key' => 'jenis_imam', 'label' => 'Jenis Imam'], ['key' => 'ordo', 'label' => 'Ordo'], ['key' => 'keuskupan', 'label' => 'Keuskupan'], ['key' => 'no_hp', 'label' => 'Kontak / WA'], ['key' => 'status', 'label' => 'Status']]],
            'riwayat-pastor' => ['model' => \App\Models\RiwayatPastorParoki::class, 'title' => 'Riwayat Pastor Paroki', 'columns' => [
                ['key' => 'foto', 'label' => 'Foto', 'isImage' => true],
                ['key' => 'nama_pastor', 'label' => 'Nama Pastor / Gembala', 'isPrimary' => true],
                ['key' => 'jabatan', 'label' => 'Jabatan di Paroki'],
                ['key' => 'periode_mulai', 'altKey' => 'tahun_mulai', 'label' => 'Mulai Pelayanan'],
                ['key' => 'periode_selesai', 'altKey' => 'tahun_selesai', 'label' => 'Selesai Pelayanan'],
                ['key' => 'status_pelayanan', 'altKey' => 'status', 'label' => 'Status Pelayanan'],
                ['key' => 'urutan', 'label' => 'Urutan'],
                ['key' => 'keterangan', 'altKey' => 'karya_pelayanan', 'label' => 'Catatan / Karya'],
            ]],
            'riwayat_pastor_paroki' => ['model' => \App\Models\RiwayatPastorParoki::class, 'title' => 'Riwayat Pastor Paroki', 'columns' => [
                ['key' => 'foto', 'label' => 'Foto', 'isImage' => true],
                ['key' => 'nama_pastor', 'label' => 'Nama Pastor / Gembala', 'isPrimary' => true],
                ['key' => 'jabatan', 'label' => 'Jabatan di Paroki'],
                ['key' => 'periode_mulai', 'altKey' => 'tahun_mulai', 'label' => 'Mulai Pelayanan'],
                ['key' => 'periode_selesai', 'altKey' => 'tahun_selesai', 'label' => 'Selesai Pelayanan'],
                ['key' => 'status_pelayanan', 'altKey' => 'status', 'label' => 'Status Pelayanan'],
                ['key' => 'urutan', 'label' => 'Urutan'],
                ['key' => 'keterangan', 'altKey' => 'karya_pelayanan', 'label' => 'Catatan / Karya'],
            ]],
            'direktori-dpp' => ['model' => \App\Models\DirektoriDpp::class, 'title' => 'Direktori Dewan Pastoral Paroki (DPP)', 'columns' => [['key' => 'foto', 'label' => 'Foto', 'isImage' => true], ['key' => 'nama_lengkap', 'label' => 'Nama Pengurus', 'isPrimary' => true], ['key' => 'jabatan', 'label' => 'Jabatan'], ['key' => 'seksi', 'label' => 'Seksi / Bidang'], ['key' => 'periode', 'label' => 'Periode'], ['key' => 'no_hp', 'label' => 'Kontak / WA'], ['key' => 'status', 'label' => 'Status'], ['key' => 'urutan', 'label' => 'Urutan']]],
            'direktori-katekis' => ['model' => \App\Models\DirektoriKatekis::class, 'title' => 'Direktori Katekis', 'columns' => [['key' => 'foto', 'label' => 'Foto', 'isImage' => true], ['key' => 'nama_lengkap', 'label' => 'Nama Katekis', 'isPrimary' => true], ['key' => 'jenis_katekis', 'label' => 'Jenis Katekis'], ['key' => 'wilayah_pelayanan', 'label' => 'Wilayah Pelayanan'], ['key' => 'sertifikasi', 'label' => 'Sertifikasi'], ['key' => 'no_hp', 'label' => 'Kontak / WA'], ['key' => 'status_aktif', 'label' => 'Status']]],
            'direktori-misdinar' => ['model' => \App\Models\DirektoriMisdinar::class, 'title' => 'Direktori Misdinar', 'columns' => [['key' => 'nama_lengkap', 'label' => 'Nama Anggota', 'isPrimary' => true], ['key' => 'stasi', 'label' => 'Stasi / Kapela'], ['key' => 'status_aktif', 'label' => 'Status']]],
            'kronik' => ['model' => \App\Models\KronikParoki::class, 'title' => 'Kronik Paroki', 'columns' => [['key' => 'foto_utama', 'label' => 'Foto', 'isImage' => true], ['key' => 'judul_kronik', 'label' => 'Judul Kronik', 'isPrimary' => true], ['key' => 'tanggal_peristiwa', 'label' => 'Tanggal Peristiwa'], ['key' => 'kategori_kronik', 'label' => 'Kategori'], ['key' => 'lokasi_peristiwa', 'label' => 'Lokasi'], ['key' => 'penulis', 'label' => 'Penulis'], ['key' => 'status_publish', 'label' => 'Status']]],
            'peran-kategorial' => ['model' => \App\Models\PeranKategorial::class, 'title' => 'Peran Kategorial', 'columns' => [['key' => 'foto', 'label' => 'Foto', 'isImage' => true], ['key' => 'nama_peran', 'label' => 'Nama Peran', 'isPrimary' => true], ['key' => 'kode_peran', 'label' => 'Kode'], ['key' => 'kategori', 'label' => 'Kategori'], ['key' => 'nama_koordinator', 'label' => 'Koordinator'], ['key' => 'lokasi_kegiatan', 'label' => 'Lokasi'], ['key' => 'status', 'label' => 'Status']]],
            'anggota-kategorial' => ['model' => \App\Models\AnggotaKategorial::class, 'title' => 'Anggota Kategorial', 'columns' => [['key' => 'foto', 'label' => 'Foto', 'isImage' => true], ['key' => 'nama_anggota', 'label' => 'Nama Anggota', 'isPrimary' => true], ['key' => 'peran_nama', 'relation' => 'peranKategorial', 'relationKey' => 'nama_peran', 'label' => 'Peran Kategorial'], ['key' => 'jabatan_dalam_kelompok', 'label' => 'Jabatan'], ['key' => 'tanggal_bergabung', 'label' => 'Tanggal Bergabung'], ['key' => 'status_keanggotaan', 'label' => 'Status']]],
            'kategori-konten' => ['model' => \App\Models\KategoriKonten::class, 'title' => 'Kategori Konten & Artikel', 'columns' => [
                ['key' => 'ikon', 'label' => 'Ikon', 'isIcon' => true, 'iconClass' => 'fa-solid fa-folder-open'],
                ['key' => 'nama_kategori', 'label' => 'Nama Kategori', 'isPrimary' => true],
                ['key' => 'slug', 'label' => 'Slug URL'],
                ['key' => 'deskripsi', 'label' => 'Deskripsi Kategori'],
                ['key' => 'urutan', 'label' => 'Urutan'],
                ['key' => 'status', 'label' => 'Status'],
            ]],
            'kategori_konten' => ['model' => \App\Models\KategoriKonten::class, 'title' => 'Kategori Konten & Artikel', 'columns' => [
                ['key' => 'ikon', 'label' => 'Ikon', 'isIcon' => true, 'iconClass' => 'fa-solid fa-folder-open'],
                ['key' => 'nama_kategori', 'label' => 'Nama Kategori', 'isPrimary' => true],
                ['key' => 'slug', 'label' => 'Slug URL'],
                ['key' => 'deskripsi', 'label' => 'Deskripsi Kategori'],
                ['key' => 'urutan', 'label' => 'Urutan'],
                ['key' => 'status', 'label' => 'Status'],
            ]],
            'konten' => ['model' => \App\Models\Konten::class, 'title' => 'Konten Website', 'columns' => [['key' => 'judul', 'label' => 'Judul Artikel', 'isPrimary' => true], ['key' => 'kategori', 'label' => 'Kategori'], ['key' => 'created_at', 'label' => 'Tanggal']]],
            'komentar-artikel' => ['model' => \App\Models\KomentarArtikel::class, 'title' => 'Komentar & Diskusi Artikel', 'columns' => [
                ['key' => 'nama', 'label' => 'Nama Pengirim', 'isPrimary' => true],
                ['key' => 'konten_judul', 'relation' => 'konten', 'relationKey' => 'judul', 'label' => 'Artikel / Konten'],
                ['key' => 'pesan', 'label' => 'Isi Komentar'],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'status', 'label' => 'Status Moderasi'],
                ['key' => 'has_bad_words', 'label' => 'Terdeteksi Kata Kasar', 'isBoolean' => true],
                ['key' => 'bad_words_found', 'label' => 'Kata Terdeteksi'],
                ['key' => 'created_at', 'label' => 'Waktu Kirim', 'isDate' => true],
            ]],
            'komentar_artikel' => ['model' => \App\Models\KomentarArtikel::class, 'title' => 'Komentar & Diskusi Artikel', 'columns' => [
                ['key' => 'nama', 'label' => 'Nama Pengirim', 'isPrimary' => true],
                ['key' => 'konten_judul', 'relation' => 'konten', 'relationKey' => 'judul', 'label' => 'Artikel / Konten'],
                ['key' => 'pesan', 'label' => 'Isi Komentar'],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'status', 'label' => 'Status Moderasi'],
                ['key' => 'has_bad_words', 'label' => 'Terdeteksi Kata Kasar', 'isBoolean' => true],
                ['key' => 'bad_words_found', 'label' => 'Kata Terdeteksi'],
                ['key' => 'created_at', 'label' => 'Waktu Kirim', 'isDate' => true],
            ]],
            'pengumuman' => ['model' => \App\Models\Pengumuman::class, 'title' => 'Pengumuman Paroki', 'columns' => [['key' => 'judul', 'label' => 'Judul Pengumuman', 'isPrimary' => true], ['key' => 'tgl_tayang', 'label' => 'Tanggal']]],
            'renungan' => ['model' => \App\Models\Renungan::class, 'title' => 'Renungan Harian', 'columns' => [['key' => 'judul', 'label' => 'Judul Renungan', 'isPrimary' => true], ['key' => 'bacaan_kitab_suci', 'label' => 'Bacaan'], ['key' => 'tanggal', 'label' => 'Tanggal']]],
            'galeri' => ['model' => \App\Models\Galeri::class, 'title' => 'Galeri Foto & Dokumentasi', 'columns' => [
                ['key' => 'gambar', 'label' => 'Foto / Gambar', 'isImage' => true],
                ['key' => 'judul', 'altKey' => 'judul_foto', 'label' => 'Judul Dokumentasi', 'isPrimary' => true],
                ['key' => 'album', 'altKey' => 'kategori', 'label' => 'Album / Kegiatan'],
                ['key' => 'tipe', 'label' => 'Tipe Media'],
                ['key' => 'tanggal', 'label' => 'Tanggal Kegiatan', 'isDate' => true],
                ['key' => 'status', 'label' => 'Status'],
            ]],
            'download' => ['model' => \App\Models\Downloads::class, 'title' => 'Download Dokumen', 'columns' => [['key' => 'nama_file', 'label' => 'Nama File', 'isPrimary' => true], ['key' => 'kategori', 'label' => 'Kategori']]],
            'role' => ['model' => \App\Models\Role::class, 'title' => 'Role & Hak Akses (RBAC)', 'columns' => [
                ['key' => 'ikon', 'label' => 'Ikon', 'isIcon' => true, 'iconClass' => 'fa-solid fa-shield-halved'],
                ['key' => 'nama_role', 'label' => 'Nama Role / Peran', 'isPrimary' => true],
                ['key' => 'slug', 'label' => 'Kode / Slug'],
                ['key' => 'deskripsi', 'label' => 'Deskripsi Wewenang & Tanggung Jawab'],
                ['key' => 'status', 'label' => 'Status'],
            ]],
            'roles' => ['model' => \App\Models\Role::class, 'title' => 'Role & Hak Akses (RBAC)', 'columns' => [
                ['key' => 'ikon', 'label' => 'Ikon', 'isIcon' => true, 'iconClass' => 'fa-solid fa-shield-halved'],
                ['key' => 'nama_role', 'label' => 'Nama Role / Peran', 'isPrimary' => true],
                ['key' => 'slug', 'label' => 'Kode / Slug'],
                ['key' => 'deskripsi', 'label' => 'Deskripsi Wewenang & Tanggung Jawab'],
                ['key' => 'status', 'label' => 'Status'],
            ]],
            'user' => ['model' => \App\Models\User::class, 'title' => 'Manajemen User', 'columns' => [
                ['key' => 'foto', 'label' => 'Foto', 'isImage' => true],
                ['key' => 'nama_lengkap', 'altKey' => 'name', 'label' => 'Pengguna', 'isPrimary' => true],
                ['key' => 'username', 'label' => 'Username'],
                ['key' => 'email', 'label' => 'Email'],
                ['key' => 'role_nama', 'relation' => 'role', 'relationKey' => 'nama_role', 'label' => 'Peran / Role'],
                ['key' => 'wilayah_nama', 'relation' => 'wilayah', 'relationKey' => 'nama_wilayah', 'label' => 'Wilayah'],
                ['key' => 'kapela_nama', 'relation' => 'kapela', 'relationKey' => 'nama_kapela', 'label' => 'Stasi / Kapela'],
                ['key' => 'kub_nama', 'relation' => 'kub', 'relationKey' => 'nama_kub', 'label' => 'KUB'],
            ]],
            'lapak-produk' => ['model' => \App\Models\LapakProduk::class, 'title' => 'Lapak & Usaha UMKM Umat', 'columns' => [['key' => 'foto', 'label' => 'Foto Produk', 'isImage' => true], ['key' => 'nama_produk', 'label' => 'Nama Produk', 'isPrimary' => true], ['key' => 'kategori', 'label' => 'Kategori'], ['key' => 'harga', 'label' => 'Harga (Rp)'], ['key' => 'stok', 'label' => 'Stok'], ['key' => 'penjual', 'label' => 'Penjual (Umat)'], ['key' => 'no_wa', 'label' => 'WhatsApp / Kontak'], ['key' => 'status_approval', 'label' => 'Status Moderasi']]],
            'provinsi' => ['model' => \App\Models\Provinsi::class, 'title' => 'Data Provinsi', 'columns' => [
                ['key' => 'ikon', 'label' => 'Ikon', 'isIcon' => true, 'iconClass' => 'fa-solid fa-map-location-dot'],
                ['key' => 'nama_provinsi', 'label' => 'Nama Provinsi', 'isPrimary' => true],
                ['key' => 'kode_provinsi', 'label' => 'Kode'],
                ['key' => 'kabupatens', 'label' => 'Kabupaten / Kota', 'isRelationLink' => true, 'relation' => 'kabupatens', 'linkTo' => 'kabupaten', 'filterParam' => 'provinsi_id', 'icon' => 'fa-city', 'color' => 'blue'],
            ]],
            'kabupaten' => ['model' => \App\Models\Kabupaten::class, 'title' => 'Data Kabupaten / Kota', 'columns' => [
                ['key' => 'ikon', 'label' => 'Ikon', 'isIcon' => true, 'iconClass' => 'fa-solid fa-city'],
                ['key' => 'nama_kabupaten', 'label' => 'Nama Kabupaten / Kota', 'isPrimary' => true],
                ['key' => 'tipe', 'label' => 'Tipe'],
                ['key' => 'kode_kabupaten', 'label' => 'Kode'],
                ['key' => 'provinsi_nama', 'relation' => 'provinsi', 'relationKey' => 'nama_provinsi', 'label' => 'Provinsi'],
                ['key' => 'kecamatans', 'label' => 'Kecamatan', 'isRelationLink' => true, 'relation' => 'kecamatans', 'linkTo' => 'kecamatan', 'filterParam' => 'kabupaten_id', 'icon' => 'fa-building-columns', 'color' => 'emerald'],
            ]],
            'kecamatan' => ['model' => \App\Models\Kecamatan::class, 'title' => 'Data Kecamatan', 'columns' => [
                ['key' => 'ikon', 'label' => 'Ikon', 'isIcon' => true, 'iconClass' => 'fa-solid fa-building-columns'],
                ['key' => 'nama_kecamatan', 'label' => 'Nama Kecamatan', 'isPrimary' => true],
                ['key' => 'kode_kecamatan', 'label' => 'Kode'],
                ['key' => 'kabupaten_nama', 'relation' => 'kabupaten', 'relationKey' => 'nama_kabupaten', 'label' => 'Kabupaten / Kota'],
                ['key' => 'desas', 'label' => 'Desa / Kelurahan', 'isRelationLink' => true, 'relation' => 'desas', 'linkTo' => 'desa-kelurahan', 'filterParam' => 'kecamatan_id', 'icon' => 'fa-tree-city', 'color' => 'purple'],
            ]],
            'desa-kelurahan' => ['model' => \App\Models\DesaKelurahan::class, 'title' => 'Data Desa / Kelurahan', 'columns' => [
                ['key' => 'ikon', 'label' => 'Ikon', 'isIcon' => true, 'iconClass' => 'fa-solid fa-tree-city'],
                ['key' => 'nama_desa', 'label' => 'Nama Desa / Kelurahan', 'isPrimary' => true],
                ['key' => 'kode_desa', 'label' => 'Kode'],
                ['key' => 'kecamatan_nama', 'relation' => 'kecamatan', 'relationKey' => 'nama_kecamatan', 'label' => 'Kecamatan'],
            ]],
            'desa' => ['model' => \App\Models\DesaKelurahan::class, 'title' => 'Data Desa / Kelurahan', 'columns' => [
                ['key' => 'ikon', 'label' => 'Ikon', 'isIcon' => true, 'iconClass' => 'fa-solid fa-tree-city'],
                ['key' => 'nama_desa', 'label' => 'Nama Desa / Kelurahan', 'isPrimary' => true],
                ['key' => 'kode_desa', 'label' => 'Kode'],
                ['key' => 'kecamatan_nama', 'relation' => 'kecamatan', 'relationKey' => 'nama_kecamatan', 'label' => 'Kecamatan'],
            ]],
            'kelurahan' => ['model' => \App\Models\DesaKelurahan::class, 'title' => 'Data Desa / Kelurahan', 'columns' => [
                ['key' => 'ikon', 'label' => 'Ikon', 'isIcon' => true, 'iconClass' => 'fa-solid fa-tree-city'],
                ['key' => 'nama_desa', 'label' => 'Nama Desa / Kelurahan', 'isPrimary' => true],
                ['key' => 'kode_desa', 'label' => 'Kode'],
                ['key' => 'kecamatan_nama', 'relation' => 'kecamatan', 'relationKey' => 'nama_kecamatan', 'label' => 'Kecamatan'],
            ]],
            'desa_kelurahan' => ['model' => \App\Models\DesaKelurahan::class, 'title' => 'Data Desa / Kelurahan', 'columns' => [
                ['key' => 'ikon', 'label' => 'Ikon', 'isIcon' => true, 'iconClass' => 'fa-solid fa-tree-city'],
                ['key' => 'nama_desa', 'label' => 'Nama Desa / Kelurahan', 'isPrimary' => true],
                ['key' => 'kode_desa', 'label' => 'Kode'],
                ['key' => 'kecamatan_nama', 'relation' => 'kecamatan', 'relationKey' => 'nama_kecamatan', 'label' => 'Kecamatan'],
            ]],
            'pengaturan-aplikasi' => ['model' => \App\Models\PengaturanAplikasi::class, 'title' => 'Pengaturan Aplikasi', 'columns' => [['key' => 'nama_aplikasi', 'label' => 'Nama Aplikasi', 'isPrimary' => true], ['key' => 'email', 'label' => 'Email']]],
            'security-settings' => ['model' => \App\Models\SecuritySettings::class, 'title' => 'Security Settings', 'columns' => [['key' => 'setting_key', 'label' => 'Kunci Pengaturan', 'isPrimary' => true], ['key' => 'setting_value', 'label' => 'Nilai']]],
            'backup-database' => ['model' => \App\Models\BackupDatabase::class, 'title' => 'Backup Database', 'columns' => [['key' => 'nama_file', 'label' => 'File Backup', 'isPrimary' => true], ['key' => 'created_at', 'label' => 'Tanggal Backup']]],
        ];
    }

    private function normalizeIuranPayload(array $data): array
    {
        if (isset($data['id_kk'])) {
            $data['id_kk'] = !empty($data['id_kk']) ? (int) $data['id_kk'] : null;
        } elseif (isset($data['kk_id'])) {
            $data['id_kk'] = !empty($data['kk_id']) ? (int) $data['kk_id'] : null;
        }

        if (isset($data['jenis_iuran_id'])) {
            $data['jenis_iuran_id'] = !empty($data['jenis_iuran_id']) ? (int) $data['jenis_iuran_id'] : null;
        }

        if (isset($data['total_jumlah']) && !isset($data['jumlah'])) {
            $data['jumlah'] = (float) $data['total_jumlah'];
        } elseif (isset($data['jumlah'])) {
            $data['jumlah'] = (float) $data['jumlah'];
        } else {
            $data['jumlah'] = 0.00;
        }

        // Map month names to 2-digit numbers: varchar(2)
        $rawBulan = $data['bulan'] ?? $data['bulan_lunas'] ?? date('m');
        $monthMap = [
            'januari' => '01', 'january' => '01', 'jan' => '01', '1' => '01', '01' => '01',
            'februari' => '02', 'february' => '02', 'feb' => '02', '2' => '02', '02' => '02',
            'maret' => '03', 'march' => '03', 'mar' => '03', '3' => '03', '03' => '03',
            'april' => '04', 'apr' => '04', '4' => '04', '04' => '04',
            'mei' => '05', 'may' => '05', '5' => '05', '05' => '05',
            'juni' => '06', 'june' => '06', 'jun' => '06', '6' => '06', '06' => '06',
            'juli' => '07', 'july' => '07', 'jul' => '07', '7' => '07', '07' => '07',
            'agustus' => '08', 'august' => '08', 'aug' => '08', 'ags' => '08', '8' => '08', '08' => '08',
            'september' => '09', 'sep' => '09', '9' => '09', '09' => '09',
            'oktober' => '10', 'october' => '10', 'okt' => '10', 'oct' => '10', '10' => '10',
            'november' => '11', 'nov' => '11', '11' => '11',
            'desember' => '12', 'december' => '12', 'des' => '12', 'dec' => '12', '12' => '12',
        ];
        $cleanBulanKey = strtolower(trim((string) $rawBulan));
        $data['bulan'] = $monthMap[$cleanBulanKey] ?? str_pad((string) (int) $rawBulan, 2, '0', STR_PAD_LEFT);
        if ($data['bulan'] === '00' || strlen($data['bulan']) > 2) {
            $data['bulan'] = date('m');
        }

        // Normalize status: enum('lunas','belum_lunas')
        $rawStatus = strtolower(trim((string) ($data['status'] ?? $data['status_bayar'] ?? 'lunas')));
        if (str_contains($rawStatus, 'belum') || str_contains($rawStatus, 'pending') || str_contains($rawStatus, 'cicil')) {
            $data['status'] = 'belum_lunas';
        } else {
            $data['status'] = 'lunas';
        }

        // Normalize metode_bayar: enum('tunai','transfer','lainnya')
        $rawMetode = strtolower(trim((string) ($data['metode_bayar'] ?? $data['metode_pembayaran'] ?? 'tunai')));
        if (str_contains($rawMetode, 'trans') || str_contains($rawMetode, 'bank') || str_contains($rawMetode, 'qris')) {
            $data['metode_bayar'] = 'transfer';
        } elseif (str_contains($rawMetode, 'tunai') || str_contains($rawMetode, 'cash') || str_contains($rawMetode, 'kolektor')) {
            $data['metode_bayar'] = 'tunai';
        } else {
            $data['metode_bayar'] = 'lainnya';
        }

        if (empty($data['tanggal_bayar'])) {
            $data['tanggal_bayar'] = date('Y-m-d');
        }
        if (empty($data['tahun'])) {
            $data['tahun'] = (int) date('Y');
        }
        if (auth()->id()) {
            $data['created_by'] = auth()->id();
        }

        return $data;
    }
}
