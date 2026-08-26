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
            'role'       => $this->resolvePanelRole($request),
            'prefix'     => $this->resolvePanelPrefix($request),
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
                \Illuminate\Support\Facades\Artisan::call('config:clear');
                \Illuminate\Support\Facades\Artisan::call('route:clear');
                \Illuminate\Support\Facades\Artisan::call('view:clear');
                \Illuminate\Support\Facades\Artisan::call('cache:clear');
                \Illuminate\Support\Facades\Artisan::call('optimize:clear');
            } catch (\Throwable $e) {
                return redirect()->back()->with('error', 'Gagal membersihkan cache: ' . $e->getMessage());
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
        $refs = [];
        $pairs = [
            ['users', 'photo'], ['users', 'foto'],
            ['paroki', 'logo'], ['paroki', 'banner'], ['paroki', 'foto'],
            ['keuskupan', 'logo'],
            ['galeri', 'gambar'], ['galeri', 'file'],
            ['konten', 'gambar'], ['konten', 'featured_image'],
            ['umat', 'foto'],
            ['kk_katolik', 'foto'],
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
                $refs[basename($val)] = true;
                $refs[ltrim(str_replace(DIRECTORY_SEPARATOR, '/', (string) $val), '/')] = true;
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
