<?php
/**
 * Seed Realistic Sample KK Katolik with Full Family Members & Complete Sacraments for Print Testing
 * Run: php artisan tinker --execute="require base_path('tests/seed_sample_kk_for_print.php');"
 */

use Illuminate\Support\Facades\DB;
use App\Models\KkKatolik;
use App\Models\Umat;
use App\Models\Wilayah;
use App\Models\Kub;

echo "\n=======================================================\n";
echo "   PEMBUATAN DATA SAMPEL KK KATOLIK LENGKAP UNTUK CETAK\n";
echo "=======================================================\n\n";

$wilayah = Wilayah::first();
$kub = Kub::first();

$noKkParoki = "K012001001";
$noKkDukcapil = "5302011508820001";

// Cek apakah KK dengan nomor ini sudah ada, jika ada perbarui agar tidak duplikat
$kk = KkKatolik::updateOrCreate(
    ['no_kk_kw' => $noKkParoki],
    [
        'no_kk_dukcapil' => $noKkDukcapil,
        'nama_lahir_pemilik' => 'Petrus Kanisius Tefa',
        'nama_baptis_pemilik' => 'Petrus Kanisius',
        'nik_pemilik' => '5302011204820002',
        'nama_pasangan' => 'Theresia Lisieux Neno',
        'wilayah_id' => $wilayah?->id,
        'kub_id' => $kub?->id,
        'gereja_paroki' => 'Paroki St. Vinsensius a Paulo Benlutu',
        'lokasi_gereja' => 'Pusat Paroki Benlutu',
        'alamat_sekarang' => 'RT 002 / RW 001, Dusun A',
        'rt' => '002',
        'rw' => '001',
        'desa_kelurahan' => 'Benlutu',
        'kecamatan' => 'Batu Putih',
        'kota_kabupaten' => 'Kabupaten Timor Tengah Selatan',
        'handphone' => '081234567890',
        'email' => 'petrus.tefa@parokibenlutu.id',
        'status_kepemilikan_rumah' => 'Milik Sendiri',
        'kategori_ekonomi' => 'Sejahtera / Mandiri',
        'pekerjaan' => 'PNS / Guru',
        'pendidikan' => 'S1 / Sarjana',
        'golongan_darah' => 'O',
        'status_kk' => 'Aktif',
        'status_verifikasi' => 'Terverifikasi',
    ]
);

echo "✅ [1] HEADER KK BERHASIL DISIMPAN:\n";
echo "    - No KK Paroki : {$kk->no_kk_kw}\n";
echo "    - No KK Sipil  : {$kk->no_kk_dukcapil}\n";
echo "    - Kepala KK    : {$kk->nama_lahir_pemilik}\n";
echo "    - Wilayah      : " . ($wilayah?->nama_wilayah ?? 'Pusat Paroki') . "\n";
echo "    - KUB          : " . ($kub?->nama_kub ?? 'Bunda Nirmala') . "\n";
echo "    - Alamat       : {$kk->alamat_sekarang}, {$kk->desa_kelurahan}\n\n";

// Hapus anggota lama dari KK ini jika ada agar tersinkronisasi rapi
Umat::where('kk_id', $kk->id)->delete();

