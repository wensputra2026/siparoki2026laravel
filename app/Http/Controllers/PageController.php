<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    private function getCommonData()
    {
        $version = Cache::get('global_view_data_version', 1);

        return Cache::remember("frontend.common_data.{$version}", 60, function () {
            $profil = DB::table('profil_paroki')->first();
            $pengaturan = DB::table('pengaturan_aplikasi')->first();
            $activeParoki = null;

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

            if (!empty($profil?->paroki_id)) {
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

            $namaParoki = $activeParoki->nama_paroki
                ?? $profil->nama_paroki
                ?? $pengaturan->nama_paroki
                ?? 'SIPAROKI';

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

            $pastorParoki = $activeParoki->nama_pastor_paroki_aktif
                ?? $profil->pastor_paroki
                ?? 'Pastor Paroki';

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
                'pastor_rekan' => $activeParoki->nama_pastor_rekan ?? $profil->pastor_rekan ?? 'Pastor Rekan',
                'frater' => $profil->frater ?? 'Frater TOP',
            ];
        });
    }

    public function beranda()
    {
        $common = $this->getCommonData();

        $jadwalMisa = Cache::remember('frontend.beranda.jadwal_misa', 300, fn () => DB::table('jadwal_misa')
            ->orderBy('tanggal')
            ->orderBy('jam_perayaan')
            ->limit(6)
            ->get());

        $pengumuman = Cache::remember('frontend.beranda.pengumuman', 300, fn () => DB::table('pengumuman')
            ->where('status', 1)
            ->latest('created_at')
            ->limit(6)
            ->get());

        $galeri = Cache::remember('frontend.beranda.galeri', 300, fn () => DB::table('galeri')
            ->where('status', 1)
            ->latest('created_at')
            ->limit(8)
            ->get());

        $artikel = Cache::remember('frontend.beranda.artikel', 300, fn () => DB::table('konten')
            ->where('status_publish', 'Publish')
            ->latest('tanggal_publish')
            ->limit(6)
            ->get());

        $stats = Cache::remember('frontend.beranda.stats', 300, fn () => [
            'total_kk' => DB::table('kk_katolik')->count(),
            'total_umat' => DB::table('umat')->count(),
            'total_kapela' => DB::table('kapela')->count() + DB::table('stasi_kapela')->count(),
            'total_kub' => DB::table('lingkungan')->count() + DB::table('kub')->count(),
        ]);

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
        $riwayat = \App\Models\RiwayatPastorParoki::orderBy('urutan')->get();
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

    public function kapela()
    {
        $common = $this->getCommonData();
        $kapela = DB::table('stasi_kapela')->get();
        if ($kapela->isEmpty()) {
            $kapela = DB::table('kapela')->get();
        }
        return view('pages.kapela', array_merge($common, compact('kapela')));
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
        $pastorList = \App\Models\MasterPastor::orderBy('id')->limit(12)->get();
        return view('pages.pelayan-pastoral', array_merge($common, compact('pastorList')));
    }

    public function sambutan()
    {
        $common = $this->getCommonData();
        return view('pages.sambutan', $common);
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
            $beritaQuery->where('kategori', $activeCategory);
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

        $categories = (clone $baseQuery)
            ->select('kategori', DB::raw('COUNT(*) as total'))
            ->whereNotNull('kategori')
            ->where('kategori', '!=', '')
            ->groupBy('kategori')
            ->orderBy('kategori')
            ->get();

        $recentNews = (clone $baseQuery)
            ->latest('tanggal_publish')
            ->latest('created_at')
            ->limit(5)
            ->get();

        $archive = (clone $baseQuery)
            ->selectRaw("DATE_FORMAT(COALESCE(tanggal_publish, created_at), '%Y-%m') as month_key, DATE_FORMAT(COALESCE(tanggal_publish, created_at), '%M %Y') as label, COUNT(*) as total")
            ->groupBy('month_key', 'label')
            ->orderByDesc('month_key')
            ->limit(8)
            ->get();

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

        $terkait = DB::table('konten')
            ->where('status_publish', 'Publish')
            ->where('id', '!=', $item->id)
            ->latest('tanggal_publish')
            ->limit(3)
            ->get();

        return view('pages.artikel-detail', array_merge($common, compact('item', 'terkait', 'detailType')));
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

    public function renungan()
    {
        $common = $this->getCommonData();
        $renungan = DB::table('renungan_harian')->latest('tanggal')->paginate(9);
        return view('pages.renungan', array_merge($common, compact('renungan')));
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
        $videos = DB::table('galeri')->whereNotNull('youtube_url')->paginate(12);
        return view('pages.video', array_merge($common, compact('videos')));
    }

    public function statistik()
    {
        $common = $this->getCommonData();
        $totalUmat = DB::table('umat')->count();
        $totalKK = DB::table('kk_katolik')->count();
        $totalKUB = DB::table('lingkungan')->count() + DB::table('kub')->count();
        $totalKapela = DB::table('kapela')->count() + DB::table('stasi_kapela')->count();
        $totalWilayah = DB::table('wilayah')->count();

        return view('pages.statistik', array_merge($common, compact('totalUmat', 'totalKK', 'totalKUB', 'totalKapela', 'totalWilayah')));
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
        $item = DB::table('downloads')
            ->where('id', $download)
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
        $item = DB::table('arsip_digital')
            ->where('id', $arsip)
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
