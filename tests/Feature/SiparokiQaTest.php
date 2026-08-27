<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\DB;

class SiparokiQaTest extends TestCase
{
    protected function admin(): User
    {
        return User::where('email', 'superadmin@paroki.org')->firstOrFail();
    }

    protected function lowPrivUser(): ?User
    {
        $row = DB::table('users as u')
            ->join('roles as r', 'r.id', '=', 'u.role_id')
            ->where('u.role_id', '!=', 1)
            ->whereNotNull('r.slug')
            ->where(function ($q) {
                $q->where('r.slug', 'like', '%wilayah%')
                  ->orWhere('r.slug', 'like', '%kub%')
                  ->orWhere('r.slug', 'like', '%umat%')
                  ->orWhere('r.slug', 'like', '%bendahara%')
                  ->orWhere('r.slug', 'like', '%penulis%')
                  ->orWhere('r.slug', 'like', '%kapela%');
            })
            ->select('u.id')
            ->first();
        return $row ? User::find($row->id) : null;
    }

    public function test_anon_redirect_to_login(): void
    {
        $resp = $this->get('/superadmin/umat/7/mutasi');
        $resp->assertRedirect('/login');
    }

    public function test_module_pages_load(): void
    {
        $this->actingAs($this->admin());
        $this->get('/superadmin/wilayah')->assertStatus(200);
        $this->get('/superadmin/kub')->assertStatus(200);
        $this->get('/superadmin/kk-katolik')->assertStatus(200);
    }

    public function test_mutasi_kub_flow(): void
    {
        $this->actingAs($this->admin());
        $orig = DB::table('umat')->where('id', 7)->first();
        $origKk = $orig->kk_id;

        $this->get('/superadmin/umat/7/mutasi')->assertStatus(200);

        $kkTujuan = DB::table('kk_katolik')
            ->where('kub_id', '!=', function ($q) use ($origKk) {
                return $q->select('kub_id')->from('kk_katolik')->where('id', $origKk);
            })
            ->whereNotNull('kub_id')
            ->first();

        $resp = $this->post('/superadmin/umat/7/mutasi', [
            'kk_tujuan_id' => $kkTujuan->id,
            'alasan' => 'QA mutasi test',
            'tgl_mutasi' => now()->toDateString(),
        ]);
        $resp->assertRedirect();

        $this->assertDatabaseHas('riwayat_mutasi_umat', ['umat_id' => 7, 'jenis_mutasi' => 'Mutasi KUB']);
        $this->assertEquals($kkTujuan->id, DB::table('umat')->where('id', 7)->value('kk_id'));

        // cleanup
        DB::table('riwayat_mutasi_umat')->where('umat_id', 7)->where('jenis_mutasi', 'Mutasi KUB')->delete();
        DB::table('umat')->where('id', 7)->update([
            'kk_id' => $origKk,
            'kk_sebelumnya_id' => $orig->kk_sebelumnya_id,
            'tanggal_keluar_dari_kk' => $orig->tanggal_keluar_dari_kk,
            'tanggal_menjadi_anggota_kk' => $orig->tanggal_menjadi_anggota_kk,
        ]);
    }

    public function test_pisah_kk_flow(): void
    {
        $this->actingAs($this->admin());
        $orig = DB::table('umat')->where('id', 7)->first();
        $origKk = $orig->kk_id;

        $this->get('/superadmin/umat/7/pisah-kk')->assertStatus(200);

        $kubTujuan = DB::table('kub')
            ->where('id', '!=', function ($q) use ($origKk) {
                return $q->select('kub_id')->from('kk_katolik')->where('id', $origKk);
            })
            ->first();

        $noKk = 'QA-PISAH-' . time();
        $resp = $this->post('/superadmin/umat/7/pisah-kk', [
            'kub_tujuan_id' => $kubTujuan->id,
            'no_kk_kw' => $noKk,
            'nama_pemilik_kk' => $orig->nama_lengkap,
            'tgl_perkawinan' => now()->toDateString(),
            'nama_pasangan' => 'QA Pasangan',
            'alasan' => 'QA pisah test',
        ]);
        $resp->assertRedirect();

        $kkBaru = DB::table('kk_katolik')->where('no_kk_kw', $noKk)->first();
        $this->assertNotNull($kkBaru);
        $this->assertDatabaseHas('riwayat_mutasi_umat', ['umat_id' => 7, 'jenis_mutasi' => 'Pisah KK (Menikah)']);
        $this->assertEquals('Menikah', DB::table('umat')->where('id', 7)->value('status_perkawinan'));

        // cleanup
        DB::table('riwayat_mutasi_umat')->where('umat_id', 7)->where('jenis_mutasi', 'Pisah KK (Menikah)')->delete();
        if ($kkBaru) { DB::table('kk_katolik')->where('id', $kkBaru->id)->delete(); }
        DB::table('umat')->where('id', 7)->update([
            'kk_id' => $origKk,
            'kk_sebelumnya_id' => $orig->kk_sebelumnya_id,
            'status_perkawinan' => $orig->status_perkawinan,
            'status_menikah' => $orig->status_menikah,
            'tgl_perkawinan' => $orig->tgl_perkawinan,
            'nama_pasangan' => $orig->nama_pasangan,
            'tanggal_keluar_dari_kk' => $orig->tanggal_keluar_dari_kk,
            'tanggal_menjadi_anggota_kk' => $orig->tanggal_menjadi_anggota_kk,
        ]);
    }

