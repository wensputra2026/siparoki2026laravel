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

trait UserModuleTrait
{
    public function resetUserPassword(Request $request, $id)
    {
        $decodedId = decode_id($id) ?: $id;
        $user = \App\Models\User::findOrFail($decodedId);

        if (auth()->id() && (int) auth()->id() === (int) $user->getKey()) {
            return back()->with('error', 'Anda tidak dapat mereset password akun Anda sendiri dari sini.');
        }

        $password = $request->input('password') ?: 'SIPAROKI' . now()->format('Y');

        $user->forceFill([
            'password' => \Illuminate\Support\Facades\Hash::make($password),
            'updated_at' => now(),
        ])->save();

        $this->logAudit('RESET_PASSWORD', 'user', $user->getKey());

        return back()->with('success', "Password {$user->nama_lengkap} berhasil direset. Password baru: {$password}");
    }


    public function toggleUserStatus(Request $request, $id)
    {
        $decodedId = decode_id($id) ?: $id;
        $user = \App\Models\User::findOrFail($decodedId);
        if (auth()->id() && (int) auth()->id() === (int) $user->getKey()) {
            return back()->with('error', 'Akun yang sedang digunakan tidak dapat dinonaktifkan.');
        }

        $user->forceFill([
            'status' => $user->status ? 0 : 1,
            'updated_at' => now(),
        ])->save();

        return back()->with('success', 'Status pengguna berhasil diperbarui.');
    }


    protected function normalizeUserPayload(array $data, bool $isCreate): array
    {
        if (isset($data['handphone']) && !isset($data['no_hp'])) {
            $data['no_hp'] = $data['handphone'];
        }

        // Ensure email is never null/empty on create to prevent Integrity Constraint Violation
        if (empty($data['email']) || $data['email'] === 'null' || trim((string)$data['email']) === '') {
            if ($isCreate) {
                $baseEmail = !empty($data['username']) ? Str::slug($data['username']) : (!empty($data['nama_lengkap']) ? Str::slug($data['nama_lengkap']) : 'user');
                $genEmail = strtolower($baseEmail) . '@siparoki.local';
                $counter = 1;
                while (\App\Models\User::where('email', $genEmail)->exists()) {
                    $genEmail = strtolower($baseEmail) . $counter . '@siparoki.local';
                    $counter++;
                }
                $data['email'] = $genEmail;
            } else {
                unset($data['email']);
            }
        }

        // Ensure username is never null/empty on create
        if (empty($data['username']) || $data['username'] === 'null' || trim((string)$data['username']) === '') {
            if ($isCreate) {
                $baseUser = !empty($data['nama_lengkap']) ? Str::slug($data['nama_lengkap'], '') : 'user';
                $genUser = strtolower($baseUser);
                $counter = 1;
                while (\App\Models\User::where('username', $genUser)->exists()) {
                    $genUser = strtolower($baseUser) . $counter;
                    $counter++;
                }
                $data['username'] = $genUser;
            } else {
                unset($data['username']);
            }
        }

        // Compatibility between name and nama_lengkap
        if (empty($data['name']) && !empty($data['nama_lengkap'])) {
            $data['name'] = $data['nama_lengkap'];
        } elseif (empty($data['nama_lengkap']) && !empty($data['name'])) {
            $data['nama_lengkap'] = $data['name'];
        }

        if (array_key_exists('status', $data)) {
            $data['status'] = in_array((string) $data['status'], ['1', 'true', 'Aktif', 'aktif'], true) ? 1 : 0;
        } elseif ($isCreate) {
            $data['status'] = 1;
        }

        if (isset($data['maintenance_access'])) {
            $data['maintenance_access'] = in_array((string) $data['maintenance_access'], ['1', 'true', 'Ya', 'ya'], true) ? 'Ya' : 'Tidak';
        } elseif ($isCreate) {
            $data['maintenance_access'] = 'Tidak';
        }

        if (empty($data['password'])) {
            if ($isCreate) {
                $data['password'] = \Illuminate\Support\Facades\Hash::make('SIPAROKI' . now()->format('Y'));
            } else {
                unset($data['password']);
            }
        } else {
            $data['password'] = \Illuminate\Support\Facades\Hash::make($data['password']);
        }

        unset($data['handphone']);

        return $data;
    }


    public function createRole(Request $request): Response
    {
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? '';
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

        return Inertia::render('Inertia/RoleForm', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment ?: 'superadmin',
            'roleItem' => null,
            'isEdit' => false,
        ]);
    }


    public function editRole(Request $request, $id): Response
    {
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? '';
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

        $roleItem = \App\Models\Role::whereUuidOrId($id)
            ->orWhere('slug', $id)
            ->first();

        if (!$roleItem && is_numeric($decodedId)) {
            $roleItem = \App\Models\Role::find($decodedId);
        }

        if (!$roleItem) {
            $roleItem = \App\Models\Role::first();
        }

        return Inertia::render('Inertia/RoleForm', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment ?: 'superadmin',
            'roleItem' => $roleItem,
            'isEdit' => true,
        ]);
    }


    public function panduanHakAkses(Request $request): Response
    {
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? '';
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

        return Inertia::render('Inertia/PanduanHakAkses', [
            'role' => $resolvedRole,
        ]);
    }

}
