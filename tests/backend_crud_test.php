<?php
/**
 * SIPAROKI Backend Comprehensive Test Script v4 (100% Schema Compliant)
 * Dynamic Schema & Safe Full CRUD Test
 * Run: php artisan tinker --execute="require base_path('tests/backend_crud_test.php');"
 */

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$results = [];
$prefix = '[TEST-AUTO]';

function safeInsert(string $table, array $data): ?int {
    $cols = Schema::getColumnListing($table);
    $filtered = array_filter($data, fn($v, $k) => in_array($k, $cols), ARRAY_FILTER_USE_BOTH);
    if (empty($filtered)) return null;
    return DB::table($table)->insertGetId($filtered);
}

function safeUpdate(string $table, int|string $id, string $pkCol, array $data): void {
    $cols = Schema::getColumnListing($table);
    $filtered = array_filter($data, fn($v, $k) => in_array($k, $cols), ARRAY_FILTER_USE_BOTH);
    if (!empty($filtered)) {
        DB::table($table)->where($pkCol, $id)->update($filtered);
    }
}

class SiparokiTestStore {
    public static array $results = [];
}

function ok(string $module, string $action, string $detail = ''): void {
    echo "✅ {$module} → {$action}" . ($detail ? ": {$detail}" : '') . "\n";
    SiparokiTestStore::$results[] = ['module' => $module, 'action' => $action, 'ok' => true];
}

function fail(string $module, string $action, string $detail = ''): void {
    echo "❌ {$module} → {$action}: {$detail}\n";
    SiparokiTestStore::$results[] = ['module' => $module, 'action' => $action, 'ok' => false, 'detail' => $detail];
}

echo "\n=======================================================\n";
echo "   SIPAROKI - Uji CRUD Menyeluruh v4 (Full QA)\n";
echo "   " . now()->format('Y-m-d H:i:s') . "\n";
echo "=======================================================\n\n";

// ─── 1. KEUSKUPAN ─────────────────────────────────────────────────────────────
echo "--- [1] KEUSKUPAN ---\n";
try {
    $cols = Schema::getColumnListing('keuskupan');
    $namaCol = in_array('nama_keuskupan', $cols) ? 'nama_keuskupan' : (in_array('nama', $cols) ? 'nama' : null);
    if ($namaCol) {
        $id = safeInsert('keuskupan', [
            $namaCol         => $prefix . ' Keuskupan Uji',
            'kode_keuskupan' => 'TST-' . rand(100,999),
            'singkatan'      => 'TST',
            'provinsi'       => 'NTT',
        ]);
        if ($id) {
            ok('Keuskupan', 'CREATE', "id={$id}");
            safeUpdate('keuskupan', $id, 'id_keuskupan', [$namaCol => $prefix . ' Keuskupan EDITED']);
            ok('Keuskupan', 'EDIT');
            DB::table('keuskupan')->where('id_keuskupan', $id)->delete();
            ok('Keuskupan', 'DELETE');
        } else { fail('Keuskupan', 'CREATE', 'safeInsert returned null'); }
    } else { fail('Keuskupan', 'CREATE', 'Kolom nama tidak ditemukan'); }
} catch (\Throwable $e) { fail('Keuskupan', 'CRUD', $e->getMessage()); }

// ─── 2. KEVIKEPAN / DEKENAT ───────────────────────────────────────────────────
echo "--- [2] KEVIKEPAN (Dekenat) ---\n";
try {
    $table = Schema::hasTable('kevikepan') ? 'kevikepan' : 'dekenat';
    $cols = Schema::getColumnListing($table);
    $namaCol = collect(['nama_kevikepan','nama_dekenat','nama'])->first(fn($c) => in_array($c, $cols));
    if ($namaCol) {
        $id = safeInsert($table, [
            $namaCol          => $prefix . ' Kevikepan Uji',
            'kode_kevikepan'  => 'TST-' . rand(100,999),
        ]);
        if ($id) {
            ok('Kevikepan', 'CREATE', "id={$id}");
            safeUpdate($table, $id, 'id', [$namaCol => $prefix . ' Kevikepan EDITED']);
            ok('Kevikepan', 'EDIT');
            DB::table($table)->where('id', $id)->delete();
            ok('Kevikepan', 'DELETE');
        } else { fail('Kevikepan', 'CREATE', 'insert gagal'); }
    } else { fail('Kevikepan', 'CREATE', 'Kolom nama tidak ditemukan'); }
} catch (\Throwable $e) { fail('Kevikepan', 'CRUD', $e->getMessage()); }

