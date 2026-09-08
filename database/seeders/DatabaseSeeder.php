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
        // 1. Seed Roles (Level Umat ditiadakan, umat dilayani via cek NIK mandiri)
        $roles = [
            ['id' => 1, 'nama_role' => 'Super Admin', 'slug' => 'super_admin', 'deskripsi' => 'Administrator Utama Sistem Informasi Paroki (Akses Penuh)', 'level_akses' => 1],
            ['id' => 2, 'nama_role' => 'Admin Paroki', 'slug' => 'admin_paroki', 'deskripsi' => 'Sekretariat & Tata Usaha Kantor Paroki', 'level_akses' => 2],
            ['id' => 3, 'nama_role' => 'Pastor', 'slug' => 'pastor', 'deskripsi' => 'Pastor Paroki & Rekan / Dewan Pastoral Paroki', 'level_akses' => 3],
            ['id' => 4, 'nama_role' => 'Admin Wilayah', 'slug' => 'admin_wilayah', 'deskripsi' => 'Koordinator & Pengurus Wilayah Rohani', 'level_akses' => 4],
            ['id' => 5, 'nama_role' => 'Admin Kapela / Stasi', 'slug' => 'admin_kapela', 'deskripsi' => 'Pengurus Stasi / Kapela Lingkungan', 'level_akses' => 5],
            ['id' => 6, 'nama_role' => 'Ketua KUB', 'slug' => 'ketua_kub', 'deskripsi' => 'Ketua & Pengurus Komunitas Umat Basis (KUB)', 'level_akses' => 6],
            ['id' => 8, 'nama_role' => 'Penulis', 'slug' => 'penulis', 'deskripsi' => 'Kontributor Berita, Renungan, Warta & Artikel Paroki', 'level_akses' => 8],
            ['id' => 9, 'nama_role' => 'Bendahara', 'slug' => 'bendahara', 'deskripsi' => 'Pengelola Keuangan, Iuran & Kolekte Paroki', 'level_akses' => 9],
        ];

        if (Schema::hasTable('roles')) {
            // Bersihkan duplikat role superadmin 62 dan role umat jika ada
            if (DB::table('roles')->where('id', 62)->exists()) {
                DB::table('users')->where('role_id', 62)->update(['role_id' => 1]);
                DB::table('roles')->where('id', 62)->delete();
            }
            if (DB::table('roles')->where('id', 7)->orWhere('slug', 'umat')->exists()) {
                DB::table('users')->where('role_id', 7)->delete();
                DB::table('roles')->where('id', 7)->orWhere('slug', 'umat')->delete();
            }

            foreach ($roles as $r) {
                $id = $r['id'];
                if (!Schema::hasColumn('roles', 'level_akses')) {
                    unset($r['level_akses']);
                }
                $exists = DB::table('roles')->where('id', $id)->first();
                if ($exists) {
                    DB::table('roles')->where('id', $id)->update($r);
                } else {
                    DB::table('roles')->insert($r);
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

        // 3. Seed Default Profil Paroki & Pengaturan Aplikasi
        $targetParokiId = env('PAROKI_ID', null);
        $targetNamaParoki = env('NAMA_PAROKI', null);

        $selectedParoki = null;
        if (Schema::hasTable('paroki')) {
            if ($targetParokiId) {
                $selectedParoki = DB::table('paroki')->where('id_paroki', $targetParokiId)->first();
            } elseif ($targetNamaParoki) {
                $selectedParoki = DB::table('paroki')->where('nama_paroki', 'like', "%{$targetNamaParoki}%")->first();
            }
            if (!$selectedParoki) {
                $selectedParoki = DB::table('paroki')->where('id_paroki', 380)->first()
                    ?? DB::table('paroki')->where('nama_paroki', 'like', '%Benlutu%')->first()
                    ?? DB::table('paroki')->first();
            }
        }

        $namaParoki = $selectedParoki?->nama_paroki ?? 'St. Vinsensius a Paulo - Benlutu';
        $parokiId = $selectedParoki?->id_paroki ?? 380;
        $keuskupanId = $selectedParoki?->keuskupan_id ?? 5;
        $dekenatId = $selectedParoki?->dekenat_id ?? 14;
        $alamatParoki = $selectedParoki?->alamat ?: 'Benlutu, Kec. Batu Putih, Kab. Timor Tengah Selatan, NTT';
        $pastorParoki = $selectedParoki?->nama_pastor_paroki_aktif ?? 'RD. Herman Hilers Penga';

        if (Schema::hasTable('profil_paroki')) {
            $parokiData = [
                'nama_paroki' => $namaParoki,
                'keuskupan' => 'Keuskupan Agung Kupang',
                'alamat' => $alamatParoki,
                'telepon' => '081234567890',
                'email' => 'parokibenlutu@gmail.com',
                'pastor_paroki' => $pastorParoki,
                'sejarah' => 'Paroki St. Vinsensius a Paulo Benlutu didirikan untuk melayani umat beriman di wilayah Benlutu dan sekitarnya.',
                'visi' => 'Menjadi persekutuan umat beriman yang mandiri, misioner, dan berakar dalam Kristus.',
                'misi' => "1. Meningkatkan kualitas peribadatan dan penghayatan sakramen.\n2. Membangun solidaritas sosial antarumat dan masyarakat.",
                'updated_at' => now(),
            ];
            $cols = Schema::getColumnListing('profil_paroki');
            if (in_array('paroki_id', $cols, true)) $parokiData['paroki_id'] = $parokiId;
            if (in_array('keuskupan_id', $cols, true)) $parokiData['keuskupan_id'] = $keuskupanId;
            if (in_array('dekenat_id', $cols, true)) $parokiData['dekenat_id'] = $dekenatId;
            if (in_array('pelindung', $cols, true)) $parokiData['pelindung'] = 'St. Vinsensius a Paulo';

            $filtered = array_intersect_key($parokiData, array_flip($cols));
            if (DB::table('profil_paroki')->count() > 0) {
                DB::table('profil_paroki')->update($filtered);
            } else {
                $filtered['created_at'] = now();
                DB::table('profil_paroki')->insert($filtered);
            }
        }

        if (Schema::hasTable('pengaturan_aplikasi')) {
            $appCols = Schema::getColumnListing('pengaturan_aplikasi');
            $appData = [
                'nama_aplikasi' => 'SIPAROKI',
                'nama_paroki' => $namaParoki,
                'nama_keuskupan' => 'Keuskupan Agung Kupang',
                'pelindung_paroki' => 'St. Vinsensius a Paulo',
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
            DB::table('pengaturan')->updateOrInsert(['kunci' => 'keuskupan'], ['nilai' => 'Keuskupan Agung Kupang', 'updated_at' => now()]);
        }

        if (Schema::hasTable('identitas_paroki')) {
            $idCols = Schema::getColumnListing('identitas_paroki');
            $idData = [];
            if (in_array('nama_paroki', $idCols, true)) $idData['nama_paroki'] = $namaParoki;
            if (in_array('paroki_id', $idCols, true)) $idData['paroki_id'] = $parokiId;
            if (in_array('id_paroki', $idCols, true)) $idData['id_paroki'] = $parokiId;
            if (in_array('nama_keuskupan', $idCols, true)) $idData['nama_keuskupan'] = 'Keuskupan Agung Kupang';
            if (in_array('keuskupan', $idCols, true)) $idData['keuskupan'] = 'Keuskupan Agung Kupang';
            if (in_array('keuskupan_id', $idCols, true)) $idData['keuskupan_id'] = $keuskupanId;
            if (in_array('dekenat_id', $idCols, true)) $idData['dekenat_id'] = $dekenatId;
            if (in_array('nama_dekenat', $idCols, true)) $idData['nama_dekenat'] = 'Kevikepan/Dekenat TTS';
            if (in_array('pelindung', $idCols, true)) $idData['pelindung'] = 'St. Vinsensius a Paulo';
            if (in_array('pelindung_paroki', $idCols, true)) $idData['pelindung_paroki'] = 'St. Vinsensius a Paulo';
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

        // 4. Seed Master Keuskupan & Dekenat
        if (class_exists(KeuskupanSeeder::class)) {
            $this->call(KeuskupanSeeder::class);
        }
        if (class_exists(DekenatSeeder::class)) {
            $this->call(DekenatSeeder::class);
        }

        // 5. Seed Master Paroki & Kuasi Paroki
        if (class_exists(ParokiSeeder::class)) {
            $this->call(ParokiSeeder::class);
        }
        if (class_exists(KuasiParokiSeeder::class)) {
            $this->call(KuasiParokiSeeder::class);
        }

        // 6. Seed Master Pastor & Foto Pastor
        if (class_exists(MasterPastorSeeder::class)) {
            $this->call(MasterPastorSeeder::class);
        }

        // 7. Seed Master Referensi & Kategori
        if (class_exists(MasterReferensiSeeder::class)) {
            $this->call(MasterReferensiSeeder::class);
        }

        // 8. Seed Jenis Iuran
        if (class_exists(JenisIuranSeeder::class)) {
            $this->call(JenisIuranSeeder::class);
        }

                // 8b. Seed Default Security Settings (Captcha & Proteksi Keamanan Langsung Aktif Saat Instalasi Baru)
        if (Schema::hasTable('security_settings')) {
            $defaultSecuritySettings = [
                'captcha_enabled' => '1',
                'captcha_provider' => 'Simple CAPTCHA',
                'captcha_site_key' => '',
                'captcha_secret_key' => '',
                'captcha_show_after_failed_attempts' => '0',
                'captcha_required_backend_login' => '1',
                'captcha_required_umat_login' => '1',
                'captcha_required_forgot_password' => '1',
                'captcha_required_public_forms' => '1',
                'max_login_attempts' => '5',
                'lockout_minutes' => '60',
                'session_timeout_minutes' => '120',
                'force_strong_password' => '1',
                'enable_brute_force_protection' => '1',
            ];

            foreach ($defaultSecuritySettings as $k => $v) {
                DB::table('security_settings')->updateOrInsert(
                    ['setting_key' => $k],
                    ['setting_value' => $v, 'updated_at' => now()]
                );
            }
        }

        // 9. Pastikan tabel teritori pastoral, umat, konten, media & operasional paroki 100% bersih/kosong saat instalasi awal
        $localTables = [
            'riwayat_mutasi_umat',
            'mutasi_umat',
            'sakramen',
            'sakramen_margo',
            'sakramen_umat',
            'sakramen_verifikasi',
            'pengajuan_sakramen',
            'anggota_keluarga',
            'umat',
            'umats',
            'kk_katolik',
            'kub',
            'kubs',
            'lingkungan',
            'wilayah',
            'wilayahs',
            'kapela',
            'stasi_kapela',
            'master_kapela',
            'konten',
            'artikel',
            'berita',
            'komentar_artikel',
            'galeri',
            'galeri_album',
            'galeri_item',
            'video',
            'pengumuman',
            'arsip_digital',
            'iuran',
            'transaksi_pembayaran',
            'kas_rekening',
            'keuangan',
            'kolekte',
            'kegiatan',
            'rapat',
            'rapat_peserta',
            'chat_pesan',
            'aset',
            'aset_maintenance',
            'intensi_misa',
            'misa_kapela',
            'misa_pastor',
            'jadwal_misa',
            'jadwal_petugas_liturgi',
            'log_aktivitas',
            'login_activity',
            'login_attempts',
            'security_logs',
            'backup_database',
        ];

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
        } catch (\Throwable $e) {}

        foreach ($localTables as $lt) {
            if (Schema::hasTable($lt)) {
                try {
                    DB::table($lt)->truncate();
                } catch (\Throwable $e) {
                    try {
                        DB::table($lt)->delete();
                    } catch (\Throwable $ex) {}
                }
            }
        }

        // Bersihkan seluruh user selain Super Admin (role_id = 1)
        if (Schema::hasTable('users')) {
            try {
                DB::table('users')
                    ->where('role_id', '!=', 1)
                    ->delete();
            } catch (\Throwable $e) {}
        }

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
        } catch (\Throwable $e) {}

        // 10. Create installed lock to mark ready
        @file_put_contents(storage_path('installed.lock'), date('Y-m-d H:i:s'));
        @file_put_contents(storage_path('installed'), date('Y-m-d H:i:s'));
    }
}
