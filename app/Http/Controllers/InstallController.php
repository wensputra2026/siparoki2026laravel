<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use PDO;

class InstallController extends Controller
{
    /**
     * Path to installed lock file.
     */
    protected function isInstalled(): bool
    {
        return File::exists(storage_path('installed')) 
            || File::exists(storage_path('installed.lock'))
            || File::exists(storage_path('framework/installed'))
            || File::exists(public_path('installed.lock'));
    }

    /**
     * Show Installer Wizard Interface.
     */
    public function index()
    {
        if ($this->isInstalled()) {
            return redirect('/')->with('info', 'Aplikasi SIPAROKI sudah terpasang. Installer telah dikunci.');
        }

        $requirements = $this->checkRequirements();
        $permissions = $this->checkPermissions();
        $allPassed = $requirements['allPassed'] && $permissions['allPassed'];

        // Load master keuskupan, dekenat & paroki
        $masterData = $this->getMasterData();

        return view('installer.index', [
            'requirements' => $requirements,
            'permissions' => $permissions,
            'allPassed' => $allPassed,
            'keuskupanList' => $masterData['keuskupan'] ?? [],
            'dekenatList' => $masterData['dekenat'] ?? [],
            'parokiList' => $masterData['paroki'] ?? [],
            'currentEnv' => [
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'database' => env('DB_DATABASE', 'siparoki_db'),
                'username' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'app_url' => url('/'),
            ]
        ]);
    }

    /**
     * Check PHP Extensions and System Requirements.
     */
    protected function checkRequirements(): array
    {
        $minPhp = '8.2.0';
        $currentPhp = PHP_VERSION;
        $phpPassed = version_compare($currentPhp, $minPhp, '>=');

        $extensions = [
            'pdo' => extension_loaded('pdo'),
            'pdo_mysql' => extension_loaded('pdo_mysql'),
            'mbstring' => extension_loaded('mbstring'),
            'openssl' => extension_loaded('openssl'),
            'tokenizer' => extension_loaded('tokenizer'),
            'xml' => extension_loaded('xml'),
            'ctype' => extension_loaded('ctype'),
            'json' => extension_loaded('json'),
            'bcmath' => extension_loaded('bcmath'),
            'fileinfo' => extension_loaded('fileinfo'),
            'curl' => extension_loaded('curl'),
            'gd' => extension_loaded('gd') || extension_loaded('imagick'),
        ];

        $allPassed = $phpPassed && !in_array(false, $extensions, true);

        return [
            'phpVersion' => $currentPhp,
            'minPhpVersion' => $minPhp,
            'phpPassed' => $phpPassed,
            'extensions' => $extensions,
            'allPassed' => $allPassed,
        ];
    }

    /**
     * Check Folder Permissions.
     */
    protected function checkPermissions(): array
    {
        $paths = [
            'storage' => storage_path(),
            'storage/framework' => storage_path('framework'),
            'storage/logs' => storage_path('logs'),
            'bootstrap/cache' => base_path('bootstrap/cache'),
            '.env' => base_path('.env'),
        ];

        $permissions = [];
        $allPassed = true;

        foreach ($paths as $name => $path) {
            $isWritable = File::exists($path) ? is_writable($path) : is_writable(dirname($path));
            $permissions[$name] = $isWritable;
            if (!$isWritable) {
                $allPassed = false;
            }
        }

        return [
            'permissions' => $permissions,
            'allPassed' => $allPassed,
        ];
    }

    /**
     * Load Master Keuskupan, Dekenat & Paroki JSON Data.
     */
    protected function getMasterData(): array
    {
        $jsonPath = public_path('installer/master_keuskupan_paroki.json');
        if (!File::exists($jsonPath)) {
            $jsonPath = database_path('data/master_keuskupan_paroki.json');
        }
        if (File::exists($jsonPath)) {
            $content = File::get($jsonPath);
            $data = json_decode($content, true);
            if (is_array($data)) {
                // Deduplicate keuskupan
                $keuskupanMap = [];
                foreach ($data['keuskupan'] ?? [] as $k) {
                    $name = trim($k['nama_keuskupan'] ?? '');
                    if ($name && !isset($keuskupanMap[$name])) {
                        $keuskupanMap[$name] = $k;
                    }
                }
                return [
                    'keuskupan' => array_values($keuskupanMap),
                    'dekenat' => $data['dekenat'] ?? [],
                    'paroki' => $data['paroki'] ?? [],
                ];
            }
        }
        return ['keuskupan' => [], 'dekenat' => [], 'paroki' => []];
    }

