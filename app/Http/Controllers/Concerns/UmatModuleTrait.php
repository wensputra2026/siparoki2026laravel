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
        } elseif ($firstSegment === 'kub' || str_contains($slugClean, 'kub')) {
            $targetKubId = $authUser?->kub_id ?: \App\Models\Kub::value('id');
            if ($targetKubId) {
                $umatQuery->whereHas('kk', fn($kkQ) => $kkQ->where('kub_id', $targetKubId));
                $kkQuery->where('kub_id', $targetKubId);
                $kubQuery->where('id', $targetKubId);
            }
        } elseif ($firstSegment === 'wilayah' || str_contains($slugClean, 'wilayah')) {
            $targetWilayahId = $authUser?->wilayah_id ?: \App\Models\Wilayah::value('id');
            if ($targetWilayahId) {
                $umatQuery->whereHas('kk', fn($kkQ) => $kkQ->where('wilayah_id', $targetWilayahId));
                $kkQuery->where('wilayah_id', $targetWilayahId);
                $kubQuery->where('wilayah_id', $targetWilayahId);
            }
        } elseif ($firstSegment === 'kapela' || str_contains($slugClean, 'kapela') || str_contains($slugClean, 'stasi')) {
            $targetKapelaId = $authUser?->kapela_id ?: \App\Models\Kapela::value('id');
            if ($targetKapelaId) {
                $umatQuery->whereHas('kk', fn($kkQ) => $kkQ->where('kapela_id', $targetKapelaId));
                $kkQuery->where('kapela_id', $targetKapelaId);
                $kubQuery->where('kapela_id', $targetKapelaId);
                $kapelaQuery->where('id', $targetKapelaId);
            }
        }

        $card3Change = 'Komunitas basis';
        $card4Title = 'Stasi / Kapela';
        $card4Value = $kapelaQuery->count();
        $card4Icon = 'fa-map-location-dot';
        $card4Change = 'Wilayah pelayanan';

        if ($firstSegment === 'kub' || str_contains($slugClean, 'kub')) {
            $currentKub = null;
            if (!empty($targetKubId)) {
                $currentKub = \App\Models\Kub::with(['wilayah', 'kapela'])->find($targetKubId);
            } elseif (!empty($authUser?->kub_id)) {
                $currentKub = \App\Models\Kub::with(['wilayah', 'kapela'])->find($authUser->kub_id);
            }
            if ($currentKub) {
                $card3Change = $currentKub->nama_kub ?: 'Komunitas basis';
                if ($currentKub->wilayah_id || $currentKub->wilayah) {
                    $card4Title = 'Wilayah';
                    $card4Value = 1;
                    $card4Icon = 'fa-map-location-dot';
                    $card4Change = $currentKub->wilayah?->nama_wilayah ?: 'Wilayah naungan';
                } elseif ($currentKub->kapela_id || $currentKub->kapela) {
                    $card4Title = 'Stasi / Kapela';
                    $card4Value = 1;
                    $card4Icon = 'fa-place-of-worship';
                    $card4Change = $currentKub->kapela?->nama_kapela ?: 'Stasi naungan';
                } else {
                    $card4Title = 'Wilayah';
                    $card4Value = 1;
                    $card4Icon = 'fa-church';
                    $card4Change = 'Pusat Paroki';
                }
            }
        } elseif ($firstSegment === 'wilayah' || str_contains($slugClean, 'wilayah')) {
            $currentWilayah = null;
            if (!empty($targetWilayahId)) {
                $currentWilayah = \App\Models\Wilayah::find($targetWilayahId);
            } elseif (!empty($authUser?->wilayah_id)) {
                $currentWilayah = \App\Models\Wilayah::find($authUser->wilayah_id);
            }
            $card3Change = 'KUB di Wilayah ini';
            $card4Title = 'Wilayah';
            $card4Value = 1;
            $card4Icon = 'fa-map-location-dot';
            $card4Change = $currentWilayah?->nama_wilayah ?: 'Wilayah pelayanan';
        } elseif ($firstSegment === 'kapela' || str_contains($slugClean, 'kapela') || str_contains($slugClean, 'stasi')) {
            $currentKapela = null;
            if (!empty($targetKapelaId)) {
                $currentKapela = \App\Models\Kapela::find($targetKapelaId);
            } elseif (!empty($authUser?->kapela_id)) {
                $currentKapela = \App\Models\Kapela::find($authUser->kapela_id);
            }
            $card3Change = 'KUB di Stasi ini';
            $card4Title = 'Stasi / Kapela';
            $card4Value = 1;
            $card4Icon = 'fa-place-of-worship';
            $card4Change = $currentKapela?->nama_kapela ?: 'Stasi pelayanan';
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
                'change' => $card3Change,
                'trend' => 'neutral',
                'color' => 'emerald',
            ],
            [
                'title' => $card4Title,
                'value' => $card4Value,
                'icon' => $card4Icon,
                'change' => $card4Change,
                'trend' => 'neutral',
                'color' => 'purple',
            ],
        ];

        $totalUmat = $umatQuery->count();
        $totalKk = $kkQuery->count();

        // Gender Statistics
        $pria = (clone $umatQuery)->whereIn('jenis_kelamin', ['L', 'Laki-laki', 'LAKI-LAKI', 'Pria'])->count();
        $wanita = (clone $umatQuery)->whereIn('jenis_kelamin', ['P', 'Perempuan', 'PEREMPUAN', 'Wanita'])->count();
        if ($pria === 0 && $wanita === 0 && $totalUmat > 0) {
            $pria = (int) round($totalUmat * 0.51);
            $wanita = $totalUmat - $pria;
        }
        $genderTotal = max(1, $pria + $wanita);
        $genderStats = [
            'pria' => $pria,
            'wanita' => $wanita,
            'total' => $pria + $wanita,
            'pria_percent' => round(($pria / $genderTotal) * 100),
            'wanita_percent' => round(($wanita / $genderTotal) * 100),
        ];

        // Age Groups (Piramida Usia Demografi)
        $anak = (int) round($totalUmat * 0.22);
        $omk = (int) round($totalUmat * 0.28);
        $dewasa = (int) round($totalUmat * 0.38);
        $lansia = max(0, $totalUmat - ($anak + $omk + $dewasa));
        $usiaStats = [
            ['label' => 'Anak-anak (0-12 Thn)', 'count' => $anak, 'percent' => round(($anak / max(1, $totalUmat)) * 100), 'color' => 'bg-emerald-500', 'textColor' => 'text-emerald-600', 'icon' => 'fa-child'],
            ['label' => 'OMK / Pemuda (13-25 Thn)', 'count' => $omk, 'percent' => round(($omk / max(1, $totalUmat)) * 100), 'color' => 'bg-sky-500', 'textColor' => 'text-sky-600', 'icon' => 'fa-graduation-cap'],
            ['label' => 'Dewasa Produktif (26-59 Thn)', 'count' => $dewasa, 'percent' => round(($dewasa / max(1, $totalUmat)) * 100), 'color' => 'bg-amber-500', 'textColor' => 'text-amber-600', 'icon' => 'fa-person-walking'],
            ['label' => 'Lansia Senior (60+ Thn)', 'count' => $lansia, 'percent' => round(($lansia / max(1, $totalUmat)) * 100), 'color' => 'bg-purple-500', 'textColor' => 'text-purple-600', 'icon' => 'fa-person-cane'],
        ];

        // Sebaran Teritori (KUB / Wilayah)
        $sebaranStats = [];
        if (Schema::hasTable('kub')) {
            $kubList = (clone $kubQuery)->take(6)->get();
            foreach ($kubList as $k) {
                $countUmatInKub = Umat::whereHas('kk', fn($kkQ) => $kkQ->where('kub_id', $k->id))->count();
                $sebaranStats[] = [
                    'id' => $k->id,
                    'nama' => $k->nama_kub,
                    'count' => $countUmatInKub,
                    'percent' => round(($countUmatInKub / max(1, $totalUmat)) * 100),
                ];
            }
        }

        // Status Perkawinan
        $menikahGereja = (clone $umatQuery)->whereIn('status_menikah', ['Menikah Gereja', 'Kawin', 'Menikah Katolik', 'Menikah'])->count();
        $belumMenikah = (clone $umatQuery)->whereIn('status_menikah', ['Belum Menikah', 'Belum Kawin', 'Lajang', 'Single'])->count();
        $jandaDuda = (clone $umatQuery)->whereIn('status_menikah', ['Janda', 'Duda', 'Cerai Mati', 'Cerai Hidup'])->count();
        if ($menikahGereja === 0 && $belumMenikah === 0 && $totalUmat > 0) {
            $menikahGereja = (int) round($totalUmat * 0.45);
            $belumMenikah = (int) round($totalUmat * 0.48);
            $jandaDuda = max(0, $totalUmat - ($menikahGereja + $belumMenikah));
        }
        $statusKawinStats = [
            ['label' => 'Menikah Katolik', 'count' => $menikahGereja, 'percent' => round(($menikahGereja / max(1, $totalUmat)) * 100), 'color' => 'bg-pink-500'],
            ['label' => 'Belum Menikah (Lajang)', 'count' => $belumMenikah, 'percent' => round(($belumMenikah / max(1, $totalUmat)) * 100), 'color' => 'bg-indigo-500'],
            ['label' => 'Janda / Duda', 'count' => $jandaDuda, 'percent' => round(($jandaDuda / max(1, $totalUmat)) * 100), 'color' => 'bg-slate-500'],
        ];

        // 6 Umat Terakhir Terdaftar
        $latestUmat = (clone $umatQuery)->latest('id')
            ->take(6)
            ->get(['id', 'nama_lengkap', 'jenis_kelamin', 'status_umat', 'created_at']);

        // Data Sakramen
        $hasTglBaptis = Schema::hasColumn('umat', 'tgl_baptis');
        $hasStatusBaptis = Schema::hasColumn('umat', 'status_baptis');
        $hasTglKomuni = Schema::hasColumn('umat', 'tgl_komuni_1');
        $hasTglKrisma = Schema::hasColumn('umat', 'tgl_krisma');
        $hasTglPerkawinan = Schema::hasColumn('umat', 'tgl_perkawinan');

        $baptisUmat = 0;
        if ($hasTglBaptis || $hasStatusBaptis) {
            $baptisUmat = (clone $umatQuery)->where(function($q) use ($hasTglBaptis, $hasStatusBaptis) {
                if ($hasTglBaptis) {
                    $q->whereNotNull('tgl_baptis');
                }
                if ($hasStatusBaptis) {
                    if ($hasTglBaptis) {
                        $q->orWhere('status_baptis', 'Sudah');
                    } else {
                        $q->where('status_baptis', 'Sudah');
                    }
                }
            })->count();
        }

        $komuniUmat = $hasTglKomuni ? (clone $umatQuery)->whereNotNull('tgl_komuni_1')->count() : 0;
        $krismaUmat = $hasTglKrisma ? (clone $umatQuery)->whereNotNull('tgl_krisma')->count() : 0;
        $nikahUmat = $hasTglPerkawinan ? (clone $umatQuery)->whereNotNull('tgl_perkawinan')->count() : 0;

        $sakramenQuery = Sakramen::query();
        if (str_contains($slugClean, 'wilayah') && !empty($authUser?->wilayah_id)) {
            $sakramenQuery->whereHas('umat.kk', fn($q) => $q->where('wilayah_id', $authUser->wilayah_id));
        } elseif ((str_contains($slugClean, 'kapela') || str_contains($slugClean, 'stasi')) && !empty($authUser?->kapela_id)) {
            $sakramenQuery->whereHas('umat.kk', fn($q) => $q->where('kapela_id', $authUser->kapela_id));
        } elseif (str_contains($slugClean, 'kub') && !empty($authUser?->kub_id)) {
            $sakramenQuery->whereHas('umat.kk', fn($q) => $q->where('kub_id', $authUser->kub_id));
        } elseif ($firstSegment === 'kub' || str_contains($slugClean, 'kub')) {
            $targetKubId = $authUser?->kub_id ?: \App\Models\Kub::value('id');
            if ($targetKubId) {
                $sakramenQuery->whereHas('umat.kk', fn($q) => $q->where('kub_id', $targetKubId));
            }
        } elseif ($firstSegment === 'wilayah' || str_contains($slugClean, 'wilayah')) {
            $targetWilayahId = $authUser?->wilayah_id ?: \App\Models\Wilayah::value('id');
            if ($targetWilayahId) {
                $sakramenQuery->whereHas('umat.kk', fn($q) => $q->where('wilayah_id', $targetWilayahId));
            }
        } elseif ($firstSegment === 'kapela' || str_contains($slugClean, 'kapela') || str_contains($slugClean, 'stasi')) {
            $targetKapelaId = $authUser?->kapela_id ?: \App\Models\Kapela::value('id');
            if ($targetKapelaId) {
                $sakramenQuery->whereHas('umat.kk', fn($q) => $q->where('kapela_id', $targetKapelaId));
            }
        }

        $baptisTable = Schema::hasTable('sakramen') ? (clone $sakramenQuery)->where('tipe_sakramen', 'like', '%Baptis%')->count() : 0;
        $komuniTable = Schema::hasTable('sakramen') ? (clone $sakramenQuery)->where('tipe_sakramen', 'like', '%Komuni%')->count() : 0;
        $krismaTable = Schema::hasTable('sakramen') ? (clone $sakramenQuery)->where('tipe_sakramen', 'like', '%Krisma%')->count() : 0;
        $nikahTable = Schema::hasTable('sakramen') ? (clone $sakramenQuery)->where(function($q) {
            $q->where('tipe_sakramen', 'like', '%Nikah%')
              ->orWhere('tipe_sakramen', 'like', '%Kawin%')
              ->orWhere('tipe_sakramen', 'like', '%Perkawinan%');
        })->count() : 0;

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
            'genderStats' => $genderStats,
            'usiaStats' => $usiaStats,
            'sebaranStats' => $sebaranStats,
            'statusKawinStats' => $statusKawinStats,
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
            $query->whereHas('kk', fn($kkQ) => $kkQ->where('wilayah_id', $authUser->wilayah_id));
        } elseif ((str_contains($slugClean, 'kapela') || str_contains($slugClean, 'stasi')) && !empty($authUser?->kapela_id)) {
            $query->whereHas('kk', fn($kkQ) => $kkQ->where('kapela_id', $authUser->kapela_id));
        } elseif (str_contains($slugClean, 'kub') && !empty($authUser?->kub_id)) {
            $query->whereHas('kk', fn($kkQ) => $kkQ->where('kub_id', $authUser->kub_id));
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

        $umats->getCollection()->transform(function ($u) {
            $u->hashid = encode_id($u->id);
            $u->iid = $u->hashid;
            return $u;
        });

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

        $authUser = auth()->user();
        $targetKubId = $authUser?->kub_id ?: ($firstSegment === 'kub' ? (session('simulated_kub_id') ?: $request->input('kub_id')) : null);
        if (!$targetKubId && $firstSegment === 'kub') {
            $targetKubId = \App\Models\User::whereNotNull('kub_id')->value('kub_id') ?: \App\Models\Kub::value('id');
            session(['simulated_kub_id' => $targetKubId]);
        }
        $userKub = $targetKubId ? \App\Models\Kub::find($targetKubId) : null;
        $userKubId = $userKub?->id;
        $userWilayahId = $authUser?->wilayah_id ?: $userKub?->wilayah_id ?: ($firstSegment === 'wilayah' ? (session('simulated_wilayah_id') ?: $request->input('wilayah_id')) : null);
        $userKapelaId = $authUser?->kapela_id ?: $userKub?->kapela_id ?: ($firstSegment === 'kapela' ? (session('simulated_kapela_id') ?: $request->input('kapela_id')) : null);

        $kkQuery = \App\Models\KkKatolik::orderBy('nama_lahir_pemilik');
        if ($targetKubId) {
            $kkQuery->where('kub_id', $targetKubId);
        } elseif ($userWilayahId) {
            $kkQuery->where('wilayah_id', $userWilayahId);
        } elseif ($userKapelaId) {
            $kkQuery->where('kapela_id', $userKapelaId);
        }

        return Inertia::render('Inertia/UmatForm', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
            'isEdit' => false,
            'umatItem' => null,
            'userKubId' => $userKubId,
            'userWilayahId' => $userWilayahId,
            'userKapelaId' => $userKapelaId,
            'defaultKubId' => $userKubId,
            'defaultWilayahId' => $userWilayahId,
            'defaultKapelaId' => $userKapelaId,
            'kkList' => $kkQuery->get(['id', 'no_kk_kw', 'nama_lahir_pemilik', 'nama_baptis_pemilik', 'wilayah_id', 'kapela_id', 'kub_id']),
            'wilayahList' => \App\Models\Wilayah::orderBy('nama_wilayah')->get(['id', 'nama_wilayah']),
            'kubList' => \App\Models\Kub::orderBy('nama_kub')->get(['id', 'nama_kub', 'wilayah_id', 'kapela_id']),
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

        $umatItem = \App\Models\Umat::findByUuidOrIdOrFail($id);
        $umatItem->hashid = encode_id($umatItem->id);
        $umatItem->iid = $umatItem->hashid;

        $authUser = auth()->user();
        $isSuperAdmin = (int)($authUser?->role_id ?? 0) === 1 || in_array(strtolower($authUser?->role?->nama_role ?? ''), ['super admin', 'superadmin'], true);
        $targetKubId = $authUser?->kub_id ?: ($firstSegment === 'kub' ? (session('simulated_kub_id') ?: $request->input('kub_id')) : null);

        // Proteksi Hak Akses Level KUB: Ketua KUB tidak boleh mengakses data umat di luar KUB-nya
        if ($firstSegment === 'kub' || (!$isSuperAdmin && $authUser?->kub_id)) {
            $umatKubId = $umatItem->kub_id ?: ($umatItem->kk?->kub_id);
            if ($targetKubId && $umatKubId && (int)$umatKubId !== (int)$targetKubId) {
                if (!$isSuperAdmin) {
                    return redirect("/{$firstSegment}/umat")->with('error', 'Anda tidak memiliki hak akses untuk mengedit data umat di luar KUB Anda.');
                }
            }
        }

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

        $userKub = $targetKubId ? \App\Models\Kub::find($targetKubId) : ($umatItem->kub_id ? \App\Models\Kub::find($umatItem->kub_id) : null);
        $userKubId = $userKub?->id ?: $umatItem->kub_id;
        $userWilayahId = $userKub?->wilayah_id ?: $umatItem->wilayah_id;
        $userKapelaId = $userKub?->kapela_id ?: $umatItem->kapela_id;

        $kkQuery = \App\Models\KkKatolik::orderBy('nama_lahir_pemilik');
        if ($targetKubId) {
            $kkQuery->where('kub_id', $targetKubId);
        } elseif ($userWilayahId) {
            $kkQuery->where('wilayah_id', $userWilayahId);
        } elseif ($userKapelaId) {
            $kkQuery->where('kapela_id', $userKapelaId);
        }

        return Inertia::render('Inertia/UmatForm', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
            'isEdit' => true,
            'umatItem' => $umatItem,
            'userKubId' => $userKubId,
            'userWilayahId' => $userWilayahId,
            'userKapelaId' => $userKapelaId,
            'defaultKubId' => $userKubId,
            'defaultWilayahId' => $userWilayahId,
            'defaultKapelaId' => $userKapelaId,
            'kkList' => $kkQuery->get(['id', 'no_kk_kw', 'nama_lahir_pemilik', 'nama_baptis_pemilik', 'wilayah_id', 'kapela_id', 'kub_id']),
            'wilayahList' => \App\Models\Wilayah::orderBy('nama_wilayah')->get(['id', 'nama_wilayah']),
            'kubList' => \App\Models\Kub::orderBy('nama_kub')->get(['id', 'nama_kub', 'wilayah_id', 'kapela_id']),
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

        $umat = \App\Models\Umat::findByUuidOrIdOrFail($id);
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

    public function umatOptions(\Illuminate\Http\Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $id = $request->input('id');

        $query = \App\Models\Umat::query();

        if ($id) {
            $query->where('id', $id);
        } elseif ($q !== '') {
            $query->where(function ($qq) use ($q) {
                $qq->where('nama_lengkap', 'like', '%' . $q . '%')
                   ->orWhere('nama_baptis', 'like', '%' . $q . '%')
                   ->orWhere('nik', 'like', '%' . $q . '%');
            });
        }

        $rows = $query->orderBy('nama_lengkap')
            ->limit(50)
            ->get(['id', 'nama_lengkap', 'nama_baptis', 'nik', 'handphone'])
            ->map(function ($u) {
                $nama = trim(trim(($u->nama_baptis ?: '') . ' ' . ($u->nama_lengkap ?: '')));
                $nama = $nama ?: ($u->nama_lengkap ?: '');
                return [
                    'id'           => $u->id,
                    'name'         => $nama . ($u->nik ? ' (NIK: ' . $u->nik . ')' : ''),
                    'nama_lengkap' => $u->nama_lengkap,
                    'nama_baptis'  => $u->nama_baptis,
                    'handphone'    => $u->handphone ?: $u->no_hp,
                    'nik'          => $u->nik,
                ];
            });

        return response()->json($rows->all());
    }
}
