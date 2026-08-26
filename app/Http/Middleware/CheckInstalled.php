<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class CheckInstalled
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $isInstalled = File::exists(storage_path('installed')) 
            || File::exists(storage_path('installed.lock'))
            || File::exists(storage_path('framework/installed'))
            || File::exists(public_path('installed.lock'));
        $isInstallerRoute = $request->is('installer') || $request->is('installer/*');

        // If already installed and attempting to access /installer, gracefully redirect to home
        if ($isInstalled && $isInstallerRoute && !$request->has('force')) {
            return redirect('/')->with('info', 'Aplikasi SIPAROKI sudah aktif.');
        }

        return $next($request);
    }
}
