<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DesaKelurahan;
use App\Models\Dekenat;
use App\Models\Kabupaten;
use App\Models\Kapela;
use App\Models\Kecamatan;
use App\Models\Keuskupan;
use App\Models\Kevikepan;
use App\Models\Kub;
use App\Models\Lingkungan;
use App\Models\Paroki;
use App\Models\Provinsi;
use App\Models\Role;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inertia\Inertia;
use Inertia\Response;

abstract class BaseAdminController extends Controller
{
    /**
     * Resolve the current authenticated user's role slug and title.
     */
    protected function resolveRoleContext(Request $request, ?string $role = null): array
    {
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? '';
        $roleMap = [
            'superadmin' => 'Super Admin',
            'v2' => 'Super Admin',
            'paroki' => 'Admin Paroki',
            'pastor' => 'Pastor',
            'wilayah' => 'Admin Wilayah',
            'kapela' => 'Admin Kapela / Stasi',
            'kub' => 'Admin KUB',
            'bendahara' => 'Bendahara',
            'penulis' => 'Penulis',
            'umat' => 'Umat',
        ];

        $title = $roleMap[$role] ?? $roleMap[$firstSegment] ?? auth()->user()?->role?->nama_role ?? 'Super Admin';
        $prefix = array_search($title, $roleMap, true) ?: ($firstSegment ?: 'superadmin');

        return [
            'title' => $title,
            'prefix' => $prefix,
            'user' => auth()->user(),
        ];
    }

    /**
     * Apply multi-level tenant isolation scoping on a query.
     */
    protected function applyTenantScope($query, Request $request, ?string $table = null)
    {
        $user = auth()->user();
        if (!$user) {
            return $query;
        }

        $tableName = $table ?: $query->getModel()->getTable();
        $cols = Schema::hasTable($tableName) ? Schema::getColumnListing($tableName) : [];

        $roleTitle = $user->role?->nama_role ?? '';
        $slug = strtolower(preg_replace('/[^a-z0-9]/', '', $roleTitle));

        // 1. Super Admin: full access or explicit paroki filter
        if ($slug === 'superadmin') {
            if ($request->filled('paroki_id') && in_array('paroki_id', $cols, true)) {
                $query->where("{$tableName}.paroki_id", $request->input('paroki_id'));
            }
            return $query;
        }

        // 2. Paroki / Pastor / Bendahara / Penulis: Scoped to their registered paroki_id
        if (in_array('paroki_id', $cols, true) && !empty($user->paroki_id)) {
            $query->where("{$tableName}.paroki_id", $user->paroki_id);
        }

        // 3. Admin Wilayah: Scoped to their assigned wilayah_id
        if (str_contains($slug, 'wilayah') && !empty($user->wilayah_id)) {
            if (in_array('wilayah_id', $cols, true)) {
                $query->where("{$tableName}.wilayah_id", $user->wilayah_id);
            } elseif (in_array('id_wilayah', $cols, true)) {
                $query->where("{$tableName}.id_wilayah", $user->wilayah_id);
            }
        }

        // 4. Admin Kapela / Stasi: Scoped to kapela_id
        if ((str_contains($slug, 'kapela') || str_contains($slug, 'stasi')) && !empty($user->kapela_id)) {
            if (in_array('kapela_id', $cols, true)) {
                $query->where("{$tableName}.kapela_id", $user->kapela_id);
            } elseif (in_array('id_kapela', $cols, true)) {
                $query->where("{$tableName}.id_kapela", $user->kapela_id);
            }
        }

        // 5. Ketua KUB: Scoped to kub_id
        if (str_contains($slug, 'kub') && !empty($user->kub_id)) {
            if (in_array('kub_id', $cols, true)) {
                $query->where("{$tableName}.kub_id", $user->kub_id);
            } elseif (in_array('id_kub', $cols, true)) {
                $query->where("{$tableName}.id_kub", $user->kub_id);
            }
        }

        // 6. Umat: Scoped to own record
        if ($slug === 'umat' && !empty($user->umat_id)) {
            if (in_array('umat_id', $cols, true)) {
                $query->where("{$tableName}.umat_id", $user->umat_id);
            } elseif (in_array('id_umat', $cols, true)) {
                $query->where("{$tableName}.id_umat", $user->umat_id);
            }
        }

        return $query;
    }

    /**
     * Get cached common lookup lists for selectors and filters.
     */
    protected function getCommonLookups(Request $request): array
    {
        return Cache::remember('admin_common_lookups_' . (auth()->user()?->paroki_id ?? 'all'), 300, function () {
            return [
                'keuskupanList' => Keuskupan::select('id_keuskupan as id', 'nama_keuskupan as name')->get(),
                'dekenatList' => Kevikepan::select('id', 'nama_kevikepan as name', 'keuskupan_id')->get(),
                'parokiList' => Paroki::select('id_paroki as id', 'nama_paroki as name', 'keuskupan_id', 'dekenat_id')->get(),
                'provinsiList' => Provinsi::select('id_provinsi as id', 'nama_provinsi as name')->get(),
                'kabupatenList' => Kabupaten::select('id_kabupaten as id', 'nama_kabupaten as name', 'provinsi_id')->get(),
                'kecamatanList' => Kecamatan::select('id_kecamatan as id', 'nama_kecamatan as name', 'kabupaten_id')->get(),
                'desaList' => DesaKelurahan::select('id_desa as id', 'nama_desa as name', 'kecamatan_id')->get(),
                'wilayahList' => Wilayah::select('id_wilayah as id', 'nama_wilayah as name', 'paroki_id')->get(),
                'kapelaList' => Kapela::select('id_kapela as id', 'nama_kapela as name', 'paroki_id')->get(),
                'kubList' => Kub::select('id_kub as id', 'nama_kub as name', 'wilayah_id', 'kapela_id', 'paroki_id')->get(),
                'roleList' => Role::whereNotIn('slug', ['umat'])
                    ->where('nama_role', 'not like', '%umat%')
                    ->select('id', 'nama_role as name', 'nama_role', 'slug')
                    ->get(),
            ];
        });
    }

    /**
     * Render an Inertia SPA page with standardized props and notifications.
     */
    protected function renderInertia(string $component, array $props = [], Request $request = null): Response
    {
        $roleContext = $this->resolveRoleContext($request ?? request());
        
        $baseProps = array_merge([
            'role' => $roleContext['title'],
            'rolePrefix' => $roleContext['prefix'],
            'authUser' => $roleContext['user'],
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
                'warning' => session('warning'),
                'info' => session('info'),
            ],
        ], $props);

        return Inertia::render($component, $baseProps);
    }
}