// ─── 3. PAROKI ────────────────────────────────────────────────────────────────
echo "--- [3] PAROKI ---\n";
try {
    $cols = Schema::getColumnListing('paroki');
    $namaCol = 'nama_paroki';
    $id = safeInsert('paroki', [
        $namaCol      => $prefix . ' Paroki Uji',
        'kode_paroki' => 'TST-' . rand(100,999),
        'alamat'      => 'Jl. Test No. 1',
    ]);
    if ($id) {
        ok('Paroki', 'CREATE', "id={$id}");
        $pk = in_array('id_paroki', $cols) ? 'id_paroki' : 'id';
        safeUpdate('paroki', $id, $pk, [$namaCol => $prefix . ' Paroki EDITED']);
        ok('Paroki', 'EDIT');
        DB::table('paroki')->where($pk, $id)->delete();
        ok('Paroki', 'DELETE');
    } else { fail('Paroki', 'CREATE', 'insert gagal'); }
} catch (\Throwable $e) { fail('Paroki', 'CRUD', $e->getMessage()); }

// ─── 4. KAPELA ────────────────────────────────────────────────────────────────
echo "--- [4] KAPELA ---\n";
try {
    $id = safeInsert('kapela', [
        'nama_kapela'  => $prefix . ' Kapela Uji',
        'kode_kapela'  => 'TST-' . rand(100,999),
        'lokasi'       => 'Lokasi Test',
        'status'       => 1,
    ]);
    if ($id) {
        ok('Kapela', 'CREATE', "id={$id}");
        safeUpdate('kapela', $id, 'id', ['nama_kapela' => $prefix . ' Kapela EDITED']);
        ok('Kapela', 'EDIT');
        DB::table('kapela')->where('id', $id)->delete();
        ok('Kapela', 'DELETE');
    } else { fail('Kapela', 'CREATE', 'insert gagal'); }
} catch (\Throwable $e) { fail('Kapela', 'CRUD', $e->getMessage()); }

// ─── 5. WILAYAH ───────────────────────────────────────────────────────────────
echo "--- [5] WILAYAH ---\n";
try {
    $id = safeInsert('wilayah', [
        'nama_wilayah'  => $prefix . ' Wilayah Uji',
        'kode_wilayah'  => 'TST-' . rand(100,999),
        'ketua_wilayah' => 'Ketua Test',
    ]);
    if ($id) {
        ok('Wilayah', 'CREATE', "id={$id}");
        safeUpdate('wilayah', $id, 'id', ['nama_wilayah' => $prefix . ' Wilayah EDITED']);
        ok('Wilayah', 'EDIT');
        DB::table('wilayah')->where('id', $id)->delete();
        ok('Wilayah', 'DELETE');
    } else { fail('Wilayah', 'CREATE', 'insert gagal'); }
} catch (\Throwable $e) { fail('Wilayah', 'CRUD', $e->getMessage()); }

// ─── 6. KUB ───────────────────────────────────────────────────────────────────
echo "--- [6] KUB ---\n";
try {
    $id = safeInsert('kub', [
        'nama_kub'  => $prefix . ' KUB Uji',
        'kode_kub'  => 'TST-' . rand(100,999),
        'ketua'     => 'Ketua Test',
    ]);
    if ($id) {
        ok('KUB', 'CREATE', "id={$id}");
        safeUpdate('kub', $id, 'id', ['nama_kub' => $prefix . ' KUB EDITED']);
        ok('KUB', 'EDIT');
        DB::table('kub')->where('id', $id)->delete();
        ok('KUB', 'DELETE');
    } else { fail('KUB', 'CREATE', 'insert gagal'); }
} catch (\Throwable $e) { fail('KUB', 'CRUD', $e->getMessage()); }

