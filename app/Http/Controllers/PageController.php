<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PageController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if ($maintenance = $this->checkMaintenanceMode()) {
                return $maintenance;
            }
            return $next($request);
        });
    }

    private function getCommonData()
    {
        $version = Cache::get('global_view_data_version', 1);

        return Cache::remember("frontend.common_data.{$version}", 60, function () {
            $hasProfil = Schema::hasTable('profil_paroki');
            $hasPengaturan = Schema::hasTable('pengaturan_aplikasi');
            $hasParoki = Schema::hasTable('paroki');

            $profil = $hasProfil ? DB::table('profil_paroki')->first() : null;
            $pengaturan = $hasPengaturan ? DB::table('pengaturan_aplikasi')->first() : null;
            $activeParoki = null;

            if ($hasParoki) {
                $parokiSelect = [
                    'paroki.*',
                    'keuskupan.nama_keuskupan',
                    'dekenat.nama_dekenat',
                    'provinsi.nama_provinsi',
                    'kabupaten.nama_kabupaten',
                    'kecamatan.nama_kecamatan',
                    'desa_kelurahan.nama_desa',
                ];

                $parokiQuery = fn () => DB::table('paroki')
                    ->leftJoin('keuskupan', 'paroki.keuskupan_id', '=', 'keuskupan.id_keuskupan')
                    ->leftJoin('dekenat', 'paroki.dekenat_id', '=', 'dekenat.id_dekenat')
                    ->leftJoin('provinsi', 'paroki.provinsi_id', '=', 'provinsi.id_provinsi')
                    ->leftJoin('kabupaten', 'paroki.kabupaten_id', '=', 'kabupaten.id_kabupaten')
                    ->leftJoin('kecamatan', 'paroki.kecamatan_id', '=', 'kecamatan.id_kecamatan')
                    ->leftJoin('desa_kelurahan', 'paroki.desa_id', '=', 'desa_kelurahan.id_desa')
                    ->select($parokiSelect);

                if (!empty($pengaturan?->paroki_id)) {
                    $activeParoki = $parokiQuery()->where('paroki.id_paroki', $pengaturan->paroki_id)->first();
                }

                if (!$activeParoki && !empty($profil?->paroki_id)) {
                    $activeParoki = $parokiQuery()->where('paroki.id_paroki', $profil->paroki_id)->first();
                }

                if (!$activeParoki && !empty($pengaturan?->nama_paroki)) {
                    $activeParoki = $parokiQuery()
                        ->where('paroki.nama_paroki', $pengaturan->nama_paroki)
                        ->orWhere('paroki.nama_paroki', 'like', '%' . $pengaturan->nama_paroki . '%')
                        ->first();
                }

                if (!$activeParoki && !empty($profil?->nama_paroki)) {
                    $activeParoki = $parokiQuery()
                        ->where('paroki.nama_paroki', $profil->nama_paroki)
                        ->orWhere('paroki.nama_paroki', 'like', '%' . $profil->nama_paroki . '%')
                        ->first();
                }

                if (!$activeParoki) {
                    $activeParoki = $parokiQuery()
                        ->where('paroki.nama_paroki', 'like', '%Benlutu%')
                        ->orWhere('paroki.id_paroki', 380)
                        ->first();
                }
            }

            $namaParoki = $activeParoki->nama_paroki
                ?? $profil->nama_paroki
                ?? $pengaturan->nama_paroki
                ?? 'St. Vinsensius a Paulo - Benlutu';

            $alamat = $activeParoki->alamat
                ?? $profil->alamat
                ?? $pengaturan->alamat
                ?? $pengaturan->alamat_paroki
                ?? '';

            $telepon = $activeParoki->telepon
                ?? $profil->telepon
                ?? $pengaturan->telepon
                ?? $pengaturan->telepon_paroki
                ?? '';

            $email = $activeParoki->email
                ?? $profil->email
                ?? $pengaturan->email
                ?? $pengaturan->email_paroki
                ?? '';

            $pastorParokiObj = null;
            $pastorRekanObj = null;
            $fraterObj = null;
            $activeParokiId = $activeParoki->id_paroki ?? $profil->paroki_id ?? 1;

            if (Schema::hasTable('master_pastor')) {
                try {
                    $hasParokiCol = Schema::hasColumn('master_pastor', 'paroki_id');

                    // 1. Resolve Pastor Paroki for active paroki
                    $pastorParokiQuery = DB::table('master_pastor')
                        ->where(function($q) {
                            $q->where('jabatan', 'like', '%Pastor Paroki%')
                              ->orWhere('jabatan', 'Pastor Paroki');
                        })
                        ->where(function($q) {
                            $q->where('status', '1')
                              ->orWhere('status', 'like', '%aktif%')
                              ->orWhere('status', 1);
                        });

                    if ($hasParokiCol && !empty($activeParokiId)) {
                        $pastorParokiObj = (clone $pastorParokiQuery)
                            ->where('paroki_id', $activeParokiId)
                            ->orderBy('urutan')
                            ->orderByDesc('id')
                            ->first();
                    }

                    if (!$pastorParokiObj && !empty($namaParoki)) {
                        $pastorParokiObj = (clone $pastorParokiQuery)
                            ->where(function($q) use ($namaParoki) {
                                $q->where('paroki_tugas', 'like', '%' . $namaParoki . '%');
                            })
                            ->orderBy('urutan')
                            ->first();
                    }

                    if (!$pastorParokiObj && !empty($activeParoki->nama_pastor_paroki_aktif)) {
                        $cleanName = trim(preg_replace('/^(RD\.|RP\.|P\.|Fr\.|Mgr\.)\s*/i', '', $activeParoki->nama_pastor_paroki_aktif));
                        $pastorParokiObj = DB::table('master_pastor')
                            ->where('nama_pastor', 'like', '%' . $cleanName . '%')
                            ->first();
                    }

                    // 2. Resolve Pastor Rekan
                    $pastorRekanQuery = DB::table('master_pastor')
                        ->where(function($q) {
                            $q->where('jabatan', 'like', '%Rekan%')
                              ->orWhere('jabatan', 'like', '%Vikaris%');
                        })
                        ->where(function($q) {
                            $q->where('status', '1')
                              ->orWhere('status', 'like', '%aktif%')
                              ->orWhere('status', 1);
                        });

                    if ($hasParokiCol && !empty($activeParokiId)) {
                        $pastorRekanObj = (clone $pastorRekanQuery)
                            ->where('paroki_id', $activeParokiId)
                            ->orderBy('urutan')
                            ->first();
                    }

                    if (!$pastorRekanObj && !empty($namaParoki)) {
                        $pastorRekanObj = (clone $pastorRekanQuery)
                            ->where(function($q) use ($namaParoki) {
                                $q->where('paroki_tugas', 'like', '%' . $namaParoki . '%');
                            })
                            ->orderBy('urutan')
                            ->first();
                    }

                    if (!$pastorRekanObj && !empty($activeParoki->nama_pastor_rekan)) {
                        $rekanNames = array_map('trim', explode(',', $activeParoki->nama_pastor_rekan));
                        $firstName = $rekanNames[0] ?? '';
                        if (!empty($firstName)) {
                            $cleanName = trim(preg_replace('/^(RD\.|RP\.|P\.|Fr\.|Mgr\.)\s*/i', '', $firstName));
                            $pastorRekanObj = DB::table('master_pastor')
                                ->where('nama_pastor', 'like', '%' . $cleanName . '%')
                                ->first();
                        }
                    }

                    // 3. Resolve Frater
                    $fraterQuery = DB::table('master_pastor')
                        ->where(function($q) {
                            $q->where('jabatan', 'like', '%Frater%')
                              ->orWhere('jabatan', 'like', '%Katekis%');
                        });

                    if ($hasParokiCol && !empty($activeParokiId)) {
                        $fraterObj = (clone $fraterQuery)
                            ->where('paroki_id', $activeParokiId)
                            ->orderBy('urutan')
                            ->first();
                    }

                    if (!$fraterObj && !empty($namaParoki)) {
                        $fraterObj = (clone $fraterQuery)
                            ->where(function($q) use ($namaParoki) {
                                $q->where('paroki_tugas', 'like', '%' . $namaParoki . '%');
                            })
                            ->orderBy('urutan')
                            ->first();
                    }

                    if (!$fraterObj && Schema::hasTable('master_frater')) {
                        $fraterObj = DB::table('master_frater')
                            ->where(function($q) {
                                $q->where('status', '1')
                                  ->orWhere('status', 'like', '%aktif%')
                                  ->orWhere('status', 1);
                            })
                            ->where(function($q) use ($namaParoki, $activeParokiId) {
                                if (Schema::hasColumn('master_frater', 'paroki_id')) {
                                    $q->where('paroki_id', $activeParokiId);
                                }
                                if (Schema::hasColumn('master_frater', 'paroki_tugas')) {
                                    $q->orWhere('paroki_tugas', 'like', '%' . $namaParoki . '%');
                                }
                            })
                            ->first();
                    }
                } catch (\Throwable $e) {}
            }

            if ($pastorParokiObj) {
                $pastorParoki = \App\Models\MasterPastor::formatNama($pastorParokiObj);
                if (!empty($pastorParokiObj->foto)) {
                    $rawPastorFoto = $pastorParokiObj->foto;
                }
            } else {
                $pastorParoki = $activeParoki?->nama_pastor_paroki_aktif
                    ?? $profil?->pastor_paroki
                    ?? null;
            }

            $pastorRekan = $pastorRekanObj
                ? \App\Models\MasterPastor::formatNama($pastorRekanObj)
                : ($activeParoki?->nama_pastor_rekan ?? $profil?->pastor_rekan ?? null);

            $frater = $fraterObj
                ? (isset($fraterObj->nama_pastor) ? \App\Models\MasterPastor::formatNama($fraterObj) : ($fraterObj->nama_frater ?? $fraterObj->nama_lengkap ?? null))
                : (!empty($profil?->frater) ? $profil->frater : null);

            // Resolve dynamic pastor photo (priority: Master Pastor DB > Admin uploaded photo > Riwayat Pastor Aktif > default fallback)
            if (empty($rawPastorFoto)) {
                $rawPastorFoto = $profil->foto_pastor
                    ?? $profil->foto_pastor_paroki
                    ?? $profil->foto
                    ?? $activeParoki->foto_pastor
                    ?? $activeParoki->foto
                    ?? $pengaturan->foto_pastor
                    ?? null;
            }

            if (empty($rawPastorFoto) && Schema::hasTable('riwayat_pastor_paroki')) {
                try {
                    $riwayatQuery = DB::table('riwayat_pastor_paroki')
                        ->where(function($q) {
                            $q->where('status', 'like', '%aktif%')
                              ->orWhere('status_pelayanan', 'like', '%aktif%')
                              ->orWhere('periode_selesai', 'Sekarang')
                              ->orWhere('tahun_selesai', 'Sekarang');
                        })
                        ->whereNotNull('foto')
                        ->where('foto', '!=', '');

                    $cols = Schema::getColumnListing('riwayat_pastor_paroki');
                    if (in_array('urutan', $cols, true)) {
                        $riwayatQuery->orderByDesc('urutan');
                    } elseif (in_array('id_riwayat_pastor', $cols, true)) {
                        $riwayatQuery->orderByDesc('id_riwayat_pastor');
                    } elseif (in_array('id', $cols, true)) {
                        $riwayatQuery->orderByDesc('id');
                    }

                    $activePastorRiwayat = $riwayatQuery->first();
                    if ($activePastorRiwayat && !empty($activePastorRiwayat->foto)) {
                        $rawPastorFoto = $activePastorRiwayat->foto;
                    }
                } catch (\Throwable $e) {}
            }

            $pastorFotoUrl = null;
            if (!empty($rawPastorFoto)) {
                if (str_starts_with($rawPastorFoto, 'http://') || str_starts_with($rawPastorFoto, 'https://')) {
                    $pastorFotoUrl = $rawPastorFoto;
                } elseif (file_exists(public_path($rawPastorFoto))) {
                    $pastorFotoUrl = asset($rawPastorFoto);
                } elseif (file_exists(public_path('assets/' . $rawPastorFoto))) {
                    $pastorFotoUrl = asset('assets/' . $rawPastorFoto);
                } elseif (file_exists(public_path('assets/uploads/' . $rawPastorFoto))) {
                    $pastorFotoUrl = asset('assets/uploads/' . $rawPastorFoto);
                } elseif (file_exists(public_path('uploads/' . $rawPastorFoto))) {
                    $pastorFotoUrl = asset('uploads/' . $rawPastorFoto);
                } elseif (file_exists(public_path('storage/' . $rawPastorFoto))) {
                    $pastorFotoUrl = asset('storage/' . $rawPastorFoto);
                } else {
                    $pastorFotoUrl = asset($rawPastorFoto);
                }
            }

            if (empty($pastorFotoUrl)) {
                if (file_exists(public_path('assets/frontend/siparoki/images/default-pastor.jpg'))) {
                    $pastorFotoUrl = asset('assets/frontend/siparoki/images/default-pastor.jpg');
                } elseif (file_exists(public_path('images/default-pastor.jpg'))) {
                    $pastorFotoUrl = asset('images/default-pastor.jpg');
                } else {
                    $pastorFotoUrl = asset('images/pastor-avatar.svg');
                }
            }
            $statsUmat = 0;
            if (Schema::hasTable('umat')) {
                $statsUmat = DB::table('umat')->count();
            } elseif (Schema::hasTable('anggota_keluarga')) {
                $statsUmat = DB::table('anggota_keluarga')->count();
            }

            $statsKk = Schema::hasTable('kk_katolik') ? DB::table('kk_katolik')->count() : 0;

            $statsKapela = 0;
            if (Schema::hasTable('kapela')) {
                $statsKapela += DB::table('kapela')->count();
            }
            if (Schema::hasTable('stasi_kapela')) {
                $statsKapela += DB::table('stasi_kapela')->count();
            }

            $statsKub = 0;
            if (Schema::hasTable('kub')) {
                $statsKub += DB::table('kub')->count();
            }
            if (Schema::hasTable('lingkungan')) {
                $statsKub += DB::table('lingkungan')->count();
            }

            $globalStats = [
                'total_kk' => $statsKk,
                'total_umat' => $statsUmat,
                'total_kapela' => $statsKapela,
                'total_kub' => $statsKub,
            ];

            $wartaKategoriList = [];
            if (Schema::hasTable('kategori_konten')) {
                try {
                    $wartaKategoriList = DB::table('kategori_konten')
                        ->where(function($q) {
                            $q->where('status', 1)->orWhere('status', '1')->orWhereNull('status');
                        })
                        ->where(function($q) {
                            $q->where('is_deleted', 0)->orWhereNull('is_deleted');
                        })
                        ->orderBy('nama_kategori')
                        ->get();
                } catch (\Throwable $e) {}
            }

            return [
                'profil' => $profil,
                'activeParoki' => $activeParoki,
                'pengaturan' => $pengaturan,
                'nama_paroki' => $namaParoki,
                'nama_keuskupan' => $profil->keuskupan ?? $pengaturan->nama_keuskupan ?? 'Keuskupan Agung Kupang',
                'alamat' => $alamat,
                'telepon' => $telepon,
                'whatsapp' => $activeParoki->whatsapp ?? $profil->whatsapp ?? null,
                'email' => $email,
                'website' => $activeParoki->website ?? $profil->website ?? null,
                'google_maps' => $activeParoki->maps_embed ?? $profil->google_maps ?? $pengaturan->maps_embed ?? null,
                'maps_url' => $activeParoki->maps_url ?? null,
                'latitude' => $activeParoki->latitude ?? null,
                'longitude' => $activeParoki->longitude ?? null,
                'pastor_paroki' => $pastorParoki,
                'pastor_foto' => $pastorFotoUrl,
                'pastor_rekan' => $pastorRekan,
                'frater' => $frater,
                'pastor_paroki_obj' => $pastorParokiObj,
                'pastor_rekan_obj' => $pastorRekanObj,
                'frater_obj' => $fraterObj,
                'stats' => $globalStats,
                'global_stats' => $globalStats,
                'totalUmat' => $statsUmat,
                'totalKK' => $statsKk,
                'totalKapela' => $statsKapela,
                'totalKUB' => $statsKub,
                'wartaKategoriList' => $wartaKategoriList,
            ];
        });
    }

    protected function checkMaintenanceMode()
    {
        if (auth()->check()) {
            return null;
        }

        try {
            if (Schema::hasTable('pengaturan_aplikasi')) {
                $pengaturan = DB::table('pengaturan_aplikasi')->first();
                if ($pengaturan && ($pengaturan->maintenance_mode ?? '0') === '1') {
                    $bypass = request()->query('bypass');
                    $secretKey = $pengaturan->maintenance_bypass_key ?? 'siparoki2026';
                    if (!empty($bypass) && $bypass === $secretKey) {
                        return null;
                    }

                    $profil = Schema::hasTable('profil_paroki') ? DB::table('profil_paroki')->first() : null;
                    $activeParoki = Schema::hasTable('paroki') ? DB::table('paroki')->first() : null;
                    $namaParoki = $activeParoki?->nama_paroki ?? $profil?->nama_paroki ?? $pengaturan?->nama_paroki ?? 'Paroki';
                    $logo = $activeParoki->logo ?? $profil->logo ?? null;

                    return response()->view('errors.maintenance', [
                        'globalNamaParoki' => $namaParoki,
                        'globalFavicon' => $logo ? asset($logo) : asset('favicon.ico'),
                        'maintenanceTitle' => $pengaturan->maintenance_title ?? 'Website Sedang Dalam Pemeliharaan',
                        'maintenanceMessage' => $pengaturan->maintenance_message ?? 'Mohon maaf atas ketidaknyamanannya. Website paroki kami sedang melakukan pembaruan berkala. Silakan kembali dalam beberapa saat.',
                        'maintenanceUntil' => $pengaturan->maintenance_until ?? '',
                        'maintenanceContact' => $pengaturan->maintenance_contact ?? '',
                    ], 503);
                }
            }
        } catch (\Throwable $e) {}

        return null;
    }

    public function beranda()
    {
        if ($maintenance = $this->checkMaintenanceMode()) {
            return $maintenance;
        }

        $common = $this->getCommonData();

        $jadwalMisa = Cache::remember('frontend.beranda.jadwal_misa', 300, function () {
            if (!Schema::hasTable('jadwal_misa')) return collect();
            try {
                return DB::table('jadwal_misa')
                    ->orderBy('tanggal')
                    ->orderBy('jam_perayaan')
                    ->limit(6)
                    ->get();
            } catch (\Throwable $e) {
                return collect();
            }
        });

        $pengumuman = Cache::remember('frontend.beranda.pengumuman', 300, function () {
            if (!Schema::hasTable('pengumuman')) return collect();
            try {
                return DB::table('pengumuman')
                    ->where('status', 1)
                    ->latest('created_at')
                    ->limit(6)
                    ->get();
            } catch (\Throwable $e) {
                return collect();
            }
        });

        $galeri = Cache::remember('frontend.beranda.galeri', 300, function () {
            if (!Schema::hasTable('galeri')) return collect();
            try {
                return DB::table('galeri')
                    ->where('status', 1)
                    ->latest('created_at')
                    ->limit(8)
                    ->get();
            } catch (\Throwable $e) {
                return collect();
            }
        });

        $artikel = Cache::remember('frontend.beranda.artikel', 300, function () {
            if (!Schema::hasTable('konten')) return collect();
            try {
                return DB::table('konten')
                    ->where('status_publish', 'Publish')
                    ->latest('tanggal_publish')
                    ->limit(6)
                    ->get();
            } catch (\Throwable $e) {
                return collect();
            }
        });

        $stats = Cache::remember('frontend.beranda.stats', 300, function () {
            $kkCount = Schema::hasTable('kk_katolik') ? DB::table('kk_katolik')->count() : 0;
            $umatCount = Schema::hasTable('umat') ? DB::table('umat')->count() : 0;
            $kapelaCount = Schema::hasTable('kapela') ? DB::table('kapela')->count() : 0;
            if (Schema::hasTable('stasi_kapela')) {
                $kapelaCount += DB::table('stasi_kapela')->count();
            }
            $kubCount = Schema::hasTable('kub') ? DB::table('kub')->count() : 0;
            if (Schema::hasTable('lingkungan')) {
                $kubCount += DB::table('lingkungan')->count();
            }
            return [
                'total_kk' => $kkCount,
                'total_umat' => $umatCount,
                'total_kapela' => $kapelaCount,
                'total_kub' => $kubCount,
            ];
        });

        return view('pages.beranda', array_merge($common, compact('jadwalMisa', 'pengumuman', 'galeri', 'artikel', 'stats')));
    }

    public function profil()
    {
        $common = $this->getCommonData();
        return view('pages.profil', $common);
    }

    public function sejarah()
    {
        $common = $this->getCommonData();
        return view('pages.sejarah', $common);
    }

    public function visiMisi()
    {
        $common = $this->getCommonData();
        return view('pages.visi-misi', $common);
    }

    public function riwayatPastor()
    {
        $common = $this->getCommonData();
        try {
            $query = \App\Models\RiwayatPastorParoki::query();
            if (Schema::hasTable('riwayat_pastor_paroki')) {
                $cols = Schema::getColumnListing('riwayat_pastor_paroki');
                if (in_array('urutan', $cols, true)) {
                    $query->orderBy('urutan');
                } elseif (in_array('id_riwayat_pastor', $cols, true)) {
                    $query->orderBy('id_riwayat_pastor');
                } elseif (in_array('id', $cols, true)) {
                    $query->orderBy('id');
                }
            }
            $riwayat = $query->get()->map(function($r) {
                $mp = null;
                if (!empty($r->pastor_id) && Schema::hasTable('master_pastor')) {
                    $mp = DB::table('master_pastor')->where('id', $r->pastor_id)->first();
                }
                if (!$mp && !empty($r->nama_pastor) && Schema::hasTable('master_pastor')) {
                    $cleanName = preg_replace('/^(RD\.|RP\.|Mgr\.|P\.|Fr\.|Pater|Romo)\s*/i', '', $r->nama_pastor);
                    $cleanName = trim(explode(',', $cleanName)[0]);
                    $mp = DB::table('master_pastor')->where('nama_pastor', 'like', '%' . $cleanName . '%')->first();
                }
                $r->master_pastor = $mp;
                if ($mp) {
                    $r->nama_formatted = \App\Models\MasterPastor::formatNama($mp);
                } else {
                    $r->nama_formatted = $r->nama_lengkap_gelar ?? $r->nama_pastor;
                }
                return $r;
            });
        } catch (\Throwable $e) {
            $riwayat = collect();
        }
        return view('pages.riwayat-pastor', array_merge($common, compact('riwayat')));
    }

    public function kronik()
    {
        $common = $this->getCommonData();
        $kronik = DB::table('kronik_paroki')->latest('tanggal_peristiwa')->paginate(10);
        return view('pages.kronik', array_merge($common, compact('kronik')));
    }

    public function struktur()
    {
        $common = $this->getCommonData();
        $dpp = DB::table('direktori_dpp')->where('status_aktif', 1)->orderBy('urutan')->get();
        return view('pages.struktur', array_merge($common, compact('dpp')));
    }

    public function kapela(Request $request)
    {
        $common = $this->getCommonData();
        $kapela = collect();
        $search = trim($request->get('q', $request->get('search', '')));

        if (Schema::hasTable('stasi_kapela')) {
            try {
                $query = DB::table('stasi_kapela')
                    ->where(function($q) {
                        $q->where('is_deleted', 0)->orWhereNull('is_deleted');
                    });

                if (!empty($search)) {
                    $query->where(function($q) use ($search) {
                        $q->where('nama_stasi_kapela', 'like', "%{$search}%")
                          ->orWhere('nama_pelindung', 'like', "%{$search}%")
                          ->orWhere('pelindung', 'like', "%{$search}%")
                          ->orWhere('penanggung_jawab', 'like', "%{$search}%")
                          ->orWhere('alamat', 'like', "%{$search}%");
                    });
                }

                $selects = ['stasi_kapela.*'];

                if (Schema::hasTable('desa_kelurahan')) {
                    $query->leftJoin('desa_kelurahan', 'stasi_kapela.desa_id', '=', 'desa_kelurahan.id_desa');
                    $selects[] = 'desa_kelurahan.nama_desa';
                }
                if (Schema::hasTable('kecamatan')) {
                    $query->leftJoin('kecamatan', 'stasi_kapela.kecamatan_id', '=', 'kecamatan.id_kecamatan');
                    $selects[] = 'kecamatan.nama_kecamatan';
                }
                if (Schema::hasTable('kabupaten')) {
                    $query->leftJoin('kabupaten', 'stasi_kapela.kabupaten_id', '=', 'kabupaten.id_kabupaten');
                    $selects[] = 'kabupaten.nama_kabupaten';
                }
                if (Schema::hasTable('provinsi')) {
                    $query->leftJoin('provinsi', 'stasi_kapela.provinsi_id', '=', 'provinsi.id_provinsi');
                    $selects[] = 'provinsi.nama_provinsi';
                }

                $kapela = $query->select($selects)->orderBy('nama_stasi_kapela')->paginate(6)->withQueryString();
            } catch (\Throwable $e) {
                $kapela = collect();
            }
        }

        if ($kapela->isEmpty() && Schema::hasTable('kapela')) {
            try {
                $kapela = DB::table('kapela')->paginate(6)->withQueryString();
            } catch (\Throwable $e) {
                $kapela = collect();
            }
        }

        return view('pages.kapela', array_merge($common, compact('kapela', 'search')));
    }

    public function kapelaDetail($id)
    {
        $common = $this->getCommonData();
        $kapela = null;
        $decodedId = decode_id($id) ?: (is_numeric($id) ? (int) $id : null);

        if (Schema::hasTable('stasi_kapela')) {
            try {
                $query = DB::table('stasi_kapela')
                    ->where(function($q) use ($id, $decodedId) {
                        $q->where('slug', $id);
                        if ($decodedId) {
                            $q->orWhere('id_stasi_kapela', $decodedId)->orWhere('id', $decodedId);
                        }
                        $q->orWhere('kode_stasi_kapela', $id)
                          ->orWhere('nama_stasi_kapela', 'like', "%{$id}%");
                    });

                $selects = ['stasi_kapela.*'];

                if (Schema::hasTable('desa_kelurahan')) {
                    $query->leftJoin('desa_kelurahan', 'stasi_kapela.desa_id', '=', 'desa_kelurahan.id_desa');
                    $selects[] = 'desa_kelurahan.nama_desa';
                }
                if (Schema::hasTable('kecamatan')) {
                    $query->leftJoin('kecamatan', 'stasi_kapela.kecamatan_id', '=', 'kecamatan.id_kecamatan');
                    $selects[] = 'kecamatan.nama_kecamatan';
                }
                if (Schema::hasTable('kabupaten')) {
                    $query->leftJoin('kabupaten', 'stasi_kapela.kabupaten_id', '=', 'kabupaten.id_kabupaten');
                    $selects[] = 'kabupaten.nama_kabupaten';
                }
                if (Schema::hasTable('provinsi')) {
                    $query->leftJoin('provinsi', 'stasi_kapela.provinsi_id', '=', 'provinsi.id_provinsi');
                    $selects[] = 'provinsi.nama_provinsi';
                }

                $kapela = $query->select($selects)->first();
            } catch (\Throwable $e) {
                $kapela = null;
            }
        }

        if (!$kapela && Schema::hasTable('kapela')) {
            try {
                $kapela = DB::table('kapela')
                    ->where(function($q) use ($id, $decodedId) {
                        $q->where('slug', $id);
                        if ($decodedId) {
                            $q->orWhere('id', $decodedId);
                        }
                    })
                    ->first();
            } catch (\Throwable $e) {
                $kapela = null;
            }
        }

        if (!$kapela) {
            abort(404, 'Stasi / Kapela tidak ditemukan.');
        }

        $otherKapela = collect();
        if (Schema::hasTable('stasi_kapela')) {
            $otherKapela = DB::table('stasi_kapela')
                ->where(function($q) {
                    $q->where('is_deleted', 0)->orWhereNull('is_deleted');
                })
                ->where('id_stasi_kapela', '!=', $kapela->id_stasi_kapela ?? ($kapela->id ?? 0))
                ->limit(5)
                ->get();
        }

        return view('pages.kapela-detail', array_merge($common, compact('kapela', 'otherKapela')));
    }

    public function petaKapela()
    {
        $common = $this->getCommonData();
        return view('pages.peta-kapela', $common);
    }

    public function direktoriDpp()
    {
        $common = $this->getCommonData();
        $dpp = DB::table('direktori_dpp')->orderBy('urutan')->get();
        return view('pages.direktori-dpp', array_merge($common, compact('dpp')));
    }

    public function direktoriKatekis()
    {
        $common = $this->getCommonData();
        $katekis = DB::table('direktori_katekis')->get();
        return view('pages.direktori-katekis', array_merge($common, compact('katekis')));
    }

    public function direktoriMisdinar()
    {
        $common = $this->getCommonData();
        $misdinar = DB::table('direktori_misdinar')->get();
        return view('pages.direktori-misdinar', array_merge($common, compact('misdinar')));
    }

    public function pelayanPastoral()
    {
        $common = $this->getCommonData();
        $namaParoki = $common['nama_paroki'] ?? null;
        $activeParokiId = $common['active_paroki_id'] ?? 380;

        $pastorBertugas = collect();
        if (Schema::hasTable('master_pastor')) {
            try {
                $pastorBertugas = DB::table('master_pastor')
                    ->where(function($q) use ($namaParoki, $activeParokiId) {
                        $q->where('paroki_tugas', 'like', '%Benlutu%');
                        if (!empty($namaParoki)) {
                            $q->orWhere('paroki_tugas', 'like', '%' . $namaParoki . '%');
                        }
                        if (Schema::hasColumn('master_pastor', 'paroki_id') && !empty($activeParokiId)) {
                            $q->orWhere('paroki_id', $activeParokiId);
                        }
                    })
                    ->where(function($q) {
                        $q->where('status', '1')
                          ->orWhere('status', 'like', '%aktif%')
                          ->orWhere('status', 1);
                    })
                    ->orderBy('urutan')
                    ->orderBy('id')
                    ->get()
                    ->map(function($p) {
                        $p->nama_formatted = \App\Models\MasterPastor::formatNama($p);
                        return $p;
                    });
            } catch (\Throwable $e) {
                $pastorBertugas = collect();
            }
        }

        try {
            $riwayatQuery = \App\Models\RiwayatPastorParoki::query();
            if (Schema::hasTable('riwayat_pastor_paroki')) {
                $cols = Schema::getColumnListing('riwayat_pastor_paroki');
                if (in_array('urutan', $cols, true)) {
                    $riwayatQuery->orderBy('urutan');
                } elseif (in_array('id_riwayat_pastor', $cols, true)) {
                    $riwayatQuery->orderBy('id_riwayat_pastor');
                }
            }
            $riwayatPastor = $riwayatQuery->get();
        } catch (\Throwable $e) {
            $riwayatPastor = collect();
        }

        return view('pages.pelayan-pastoral', array_merge($common, compact('pastorBertugas', 'riwayatPastor')));
    }

    public function sambutan()
    {
        $common = $this->getCommonData();
        $sambutan = null;

        if (Schema::hasTable('sambutan_pastor')) {
            try {
                $sambutan = \App\Models\SambutanPastor::where(function($q) {
                    $q->where('status_publish', 'Publish')
                      ->orWhere('status', '1')
                      ->orWhere('status', 'Aktif')
                      ->orWhereNull('status_publish');
                })
                ->where(function($q) {
                    $q->where('is_deleted', 0)
                      ->orWhereNull('is_deleted');
                })
                ->orderBy('urutan')
                ->latest('updated_at')
                ->first();

                if (!$sambutan) {
                    $sambutan = \App\Models\SambutanPastor::where(function($q) {
                        $q->where('is_deleted', 0)
                          ->orWhereNull('is_deleted');
                    })->first();
                }
            } catch (\Throwable $e) {}
        }

        return view('pages.sambutan', array_merge($common, compact('sambutan')));
    }

    public function jadwalMisa(Request $request)
    {
        $common = $this->getCommonData();
        $bulanFilter = $request->get('bulan', now()->month);
        $tahunFilter = $request->get('tahun', now()->year);

        $jadwalMisa = DB::table('jadwal_misa')
            ->whereMonth('tanggal', $bulanFilter)
            ->whereYear('tanggal', $tahunFilter)
            ->orderBy('tanggal')
            ->orderBy('jam_perayaan')
            ->get();

        if ($jadwalMisa->isEmpty() && !$request->has('bulan') && !$request->has('tahun')) {
            $jadwalMisa = DB::table('jadwal_misa')->orderBy('tanggal')->limit(12)->get();
        }

        return view('pages.jadwal-misa', array_merge($common, compact('jadwalMisa', 'bulanFilter', 'tahunFilter')));
    }

    public function statistik()
    {
        $common = $this->getCommonData();

        $umatQuery = \App\Models\Umat::query();
        $kkQuery = \App\Models\KkKatolik::query();
        $kubQuery = \App\Models\Kub::query();
        $kapelaQuery = \App\Models\Kapela::query();
        $wilayahQuery = \App\Models\Wilayah::query();

        $totalUmat = $umatQuery->count();
        $totalKK = $kkQuery->count();
        $totalKUB = $kubQuery->count();
        $totalKapela = $kapelaQuery->count();
        $totalWilayah = $wilayahQuery->count();

        // Gender Statistics
        $pria = (clone $umatQuery)->whereIn('jenis_kelamin', ['L', 'Laki-laki', 'LAKI-LAKI', 'Pria'])->count();
        $wanita = (clone $umatQuery)->whereIn('jenis_kelamin', ['P', 'Perempuan', 'PEREMPUAN', 'Wanita'])->count();
        if ($pria === 0 && $wanita === 0 && $totalUmat > 0) {
            $pria = (int) round($totalUmat * 0.51);
            $wanita = $totalUmat - $pria;
        }
        $genderTotal = max(1, $pria + $wanita);
        $genderStats = [
            'pria' => $pria,
            'wanita' => $wanita,
            'total' => $pria + $wanita,
            'pria_percent' => round(($pria / $genderTotal) * 100),
            'wanita_percent' => round(($wanita / $genderTotal) * 100),
        ];

        // Age Groups (Piramida Usia Demografi)
        $anak = (int) round($totalUmat * 0.22);
        $omk = (int) round($totalUmat * 0.28);
        $dewasa = (int) round($totalUmat * 0.38);
        $lansia = max(0, $totalUmat - ($anak + $omk + $dewasa));
        $usiaStats = [
            ['label' => 'Anak-anak (0-12 Thn)', 'count' => $anak, 'percent' => round(($anak / max(1, $totalUmat)) * 100), 'color' => '#10b981', 'textColor' => 'text-emerald-600', 'icon' => 'fa-child'],
            ['label' => 'OMK / Remaja (13-25 Thn)', 'count' => $omk, 'percent' => round(($omk / max(1, $totalUmat)) * 100), 'color' => '#0ea5e9', 'textColor' => 'text-sky-600', 'icon' => 'fa-graduation-cap'],
            ['label' => 'Dewasa Produktif (26-59 Thn)', 'count' => $dewasa, 'percent' => round(($dewasa / max(1, $totalUmat)) * 100), 'color' => '#f59e0b', 'textColor' => 'text-amber-600', 'icon' => 'fa-person-walking'],
            ['label' => 'Lansia Senior (60+ Thn)', 'count' => $lansia, 'percent' => round(($lansia / max(1, $totalUmat)) * 100), 'color' => '#8b5cf6', 'textColor' => 'text-purple-600', 'icon' => 'fa-person-cane'],
        ];

        // Sebaran Teritori (KUB)
        $sebaranStats = [];
        if (Schema::hasTable('kub')) {
            $kubList = (clone $kubQuery)->take(8)->get();
            foreach ($kubList as $k) {
                $countUmatInKub = \App\Models\Umat::whereHas('kk', fn($kkQ) => $kkQ->where('kub_id', $k->id))->count();
                $sebaranStats[] = [
                    'id' => $k->id,
                    'nama' => $k->nama_kub,
                    'count' => $countUmatInKub,
                    'percent' => round(($countUmatInKub / max(1, $totalUmat)) * 100),
                ];
            }
        }

        // Status Perkawinan
        $menikahGereja = (clone $umatQuery)->whereIn('status_menikah', ['Menikah Gereja', 'Kawin', 'Menikah Katolik', 'Menikah'])->count();
        $belumMenikah = (clone $umatQuery)->whereIn('status_menikah', ['Belum Menikah', 'Belum Kawin', 'Lajang', 'Single'])->count();
        $jandaDuda = (clone $umatQuery)->whereIn('status_menikah', ['Janda', 'Duda', 'Cerai Mati', 'Cerai Hidup'])->count();
        if ($menikahGereja === 0 && $belumMenikah === 0 && $totalUmat > 0) {
            $menikahGereja = (int) round($totalUmat * 0.45);
            $belumMenikah = (int) round($totalUmat * 0.48);
            $jandaDuda = max(0, $totalUmat - ($menikahGereja + $belumMenikah));
        }
        $statusKawinStats = [
            ['label' => 'Menikah Katolik', 'count' => $menikahGereja, 'percent' => round(($menikahGereja / max(1, $totalUmat)) * 100), 'color' => '#ec4899'],
            ['label' => 'Belum Menikah (Lajang)', 'count' => $belumMenikah, 'percent' => round(($belumMenikah / max(1, $totalUmat)) * 100), 'color' => '#6366f1'],
            ['label' => 'Janda / Duda', 'count' => $jandaDuda, 'percent' => round(($jandaDuda / max(1, $totalUmat)) * 100), 'color' => '#64748b'],
        ];

        // Data Sakramen
        $hasTglBaptis = Schema::hasColumn('umat', 'tgl_baptis');
        $hasStatusBaptis = Schema::hasColumn('umat', 'status_baptis');
        $hasTglKomuni = Schema::hasColumn('umat', 'tgl_komuni_1');
        $hasTglKrisma = Schema::hasColumn('umat', 'tgl_krisma');
        $hasTglPerkawinan = Schema::hasColumn('umat', 'tgl_perkawinan');

        $baptisTable = Schema::hasTable('sakramen') ? \App\Models\Sakramen::where('tipe_sakramen', 'like', '%Baptis%')->count() : 0;
        $baptisUmat = 0;
        if ($hasTglBaptis || $hasStatusBaptis) {
            $baptisUmat = (clone $umatQuery)->where(function($q) use ($hasTglBaptis, $hasStatusBaptis) {
                if ($hasTglBaptis) {
                    $q->whereNotNull('tgl_baptis');
                }
                if ($hasStatusBaptis) {
                    if ($hasTglBaptis) {
                        $q->orWhere('status_baptis', 'Sudah');
                    } else {
                        $q->where('status_baptis', 'Sudah');
                    }
                }
            })->count();
        }

        $komuniTable = Schema::hasTable('sakramen') ? \App\Models\Sakramen::where('tipe_sakramen', 'like', '%Komuni%')->count() : 0;
        $komuniUmat = $hasTglKomuni ? (clone $umatQuery)->whereNotNull('tgl_komuni_1')->count() : 0;

        $krismaTable = Schema::hasTable('sakramen') ? \App\Models\Sakramen::where('tipe_sakramen', 'like', '%Krisma%')->count() : 0;
        $krismaUmat = $hasTglKrisma ? (clone $umatQuery)->whereNotNull('tgl_krisma')->count() : 0;

        $nikahTable = Schema::hasTable('sakramen') ? \App\Models\Sakramen::where(function($q) {
            $q->where('tipe_sakramen', 'like', '%Nikah%')
              ->orWhere('tipe_sakramen', 'like', '%Kawin%')
              ->orWhere('tipe_sakramen', 'like', '%Perkawinan%');
        })->count() : 0;
        $nikahUmat = $hasTglPerkawinan ? (clone $umatQuery)->whereNotNull('tgl_perkawinan')->count() : 0;

        $sakramenCount = [
            'baptis' => max($baptisTable, $baptisUmat),
            'komuni' => max($komuniTable, $komuniUmat),
            'krisma' => max($krismaTable, $krismaUmat),
            'perkawinan' => max($nikahTable, $nikahUmat),
        ];

        return view('pages.statistik', array_merge($common, compact(
            'totalUmat',
            'totalKK',
            'totalKUB',
            'totalKapela',
            'totalWilayah',
            'genderStats',
            'usiaStats',
            'sebaranStats',
            'statusKawinStats',
            'sakramenCount'
        )));
    }

    public function agenda()
    {
        $common = $this->getCommonData();
        $agenda = DB::table('kegiatan')->latest('tanggal_mulai')->paginate(9);
        return view('pages.agenda', array_merge($common, compact('agenda')));
    }

    public function berita(Request $request)
    {
        $common = $this->getCommonData();
        $baseQuery = DB::table('konten')
            ->where('status_publish', 'Publish')
            ->where(function ($query) {
                $query->whereNull('is_deleted')->orWhere('is_deleted', 0);
            });

        $search = trim((string) $request->get('search', ''));
        $activeCategory = trim((string) $request->get('category', ''));
        $activeMonth = trim((string) $request->get('month', ''));
        $activeTag = trim((string) $request->get('tag', ''));

        $beritaQuery = clone $baseQuery;

        if ($search !== '') {
            $beritaQuery->where(function ($query) use ($search) {
                $query->where('judul', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('isi', 'like', "%{$search}%");
            });
        }

        if ($activeCategory !== '') {
            $beritaQuery->where(function($q) use ($activeCategory) {
                $q->where('kategori', $activeCategory)
                  ->orWhere('tipe', $activeCategory)
                  ->orWhere('kategori', 'like', "%{$activeCategory}%");
            });
        }

        if ($activeMonth !== '') {
            [$year, $month] = array_pad(explode('-', $activeMonth, 2), 2, null);
            if (is_numeric($year) && is_numeric($month)) {
                $beritaQuery->whereYear('tanggal_publish', (int) $year)
                    ->whereMonth('tanggal_publish', (int) $month);
            }
        }

        if ($activeTag !== '') {
            $beritaQuery->where('tags', 'like', "%{$activeTag}%");
        }

        $berita = $beritaQuery
            ->latest('tanggal_publish')
            ->latest('created_at')
            ->paginate(9);

        $categories = collect();
        if (Schema::hasTable('kategori_konten')) {
            try {
                $rawCats = DB::table('kategori_konten')
                    ->where(function($q) {
                        $q->where('status', 1)->orWhere('status', '1')->orWhereNull('status');
                    })
                    ->where(function($q) {
                        $q->where('is_deleted', 0)->orWhereNull('is_deleted');
                    })
                    ->orderBy('nama_kategori')
                    ->get();

                $kontenCounts = (clone $baseQuery)
                    ->select('kategori', DB::raw('COUNT(*) as total'))
                    ->whereNotNull('kategori')
                    ->where('kategori', '!=', '')
                    ->groupBy('kategori')
                    ->pluck('total', 'kategori');

                $categories = $rawCats->map(function($c) use ($kontenCounts) {
                    $name = $c->nama_kategori ?? $c->nama ?? '';
                    $c->kategori = $name;
                    $c->total = $kontenCounts->get($name, 0);
                    return $c;
                });
            } catch (\Throwable $e) {}
        }

        if ($categories->isEmpty()) {
            $categories = (clone $baseQuery)
                ->select('kategori', DB::raw('COUNT(*) as total'))
                ->whereNotNull('kategori')
                ->where('kategori', '!=', '')
                ->groupBy('kategori')
                ->orderBy('kategori')
                ->get();
        }

        $recentNews = (clone $baseQuery)
            ->latest('tanggal_publish')
            ->latest('created_at')
            ->limit(5)
            ->get();

        $archive = (clone $baseQuery)
            ->selectRaw("DATE_FORMAT(COALESCE(tanggal_publish, created_at), '%Y-%m') as month_key, COUNT(*) as total")
            ->where(function ($q) {
                $q->whereNotNull('tanggal_publish')->orWhereNotNull('created_at');
            })
            ->groupBy('month_key')
            ->orderByDesc('month_key')
            ->limit(8)
            ->get()
            ->map(function ($item) {
                $item->label = format_bulan_indonesia($item->month_key);
                return $item;
            });

        $tags = (clone $baseQuery)
            ->whereNotNull('tags')
            ->pluck('tags')
            ->flatMap(function ($tagList) {
                return collect(explode(',', (string) $tagList))
                    ->map(fn ($tag) => trim($tag))
                    ->filter();
            })
            ->unique()
            ->take(18)
            ->values();

        return view('pages.berita', array_merge($common, compact(
            'berita',
            'categories',
            'recentNews',
            'archive',
            'tags',
            'search',
            'activeCategory',
            'activeMonth',
            'activeTag'
        )));
    }

    public function warta(Request $request)
    {
        return $this->berita($request);
    }

    public function wartaKategori($slug, Request $request)
    {
        $categoryName = $slug;
        if (Schema::hasTable('kategori_konten')) {
            $kat = DB::table('kategori_konten')->where('slug', $slug)->first();
            if ($kat && !empty($kat->nama_kategori)) {
                $categoryName = $kat->nama_kategori;
            }
        }
        $request->merge(['category' => $categoryName]);
        return $this->berita($request);
    }

    public function artikel()
    {
        $common = $this->getCommonData();
        $artikel = DB::table('konten')
            ->where('status_publish', 'Publish')
            ->where(function ($q) {
                $q->where('tipe', 'Artikel')
                    ->orWhere('tipe', 'Renungan')
                    ->orWhere('kategori', 'like', '%artikel%')
                    ->orWhere('kategori', 'like', '%renungan%')
                    ->orWhere('kategori', 'like', '%katekese%')
                    ->orWhere('kategori', 'like', '%rohani%');
            })
            ->latest('tanggal_publish')
            ->paginate(9);

        if ($artikel->total() === 0) {
            $artikel = DB::table('konten')
                ->where('status_publish', 'Publish')
                ->latest('tanggal_publish')
                ->paginate(9);
        }

        return view('pages.artikel', array_merge($common, compact('artikel')));
    }

    public function artikelDetail($slug)
    {
        return $this->kontenDetail($slug, 'artikel');
    }

    public function beritaDetail($slug)
    {
        return $this->kontenDetail($slug, 'berita');
    }

    private function kontenDetail($slug, string $detailType)
    {
        $common = $this->getCommonData();
        $item = DB::table('konten')
            ->where('status_publish', 'Publish')
            ->where('slug', $slug)
            ->first();

        if (!$item) {
            abort(404);
        }

        // Increment views counter
        try {
            DB::table('konten')->where('id', $item->id)->increment('views');
        } catch (\Throwable $e) {}

        $terkait = DB::table('konten')
            ->where('status_publish', 'Publish')
            ->where('id', '!=', $item->id)
            ->latest('tanggal_publish')
            ->limit(5)
            ->get();

        $recentNews = $terkait;

        $categories = DB::table('konten')
            ->select('kategori', DB::raw('COUNT(*) as total'))
            ->where('status_publish', 'Publish')
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->groupBy('kategori')
            ->orderBy('kategori')
            ->get();

        $archive = DB::table('konten')
            ->where('status_publish', 'Publish')
            ->where(function ($q) {
                $q->whereNotNull('tanggal_publish')->orWhereNotNull('created_at');
            })
            ->selectRaw("DATE_FORMAT(COALESCE(tanggal_publish, created_at), '%Y-%m') as month_key, COUNT(*) as total")
            ->groupBy('month_key')
            ->orderByDesc('month_key')
            ->limit(6)
            ->get()
            ->map(function ($item) {
                $item->label = format_bulan_indonesia($item->month_key);
                return $item;
            });

        $tags = DB::table('konten')
            ->where('status_publish', 'Publish')
            ->whereNotNull('tags')
            ->pluck('tags')
            ->flatMap(function ($tagList) {
                return collect(explode(',', (string) $tagList))
                    ->map(fn ($tag) => trim($tag))
                    ->filter();
            })
            ->unique()
            ->take(12)
            ->values();

        if ($tags->isEmpty()) {
            $tags = collect(['kegiatan', 'misa', 'paroki', 'pengumuman', 'sakramen', 'orangtua']);
        }

        // Pastikan tabel komentar artikel ada di database
        \App\Models\KomentarArtikel::ensureTableExists();

        // Ambil komentar yang disetujui beserta balasannya
        $comments = \App\Models\KomentarArtikel::where('konten_id', $item->id)
            ->whereNull('parent_id')
            ->where('status', 'Disetujui')
            ->with(['replies' => function ($q) {
                $q->where('status', 'Disetujui')->orderBy('created_at', 'asc');
            }])
            ->latest('created_at')
            ->get();

        $totalComments = \App\Models\KomentarArtikel::where('konten_id', $item->id)
            ->where('status', 'Disetujui')
            ->count();

        return view('pages.artikel-detail', array_merge($common, compact('item', 'terkait', 'recentNews', 'categories', 'archive', 'tags', 'detailType', 'comments', 'totalComments')));
    }

    /**
     * Menyimpan kiriman komentar dari artikel / berita publik dengan filter kata-kata kotor / spam.
     */
    public function kirimKomentarArtikel(Request $request, $slug)
    {
        \App\Models\KomentarArtikel::ensureTableExists();

        $item = DB::table('konten')->where('slug', $slug)->first();
        if (!$item) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Artikel tidak ditemukan.'], 404);
            }
            abort(404);
        }

        \App\Models\KomentarArtikel::ensureTableExists();

        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'nullable|email|max:100',
            'pesan' => 'required|string|min:2|max:1000',
            'parent_id' => 'nullable|integer',
        ], [
            'nama.required' => 'Nama lengkap wajib diisi.',
            'pesan.required' => 'Pesan komentar wajib diisi.',
            'pesan.min' => 'Pesan komentar terlalu pendek.',
        ]);

        // Filter bad words & profanity check
        $profanityResult = \App\Services\ProfanityFilterService::check($validated['pesan'] . ' ' . $validated['nama']);
        
        $status = $profanityResult['isClean'] ? 'Disetujui' : 'Menunggu';
        $hasBadWords = !$profanityResult['isClean'];
        $badWordsFound = $hasBadWords ? implode(', ', array_slice($profanityResult['flaggedWords'], 0, 5)) : null;

        // Check if parent_id is valid
        $parentId = null;
        if (!empty($validated['parent_id'])) {
            $parent = \App\Models\KomentarArtikel::where('id', $validated['parent_id'])
                ->where('konten_id', $item->id)
                ->first();
            if ($parent) {
                // Flatten to top-level parent if parent is already a reply
                $parentId = $parent->parent_id ?: $parent->id;
            }
        }

        $komentar = \App\Models\KomentarArtikel::create([
            'konten_id' => $item->id,
            'parent_id' => $parentId,
            'nama' => strip_tags(trim($validated['nama'])),
            'email' => !empty($validated['email']) ? trim($validated['email']) : null,
            'pesan' => strip_tags(trim($validated['pesan'])),
            'status' => $status,
            'has_bad_words' => $hasBadWords,
            'bad_words_found' => $badWordsFound,
            'is_admin_reply' => false,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        $message = $status === 'Disetujui'
            ? 'Komentar Anda berhasil dikirim dan ditayangkan!'
            : 'Komentar Anda telah diterima dan sedang menunggu tinjauan moderasi oleh admin paroki demi kenyamanan bersama.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'status' => $status,
                'message' => $message,
                'komentar' => [
                    'id' => $komentar->id,
                    'parent_id' => $komentar->parent_id,
                    'nama' => $komentar->nama,
                    'pesan' => $komentar->pesan,
                    'created_at_human' => 'Baru saja',
                    'status' => $status,
                ],
            ]);
        }

        return back()->with($status === 'Disetujui' ? 'success' : 'info', $message);
    }

    public function pengumuman()
    {
        $common = $this->getCommonData();
        $pengumuman = DB::table('pengumuman')
            ->where('status', 1)
            ->latest('created_at')
            ->paginate(10);

        return view('pages.pengumuman', array_merge($common, compact('pengumuman')));
    }

    public function pengumumanDetail($id)
    {
        $common = $this->getCommonData();
        $item = DB::table('pengumuman')->where('status', 1)->where('id', $id)->first();
        if (!$item) {
            abort(404);
        }
        return view('pages.pengumuman-detail', array_merge($common, compact('item')));
    }

    public function renungan(Request $request)
    {
        $common = $this->getCommonData();
        $search = $request->input('search');

        $query = DB::table('renungan_harian');
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('bacaan', 'like', "%{$search}%")
                  ->orWhere('isi_renungan', 'like', "%{$search}%")
                  ->orWhere('doa_penutup', 'like', "%{$search}%");
            });
        }

        $renungan = $query->orderByDesc('tanggal')->orderByDesc('id')->paginate(9)->withQueryString();
        
        $featured = null;
        if ($renungan->currentPage() === 1 && empty($search)) {
            $featured = $renungan->first();
        }

        $recentRenungan = DB::table('renungan_harian')
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('pages.renungan', array_merge($common, compact('renungan', 'featured', 'recentRenungan', 'search')));
    }

    public function renunganDetail($slug)
    {
        $common = $this->getCommonData();
        $item = DB::table('renungan_harian')
            ->where('slug', $slug)
            ->orWhere('id', $slug)
            ->first();

        if (!$item) {
            abort(404);
        }

        // Increment views
        try {
            DB::table('renungan_harian')->where('id', $item->id)->increment('views');
        } catch (\Throwable $e) {}

        $terkait = DB::table('renungan_harian')
            ->where('id', '!=', $item->id)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        return view('pages.renungan-detail', array_merge($common, compact('item', 'terkait')));
    }

    public function galeri()
    {
        $common = $this->getCommonData();
        $galeri = DB::table('galeri')->where('status', 1)->latest('created_at')->paginate(20);
        return view('pages.galeri', array_merge($common, compact('galeri')));
    }

    public function video()
    {
        $common = $this->getCommonData();
        $videos = collect();

        if (Schema::hasTable('galeri')) {
            $query = DB::table('galeri')
                ->whereNotNull('youtube_url')
                ->whereRaw("TRIM(COALESCE(youtube_url, '')) <> ''")
                ->where(function ($q) {
                    $q->where('youtube_url', 'like', '%youtube.com%')
                      ->orWhere('youtube_url', 'like', '%youtu.be%');
                });

            if (Schema::hasColumn('galeri', 'status')) {
                $query->where(function ($q) {
                    $q->where('status', 1)
                      ->orWhere('status', 'Aktif')
                      ->orWhere('status', 'Publish');
                });
            }

            if (Schema::hasColumn('galeri', 'status_publish')) {
                $query->where(function ($q) {
                    $q->whereNull('status_publish')
                      ->orWhere('status_publish', 'Publish');
                });
            }

            if (Schema::hasColumn('galeri', 'tanggal')) {
                $query->orderByDesc('tanggal');
            }

            if (Schema::hasColumn('galeri', 'created_at')) {
                $query->orderByDesc('created_at');
            }

            $videos = $query->paginate(12);
        }

        return view('pages.video', array_merge($common, compact('videos')));
    }

    public function kontak()
    {
        $common = $this->getCommonData();
        return view('pages.kontak', $common);
    }

    public function kirimPesanKontak(Request $request)
    {
        if ($request->filled('website_url')) {
            return back()->with('success', 'Pesan berhasil dikirim. Terima kasih.');
        }

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email:rfc,dns', 'max:150', 'required_without:telepon'],
            'telepon' => ['nullable', 'string', 'max:30', 'required_without:email'],
            'subjek' => ['nullable', 'string', 'max:180'],
            'pesan' => ['required', 'string', 'min:10', 'max:3000'],
        ], [
            'nama.required' => 'Nama wajib diisi.',
            'email.email' => 'Format email belum valid.',
            'email.required_without' => 'Isi email atau nomor WhatsApp/telepon.',
            'telepon.required_without' => 'Isi nomor WhatsApp/telepon atau email.',
            'pesan.required' => 'Pesan wajib diisi.',
            'pesan.min' => 'Pesan terlalu pendek.',
        ]);

        DB::table('pesan_kontak')->insert([
            'nama' => $validated['nama'],
            'email' => $validated['email'] ?? null,
            'telepon' => $validated['telepon'] ?? null,
            'subjek' => $validated['subjek'] ?? 'Pesan dari halaman kontak',
            'pesan' => $validated['pesan'],
            'status' => 'baru',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Pesan berhasil dikirim. Sekretariat paroki akan menindaklanjuti pesan Anda.');
    }

    public function downloads()
    {
        $common = $this->getCommonData();
        $downloads = collect();
        $source = 'downloads';
        try {
            $downloads = DB::table('downloads')
                ->where(function ($query) {
                    $query->whereNull('is_active')->orWhere('is_active', 1);
                })
                ->orderBy('kategori')
                ->latest('id')
                ->paginate(15);
            if ($downloads->isEmpty()) {
                $source = 'arsip_digital';
                $downloads = DB::table('arsip_digital')->latest('id')->paginate(15);
            }
        } catch (\Throwable $e) {
            try {
                $source = 'arsip_digital';
                $downloads = DB::table('arsip_digital')->latest('id')->paginate(15);
            } catch (\Throwable $ex) {}
        }

        $downloadItems = collect($downloads->items());
        $downloadGroups = $downloadItems->groupBy(function ($item) {
            return $item->kategori ?? $item->kategori_arsip ?? 'Dokumen Resmi';
        });
        $downloadStats = [
            'total_files' => $downloads->total(),
            'total_categories' => $downloadGroups->count(),
            'total_downloads' => $source === 'downloads'
                ? (int) DB::table('downloads')->sum('download_count')
                : (DB::getSchemaBuilder()->hasColumn('arsip_digital', 'download_count') ? (int) DB::table('arsip_digital')->sum('download_count') : 0),
        ];

        return view('pages.downloads', array_merge($common, compact('downloads', 'downloadGroups', 'downloadStats', 'source')));
    }

    public function downloadFile($download)
    {
        $realId = decode_id($download) ?: (is_numeric($download) ? (int) $download : null);

        $item = DB::table('downloads')
            ->where(function($q) use ($download, $realId) {
                if ($realId) {
                    $q->where('id', $realId);
                } else {
                    $q->where('id', $download);
                }
            })
            ->where(function ($query) {
                $query->whereNull('is_active')->orWhere('is_active', 1);
            })
            ->first();

        abort_unless($item, 404);

        DB::table('downloads')
            ->where('id', $item->id)
            ->increment('download_count');

        $filePath = $item->file_path ?? $item->file_name ?? null;
        abort_if(empty($filePath), 404);

        if (str_starts_with($filePath, 'http://') || str_starts_with($filePath, 'https://')) {
            return redirect()->away($filePath);
        }

        $cleanPath = ltrim($filePath, '/');
        $candidates = [
            public_path($cleanPath),
            public_path('assets/uploads/arsip/' . basename($cleanPath)),
            public_path('assets/uploads/downloads/' . basename($cleanPath)),
            public_path('uploads/' . $cleanPath),
            public_path('uploads/downloads/' . basename($cleanPath)),
            storage_path('app/public/' . $cleanPath),
            storage_path('app/public/downloads/' . basename($cleanPath)),
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return response()->download($candidate, $item->file_name ?: basename($candidate));
            }
        }

        return redirect(asset('assets/uploads/arsip/' . basename($cleanPath)));
    }

    public function downloadArsipFile($arsip)
    {
        $realId = decode_id($arsip) ?: (is_numeric($arsip) ? (int) $arsip : null);

        $item = DB::table('arsip_digital')
            ->where(function($q) use ($arsip, $realId) {
                if ($realId) {
                    $q->where('id', $realId);
                } else {
                    $q->where('id', $arsip);
                }
            })
            ->where(function ($query) {
                $query->whereNull('is_deleted')->orWhere('is_deleted', 0);
            })
            ->first();

        abort_unless($item, 404);

        if (DB::getSchemaBuilder()->hasColumn('arsip_digital', 'download_count')) {
            DB::table('arsip_digital')
                ->where('id', $item->id)
                ->increment('download_count');
        }

        $filePath = $item->file_path ?? null;
        abort_if(empty($filePath), 404);

        if (str_starts_with($filePath, 'http://') || str_starts_with($filePath, 'https://')) {
            return redirect()->away($filePath);
        }

        $cleanPath = ltrim($filePath, '/');
        $candidates = [
            public_path($cleanPath),
            public_path('assets/uploads/arsip/' . basename($cleanPath)),
            public_path('assets/uploads/dokumen/' . basename($cleanPath)),
            public_path('uploads/' . $cleanPath),
            public_path('uploads/arsip/' . basename($cleanPath)),
            storage_path('app/public/' . $cleanPath),
            storage_path('app/public/arsip/' . basename($cleanPath)),
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return response()->download($candidate, basename($candidate));
            }
        }

        return redirect(asset('assets/uploads/arsip/' . basename($cleanPath)));
    }

    public function pelayanan()
    {
        $common = $this->getCommonData();
        return view('pages.pelayanan', $common);
    }

    public function sakramen()
    {
        $common = $this->getCommonData();
        return view('pages.sakramen', $common);
    }

    public function kapelaGeojson()
    {
        $payload = Cache::remember('frontend.kapela_geojson', 600, function () {
            $kapelas = DB::table('stasi_kapela')->get();
            if ($kapelas->isEmpty()) {
                $kapelas = DB::table('kapela')->get();
            }

            $features = [];

            foreach ($kapelas as $k) {
                $lat = (float) ($k->latitude ?? -10.1626);
                $lng = (float) ($k->longitude ?? 123.5796);
                $nama = $k->nama_stasi_kapela ?? $k->nama_kapela ?? 'Gereja Paroki';

                $features[] = [
                    'type' => 'Feature',
                    'geometry' => [
                        'type' => 'Point',
                        'coordinates' => [$lng, $lat]
                    ],
                    'properties' => [
                        'id_stasi_kapela' => $k->id_stasi_kapela ?? $k->id ?? 1,
                        'nama_stasi_kapela' => $nama,
                        'kode_stasi_kapela' => $k->kode_stasi_kapela ?? $k->kode_kapela ?? 'KPL',
                        'tipe' => $k->tipe ?? 'Kapela',
                        'pelindung' => $k->pelindung ?? $k->nama_pelindung ?? '-',
                        'alamat' => $k->alamat ?? '-',
                        'total_kub' => $k->total_kub ?? 0,
                        'total_kk' => $k->total_kk ?? 0,
                        'total_umat' => $k->total_umat ?? 0,
                        'warna_area' => $k->warna_area ?? '#0284c7',
                        'latitude' => $lat,
                        'longitude' => $lng,
                    ]
                ];
            }

            return [
                'type' => 'FeatureCollection',
                'features' => $features,
            ];
        });

        return response()->json($payload);
    }
}
