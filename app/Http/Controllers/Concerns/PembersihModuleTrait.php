<?php

namespace App\Http\Controllers\Concerns;

use App\Models\DesaKelurahan;
use App\Models\Dekenat;
use App\Models\Kabupaten;
use App\Models\Kapela;
use App\Models\Kecamatan;
use App\Models\Keuskupan;
use App\Models\Kevikepan;
use App\Models\KkKatolik;
use App\Models\Kub;
use App\Models\Lingkungan;
use App\Models\Paroki;
use App\Models\Provinsi;
use App\Models\Sakramen;
use App\Models\Umat;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

trait PembersihModuleTrait
{

    public function pembersihSistem(\Illuminate\Http\Request $request)
    {
        $firstSegment = $request->segment(1) ?: 'superadmin';
        $roleMap = [
            'superadmin' => 'Super Admin',
            'paroki'     => 'Admin Paroki',
            'pastor'     => 'Pastor',
            'wilayah'    => 'Admin Wilayah',
            'kapela'     => 'Admin Kapela / Stasi',
            'kub'        => 'Ketua KUB',
            'bendahara'  => 'Bendahara',
            'penulis'    => 'Penulis',
            'umat'       => 'Umat',
        ];
        $resolvedRole = $roleMap[$firstSegment] ?? auth()->user()?->role?->nama_role ?? 'Super Admin';

        $uploads = public_path('uploads');
        $scanned = is_dir($uploads);
        $orphans = [];
        $totalFiles = 0;
        $totalSize = 0;

        if ($scanned) {
            $referenced = $this->pembersihCollectReferences();
            $files = $this->pembersihScan($uploads, $uploads);
            foreach ($files as $f) {
                $totalFiles++;
                $totalSize += $f['size'];
                $rel = ltrim(str_replace(DIRECTORY_SEPARATOR, '/', substr($f['path'], strlen($uploads))), '/');
                if (!isset($referenced[$rel]) && !isset($referenced[basename($rel)])) {
                    $orphans[] = [
                        'path' => $rel,
                        'size' => $f['size'],
                        'mtime' => $f['mtime'],
                    ];
                }
            }
        }

        $cacheInfo = [
            'cache'   => $this->pembersihDirSize(storage_path('framework/cache')),
            'views'   => $this->pembersihDirSize(storage_path('framework/views')),
            'sessions'=> $this->pembersihDirSize(storage_path('framework/sessions')),
            'logs'    => $this->pembersihDirSize(storage_path('logs')),
        ];

        return \Inertia\Inertia::render('Inertia/PembersihSistem', [
            'role'       => $resolvedRole,
            'prefix'     => $firstSegment,
            'orphans'    => $orphans,
            'totalFiles' => $totalFiles,
            'totalSize'  => $totalSize,
            'scanned'    => $scanned,
            'cacheInfo'  => $cacheInfo,
        ]);
    }

