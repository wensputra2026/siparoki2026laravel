<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SyncKatedralJenisIuranSeeder extends Seeder
{
    public function run(): void
    {
        echo "Copying all rows from katedral.jenis_iuran into parokibenlutularavel12.jenis_iuran...\n";
        
        DB::statement("SET FOREIGN_KEY_CHECKS=0;");
        DB::table('jenis_iuran')->truncate();
        
        // Direct exact copy of all 28 rows and all columns
        DB::statement("INSERT INTO `parokibenlutularavel12`.`jenis_iuran` SELECT * FROM `katedral`.`jenis_iuran`");
        
        DB::statement("SET FOREIGN_KEY_CHECKS=1;");

        $total = DB::table('jenis_iuran')->count();
        echo "Successfully copied {$total} rows from http://localhost/katedral/admin/jenis-iuran!\n";

        $rows = DB::table('jenis_iuran')->select('id', 'kode_iuran', 'nama_iuran', 'kategori_iuran', 'basis_penagihan', 'nominal_default', 'periode', 'status')->get();
        foreach ($rows as $r) {
            echo sprintf(" [%2d] %-15s | %-32s | %-16s | Rp %s\n", $r->id, $r->kode_iuran, $r->nama_iuran, $r->kategori_iuran, number_format($r->nominal_default, 0, ',', '.'));
        }
    }
}
