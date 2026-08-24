<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Wilayah;
use App\Models\Kapela;
use App\Models\Kub;
use Illuminate\Support\Facades\DB;

class CoreModuleCrudTest extends TestCase
{
    /**
     * Test public pages load correctly.
     */
    public function test_homepage_loads_successfully(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_jadwal_misa_public_loads(): void
    {
        $response = $this->get('/jadwal-misa');
        $response->assertStatus(200);
    }

    public function test_berita_public_loads(): void
    {
        $response = $this->get('/berita');
        $response->assertStatus(200);
    }

    public function test_kapela_geojson_api_loads(): void
    {
        $response = $this->get('/api/kapela-geojson');
        $response->assertStatus(200);
    }

    /**
     * Test creating, editing, and deleting a Wilayah record safely.
     */
    public function test_wilayah_model_crud_cycle(): void
    {
        $wilayah = Wilayah::create([
            'nama_wilayah' => '[TEST-FEATURE] Wilayah Uji',
            'kode_wilayah' => 'TST-WIL-999',
        ]);

        $this->assertDatabaseHas('wilayah', [
            'id' => $wilayah->id,
            'nama_wilayah' => '[TEST-FEATURE] Wilayah Uji',
        ]);

        $wilayah->nama_wilayah = '[TEST-FEATURE] Wilayah Edited';
        $wilayah->save();

        $this->assertDatabaseHas('wilayah', [
            'id' => $wilayah->id,
            'nama_wilayah' => '[TEST-FEATURE] Wilayah Edited',
        ]);

        $wilayah->delete();

        $this->assertDatabaseMissing('wilayah', [
            'id' => $wilayah->id,
        ]);
    }
}
