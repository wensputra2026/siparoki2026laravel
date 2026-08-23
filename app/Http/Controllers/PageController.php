<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PageController extends Controller
{
    private function getCommonData()
    {
        $profil = DB::table('profil_paroki')->first();
        $pengaturan = DB::table('pengaturan_aplikasi')->first();

        return [
            'profil' => $profil,
            'pengaturan' => $pengaturan,
            'nama_paroki' => $profil->nama_paroki ?? $pengaturan->nama_paroki ?? 'Kristus Raja - Katedral / Bonipoi',
            'nama_keuskupan' => $profil->keuskupan ?? $pengaturan->nama_keuskupan ?? 'Keuskupan Agung Kupang',
            'alamat' => $profil->alamat ?? $pengaturan->alamat ?? 'Fontein, Kec. Kota Raja, Kota Kupang, Prov. Nusa Tenggara Timur',
            'telepon' => $profil->telepon ?? $pengaturan->telepon ?? '(0380) 821-234',
            'email' => $profil->email ?? $pengaturan->email ?? 'katedral.kupang@gmail.com',
            'google_maps' => $profil->google_maps ?? $pengaturan->maps_embed ?? null,
            'pastor_paroki' => $profil->pastor_paroki ?? 'Pastor Paroki',
            'pastor_rekan' => $profil->pastor_rekan ?? 'Pastor Rekan',
            'frater' => $profil->frater ?? 'Frater TOP',
        ];
    }

    public function beranda()
    {
        $common = $this->getCommonData();

        $jadwalMisa = DB::table('jadwal_misa')
            ->orderBy('tanggal')
            ->orderBy('jam_perayaan')
            ->limit(6)
            ->get();

        $pengumuman = DB::table('pengumuman')
            ->where('status', 1)
            ->latest('created_at')
            ->limit(6)
            ->get();

        $galeri = DB::table('galeri')
            ->where('status', 1)
            ->latest('created_at')
            ->limit(8)
            ->get();

        $artikel = DB::table('konten')
            ->where('status_publish', 'Publish')
            ->latest('tanggal_publish')
            ->limit(6)
            ->get();

        $stats = [
            'total_kk' => DB::table('kk_katolik')->count(),
            'total_umat' => DB::table('umat')->count(),
            'total_kapela' => DB::table('kapela')->count() + DB::table('stasi_kapela')->count(),
            'total_kub' => DB::table('lingkungan')->count() + DB::table('kub')->count(),
        ];

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
        $riwayat = DB::table('riwayat_pastor_paroki')->orderBy('urutan')->get();
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
        $pastorList = DB::table('master_pastor')->limit(12)->get();
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

        $jadwalMisa = DB::table('jadwal_misa')
            ->whereMonth('tanggal', $bulanFilter)
            ->whereYear('tanggal', now()->year)
            ->orderBy('tanggal')
            ->orderBy('jam_perayaan')
            ->get();

        if ($jadwalMisa->isEmpty()) {
            $jadwalMisa = DB::table('jadwal_misa')->orderBy('tanggal')->limit(12)->get();
        }

        return view('pages.jadwal-misa', array_merge($common, compact('jadwalMisa', 'bulanFilter')));
    }

    public function agenda()
    {
        $common = $this->getCommonData();
        $agenda = DB::table('kegiatan')->latest('tanggal_mulai')->paginate(9);
        return view('pages.agenda', array_merge($common, compact('agenda')));
    }

    public function berita()
    {
        $common = $this->getCommonData();
        $berita = DB::table('konten')
            ->where('status_publish', 'Publish')
            ->latest('tanggal_publish')
            ->paginate(9);

        return view('pages.berita', array_merge($common, compact('berita')));
    }

    public function artikel()
    {
        $common = $this->getCommonData();
        $artikel = DB::table('konten')
            ->where('status_publish', 'Publish')
            ->latest('tanggal_publish')
            ->paginate(9);

        return view('pages.artikel', array_merge($common, compact('artikel')));
    }

    public function artikelDetail($slug)
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

        return view('pages.artikel-detail', array_merge($common, compact('item', 'terkait')));
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

    public function downloads()
    {
        $common = $this->getCommonData();
        $downloads = DB::table('downloads')->latest('created_at')->paginate(15);
        if ($downloads->isEmpty()) {
            $downloads = DB::table('arsip_digital')->latest('created_at')->paginate(15);
        }
        return view('pages.downloads', array_merge($common, compact('downloads')));
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

        return response()->json([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }
}
