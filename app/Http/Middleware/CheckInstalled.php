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
        $isInstalled = File::exists(storage_path('installed')) || File::exists(storage_path('framework/installed'));
        $isInstallerRoute = $request->is('installer') || $request->is('installer/*');

        // Allow static assets
        if ($request->is('build/*') || $request->is('assets/*') || $request->is('fonts/*') || $request->is('images/*') || $request->is('favicon.ico') || $request->is('env.js') || $request->is('css/*') || $request->is('js/*')) {
            return $next($request);
        }

        // If not installed and not on installer route, redirect to /installer
        if (!$isInstalled && !$isInstallerRoute) {
            return redirect()->route('installer.index');
        }

        // If already installed and attempting to access /installer, redirect to home
        if ($isInstalled && $isInstallerRoute) {
            return redirect('/')->with('info', 'Aplikasi SIPAROKI sudah terpasang. Installer telah dikunci.');
        }

        return $next($request);
    }
}
