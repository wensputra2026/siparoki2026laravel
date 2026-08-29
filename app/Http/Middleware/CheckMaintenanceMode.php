<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Never block admin panels, auth routes, and backend APIs
        $path = trim($request->path(), '/');
        $exemptPrefixes = [
            'superadmin',
            'pastor',
            'paroki',
            'bendahara',
            'wilayah',
            'kapela',
            'kub',
            'penulis',
            'admin',
            'login',
            'logout',
            'register',
            'forgot-password',
            'reset-password',
            'api',
            'midtrans',
            'up',
            'sanctum',
            '_debugbar',
        ];

        foreach ($exemptPrefixes as $prefix) {
            if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                return $next($request);
            }
        }

        // 2. Check if maintenance mode is enabled in database
        try {
            if (!Schema::hasTable('pengaturan_aplikasi')) {
                return $next($request);
            }

            $pengaturan = DB::table('pengaturan_aplikasi')->first();
            if (!$pengaturan || ($pengaturan->maintenance_mode ?? '0') !== '1') {
                return $next($request);
            }

            $secretKey = $pengaturan->maintenance_bypass_key ?: 'siparoki2026';

            // Handle exit bypass
            if ($request->query('exit_bypass') === '1') {
                $request->session()->forget('maintenance_bypassed');
            }

            // Handle secret key bypass via URL
            if ($request->query('bypass') === $secretKey) {
                $request->session()->put('maintenance_bypassed', true);
                return $next($request);
            }

            // Check active session bypass
            if ($request->session()->get('maintenance_bypassed') === true) {
                return $next($request);
            }

            // Check if user is Super Admin who explicitly wants to view live site
            $user = Auth::user();
            $isSuperAdmin = $user && ((int) ($user->role_id ?? 0) === 1 || in_array(strtolower($user->role?->nama_role ?? ($user->role?->name ?? '')), ['super admin', 'superadmin'], true));

            if ($isSuperAdmin && $request->query('admin_preview') === '1') {
                $request->session()->put('maintenance_bypassed', true);
                return $next($request);
            }

            // Prepare maintenance page metadata
            $profil = Schema::hasTable('profil_paroki') ? DB::table('profil_paroki')->first() : null;
            $activeParoki = Schema::hasTable('paroki') ? DB::table('paroki')->first() : null;
            $namaParoki = $activeParoki?->nama_paroki ?? $profil?->nama_paroki ?? $pengaturan?->nama_paroki ?? 'Paroki St. Vinsensius a Paulo Benlutu';
            $logo = $activeParoki?->logo ?? $profil?->logo ?? null;

            return response()->view('errors.maintenance', [
                'globalNamaParoki'   => $namaParoki,
                'globalFavicon'      => $logo ? asset($logo) : asset('favicon.ico'),
                'maintenanceTitle'   => $pengaturan->maintenance_title ?? 'Website Sedang Dalam Pemeliharaan / Perawatan',
                'maintenanceMessage' => $pengaturan->maintenance_message ?? 'Mohon maaf atas ketidaknyamanannya. Website Paroki kami sedang melakukan pembaruan berkala. Silakan kembali dalam beberapa saat.',
                'maintenanceUntil'   => $pengaturan->maintenance_until ?? '',
                'maintenanceContact' => $pengaturan->maintenance_contact ?? '',
                'isSuperAdmin'       => $isSuperAdmin,
            ], 503);

        } catch (\Throwable $e) {
            return $next($request);
        }
    }
}