$members = [
    // 1. KEPALA KELUARGA
    [
        'kk_id' => $kk->id,
        'no_kk_kw' => $kk->no_kk_kw,
        'nama_pemilik_kk' => $kk->nama_lahir_pemilik,
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
        'agama_asal' => 'Katolik sejak lahir',
        'pekerjaan' => 'PNS / Guru',
        'pendidikan' => 'S1 / Sarjana',
        'status_umat' => 'Aktif',
        'status_perkawinan' => 'Menikah',
        // Sakramen Baptis
        'status_baptis' => 'Sudah',
        'jenis_penerimaan_baptis' => 'Baptis Bayi (Infantis)',
        'tgl_baptis' => '1982-06-12',
        'paroki_baptis' => 'Paroki St. Vinsensius a Paulo Benlutu',
        'pastor_baptis' => 'RD. Herman Hillers Penga',
        'wali_baptis' => 'Fransiskus Xaverius Tefa',
        'buku_baptis_vol' => 'I',
        'buku_baptis_hal' => '45',
        'buku_baptis_no' => '120',
        // Komuni & Krisma
        'tgl_komuni_1' => '1992-05-24',
        'paroki_komuni_1' => 'Paroki St. Vinsensius a Paulo Benlutu',
        'tgl_krisma' => '1998-08-15',
        'paroki_krisma' => 'Katedral Kristus Raja Kupang',
        // Perkawinan
        'tgl_perkawinan' => '2010-09-25',
        'paroki_perkawinan' => 'Paroki St. Vinsensius a Paulo Benlutu',
        'nama_pasangan' => 'Theresia Lisieux Neno',
        'status_perkawinan_kanonik' => 'Sah secara Katolik (Kanonik)',
    ],
    // 2. ISTRI
    [
        'kk_id' => $kk->id,
        'no_kk_kw' => $kk->no_kk_kw,
        'nama_pemilik_kk' => $kk->nama_lahir_pemilik,
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
        'agama_asal' => 'Katolik sejak lahir',
        'pekerjaan' => 'Wiraswasta',
        'pendidikan' => 'SMA / SMK',
        'status_umat' => 'Aktif',
        'status_perkawinan' => 'Menikah',
        // Sakramen Baptis
        'status_baptis' => 'Sudah',
        'jenis_penerimaan_baptis' => 'Baptis Bayi (Infantis)',
        'tgl_baptis' => '1986-11-15',
        'paroki_baptis' => 'Paroki St. Maria Mater Dolorosa Soe',
        'pastor_baptis' => 'RP. Damasus Sumardi, CMF',
        'wali_baptis' => 'Klara Assisi Neno',
        'buku_baptis_vol' => 'II',
        'buku_baptis_hal' => '18',
        'buku_baptis_no' => '088',
        // Komuni & Krisma
        'tgl_komuni_1' => '1996-06-02',
        'paroki_komuni_1' => 'Paroki St. Maria Mater Dolorosa Soe',
        'tgl_krisma' => '2002-07-20',
        'paroki_krisma' => 'Paroki St. Maria Mater Dolorosa Soe',
        // Perkawinan
        'tgl_perkawinan' => '2010-09-25',
        'paroki_perkawinan' => 'Paroki St. Vinsensius a Paulo Benlutu',
        'nama_pasangan' => 'Petrus Kanisius Tefa',
        'status_perkawinan_kanonik' => 'Sah secara Katolik (Kanonik)',
    ],
    // 3. ANAK PERTAMA
    [
        'kk_id' => $kk->id,
        'no_kk_kw' => $kk->no_kk_kw,
        'nama_pemilik_kk' => $kk->nama_lahir_pemilik,
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
        'agama_asal' => 'Katolik sejak lahir',
        'pekerjaan' => 'Pelajar / Mahasiswa',
        'pendidikan' => 'SMP / MTs',
        'status_umat' => 'Aktif',
        'status_perkawinan' => 'Belum Menikah',
        // Sakramen
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
    // 4. ANAK KEDUA
    [
        'kk_id' => $kk->id,
        'no_kk_kw' => $kk->no_kk_kw,
        'nama_pemilik_kk' => $kk->nama_lahir_pemilik,
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
        'agama_asal' => 'Katolik sejak lahir',
        'pekerjaan' => 'Belum / Tidak Bekerja',
        'pendidikan' => 'Belum Sekolah',
        'status_umat' => 'Aktif',
        'status_perkawinan' => 'Belum Menikah',
        // Sakramen
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
];

foreach ($members as $idx => $m) {
    $u = Umat::create($m);
    echo "✅ [2." . ($idx + 1) . "] Anggota " . ($idx + 1) . " Tersimpan: {$u->nama_lengkap} ({$u->hubungan_keluarga})\n";
    echo "       - NIK     : {$u->nik}\n";
    echo "       - Lahir   : {$u->tempat_lahir}, {$u->tanggal_lahir->format('d/m/Y')}\n";
    echo "       - Baptis  : " . ($u->tgl_baptis ? $u->tgl_baptis->format('d/m/Y') : '-') . " [Vol " . ($u->buku_baptis_vol ?? '-') . "/Hal " . ($u->buku_baptis_hal ?? '-') . "/No " . ($u->buku_baptis_no ?? '-') . "]\n";
    if ($u->tgl_komuni_1) echo "       - Komuni  : " . $u->tgl_komuni_1 . "\n";
    if ($u->tgl_krisma) echo "       - Krisma  : " . $u->tgl_krisma . "\n";
    if ($u->tgl_perkawinan) echo "       - Nikah   : " . $u->tgl_perkawinan . " (" . $u->status_perkawinan_kanonik . ")\n";
    echo "\n";
}

echo "=======================================================\n";
echo "   DATA BERHASIL DISIMPAN PERMANEN DI DATABASE!\n";
echo "   Silakan buka: http://127.0.0.1:8000/superadmin/kk-katolik\n";
echo "   Dan klik tombol Detail / Cetak pada KK No: {$kk->no_kk_kw}\n";
echo "=======================================================\n\n";

@unlink(__FILE__);