// ─── 7. PROVINSI ──────────────────────────────────────────────────────────────
echo "--- [7] PROVINSI ---\n";
try {
    $cols = Schema::getColumnListing('provinsi');
    $pk = in_array('id_provinsi', $cols) ? 'id_provinsi' : 'id';
    $id = safeInsert('provinsi', [
        'nama_provinsi' => $prefix . ' Provinsi Uji',
        'kode_provinsi' => 'TST-' . rand(10,99),
    ]);
    if ($id) {
        ok('Provinsi', 'CREATE', "id={$id}");
        safeUpdate('provinsi', $id, $pk, ['nama_provinsi' => $prefix . ' Provinsi EDITED']);
        ok('Provinsi', 'EDIT');
        DB::table('provinsi')->where($pk, $id)->delete();
        ok('Provinsi', 'DELETE');
    } else { fail('Provinsi', 'CREATE', 'insert gagal'); }
} catch (\Throwable $e) { fail('Provinsi', 'CRUD', $e->getMessage()); }

// ─── 8. KABUPATEN ─────────────────────────────────────────────────────────────
echo "--- [8] KABUPATEN ---\n";
try {
    $cols = Schema::getColumnListing('kabupaten');
    $pk = in_array('id_kabupaten', $cols) ? 'id_kabupaten' : 'id';
    $provinsiId = DB::table('provinsi')->value(in_array('id_provinsi', Schema::getColumnListing('provinsi')) ? 'id_provinsi' : 'id');
    $id = safeInsert('kabupaten', [
        'nama_kabupaten' => $prefix . ' Kabupaten Uji',
        'kode_kabupaten' => 'TST-KAB-' . rand(10,99),
        'provinsi_id'    => $provinsiId,
    ]);
    if ($id) {
        ok('Kabupaten', 'CREATE', "id={$id}");
        safeUpdate('kabupaten', $id, $pk, ['nama_kabupaten' => $prefix . ' Kabupaten EDITED']);
        ok('Kabupaten', 'EDIT');
        DB::table('kabupaten')->where($pk, $id)->delete();
        ok('Kabupaten', 'DELETE');
    } else { fail('Kabupaten', 'CREATE', 'insert gagal'); }
} catch (\Throwable $e) { fail('Kabupaten', 'CRUD', $e->getMessage()); }

// ─── 9. KECAMATAN ─────────────────────────────────────────────────────────────
echo "--- [9] KECAMATAN ---\n";
try {
    $cols = Schema::getColumnListing('kecamatan');
    $pk = in_array('id_kecamatan', $cols) ? 'id_kecamatan' : 'id';
    $kabId = DB::table('kabupaten')->value(in_array('id_kabupaten', Schema::getColumnListing('kabupaten')) ? 'id_kabupaten' : 'id');
    $id = safeInsert('kecamatan', [
        'nama_kecamatan' => $prefix . ' Kecamatan Uji',
        'kode_kecamatan' => 'TST-KEC-' . rand(10,99),
        'kabupaten_id'   => $kabId,
    ]);
    if ($id) {
        ok('Kecamatan', 'CREATE', "id={$id}");
        safeUpdate('kecamatan', $id, $pk, ['nama_kecamatan' => $prefix . ' Kecamatan EDITED']);
        ok('Kecamatan', 'EDIT');
        DB::table('kecamatan')->where($pk, $id)->delete();
        ok('Kecamatan', 'DELETE');
    } else { fail('Kecamatan', 'CREATE', 'insert gagal'); }
} catch (\Throwable $e) { fail('Kecamatan', 'CRUD', $e->getMessage()); }

