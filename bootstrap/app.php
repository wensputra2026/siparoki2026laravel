<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
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
            $statusCode = method_exists($response, 'getStatusCode') ? $response->getStatusCode() : 500;

            if (in_array($statusCode, [500, 503, 404, 403, 419, 429], true)) {
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
