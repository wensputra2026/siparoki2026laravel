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

trait ProfileModuleTrait
{
    public function profilParoki(Request $request): Response
    {
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

        $allParokis = Paroki::with(['keuskupan', 'dekenat'])
            ->orderBy('nama_paroki')
            ->get(['id_paroki', 'nama_paroki', 'kode_paroki', 'keuskupan_id', 'dekenat_id', 'status_paroki', 'status']);

        $selectedParokiId = $request->query('paroki_id')
            ?? $request->session()->get('default_paroki_id')
            ?? (Paroki::where('nama_paroki', 'like', '%Benlutu%')->value('id_paroki') ?? Paroki::value('id_paroki'));

        if ($request->query('set_default') && $request->query('paroki_id')) {
            $request->session()->put('default_paroki_id', (int) $request->query('paroki_id'));
            $selectedParokiId = (int) $request->query('paroki_id');

            // Safe auto-sync selected default paroki to global settings
            $chosen = Paroki::find($selectedParokiId);
            $this->syncParokiToGlobalSettings($chosen);
        }

        $paroki = Paroki::with(['keuskupan', 'dekenat', 'provinsi', 'kabupaten', 'kecamatan', 'desa'])->find($selectedParokiId)
            ?? Paroki::with(['keuskupan', 'dekenat', 'provinsi', 'kabupaten', 'kecamatan', 'desa'])->first()
            ?? new Paroki([
                'nama_paroki' => 'Paroki St. Vincentius a Paulo Benlutu',
                'kode_paroki' => 'PRK-BNL-001',
                'pelindung_paroki' => 'Santo Vincentius a Paulo',
                'alamat' => 'Benlutu, Kec. Batu Putih, Kab. Timor Tengah Selatan, NTT',
                'nama_pastor_paroki_aktif' => 'RD. Krispinus Saku',
                'telepon' => '(0380) 123456',
                'whatsapp' => '081234567890',
                'email' => 'sekretariat@parokibenlutu.org',
                'keterangan' => 'Paroki Santo Vincentius a Paulo Benlutu melayani umat beriman dengan penuh dedikasi dan kasih Kristiani.',
            ]);

        $keuskupanList = \Illuminate\Support\Facades\Cache::remember('ref_keuskupan_list', 3600, function() {
            return Keuskupan::orderBy('nama_keuskupan')->get(['id_keuskupan', 'nama_keuskupan']);
        });

        $dekenatList = \Illuminate\Support\Facades\Cache::remember('ref_dekenat_list_v4', 3600, function() {
            return Dekenat::all();
        });

        $provinsiList = \Illuminate\Support\Facades\Cache::remember('ref_provinsi_list', 3600, function() {
            return Provinsi::orderBy('nama_provinsi')->get(['id_provinsi', 'nama_provinsi']);
        });

        $kabupatenList = \Illuminate\Support\Facades\Cache::remember('ref_kabupaten_list', 3600, function() {
            return Kabupaten::orderBy('nama_kabupaten')->get(['id_kabupaten', 'provinsi_id', 'nama_kabupaten']);
        });

        $kecamatanList = \Illuminate\Support\Facades\Cache::remember('ref_kecamatan_list', 3600, function() {
            return Kecamatan::orderBy('nama_kecamatan')->get(['id_kecamatan', 'kabupaten_id', 'nama_kecamatan']);
        });

        $desaList = \Illuminate\Support\Facades\Cache::remember('ref_desa_scoped_ntt', 3600, function() {
            return DesaKelurahan::whereIn('kecamatan_id', function($q) {
                $q->select('id_kecamatan')->from('kecamatan')->whereIn('kabupaten_id', function($q2) {
                    $q2->select('id_kabupaten')->from('kabupaten')->where('provinsi_id', 53);
                });
            })->orderBy('nama_desa')->get(['id_desa', 'kecamatan_id', 'nama_desa']);
        });

        $pastors = \Illuminate\Support\Facades\Schema::hasTable('master_pastor')
            ? \App\Models\MasterPastor::orderBy('urutan')->orderBy('nama_pastor')->get()->map(function($p) {
                $p->nama_formatted = \App\Models\MasterPastor::formatNama($p);
                return $p;
            })
            : [];

        $totalUmat = \Illuminate\Support\Facades\Schema::hasTable('umat') ? \App\Models\Umat::count() : 1850;
        $totalKk = \Illuminate\Support\Facades\Schema::hasTable('keluarga') ? \App\Models\Keluarga::count() : 420;

        return Inertia::render('Inertia/ProfilParoki', [
            'paroki' => $paroki,
            'totalWilayah' => Wilayah::count(),
            'totalLingkungan' => Lingkungan::count(),
            'totalKapela' => Kapela::count(),
            'totalKub' => \Illuminate\Support\Facades\Schema::hasTable('kub') ? Kub::count() : 0,
            'totalUmat' => $totalUmat,
            'totalKk' => $totalKk,
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
            'allParokis' => $allParokis,
            'defaultParokiId' => $selectedParokiId,
            'keuskupanList' => $keuskupanList,
            'dekenatList' => $dekenatList,
            'provinsiList' => $provinsiList,
            'kabupatenList' => $kabupatenList,
            'kecamatanList' => $kecamatanList,
            'desaList' => $desaList,
            'pastors' => $pastors,
        ]);
    }


