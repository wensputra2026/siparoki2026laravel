<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class JenisIuranSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            SyncKatedralJenisIuranSeeder::class,
        ]);
    }
}