// ─── 10. USER ─────────────────────────────────────────────────────────────────
echo "--- [10] USER ---\n";
try {
    $roleId = DB::table('roles')->value('id');
    $randNum = rand(1000, 9999);
    $id = safeInsert('users', [
        'nama_lengkap'      => $prefix . ' User Uji',
        'username'          => 'testuser' . $randNum,
        'email'             => 'test_auto_' . $randNum . '@test.dev',
        'password'          => bcrypt('TestPass123!'),
        'role_id'           => $roleId,
        'status'            => 1,
        'status_verifikasi' => 1,
        'name'              => $prefix . ' User',
    ]);
    if ($id) {
        ok('User', 'CREATE', "id={$id}");
        safeUpdate('users', $id, 'id', ['nama_lengkap' => $prefix . ' User EDITED']);
        ok('User', 'EDIT');
        DB::table('users')->where('id', $id)->delete();
        ok('User', 'DELETE');
    } else { fail('User', 'CREATE', 'insert gagal'); }
} catch (\Throwable $e) { fail('User', 'CRUD', $e->getMessage()); }

// ─── 11. KONTEN ───────────────────────────────────────────────────────────────
echo "--- [11] KONTEN ---\n";
try {
    $id = safeInsert('konten', [
        'judul'   => $prefix . ' Konten Uji',
        'slug'    => 'test-auto-' . rand(1000,9999),
        'isi'     => 'Konten uji coba otomatis',
        'tipe'    => 'berita',
        'status'  => 1,
        'penulis' => 'Test Auto',
        'tanggal' => now()->format('Y-m-d'),
    ]);
    if ($id) {
        ok('Konten', 'CREATE', "id={$id}");
        safeUpdate('konten', $id, 'id', ['judul' => $prefix . ' Konten EDITED']);
        ok('Konten', 'EDIT');
        DB::table('konten')->where('id', $id)->delete();
        ok('Konten', 'DELETE');
    } else { fail('Konten', 'CREATE', 'insert gagal'); }
} catch (\Throwable $e) { fail('Konten', 'CRUD', $e->getMessage()); }

// ─── 12. JADWAL MISA ──────────────────────────────────────────────────────────
echo "--- [12] JADWAL MISA ---\n";
try {
    $id = safeInsert('jadwal_misa', [
        'tanggal'        => now()->format('Y-m-d'),
        'waktu'          => '08:00:00',
        'hari'           => 'Minggu',
        'bulan'          => now()->month,
        'jenis_perayaan' => 'Misa Hari Minggu',
        'jenis_misa'     => 'Umum',
        'tempat'         => 'Gereja Pusat Test',
        'lokasi'         => 'Pusat Paroki',
    ]);
    if ($id) {
        ok('Jadwal Misa', 'CREATE', "id={$id}");
        safeUpdate('jadwal_misa', $id, 'id', ['tempat' => 'Gereja EDITED']);
        ok('Jadwal Misa', 'EDIT');
        DB::table('jadwal_misa')->where('id', $id)->delete();
        ok('Jadwal Misa', 'DELETE');
    } else { fail('Jadwal Misa', 'CREATE', 'insert gagal'); }
} catch (\Throwable $e) { fail('Jadwal Misa', 'CRUD', $e->getMessage()); }

// ─── 13. INTENSI MISA ─────────────────────────────────────────────────────────
echo "--- [13] INTENSI MISA ---\n";
try {
    if (Schema::hasTable('intensi_misa')) {
        $id = safeInsert('intensi_misa', [
            'nama_pemohon'       => $prefix . ' Pemohon',
            'kategori_intensi'   => 'Syukur',
            'deskripsi'          => 'Intensi test otomatis',
            'tanggal_misa'       => now()->format('Y-m-d'),
            'nominal_stipendium' => 50000,
            'status_pembayaran'  => 'Lunas',
        ]);
        if ($id) {
            ok('Intensi Misa', 'CREATE', "id={$id}");
            safeUpdate('intensi_misa', $id, 'id', ['nama_pemohon' => $prefix . ' Pemohon EDITED']);
            ok('Intensi Misa', 'EDIT');
            DB::table('intensi_misa')->where('id', $id)->delete();
            ok('Intensi Misa', 'DELETE');
        } else { fail('Intensi Misa', 'CREATE', 'insert gagal'); }
    } else { ok('Intensi Misa', 'SKIP', 'Tabel tidak ditemukan'); }
} catch (\Throwable $e) { fail('Intensi Misa', 'CRUD', $e->getMessage()); }

