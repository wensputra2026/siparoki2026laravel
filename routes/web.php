<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

// Beranda
Route::get('/', [PageController::class, 'beranda'])->name('beranda');

// Profil Dropdown
Route::get('/profil', [PageController::class, 'profil'])->name('profil');
Route::get('/sejarah', [PageController::class, 'sejarah'])->name('sejarah');
Route::get('/visi-misi', [PageController::class, 'visiMisi'])->name('visi-misi');
Route::get('/riwayat-pastor', [PageController::class, 'riwayatPastor'])->name('riwayat-pastor');
Route::get('/kronik', [PageController::class, 'kronik'])->name('kronik');
Route::get('/struktur', [PageController::class, 'struktur'])->name('struktur');
Route::get('/kapela', [PageController::class, 'kapela'])->name('kapela');
Route::get('/peta-kapela', [PageController::class, 'petaKapela'])->name('peta-kapela');
Route::get('/direktori-dpp', [PageController::class, 'direktoriDpp'])->name('direktori-dpp');
Route::get('/direktori-katekis', [PageController::class, 'direktoriKatekis'])->name('direktori-katekis');
Route::get('/direktori-misdinar', [PageController::class, 'direktoriMisdinar'])->name('direktori-misdinar');
Route::get('/pelayan-pastoral', [PageController::class, 'pelayanPastoral'])->name('pelayan-pastoral');
Route::get('/sambutan', [PageController::class, 'sambutan'])->name('sambutan');

// Jadwal Dropdown
Route::get('/jadwal-misa', [PageController::class, 'jadwalMisa'])->name('jadwal-misa');
Route::get('/agenda', [PageController::class, 'agenda'])->name('agenda');
Route::get('/kegiatan', [PageController::class, 'agenda'])->name('kegiatan');

// Berita & Warta Dropdown
Route::get('/berita', [PageController::class, 'berita'])->name('berita');
Route::get('/artikel', [PageController::class, 'artikel'])->name('artikel');
Route::get('/artikel/{slug}', [PageController::class, 'artikelDetail'])->name('artikel.detail');
Route::get('/pengumuman', [PageController::class, 'pengumuman'])->name('pengumuman');
Route::get('/pengumuman/{id}', [PageController::class, 'pengumumanDetail'])->name('pengumuman.detail');
Route::get('/renungan', [PageController::class, 'renungan'])->name('renungan');

// Galeri & Media Dropdown
Route::get('/galeri', [PageController::class, 'galeri'])->name('galeri');
Route::get('/video', [PageController::class, 'video'])->name('video');

// Navigasi Utama Lainnya
Route::get('/statistik', [PageController::class, 'statistik'])->name('statistik');
Route::get('/kontak', [PageController::class, 'kontak'])->name('kontak');
Route::get('/downloads', [PageController::class, 'downloads'])->name('downloads');

// Pelayanan & Sakramen
Route::get('/pelayanan', [PageController::class, 'pelayanan'])->name('pelayanan');
Route::get('/pengajuan-sakramen', [PageController::class, 'sakramen'])->name('pengajuan-sakramen');
Route::get('/sakramen', [PageController::class, 'sakramen'])->name('sakramen');

// API GeoJSON Kapela (Untuk Leaflet Map)
Route::get('/api/kapela-geojson', [PageController::class, 'kapelaGeojson'])->name('api.kapela-geojson');
