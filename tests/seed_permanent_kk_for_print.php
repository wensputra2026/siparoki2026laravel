<?php
/**
 * Auto-Migrate Missing Columns on table 'umat' and Seed 3 Permanent Realistic Families for Print Testing
 * Run: php artisan tinker --execute="require base_path('tests/seed_permanent_kk_for_print.php');"
 */

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\KkKatolik;
use App\Models\Umat;
use App\Models\Wilayah;
use App\Models\Kub;

echo "\n=======================================================\n";
echo "   PEMBUATAN DATA LENGKAP 3 KK KATOLIK UNTUK UJI CETAK\n";
echo "   Paroki St. Vinsensius a Paulo Benlutu\n";
echo "=======================================================\n\n";

// 1. Pastikan kolom-kolom yang diperlukan di tabel 'umat' tersedia
$umatColumns = [
    'pendidikan' => 'string:100',
    'pendidikan_saat_ini' => 'string:100',
    'pekerjaan' => 'string:150',
    'golongan_darah' => 'string:20',
    'suku_etnis' => 'string:100',
    'agama_asal' => 'string:50',
    'status_baptis' => 'string:50',
    'jenis_penerimaan_baptis' => 'string:50',
    'tgl_baptis' => 'date',
    'paroki_baptis' => 'string:150',
    'pastor_baptis' => 'string:150',
    'wali_baptis' => 'string:150',
    'buku_baptis_vol' => 'string:20',
    'buku_baptis_hal' => 'string:20',
    'buku_baptis_no' => 'string:20',
    'tgl_komuni_1' => 'date',
    'paroki_komuni_1' => 'string:150',
    'tgl_krisma' => 'date',
    'paroki_krisma' => 'string:150',
    'tgl_perkawinan' => 'date',
    'paroki_perkawinan' => 'string:150',
    'nama_pasangan' => 'string:150',
    'status_perkawinan_kanonik' => 'string:50',
    'peristiwa_lain' => 'string:255',
    'no_surat_peristiwa' => 'string:100',
    'status_panggilan' => 'string:100',
    'nama_ordo_kongregasi' => 'string:150',
    'tahap_panggilan' => 'string:100',
    'tempat_tugas_biara' => 'string:255',
    'tgl_tahbisan_kaul' => 'date',
];

if (Schema::hasTable('umat')) {
    Schema::table('umat', function ($table) use ($umatColumns) {
        foreach ($umatColumns as $col => $typeDef) {
            if (!Schema::hasColumn('umat', $col)) {
                if ($typeDef === 'date') {
                    $table->date($col)->nullable();
                } else {
                    $len = 255;
                    if (str_contains($typeDef, ':')) {
                        $len = (int) explode(':', $typeDef)[1];
                    }
                    $table->string($col, $len)->nullable();
                }
            }
        }
    });
}

// 2. Ambil Wilayah dan KUB
$wilayah1 = Wilayah::first();
$wilayah2 = Wilayah::skip(1)->first() ?? $wilayah1;
$kub1 = Kub::first();
$kub2 = Kub::skip(1)->first() ?? $kub1;

