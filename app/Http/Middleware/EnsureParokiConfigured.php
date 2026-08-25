<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class EnsureParokiConfigured
{
    /**
     * Handle an incoming request and ensure parish setup is configured.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Excluded routes from setup enforcement
        if (
            $request->is('setup-paroki*') ||
            $request->is('api/setup*') ||
            $request->is('logout') ||
            $request->is('login') ||
            $request->is('assets/*') ||
            $request->is('build/*') ||
            $request->is('fonts/*') ||
            $request->is('images/*') ||
            $request->is('vendor/*') ||
            $request->is('up')
        ) {
            return $next($request);
        }

        try {
            if (Schema::hasTable('pengaturan_aplikasi')) {
                $pengaturan = DB::table('pengaturan_aplikasi')->first();
                if ($pengaturan && isset($pengaturan->is_setup_completed) && (int) $pengaturan->is_setup_completed === 0) {
                    return redirect('/setup-paroki');
                }
            }
        } catch (\Throwable $e) {
            // Silently continue if database is currently resolving
        }

        return $next($request);
    }
}