    public function pembersihSistemAksi(\Illuminate\Http\Request $request)
    {
        $action = $request->input('action');

        if ($action === 'clear_cache') {
            try {
                \Illuminate\Support\Facades\Cache::flush();
                \Illuminate\Support\Facades\Artisan::call('config:clear');
                \Illuminate\Support\Facades\Artisan::call('route:clear');
                \Illuminate\Support\Facades\Artisan::call('view:clear');
                \Illuminate\Support\Facades\Artisan::call('cache:clear');
                \Illuminate\Support\Facades\Artisan::call('optimize:clear');
            } catch (\Throwable $e) {
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => 'Gagal: ' . $e->getMessage()], 500);
                }
                return redirect()->back()->with('error', 'Gagal membersihkan cache: ' . $e->getMessage());
            }
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Seluruh cache aplikasi berhasil dibersihkan.']);
            }
            return redirect()->back()->with('success', 'Seluruh cache aplikasi berhasil dibersihkan.');
        }

        if ($action === 'delete_orphans') {
            $paths = (array) $request->input('paths', []);
            $uploads = rtrim(public_path('uploads'), '/');
            $deleted = 0;
            foreach ($paths as $p) {
                $p = ltrim(str_replace('..', '', (string) $p), '/');
                if ($p === '') {
                    continue;
                }
                $full = $uploads . '/' . $p;
                if (is_file($full) && str_starts_with($full, $uploads . '/')) {
                    @unlink($full);
                    $deleted++;
                }
            }
            \Illuminate\Support\Facades\Cache::forget('pembersih_references');
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'deleted' => $deleted, 'message' => "{$deleted} file yatim berhasil dihapus."]);
            }
            return redirect()->back()->with('success', $deleted . ' file yatim berhasil dihapus.');
        }

        return redirect()->back()->with('error', 'Aksi tidak dikenal.');
    }

    protected function pembersihScan(string $base, string $dir): array
    {
        $result = [];
        if (!is_dir($dir)) {
            return $result;
        }
        foreach (array_diff(scandir($dir), ['.', '..']) as $name) {
            $path = $dir . DIRECTORY_SEPARATOR . $name;
            if (is_dir($path)) {
                $result = array_merge($result, $this->pembersihScan($base, $path));
            } else {
                $result[] = [
                    'path' => $path,
                    'size' => filesize($path),
                    'mtime' => filemtime($path),
                ];
            }
        }
        return $result;
    }

    protected function pembersihCollectReferences(): array
    {
        $refs = [
            '.gitkeep' => true,
            '.htaccess' => true,
            'index.html' => true,
            'index.php' => true,
            'favicon.ico' => true,
            'logo.png' => true,
            'default.jpg' => true,
            'default-pastor.jpg' => true,
            'avatar-default.jpg' => true,
            'church-logo.png' => true,
            'logo-paroki.png' => true,
            'logo-keuskupan.png' => true,
        ];

        $pairs = [
            ['anggota_kategorial', 'foto'],
            ['arsip_digital', 'file_path'],
            ['artikel', 'gambar'],
            ['aset', 'foto'],
            ['aset', 'foto_aset'],
            ['aset', 'dokumen_aset'],
            ['backup_database', 'nama_file'],
            ['banner', 'gambar'],
            ['chat_pesan', 'lampiran'],
            ['defunctorum', 'foto'],
            ['direktori_dpp', 'foto'],
            ['direktori_katekis', 'foto'],
            ['direktori_misdinar', 'foto'],
            ['downloads', 'file_path'],
            ['downloads', 'file_name'],
            ['galeri', 'gambar'],
            ['galeri', 'og_image'],
            ['galeri_album', 'og_image'],
            ['galeri_item', 'file_foto'],
            ['jadwal_misa', 'foto'],
            ['kegiatan', 'gambar'],
            ['keuangan', 'bukti'],
            ['keuskupan', 'logo'],
            ['kk_katolik', 'foto'],
            ['konten', 'gambar'],
            ['konten', 'file_pdf'],
            ['konten', 'featured_image'],
            ['kronik_paroki', 'foto_utama'],
            ['kronik_paroki', 'dokumen_lampiran'],
            ['kronik_paroki', 'og_image'],
            ['lapak_produk', 'foto'],
            ['lapak_transaksi', 'bukti_pembayaran'],
            ['master_frater', 'foto'],
            ['master_pastor', 'foto'],
            ['master_uskup', 'foto'],
            ['metode_pembayaran', 'logo_bank'],
            ['metode_pembayaran', 'gambar_qris'],
            ['panduan_doa', 'gambar'],
            ['paroki', 'logo'],
            ['paroki', 'banner'],
            ['paroki', 'foto'],
            ['pembayaran_cetak_sakramen', 'bukti_transfer'],
            ['pengajuan_sakramen', 'bukti_pembayaran'],
            ['pengaturan_aplikasi', 'qris_image'],
            ['pengaturan_aplikasi', 'hero_video_file'],
            ['pengaturan_aplikasi', 'hero_video_poster'],
            ['pengaturan_aplikasi', 'video_header_file'],
            ['pengaturan_aplikasi', 'video_header_poster'],
            ['pengumuman', 'gambar'],
            ['penulis', 'foto'],
            ['peran_kategorial', 'foto'],
            ['peran_kategorial', 'ikon'],
            ['peran_kategorial', 'og_image'],
            ['profil_paroki', 'logo'],
            ['profil_paroki', 'foto_gereja'],
            ['profil_paroki', 'banner'],
            ['rapat', 'file_lampiran'],
            ['renungan_harian', 'gambar'],
            ['riwayat_pastor_paroki', 'foto'],
            ['sakramen', 'dokumen'],
            ['sakramen', 'lampiran'],
            ['sakramen_verifikasi', 'bukti_dokumen'],
            ['sambutan_pastor', 'foto_pastor'],
            ['sambutan_pastor', 'og_image'],
            ['sambutan_pastor', 'foto'],
            ['seo_pages', 'og_image'],
            ['setting', 'logo_kanan'],
            ['setting', 'logo_kiri'],
            ['slider_banner', 'gambar'],
            ['sliders', 'gambar'],
            ['stasi_kapela', 'foto'],
            ['surat_keluar', 'file_pdf'],
            ['surat_masuk', 'lampiran'],
            ['surat_masuk', 'file_scan'],
            ['umat', 'foto'],
            ['users', 'foto'],
            ['users', 'photo'],
            ['uskup', 'foto'],
            ['web_sliders', 'image'],
        ];

        foreach ($pairs as [$table, $col]) {
            if (!\Illuminate\Support\Facades\Schema::hasTable($table) || !\Illuminate\Support\Facades\Schema::hasColumn($table, $col)) {
                continue;
            }
            $rows = \Illuminate\Support\Facades\DB::table($table)->select($col)->whereNotNull($col)->get();
            foreach ($rows as $row) {
                $val = $row->$col;
                if (!$val) {
                    continue;
                }
                $cleanVal = ltrim(str_replace(DIRECTORY_SEPARATOR, '/', (string) $val), '/');
                $refs[basename($val)] = true;
                $refs[$cleanVal] = true;
                $refs[preg_replace('#^(public/)?uploads/#', '', $cleanVal)] = true;
            }
        }
        return $refs;
    }

    protected function pembersihDirSize(string $dir): int
    {
        if (!is_dir($dir)) {
            return 0;
        }
        $size = 0;
        foreach (array_diff(scandir($dir), ['.', '..']) as $name) {
            $path = $dir . DIRECTORY_SEPARATOR . $name;
            if (is_dir($path)) {
                $size += $this->pembersihDirSize($path);
            } else {
                $size += filesize($path);
            }
        }
        return $size;
    }
}