$families = [
    // ==========================================
    // KELUARGA 1: PETRUS KANISIUS TEFA (4 Jiwa)
    // ==========================================
    [
        'header' => [
            'no_kk_kw' => 'K012001001',
            'no_kk_dukcapil' => '5302011508820001',
            'nama_lahir_pemilik' => 'Petrus Kanisius Tefa',
            'nama_baptis_pemilik' => 'Petrus Kanisius',
            'nik_pemilik' => '5302011204820002',
            'nama_pasangan' => 'Theresia Lisieux Neno',
            'wilayah_id' => $wilayah1?->id,
            'kub_id' => $kub1?->id,
            'alamat_sekarang' => 'RT 002 / RW 001, Dusun A',
            'rt' => '002',
            'rw' => '001',
            'desa_kelurahan' => 'Benlutu',
            'kecamatan' => 'Batu Putih',
            'kota_kabupaten' => 'Kabupaten Timor Tengah Selatan',
            'handphone' => '081234567891',
            'email' => 'petrus.tefa@parokibenlutu.id',
            'status_kepemilikan_rumah' => 'Milik Sendiri',
            'kategori_ekonomi' => 'Sejahtera / Mandiri',
            'pekerjaan' => 'PNS / Guru',
            'pendidikan' => 'S1 / Sarjana',
            'golongan_darah' => 'O',
            'status_kk' => 'Aktif',
            'status_verifikasi' => 'Terverifikasi',
        ],
        'members' => [
            [
                'no_urut_anggota' => 1,
                'nama_lengkap' => 'Petrus Kanisius Tefa',
                'nama_lahir' => 'Petrus Kanisius Tefa',
                'nama_baptis' => 'Petrus Kanisius',
                'nik' => '5302011204820002',
                'hubungan_keluarga' => 'Kepala Keluarga',
                'jenis_kelamin' => 'Laki-Laki',
                'tempat_lahir' => 'Benlutu',
                'tanggal_lahir' => '1982-04-12',
                'suku_etnis' => 'Timor / Dawan',
                'golongan_darah' => 'O',
                'pekerjaan' => 'PNS / Guru',
                'pendidikan' => 'S1 / Sarjana',
                'pendidikan_saat_ini' => 'S1 / Sarjana',
                'status_umat' => 'Aktif',
                'status_perkawinan' => 'Menikah',
                'status_baptis' => 'Sudah',
                'jenis_penerimaan_baptis' => 'Baptis Bayi (Infantis)',
                'tgl_baptis' => '1982-06-12',
                'paroki_baptis' => 'Paroki St. Vinsensius a Paulo Benlutu',
                'pastor_baptis' => 'RD. Herman Hillers Penga',
                'wali_baptis' => 'Fransiskus Xaverius Tefa',
                'buku_baptis_vol' => 'I',
                'buku_baptis_hal' => '45',
                'buku_baptis_no' => '120',
                'tgl_komuni_1' => '1992-05-24',
                'paroki_komuni_1' => 'Paroki St. Vinsensius a Paulo Benlutu',
                'tgl_krisma' => '1998-08-15',
                'paroki_krisma' => 'Katedral Kristus Raja Kupang',
                'tgl_perkawinan' => '2010-09-25',
                'paroki_perkawinan' => 'Paroki St. Vinsensius a Paulo Benlutu',
                'nama_pasangan' => 'Theresia Lisieux Neno',
                'status_perkawinan_kanonik' => 'Sah secara Katolik (Kanonik)',
            ],
            [
                'no_urut_anggota' => 2,
                'nama_lengkap' => 'Theresia Lisieux Neno',
                'nama_lahir' => 'Theresia Lisieux Neno',
                'nama_baptis' => 'Theresia Lisieux',
                'nik' => '5302015010860003',
                'hubungan_keluarga' => 'Istri',
                'jenis_kelamin' => 'Perempuan',
                'tempat_lahir' => 'Soe',
                'tanggal_lahir' => '1986-10-10',
                'suku_etnis' => 'Timor / Dawan',
                'golongan_darah' => 'A',
                'pekerjaan' => 'Wiraswasta',
                'pendidikan' => 'SMA / SMK',
                'pendidikan_saat_ini' => 'SMA / SMK',
                'status_umat' => 'Aktif',
                'status_perkawinan' => 'Menikah',
                'status_baptis' => 'Sudah',
                'jenis_penerimaan_baptis' => 'Baptis Bayi (Infantis)',
                'tgl_baptis' => '1986-11-15',
                'paroki_baptis' => 'Paroki St. Maria Mater Dolorosa Soe',
                'pastor_baptis' => 'RP. Damasus Sumardi, CMF',
                'wali_baptis' => 'Klara Assisi Neno',
                'buku_baptis_vol' => 'II',
                'buku_baptis_hal' => '18',
                'buku_baptis_no' => '088',
                'tgl_komuni_1' => '1996-06-02',
                'paroki_komuni_1' => 'Paroki St. Maria Mater Dolorosa Soe',
                'tgl_krisma' => '2002-07-20',
                'paroki_krisma' => 'Paroki St. Maria Mater Dolorosa Soe',
                'tgl_perkawinan' => '2010-09-25',
                'paroki_perkawinan' => 'Paroki St. Vinsensius a Paulo Benlutu',
                'nama_pasangan' => 'Petrus Kanisius Tefa',
                'status_perkawinan_kanonik' => 'Sah secara Katolik (Kanonik)',
            ],
            [
                'no_urut_anggota' => 3,
                'nama_lengkap' => 'Mikael Gabriel Tefa',
                'nama_lahir' => 'Mikael Gabriel Tefa',
                'nama_baptis' => 'Mikael Gabriel',
                'nik' => '5302011003120004',
                'hubungan_keluarga' => 'Anak Kandung',
                'jenis_kelamin' => 'Laki-Laki',
                'tempat_lahir' => 'Benlutu',
                'tanggal_lahir' => '2012-03-10',
                'suku_etnis' => 'Timor / Dawan',
                'golongan_darah' => 'O',
                'pekerjaan' => 'Pelajar / Mahasiswa',
                'pendidikan' => 'SMP / MTs',
                'pendidikan_saat_ini' => 'SMP / MTs',
                'status_umat' => 'Aktif',
                'status_perkawinan' => 'Belum Menikah',
                'status_baptis' => 'Sudah',
                'jenis_penerimaan_baptis' => 'Baptis Bayi (Infantis)',
                'tgl_baptis' => '2012-05-01',
                'paroki_baptis' => 'Paroki St. Vinsensius a Paulo Benlutu',
                'pastor_baptis' => 'RD. Krispinus Saku',
                'wali_baptis' => 'Antonius Padua',
                'buku_baptis_vol' => 'III',
                'buku_baptis_hal' => '05',
                'buku_baptis_no' => '014',
                'tgl_komuni_1' => '2022-06-19',
                'paroki_komuni_1' => 'Paroki St. Vinsensius a Paulo Benlutu',
                'tgl_krisma' => '2025-08-20',
                'paroki_krisma' => 'Paroki St. Vinsensius a Paulo Benlutu',
            ],
            [
                'no_urut_anggota' => 4,
                'nama_lengkap' => 'Agnes Monika Tefa',
                'nama_lahir' => 'Agnes Monika Tefa',
                'nama_baptis' => 'Agnes Monika',
                'nik' => '5302016101230005',
                'hubungan_keluarga' => 'Anak Kandung',
                'jenis_kelamin' => 'Perempuan',
                'tempat_lahir' => 'Benlutu',
                'tanggal_lahir' => '2023-01-21',
                'suku_etnis' => 'Timor / Dawan',
                'golongan_darah' => 'A',
                'pekerjaan' => 'Belum / Tidak Bekerja',
                'pendidikan' => 'Belum Sekolah',
                'pendidikan_saat_ini' => 'Belum Sekolah',
                'status_umat' => 'Aktif',
                'status_perkawinan' => 'Belum Menikah',
                'status_baptis' => 'Sudah',
                'jenis_penerimaan_baptis' => 'Baptis Bayi (Infantis)',
                'tgl_baptis' => '2023-03-05',
                'paroki_baptis' => 'Paroki St. Vinsensius a Paulo Benlutu',
                'pastor_baptis' => 'RD. Herman Hillers Penga',
                'wali_baptis' => 'Elisabeth Seton',
                'buku_baptis_vol' => 'IV',
                'buku_baptis_hal' => '22',
                'buku_baptis_no' => '071',
            ],
        ],
    ],

    // ==========================================
    // KELUARGA 2: FRANSISKUS XAVERIUS BANO (3 Jiwa)
    // ==========================================
    [
        'header' => [
            'no_kk_kw' => 'K012001002',
            'no_kk_dukcapil' => '5302012004780001',
            'nama_lahir_pemilik' => 'Fransiskus Xaverius Bano',
            'nama_baptis_pemilik' => 'Fransiskus Xaverius',
            'nik_pemilik' => '5302011506780001',
            'nama_pasangan' => 'Maria Goretti Mella',
            'wilayah_id' => $wilayah2?->id,
            'kub_id' => $kub2?->id,
            'alamat_sekarang' => 'RT 004 / RW 002, Dusun B',
            'rt' => '004',
            'rw' => '002',
            'desa_kelurahan' => 'Benlutu',
            'kecamatan' => 'Batu Putih',
            'kota_kabupaten' => 'Kabupaten Timor Tengah Selatan',
            'handphone' => '081234567892',
            'email' => 'frans.bano@parokibenlutu.id',
            'status_kepemilikan_rumah' => 'Milik Sendiri',
            'kategori_ekonomi' => 'Sederhana / Cukup',
            'pekerjaan' => 'Petani / Pekebun',
            'pendidikan' => 'SMA / SMK',
            'golongan_darah' => 'B',
            'status_kk' => 'Aktif',
            'status_verifikasi' => 'Terverifikasi',
        ],
        'members' => [
            [
                'no_urut_anggota' => 1,
                'nama_lengkap' => 'Fransiskus Xaverius Bano',
                'nama_lahir' => 'Fransiskus Xaverius Bano',
                'nama_baptis' => 'Fransiskus Xaverius',
                'nik' => '5302011506780001',
                'hubungan_keluarga' => 'Kepala Keluarga',
                'jenis_kelamin' => 'Laki-Laki',
                'tempat_lahir' => 'Benlutu',
                'tanggal_lahir' => '1978-06-15',
                'suku_etnis' => 'Timor / Dawan',
                'golongan_darah' => 'B',
                'pekerjaan' => 'Petani / Pekebun',
                'pendidikan' => 'SMA / SMK',
                'pendidikan_saat_ini' => 'SMA / SMK',
                'status_umat' => 'Aktif',
                'status_perkawinan' => 'Menikah',
                'status_baptis' => 'Sudah',
                'jenis_penerimaan_baptis' => 'Baptis Bayi (Infantis)',
                'tgl_baptis' => '1978-08-20',
                'paroki_baptis' => 'Paroki St. Vinsensius a Paulo Benlutu',
                'buku_baptis_vol' => 'I',
                'buku_baptis_hal' => '12',
                'buku_baptis_no' => '035',
                'tgl_komuni_1' => '1988-06-05',
                'paroki_komuni_1' => 'Paroki St. Vinsensius a Paulo Benlutu',
                'tgl_krisma' => '1994-07-16',
                'paroki_krisma' => 'Paroki St. Vinsensius a Paulo Benlutu',
                'tgl_perkawinan' => '2005-11-12',
                'paroki_perkawinan' => 'Paroki St. Vinsensius a Paulo Benlutu',
                'nama_pasangan' => 'Maria Goretti Mella',
                'status_perkawinan_kanonik' => 'Sah secara Katolik (Kanonik)',
            ],
            [
                'no_urut_anggota' => 2,
                'nama_lengkap' => 'Maria Goretti Mella',
                'nama_lahir' => 'Maria Goretti Mella',
                'nama_baptis' => 'Maria Goretti',
                'nik' => '5302014408800002',
                'hubungan_keluarga' => 'Istri',
                'jenis_kelamin' => 'Perempuan',
                'tempat_lahir' => 'Batu Putih',
                'tanggal_lahir' => '1980-08-04',
                'suku_etnis' => 'Timor / Dawan',
                'golongan_darah' => 'O',
                'pekerjaan' => 'Ibu Rumah Tangga',
                'pendidikan' => 'SMA / SMK',
                'pendidikan_saat_ini' => 'SMA / SMK',
                'status_umat' => 'Aktif',
                'status_perkawinan' => 'Menikah',
                'status_baptis' => 'Sudah',
                'jenis_penerimaan_baptis' => 'Baptis Bayi (Infantis)',
                'tgl_baptis' => '1980-09-28',
                'paroki_baptis' => 'Paroki St. Vinsensius a Paulo Benlutu',
                'buku_baptis_vol' => 'I',
                'buku_baptis_hal' => '33',
                'buku_baptis_no' => '092',
                'tgl_komuni_1' => '1990-06-10',
                'paroki_komuni_1' => 'Paroki St. Vinsensius a Paulo Benlutu',
                'tgl_krisma' => '1996-08-15',
                'paroki_krisma' => 'Paroki St. Vinsensius a Paulo Benlutu',
                'tgl_perkawinan' => '2005-11-12',
                'paroki_perkawinan' => 'Paroki St. Vinsensius a Paulo Benlutu',
                'nama_pasangan' => 'Fransiskus Xaverius Bano',
                'status_perkawinan_kanonik' => 'Sah secara Katolik (Kanonik)',
            ],
            [
                'no_urut_anggota' => 3,
                'nama_lengkap' => 'Yohanes Bosco Bano',
                'nama_lahir' => 'Yohanes Bosco Bano',
                'nama_baptis' => 'Yohanes Bosco',
                'nik' => '5302011202080003',
                'hubungan_keluarga' => 'Anak Kandung',
                'jenis_kelamin' => 'Laki-Laki',
                'tempat_lahir' => 'Benlutu',
                'tanggal_lahir' => '2008-02-12',
                'suku_etnis' => 'Timor / Dawan',
                'golongan_darah' => 'B',
                'pekerjaan' => 'Pelajar / Mahasiswa',
                'pendidikan' => 'SMA / SMK',
                'pendidikan_saat_ini' => 'SMA / SMK',
                'status_umat' => 'Aktif',
                'status_perkawinan' => 'Belum Menikah',
                'status_baptis' => 'Sudah',
                'jenis_penerimaan_baptis' => 'Baptis Bayi (Infantis)',
                'tgl_baptis' => '2008-04-06',
                'paroki_baptis' => 'Paroki St. Vinsensius a Paulo Benlutu',
                'buku_baptis_vol' => 'II',
                'buku_baptis_hal' => '78',
                'buku_baptis_no' => '210',
                'tgl_komuni_1' => '2018-06-03',
                'paroki_komuni_1' => 'Paroki St. Vinsensius a Paulo Benlutu',
                'tgl_krisma' => '2024-08-15',
                'paroki_krisma' => 'Paroki St. Vinsensius a Paulo Benlutu',
            ],
        ],
    ],
];

