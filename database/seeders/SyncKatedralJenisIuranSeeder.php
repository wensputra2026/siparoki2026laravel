<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SyncKatedralJenisIuranSeeder extends Seeder
{
    /**
     * Master Data Tetap Jenis Iuran Standar Paroki
     */
    public function run(): void
    {
        $jsonPath = __DIR__ . '/jenis_iuran_master_data.json';
        $items = [];

        if (file_exists($jsonPath)) {
            $items = json_decode(file_get_contents($jsonPath), true) ?: [];
        }

        if (empty($items)) {
            $items = [
                ['id' => 1, 'kode_iuran' => 'IUR-GRJ', 'slug' => 'iuran-gereja', 'kategori_iuran' => 'Paroki', 'basis_penagihan' => 'Per Kepala Keluarga', 'nama_iuran' => 'Iuran Gereja', 'nominal_default' => 50000, 'periode' => 'Bulanan', 'wajib' => 1, 'tampil_di_laporan' => 1, 'status' => 1],
                ['id' => 2, 'kode_iuran' => 'IUR-PMB', 'slug' => 'iuran-pembangunan', 'kategori_iuran' => 'Pembangunan', 'basis_penagihan' => 'Per Kepala Keluarga', 'nama_iuran' => 'Iuran Pembangunan', 'nominal_default' => 25000, 'periode' => 'Bulanan', 'wajib' => 1, 'tampil_di_laporan' => 1, 'status' => 1],
                ['id' => 3, 'kode_iuran' => 'IUR-KMT', 'slug' => 'iuran-kematian', 'kategori_iuran' => 'Sosial', 'basis_penagihan' => 'Per Kepala Keluarga', 'nama_iuran' => 'Iuran Kematian', 'nominal_default' => 10000, 'periode' => 'Bulanan', 'wajib' => 1, 'tampil_di_laporan' => 1, 'status' => 1],
                ['id' => 4, 'kode_iuran' => 'IUR-KUB', 'slug' => 'iuran-kub', 'kategori_iuran' => 'KUB', 'basis_penagihan' => 'Per Lingkungan/KUB', 'nama_iuran' => 'Iuran KUB', 'nominal_default' => 20000, 'periode' => 'Bulanan', 'wajib' => 1, 'tampil_di_laporan' => 1, 'status' => 1],
                ['id' => 5, 'kode_iuran' => 'IUR-SOS', 'slug' => 'iuran-sosial', 'kategori_iuran' => 'Sosial', 'basis_penagihan' => 'Per Kepala Keluarga', 'nama_iuran' => 'Iuran Sosial', 'nominal_default' => 15000, 'periode' => 'Bulanan', 'wajib' => 1, 'tampil_di_laporan' => 1, 'status' => 1],
                ['id' => 6, 'kode_iuran' => 'KOL-MGG', 'slug' => 'kolekte-minggu', 'kategori_iuran' => 'Liturgi', 'basis_penagihan' => 'Sukarela', 'nama_iuran' => 'Kolekte Minggu', 'nominal_default' => 0, 'periode' => 'Mingguan', 'wajib' => 1, 'tampil_di_laporan' => 1, 'status' => 1],
                ['id' => 7, 'kode_iuran' => 'DNS-KHS', 'slug' => 'donasi-khusus', 'kategori_iuran' => 'Lainnya', 'basis_penagihan' => 'Sukarela', 'nama_iuran' => 'Donasi Khusus', 'nominal_default' => 0, 'periode' => 'Sekali Bayar', 'wajib' => 1, 'tampil_di_laporan' => 1, 'status' => 1],
                ['id' => 22, 'kode_iuran' => 'AKSI_NATAL', 'slug' => 'aksi-natal', 'kategori_iuran' => 'Sosial', 'basis_penagihan' => 'Per Lingkungan/KUB', 'nama_iuran' => 'Aksi Natal', 'nominal_default' => 0, 'periode' => 'Sekali Bayar', 'wajib' => 1, 'tampil_di_laporan' => 1, 'status' => 1],
                ['id' => 23, 'kode_iuran' => 'AKSI_PUASA', 'slug' => 'aksi-puasa-pembangunan', 'kategori_iuran' => 'Pembangunan', 'basis_penagihan' => 'Per Lingkungan/KUB', 'nama_iuran' => 'Aksi Puasa Pembangunan', 'nominal_default' => 0, 'periode' => 'Sekali Bayar', 'wajib' => 1, 'tampil_di_laporan' => 1, 'status' => 1],
                ['id' => 24, 'kode_iuran' => 'BLN_MARIA', 'slug' => 'bulan-maria', 'kategori_iuran' => 'Liturgi', 'basis_penagihan' => 'Per Lingkungan/KUB', 'nama_iuran' => 'Bulan Maria', 'nominal_default' => 0, 'periode' => 'Sekali Bayar', 'wajib' => 1, 'tampil_di_laporan' => 1, 'status' => 1],
                ['id' => 25, 'kode_iuran' => 'BLN_ROSARI', 'slug' => 'bulan-rosario', 'kategori_iuran' => 'Liturgi', 'basis_penagihan' => 'Per Lingkungan/KUB', 'nama_iuran' => 'Bulan Rosario', 'nominal_default' => 0, 'periode' => 'Sekali Bayar', 'wajib' => 1, 'tampil_di_laporan' => 1, 'status' => 1],
                ['id' => 26, 'kode_iuran' => 'PEMB_GEREJA', 'slug' => 'pembangunan-gereja', 'kategori_iuran' => 'Pembangunan', 'basis_penagihan' => 'Per Kepala Keluarga', 'nama_iuran' => 'Pembangunan Gereja', 'nominal_default' => 0, 'periode' => 'Bulanan', 'wajib' => 1, 'tampil_di_laporan' => 1, 'status' => 1],
                ['id' => 27, 'kode_iuran' => 'SEKAMI', 'slug' => 'sekami', 'kategori_iuran' => 'Paroki', 'basis_penagihan' => 'Per Anak SEKAMI', 'nama_iuran' => 'SEKAMI', 'nominal_default' => 0, 'periode' => 'Bulanan', 'wajib' => 1, 'tampil_di_laporan' => 1, 'status' => 1],
                ['id' => 28, 'kode_iuran' => 'TAHTA_SUCI', 'slug' => 'tahta-suci', 'kategori_iuran' => 'Universal Gereja', 'basis_penagihan' => 'Per Jiwa', 'nama_iuran' => 'Tahta Suci', 'nominal_default' => 20000, 'periode' => 'Tahunan', 'wajib' => 1, 'tampil_di_laporan' => 1, 'status' => 1],
            ];
        }

        DB::statement("SET FOREIGN_KEY_CHECKS=0;");
        DB::table('jenis_iuran')->truncate();

        foreach ($items as $item) {
            $row = (array)$item;
            DB::table('jenis_iuran')->insert([
                'id' => $row['id'] ?? null,
                'kode_iuran' => $row['kode_iuran'] ?? null,
                'slug' => $row['slug'] ?? null,
                'kategori_iuran' => !empty($row['kategori_iuran']) ? $row['kategori_iuran'] : 'Paroki',
                'basis_penagihan' => !empty($row['basis_penagihan']) ? $row['basis_penagihan'] : 'Per Kepala Keluarga',
                'nama_iuran' => $row['nama_iuran'] ?? 'Tanpa Nama',
                'nominal_default' => (float)($row['nominal_default'] ?? 0),
                'periode' => !empty($row['periode']) ? $row['periode'] : 'Bulanan',
                'bulan_mulai' => $row['bulan_mulai'] ?? null,
                'bulan_selesai' => $row['bulan_selesai'] ?? null,
                'tahun' => $row['tahun'] ?? null,
                'berlaku_dari' => $row['berlaku_dari'] ?? null,
                'berlaku_sampai' => $row['berlaku_sampai'] ?? null,
                'target_role' => $row['target_role'] ?? 'Semua',
                'wajib' => isset($row['wajib']) ? (int)$row['wajib'] : 1,
                'tampil_di_laporan' => isset($row['tampil_di_laporan']) ? (int)$row['tampil_di_laporan'] : 1,
                'paroki_id' => $row['paroki_id'] ?? null,
                'created_by' => $row['created_by'] ?? null,
                'updated_by' => $row['updated_by'] ?? null,
                'keterangan' => $row['keterangan'] ?? null,
                'status' => isset($row['status']) ? (int)$row['status'] : 1,
                'created_at' => $row['created_at'] ?? now(),
                'updated_at' => $row['updated_at'] ?? now(),
                'deleted_at' => $row['deleted_at'] ?? null,
                'deleted_by' => $row['deleted_by'] ?? null,
                'delete_reason' => $row['delete_reason'] ?? null,
                'is_deleted' => isset($row['is_deleted']) ? (int)$row['is_deleted'] : 0,
            ]);
        }

        DB::statement("SET FOREIGN_KEY_CHECKS=1;");
        echo "Berhasil memuat " . count($items) . " data tetap jenis_iuran ke database paroki!\n";
    }
}
