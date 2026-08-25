<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protects the Inertia panel routes.
 *
 * - Requires an authenticated user (guests are redirected to /login).
 * - Enforces role-based access: super admin / pastor / paroki roles may access
 *   every panel prefix; other roles may only access their own role prefix
 *   (e.g. a "wilayah" user may only open /wilayah/*).
 */
class PanelAccess
{
    /**
     * Normalize a role slug to a lowercase alphanumeric token so e.g.
     * "admin_wilayah" becomes "adminwilayah" (consistent with Gate::before).
     */
    private function roleSlug($user): string
    {
        return strtolower(preg_replace('/[^a-z0-9]/', '', $user->role?->slug ?? $user->role?->nama_role ?? ''));
    }

    /**
     * Elevated roles may access every panel prefix (super admin area).
     */
    private function isElevated(string $slug): bool
    {
        if (in_array($slug, [
            'superadmin',
            'superadministrator',
            'admin',
            'administrator',
            'pastor',
            'pastorparoki',
            'paroki',
            'parokipastoral',
        ], true)) {
            return true;
        }

        // Paroki / pastor roles are also elevated regardless of prefix.
        return str_contains($slug, 'paroki') || str_contains($slug, 'pastor');
    }

    /**
     * Map a (non-elevated) role slug to its panel URL prefix.
     */
    private function rolePrefix(string $slug): ?string
    {
        $checks = [
            'wilayah' => 'wilayah',
            'kapela' => 'kapela',
            'stasi' => 'kapela',
            'kub' => 'kub',
            'bendahara' => 'bendahara',
            'penulis' => 'penulis',
            'komsos' => 'penulis',
            'umat' => 'umat',
        ];

        foreach ($checks as $needle => $prefix) {
            if (str_contains($slug, $needle)) {
                return $prefix;
            }
        }

        return null;
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $slug = $this->roleSlug($user);

        $isSuper = in_array($slug, ['superadmin', 'superadministrator'], true);
        $superOnlyModules = [
            'keuskupan', 'dekenat', 'kevikepan', 'kuasi-paroki',
            'provinsi', 'kabupaten', 'kecamatan', 'desa-kelurahan',
            'role', 'roles', 'backup-database'
        ];

        $segments = explode('/', trim($request->path(), '/'));
        $segment = $segments[0] ?? '';
        $module = $segments[1] ?? null;

        if (!$isSuper && $module !== null && in_array($module, $superOnlyModules, true)) {
            abort(403, 'Modul ' . $module . ' hanya dapat diakses dan dikelola oleh Super Administrator.');
        }

        if ($this->isElevated($slug)) {
            return $next($request);
        }

        $segments = explode('/', trim($request->path(), '/'));
        $segment = $segments[0] ?? '';
        $module = $segments[1] ?? null;

        // Generic dashboards / profile are available to any authenticated user.
        if (in_array($segment, ['dashboard'], true)) {
            return $next($request);
        }

        // The /admin/* area is elevated (master data + management).
        if ($segment === 'admin') {
            abort(403, 'Anda tidak memiliki akses ke area ini.');
        }

        // Modules that must NEVER be reachable by non-elevated roles,
        // regardless of which panel prefix they use (privilege escalation /
        // account takeover / database backup abuse protection).
        $sensitiveModules = [
            'user', 'role', 'roles',
            'backup-database', 'security-settings', 'security-center',
            'pengaturan-aplikasi', 'pengaturan', 'settings',
        ];

        // Pages that any authenticated user may open inside their own panel.
        $safeModules = [
            'profil-saya', 'profil-paroki', 'panduan-hak-akses',
            'statistik', 'demografi', 'dashboard',
        ];

        // Least-privilege module allow-list per (non-elevated) role prefix.
        $moduleAccess = [
            'bendahara' => ['keuangan', 'aset', 'kategori-keuangan', 'lapak-produk', 'jenis-iuran', 'iuran', 'kolekte', 'intensi-misa', 'intensi', 'umat', 'data-umat', 'kk-katolik', 'kk', 'keluarga'],
            'penulis' => ['konten', 'kategori-konten', 'pengumuman', 'galeri', 'renungan', 'kegiatan', 'artikel', 'berita', 'komentar-artikel', 'download'],
            'wilayah' => ['umat', 'data-umat', 'kk-katolik', 'kk', 'keluarga', 'sakramen', 'pengajuan-sakramen', 'wilayah', 'lingkungan', 'kub', 'kegiatan', 'iuran', 'keuangan', 'aset'],
            'kapela' => ['umat', 'data-umat', 'kk-katolik', 'kk', 'keluarga', 'sakramen', 'pengajuan-sakramen', 'wilayah', 'lingkungan', 'kub', 'kapela', 'stasi', 'kegiatan', 'iuran', 'keuangan', 'aset'],
            'kub' => ['umat', 'data-umat', 'kk-katolik', 'kk', 'keluarga', 'sakramen', 'pengajuan-sakramen', 'lingkungan', 'kub', 'iuran', 'keuangan', 'aset', 'lapak-produk'],
            'umat' => ['kk-katolik', 'kk', 'keluarga', 'pengajuan-sakramen', 'lapak-produk', 'umat'],
        ];

        // The legacy /v2/* prefix smoothly redirects to the user's role panel.
        if ($segment === 'v2') {
            $prefix = $this->rolePrefix($slug) ?? 'superadmin';
            if ($module === null || in_array($module, ['dashboard', ''], true)) {
                return redirect("/{$prefix}");
            }
            return redirect("/{$prefix}/{$module}");
        }

        $prefix = $this->rolePrefix($slug);

        if (!$prefix || $segment !== $prefix) {
            abort(403, 'Akses ditolak untuk peran Anda.');
        }

        if ($module !== null) {
            if (in_array($module, $sensitiveModules, true)) {
                abort(403, 'Modul ini hanya dapat diakses oleh Administrator.');
            }
            if (in_array($module, $safeModules, true)) {
                return $next($request);
            }
            $allowed = $moduleAccess[$prefix] ?? [];
            if (!in_array($module, $allowed, true)) {
                abort(403, 'Anda tidak memiliki wewenang untuk modul ini.');
            }
        }

        return $next($request);
    }
}
