<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\MasterReferensiController;
use Illuminate\Support\Facades\Route;



// Inisialisasi & Setup Paroki Wizard (Untuk Instalasi Baru GitHub / Ganti Paroki)
Route::get('/setup-paroki', [\App\Http\Controllers\SetupParokiController::class, 'index'])->name('setup.paroki');
Route::post('/setup-paroki', [\App\Http\Controllers\SetupParokiController::class, 'save'])->name('setup.paroki.save');
Route::get('/api/setup/hierarchy', [\App\Http\Controllers\SetupParokiController::class, 'getHierarchy'])->name('setup.paroki.hierarchy');
Route::post('/admin/setup-paroki/reset', [\App\Http\Controllers\SetupParokiController::class, 'resetSetup'])->name('setup.paroki.reset')->middleware('auth');

// Dropdown Wilayah Sipil (Kemendagri)
Route::prefix('api/wilayah')->group(function () {
    Route::get('/provinsi', [\App\Http\Controllers\WilayahDropdownController::class, 'getProvinsi'])->name('api.wilayah.provinsi');
    Route::get('/kabupaten/{provinsiKode}', [\App\Http\Controllers\WilayahDropdownController::class, 'getKabupaten'])->name('api.wilayah.kabupaten');
    Route::get('/kecamatan/{kabupatenKode}', [\App\Http\Controllers\WilayahDropdownController::class, 'getKecamatan'])->name('api.wilayah.kecamatan');
    Route::get('/desa/{kecamatanKode}', [\App\Http\Controllers\WilayahDropdownController::class, 'getDesa'])->name('api.wilayah.desa');
});

// Beranda (Mendukung GET, HEAD, dan graceful fallback POST agar tidak terjadi 405 Method Not Allowed)
Route::match(['get', 'post', 'head'], '/', function (\Illuminate\Http\Request $request) {
    if ($request->isMethod('post')) {
        return redirect('/');
    }
    return app(PageController::class)->beranda($request);
})->name('beranda');

// Profil Dropdown
Route::get('/profil', [PageController::class, 'profil'])->name('profil');
Route::get('/sejarah', [PageController::class, 'sejarah'])->name('sejarah');
Route::get('/visi-misi', [PageController::class, 'visiMisi'])->name('visi-misi');
Route::get('/riwayat-pastor', [PageController::class, 'riwayatPastor'])->name('riwayat-pastor');
Route::get('/kronik', [PageController::class, 'kronik'])->name('kronik');
Route::get('/struktur', [PageController::class, 'struktur'])->name('struktur');
Route::get('/kapela', [PageController::class, 'kapela'])->name('kapela');
Route::get('/profil-kapela', [PageController::class, 'kapela'])->name('profil-kapela');
Route::get('/profil-kapela/{id}', [PageController::class, 'kapelaDetail'])->name('profil-kapela.detail');
Route::get('/kapela/{id}', [PageController::class, 'kapelaDetail'])->name('kapela.detail');
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

// Warta Paroki (Berita, Artikel, Pengumuman, Renungan, Kategori)
Route::get('/warta', [PageController::class, 'warta'])->name('warta');
Route::get('/warta-paroki', [PageController::class, 'warta'])->name('warta.paroki');
Route::get('/warta/kategori/{slug}', [PageController::class, 'wartaKategori'])->name('warta.kategori');
Route::get('/kategori/{slug}', [PageController::class, 'wartaKategori'])->name('kategori.konten');
Route::get('/berita', [PageController::class, 'berita'])->name('berita');
Route::get('/berita/{slug}', [PageController::class, 'beritaDetail'])->name('berita.detail');
Route::post('/berita/{slug}/komentar', [PageController::class, 'kirimKomentarArtikel'])->name('berita.komentar.kirim')->middleware('throttle:20,1');
Route::get('/artikel', [PageController::class, 'artikel'])->name('artikel');
Route::get('/artikel/{slug}', [PageController::class, 'artikelDetail'])->name('artikel.detail');
Route::post('/artikel/{slug}/komentar', [PageController::class, 'kirimKomentarArtikel'])->name('artikel.komentar.kirim')->middleware('throttle:20,1');
Route::get('/pengumuman', [PageController::class, 'pengumuman'])->name('pengumuman');
Route::get('/renungan', [PageController::class, 'renungan'])->name('renungan');
Route::get('/renungan/{slug}', [PageController::class, 'renunganDetail'])->name('renungan.detail');

// Galeri & Media Dropdown
Route::get('/galeri', [PageController::class, 'galeri'])->name('galeri');
Route::get('/video', [PageController::class, 'video'])->name('video');

// Navigasi Utama Lainnya
Route::get('/statistik', [PageController::class, 'statistik'])->name('statistik');
Route::get('/kontak', [PageController::class, 'kontak'])->name('kontak');
Route::post('/kontak', [PageController::class, 'kirimPesanKontak'])->name('kontak.kirim');
Route::get('/downloads', [PageController::class, 'downloads'])->name('downloads');
Route::get('/downloads/{download}/unduh', [PageController::class, 'downloadFile'])->name('downloads.file');
Route::get('/downloads/arsip/{arsip}/unduh', [PageController::class, 'downloadArsipFile'])->name('downloads.arsip.file');

// Pelayanan & Sakramen (Administrasi sakramen ditangani internal via Admin KUB)
Route::get('/pelayanan', [PageController::class, 'pelayanan'])->name('pelayanan');
Route::get('/pengajuan-sakramen', fn () => redirect('/'))->name('pengajuan-sakramen');
Route::get('/sakramen', fn () => redirect('/'))->name('sakramen');

// Layanan Publik Mandiri: Cek Data Umat via NIK
Route::match(['get', 'post'], '/cek-data-umat', [PageController::class, 'cekDataUmat'])->name('cek-data-umat')->middleware('throttle:40,1');
Route::get('/cek-nik', fn () => redirect()->route('cek-data-umat'));
Route::get('/cek-umat', fn () => redirect()->route('cek-data-umat'));
Route::get('/layanan/cek-data', fn () => redirect()->route('cek-data-umat'));


// Auth Routes (Login, Register & Lupa Password)
Route::get('/login', [\App\Http\Controllers\AuthController::class, 'showLogin'])->name('login');
Route::get('/masuk', [\App\Http\Controllers\AuthController::class, 'showLogin'])->name('masuk');
Route::get('/admin/login', fn () => redirect('/login'))->name('admin.login');
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'processLogin'])->name('login.process')->middleware('throttle:10,1');
Route::get('/captcha/refresh', [\App\Http\Controllers\AuthController::class, 'refreshCaptcha'])->name('captcha.refresh');

Route::get('/register', [\App\Http\Controllers\AuthController::class, 'showRegister'])->name('register');
Route::get('/daftar', [\App\Http\Controllers\AuthController::class, 'showRegister'])->name('daftar');
Route::post('/register', [\App\Http\Controllers\AuthController::class, 'processRegister'])->name('register.process')->middleware('throttle:10,1');

Route::get('/lupa-password', [\App\Http\Controllers\AuthController::class, 'showForgotPassword'])->name('lupa-password');
Route::get('/forgot-password', [\App\Http\Controllers\AuthController::class, 'showForgotPassword'])->name('forgot-password');
Route::post('/lupa-password', [\App\Http\Controllers\AuthController::class, 'processForgotPassword'])->name('lupa-password.process')->middleware('throttle:10,1');

// API GeoJSON Kapela (Untuk Leaflet Map)
Route::get('/api/kapela-geojson', [PageController::class, 'kapelaGeojson'])->name('api.kapela-geojson');

// Auth Logout (Strict POST for CSRF protection, safe GET redirect)
Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
Route::get('/logout', fn () => redirect('/login'))->name('logout.redirect');

// Static / Dynamic smart uploads handler for katedral assets
Route::get('/assets/uploads/{path}', function($path) {
    $cleanPath = preg_replace('#^(video/)?uploads/#', '', $path);
    $baseName = basename($path);
    $candidates = [
        public_path('assets/uploads/' . $path),
        public_path('assets/uploads/' . $cleanPath),
        public_path('uploads/' . $path),
        public_path('uploads/' . $cleanPath),
        public_path('assets/uploads/video/' . $baseName),
        public_path('assets/uploads/profil/' . $baseName),
        public_path('uploads/video/' . $baseName),
        public_path('uploads/profil/' . $baseName),
        storage_path('app/public/' . $path),
        storage_path('app/public/' . $cleanPath),
    ];
    $safe = siparoki_resolve_safe_file($candidates, [public_path(), storage_path('app/public')]);
    if ($safe) {
        return response()->file($safe);
    }
    abort(404);
})->where('path', '.*');
Route::get('/uploads/{path}', function($path) {
    $cleanPath = preg_replace('#^(video/)?uploads/#', '', $path);
    $baseName = basename($path);
    $candidates = [
        public_path('uploads/' . $path),
        public_path('uploads/' . $cleanPath),
        public_path('assets/uploads/' . $path),
        public_path('assets/uploads/' . $cleanPath),
        public_path('uploads/users/' . $baseName),
        public_path('uploads/profil/' . $baseName),
        public_path('assets/uploads/users/' . $baseName),
        public_path('assets/uploads/profil/' . $baseName),
        public_path('assets/uploads/video/' . $baseName),
        public_path('assets/uploads/galeri/' . $baseName),
        public_path('uploads/video/' . $baseName),
        public_path('uploads/galeri/' . $baseName),
        storage_path('app/public/' . $path),
    ];
    $safe = siparoki_resolve_safe_file($candidates, [public_path(), storage_path('app/public')]);
    if ($safe) {
        return response()->file($safe);
    }
    abort(404);
})->where('path', '.*');

