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

        if ($this->isElevated($slug)) {
            return $next($request);
        }

        $segment = explode('/', trim($request->path(), '/'))[0] ?? '';

        // Generic dashboards are available to any authenticated user.
        if (in_array($segment, ['v2', 'dashboard'], true)) {
            return $next($request);
        }

        // The /admin/* area is elevated (master data + management).
        if ($segment === 'admin') {
            abort(403, 'Anda tidak memiliki akses ke area ini.');
        }

        $prefix = $this->rolePrefix($slug);

        if ($prefix && $segment === $prefix) {
            return $next($request);
        }

        abort(403, 'Akses ditolak untuk peran Anda.');
    }
}
