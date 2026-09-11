<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class GitHubDeployController extends Controller
{
    private const REPO = 'wensputra2026/siparoki2026laravel';
    private const BRANCH = 'main';
    private const DEFAULT_SECRET = 'SiparokiDeploy2026!';

    /**
     * Handle incoming GitHub webhook or manual sync request.
     */
    public function handle(Request $request)
    {
        $secret = env('DEPLOY_SECRET_TOKEN', self::DEFAULT_SECRET);
        $token = $request->query('token') ?: $request->header('X-Deploy-Token');
        $githubSig = $request->header('X-Hub-Signature-256');

        $authorized = false;
        if ($token && hash_equals($secret, (string)$token)) {
            $authorized = true;
        } elseif ($githubSig) {
            $payload = $request->getContent();
            $computedSig = 'sha256=' . hash_hmac('sha256', $payload, $secret);
            if (hash_equals($computedSig, $githubSig)) {
                $authorized = true;
            }
        }

        if (!$authorized) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized: Invalid token or signature'
            ], 403);
        }

        // Check if event is a ping
        $event = $request->header('X-GitHub-Event');
        if ($event === 'ping') {
            return response()->json([
                'status' => 'success',
                'message' => 'Pong! Webhook connection verified successfully.'
            ]);
        }

        $baseDir = base_path();
        $payloadData = $request->json()->all();
        $filesToSync = [];
        $hasMigrations = false;

        // Extract files from commits payload
        if (!empty($payloadData['commits']) && is_array($payloadData['commits'])) {
            foreach ($payloadData['commits'] as $commit) {
                foreach (['added', 'modified'] as $action) {
                    if (!empty($commit[$action]) && is_array($commit[$action])) {
                        foreach ($commit[$action] as $file) {
                            $filesToSync[] = $file;
                        }
                    }
                }
            }
        } elseif (!empty($payloadData['head_commit'])) {
            foreach (['added', 'modified'] as $action) {
                if (!empty($payloadData['head_commit'][$action])) {
                    foreach ($payloadData['head_commit'][$action] as $file) {
                        $filesToSync[] = $file;
                    }
                }
            }
        }

        $filesToSync = array_values(array_unique($filesToSync));

        // If no files extracted (e.g. manual GET trigger), fetch changed files from GitHub API
        if (empty($filesToSync)) {
            $apiFiles = $this->fetchLatestCommitFiles();
            if (!empty($apiFiles)) {
                $filesToSync = $apiFiles;
            }
        }

        // Try standard git pull first if git environment exists
        $gitPulled = false;
        if (is_dir($baseDir . '/.git') && function_exists('shell_exec')) {
            $gitOut = @shell_exec("cd {$baseDir} && git pull origin " . self::BRANCH . " 2>&1");
            if ($gitOut && !str_contains($gitOut, 'fatal:') && !str_contains($gitOut, 'error:')) {
                $gitPulled = true;
            }
        }

        $syncedFiles = [];
        $failedFiles = [];

        // If git pull wasn't possible or failed (e.g. on shared cPanel without .git), download files directly
        if (!$gitPulled && !empty($filesToSync)) {
            $rawBase = "https://raw.githubusercontent.com/" . self::REPO . "/" . self::BRANCH . "/";

            foreach ($filesToSync as $file) {
                // Security check: do not overwrite critical environment files
                if ($this->isProtectedFile($file)) {
                    continue;
                }

                $url = $rawBase . $file . '?v=' . time();
                $content = $this->downloadFile($url);

                if ($content !== false) {
                    $targetPath = $baseDir . '/' . str_replace('\\', '/', $file);
                    $targetDir = dirname($targetPath);
                    if (!is_dir($targetDir)) {
                        @mkdir($targetDir, 0755, true);
                    }

                    $written = @file_put_contents($targetPath, $content);
                    if ($written !== false) {
                        $syncedFiles[] = $file;

                        // Also duplicate to public root if public/ file
                        if (str_starts_with($file, 'public/')) {
                            $pubTarget = public_path(substr($file, 7));
                            $pubDir = dirname($pubTarget);
                            if (!is_dir($pubDir)) {
                                @mkdir($pubDir, 0755, true);
                            }
                            @file_put_contents($pubTarget, $content);
                        }

                        if (str_contains($file, 'database/migrations/')) {
                            $hasMigrations = true;
                        }
                    } else {
                        $failedFiles[] = $file . ' (Write failed)';
                    }
                } else {
                    $failedFiles[] = $file . ' (Download failed)';
                }
            }
        }

        // Run migrations if new migration detected or forced
        $migrateOutput = '';
        if ($hasMigrations || $request->has('migrate')) {
            try {
                Artisan::call('migrate', ['--force' => true]);
                $migrateOutput = trim(Artisan::output());
            } catch (\Throwable $e) {
                $migrateOutput = 'Migration error: ' . $e->getMessage();
            }
        }

        // Clear all Laravel caches
        try {
            Artisan::call('optimize:clear');
            $cacheOutput = trim(Artisan::output());
        } catch (\Throwable $e) {
            $cacheOutput = 'Cache clear error: ' . $e->getMessage();
        }

        $result = [
            'status' => 'success',
            'git_pulled' => $gitPulled,
            'synced_count' => count($syncedFiles),
            'synced_files' => $syncedFiles,
            'failed_files' => $failedFiles,
            'migrations' => $migrateOutput ?: 'No pending migrations',
            'cache' => $cacheOutput,
            'timestamp' => now()->toIso8601String()
        ];

        Log::info('GitHub Deploy Webhook processed', $result);

        return response()->json($result);
    }

    private function isProtectedFile(string $file): bool
    {
        $file = strtolower(trim($file));
        if ($file === '.env' || str_starts_with($file, '.env.') || $file === '.cpanel.yml') {
            return true;
        }
        if (str_starts_with($file, 'storage/logs/') || str_starts_with($file, 'storage/app/public/')) {
            return true;
        }
        if (str_starts_with($file, '.git/')) {
            return true;
        }
        return false;
    }

    private function downloadFile(string $url)
    {
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'SiparokiWebhook/1.0');
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $content = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($code === 200 && $content !== false) {
                return $content;
            }
        }

        $ctx = stream_context_create([
            'http' => [
                'timeout' => 30,
                'header' => "User-Agent: SiparokiWebhook/1.0\r\n"
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);
        return @file_get_contents($url, false, $ctx);
    }

    private function fetchLatestCommitFiles(): array
    {
        $url = "https://api.github.com/repos/" . self::REPO . "/commits/" . self::BRANCH;
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'SiparokiWebhook/1.0');
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        $res = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code === 200 && $res) {
            $data = json_decode($res, true);
            if (!empty($data['files']) && is_array($data['files'])) {
                return array_column($data['files'], 'filename');
            }
        }
        return [];
    }
}