    public function test_tambah_riwayat_manual(): void
    {
        $this->actingAs($this->admin());
        $this->get('/superadmin/riwayat-mutasi/tambah')->assertStatus(200);

        $kubAsal = DB::table('kk_katolik')->where('id', DB::table('umat')->where('id', 7)->value('kk_id'))->value('kub_id');
        $kubTujuan = DB::table('kub')->where('id', '!=', $kubAsal)->first();
        $wilAsal = DB::table('wilayah')->first()->id;
        $wilTuj = DB::table('wilayah')->skip(1)->first()->id;

        $resp = $this->post('/superadmin/riwayat-mutasi/tambah', [
            'umat_id' => 7,
            'jenis_mutasi' => 'Pindah Wilayah',
            'kub_asal_id' => $kubAsal,
            'kub_tujuan_id' => $kubTujuan->id,
            'wilayah_asal_id' => $wilAsal,
            'wilayah_tujuan_id' => $wilTuj,
            'tgl_mutasi' => now()->toDateString(),
            'alasan' => 'QA manual',
        ]);
        $resp->assertRedirect();
        $this->assertDatabaseHas('riwayat_mutasi_umat', ['umat_id' => 7, 'jenis_mutasi' => 'Pindah Wilayah']);

        // cleanup
        DB::table('riwayat_mutasi_umat')->where('umat_id', 7)->where('jenis_mutasi', 'Pindah Wilayah')->delete();
    }

    public function test_delete_protection_kk_with_members(): void
    {
        $this->actingAs($this->admin());
        $kk = DB::table('kk_katolik')
            ->whereIn('id', function ($q) { $q->select('kk_id')->from('umat')->whereNotNull('kk_id'); })
            ->first();
        $this->assertNotNull($kk);

        $resp = $this->delete("/superadmin/kk-katolik/{$kk->id}");
        $resp->assertRedirect();
        $this->assertDatabaseHas('kk_katolik', ['id' => $kk->id]);
    }

    public function test_superadmin_can_access_role_module(): void
    {
        $this->actingAs($this->admin());
        $this->get('/superadmin/role')->assertStatus(200);
    }

    public function test_riwayat_page_renders_data(): void
    {
        $this->actingAs($this->admin());
        DB::table('riwayat_mutasi_umat')->insert([
            'umat_id' => 7,
            'jenis_mutasi' => 'Mutasi KUB',
            'alasan' => 'QA riwayat render',
            'tgl_mutasi' => now()->toDateString(),
            'created_by' => $this->admin()->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $resp = $this->get('/superadmin/umat/7/riwayat');
        $resp->assertStatus(200);
        $resp->assertSee('Mutasi KUB');

        DB::table('riwayat_mutasi_umat')->where('umat_id', 7)->where('jenis_mutasi', 'Mutasi KUB')->where('alasan', 'QA riwayat render')->delete();
    }

    public function test_generic_module_create(): void
    {
        $this->actingAs($this->admin());
        $parokiId = DB::table('paroki')->orderBy('id_paroki')->value('id_paroki');
        $resp = $this->post('/superadmin/wilayah', [
            'paroki_id' => $parokiId,
            'kode_wilayah' => 'QA-' . time(),
            'nama_wilayah' => 'QA Wilayah Test',
            'status' => 'Aktif',
        ]);
        $resp->assertRedirect();
        $this->assertDatabaseHas('wilayah', ['nama_wilayah' => 'QA Wilayah Test']);

        DB::table('wilayah')->where('nama_wilayah', 'QA Wilayah Test')->delete();
    }

    public function test_low_privilege_blocked_from_super_module(): void
    {
        $u = $this->lowPrivUser();
        if (!$u) {
            $this->markTestSkipped('Tidak ada user low-privilege untuk diuji.');
            return;
        }
        $user = User::find($u->id);
        $this->actingAs($user);
        $resp = $this->get('/superadmin/role');
        $resp->assertForbidden();
    }
}
