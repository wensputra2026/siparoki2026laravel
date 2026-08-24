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
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    /**
     * Show modern Inertia Vue 3 Login page.
     */
    public function showLogin(): Response
    {
        return Inertia::render('Auth/Login', [
            'status' => session('status'),
        ]);
    }

    /**
     * Process user login authentication.
     */
    public function processLogin(Request $request)
    {
        $credentials = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ], [
            'login.required' => 'Email atau Username wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $login = $request->input('login');
        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $remember = $request->boolean('remember');

        if (Auth::attempt([$field => $login, 'password' => $request->password], $remember) ||
            ($field === 'email' && Auth::attempt(['username' => $login, 'password' => $request->password], $remember))) {
            $request->session()->regenerate();

            $user = Auth::user();
            $roleSlug = strtolower($user->role->slug ?? $user->role->nama_role ?? '');

            if (str_contains($roleSlug, 'super')) {
                return redirect('/superadmin')->with('success', 'Selamat datang kembali, Super Admin!');
            } elseif (str_contains($roleSlug, 'paroki') || str_contains($roleSlug, 'pastor')) {
                return redirect('/paroki')->with('success', 'Selamat datang di Panel Paroki!');
            } elseif (str_contains($roleSlug, 'wilayah')) {
                return redirect('/wilayah')->with('success', 'Selamat datang di Panel Admin Wilayah!');
            } elseif (str_contains($roleSlug, 'kapela') || str_contains($roleSlug, 'stasi')) {
                return redirect('/kapela')->with('success', 'Selamat datang di Panel Admin Kapela!');
            } elseif (str_contains($roleSlug, 'kub')) {
                return redirect('/kub')->with('success', 'Selamat datang di Panel Pengurus KUB!');
            } elseif (str_contains($roleSlug, 'bendahara')) {
                return redirect('/bendahara')->with('success', 'Selamat datang di Panel Bendahara!');
            } elseif (str_contains($roleSlug, 'penulis') || str_contains($roleSlug, 'komsos')) {
                return redirect('/penulis')->with('success', 'Selamat datang di Panel Redaksi / Komsos!');
            }

            return redirect('/v2/dashboard')->with('success', 'Selamat datang kembali di SIPAROKI!');
        }

        return back()->withErrors([
            'login' => 'Email, Username, atau Kata Sandi yang Anda masukkan salah.',
        ])->onlyInput('login');
    }

    /**
     * Show modern Inertia Vue 3 Register page.
     */
    public function showRegister(): Response
    {
        $wilayahs = collect();
        $lingkungans = collect();
        $kubs = collect();

        try {
            $wilayahs = DB::table('wilayah')->orderBy('nama_wilayah')->get(['id', 'nama_wilayah', 'kode_wilayah']);
        } catch (\Throwable $e) {}

        try {
            $lingkungans = DB::table('lingkungan')->orderBy('nama_lingkungan')->get(['id', 'nama_lingkungan', 'wilayah_id']);
        } catch (\Throwable $e) {}

        try {
            $kubs = DB::table('kub')->orderBy('nama_kub')->get(['id', 'nama_kub', 'lingkungan_id']);
        } catch (\Throwable $e) {}

        return Inertia::render('Auth/Register', [
            'wilayahs' => $wilayahs,
            'lingkungans' => $lingkungans,
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
            'lingkungan_id' => 'nullable|integer',
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
            'lingkungan_id' => $request->lingkungan_id,
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
     * Show modern Inertia Vue 3 Forgot Password page.
     */
    public function showForgotPassword(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
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

        if (!$user) {
            return back()->withErrors(['identitas' => 'Akun dengan data tersebut tidak ditemukan dalam sistem paroki.'])->withInput();
        }

        return back()->with('status', 'Permintaan reset kata sandi telah dicatat. Silakan hubungi Sekretariat Paroki atau periksa pesan WhatsApp/Email untuk instruksi pemulihan akun.');
    }

    /**
     * Logout authenticated user.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('status', 'Anda telah berhasil keluar dari sistem.');
    }
}
