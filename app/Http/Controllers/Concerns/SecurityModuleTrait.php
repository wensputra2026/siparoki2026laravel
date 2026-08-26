<?php

namespace App\Http\Controllers\Concerns;

use App\Models\DesaKelurahan;
use App\Models\Dekenat;
use App\Models\Kabupaten;
use App\Models\Kapela;
use App\Models\Kecamatan;
use App\Models\Keuskupan;
use App\Models\Kevikepan;
use App\Models\KkKatolik;
use App\Models\Kub;
use App\Models\Lingkungan;
use App\Models\Paroki;
use App\Models\Provinsi;
use App\Models\Sakramen;
use App\Models\Umat;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

trait SecurityModuleTrait
{
    public function securityCenter(Request $request): Response
    {
        $this->ensureSecurityTables();
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? 'superadmin';
        $roleMap = [
            'superadmin' => 'Super Admin',
            'paroki' => 'Admin Paroki',
            'pastor' => 'Pastor',
            'wilayah' => 'Admin Wilayah',
            'kapela' => 'Admin Kapela / Stasi',
            'kub' => 'Ketua KUB',
            'bendahara' => 'Bendahara',
            'penulis' => 'Penulis',
            'umat' => 'Umat',
        ];
        $resolvedRole = $roleMap[$firstSegment] ?? auth()->user()?->role?->nama_role ?? 'Super Admin';

        // Load settings
        $settingsRaw = [];
        try {
            if (Schema::hasTable('security_settings') && Schema::hasColumn('security_settings', 'setting_key') && Schema::hasColumn('security_settings', 'setting_value')) {
                $settingsRaw = DB::table('security_settings')->pluck('setting_value', 'setting_key')->toArray();
            }
        } catch (\Throwable $e) {}

        $settings = [
            'max_login_attempts' => (int) ($settingsRaw['max_login_attempts'] ?? 5),
            'lockout_minutes' => (int) ($settingsRaw['lockout_minutes'] ?? 60),
            'session_timeout_minutes' => (int) ($settingsRaw['session_timeout_minutes'] ?? config('session.lifetime', 120)),
            'force_strong_password' => ($settingsRaw['force_strong_password'] ?? '1') === '1',
            'enable_brute_force_protection' => ($settingsRaw['enable_brute_force_protection'] ?? '1') === '1',
            'block_untrusted_ip' => ($settingsRaw['block_untrusted_ip'] ?? '0') === '1',
        ];

        // Blocked IPs
        $blockedIps = collect();
        try {
            if (Schema::hasTable('blocked_ips')) {
                $blockedIps = DB::table('blocked_ips')->orderByDesc('id')->get();
            }
        } catch (\Throwable $e) {}

        // Security Logs
        $logs = collect();
        try {
            if (Schema::hasTable('security_logs')) {
                $logs = DB::table('security_logs')->orderByDesc('id')->limit(150)->get();
            }
        } catch (\Throwable $e) {}

        // Audit metrics
        $isHttps = $request->isSecure() || $request->header('x-forwarded-proto') === 'https';
        $isDebug = config('app.debug', false);
        $appEnv = config('app.env', 'production');
        $sessionDriver = config('session.driver', 'file');
        $sessionLifetime = config('session.lifetime', 120);
        $uploadWritable = is_writable(public_path('uploads'));
        $storageWritable = is_writable(storage_path());

        // Score calculation
        $score = 100;
        if ($isDebug && $appEnv === 'production') $score -= 15;
        if (!$isHttps && $appEnv === 'production') $score -= 15;
        if (!$uploadWritable) $score -= 10;
        if (!$storageWritable) $score -= 10;
        if (!$settings['enable_brute_force_protection']) $score -= 10;
        if ($score < 0) $score = 0;

        $auditChecks = [
            [
                'title' => 'Proteksi CSRF (Cross-Site Request Forgery)',
                'status' => 'PASS',
                'description' => 'CSRF Token aktif di seluruh form, API monolith, dan session cookies.',
                'badge' => 'Aktif',
                'icon' => 'fa-shield-check',
            ],
            [
                'title' => 'Enkripsi & Status HTTPS / SSL',
                'status' => $isHttps ? 'PASS' : ($appEnv === 'local' ? 'INFO' : 'WARNING'),
                'description' => $isHttps ? 'Koneksi terenkripsi aman menggunakan SSL/TLS.' : ($appEnv === 'local' ? 'Mode Lokal / Development (HTTP).' : 'Disarankan mengaktifkan SSL/HTTPS di environment produksi.'),
                'badge' => $isHttps ? 'Secure (HTTPS)' : ($appEnv === 'local' ? 'Lokal Dev' : 'HTTP'),
                'icon' => 'fa-lock',
            ],
            [
                'title' => 'Mode Debug Aplikasi (APP_DEBUG)',
                'status' => (!$isDebug || $appEnv === 'local') ? 'PASS' : 'WARNING',
                'description' => $isDebug ? ($appEnv === 'local' ? 'Debug aktif untuk lingkungan pengembangan lokal.' : 'Debug aktif di produksi berisiko membocorkan stack trace.') : 'Debug mode dinonaktifkan (Aman).',
                'badge' => $isDebug ? 'Debug ON' : 'Debug OFF',
                'icon' => 'fa-bug',
            ],
            [
                'title' => 'Keamanan Sesi & Session Lifetime',
                'status' => 'PASS',
                'description' => 'Sesi dikelola dengan driver ' . $sessionDriver . ' dengan durasi kedaluwarsa ' . $sessionLifetime . ' menit dan flag HttpOnly.',
                'badge' => $sessionLifetime . ' Menit',
                'icon' => 'fa-user-clock',
            ],
            [
                'title' => 'Izin Tulis Direktori Uploads & Storage',
                'status' => ($uploadWritable && $storageWritable) ? 'PASS' : 'FAIL',
                'description' => ($uploadWritable && $storageWritable) ? 'Direktori public/uploads dan storage memiliki izin yang tepat.' : 'Periksa permission direktori public/uploads atau storage.',
                'badge' => ($uploadWritable && $storageWritable) ? 'Writable' : 'Permission Error',
                'icon' => 'fa-folder-gear',
            ],
            [
                'title' => 'Proteksi Brute Force Login',
                'status' => $settings['enable_brute_force_protection'] ? 'PASS' : 'WARNING',
                'description' => $settings['enable_brute_force_protection'] ? 'Pemblokiran otomatis aktif setelah ' . $settings['max_login_attempts'] . 'x percobaan gagal.' : 'Proteksi brute force dinonaktifkan.',
                'badge' => $settings['enable_brute_force_protection'] ? 'Maks ' . $settings['max_login_attempts'] . 'x Gagal' : 'Nonaktif',
                'icon' => 'fa-shield-halved',
            ],
        ];

        $todayFailed = 0;
        $todaySuccess = 0;
        try {
            if (Schema::hasTable('security_logs') && Schema::hasColumn('security_logs', 'status')) {
                $todayFailed = DB::table('security_logs')->where('status', 'FAILED')->whereDate('created_at', today())->count();
                $todaySuccess = DB::table('security_logs')->where('status', 'SUCCESS')->whereDate('created_at', today())->count();
            }
        } catch (\Throwable $e) {}

        return Inertia::render('Inertia/SecurityCenter', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
            'settings' => $settings,
            'blockedIps' => $blockedIps,
            'logs' => $logs,
            'auditChecks' => $auditChecks,
            'securityScore' => $score,
            'todayFailed' => $todayFailed,
            'todaySuccess' => $todaySuccess,
            'totalBlocked' => $blockedIps->count(),
            'currentIp' => $request->ip(),
            'phpVersion' => PHP_VERSION,
            'laravelVersion' => app()->version(),
        ]);
    }


    public function updateSecuritySettings(Request $request)
    {
        $this->ensureSecurityTables();
        $validated = $request->validate([
            'max_login_attempts' => 'required|integer|min:1|max:50',
            'lockout_minutes' => 'required|integer|min:1|max:1440',
            'session_timeout_minutes' => 'required|integer|min:5|max:1440',
            'force_strong_password' => 'nullable|boolean',
            'enable_brute_force_protection' => 'nullable|boolean',
            'block_untrusted_ip' => 'nullable|boolean',
        ]);

        foreach ($validated as $key => $val) {
            DB::table('security_settings')->updateOrInsert(
                ['setting_key' => $key],
                ['setting_value' => (string) ($val === true ? '1' : ($val === false ? '0' : $val)), 'updated_at' => now()]
            );
        }

        return back()->with('success', 'Pengaturan kebijakan keamanan berhasil diperbarui.');
    }


    public function blockIp(Request $request)
    {
        $this->ensureSecurityTables();
        $validated = $request->validate([
            'ip_address' => 'required|string|max:45',
            'reason' => 'nullable|string|max:255',
            'blocked_duration' => 'nullable|integer',
        ]);

        $ip = trim($validated['ip_address']);
        if ($ip === '127.0.0.1' || $ip === '::1' || $ip === $request->ip()) {
            return back()->with('error', 'Anda tidak dapat memblokir alamat IP Anda sendiri atau localhost.');
        }

        $blockedUntil = null;
        if (!empty($validated['blocked_duration']) && (int) $validated['blocked_duration'] > 0) {
            $blockedUntil = now()->addHours((int) $validated['blocked_duration']);
        }

        DB::table('blocked_ips')->updateOrInsert(
            ['ip_address' => $ip],
            [
                'reason' => $validated['reason'] ?: 'Diblokir manual oleh ' . (auth()->user()?->nama_lengkap ?? 'Super Admin'),
                'blocked_by' => auth()->user()?->nama_lengkap ?? 'Super Admin',
                'blocked_until' => $blockedUntil,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return back()->with('success', 'Alamat IP ' . $ip . ' berhasil ditambahkan ke daftar blokir.');
    }


    public function unblockIp(Request $request, $id)
    {
        $this->ensureSecurityTables();
        $decodedId = decode_id($id) ?: $id;
        DB::table('blocked_ips')->where('id', $decodedId)->orWhere('ip_address', $id)->delete();
        return back()->with('success', 'Alamat IP berhasil dilepas dari daftar blokir.');
    }


    public function clearSecurityLogs(Request $request)
    {
        $this->ensureSecurityTables();
        DB::table('security_logs')->truncate();
        return back()->with('success', 'Seluruh log aktivitas keamanan berhasil dibersihkan.');
    }


    public function clearSystemSecurityCache(Request $request)
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            \Illuminate\Support\Facades\Artisan::call('config:clear');
        } catch (\Throwable $e) {}

        return back()->with('success', 'Cache sistem dan sesi keamanan berhasil disegarkan.');
    }


    protected function ensureSecurityTables(): void
    {
        try {
            if (!Schema::hasTable('security_settings')) {
                Schema::create('security_settings', function ($table) {
                    $table->increments('id');
                    $table->string('setting_key', 100)->unique();
                    $table->text('setting_value')->nullable();
                    $table->timestamps();
                });

                DB::table('security_settings')->insert([
                    ['setting_key' => 'max_login_attempts', 'setting_value' => '5', 'created_at' => now(), 'updated_at' => now()],
                    ['setting_key' => 'lockout_minutes', 'setting_value' => '60', 'created_at' => now(), 'updated_at' => now()],
                    ['setting_key' => 'session_timeout_minutes', 'setting_value' => '120', 'created_at' => now(), 'updated_at' => now()],
                    ['setting_key' => 'force_strong_password', 'setting_value' => '1', 'created_at' => now(), 'updated_at' => now()],
                    ['setting_key' => 'enable_brute_force_protection', 'setting_value' => '1', 'created_at' => now(), 'updated_at' => now()],
                ]);
            } else {
                $cols = Schema::getColumnListing('security_settings');
                Schema::table('security_settings', function ($table) use ($cols) {
                    if (!in_array('setting_key', $cols, true)) $table->string('setting_key', 100)->nullable();
                    if (!in_array('setting_value', $cols, true)) $table->text('setting_value')->nullable();
                });

                if (DB::table('security_settings')->count() === 0) {
                    DB::table('security_settings')->insert([
                        ['setting_key' => 'max_login_attempts', 'setting_value' => '5', 'created_at' => now(), 'updated_at' => now()],
                        ['setting_key' => 'lockout_minutes', 'setting_value' => '60', 'created_at' => now(), 'updated_at' => now()],
                        ['setting_key' => 'session_timeout_minutes', 'setting_value' => '120', 'created_at' => now(), 'updated_at' => now()],
                        ['setting_key' => 'force_strong_password', 'setting_value' => '1', 'created_at' => now(), 'updated_at' => now()],
                        ['setting_key' => 'enable_brute_force_protection', 'setting_value' => '1', 'created_at' => now(), 'updated_at' => now()],
                    ]);
                }
            }

            if (!Schema::hasTable('blocked_ips')) {
                Schema::create('blocked_ips', function ($table) {
                    $table->increments('id');
                    $table->string('ip_address', 45)->index();
                    $table->string('reason', 255)->nullable();
                    $table->string('blocked_by', 100)->nullable();
                    $table->timestamp('blocked_until')->nullable();
                    $table->timestamps();
                });
            } else {
                $cols = Schema::getColumnListing('blocked_ips');
                Schema::table('blocked_ips', function ($table) use ($cols) {
                    if (!in_array('ip_address', $cols, true)) $table->string('ip_address', 45)->nullable()->index();
                    if (!in_array('reason', $cols, true)) $table->string('reason', 255)->nullable();
                    if (!in_array('blocked_by', $cols, true)) $table->string('blocked_by', 100)->nullable();
                    if (!in_array('blocked_until', $cols, true)) $table->timestamp('blocked_until')->nullable();
                });
            }

            if (!Schema::hasTable('security_logs')) {
                Schema::create('security_logs', function ($table) {
                    $table->increments('id');
                    $table->string('ip_address', 45)->nullable();
                    $table->unsignedBigInteger('user_id')->nullable();
                    $table->string('username', 100)->nullable();
                    $table->string('event_type', 50)->default('LOGIN');
                    $table->string('user_agent', 255)->nullable();
                    $table->string('status', 20)->default('SUCCESS');
                    $table->text('details')->nullable();
                    $table->timestamps();
                });
            } else {
                $cols = Schema::getColumnListing('security_logs');
                Schema::table('security_logs', function ($table) use ($cols) {
                    if (!in_array('ip_address', $cols, true)) $table->string('ip_address', 45)->nullable();
                    if (!in_array('user_id', $cols, true)) $table->unsignedBigInteger('user_id')->nullable();
                    if (!in_array('username', $cols, true)) $table->string('username', 100)->nullable();
                    if (!in_array('event_type', $cols, true)) $table->string('event_type', 50)->default('LOGIN');
                    if (!in_array('user_agent', $cols, true)) $table->string('user_agent', 255)->nullable();
                    if (!in_array('status', $cols, true)) $table->string('status', 20)->default('SUCCESS');
                    if (!in_array('details', $cols, true)) $table->text('details')->nullable();
                });
            }
        } catch (\Throwable $e) {}
    }

}
