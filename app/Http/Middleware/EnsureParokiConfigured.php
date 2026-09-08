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
     * Dinonaktifkan: Menjalankan aplikasi langsung dari instalasi GitHub tanpa memaksa setup wizard.
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