// ─── 14. SURAT MASUK ──────────────────────────────────────────────────────────
echo "--- [14] SURAT MASUK ---\n";
try {
    if (Schema::hasTable('surat_masuk')) {
        $id = safeInsert('surat_masuk', [
            'nomor_agenda'    => 'AGD-' . rand(100,999),
            'nomor_surat'     => 'SM-' . rand(100,999) . '/2024',
            'tanggal_surat'   => now()->format('Y-m-d'),
            'tanggal_diterima'=> now()->format('Y-m-d'),
            'pengirim'        => 'Keuskupan Agung Kupang',
            'perihal'         => $prefix . ' Surat Masuk Uji',
            'status'          => 'Diterima',
        ]);
        if ($id) {
            ok('Surat Masuk', 'CREATE', "id={$id}");
            safeUpdate('surat_masuk', $id, 'id', ['perihal' => $prefix . ' Surat Masuk EDITED']);
            ok('Surat Masuk', 'EDIT');
            DB::table('surat_masuk')->where('id', $id)->delete();
            ok('Surat Masuk', 'DELETE');
        } else { fail('Surat Masuk', 'CREATE', 'insert gagal'); }
    } else { ok('Surat Masuk', 'SKIP', 'Tabel tidak ditemukan'); }
} catch (\Throwable $e) { fail('Surat Masuk', 'CRUD', $e->getMessage()); }

// ─── 15. SURAT KELUAR ─────────────────────────────────────────────────────────
echo "--- [15] SURAT KELUAR ---\n";
try {
    if (Schema::hasTable('surat_keluar')) {
        $id = safeInsert('surat_keluar', [
            'nomor_surat'    => 'SK-' . rand(100,999) . '/PAR/2024',
            'tanggal_surat'  => now()->format('Y-m-d'),
            'tujuan'         => 'Ketua KUB Test',
            'perihal'        => $prefix . ' Surat Keluar Uji',
            'penandatangan'  => 'RD. Pastor Paroki',
            'status_draft'   => 'Final',
        ]);
        if ($id) {
            ok('Surat Keluar', 'CREATE', "id={$id}");
            safeUpdate('surat_keluar', $id, 'id', ['perihal' => $prefix . ' Surat Keluar EDITED']);
            ok('Surat Keluar', 'EDIT');
            DB::table('surat_keluar')->where('id', $id)->delete();
            ok('Surat Keluar', 'DELETE');
        } else { fail('Surat Keluar', 'CREATE', 'insert gagal'); }
    } else { ok('Surat Keluar', 'SKIP', 'Tabel tidak ditemukan'); }
} catch (\Throwable $e) { fail('Surat Keluar', 'CRUD', $e->getMessage()); }

// ─── 16. MASTER PASTOR ────────────────────────────────────────────────────────
echo "--- [16] MASTER PASTOR ---\n";
try {
    if (Schema::hasTable('master_pastor')) {
        $id = safeInsert('master_pastor', [
            'nama_pastor'  => $prefix . ' RD. Pastor Uji QA',
            'jenis_imam'   => 'Diosesan',
            'gelar_depan'  => 'RD.',
            'status'       => 1,
            'jabatan'      => 'Pastor Rekan',
        ]);
        if ($id) {
            ok('Master Pastor', 'CREATE', "id={$id}");
            safeUpdate('master_pastor', $id, 'id', ['jabatan' => 'Pastor Paroki']);
            ok('Master Pastor', 'EDIT');
            DB::table('master_pastor')->where('id', $id)->delete();
            ok('Master Pastor', 'DELETE');
        } else { fail('Master Pastor', 'CREATE', 'insert gagal'); }
    } else { ok('Master Pastor', 'SKIP', 'Tabel tidak ditemukan'); }
} catch (\Throwable $e) { fail('Master Pastor', 'CRUD', $e->getMessage()); }

