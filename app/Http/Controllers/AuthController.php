<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Umat;
use App\Models\Wilayah;
use App\Models\Lingkungan;
use App\Models\Kub;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Concerns\SecurityModuleTrait;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    use SecurityModuleTrait;
    /**
     * Helper to redirect authenticated user to their role dashboard.
     */
    protected function redirectUserByRole($user)
    {
        $roleSlug = strtolower($user->role->slug ?? $user->role->nama_role ?? '');

        if (str_contains($roleSlug, 'super')) {
            return redirect('/superadmin');
        } elseif (str_contains($roleSlug, 'pastor')) {
            return redirect('/pastor');
        } elseif (str_contains($roleSlug, 'paroki') || str_contains($roleSlug, 'sekretariat')) {
            return redirect('/paroki');
        } elseif (str_contains($roleSlug, 'wilayah')) {
            return redirect('/wilayah');
        } elseif (str_contains($roleSlug, 'kapela') || str_contains($roleSlug, 'stasi')) {
            return redirect('/kapela');
        } elseif (str_contains($roleSlug, 'kub')) {
            return redirect('/kub');
        } elseif (str_contains($roleSlug, 'bendahara')) {
            return redirect('/bendahara');
        } elseif (str_contains($roleSlug, 'penulis') || str_contains($roleSlug, 'komsos')) {
            return redirect('/penulis');
        } elseif (str_contains($roleSlug, 'umat')) {
            return redirect('/umat');
        }

        return redirect('/superadmin');
    }

    /**
     * Get CAPTCHA configuration from security_settings.
     */
    protected function getCaptchaConfig(): array
    {
        $provider = 'Simple CAPTCHA';
        $siteKey = '';
        $secretKey = '';

        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('security_settings')) {
                $settings = DB::table('security_settings')
                    ->whereIn('setting_key', ['captcha_provider', 'captcha_site_key', 'captcha_secret_key'])
                    ->pluck('setting_value', 'setting_key')
                    ->toArray();

                $provider = $settings['captcha_provider'] ?? 'Simple CAPTCHA';
                $siteKey = $settings['captcha_site_key'] ?? '';
                $secretKey = $settings['captcha_secret_key'] ?? '';
            }
        } catch (\Throwable $e) {}

        return [
            'provider' => $provider,
            'site_key' => $siteKey,
            'secret_key' => $secretKey,
        ];
    }

    /**
     * Check if CAPTCHA is required for login.
     */
    protected function isCaptchaRequired(Request $request): bool
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('security_settings')) {
                $settings = DB::table('security_settings')
                    ->whereIn('setting_key', ['captcha_enabled', 'captcha_required_backend_login', 'captcha_show_after_failed_attempts'])
                    ->pluck('setting_value', 'setting_key')
                    ->toArray();

                // If explicitly disabled by admin in Security Center, return false
                if (isset($settings['captcha_enabled']) && $settings['captcha_enabled'] === '0') {
                    return false;
                }

                if (isset($settings['captcha_required_backend_login']) && $settings['captcha_required_backend_login'] === '0') {
                    return false;
                }

                $threshold = (int) ($settings['captcha_show_after_failed_attempts'] ?? 0);
                if ($threshold > 0) {
                    $sessionFails = (int) session('login_failed_attempts', 0);
                    if ($sessionFails < $threshold) {
                        return false;
                    }
                }
            }

            // Active by default
            return true;
        } catch (\Throwable $e) {
            return true;
        }
    }

    /**
     * Verify CAPTCHA response based on active provider.
     */
    protected function verifyCaptchaResponse(Request $request): bool
    {
        $cfg = $this->getCaptchaConfig();
        $provider = $cfg['provider'];
        $secretKey = $cfg['secret_key'];

        // Google reCAPTCHA v2 / v3
        if (($provider === 'Google reCAPTCHA v2 Checkbox' || $provider === 'Google reCAPTCHA v3 Invisible') && !empty($cfg['site_key'])) {
            $recaptchaToken = $request->input('g-recaptcha-response');
            if (empty($recaptchaToken)) {
                return false;
            }
            if (empty($secretKey)) {
                return true;
            }
            try {
                $response = \Illuminate\Support\Facades\Http::asForm()->timeout(5)->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $secretKey,
                    'response' => $recaptchaToken,
                    'remoteip' => $request->ip(),
                ]);
                $data = $response->json();
                return (bool) ($data['success'] ?? false);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Google reCAPTCHA verification failed: ' . $e->getMessage());
                return false;
            }
        }

        // Cloudflare Turnstile
        if ($provider === 'Cloudflare Turnstile' && !empty($cfg['site_key'])) {
            $turnstileToken = $request->input('cf-turnstile-response');
            if (empty($turnstileToken)) {
                return false;
            }
            if (empty($secretKey)) {
                return true;
            }
            try {
                $response = \Illuminate\Support\Facades\Http::asForm()->timeout(5)->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => $secretKey,
                    'response' => $turnstileToken,
                    'remoteip' => $request->ip(),
                ]);
                $data = $response->json();
                return (bool) ($data['success'] ?? false);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('Cloudflare Turnstile verification failed: ' . $e->getMessage());
                return false;
            }
        }

        // Default / Fallback: Simple CAPTCHA (Math challenge)
        $captchaInput = trim((string) $request->input('captcha', ''));
        $expectedAnswer = (string) session('simple_captcha_answer', '');
        return ($captchaInput !== '' && $captchaInput === $expectedAnswer);
    }

    /**
     * Generate simple math CAPTCHA challenge and store answer in session.
     */
    protected function generateSimpleCaptcha(): array
    {
        $n1 = rand(1, 10);
        $n2 = rand(1, 9);
        $question = "{$n1} + {$n2} = ?";
        $answer = (string) ($n1 + $n2);

        session(['simple_captcha_answer' => $answer]);

        return [
            'question' => $question,
            'answer' => $answer,
        ];
    }

    /**
     * Endpoint to refresh CAPTCHA challenge via AJAX.
     */
    public function refreshCaptcha(Request $request)
    {
        $captcha = $this->generateSimpleCaptcha();
        return response()->json([
            'success' => true,
            'question' => $captcha['question'],
        ]);
    }

    /**
     * Show login page.
     */
    public function showLogin(Request $request)
    {
        if (Auth::check()) {
            $user = Auth::user();
            $isSuperAdmin = ((int) ($user->role_id ?? 0) === 1) || in_array(strtolower($user->role?->nama_role ?? ($user->role?->name ?? ($user->role?->slug ?? ''))), ['superadmin', 'super admin', 'super administrator', 'superadministrator'], true);

            // If backend maintenance is active and user is not Super Admin, logout immediately
            if (!$isSuperAdmin && \Illuminate\Support\Facades\Schema::hasTable('pengaturan_aplikasi')) {
                $p = DB::table('pengaturan_aplikasi')->first();
                if ($p && ($p->maintenance_backend ?? '0') === '1') {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    $msg = $p->maintenance_message_backend ?: ($p->pesan_maintenance ?: 'Panel aplikasi sedang dalam pemeliharaan sistem oleh Super Admin. Seluruh akses pengguna selain Super Admin sementara ditutup.');
                    return view('pages.auth.login', [
                        'status' => null,
                        'errors' => new \Illuminate\Support\ViewErrorBag(),
                    ])->with('error', $msg);
                }
            }

            return $this->redirectUserByRole($user);
        }

        $kickedMsg = null;
        if ($request->query('maintenance_kicked') === '1') {
            $kickedMsg = 'Sesi Anda telah dihentikan otomatis karena Super Admin sedang mengaktifkan Mode Pemeliharaan Panel Petugas & Umat.';
        }

        try {
            $this->ensureSecurityTables();
        } catch (\Throwable $e) {}

        $showCaptcha = $this->isCaptchaRequired($request);
        $captchaData = $this->generateSimpleCaptcha();
        $captchaQuestion = $captchaData['question'];
        $captchaConfig = $this->getCaptchaConfig();

        return response()
            ->view('pages.auth.login', [
                'status' => session('status'),
                'error_message' => $kickedMsg ?: session('error'),
                'showCaptcha' => $showCaptcha,
                'captchaQuestion' => $captchaQuestion,
                'captchaProvider' => $captchaConfig['provider'],
                'captchaSiteKey' => $captchaConfig['site_key'],
                'errors' => session('errors') ?? new \Illuminate\Support\ViewErrorBag(),
            ])
            ->header('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Process user login authentication.
     */
    public function processLogin(Request $request)
    {
        $ip = $request->ip();
        $userAgent = substr((string) $request->userAgent(), 0, 255);

        // 1. Check if IP is currently blocked
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('blocked_ips')) {
                $blocked = DB::table('blocked_ips')
                    ->where('ip_address', $ip)
                    ->where(function ($q) {
                        $q->whereNull('blocked_until')->orWhere('blocked_until', '>', now());
                    })
                    ->first();

                if ($blocked) {
                    return back()->withErrors([
                        'login' => 'Akses dari alamat IP Anda (' . $ip . ') diblokir oleh Firewall Sistem. Alasan: ' . ($blocked->reason ?: 'Aktivitas mencurigakan'),
                    ])->onlyInput('login');
                }
            }
        } catch (\Throwable $e) {}

        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required' => 'Email atau Username wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        // 2. Validate CAPTCHA if required
        if ($this->isCaptchaRequired($request)) {
            if (! $this->verifyCaptchaResponse($request)) {
                $sessionFails = (int) session('login_failed_attempts', 0);
                session(['login_failed_attempts' => $sessionFails + 1]);

                $this->generateSimpleCaptcha();

                return back()->withErrors([
                    'captcha' => 'Hasil verifikasi keamanan (CAPTCHA) tidak sesuai. Silakan coba kembali.',
                ])->onlyInput('login');
            }
        }

        $login = $request->input('login');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $remember = $request->boolean('remember');

        if (Auth::attempt([$field => $login, 'password' => $request->password], $remember) ||
            ($field === 'email' && Auth::attempt(['username' => $login, 'password' => $request->password], $remember))) {
            $request->session()->regenerate();

            // Clear CAPTCHA and fail tracking on successful login
            session()->forget(['simple_captcha_answer', 'login_failed_attempts']);

            $user = Auth::user();

            // Check if Backend / Panel Maintenance is active for non-superadmin
            $isSuperAdmin = ((int) ($user->role_id ?? 0) === 1) || in_array(strtolower($user->role?->nama_role ?? ($user->role?->name ?? ($user->role?->slug ?? ''))), ['superadmin', 'super admin', 'super administrator', 'superadministrator'], true);

            if (!$isSuperAdmin && \Illuminate\Support\Facades\Schema::hasTable('pengaturan_aplikasi')) {
                $p = DB::table('pengaturan_aplikasi')->first();
                if ($p && ($p->maintenance_backend ?? '0') === '1') {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    $msg = $p->maintenance_message_backend
                        ?: ($p->pesan_maintenance
                        ?: 'Panel administrasi aplikasi sedang dalam masa pemeliharaan sistem oleh Super Admin. Akses pengguna selain Super Admin sementara ditutup.');

                    return back()->withErrors([
                        'login' => $msg,
                    ])->onlyInput('login');
                }
            }

            // Log successful login
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('security_logs')) {
                    DB::table('security_logs')->insert([
                        'ip_address' => $ip,
                        'user_id' => $user->id ?? null,
                        'username' => $user->username ?? $user->email ?? $login,
                        'event_type' => 'LOGIN',
                        'user_agent' => $userAgent,
                        'status' => 'SUCCESS',
                        'details' => 'Login berhasil via web portal (' . ($user->role->nama_role ?? 'User') . ')',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } catch (\Throwable $e) {}

            return $this->redirectUserByRole($user)->with('success', 'Selamat datang kembali, ' . ($user->name ?? $user->nama_lengkap ?? 'Petugas') . '!');
        }

        // Log failed login attempt
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('security_logs')) {
                DB::table('security_logs')->insert([
                    'ip_address' => $ip,
                    'user_id' => null,
                    'username' => $login,
                    'event_type' => 'LOGIN_ATTEMPT',
                    'user_agent' => $userAgent,
                    'status' => 'FAILED',
                    'details' => 'Percobaan login gagal (Password atau username salah)',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Check recent failed attempts for brute-force detection (e.g. 5 fails in 15 mins)
                $maxAttempts = 5;
                if (\Illuminate\Support\Facades\Schema::hasTable('security_settings')) {
                    $settingMax = DB::table('security_settings')->where('setting_key', 'max_login_attempts')->value('setting_value');
                    if (!empty($settingMax) && is_numeric($settingMax)) {
                        $maxAttempts = (int) $settingMax;
                    }
                }

                $recentFails = DB::table('security_logs')
                    ->where('ip_address', $ip)
                    ->where('status', 'FAILED')
                    ->where('created_at', '>=', now()->subMinutes(15))
                    ->count();

                if ($recentFails >= $maxAttempts && \Illuminate\Support\Facades\Schema::hasTable('blocked_ips')) {
                    $alreadyBlocked = DB::table('blocked_ips')->where('ip_address', $ip)->exists();
                    if (!$alreadyBlocked) {
                        DB::table('blocked_ips')->insert([
                            'ip_address' => $ip,
                            'reason' => 'Brute Force Protection: ' . $recentFails . 'x percobaan login gagal',
                            'blocked_by' => 'Auto-Firewall',
                            'blocked_until' => now()->addMinutes(60),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        } catch (\Throwable $e) {}

        // Track session failed attempts for adaptive CAPTCHA
        $sessionFails = (int) session('login_failed_attempts', 0);
        session(['login_failed_attempts' => $sessionFails + 1]);

        return back()->withErrors([
            'login' => 'Email, Username, atau Kata Sandi yang Anda masukkan salah.',
        ])->onlyInput('login');
    }

    /**
     * Show Register page (Dialihkan ke Cek Data Umat Mandiri).
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectUserByRole(Auth::user());
        }

        // Umat paroki tidak memerlukan pembuatan akun mandiri.
        // Data mereka dicek langsung melalui NIK di portal publik.
        return redirect()->route('cek-data-umat')->with('info', 'Umat paroki tidak memerlukan akun login mandiri. Anda dapat langsung mengecek status data sensus & sakramen melalui NIK Anda.');
    }

    /**
     * Process user registration.
     */
    public function processRegister(Request $request)
    {
        return redirect()->route('cek-data-umat')->with('info', 'Pendaftaran akun mandiri dinonaktifkan. Data sensus umat dikelola langsung oleh Sekretariat Paroki.');
    }

    /**
     * Show Forgot Password page.
     */
    public function showForgotPassword()
    {
        if (Auth::check()) {
            return $this->redirectUserByRole(Auth::user());
        }

        return view('pages.auth.lupa-password', [
            'status' => session('status'),
        ]);
    }

    /**
     * Process password reset request.
     */
    public function processForgotPassword(Request $request)
    {
        $request->validate([
            'identitas' => 'required|string',
        ], [
            'identitas.required' => 'Silakan masukkan Email, Username, atau Nomor WhatsApp terdaftar.',
        ]);

        $identitas = trim($request->identitas);

        $user = User::where('email', $identitas)
            ->orWhere('username', $identitas)
            ->orWhere('no_hp', $identitas)
            ->first();

        // Always return the same response to avoid user enumeration.
        // (No email/SMS is actually dispatched in this build; recovery is
        // handled by the Sekretariat Paroki.)
        if ($user) {
            try {
                \Illuminate\Support\Facades\DB::table('security_logs')->insert([
                    'ip_address' => $request->ip(),
                    'user_id' => $user->id,
                    'username' => $user->username ?? $user->email ?? $identitas,
                    'event_type' => 'PASSWORD_RESET_REQUEST',
                    'user_agent' => substr((string) $request->userAgent(), 0, 255),
                    'status' => 'SUCCESS',
                    'details' => 'Permintaan reset password diajukan.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (\Throwable $e) {}
        }

        return back()->with('status', 'Jika data terdaftar, permintaan reset kata sandi telah dicatat. Silakan hubungi Sekretariat Paroki atau periksa pesan WhatsApp/Email untuk instruksi pemulihan akun.');
    }

    /**
     * Logout authenticated user.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->header('X-Inertia')) {
            return Inertia::location(url('/login'));
        }

        return redirect('/login')->with('status', 'Anda telah berhasil keluar dari sistem.');
    }
}