foreach ($families as $idx => $f) {
    $headerData = $f['header'];
    $kk = KkKatolik::updateOrCreate(
        ['no_kk_kw' => $headerData['no_kk_kw']],
        $headerData
    );

    // Sync Anggota
    Umat::where('kk_id', $kk->id)->delete();
    foreach ($f['members'] as $m) {
        $m['kk_id'] = $kk->id;
        $m['no_kk_kw'] = $kk->no_kk_kw;
        $m['nama_pemilik_kk'] = $kk->nama_lahir_pemilik;
        Umat::create($m);
    }

    echo "✅ [KK-" . ($idx + 1) . "] {$kk->no_kk_kw} - {$kk->nama_lahir_pemilik} (" . count($f['members']) . " Anggota Keluarga)\n";
    echo "       - URL View : http://127.0.0.1:8000/superadmin/kk-katolik/{$kk->id}/view\n";
    echo "       - URL Cetak: http://127.0.0.1:8000/superadmin/kk-katolik/{$kk->id}/cetak\n\n";
}

echo "=======================================================\n";
echo "   SEMUA DATA BERHASIL TERSIMPAN DI DATABASE!\n";
echo "   Total KK Aktif: " . KkKatolik::count() . " Keluarga\n";
echo "   Total Umat    : " . Umat::count() . " Jiwa\n";
echo "=======================================================\n\n";