    public function profilSaya(Request $request): Response
    {
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

        $user = auth()->user();
        if ($user) {
            $user->loadMissing(['role', 'wilayah', 'kapela', 'kub', 'umat']);
        } else {
            $user = \App\Models\User::with(['role', 'wilayah', 'kapela', 'kub', 'umat'])->first()
                ?? new \App\Models\User([
                    'nama_lengkap' => 'Administrator Paroki',
                    'username' => 'superadmin',
                    'email' => 'admin@parokibenlutu.org',
                    'no_hp' => '081234567890',
                    'status' => 1,
                ]);
        }

        return Inertia::render('Inertia/ProfilSaya', [
            'user' => $user,
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
        ]);
    }


    public function updateProfilSaya(Request $request)
    {
        $user = auth()->user() ?? \App\Models\User::first();
        if (!$user) {
            return back()->with('error', 'User tidak ditemukan.');
        }

        $userId = $user->getKey();
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:150',
            'username' => 'required|string|max:100|unique:users,username,' . $userId,
            'email' => 'required|email|max:150|unique:users,email,' . $userId,
            'no_hp' => 'nullable|string|max:30',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png,webp,gif|max:2048',
        ]);

        if ($request->hasFile('foto')) {
            $savedPhoto = \App\Services\ImageOptimizer::optimizeAndSave(
                $request->file('foto'),
                'uploads/users',
                600,
                600,
                85
            );

            $validated['foto'] = ltrim($savedPhoto, '/');
        }

        // Introspect users table columns to safely update existing schema
        $cols = \Illuminate\Support\Facades\Schema::getColumnListing('users');
        $payload = [];
        if (in_array('nama_lengkap', $cols, true)) {
            $payload['nama_lengkap'] = $validated['nama_lengkap'];
        }
        if (in_array('name', $cols, true)) {
            $payload['name'] = $validated['nama_lengkap'];
        }
        if (in_array('username', $cols, true)) {
            $payload['username'] = $validated['username'];
        }
        if (in_array('email', $cols, true)) {
            $payload['email'] = $validated['email'];
        }
        if (in_array('no_hp', $cols, true)) {
            $payload['no_hp'] = $validated['no_hp'] ?? null;
        }
        if (isset($validated['foto']) && in_array('foto', $cols, true)) {
            $payload['foto'] = $validated['foto'];
        }

        $user->update($payload);

        return back()->with('success', 'Profil dan foto Anda berhasil diperbarui.');
    }


    public function updatePasswordProfilSaya(Request $request)
    {
        $user = auth()->user() ?? \App\Models\User::first();
        if (!$user) {
            return back()->with('error', 'User tidak ditemukan.');
        }

        $request->validate([
            'current_password' => 'nullable|string',
            'new_password' => 'required|string|min:6|confirmed',
        ], [
            'new_password.required' => 'Kata sandi baru wajib diisi.',
            'new_password.min' => 'Kata sandi minimal 6 karakter.',
            'new_password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        if ($request->filled('current_password')) {
            if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Kata sandi saat ini salah.']);
            }
        }

        $user->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->new_password),
        ]);

        return back()->with('success', 'Kata sandi Anda berhasil diperbarui.');
    }

}
