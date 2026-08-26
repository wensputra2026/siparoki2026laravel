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

trait UmatModuleTrait
{
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

}
