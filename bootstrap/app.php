<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
// Auto-clear stale bootstrap cache files on shared hosting / FTP deploys
foreach (['routes-v7.php', 'config.php'] as $cacheFile) {
    $targetPath = __DIR__ . '/cache/' . $cacheFile;
    if (file_exists($targetPath)) {
        @unlink($targetPath);
    }
}

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\CheckMaintenanceMode::class,
            \App\Http\Middleware\EnsureParokiConfigured::class,
            \App\Http\Middleware\HandleInertiaRequests::class,
            \App\Http\Middleware\MinifyHtml::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'midtrans/*',
            'api/midtrans/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->respond(function ($response, \Throwable $exception, \Illuminate\Http\Request $request) {
            if ($exception instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException && $request->isMethod('GET')) {
                $path = trim($request->path(), '/');
                if (str_contains($path, 'update')) {
                    $editPath = preg_replace('#/update(?:/([^/]+))?$#', '/edit/$1', $path);
                    if ($editPath === $path) {
                        $editPath = preg_replace('#/([^/]+)/update$#', '/edit/$1', $path);
                    }
                    if ($editPath !== $path) {
                        return redirect('/' . trim($editPath, '/'));
                    }
                } elseif (str_contains($path, 'store')) {
                    $createPath = preg_replace('#/store$#', '/create', $path);
                    if ($createPath !== $path) {
                        return redirect('/' . trim($createPath, '/'));
                    }
                }
                return redirect()->back();
            }

            $statusCode = method_exists($response, 'getStatusCode') ? $response->getStatusCode() : 500;

            if (in_array($statusCode, [405, 500, 503, 404, 403, 419, 429], true)) {
                if ($request->header('X-Inertia')) {
                    return \Inertia\Inertia::render('Errors/Error', [
                        'status' => $statusCode,
                        'message' => (!app()->environment(['local', 'testing']) && $statusCode === 500)
                            ? 'Terjadi kendala pada server kami.'
                            : ($exception->getMessage() ?: ''),
                    ])
                    ->toResponse($request)
                    ->setStatusCode($statusCode);
                }
            }

            return $response;
        });
    })->create();
