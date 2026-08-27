<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class SecurityHardeningTest extends TestCase
{
    private function makeUser(string $roleSlug): User
    {
        $role = Role::where('slug', $roleSlug)->first()
            ?? Role::create(['nama_role' => $roleSlug, 'slug' => $roleSlug, 'status' => 1]);

        return User::create([
            'nama_lengkap' => 'Test ' . $roleSlug,
            'username' => 'test_' . $roleSlug . '_' . uniqid(),
            'email' => 'test_' . $roleSlug . '_' . uniqid() . '@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
            'status' => 1,
        ]);
    }

    public function test_bendahara_cannot_access_sensitive_modules(): void
    {
        $user = $this->makeUser('bendahara');
        $this->actingAs($user);

        foreach (['user', 'role', 'roles', 'backup-database', 'security-center', 'pengaturan-aplikasi'] as $mod) {
            $this->get("/bendahara/{$mod}")->assertStatus(403);
        }
    }

    public function test_bendahara_can_access_keuangan_and_umat_reference(): void
    {
        $user = $this->makeUser('bendahara');
        $this->actingAs($user);

        $this->get('/bendahara/keuangan')->assertStatus(200);
        $this->get('/bendahara/umat')->assertStatus(200);
    }

    public function test_bendahara_cannot_create_superadmin_via_user_module(): void
    {
        $user = $this->makeUser('bendahara');
        $this->actingAs($user);

        $superRole = Role::where('slug', 'super_admin')->first();

        $res = $this->post('/bendahara/user', [
            'nama_lengkap' => 'Hacker',
            'email' => 'hacker' . uniqid() . '@example.com',
            'username' => 'hacker_' . uniqid(),
            'role_id' => $superRole?->id ?? 1,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);
        $this->assertTrue(in_array($res->status(), [302, 403]));
    }

    public function test_v2_prefix_is_not_an_admin_backdoor(): void
    {
        $user = $this->makeUser('umat');
        $this->actingAs($user);

        $res1 = $this->get('/v2/user');
        $this->assertTrue(in_array($res1->status(), [302, 403]));

        $res2 = $this->get('/v2/keuangan');
        $this->assertTrue(in_array($res2->status(), [302, 403]));

        $this->get('/umat/dashboard')->assertStatus(200);
    }

    public function test_superadmin_can_access_everything(): void
    {
        $user = $this->makeUser('super_admin');
        $this->actingAs($user);

        $this->get('/superadmin/user')->assertStatus(200);
        $this->get('/superadmin/keuangan')->assertStatus(200);
        $this->get('/superadmin/backup-database')->assertStatus(200);
    }

    public function test_keuangan_rejects_negative_or_zero_amount(): void
    {
        $user = $this->makeUser('bendahara');
        $this->actingAs($user);

        $this->post('/bendahara/keuangan', [
            'jumlah' => -5000,
            'jenis' => 'pengeluaran',
            'kategori' => 'operasional',
        ])->assertSessionHasErrors('jumlah');

        $this->post('/bendahara/keuangan', [
            'jumlah' => 0,
            'jenis' => 'pengeluaran',
            'kategori' => 'operasional',
        ])->assertSessionHasErrors('jumlah');
    }
}
