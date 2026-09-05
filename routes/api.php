<?php

use App\Http\Controllers\MidtransController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::post('/midtrans/webhook', [MidtransController::class, 'handleCallback'])
    ->name('midtrans.webhook');

// GitHub Auto-Deploy Webhook
Route::match(['GET', 'POST'], '/deploy-webhook', function (Request $request) {
    $expectedSecret = env('DEPLOY_SECRET_TOKEN', 'SiparokiDeploy2026!');
    $token = $request->query('token') ?: $request->header('X-Deploy-Token');

    // Support token query or GitHub X-Hub-Signature-256
    $githubSignature = $request->header('X-Hub-Signature-256');
    $isAuthorized = false;

    if ($token && hash_equals($expectedSecret, (string)$token)) {
        $isAuthorized = true;
    } elseif ($githubSignature) {
        $payload = $request->getContent();
        $computedSig = 'sha256=' . hash_hmac('sha256', $payload, $expectedSecret);
        if (hash_equals($computedSig, $githubSignature)) {
            $isAuthorized = true;
        }
    }

    if (!$isAuthorized) {
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized: Invalid token or signature'
        ], 403);
    }

    $basePath = base_path();
    $commands = [
        'git pull origin main 2>&1',
        'php artisan view:clear 2>&1',
        'php artisan optimize:clear 2>&1',
    ];

    $logResults = [];
    foreach ($commands as $cmd) {
        $res = @shell_exec("cd {$basePath} && {$cmd}");
        $logResults[$cmd] = trim((string)$res);
    }

    Log::info('GitHub Auto-Deploy Webhook executed', $logResults);

    return response()->json([
        'status' => 'success',
        'message' => 'Auto-deploy triggered successfully!',
        'timestamp' => now()->toIso8601String(),
        'results' => $logResults,
    ]);
})->name('github.deploy.webhook');
