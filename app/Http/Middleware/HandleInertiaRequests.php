<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        // Cache active paroki for 10 minutes
        $activeParoki = Cache::remember('active_paroki_middleware_v3', 600, function () {
            try {
                $profil = Schema::hasTable('profil_paroki')
                    ? DB::table('profil_paroki')->first()
                    : null;

                if ($profil && !empty($profil->paroki_id)) {
                    $paroki = \App\Models\Paroki::where('id_paroki', $profil->paroki_id)->first();
                    if ($paroki) return $paroki;
                }

                if (Schema::hasTable('pengaturan_aplikasi')) {
                    $pengaturan = DB::table('pengaturan_aplikasi')->first();
                    if ($pengaturan && !empty($pengaturan->nama_paroki)) {
                        $paroki = \App\Models\Paroki::where('nama_paroki', $pengaturan->nama_paroki)
                            ->orWhere('nama_paroki', 'like', '%' . $pengaturan->nama_paroki . '%')
                            ->first();
                        if ($paroki) return $paroki;
                    }
                }

                if ($profil && !empty($profil->nama_paroki)) {
                    $paroki = \App\Models\Paroki::where('nama_paroki', $profil->nama_paroki)
                        ->orWhere('nama_paroki', 'like', '%' . $profil->nama_paroki . '%')
                        ->first();
                    if ($paroki) return $paroki;
                }

                return \App\Models\Paroki::first();
            } catch (\Throwable $e) {
                return null;
            }
        });

        // Session-specific paroki override
        try {
            $sessionParokiId = $request->session()->get('default_paroki_id');
            if ($sessionParokiId) {
                $sessionParoki = \App\Models\Paroki::find($sessionParokiId);
                if ($sessionParoki) {
                    $activeParoki = $sessionParoki;
                }
            }
        } catch (\Throwable $e) {}

        $profilParoki = null;
        $pengaturanAplikasi = null;

        try {
            $profilParoki = Schema::hasTable('profil_paroki')
                ? DB::table('profil_paroki')->first()
                : null;
            $pengaturanAplikasi = Schema::hasTable('pengaturan_aplikasi')
                ? DB::table('pengaturan_aplikasi')->first()
                : null;
        } catch (\Throwable $e) {}

        $namaParoki = $activeParoki?->nama_paroki
            ?? $profilParoki?->nama_paroki
            ?? $pengaturanAplikasi?->nama_paroki
            ?? 'Paroki St. Vincentius a Paulo Benlutu';
        $logoParoki = $activeParoki?->logo
            ?: ($profilParoki?->logo ?? null);

        // Cache scopeOptions for 10 minutes — these rarely change
        $currentRole = $request->user()?->role?->nama_role ?? $request->user()?->role?->slug ?? '';
        $roleKey = str_replace(['_', '-', ' '], '', strtolower($currentRole));
        $isSuperUser = (int) ($request->user()?->role_id ?? 0) === 1 || in_array($roleKey, ['superadmin', 'superadministrator', 'admin', 'administrator', 'pastor', 'pastorparoki'], true);
        $needsPastorScope = $isSuperUser || str_contains($roleKey, 'pastor');
        $needsWilayahScope = $isSuperUser || str_contains($roleKey, 'wilayah');
        $needsKapelaScope = $isSuperUser || str_contains($roleKey, 'kapela') || str_contains($roleKey, 'stasi');
        $needsKubScope = $isSuperUser || str_contains($roleKey, 'kub');

        $scopeOptions = Cache::remember("scope_options_middleware_v5.{$roleKey}", 600, function () use ($needsPastorScope, $needsWilayahScope, $needsKapelaScope, $needsKubScope) {
            $result = ['pastors' => [], 'wilayah' => [], 'kapela' => [], 'kub' => []];
            try {
                // Pastors
                if ($needsPastorScope && Schema::hasTable('master_pastor') && \App\Models\MasterPastor::count() > 0) {
                    $pastors = \App\Models\MasterPastor::select('id', 'nama_pastor', 'gelar_depan', 'ordo', 'jenis_imam', 'jabatan')
                        ->orderBy('nama_pastor')->get();
                    $result['pastors'] = $pastors->map(fn ($p) => [
                        'id'          => $p->id,
                        'nama_pastor' => \App\Models\MasterPastor::formatNama($p),
                        'jabatan'     => $p->jabatan ?? 'Pastor',
                    ])->values()->all();
                } elseif ($needsPastorScope && Schema::hasTable('riwayat_pastor_paroki') && \App\Models\RiwayatPastorParoki::count() > 0) {
                    $pastors = \App\Models\RiwayatPastorParoki::orderBy('urutan')->get();
                    $result['pastors'] = $pastors->map(fn ($p) => [
                        'id'          => $p->id_riwayat_pastor ?? $p->id ?? 1,
                        'nama_pastor' => \App\Models\MasterPastor::formatNama($p),
                        'jabatan'     => $p->jabatan ?? 'Pastor Paroki',
                    ])->values()->all();
                }

                if ($needsPastorScope && empty($result['pastors'])) {
                    $result['pastors'] = [
                        ['id' => 1, 'nama_pastor' => 'RD. Herman Hillers Penga', 'jabatan' => 'Pastor Paroki'],
                        ['id' => 2, 'nama_pastor' => 'RD. Krispinus Saku', 'jabatan' => 'Pastor Rekan'],
                        ['id' => 3, 'nama_pastor' => 'RP. Damasus Sumardi, CMF', 'jabatan' => 'Pastor Rekan / Vikaris'],
                    ];
                }

                // Wilayah
                if ($needsWilayahScope && Schema::hasTable('wilayah') && \App\Models\Wilayah::count() > 0) {
                    $result['wilayah'] = \App\Models\Wilayah::select('id', 'nama_wilayah', 'kode_wilayah')
                        ->orderBy('nama_wilayah')->get();
                } elseif ($needsWilayahScope) {
                    $result['wilayah'] = [
                        ['id' => 1, 'nama_wilayah' => 'Wilayah 01 - Pusat Paroki Benlutu', 'kode_wilayah' => 'WIL-01'],
                        ['id' => 2, 'nama_wilayah' => 'Wilayah 02 - St. Petrus', 'kode_wilayah' => 'WIL-02'],
                    ];
                }

                // Kapela
                if ($needsKapelaScope && Schema::hasTable('kapela') && \App\Models\Kapela::count() > 0) {
                    $result['kapela'] = \App\Models\Kapela::select('id', 'nama_kapela', 'kode_kapela')
                        ->orderBy('nama_kapela')->get();
                } elseif ($needsKapelaScope) {
                    $result['kapela'] = [
                        ['id' => 1, 'nama_kapela' => 'Stasi St. Mikael', 'kode_kapela' => 'STA-01'],
                    ];
                }

                // KUB
                if ($needsKubScope && Schema::hasTable('kub') && \App\Models\Kub::count() > 0) {
                    $result['kub'] = \App\Models\Kub::select('id', 'nama_kub', 'kode_kub', 'wilayah_id', 'kapela_id')
                        ->orderBy('nama_kub')->get();
                } elseif ($needsKubScope) {
                    $result['kub'] = [
                        ['id' => 1, 'nama_kub' => 'KUB 01 - St. Yosef', 'kode_kub' => 'KUB-01'],
                    ];
                }
            } catch (\Throwable $e) {}

            return $result;
        });

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    'id'             => $request->user()->id,
                    'name'           => $request->user()->nama_lengkap ?? $request->user()->name,
                    'email'          => $request->user()->email,
                    'foto'           => $request->user()->foto ?? null,
                    'role_id'        => (int) ($request->user()->role_id ?? 0),
                    'role'           => $request->user()->role?->nama_role ?? 'Pengguna',
                    'is_super_admin' => (int) ($request->user()->role_id ?? 0) === 1 || in_array(strtolower(preg_replace('/[^a-z]/', '', $request->user()->role?->nama_role ?? $request->user()->role?->slug ?? '')), ['superadmin', 'superadministrator'], true),
                ] : null,
            ],
            'scopeOptions' => $scopeOptions,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
                'status'  => fn () => $request->session()->get('status'),
            ],
            'app' => [
                'name'        => config('app.name', 'SIPAROKI'),
                'paroki'      => $namaParoki,
                'nama_paroki' => $namaParoki,
                'logo'        => $logoParoki,
                'favicon'     => $logoParoki,
            ],
        ];
    }
}