    /**
     * AJAX Endpoint to test Database connection.
     */
    public function testDatabase(Request $request)
    {
        $host = $request->input('host', '127.0.0.1');
        $port = $request->input('port', '3306');
        $database = $request->input('database', 'siparoki_db');
        $username = $request->input('username', 'root');
        $password = $request->input('password', '');

        try {
            // Test connection to MySQL server
            $dsnWithoutDb = "mysql:host={$host};port={$port};charset=utf8mb4";
            $pdo = new PDO($dsnWithoutDb, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);

            // Attempt to create database if not exists
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

            return response()->json([
                'success' => true,
                'message' => "Koneksi database ke '{$database}' berhasil terhubung dan siap digunakan!",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => "Koneksi database gagal: " . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * AJAX Endpoint to get Paroki & Dekenat by Keuskupan ID.
     */
    public function getParokiByKeuskupan(Request $request)
    {
        $keuskupanId = (int) $request->input('keuskupan_id', 0);
        $dekenatId = (int) $request->input('dekenat_id', 0);
        $masterData = $this->getMasterData();

        $dekenats = array_values(array_filter($masterData['dekenat'] ?? [], function ($d) use ($keuskupanId) {
            return (int) ($d['keuskupan_id'] ?? 0) === $keuskupanId;
        }));

        $parokis = array_values(array_filter($masterData['paroki'] ?? [], function ($p) use ($keuskupanId, $dekenatId) {
            $matchK = (int) ($p['keuskupan_id'] ?? 0) === $keuskupanId;
            if ($dekenatId > 0) {
                return $matchK && (int) ($p['dekenat_id'] ?? 0) === $dekenatId;
            }
            return $matchK;
        }));

        return response()->json([
            'success' => true,
            'dekenats' => $dekenats,
            'parokis' => $parokis,
        ]);
    }

    /**
     * Execute Full Installation Process.
     */
    public function install(Request $request)
    {
        if ($this->isInstalled()) {
            return response()->json([
                'success' => false,
                'message' => 'Aplikasi sudah terpasang.',
            ], 403);
        }

        $request->validate([
            'db_host' => 'required',
            'db_port' => 'required',
            'db_database' => 'required',
            'db_username' => 'required',
            'nama_keuskupan' => 'required',
            'nama_paroki' => 'required',
            'pastor_paroki' => 'required',
            'admin_name' => 'required|min:3',
            'admin_username' => 'required|min:3',
            'admin_email' => 'required|email',
            'admin_password' => 'required|min:6',
        ]);

        try {
            $dbHost = $request->input('db_host', '127.0.0.1');
            $dbPort = $request->input('db_port', '3306');
            $dbName = $request->input('db_database', 'siparoki_db');
            $dbUser = $request->input('db_username', 'root');
            $dbPass = $request->input('db_password', '');

            // 1. Ensure Database Exists
            $dsn = "mysql:host={$dbHost};port={$dbPort};charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 5,
            ]);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

            $appUrl = rtrim($request->root(), '/');

            // 2. Update .env File
            $this->updateEnvFile([
                'APP_NAME' => '"SIPAROKI 2026"',
                'APP_ENV' => 'production',
                'APP_DEBUG' => 'false',
                'APP_URL' => $appUrl,
                'DB_CONNECTION' => 'mysql',
                'DB_HOST' => $dbHost,
                'DB_PORT' => $dbPort,
                'DB_DATABASE' => $dbName,
                'DB_USERNAME' => $dbUser,
                'DB_PASSWORD' => '"' . $dbPass . '"',
            ]);

            // Set runtime DB config
            config([
                'database.connections.mysql.host' => $dbHost,
                'database.connections.mysql.port' => $dbPort,
                'database.connections.mysql.database' => $dbName,
                'database.connections.mysql.username' => $dbUser,
                'database.connections.mysql.password' => $dbPass,
            ]);
            DB::purge('mysql');
            DB::reconnect('mysql');

            // 3. Generate App Key if missing
            if (empty(env('APP_KEY')) || env('APP_KEY') === 'base64:') {
                Artisan::call('key:generate', ['--force' => true]);
            }

            // 4. Run Migrations
            Artisan::call('migrate', ['--force' => true]);

            // 5. Setup Master Keuskupan, Dekenat & Paroki
            $masterData = $this->getMasterData();
            $namaKeuskupan = trim($request->input('nama_keuskupan'));
            $namaDekenat = trim($request->input('nama_dekenat', ''));
            $namaParoki = trim($request->input('nama_paroki'));
            $alamatParoki = trim($request->input('alamat_paroki', ''));
            $pastorParoki = trim($request->input('pastor_paroki'));
            $keuskupanId = (int) $request->input('keuskupan_id', 0);
            $dekenatId = (int) $request->input('dekenat_id', 0);

            // Seed Keuskupan table if empty
            if (Schema::hasTable('keuskupan')) {
                if (DB::table('keuskupan')->count() === 0 && !empty($masterData['keuskupan'])) {
                    foreach ($masterData['keuskupan'] as $k) {
                        DB::table('keuskupan')->insertOrIgnore([
                            'id_keuskupan' => $k['id_keuskupan'],
                            'nama_keuskupan' => $k['nama_keuskupan'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                $activeKeuskupan = DB::table('keuskupan')
                    ->where('nama_keuskupan', $namaKeuskupan)
                    ->orWhere('id_keuskupan', $keuskupanId)
                    ->first();

                if (!$activeKeuskupan) {
                    $newKeuskupanId = DB::table('keuskupan')->insertGetId([
                        'nama_keuskupan' => $namaKeuskupan,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $keuskupanId = $newKeuskupanId;
                } else {
                    $keuskupanId = $activeKeuskupan->id_keuskupan;
                }
            }

            // Seed Dekenat & Kevikepan tables if empty
            if (Schema::hasTable('dekenat')) {
                if (DB::table('dekenat')->count() === 0 && !empty($masterData['dekenat'])) {
                    foreach ($masterData['dekenat'] as $d) {
                        DB::table('dekenat')->insertOrIgnore([
                            'id_dekenat' => $d['id_dekenat'] ?? $d['id'] ?? null,
                            'keuskupan_id' => $d['keuskupan_id'] ?? $keuskupanId,
                            'nama_dekenat' => $d['nama_dekenat'] ?? $d['nama_kevikepan'] ?? 'Dekenat',
                            'status' => 'Aktif',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                if (!empty($namaDekenat)) {
                    $activeDekenat = DB::table('dekenat')
                        ->where('nama_dekenat', $namaDekenat)
                        ->orWhere('id_dekenat', $dekenatId)
                        ->first();

                    if (!$activeDekenat) {
                        $dekenatId = DB::table('dekenat')->insertGetId([
                            'keuskupan_id' => $keuskupanId,
                            'nama_dekenat' => $namaDekenat,
                            'status' => 'Aktif',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        $dekenatId = $activeDekenat->id_dekenat;
                    }
                }
            }

            if (Schema::hasTable('kevikepan')) {
                if (DB::table('kevikepan')->count() === 0 && !empty($masterData['dekenat'])) {
                    foreach ($masterData['dekenat'] as $d) {
                        DB::table('kevikepan')->insertOrIgnore([
                            'id' => $d['id'] ?? $d['id_dekenat'] ?? null,
                            'keuskupan_id' => $d['keuskupan_id'] ?? $keuskupanId,
                            'nama_kevikepan' => $d['nama_dekenat'] ?? $d['nama_kevikepan'] ?? 'Dekenat',
                            'status' => 'Aktif',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                if (!empty($namaDekenat)) {
                    $activeKevikepan = DB::table('kevikepan')
                        ->where('nama_kevikepan', $namaDekenat)
                        ->orWhere('id', $dekenatId)
                        ->first();

                    if (!$activeKevikepan) {
                        $dekenatId = DB::table('kevikepan')->insertGetId([
                            'keuskupan_id' => $keuskupanId,
                            'nama_kevikepan' => $namaDekenat,
                            'status' => 'Aktif',
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    } else {
                        $dekenatId = $activeKevikepan->id;
                    }
                }
            }

            // Populate / Update Paroki & Profil Paroki
            $parokiId = 1;
            if (Schema::hasTable('paroki')) {
                $existingParoki = DB::table('paroki')->where('nama_paroki', $namaParoki)->first();
                $parokiData = [
                    'keuskupan_id' => $keuskupanId,
                    'dekenat_id' => $dekenatId ?: null,
                    'nama_paroki' => $namaParoki,
                    'alamat' => $alamatParoki,
                    'nama_pastor_paroki_aktif' => $pastorParoki,
                    'status' => 'Aktif',
                    'updated_at' => now(),
                ];

                if ($existingParoki) {
                    $parokiId = $existingParoki->id_paroki ?? $existingParoki->id ?? 1;
                    DB::table('paroki')->where('id_paroki', $parokiId)->update($parokiData);
                } else {
                    $parokiData['created_at'] = now();
                    $parokiId = DB::table('paroki')->insertGetId($parokiData);
                }
            }

            if (Schema::hasTable('profil_paroki')) {
                $profilCols = Schema::getColumnListing('profil_paroki');
                $profilData = [
                    'nama_paroki' => $namaParoki,
                    'keuskupan' => $namaKeuskupan,
                    'pastor_paroki' => $pastorParoki,
                    'alamat' => $alamatParoki,
                    'updated_at' => now(),
                ];
                if (in_array('paroki_id', $profilCols, true)) $profilData['paroki_id'] = $parokiId;
                if (in_array('keuskupan_id', $profilCols, true)) $profilData['keuskupan_id'] = $keuskupanId;
                if (in_array('dekenat_id', $profilCols, true)) $profilData['dekenat_id'] = $dekenatId;

                $filteredProfil = array_intersect_key($profilData, array_flip($profilCols));
                if (DB::table('profil_paroki')->count() > 0) {
                    DB::table('profil_paroki')->update($filteredProfil);
                } else {
                    $filteredProfil['created_at'] = now();
                    DB::table('profil_paroki')->insert($filteredProfil);
                }
            }

            if (Schema::hasTable('pengaturan_aplikasi')) {
                $appCols = Schema::getColumnListing('pengaturan_aplikasi');
                $appData = [
                    'nama_aplikasi' => 'SIPAROKI',
                    'nama_paroki' => $namaParoki,
                    'nama_keuskupan' => $namaKeuskupan,
                    'alamat' => $alamatParoki,
                    'updated_at' => now(),
                ];
                if (in_array('paroki_id', $appCols, true)) $appData['paroki_id'] = $parokiId;
                if (in_array('keuskupan_id', $appCols, true)) $appData['keuskupan_id'] = $keuskupanId;
                if (in_array('dekenat_id', $appCols, true)) $appData['dekenat_id'] = $dekenatId;
                if (in_array('is_setup_completed', $appCols, true)) $appData['is_setup_completed'] = 1;

                $filteredApp = array_intersect_key($appData, array_flip($appCols));
                if (DB::table('pengaturan_aplikasi')->count() > 0) {
                    DB::table('pengaturan_aplikasi')->update($filteredApp);
                } else {
                    $filteredApp['created_at'] = now();
                    DB::table('pengaturan_aplikasi')->insert($filteredApp);
                }
            }

            if (Schema::hasTable('pengaturan')) {
                DB::table('pengaturan')->updateOrInsert(['kunci' => 'nama_paroki'], ['nilai' => $namaParoki, 'updated_at' => now()]);
                DB::table('pengaturan')->updateOrInsert(['kunci' => 'keuskupan'], ['nilai' => $namaKeuskupan, 'updated_at' => now()]);
            }

            if (Schema::hasTable('identitas_paroki')) {
                $idCols = Schema::getColumnListing('identitas_paroki');
                $idData = [];
                if (in_array('nama_paroki', $idCols, true)) $idData['nama_paroki'] = $namaParoki;
                if (in_array('paroki_id', $idCols, true)) $idData['paroki_id'] = $parokiId;
                if (in_array('id_paroki', $idCols, true)) $idData['id_paroki'] = $parokiId;
                if (in_array('nama_keuskupan', $idCols, true)) $idData['nama_keuskupan'] = $namaKeuskupan;
                if (in_array('keuskupan', $idCols, true)) $idData['keuskupan'] = $namaKeuskupan;
                if (in_array('keuskupan_id', $idCols, true)) $idData['keuskupan_id'] = $keuskupanId;
                if (in_array('dekenat_id', $idCols, true)) $idData['dekenat_id'] = $dekenatId;
                if (in_array('nama_dekenat', $idCols, true)) $idData['nama_dekenat'] = $namaDekenat;
                if (in_array('pastor_paroki', $idCols, true)) $idData['pastor_paroki'] = $pastorParoki;
                if (in_array('nama_pastor_paroki_aktif', $idCols, true)) $idData['nama_pastor_paroki_aktif'] = $pastorParoki;
                if (in_array('alamat', $idCols, true)) $idData['alamat'] = $alamatParoki;

                if (!empty($idData)) {
                    if (DB::table('identitas_paroki')->count() > 0) {
                        DB::table('identitas_paroki')->update($idData);
                    } else {
                        DB::table('identitas_paroki')->insert($idData);
                    }
                }
            }

            // Setup Master Pastor default
            if (Schema::hasTable('master_pastor')) {
                $existingPastor = DB::table('master_pastor')
                    ->where('nama_pastor', 'like', '%' . $pastorParoki . '%')
                    ->orWhere('jabatan', 'Pastor Paroki')
                    ->first();

                if ($existingPastor) {
                    DB::table('master_pastor')->where('id', $existingPastor->id)->update([
                        'nama_pastor' => preg_replace('/^(RD\.|RP\.|Mgr\.)\s*/i', '', $pastorParoki),
                        'gelar_depan' => str_starts_with(strtoupper($pastorParoki), 'RP.') ? 'RP.' : 'RD.',
                        'jabatan' => 'Pastor Paroki',
                        'jenis_imam' => 'Diosesan / Projo',
                        'status' => 'Aktif Melayani',
                        'urutan' => 1,
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('master_pastor')->insert([
                        'nama_pastor' => preg_replace('/^(RD\.|RP\.|Mgr\.)\s*/i', '', $pastorParoki),
                        'gelar_depan' => str_starts_with(strtoupper($pastorParoki), 'RP.') ? 'RP.' : 'RD.',
                        'jabatan' => 'Pastor Paroki',
                        'jenis_imam' => 'Diosesan / Projo',
                        'keuskupan' => $namaKeuskupan,
                        'status' => 'Aktif Melayani',
                        'urutan' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // 6. Create Super Admin User
            $adminName = $request->input('admin_name');
            $adminEmail = strtolower(trim($request->input('admin_email')));
            $adminUsername = strtolower(trim($request->input('admin_username')));
            $adminPassword = $request->input('admin_password');

            if (Schema::hasTable('users')) {
                $userCols = Schema::getColumnListing('users');
                $userData = [
                    'name' => $adminName,
                    'email' => $adminEmail,
                    'password' => Hash::make($adminPassword),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (in_array('username', $userCols, true)) {
                    $userData['username'] = $adminUsername;
                }
                if (in_array('role', $userCols, true)) {
                    $userData['role'] = 'superadmin';
                }
                if (in_array('peran', $userCols, true)) {
                    $userData['peran'] = 'Super Admin';
                }
                if (in_array('status', $userCols, true)) {
                    $userData['status'] = 'Aktif';
                }
                if (in_array('paroki_id', $userCols, true)) {
                    $userData['paroki_id'] = $parokiId;
                }
                if (in_array('email_verified_at', $userCols, true)) {
                    $userData['email_verified_at'] = now();
                }

                $existingUser = DB::table('users')->where('email', $adminEmail)->first();
                if ($existingUser) {
                    DB::table('users')->where('id', $existingUser->id)->update($userData);
                } else {
                    DB::table('users')->insert($userData);
                }
            }

            // 7. Write Installation Lock File
            File::put(storage_path('installed'), json_encode([
                'installed_at' => date('Y-m-d H:i:s'),
                'paroki' => $namaParoki,
                'keuskupan' => $namaKeuskupan,
                'admin_email' => $adminEmail,
                'version' => 'SIPAROKI 2026',
            ], JSON_PRETTY_PRINT));

            File::put(storage_path('framework/installed'), date('Y-m-d H:i:s'));

            // Clear optimization cache
            try {
                Artisan::call('optimize:clear');
            } catch (\Throwable $e) {}

            $loginUrl = $appUrl . '/login';
            $homeUrl = $appUrl . '/';

            return response()->json([
                'success' => true,
                'message' => 'Instalasi SIPAROKI berhasil selesai!',
                'redirect' => $loginUrl,
                'login_url' => $loginUrl,
                'home_url' => $homeUrl,
                'admin_email' => $adminEmail,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses instalasi: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper to update values in .env file safely.
     */
    protected function updateEnvFile(array $data): void
    {
        $envPath = base_path('.env');
        if (!File::exists($envPath)) {
            $examplePath = base_path('.env.example');
            if (File::exists($examplePath)) {
                File::copy($examplePath, $envPath);
            } else {
                File::put($envPath, '');
            }
        }

        $envContent = File::get($envPath);

        foreach ($data as $key => $value) {
            $pattern = "/^{$key}=(.*)$/m";
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, "{$key}={$value}", $envContent);
            } else {
                $envContent .= "\n{$key}={$value}";
            }
        }

        File::put($envPath, $envContent);
    }
}
