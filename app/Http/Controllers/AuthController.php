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
            }

            // Active by default
            return true;
        } catch (\Throwable $e) {
            return true;
        }
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

        return response()
            ->view('pages.auth.login', [
                'status' => session('status'),
                'error_message' => $kickedMsg ?: session('error'),
                'showCaptcha' => $showCaptcha,
                'captchaQuestion' => $captchaQuestion,
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
            $captchaInput = trim((string) $request->input('captcha', ''));
            $expectedAnswer = (string) session('simple_captcha_answer', '');

            if ($captchaInput === '' || $captchaInput !== $expectedAnswer) {
                $sessionFails = (int) session('login_failed_attempts', 0);
                session(['login_failed_attempts' => $sessionFails + 1]);

                $this->generateSimpleCaptcha();

                return back()->withErrors([
                    'captcha' => 'Hasil verifikasi keamanan (CAPTCHA) tidak sesuai. Silakan hitung kembali.',
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
     * Show Register page.
     */
    public function showRegister()
    {
        if (Auth::check()) {
            return $this->redirectUserByRole(Auth::user());
        }

        $wilayahs = collect();
        $kapelas = collect();
        $kubs = collect();

        try {
            $wilayahs = DB::table('wilayah')->orderBy('nama_wilayah')->get(['id', 'nama_wilayah', 'kode_wilayah']);
        } catch (\Throwable $e) {}

        try {
            $kapelas = DB::table('kapela')
                ->where(function($q) {
                    $q->where('is_deleted', 0)->orWhereNull('is_deleted');
                })
                ->orderBy('nama_kapela')
                ->get(['id', 'nama_kapela', 'kode_kapela']);
        } catch (\Throwable $e) {}

        try {
            $kubs = DB::table('kub')->orderBy('nama_kub')->get(['id', 'nama_kub', 'wilayah_id', 'kapela_id']);
        } catch (\Throwable $e) {}

        return view('pages.auth.register', [
            'wilayahs' => $wilayahs,
            'kapelas' => $kapelas,
            'kubs' => $kubs,
        ]);
    }

    /**
     * Process user registration.
     */
    public function processRegister(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'nullable|string|unique:users,username|max:50',
            'nik' => 'nullable|string|max:20',
            'no_hp' => 'nullable|string|max:20',
            'wilayah_id' => 'nullable|integer',
            'kapela_id' => 'nullable|integer',
            'kub_id' => 'nullable|integer',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.unique' => 'Alamat email ini sudah terdaftar di sistem.',
            'username.unique' => 'Username ini sudah digunakan.',
            'password.min' => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $umatRole = Role::where('slug', 'umat')
            ->orWhere('nama_role', 'like', '%umat%')
            ->first();

        $roleId = $umatRole->id ?? 5;
        $username = $request->username ?: strtolower(preg_replace('/[^a-zA-Z0-9]/', '', explode('@', $request->email)[0]) . rand(10, 99));

        $umatId = null;
        if (!empty($request->nik)) {
            $umatFound = DB::table('umat')->where('nik', $request->nik)->first();
            if ($umatFound) {
                $umatId = $umatFound->id;
            }
        }

        $user = User::create([
            'nama_lengkap' => $request->nama_lengkap,
            'email' => $request->email,
            'username' => $username,
            'no_hp' => $request->no_hp,
            'wilayah_id' => $request->wilayah_id,
            'kapela_id' => $request->kapela_id,
            'kub_id' => $request->kub_id,
            'umat_id' => $umatId,
            'password' => Hash::make($request->password),
            'role_id' => $roleId,
            'status' => 1,
        ]);

        Auth::login($user);

        return redirect('/v2/dashboard')->with('success', 'Pendaftaran akun jemaat berhasil! Selamat datang di SIPAROKI.');
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
