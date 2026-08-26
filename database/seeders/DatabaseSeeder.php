<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\ProfilParoki;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Roles
        $roles = [
            ['id' => 1, 'nama_role' => 'Super Admin', 'slug' => 'superadmin', 'deskripsi' => 'Administrator Utama Sistem Informasi Paroki (Akses Penuh)', 'level_akses' => 1],
            ['id' => 2, 'nama_role' => 'Pastor', 'slug' => 'pastor', 'deskripsi' => 'Pastor Paroki & Rekan / Dewan Pastoral Paroki', 'level_akses' => 2],
            ['id' => 3, 'nama_role' => 'Admin Paroki', 'slug' => 'paroki', 'deskripsi' => 'Sekretariat & Tata Usaha Kantor Paroki', 'level_akses' => 3],
            ['id' => 4, 'nama_role' => 'Bendahara', 'slug' => 'bendahara', 'deskripsi' => 'Pengelola Keuangan, Iuran & Kolekte Paroki', 'level_akses' => 4],
            ['id' => 5, 'nama_role' => 'Admin Wilayah', 'slug' => 'wilayah', 'deskripsi' => 'Koordinator & Pengurus Wilayah Rohani', 'level_akses' => 5],
            ['id' => 6, 'nama_role' => 'Admin Kapela / Stasi', 'slug' => 'kapela', 'deskripsi' => 'Pengurus Stasi / Kapela Lingkungan', 'level_akses' => 6],
            ['id' => 7, 'nama_role' => 'Ketua KUB', 'slug' => 'kub', 'deskripsi' => 'Ketua & Pengurus Komunitas Umat Basis (KUB)', 'level_akses' => 7],
            ['id' => 8, 'nama_role' => 'Penulis', 'slug' => 'penulis', 'deskripsi' => 'Kontributor Berita, Renungan, Warta & Artikel Paroki', 'level_akses' => 8],
            ['id' => 9, 'nama_role' => 'Umat', 'slug' => 'umat', 'deskripsi' => 'Warga Jemaat / Umat Paroki', 'level_akses' => 9],
        ];

        if (Schema::hasTable('roles')) {
            foreach ($roles as $r) {
                if (Schema::hasColumn('roles', 'level_akses')) {
                    DB::table('roles')->updateOrInsert(['id' => $r['id']], $r);
                } else {
                    unset($r['level_akses']);
                    DB::table('roles')->updateOrInsert(['id' => $r['id']], $r);
                }
            }
        }

        // 2. Seed Default Super Admin User
        if (Schema::hasTable('users')) {
            $adminData = [
                'name' => 'Super Administrator',
                'email' => 'superadmin@paroki.org',
                'password' => Hash::make('Admin@Paroki2026!'),
                'role_id' => 1,
                'nama_lengkap' => 'Super Administrator SIPAROKI',
                'status_aktif' => 1,
                'status_user' => 'Aktif',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $userCols = Schema::getColumnListing('users');
            $filteredAdmin = array_intersect_key($adminData, array_flip($userCols));

            DB::table('users')->updateOrInsert(['email' => 'superadmin@paroki.org'], $filteredAdmin);
        }

        // 3. Seed Default Profil Paroki
        if (Schema::hasTable('profil_paroki')) {
            $count = DB::table('profil_paroki')->count();
            if ($count === 0) {
                $parokiData = [
                    'nama_paroki' => 'Paroki St. Vinsensius a Paulo - Benlutu',
                    'keuskupan' => 'Keuskupan Agung Kupang',
                    'dekenat' => 'Dekenat Kota Kupang',
                    'alamat' => 'Jl. Timor Raya Km. 28, Benlutu, Nusa Tenggara Timur',
                    'telepon' => '081234567890',
                    'email' => 'parokibenlutu@gmail.com',
                    'sejarah' => 'Paroki St. Vinsensius a Paulo Benlutu didirikan untuk melayani umat beriman di wilayah Benlutu dan sekitarnya.',
                    'visi' => 'Menjadi persekutuan umat beriman yang mandiri, misioner, dan berakar dalam Kristus.',
                    'misi' => '1. Meningkatkan kualitas peribadatan dan penghayatan sakramen.\n2. Membangun solidaritas sosial antarumat dan masyarakat.',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
                // Filter only columns that exist
                $cols = Schema::getColumnListing('profil_paroki');
                $filtered = array_intersect_key($parokiData, array_flip($cols));
                DB::table('profil_paroki')->insert($filtered);
            }
        }

        // 4. Seed Master Keuskupan & Paroki jika file JSON tersedia
        $jsonPath = database_path('data/master_keuskupan_paroki.json');
        if (File::exists($jsonPath) && Schema::hasTable('keuskupan')) {
            try {
                $data = json_decode(File::get($jsonPath), true);
                if (!empty($data['keuskupan'])) {
                    foreach ($data['keuskupan'] as $k) {
                        DB::table('keuskupan')->updateOrInsert(
                            ['id' => $k['id']],
                            ['nama_keuskupan' => $k['nama_keuskupan'], 'region' => $k['region'] ?? 'Indonesia', 'created_at' => now(), 'updated_at' => now()]
                        );
                    }
                }
            } catch (\Throwable $e) {}
        }

        // 5. Seed Jenis Iuran
        if (class_exists(JenisIuranSeeder::class)) {
            $this->call(JenisIuranSeeder::class);
        }

        // 6. Create installed lock to mark ready
        @file_put_contents(storage_path('installed.lock'), date('Y-m-d H:i:s'));
        @file_put_contents(storage_path('installed'), date('Y-m-d H:i:s'));
    }
}
