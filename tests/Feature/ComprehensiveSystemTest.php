<?php

namespace Tests\Feature;

use App\Models\Kapela;
use App\Models\KkKatolik;
use App\Models\Kub;
use App\Models\Role;
use App\Models\Umat;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ComprehensiveSystemTest extends TestCase
{
    private function createAdmin(): User
    {
        $role = Role::firstOrCreate(
            ['slug' => 'superadmin'],
            ['nama_role' => 'Super Administrator', 'status' => 1]
        );

        return User::firstOrCreate(
            ['username' => 'test_qa_superadmin'],
            [
                'nama_lengkap' => 'QA Super Administrator',
                'email' => 'qa_superadmin@siparoki.test',
                'password' => bcrypt('password123'),
                'role_id' => $role->id,
                'status' => 1,
            ]
        );
    }

    public function test_all_public_pages_render_without_500_errors(): void
    {
        $publicRoutes = [
            '/',
            '/profil',
            '/sejarah',
            '/visi-misi',
            '/sambutan',
            '/jadwal-misa',
            '/pelayan-pastoral',
            '/riwayat-pastor',
            '/agenda',
            '/warta',
            '/berita',
            '/artikel',
            '/pengumuman',
            '/renungan',
            '/galeri',
            '/video',
            '/statistik',
            '/kontak',
            '/downloads',
            '/sakramen',
            '/kapela',
        ];

        foreach ($publicRoutes as $route) {
            $response = $this->get($route);
            $this->assertTrue(
                in_array($response->status(), [200, 302]),
                "Route {$route} failed with status {$response->status()}"
            );
        }
    }

    public function test_kk_katolik_and_umat_crud_and_relational_protection(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        // 1. Create Territory
        $wilayah = Wilayah::create([
            'nama_wilayah' => 'Wilayah QA ' . uniqid(),
            'kode_wilayah' => 'W-QA-' . rand(100, 999),
            'status' => 'Aktif',
        ]);

        $kub = Kub::create([
            'wilayah_id' => $wilayah->id,
            'nama_kub' => 'KUB QA ' . uniqid(),
            'kode_kub' => 'KUB-QA-' . rand(100, 999),
            'status' => 1,
        ]);

        // 2. Create KK Katolik with Umat
        $noKk = '530' . rand(1000000000000, 9999999999999);
        $kk = KkKatolik::create([
            'no_kk_kw' => $noKk,
            'nama_lahir_pemilik' => 'Bapak QA Test',
            'nama_baptis_pemilik' => 'Petrus',
            'wilayah_id' => $wilayah->id,
            'kub_id' => $kub->id,
            'alamat_sekarang' => 'Jl. Uji Coba No. 123',
            'status_kk' => 'Aktif',
        ]);

        $this->assertDatabaseHas('kk_katolik', ['id' => $kk->id, 'no_kk_kw' => $noKk]);

        $nikUmat = '530' . rand(1000000000000, 9999999999999);
        $umat = Umat::create([
            'kk_id' => $kk->id,
            'nik' => $nikUmat,
            'nama_lengkap' => 'Umat QA Test',
            'nama_baptis' => 'Petrus',
            'nama_lahir' => 'QA Test',
            'jenis_kelamin' => 'Laki-laki',
            'tempat_lahir' => 'Kupang',
            'tanggal_lahir' => '1995-05-15',
            'status_perkawinan' => 'Menikah',
            'hubungan_keluarga' => 'Kepala Keluarga',
            'status_aktif' => 1,
        ]);

        $this->assertDatabaseHas('umat', ['id' => $umat->id, 'nik' => $nikUmat]);

        // 3. Relational delete protection: cannot delete KK when it has Umat
        $delKkRes = $this->delete("/superadmin/kk-katolik/{$kk->id}");
        $this->assertTrue(in_array($delKkRes->status(), [302, 400, 422]));
        // KK should still exist in database
        $this->assertDatabaseHas('kk_katolik', ['id' => $kk->id]);

        // 4. Update Umat
        $umat->update(['handphone' => '081234567890']);
        $this->assertDatabaseHas('umat', ['id' => $umat->id, 'handphone' => '081234567890']);

        // 5. Clean up
        $umat->delete();
        $this->assertDatabaseMissing('umat', ['id' => $umat->id]);

        $kk->delete();
        $this->assertDatabaseMissing('kk_katolik', ['id' => $kk->id]);

        $kub->delete();
        $wilayah->delete();
    }

    public function test_settings_hub_video_header_and_widgets_saving(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        $resVideo = $this->post('/superadmin/pengaturan/video/save', [
            'video_header_type' => 'youtube',
            'video_header_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            'video_header_title' => 'Gereja St. Vinsensius a Paulo Benlutu',
            'video_header_subtitle' => 'Paroki Mandiri dan Berbuah dalam Kasih',
            'video_header_status' => 'Aktif',
        ]);
        $this->assertTrue(in_array($resVideo->status(), [302, 200]));

        $resSeo = $this->post('/superadmin/pengaturan/seo/save', [
            'meta_title' => 'SIPAROKI Digital 2026',
            'meta_description' => 'Sistem Informasi Manajemen Paroki',
            'meta_keywords' => 'paroki, gereja, katolik, benlutu',
        ]);
        $this->assertTrue(in_array($resSeo->status(), [302, 200]));

        $resWidget = $this->post('/superadmin/pengaturan/widget/save', [
            'widget_jadwal_misa' => '1',
            'widget_renungan' => '1',
            'widget_statistik' => '1',
            'jam_operasional' => 'Senin - Sabtu: 08.00 - 14.00 WITA',
        ]);
        $this->assertTrue(in_array($resWidget->status(), [302, 200]));
    }

    public function test_generic_module_crud_endpoints(): void
    {
        $admin = $this->createAdmin();
        $this->actingAs($admin);

        // Test viewing major modules
        $modules = [
            'wilayah',
            'kub',
            'kapela',
            'direktori-dpp',
            'direktori-katekis',
            'direktori-misdinar',
            'master-pastor',
            'kronik',
            'jadwal-misa',
            'sakramen',
            'pengajuan-sakramen',
            'riwayat-mutasi-umat',
            'konten',
            'kategori-konten',
            'galeri',
            'pengumuman',
            'renungan',
            'keuangan',
            'aset',
        ];

        foreach ($modules as $mod) {
            $res = $this->get("/superadmin/{$mod}");
            $this->assertTrue(
                in_array($res->status(), [200, 302]),
                "Generic Module {$mod} failed with status {$res->status()}"
            );
        }
    }
}