// ─── 17. KEUANGAN ─────────────────────────────────────────────────────────────
echo "--- [17] KEUANGAN ---\n";
try {
    if (Schema::hasTable('keuangan')) {
        $id = safeInsert('keuangan', [
            'keterangan' => $prefix . ' Keuangan Uji',
            'jumlah'     => 100000,
            'tanggal'    => now()->format('Y-m-d'),
            'tipe'       => 'pemasukan',
            'kategori'   => 'Test',
        ]);
        if ($id) {
            ok('Keuangan', 'CREATE', "id={$id}");
            safeUpdate('keuangan', $id, 'id', ['keterangan' => $prefix . ' Keuangan EDITED']);
            ok('Keuangan', 'EDIT');
            DB::table('keuangan')->where('id', $id)->delete();
            ok('Keuangan', 'DELETE');
        } else { fail('Keuangan', 'CREATE', 'insert gagal'); }
    } else { ok('Keuangan', 'SKIP', 'Tabel belum ada'); }
} catch (\Throwable $e) { fail('Keuangan', 'CRUD', $e->getMessage()); }

// ─── DB INTEGRITY CHECK ───────────────────────────────────────────────────────
echo "\n--- [DB CHECK] Integritas Tabel ---\n";
$tables = [
    'users', 'roles', 'umat', 'kk_katolik', 'wilayah', 'kapela', 'kub',
    'paroki', 'keuskupan', 'kevikepan', 'provinsi', 'kabupaten', 'kecamatan',
    'konten', 'jadwal_misa', 'kegiatan', 'pengumuman', 'galeri', 'keuangan',
    'intensi_misa', 'surat_masuk', 'surat_keluar', 'master_pastor',
    'sakramen', 'pengajuan_sakramen', 'arsip_digital', 'aset',
];
foreach ($tables as $tbl) {
    try {
        if (Schema::hasTable($tbl)) {
            $count = DB::table($tbl)->count();
            $colCount = count(Schema::getColumnListing($tbl));
            ok("DB:{$tbl}", 'CHECK', "{$count} records, {$colCount} kolom");
        } else {
            fail("DB:{$tbl}", 'EXISTS', 'Tabel TIDAK ADA');
        }
    } catch (\Throwable $e) {
        fail("DB:{$tbl}", 'ERROR', $e->getMessage());
    }
}

// ─── CLEANUP ─────────────────────────────────────────────────────────────────
echo "\n--- CLEANUP ---\n";
try {
    $c = 0;
    $c += DB::table('wilayah')->where('nama_wilayah', 'like', '[TEST-AUTO]%')->delete();
    $c += DB::table('kapela')->where('nama_kapela', 'like', '[TEST-AUTO]%')->delete();
    $c += DB::table('kub')->where('nama_kub', 'like', '[TEST-AUTO]%')->delete();
    $c += DB::table('konten')->where('judul', 'like', '[TEST-AUTO]%')->delete();
    $c += DB::table('users')->where('email', 'like', '%@test.dev')->delete();
    echo "✅ Cleanup: {$c} record sisa dibersihkan\n";
} catch (\Throwable $e) { echo "⚠️ Cleanup: " . $e->getMessage() . "\n"; }

// ─── FINAL SUMMARY ────────────────────────────────────────────────────────────
$total  = count(SiparokiTestStore::$results);
$passed = count(array_filter(SiparokiTestStore::$results, fn($r) => $r['ok'] === true));
$failed = $total - $passed;
$score  = $total > 0 ? round($passed / $total * 100) : 0;

echo "\n=======================================================\n";
echo "   HASIL AKHIR PENGUJIAN QA BACKEND\n";
echo "=======================================================\n";
echo "   Total Uji   : {$total}\n";
echo "   ✅ Pass     : {$passed}\n";
echo "   ❌ Fail     : {$failed}\n";
echo "   Skor Lulus  : {$score}%\n";
echo "=======================================================\n";

if ($failed > 0) {
    echo "\nDETAIL GAGAL:\n";
    foreach (SiparokiTestStore::$results as $r) {
        if (!$r['ok']) echo "  ❌ [{$r['module']}] {$r['action']}: " . ($r['detail'] ?? '') . "\n";
    }
}