// Static fallback handler for /assets/css/{path}
Route::get('/assets/css/{path}', function($path) {
    $baseName = basename($path);
    $candidates = [
        public_path('assets/css/' . $path),
        public_path('css/' . $path),
        public_path('assets/css/' . $baseName),
        public_path('css/' . $baseName),
    ];
    $safe = siparoki_resolve_safe_file($candidates, [public_path()]);
    if ($safe) {
        return response()->file($safe, [
            'Content-Type' => 'text/css; charset=UTF-8',
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
    abort(404);
})->where('path', '.*');

// Static / Dynamic storage uploads handler for galeri & assets
Route::get('/storage/{path}', function($path) {
    $cleanPath = preg_replace('#^(uploads/)?#', '', $path);
    $baseName = basename($path);
    $candidates = [
        storage_path('app/public/' . $path),
        public_path('uploads/' . $path),
        public_path('uploads/users/' . $baseName),
        public_path('uploads/profil/' . $baseName),
        public_path('uploads/galeri/' . $baseName),
        public_path('assets/uploads/' . $path),
        public_path('assets/uploads/users/' . $baseName),
        public_path('assets/uploads/profil/' . $baseName),
        public_path('assets/uploads/galeri/' . $baseName),
        public_path('uploads/' . $cleanPath),
        public_path('assets/uploads/' . $cleanPath),
    ];
    $safe = siparoki_resolve_safe_file($candidates, [public_path(), storage_path('app/public')]);
    if ($safe) {
        return response()->file($safe);
    }
    abort(404);
})->where('path', '.*');

// Static fallback for bare image filenames (e.g. /1787542903_6a8bbd774c7de.webp)
Route::get('/{filename}.{ext}', function($filename, $ext) {
    $fullName = $filename . '.' . $ext;
    $candidates = [
        public_path('uploads/users/' . $fullName),
        public_path('uploads/profil/' . $fullName),
        public_path('uploads/galeri/' . $fullName),
        public_path('uploads/konten/' . $fullName),
        public_path('uploads/' . $fullName),
        public_path('assets/uploads/users/' . $fullName),
        public_path('assets/uploads/profil/' . $fullName),
        public_path('assets/uploads/' . $fullName),
        storage_path('app/public/' . $fullName),
    ];
    $safe = siparoki_resolve_safe_file($candidates, [public_path(), storage_path('app/public')]);
    if ($safe) {
        return response()->file($safe);
    }
    abort(404);
})->where('ext', 'webp|jpg|jpeg|png|gif|svg|ico|jfif');



// Proxy route: serve pastor/umat photos by filename securely within public/storage
Route::get('/foto-pastor/{filename}', function(string $filename) {
    $baseName = basename($filename);
    $laravelPublic = public_path();

    $candidates = [
        $laravelPublic . '/uploads/pastor/' . $baseName,
        $laravelPublic . '/uploads/profil/' . $baseName,
        $laravelPublic . '/uploads/' . $baseName,
        $laravelPublic . '/assets/uploads/pastor/' . $baseName,
        $laravelPublic . '/assets/uploads/profil/' . $baseName,
        $laravelPublic . '/assets/uploads/' . $baseName,
        storage_path('app/public/pastor/' . $baseName),
        storage_path('app/public/' . $baseName),
    ];

    $safe = siparoki_resolve_safe_file($candidates, [$laravelPublic, storage_path('app/public')]);
    if ($safe) {
        return response()->file($safe);
    }

    // Return default pastor photo as fallback
    $defaultPastor = $laravelPublic . '/assets/frontend/siparoki/images/default-pastor.jpg';
    if (file_exists($defaultPastor)) {
        return response()->file($defaultPastor);
    }
    $defaultAvatar = $laravelPublic . '/assets/img/default-avatar.png';
    if (file_exists($defaultAvatar)) {
        return response()->file($defaultAvatar);
    }
    abort(404);
})->where('filename', '[^/]+');

// Level-specific Role Panel Route Groups (Modern Monolith No-API)
$rolePrefixes = [
    'superadmin' => 'Super Admin',
    'paroki' => 'Admin Paroki',
    'pastor' => 'Pastor',
    'wilayah' => 'Admin Wilayah',
    'kapela' => 'Admin Kapela / Stasi',
    'kub' => 'Admin KUB',
    'bendahara' => 'Bendahara',
    'penulis' => 'Penulis',
];

$legacyModuleAliases = [
    'kk' => 'kk-katolik',
    'keluarga' => 'kk-katolik',
    'downloads' => 'download',
    'intensi' => 'intensi-misa',
    'lapak' => 'lapak-produk',
    'kuasi_paroki' => 'kuasi-paroki',
    'users' => 'user',
    'artikel' => 'konten',
    'berita' => 'konten',
    'video' => 'galeri',
    'roles' => 'role',
    'backup_restore' => 'backup-database',
    'security-center' => 'security-settings',
    'clean-uploads' => 'backup-database',
    'katekumen' => 'pengajuan-sakramen',
    'kanonikal' => 'sakramen',
];

Route::middleware([\App\Http\Middleware\PanelAccess::class])->group(function () use ($rolePrefixes, $legacyModuleAliases) {
    // Endpoint pencarian umat secara lazy (tenant-scoped) untuk SearchableSelect.
    Route::get('/umat-options', [\App\Http\Controllers\InertiaPanelController::class, 'umatOptions'])->name('panel.umat.options');

foreach ($rolePrefixes as $prefix => $roleTitle) {
    Route::prefix($prefix)->group(function () use ($prefix, $legacyModuleAliases) {
        Route::get('/', [\App\Http\Controllers\InertiaPanelController::class, 'dashboard'])->name("panel.{$prefix}");
        Route::get('/dashboard', [\App\Http\Controllers\InertiaPanelController::class, 'dashboard'])->name("panel.{$prefix}.dashboard");
        Route::get('/umat', fn (\Illuminate\Http\Request $request) => app(\App\Http\Controllers\InertiaPanelController::class)->module($request, 'umat'))->name("panel.{$prefix}.umat");
        Route::get('/data-umat', fn (\Illuminate\Http\Request $request) => app(\App\Http\Controllers\InertiaPanelController::class)->module($request, 'umat'))->name("panel.{$prefix}.data-umat");
        Route::get('/umat/create', [\App\Http\Controllers\InertiaPanelController::class, 'createUmat'])->name("panel.{$prefix}.umat.create");
        Route::get('/data-umat/create', [\App\Http\Controllers\InertiaPanelController::class, 'createUmat'])->name("panel.{$prefix}.data-umat.create");
        Route::get('/umat/{id}/edit', [\App\Http\Controllers\InertiaPanelController::class, 'editUmat'])->name("panel.{$prefix}.umat.edit");
        Route::get('/data-umat/{id}/edit', [\App\Http\Controllers\InertiaPanelController::class, 'editUmat'])->name("panel.{$prefix}.data-umat.edit");
        Route::post('/umat/store', [\App\Http\Controllers\InertiaPanelController::class, 'storeUmat'])->name("panel.{$prefix}.umat.store");
        Route::post('/umat/{id}/update', [\App\Http\Controllers\InertiaPanelController::class, 'updateUmat'])->name("panel.{$prefix}.umat.update");
        Route::get('/umat/{id}/mutasi', [\App\Http\Controllers\Admin\Pastoral\MutasiUmatController::class, 'showMutasi'])->name("panel.{$prefix}.umat.mutasi");
        Route::post('/umat/{id}/mutasi', [\App\Http\Controllers\Admin\Pastoral\MutasiUmatController::class, 'prosesMutasi'])->name("panel.{$prefix}.umat.mutasi.store");
        Route::get('/umat/{id}/pisah-kk', [\App\Http\Controllers\Admin\Pastoral\MutasiUmatController::class, 'showPisah'])->name("panel.{$prefix}.umat.pisah");
        Route::post('/umat/{id}/pisah-kk', [\App\Http\Controllers\Admin\Pastoral\MutasiUmatController::class, 'prosesPisah'])->name("panel.{$prefix}.umat.pisah.store");
        Route::get('/umat/{id}/riwayat', [\App\Http\Controllers\Admin\Pastoral\MutasiUmatController::class, 'showRiwayat'])->name("panel.{$prefix}.umat.riwayat");
        Route::get('/umat/{id}/cetak', [\App\Http\Controllers\InertiaPanelController::class, 'exportUmatPdf'])->name("panel.{$prefix}.umat.cetak");
        Route::get('/umat/{id}/pdf', [\App\Http\Controllers\InertiaPanelController::class, 'exportUmatPdf'])->name("panel.{$prefix}.umat.pdf");
        Route::get('/data-umat/{id}/cetak', [\App\Http\Controllers\InertiaPanelController::class, 'exportUmatPdf'])->name("panel.{$prefix}.data-umat.cetak");
        Route::get('/data-umat/{id}/pdf', [\App\Http\Controllers\InertiaPanelController::class, 'exportUmatPdf'])->name("panel.{$prefix}.data-umat.pdf");
        Route::get('/riwayat-mutasi/tambah', [\App\Http\Controllers\Admin\Pastoral\MutasiUmatController::class, 'showTambah'])->name("panel.{$prefix}.riwayat-mutasi.create");
        Route::post('/riwayat-mutasi/tambah', [\App\Http\Controllers\Admin\Pastoral\MutasiUmatController::class, 'storeTambah'])->name("panel.{$prefix}.riwayat-mutasi.store");
        Route::get('/master-pastor/create', [\App\Http\Controllers\InertiaPanelController::class, 'createPastor'])->name("panel.{$prefix}.master-pastor.create");
        Route::get('/pastor/create', [\App\Http\Controllers\InertiaPanelController::class, 'createPastor'])->name("panel.{$prefix}.pastor.create");
        Route::get('/master-referensi/pastor/create', [\App\Http\Controllers\InertiaPanelController::class, 'createPastor'])->name("panel.{$prefix}.master-referensi.pastor.create");
        Route::get('/master-pastor/{id}/edit', [\App\Http\Controllers\InertiaPanelController::class, 'editPastor'])->name("panel.{$prefix}.master-pastor.edit");
        Route::get('/master-pastor/edit/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'editPastor'])->name("panel.{$prefix}.master-pastor.edit.alt");
        Route::get('/pastor/{id}/edit', [\App\Http\Controllers\InertiaPanelController::class, 'editPastor'])->name("panel.{$prefix}.pastor.edit");
        Route::get('/pastor/edit/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'editPastor'])->name("panel.{$prefix}.pastor.edit.alt");
        Route::get('/master-referensi/pastor/edit/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'editPastor'])->name("panel.{$prefix}.master-referensi.pastor.edit");
        Route::get('/master-referensi/pastor/{id}/edit', [\App\Http\Controllers\InertiaPanelController::class, 'editPastor'])->name("panel.{$prefix}.master-referensi.pastor.edit.id");
        Route::post('/master-pastor/store', [\App\Http\Controllers\InertiaPanelController::class, 'storePastor'])->name("panel.{$prefix}.master-pastor.store");
        Route::get('/master-pastor/store', fn () => redirect("/{$prefix}/master-pastor/create"));
        Route::post('/pastor/store', [\App\Http\Controllers\InertiaPanelController::class, 'storePastor'])->name("panel.{$prefix}.pastor.store");
        Route::get('/pastor/store', fn () => redirect("/{$prefix}/pastor/create"));
        Route::post('/master-referensi/pastor/store', [\App\Http\Controllers\InertiaPanelController::class, 'storePastor'])->name("panel.{$prefix}.master-referensi.pastor.store");
        Route::get('/master-referensi/pastor/store', fn () => redirect("/{$prefix}/master-referensi/pastor/create"));
        Route::match(['post', 'put'], '/master-pastor/{id}/update', [\App\Http\Controllers\InertiaPanelController::class, 'updatePastor'])->name("panel.{$prefix}.master-pastor.update");
        Route::match(['post', 'put'], '/master-pastor/update/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'updatePastor'])->name("panel.{$prefix}.master-pastor.update.alt");
        Route::match(['post', 'put'], '/pastor/{id}/update', [\App\Http\Controllers\InertiaPanelController::class, 'updatePastor'])->name("panel.{$prefix}.pastor.update");
        Route::match(['post', 'put'], '/pastor/update/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'updatePastor'])->name("panel.{$prefix}.pastor.update.alt");
        Route::match(['post', 'put'], '/master-referensi/pastor/{id}/update', [\App\Http\Controllers\InertiaPanelController::class, 'updatePastor'])->name("panel.{$prefix}.master-referensi.pastor.update");
        Route::match(['post', 'put'], '/master-referensi/pastor/update/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'updatePastor'])->name("panel.{$prefix}.master-referensi.pastor.update.alt");
        Route::get('/master-referensi/pastor/{id}/update', fn ($id) => redirect("/admin/master-referensi/pastor/edit/{$id}"));
        Route::get('/master-referensi/pastor/update/{id}', fn ($id) => redirect("/admin/master-referensi/pastor/edit/{$id}"));
        Route::get('/master-pastor/{id}/update', fn ($id) => redirect("/{$prefix}/master-pastor/{$id}/edit"));
        Route::get('/master-pastor/update/{id}', fn ($id) => redirect("/{$prefix}/master-pastor/{$id}/edit"));
        Route::get('/pastor/{id}/update', fn ($id) => redirect("/{$prefix}/pastor/{$id}/edit"));
        Route::get('/pastor/update/{id}', fn ($id) => redirect("/{$prefix}/pastor/{$id}/edit"));
        Route::get('/umat/{id}/update', fn ($id) => redirect("/{$prefix}/umat/{$id}/edit"));
        Route::get('/umat/store', fn () => redirect("/{$prefix}/umat/create"));
        if (in_array($prefix, ['superadmin', 'admin', 'paroki'], true)) {
            Route::get('/profil-paroki', [\App\Http\Controllers\InertiaPanelController::class, 'profilParoki'])->name("panel.{$prefix}.profil-paroki");
            Route::post('/profil-paroki/set-default', [\App\Http\Controllers\InertiaPanelController::class, 'setDefaultParoki'])->name("panel.{$prefix}.profil-paroki.set-default");
            Route::post('/profil-paroki', [\App\Http\Controllers\InertiaPanelController::class, 'updateProfilParokiDirect'])->name("panel.{$prefix}.profil-paroki.update");
            Route::put('/profil-paroki', [\App\Http\Controllers\InertiaPanelController::class, 'updateProfilParokiDirect'])->name("panel.{$prefix}.profil-paroki.update.put");
        }
        Route::get('/profil-saya', [\App\Http\Controllers\InertiaPanelController::class, 'profilSaya'])->name("panel.{$prefix}.profil-saya");
        Route::post('/profil-saya', [\App\Http\Controllers\InertiaPanelController::class, 'updateProfilSaya'])->name("panel.{$prefix}.profil-saya.update");
        Route::put('/profil-saya', [\App\Http\Controllers\InertiaPanelController::class, 'updateProfilSaya'])->name("panel.{$prefix}.profil-saya.update.put");
        Route::post('/profil-saya/password', [\App\Http\Controllers\InertiaPanelController::class, 'updatePasswordProfilSaya'])->name("panel.{$prefix}.profil-saya.password");
        Route::get('/panduan-hak-akses', [\App\Http\Controllers\InertiaPanelController::class, 'panduanHakAkses'])->name("panel.{$prefix}.panduan-hak-akses");
        Route::get('/statistik', [\App\Http\Controllers\InertiaPanelController::class, 'statistik'])->name("panel.{$prefix}.statistik");
        Route::get('/demografi', [\App\Http\Controllers\InertiaPanelController::class, 'statistik'])->name("panel.{$prefix}.demografi");
        Route::get('/role/create', [\App\Http\Controllers\InertiaPanelController::class, 'createRole'])->name("panel.{$prefix}.role.create");
        Route::get('/roles/create', [\App\Http\Controllers\InertiaPanelController::class, 'createRole'])->name("panel.{$prefix}.roles.create");
        Route::get('/role/{id}/edit', [\App\Http\Controllers\InertiaPanelController::class, 'editRole'])->name("panel.{$prefix}.role.edit");
        Route::get('/roles/{id}/edit', [\App\Http\Controllers\InertiaPanelController::class, 'editRole'])->name("panel.{$prefix}.roles.edit");
        Route::get('/roles/edit/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'editRole'])->name("panel.{$prefix}.roles.edit.alt");
        Route::get('/role/edit/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'editRole'])->name("panel.{$prefix}.role.edit.alt");
        Route::get('/konten/create', [\App\Http\Controllers\InertiaPanelController::class, 'createKonten'])->name("panel.{$prefix}.konten.create");
        Route::get('/konten/{id}/preview', [\App\Http\Controllers\InertiaPanelController::class, 'previewKonten'])->name("panel.{$prefix}.konten.preview");
        Route::get('/konten/{id}/edit', [\App\Http\Controllers\InertiaPanelController::class, 'editKonten'])->name("panel.{$prefix}.konten.edit");
        Route::get('/kk-katolik/create', [\App\Http\Controllers\InertiaPanelController::class, 'createKk'])->name("panel.{$prefix}.kk-katolik.create");
        Route::get('/kk/create', [\App\Http\Controllers\InertiaPanelController::class, 'createKk'])->name("panel.{$prefix}.kk.create");
        Route::get('/keluarga/create', [\App\Http\Controllers\InertiaPanelController::class, 'createKk'])->name("panel.{$prefix}.keluarga.create");
        Route::get('/kk-katolik/{id}/view', [\App\Http\Controllers\InertiaPanelController::class, 'viewKk'])->name("panel.{$prefix}.kk-katolik.view");
        Route::get('/kk/view/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'viewKk'])->name("panel.{$prefix}.kk.view");
        Route::get('/kk-katolik/{id}/cetak', [\App\Http\Controllers\InertiaPanelController::class, 'exportKkPdf'])->name("panel.{$prefix}.kk-katolik.cetak");
        Route::get('/kk-katolik/{id}/pdf', [\App\Http\Controllers\InertiaPanelController::class, 'exportKkPdf'])->name("panel.{$prefix}.kk-katolik.pdf");
        Route::get('/kk/export_pdf/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'exportKkPdf'])->name("panel.{$prefix}.kk.export_pdf");
        Route::get('/kk-katolik/{id}/edit', [\App\Http\Controllers\InertiaPanelController::class, 'editKk'])->name("panel.{$prefix}.kk-katolik.edit");
        Route::get('/kk/{id}/edit', [\App\Http\Controllers\InertiaPanelController::class, 'editKk'])->name("panel.{$prefix}.kk.edit");
        Route::get('/keluarga/{id}/edit', [\App\Http\Controllers\InertiaPanelController::class, 'editKk'])->name("panel.{$prefix}.keluarga.edit");
        Route::get('/wilayah-sipil/desa-kelurahan', [\App\Http\Controllers\InertiaPanelController::class, 'desaKelurahanOptions'])->name("panel.{$prefix}.wilayah-sipil.desa-kelurahan");
        Route::get('/galeri/create', [\App\Http\Controllers\InertiaPanelController::class, 'createGaleri'])->name("panel.{$prefix}.galeri.create");
        Route::get('/galeri/tambah', [\App\Http\Controllers\InertiaPanelController::class, 'createGaleri'])->name("panel.{$prefix}.galeri.tambah");
        Route::get('/galeri/{id}/edit', [\App\Http\Controllers\InertiaPanelController::class, 'editGaleri'])->name("panel.{$prefix}.galeri.edit");
        Route::get('/backup-database', [\App\Http\Controllers\InertiaPanelController::class, 'backupDatabase'])->name("panel.{$prefix}.backup-database");
        Route::get('/backup-database/download-direct', [\App\Http\Controllers\InertiaPanelController::class, 'downloadDirectDatabaseBackup'])->name("panel.{$prefix}.backup-database.download-direct");
        Route::post('/backup-database/clear-server-backups', [\App\Http\Controllers\InertiaPanelController::class, 'clearServerBackups'])->name("panel.{$prefix}.backup-database.clear-server");
        Route::post('/backup-database/generate', [\App\Http\Controllers\InertiaPanelController::class, 'generateDatabaseBackup'])->name("panel.{$prefix}.backup-database.generate");
        Route::post('/backup-database/generate-media', [\App\Http\Controllers\InertiaPanelController::class, 'generateMediaBackup'])->name("panel.{$prefix}.backup-database.generate-media");
        Route::get('/backup-database/{id}/download', [\App\Http\Controllers\InertiaPanelController::class, 'downloadDatabaseBackup'])->name("panel.{$prefix}.backup-database.download");
        Route::post('/backup-database/{id}/restore', [\App\Http\Controllers\InertiaPanelController::class, 'restoreDatabaseBackup'])->name("panel.{$prefix}.backup-database.restore");
        Route::post('/backup-database/upload-restore', [\App\Http\Controllers\InertiaPanelController::class, 'uploadRestoreDatabaseBackup'])->name("panel.{$prefix}.backup-database.upload-restore");
        Route::delete('/backup-database/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'deleteDatabaseBackup'])->name("panel.{$prefix}.backup-database.delete");
        Route::get('/security-center', [\App\Http\Controllers\InertiaPanelController::class, 'securityCenter'])->name("panel.{$prefix}.security-center");
        Route::get('/security-settings', [\App\Http\Controllers\InertiaPanelController::class, 'securityCenter'])->name("panel.{$prefix}.security-settings");
        Route::post('/security/settings', [\App\Http\Controllers\InertiaPanelController::class, 'updateSecuritySettings'])->name("panel.{$prefix}.security.settings.update");
        Route::post('/security/block-ip', [\App\Http\Controllers\InertiaPanelController::class, 'blockIp'])->name("panel.{$prefix}.security.block-ip");
        Route::post('/security/unblock-ip/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'unblockIp'])->name("panel.{$prefix}.security.unblock-ip");
        Route::delete('/security/unblock-ip/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'unblockIp'])->name("panel.{$prefix}.security.unblock-ip.delete");
        Route::post('/security/clear-logs', [\App\Http\Controllers\InertiaPanelController::class, 'clearSecurityLogs'])->name("panel.{$prefix}.security.clear-logs");
        Route::post('/security/clear-cache', [\App\Http\Controllers\InertiaPanelController::class, 'clearSystemSecurityCache'])->name("panel.{$prefix}.security.clear-cache");

        // Settings Hub (Pengaturan Terpadu: Pembayaran, Midtrans, OTP, Video, Slider, SEO, Widget)
        Route::get('/pengaturan-aplikasi', [\App\Http\Controllers\InertiaPanelController::class, 'pengaturanHub'])->name("panel.{$prefix}.pengaturan-aplikasi");
        Route::get('/pengaturan/midtrans', fn (\Illuminate\Http\Request $req) => app(\App\Http\Controllers\InertiaPanelController::class)->pengaturanHub($req, 'midtrans'))->name("panel.{$prefix}.pengaturan.midtrans");
        Route::get('/pengaturan-midtrans', fn (\Illuminate\Http\Request $req) => app(\App\Http\Controllers\InertiaPanelController::class)->pengaturanHub($req, 'midtrans'))->name("panel.{$prefix}.pengaturan-midtrans");
        Route::get('/pengaturan/pembayaran', fn (\Illuminate\Http\Request $req) => app(\App\Http\Controllers\InertiaPanelController::class)->pengaturanHub($req, 'pembayaran'))->name("panel.{$prefix}.pengaturan.pembayaran");
        Route::get('/pengaturan-pembayaran', fn (\Illuminate\Http\Request $req) => app(\App\Http\Controllers\InertiaPanelController::class)->pengaturanHub($req, 'pembayaran'))->name("panel.{$prefix}.pengaturan-pembayaran");
        Route::get('/metode-pembayaran', fn (\Illuminate\Http\Request $req) => app(\App\Http\Controllers\InertiaPanelController::class)->pengaturanHub($req, 'pembayaran'))->name("panel.{$prefix}.metode-pembayaran");
        Route::get('/pengaturan/otp', fn (\Illuminate\Http\Request $req) => app(\App\Http\Controllers\InertiaPanelController::class)->pengaturanHub($req, 'otp'))->name("panel.{$prefix}.pengaturan.otp");
        Route::get('/pengaturan-otp', fn (\Illuminate\Http\Request $req) => app(\App\Http\Controllers\InertiaPanelController::class)->pengaturanHub($req, 'otp'))->name("panel.{$prefix}.pengaturan-otp");
        Route::get('/pengaturan/meta_tag', fn (\Illuminate\Http\Request $req) => app(\App\Http\Controllers\InertiaPanelController::class)->pengaturanHub($req, 'seo'))->name("panel.{$prefix}.pengaturan.meta_tag");
        Route::get('/seo', fn (\Illuminate\Http\Request $req) => app(\App\Http\Controllers\InertiaPanelController::class)->pengaturanHub($req, 'seo'))->name("panel.{$prefix}.seo");
        Route::get('/video-header', fn (\Illuminate\Http\Request $req) => app(\App\Http\Controllers\InertiaPanelController::class)->pengaturanHub($req, 'video'))->name("panel.{$prefix}.video-header");
        Route::get('/slider', fn (\Illuminate\Http\Request $req) => app(\App\Http\Controllers\InertiaPanelController::class)->pengaturanHub($req, 'slider'))->name("panel.{$prefix}.slider");
        Route::get('/widget', fn (\Illuminate\Http\Request $req) => app(\App\Http\Controllers\InertiaPanelController::class)->pengaturanHub($req, 'widget'))->name("panel.{$prefix}.widget");
        Route::get('/menu', fn (\Illuminate\Http\Request $req) => app(\App\Http\Controllers\InertiaPanelController::class)->pengaturanHub($req, 'widget'))->name("panel.{$prefix}.menu");
        Route::get('/pengaturan/maintenance', fn (\Illuminate\Http\Request $req) => app(\App\Http\Controllers\InertiaPanelController::class)->pengaturanHub($req, 'maintenance'))->name("panel.{$prefix}.pengaturan.maintenance");
        Route::get('/maintenance', fn (\Illuminate\Http\Request $req) => app(\App\Http\Controllers\InertiaPanelController::class)->pengaturanHub($req, 'maintenance'))->name("panel.{$prefix}.maintenance");

        // Settings Hub Actions (GET redirects back to settings tab, POST saves)
        Route::match(['GET', 'POST'], '/pengaturan/midtrans/save', fn (\Illuminate\Http\Request $r) => $r->isMethod('post') ? app(\App\Http\Controllers\InertiaPanelController::class)->savePengaturanMidtrans($r) : redirect("/{$prefix}/metode-pembayaran"))->name("panel.{$prefix}.pengaturan.midtrans.save");
        Route::post('/pengaturan/midtrans/test', [\App\Http\Controllers\InertiaPanelController::class, 'testMidtransConnection'])->name("panel.{$prefix}.pengaturan.midtrans.test");
        Route::match(['GET', 'POST'], '/pengaturan/pembayaran/save', fn (\Illuminate\Http\Request $r) => $r->isMethod('post') ? app(\App\Http\Controllers\InertiaPanelController::class)->saveMetodePembayaran($r) : redirect("/{$prefix}/metode-pembayaran"))->name("panel.{$prefix}.pengaturan.pembayaran.save");
        Route::delete('/pengaturan/pembayaran/{id}/delete', [\App\Http\Controllers\InertiaPanelController::class, 'deleteMetodePembayaran'])->name("panel.{$prefix}.pengaturan.pembayaran.delete");
        Route::post('/pengaturan/pembayaran/{id}/delete', [\App\Http\Controllers\InertiaPanelController::class, 'deleteMetodePembayaran'])->name("panel.{$prefix}.pengaturan.pembayaran.delete.post");
        Route::match(['GET', 'POST'], '/pengaturan/otp/save', fn (\Illuminate\Http\Request $r) => $r->isMethod('post') ? app(\App\Http\Controllers\InertiaPanelController::class)->savePengaturanOtp($r) : redirect("/{$prefix}/pengaturan-otp"))->name("panel.{$prefix}.pengaturan.otp.save");
        Route::post('/pengaturan/otp/test', [\App\Http\Controllers\InertiaPanelController::class, 'testKirimWhatsapp'])->name("panel.{$prefix}.pengaturan.otp.test");
        Route::match(['GET', 'POST'], '/pengaturan/video/save', fn (\Illuminate\Http\Request $r) => $r->isMethod('post') ? app(\App\Http\Controllers\InertiaPanelController::class)->saveVideoHeader($r) : redirect("/{$prefix}/video-header"))->name("panel.{$prefix}.pengaturan.video.save");
        Route::match(['GET', 'POST'], '/pengaturan/slider/save', fn (\Illuminate\Http\Request $r) => $r->isMethod('post') ? app(\App\Http\Controllers\InertiaPanelController::class)->saveSlider($r) : redirect("/{$prefix}/slider"))->name("panel.{$prefix}.pengaturan.slider.save");
        Route::delete('/pengaturan/slider/{id}/delete', [\App\Http\Controllers\InertiaPanelController::class, 'deleteSlider'])->name("panel.{$prefix}.pengaturan.slider.delete");
        Route::post('/pengaturan/slider/{id}/delete', [\App\Http\Controllers\InertiaPanelController::class, 'deleteSlider'])->name("panel.{$prefix}.pengaturan.slider.delete.post");
        Route::match(['GET', 'POST'], '/pengaturan/seo/save', fn (\Illuminate\Http\Request $r) => $r->isMethod('post') ? app(\App\Http\Controllers\InertiaPanelController::class)->saveSeoMeta($r) : redirect("/{$prefix}/seo"))->name("panel.{$prefix}.pengaturan.seo.save");
        Route::match(['GET', 'POST'], '/pengaturan/widget/save', fn (\Illuminate\Http\Request $r) => $r->isMethod('post') ? app(\App\Http\Controllers\InertiaPanelController::class)->saveWidgetSettings($r) : redirect("/{$prefix}/widget"))->name("panel.{$prefix}.pengaturan.widget.save");
        Route::match(['GET', 'POST'], '/pengaturan/maintenance/save', fn (\Illuminate\Http\Request $r) => $r->isMethod('post') ? app(\App\Http\Controllers\InertiaPanelController::class)->saveMaintenanceSettings($r) : redirect("/{$prefix}/maintenance"))->name("panel.{$prefix}.pengaturan.maintenance.save");

        // Pembersih Sistem
        Route::get('/pembersih-sistem', [\App\Http\Controllers\InertiaPanelController::class, 'pembersihSistem'])->name("panel.{$prefix}.pembersih-sistem");
        Route::post('/pembersih-sistem/aksi', [\App\Http\Controllers\InertiaPanelController::class, 'pembersihSistemAksi'])->name("panel.{$prefix}.pembersih-sistem.aksi");

        // Master Referensi
        Route::get('/master-referensi', [\App\Http\Controllers\MasterReferensiController::class, 'index'])->name("panel.{$prefix}.master-referensi.index");
        Route::get('/master-referensi/{type}', [\App\Http\Controllers\MasterReferensiController::class, 'list'])->name("panel.{$prefix}.master-referensi.list");
        Route::get('/master-referensi/options/{relTable}', [\App\Http\Controllers\MasterReferensiController::class, 'options'])->name("panel.{$prefix}.master-referensi.options");
        Route::post('/master-referensi/{type}', [\App\Http\Controllers\MasterReferensiController::class, 'store'])->name("panel.{$prefix}.master-referensi.store");
        Route::put('/master-referensi/{type}/{id}', [\App\Http\Controllers\MasterReferensiController::class, 'update'])->name("panel.{$prefix}.master-referensi.update");
        Route::post('/master-referensi/{type}/{id}', [\App\Http\Controllers\MasterReferensiController::class, 'update'])->name("panel.{$prefix}.master-referensi.update.post");
        Route::get('/master-referensi/{type}/{id}/update', fn ($type, $id) => redirect("/{$prefix}/master-referensi/{$type}"));
        Route::get('/master-referensi/{type}/{id}', fn ($type, $id) => redirect("/{$prefix}/master-referensi/{$type}"));
        Route::delete('/master-referensi/{type}/{id}', [\App\Http\Controllers\MasterReferensiController::class, 'destroy'])->name("panel.{$prefix}.master-referensi.destroy");

        Route::get('/jadwal-misa/bulan', fn () => redirect("/{$prefix}/jadwal-misa"));
        Route::get('/sakramen/daftar_pembayaran', fn () => redirect("/{$prefix}/pengajuan-sakramen"));
        Route::get('/kk/mutasi_kub', fn () => redirect("/{$prefix}/kk-katolik"));
        Route::get('/surat/masuk', fn () => redirect("/{$prefix}/surat-masuk"));
        Route::get('/surat/keluar', fn () => redirect("/{$prefix}/surat-keluar"));
        Route::get('/pengaturan/{page}', fn (string $page) => redirect("/{$prefix}/pengaturan-aplikasi"));
        Route::get('/{legacy}/create', fn (string $legacy) => redirect("/{$prefix}/" . ($legacyModuleAliases[$legacy] ?? $legacy)));
        Route::get('/{legacy}', fn (string $legacy) => redirect("/{$prefix}/{$legacyModuleAliases[$legacy]}"))
            ->whereIn('legacy', array_keys($legacyModuleAliases));
        Route::get('/{slug}/export/{format}', [\App\Http\Controllers\InertiaPanelController::class, 'exportModule'])->name("panel.{$prefix}.module.export");
        Route::post('/{slug}/import', [\App\Http\Controllers\InertiaPanelController::class, 'importModule'])->name("panel.{$prefix}.module.import");
        Route::post('/user/{id}/reset-password', [\App\Http\Controllers\InertiaPanelController::class, 'resetUserPassword'])->name("panel.{$prefix}.user.reset-password");
        Route::post('/user/{id}/toggle-status', [\App\Http\Controllers\InertiaPanelController::class, 'toggleUserStatus'])->name("panel.{$prefix}.user.toggle-status");
        Route::post('/user/{id}/impersonate', [\App\Http\Controllers\InertiaPanelController::class, 'impersonateUser'])->name("panel.{$prefix}.user.impersonate");
        Route::get('/{slug}', [\App\Http\Controllers\InertiaPanelController::class, 'module'])->name("panel.{$prefix}.module");
        Route::post('/{slug}', [\App\Http\Controllers\InertiaPanelController::class, 'storeModule'])->name("panel.{$prefix}.module.store");
        Route::put('/{slug}/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'updateModule'])->name("panel.{$prefix}.module.update");
        Route::post('/{slug}/bulk-delete', [\App\Http\Controllers\InertiaPanelController::class, 'bulkDestroyModule'])->name("panel.{$prefix}.module.bulk_destroy");
        Route::delete('/{slug}/bulk-delete', [\App\Http\Controllers\InertiaPanelController::class, 'bulkDestroyModule']);
        Route::delete('/{slug}/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'destroyModule'])->name("panel.{$prefix}.module.destroy");
    });
}
});

// Global Admin Aliases
Route::get('/panduan', function () {
    $user = auth()->user();
    if (!$user) return redirect()->route('login');
    $slug = strtolower(preg_replace('/[^a-z0-9]/', '', $user->role?->slug ?? $user->role?->nama_role ?? ''));
    $roleMap = [
        'superadmin' => 'superadmin',
        'paroki' => 'paroki',
        'pastor' => 'pastor',
        'wilayah' => 'wilayah',
        'kapela' => 'kapela',
        'stasi' => 'kapela',
        'kub' => 'kub',
        'bendahara' => 'bendahara',
        'penulis' => 'penulis',
        'umat' => 'umat',
    ];
    $prefix = 'superadmin';
    foreach ($roleMap as $k => $p) {
        if (str_contains($slug, $k)) { $prefix = $p; break; }
    }
    return redirect("/{$prefix}/panduan-hak-akses");
})->name('panduan');

Route::get('/profil-saya', function () {
    $user = auth()->user();
    if (!$user) return redirect()->route('login');
    $slug = strtolower(preg_replace('/[^a-z0-9]/', '', $user->role?->slug ?? $user->role?->nama_role ?? ''));
    $roleMap = [
        'superadmin' => 'superadmin',
        'paroki' => 'paroki',
        'pastor' => 'pastor',
        'wilayah' => 'wilayah',
        'kapela' => 'kapela',
        'stasi' => 'kapela',
        'kub' => 'kub',
        'bendahara' => 'bendahara',
        'penulis' => 'penulis',
        'umat' => 'umat',
    ];
    $prefix = 'superadmin';
    foreach ($roleMap as $k => $p) {
        if (str_contains($slug, $k)) { $prefix = $p; break; }
    }
    return redirect("/{$prefix}/profil-saya");
})->name('profil-saya.legacy');
Route::get('/penfui/users', fn () => redirect('/superadmin/user'))->name('penfui.users.legacy');

// Master Referensi & Admin Profil Saya (Inertia, ringan, tanpa eager relation Eloquent)
Route::middleware([\App\Http\Middleware\PanelAccess::class])->prefix('admin')->group(function () use ($legacyModuleAliases) {
    Route::get('/profil-saya', [\App\Http\Controllers\InertiaPanelController::class, 'profilSaya'])->name('admin.profil-saya');
    Route::post('/profil-saya', [\App\Http\Controllers\InertiaPanelController::class, 'updateProfilSaya'])->name('admin.profil-saya.update');
    Route::post('/profil-saya/password', [\App\Http\Controllers\InertiaPanelController::class, 'updatePasswordProfilSaya'])->name('admin.profil-saya.password');
    Route::get('/profil-paroki', [\App\Http\Controllers\InertiaPanelController::class, 'profilParoki'])->name('admin.profil-paroki');
    Route::get('/konten/create', [\App\Http\Controllers\InertiaPanelController::class, 'createKonten'])->name('admin.konten.create');
    Route::get('/konten/{id}/preview', [\App\Http\Controllers\InertiaPanelController::class, 'previewKonten'])->name('admin.konten.preview');
    Route::get('/konten/{id}/edit', [\App\Http\Controllers\InertiaPanelController::class, 'editKonten'])->name('admin.konten.edit');
    Route::get('/kk-katolik/create', [\App\Http\Controllers\InertiaPanelController::class, 'createKk'])->name('admin.kk-katolik.create');
    Route::get('/kk/create', [\App\Http\Controllers\InertiaPanelController::class, 'createKk'])->name('admin.kk.create');
    Route::get('/keluarga/create', [\App\Http\Controllers\InertiaPanelController::class, 'createKk'])->name('admin.keluarga.create');
    Route::get('/kk-katolik/{id}/edit', [\App\Http\Controllers\InertiaPanelController::class, 'editKk'])->name('admin.kk-katolik.edit');
    Route::get('/kk/{id}/edit', [\App\Http\Controllers\InertiaPanelController::class, 'editKk'])->name('admin.kk.edit');
    Route::get('/keluarga/{id}/edit', [\App\Http\Controllers\InertiaPanelController::class, 'editKk'])->name('admin.keluarga.edit');
    Route::get('/galeri/create', [\App\Http\Controllers\InertiaPanelController::class, 'createGaleri'])->name('admin.galeri.create');
    Route::get('/galeri/tambah', [\App\Http\Controllers\InertiaPanelController::class, 'createGaleri'])->name('admin.galeri.tambah');
    Route::get('/galeri/{id}/edit', [\App\Http\Controllers\InertiaPanelController::class, 'editGaleri'])->name('admin.galeri.edit');
    Route::get('/backup-database', [\App\Http\Controllers\InertiaPanelController::class, 'backupDatabase'])->name('admin.backup-database');
    Route::get('/backup-database/download-direct', [\App\Http\Controllers\InertiaPanelController::class, 'downloadDirectDatabaseBackup'])->name('admin.backup-database.download-direct');
    Route::post('/backup-database/clear-server-backups', [\App\Http\Controllers\InertiaPanelController::class, 'clearServerBackups'])->name('admin.backup-database.clear-server');
    Route::post('/backup-database/generate', [\App\Http\Controllers\InertiaPanelController::class, 'generateDatabaseBackup'])->name('admin.backup-database.generate');
    Route::post('/backup-database/generate-media', [\App\Http\Controllers\InertiaPanelController::class, 'generateMediaBackup'])->name('admin.backup-database.generate-media');
    Route::get('/backup-database/{id}/download', [\App\Http\Controllers\InertiaPanelController::class, 'downloadDatabaseBackup'])->name('admin.backup-database.download');
    Route::post('/backup-database/{id}/restore', [\App\Http\Controllers\InertiaPanelController::class, 'restoreDatabaseBackup'])->name('admin.backup-database.restore');
    Route::post('/backup-database/upload-restore', [\App\Http\Controllers\InertiaPanelController::class, 'uploadRestoreDatabaseBackup'])->name('admin.backup-database.upload-restore');
    Route::delete('/backup-database/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'deleteDatabaseBackup'])->name('admin.backup-database.delete');
    Route::get('/master-referensi', [MasterReferensiController::class, 'index'])->name('admin.master-referensi.index');
    Route::get('/master-referensi/pastor/create', [\App\Http\Controllers\InertiaPanelController::class, 'createPastor'])->name('admin.master-referensi.pastor.create');
    Route::get('/master-referensi/pastor/{id}/edit', [\App\Http\Controllers\InertiaPanelController::class, 'editPastor'])->name('admin.master-referensi.pastor.edit');
    Route::get('/master-referensi/pastor/edit/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'editPastor'])->name('admin.master-referensi.pastor.edit.alt');
    Route::post('/master-pastor/store', [\App\Http\Controllers\InertiaPanelController::class, 'storePastor'])->name('admin.master-pastor.store');
    Route::get('/master-pastor/store', fn () => redirect('/admin/master-referensi/pastor/create'));
    Route::match(['post', 'put'], '/master-pastor/{id}/update', [\App\Http\Controllers\InertiaPanelController::class, 'updatePastor'])->name('admin.master-pastor.update');
    Route::match(['post', 'put'], '/master-pastor/update/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'updatePastor'])->name('admin.master-pastor.update.alt');
    Route::get('/master-pastor/{id}/update', fn ($id) => redirect("/admin/master-referensi/pastor/edit/{$id}"));
    Route::get('/master-pastor/update/{id}', fn ($id) => redirect("/admin/master-referensi/pastor/edit/{$id}"));
    Route::post('/master-referensi/pastor/store', [\App\Http\Controllers\InertiaPanelController::class, 'storePastor'])->name('admin.master-referensi.pastor.store');
    Route::get('/master-referensi/pastor/store', fn () => redirect('/admin/master-referensi/pastor/create'));
    Route::match(['post', 'put'], '/master-referensi/pastor/{id}/update', [\App\Http\Controllers\InertiaPanelController::class, 'updatePastor'])->name('admin.master-referensi.pastor.update');
    Route::match(['post', 'put'], '/master-referensi/pastor/update/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'updatePastor'])->name('admin.master-referensi.pastor.update.alt');
    Route::get('/master-referensi/pastor/{id}/update', fn ($id) => redirect("/admin/master-referensi/pastor/edit/{$id}"));
    Route::get('/master-referensi/pastor/update/{id}', fn ($id) => redirect("/admin/master-referensi/pastor/edit/{$id}"));
    Route::post('/pastor/store', [\App\Http\Controllers\InertiaPanelController::class, 'storePastor'])->name('admin.pastor.store');
    Route::get('/pastor/store', fn () => redirect('/admin/master-referensi/pastor/create'));
    Route::match(['post', 'put'], '/pastor/{id}/update', [\App\Http\Controllers\InertiaPanelController::class, 'updatePastor'])->name('admin.pastor.update');
    Route::match(['post', 'put'], '/pastor/update/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'updatePastor'])->name('admin.pastor.update.alt');
    Route::get('/pastor/{id}/update', fn ($id) => redirect("/admin/master-referensi/pastor/edit/{$id}"));
    Route::get('/pastor/update/{id}', fn ($id) => redirect("/admin/master-referensi/pastor/edit/{$id}"));
    Route::get('/master-referensi/{type}', [MasterReferensiController::class, 'list'])->name('admin.master-referensi.list');
    Route::get('/master-referensi/options/{relTable}', [MasterReferensiController::class, 'options'])->name('admin.master-referensi.options');
    Route::post('/master-referensi/{type}', [MasterReferensiController::class, 'store'])->name('admin.master-referensi.store');
    Route::put('/master-referensi/{type}/{id}', [MasterReferensiController::class, 'update'])->name('admin.master-referensi.update');
    Route::post('/master-referensi/{type}/{id}', [MasterReferensiController::class, 'update'])->name('admin.master-referensi.update.post');
    Route::get('/master-referensi/{type}/{id}/update', fn ($type, $id) => redirect("/admin/master-referensi/{$type}"));
    Route::get('/master-referensi/{type}/{id}', fn ($type, $id) => redirect("/admin/master-referensi/{$type}"));
    Route::delete('/master-referensi/{type}/{id}', [MasterReferensiController::class, 'destroy'])->name('admin.master-referensi.destroy');
    Route::get('/jadwal-misa/bulan', fn () => redirect('/admin/jadwal-misa'));
    Route::get('/sakramen/daftar_pembayaran', fn () => redirect('/admin/pengajuan-sakramen'));
    Route::get('/kk/mutasi_kub', fn () => redirect('/admin/kk-katolik'));
    Route::get('/surat/masuk', fn () => redirect('/admin/surat-masuk'));
    Route::get('/surat/keluar', fn () => redirect('/admin/surat-keluar'));
    Route::get('/pengaturan/{page}', fn (string $page) => redirect('/admin/pengaturan-aplikasi'));
    Route::get('/{legacy}/create', fn (string $legacy) => redirect('/admin/' . ($legacyModuleAliases[$legacy] ?? $legacy)));
    Route::get('/{legacy}', fn (string $legacy) => redirect("/admin/{$legacyModuleAliases[$legacy]}"))
        ->whereIn('legacy', array_keys($legacyModuleAliases));
    Route::get('/{slug}/export/{format}', [\App\Http\Controllers\InertiaPanelController::class, 'exportModule'])->name('admin.module.export');
    Route::post('/{slug}/import', [\App\Http\Controllers\InertiaPanelController::class, 'importModule'])->name('admin.module.import');
    Route::get('/{slug}', [\App\Http\Controllers\InertiaPanelController::class, 'module'])->name('admin.module');
    Route::post('/{slug}', [\App\Http\Controllers\InertiaPanelController::class, 'storeModule'])->name('admin.module.store');
    Route::put('/{slug}/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'updateModule'])->name('admin.module.update');
    Route::post('/{slug}/bulk-delete', [\App\Http\Controllers\InertiaPanelController::class, 'bulkDestroyModule'])->name('admin.module.bulk_destroy');
    Route::delete('/{slug}/bulk-delete', [\App\Http\Controllers\InertiaPanelController::class, 'bulkDestroyModule']);
    Route::delete('/{slug}/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'destroyModule'])->name('admin.module.destroy');
});

Route::get('/admin', function () {
    $authUser = auth()->user();
    if (!$authUser) {
        return redirect('/login');
    }
    $slugClean = strtolower(preg_replace('/[^a-z0-9]/', '', $authUser->role?->slug ?? $authUser->role?->nama_role ?? ''));
    if (str_contains($slugClean, 'wilayah')) {
        return redirect('/wilayah');
    } elseif (str_contains($slugClean, 'kapela') || str_contains($slugClean, 'stasi')) {
        return redirect('/kapela');
    } elseif (str_contains($slugClean, 'kub')) {
        return redirect('/kub');
    } elseif (str_contains($slugClean, 'pastor')) {
        return redirect('/pastor');
    } elseif (str_contains($slugClean, 'bendahara')) {
        return redirect('/bendahara');
    } elseif (str_contains($slugClean, 'penulis') || str_contains($slugClean, 'komsos')) {
        return redirect('/penulis');
    } elseif (str_contains($slugClean, 'umat')) {
        return redirect('/umat');
    } elseif (str_contains($slugClean, 'paroki') || str_contains($slugClean, 'sekretariat')) {
        return redirect('/paroki');
    }
    return redirect('/superadmin');
})->name('admin');

Route::get('/dashboard', function () {
    $authUser = auth()->user();
    if (!$authUser) {
        return redirect('/login');
    }
    $slugClean = strtolower(preg_replace('/[^a-z0-9]/', '', $authUser->role?->slug ?? $authUser->role?->nama_role ?? ''));
    if (str_contains($slugClean, 'wilayah')) {
        return redirect('/wilayah');
    } elseif (str_contains($slugClean, 'kapela') || str_contains($slugClean, 'stasi')) {
        return redirect('/kapela');
    } elseif (str_contains($slugClean, 'kub')) {
        return redirect('/kub');
    } elseif (str_contains($slugClean, 'pastor')) {
        return redirect('/pastor');
    } elseif (str_contains($slugClean, 'bendahara')) {
        return redirect('/bendahara');
    } elseif (str_contains($slugClean, 'penulis') || str_contains($slugClean, 'komsos')) {
        return redirect('/penulis');
    } elseif (str_contains($slugClean, 'umat')) {
        return redirect('/umat');
    } elseif (str_contains($slugClean, 'paroki') || str_contains($slugClean, 'sekretariat')) {
        return redirect('/paroki');
    }
    return redirect('/superadmin');
})->name('dashboard');

// Backward compatibility for /v2/
Route::middleware([\App\Http\Middleware\PanelAccess::class])->prefix('v2')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\InertiaPanelController::class, 'dashboard'])->name('v2.dashboard');
    Route::get('/umat', [\App\Http\Controllers\InertiaPanelController::class, 'umat'])->name('v2.umat');
    Route::get('/profil-paroki', [\App\Http\Controllers\InertiaPanelController::class, 'profilParoki'])->name('v2.profil-paroki');
    Route::get('/profil-saya', [\App\Http\Controllers\InertiaPanelController::class, 'profilSaya'])->name('v2.profil-saya');
    Route::post('/profil-saya', [\App\Http\Controllers\InertiaPanelController::class, 'updateProfilSaya'])->name('v2.profil-saya.update');
    Route::post('/profil-saya/password', [\App\Http\Controllers\InertiaPanelController::class, 'updatePasswordProfilSaya'])->name('v2.profil-saya.password');
    Route::get('/panduan-hak-akses', [\App\Http\Controllers\InertiaPanelController::class, 'panduanHakAkses'])->name('v2.panduan-hak-akses');
    Route::get('/statistik', [\App\Http\Controllers\InertiaPanelController::class, 'statistik'])->name('v2.statistik');
    Route::get('/demografi', [\App\Http\Controllers\InertiaPanelController::class, 'statistik'])->name('v2.demografi');
    Route::get('/konten/create', [\App\Http\Controllers\InertiaPanelController::class, 'createKonten'])->name('v2.konten.create');
    Route::get('/konten/{id}/preview', [\App\Http\Controllers\InertiaPanelController::class, 'previewKonten'])->name('v2.konten.preview');
    Route::get('/konten/{id}/edit', [\App\Http\Controllers\InertiaPanelController::class, 'editKonten'])->name('v2.konten.edit');
    Route::get('/kk-katolik/create', [\App\Http\Controllers\InertiaPanelController::class, 'createKk'])->name('v2.kk-katolik.create');
    Route::get('/kk/create', [\App\Http\Controllers\InertiaPanelController::class, 'createKk'])->name('v2.kk.create');
    Route::get('/keluarga/create', [\App\Http\Controllers\InertiaPanelController::class, 'createKk'])->name('v2.keluarga.create');
    Route::get('/kk-katolik/{id}/edit', [\App\Http\Controllers\InertiaPanelController::class, 'editKk'])->name('v2.kk-katolik.edit');
    Route::get('/kk/{id}/edit', [\App\Http\Controllers\InertiaPanelController::class, 'editKk'])->name('v2.kk.edit');
    Route::get('/keluarga/{id}/edit', [\App\Http\Controllers\InertiaPanelController::class, 'editKk'])->name('v2.keluarga.edit');
    Route::get('/{slug}', [\App\Http\Controllers\InertiaPanelController::class, 'module'])->name('v2.module');
    Route::post('/{slug}', [\App\Http\Controllers\InertiaPanelController::class, 'storeModule'])->name('v2.module.store');
    Route::put('/{slug}/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'updateModule'])->name('v2.module.update');
    Route::post('/{slug}/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'updateModule'])->name('v2.module.update.post');
    Route::delete('/{slug}/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'destroyModule'])->name('v2.module.destroy');
});

// Legacy Media Library Asset Fallback
Route::get('/media_library/{path}', function ($path) {
    $safe = siparoki_resolve_safe_file([
        public_path('media_library/' . $path),
        public_path('uploads/' . $path),
        public_path('uploads/keuskupan/' . basename($path)),
    ], [public_path('media_library'), public_path('uploads')]);
    if ($safe) {
        return response()->file($safe);
    }
    $defaultSvg = public_path('uploads/keuskupan/logo_keuskupan_kupang.svg');
    if (file_exists($defaultSvg)) {
        return response()->file($defaultSvg, ['Content-Type' => 'image/svg+xml']);
    }
    return response('', 204);
})->where('path', '.*');

// ==========================================
// MIDTRANS SNAP PAYMENT GATEWAY & WEBHOOK
// ==========================================
Route::post('/midtrans/snap-token', [\App\Http\Controllers\MidtransController::class, 'createSnapToken'])->name('midtrans.snap-token');
Route::post('/midtrans/callback', [\App\Http\Controllers\MidtransController::class, 'handleCallback'])->name('midtrans.callback');
Route::get('/midtrans/status/{orderId}', [\App\Http\Controllers\MidtransController::class, 'checkStatus'])->name('midtrans.status');

// ==========================================
// NOTIFIKASI REAL-TIME (REVERB / POLLING)
// ==========================================
Route::middleware('auth')->prefix('api/notifikasi')->group(function () {
    Route::get('/list', [\App\Http\Controllers\NotifikasiController::class, 'list'])->name('notifikasi.list');
    Route::get('/poll', [\App\Http\Controllers\NotifikasiController::class, 'poll'])->name('notifikasi.poll');
    Route::post('/mark-read/{id?}', [\App\Http\Controllers\NotifikasiController::class, 'markAsRead'])->name('notifikasi.mark-read');
});

// ==========================================
// CHAT & PESAN INTERNAL REAL-TIME
// ==========================================
Route::middleware('auth')->prefix('api/chat')->group(function () {
    Route::get('/contacts', [\App\Http\Controllers\ChatController::class, 'getContacts'])->name('chat.contacts');
    Route::get('/messages/{recipientId}', [\App\Http\Controllers\ChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('/send', [\App\Http\Controllers\ChatController::class, 'sendMessage'])->name('chat.send');
    Route::get('/poll', [\App\Http\Controllers\ChatController::class, 'poll'])->name('chat.poll');
    Route::post('/mark-read/{senderId}', [\App\Http\Controllers\ChatController::class, 'markRead'])->name('chat.mark-read');
});

// Fallback redirect untuk URL lawas /v2/* ke /superadmin/*
Route::get('/v2/{path?}', function ($path = '') {
    $target = '/superadmin' . ($path ? '/' . $path : '');
    return redirect($target);
})->where('path', '.*');

// Impersonation & Scope Persistence
Route::middleware('auth')->group(function () {
    Route::post('/impersonate/leave', [\App\Http\Controllers\InertiaPanelController::class, 'leaveImpersonation'])->name('impersonate.leave');
    Route::post('/api/set-active-scope', [\App\Http\Controllers\InertiaPanelController::class, 'setActiveScope'])->name('api.set-active-scope');
});


// ==========================================
// MAINTENANCE / SYSTEM OPTIMIZE & MIGRATE
// ==========================================
Route::get('/clear-cache', function () {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);

    // Bersihkan karakter encoding rusak (mojibake) dengan PHP str_replace (100% akurat per byte)
    $replacements = [
        'ΓÇ£' => '“',
        'ΓÇ¥' => '”',
        'ΓÇÖ' => '’',
        'ΓÇÿ' => '‘',
        'ΓÇö' => '—',
        'ΓÇô' => '–',
        'ΓÇª' => '…',
        'Çœ'  => '“',
        'Ç '  => '”',
        'â€œ' => '“',
        'â€' => '”',
        'â€™' => '’',
        'â€”' => '—',
        'â€“' => '–',
        'â€¦' => '…',
    ];

    $tables = ['kapela', 'paroki', 'wilayah', 'kub', 'konten', 'profil_paroki', 'jadwal_misa', 'kegiatan', 'arsip_digital', 'rapat'];
    $fixedCount = 0;
    foreach ($tables as $table) {
        if (!\Illuminate\Support\Facades\Schema::hasTable($table)) {
            continue;
        }
        $cols = \Illuminate\Support\Facades\Schema::getColumnListing($table);
        $textCols = [];
        foreach ($cols as $c) {
            try {
                $type = \Illuminate\Support\Facades\Schema::getColumnType($table, $c);
                if (in_array($type, ['string', 'text', 'mediumtext', 'longtext'], true)) {
                    $textCols[] = $c;
                }
            } catch (\Throwable $e) {
                if (in_array($c, ['sejarah', 'keterangan', 'deskripsi', 'visi', 'misi', 'lokasi', 'alamat', 'nama_kapela', 'judul', 'isi'], true)) {
                    $textCols[] = $c;
                }
            }
        }

        if (empty($textCols)) {
            continue;
        }

        $pk = 'id';
        if (!in_array('id', $cols, true)) {
            $pk = $cols[0];
        }

        $records = \Illuminate\Support\Facades\DB::table($table)->get();
        foreach ($records as $rec) {
            $rowUpdates = [];
            foreach ($textCols as $tc) {
                $orig = $rec->$tc ?? null;
                if (is_string($orig) && $orig !== '') {
                    $cleaned = str_replace(array_keys($replacements), array_values($replacements), $orig);
                    if ($cleaned !== $orig) {
                        $rowUpdates[$tc] = $cleaned;
                    }
                }
            }
            if (!empty($rowUpdates)) {
                \Illuminate\Support\Facades\DB::table($table)->where($pk, $rec->$pk)->update($rowUpdates);
                $fixedCount++;
            }
        }
    }

    // Pastikan role Ketua KUB terbarui menjadi Admin KUB
    if (\Illuminate\Support\Facades\Schema::hasTable('roles')) {
        \Illuminate\Support\Facades\DB::table('roles')
            ->where('nama_role', 'Ketua KUB')
            ->orWhere('slug', 'ketua_kub')
            ->update([
                'nama_role' => 'Admin KUB',
                'slug' => 'admin_kub',
                'deskripsi' => 'Admin & Pengurus Komunitas Umat Basis (KUB)'
            ]);
    }

    // Sinkronisasi data Kuasi Paroki sesuai data Excel Umat
    if (\Illuminate\Support\Facades\Schema::hasTable('kuasi_paroki')) {
        // 1. Haukoto
        \Illuminate\Support\Facades\DB::table('kuasi_paroki')
            ->where('id', 1)
            ->orWhere('NamaKuasiParoki', 'LIKE', '%Haukoto%')
            ->update([
                'NamaKuasiParoki' => 'Kuasi Paroki Haukoto',
                'nama_kuasi' => 'Kuasi Paroki Haukoto',
                'paroki_id' => 392,
                'dekenat_id' => 13,
                'keuskupan_id' => 5,
                'StatusAktif' => 'N',
                'status' => 'Nonaktif',
            ]);

        // 2. Lasiana
        \Illuminate\Support\Facades\DB::table('kuasi_paroki')
            ->where('id', 2)
            ->orWhere('NamaKuasiParoki', 'LIKE', '%Lasiana%')
            ->update([
                'NamaKuasiParoki' => 'Kuasi Paroki Lasiana',
                'nama_kuasi' => 'Kuasi Paroki Lasiana',
                'paroki_id' => 384,
                'dekenat_id' => 13,
                'keuskupan_id' => 5,
                'StatusAktif' => 'Y',
                'status' => 'Aktif',
            ]);

        // 3. Manulai
        \Illuminate\Support\Facades\DB::table('kuasi_paroki')
            ->where('id', 3)
            ->orWhere('NamaKuasiParoki', 'LIKE', '%Manulai%')
            ->update([
                'NamaKuasiParoki' => 'Kuasi Paroki Manulai',
                'nama_kuasi' => 'Kuasi Paroki Manulai',
                'paroki_id' => 385,
                'dekenat_id' => 13,
                'keuskupan_id' => 5,
                'StatusAktif' => 'Y',
                'status' => 'Aktif',
            ]);

        // 4. Semau
        \Illuminate\Support\Facades\DB::table('kuasi_paroki')
            ->where('id', 4)
            ->orWhere('NamaKuasiParoki', 'LIKE', '%Semau%')
            ->update([
                'NamaKuasiParoki' => 'Kuasi Paroki Semau',
                'nama_kuasi' => 'Kuasi Paroki Semau',
                'paroki_id' => 392,
                'dekenat_id' => 13,
                'keuskupan_id' => 5,
                'pelindung' => 'Santo Petrus Bau-Kuanag',
                'Keterangan' => 'Santo Petrus Bau-Kuanag',
                'StatusAktif' => 'Y',
                'status' => 'Aktif',
            ]);

        // 5. Oesapa
        \Illuminate\Support\Facades\DB::table('kuasi_paroki')
            ->where('id', 5)
            ->orWhere('NamaKuasiParoki', 'LIKE', '%Oesapa%')
            ->update([
                'NamaKuasiParoki' => 'Kuasi Paroki Oesapa',
                'nama_kuasi' => 'Kuasi Paroki Oesapa',
                'paroki_id' => 397,
                'dekenat_id' => 13,
                'keuskupan_id' => 5,
                'pelindung' => 'Santo Petrus dan Paulus',
                'Keterangan' => 'Santo Petrus dan Paulus',
                'StatusAktif' => 'Y',
                'status' => 'Aktif',
            ]);

        // 6. Siolais
        \Illuminate\Support\Facades\DB::table('kuasi_paroki')
            ->where('id', 6)
            ->orWhere('NamaKuasiParoki', 'LIKE', '%Siolais%')
            ->update([
                'NamaKuasiParoki' => 'Sta Maria Reinha Rosari Siolais',
                'nama_kuasi' => 'Sta Maria Reinha Rosari Siolais',
                'paroki_id' => 382,
                'dekenat_id' => 14,
                'keuskupan_id' => 5,
                'StatusAktif' => 'Y',
                'status' => 'Aktif',
            ]);

        // 7. Nunohonis
        \Illuminate\Support\Facades\DB::table('kuasi_paroki')
            ->where('id', 7)
            ->orWhere('NamaKuasiParoki', 'LIKE', '%Nunohonis%')
            ->update([
                'NamaKuasiParoki' => 'Santo Vinsensius (Nunohonis)',
                'nama_kuasi' => 'Santo Vinsensius (Nunohonis)',
                'paroki_id' => 382,
                'dekenat_id' => 14,
                'keuskupan_id' => 5,
                'StatusAktif' => 'N',
                'status' => 'Nonaktif',
            ]);

        // 8. Tahon
        \Illuminate\Support\Facades\DB::table('kuasi_paroki')
            ->where('id', 14)
            ->orWhere('NamaKuasiParoki', 'LIKE', '%Tahon%')
            ->update([
                'NamaKuasiParoki' => 'Tahon – Santa Maria Fatima',
                'nama_kuasi' => 'Tahon – Santa Maria Fatima',
                'paroki_id' => 409,
                'dekenat_id' => 4,
                'keuskupan_id' => 14,
                'StatusAktif' => 'Y',
                'status' => 'Aktif',
            ]);

        // 9. Orphans without parent paroki in excel
        \Illuminate\Support\Facades\DB::table('kuasi_paroki')
            ->whereIn('id', [13, 15, 16, 19, 20, 21])
            ->update([
                'paroki_id' => null,
                'dekenat_id' => null,
                'keuskupan_id' => null,
            ]);

        \Illuminate\Support\Facades\DB::table('kuasi_paroki')->where('id', 15)->update([
            'NamaKuasiParoki' => 'Hati Kudus Yesus - Daruba',
            'nama_kuasi' => 'Hati Kudus Yesus - Daruba',
        ]);
        \Illuminate\Support\Facades\DB::table('kuasi_paroki')->where('id', 16)->update([
            'NamaKuasiParoki' => 'Maronggela - Kurubhoko',
            'nama_kuasi' => 'Maronggela - Kurubhoko',
        ]);
        \Illuminate\Support\Facades\DB::table('kuasi_paroki')->where('id', 13)->update([
            'NamaKuasiParoki' => 'Onekore - Puurere',
            'nama_kuasi' => 'Onekore - Puurere',
        ]);
        \Illuminate\Support\Facades\DB::table('kuasi_paroki')->where('id', 19)->update([
            'NamaKuasiParoki' => 'St. Fransiskus Xaverius - Kairatu/Meliau',
            'nama_kuasi' => 'St. Fransiskus Xaverius - Kairatu/Meliau',
        ]);
        \Illuminate\Support\Facades\DB::table('kuasi_paroki')->where('id', 20)->update([
            'NamaKuasiParoki' => 'St. Petrus - Seira',
            'nama_kuasi' => 'St. Petrus - Seira',
        ]);
        \Illuminate\Support\Facades\DB::table('kuasi_paroki')->where('id', 21)->update([
            'NamaKuasiParoki' => 'St. Petrus dan Paulus - Benu',
            'nama_kuasi' => 'St. Petrus dan Paulus - Benu',
        ]);
    }

    $kuasiSummary = \Illuminate\Support\Facades\DB::table('kuasi_paroki')
        ->select('id', 'NamaKuasiParoki', 'paroki_id', 'dekenat_id', 'keuskupan_id', 'StatusAktif', 'status')
        ->get();

    return response()->json([
        'status' => 'success',
        'message' => "Cache cleared, migrations executed, roles updated, kuasi paroki synced, and {$fixedCount} records with mojibake cleaned successfully.",
        'kuasi_summary' => $kuasiSummary
    ]);
});

// GitHub Auto-Deploy Webhook (Support URL tanpa prefix /api)
Route::match(['GET', 'POST'], '/deploy-webhook', function (\Illuminate\Http\Request $request) {
    $expectedSecret = env('DEPLOY_SECRET_TOKEN', 'SiparokiDeploy2026!');
    $token = $request->query('token') ?: $request->header('X-Deploy-Token');

    $githubSignature = $request->header('X-Hub-Signature-256');
    $isAuthorized = false;

    if ($token && hash_equals($expectedSecret, (string)$token)) {
        $isAuthorized = true;
    } elseif ($githubSignature) {
        $payload = $request->getContent();
        $computedSig = 'sha256=' . hash_hmac('sha256', $payload, $expectedSecret);
        if (hash_equals($computedSig, $githubSignature)) {
            $isAuthorized = true;
        }
    }

    if (!$isAuthorized) {
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthorized: Invalid token or signature'
        ], 403);
    }

    $basePath = base_path();
    $commands = [
        'git pull origin main 2>&1',
        'php artisan migrate --force 2>&1',
        'php artisan view:clear 2>&1',
        'php artisan optimize:clear 2>&1',
    ];

    $logResults = [];
    foreach ($commands as $cmd) {
        $res = @shell_exec("cd {$basePath} && {$cmd}");
        $logResults[$cmd] = trim((string)$res);
    }

    \Illuminate\Support\Facades\Log::info('GitHub Auto-Deploy Webhook executed', $logResults);

    return response()->json([
        'status' => 'success',
        'message' => 'Auto-deploy triggered successfully!',
        'timestamp' => now()->toIso8601String(),
        'results' => $logResults,
    ]);
})->name('web.deploy.webhook');

