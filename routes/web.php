<?php

use App\Http\Controllers\PageController;
use App\Http\Controllers\MasterReferensiController;
use Illuminate\Support\Facades\Route;

if (!function_exists('siparoki_resolve_safe_file')) {
    /**
     * Safely resolve a candidate list of file paths, guaranteeing the final
     * realpath stays inside one of the allowed base directories. Prevents
     * directory traversal (e.g. /assets/uploads/../../.env) from disclosing
     * arbitrary files outside the web root.
     */
    function siparoki_resolve_safe_file(array $candidates, array $allowedBases): ?string
    {
        $normalized = [];
        foreach ($allowedBases as $base) {
            $real = realpath($base);
            if ($real === false) {
                $real = $base;
            }
            $normalized[] = rtrim($real, '/\\') . DIRECTORY_SEPARATOR;
        }

        foreach ($candidates as $candidate) {
            $real = realpath($candidate);
            if ($real === false || !is_file($real)) {
                continue;
            }
            $real .= (is_dir($real) ? DIRECTORY_SEPARATOR : '');
            foreach ($normalized as $base) {
                if (strncmp($real, $base, strlen($base)) === 0) {
                    return $real;
                }
            }
        }

        return null;
    }
}

// Inisialisasi & Setup Paroki Wizard (Untuk Instalasi Baru GitHub / Ganti Paroki)
Route::get('/setup-paroki', [\App\Http\Controllers\SetupParokiController::class, 'index'])->name('setup.paroki');
Route::post('/setup-paroki', [\App\Http\Controllers\SetupParokiController::class, 'save'])->name('setup.paroki.save');
Route::get('/api/setup/hierarchy', [\App\Http\Controllers\SetupParokiController::class, 'getHierarchy'])->name('setup.paroki.hierarchy');
Route::post('/admin/setup-paroki/reset', [\App\Http\Controllers\SetupParokiController::class, 'resetSetup'])->name('setup.paroki.reset');

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
Route::get('/profil-kapela', [PageController::class, 'kapela'])->name('profil-kapela');
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
Route::get('/berita/{slug}', [PageController::class, 'beritaDetail'])->name('berita.detail');
Route::post('/berita/{slug}/komentar', [PageController::class, 'kirimKomentarArtikel'])->name('berita.komentar.kirim')->middleware('throttle:20,1');
Route::get('/artikel', [PageController::class, 'artikel'])->name('artikel');
Route::get('/artikel/{slug}', [PageController::class, 'artikelDetail'])->name('artikel.detail');
Route::post('/artikel/{slug}/komentar', [PageController::class, 'kirimKomentarArtikel'])->name('artikel.komentar.kirim')->middleware('throttle:20,1');
Route::get('/pengumuman', [PageController::class, 'pengumuman'])->name('pengumuman');
Route::get('/pengumuman/{id}', [PageController::class, 'pengumumanDetail'])->name('pengumuman.detail');
Route::get('/renungan', [PageController::class, 'renungan'])->name('renungan');

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

// Pelayanan & Sakramen
Route::get('/pelayanan', [PageController::class, 'pelayanan'])->name('pelayanan');
Route::get('/pengajuan-sakramen', [PageController::class, 'sakramen'])->name('pengajuan-sakramen');
Route::get('/sakramen', [PageController::class, 'sakramen'])->name('sakramen');

// Auth Routes (Login, Register & Lupa Password)
Route::get('/login', [\App\Http\Controllers\AuthController::class, 'showLogin'])->name('login');
Route::get('/masuk', [\App\Http\Controllers\AuthController::class, 'showLogin'])->name('masuk');
Route::get('/admin/login', fn () => redirect('/login'))->name('admin.login');
Route::post('/login', [\App\Http\Controllers\AuthController::class, 'processLogin'])->name('login.process')->middleware('throttle:10,1');

Route::get('/register', [\App\Http\Controllers\AuthController::class, 'showRegister'])->name('register');
Route::get('/daftar', [\App\Http\Controllers\AuthController::class, 'showRegister'])->name('daftar');
Route::post('/register', [\App\Http\Controllers\AuthController::class, 'processRegister'])->name('register.process')->middleware('throttle:10,1');

Route::get('/lupa-password', [\App\Http\Controllers\AuthController::class, 'showForgotPassword'])->name('lupa-password');
Route::get('/forgot-password', [\App\Http\Controllers\AuthController::class, 'showForgotPassword'])->name('forgot-password');
Route::post('/lupa-password', [\App\Http\Controllers\AuthController::class, 'processForgotPassword'])->name('lupa-password.process')->middleware('throttle:10,1');

// API GeoJSON Kapela (Untuk Leaflet Map)
Route::get('/api/kapela-geojson', [PageController::class, 'kapelaGeojson'])->name('api.kapela-geojson');

// Auth Logout
Route::match(['get', 'post'], '/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

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
        public_path('assets/uploads/video/' . $baseName),
        public_path('assets/uploads/profil/' . $baseName),
        public_path('assets/uploads/galeri/' . $baseName),
        public_path('uploads/video/' . $baseName),
        public_path('uploads/profil/' . $baseName),
        public_path('uploads/galeri/' . $baseName),
        storage_path('app/public/' . $path),
    ];
    $safe = siparoki_resolve_safe_file($candidates, [public_path(), storage_path('app/public')]);
    if ($safe) {
        return response()->file($safe);
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
        public_path('uploads/galeri/' . $baseName),
        public_path('assets/uploads/' . $path),
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

// Serve Konoha styles and assets directly from workspace konoha folder
Route::get('/konoha/{file}', function($file) {
    $path = base_path('konoha/' . $file);
    $safe = siparoki_resolve_safe_file([$path], [base_path('konoha')]);
    if ($safe) {
        $ext = pathinfo($safe, PATHINFO_EXTENSION);
        $mime = $ext === 'css' ? 'text/css' : ($ext === 'js' ? 'application/javascript' : mime_content_type($safe));
        return response()->file($safe, ['Content-Type' => $mime]);
    }
    abort(404);
})->where('file', '.*');

// Proxy route: serve CI3 pastor/umat photos by filename
Route::get('/foto-pastor/{filename}', function(string $filename) {
    $baseName = basename($filename);
    $ci3Root = base_path('../../parokibenlutuci31');
    $laravelPublic = public_path();

    $candidates = [
        // Laravel public paths first
        $laravelPublic . '/uploads/pastor/' . $baseName,
        $laravelPublic . '/uploads/profil/' . $baseName,
        $laravelPublic . '/uploads/' . $baseName,
        $laravelPublic . '/assets/uploads/pastor/' . $baseName,
        $laravelPublic . '/assets/uploads/profil/' . $baseName,
        $laravelPublic . '/assets/uploads/' . $baseName,
        // CI3 source paths
        $ci3Root . '/assets/uploads/pastor/' . $baseName,
        $ci3Root . '/assets/uploads/profil/' . $baseName,
        $ci3Root . '/assets/uploads/' . $baseName,
        $ci3Root . '/uploads/pastor/' . $baseName,
        $ci3Root . '/uploads/' . $baseName,
        // Laragon www root
        base_path('../../parokibenlutuci31/assets/uploads/' . $baseName),
        base_path('../../parokibenlutuci31/uploads/' . $baseName),
    ];

    foreach ($candidates as $path) {
        if (file_exists($path) && !is_dir($path)) {
            return response()->file($path);
        }
    }

    // Return default avatar as fallback
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
    'kub' => 'Ketua KUB',
    'bendahara' => 'Bendahara',
    'penulis' => 'Penulis',
    'umat' => 'Umat',
];

$legacyModuleAliases = [
    'kk' => 'kk-katolik',
    'keluarga' => 'kk-katolik',
    'downloads' => 'download',
    'intensi' => 'intensi-misa',
    'lapak' => 'lapak-produk',
    'kuasi_paroki' => 'kuasi-paroki',
    'roles' => 'role',
    'backup_restore' => 'backup-database',
    'security-center' => 'security-settings',
    'clean-uploads' => 'backup-database',
    'sambutan-pastor' => 'profil-paroki',
    'katekumen' => 'pengajuan-sakramen',
    'kanonikal' => 'sakramen',
    'video-header' => 'pengaturan-aplikasi',
    'menu' => 'pengaturan-aplikasi',
    'seo' => 'pengaturan-aplikasi',
    'slider' => 'pengaturan-aplikasi',
    'widget' => 'pengaturan-aplikasi',
];

Route::middleware([\App\Http\Middleware\PanelAccess::class])->group(function () use ($rolePrefixes, $legacyModuleAliases) {
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
        Route::get('/master-pastor/create', [\App\Http\Controllers\InertiaPanelController::class, 'createPastor'])->name("panel.{$prefix}.master-pastor.create");
        Route::get('/pastor/create', [\App\Http\Controllers\InertiaPanelController::class, 'createPastor'])->name("panel.{$prefix}.pastor.create");
        Route::get('/master-referensi/pastor/create', [\App\Http\Controllers\InertiaPanelController::class, 'createPastor'])->name("panel.{$prefix}.master-referensi.pastor.create");
        Route::get('/master-pastor/{id}/edit', [\App\Http\Controllers\InertiaPanelController::class, 'editPastor'])->name("panel.{$prefix}.master-pastor.edit");
        Route::get('/master-pastor/edit/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'editPastor'])->name("panel.{$prefix}.master-pastor.edit.alt");
        Route::get('/pastor/{id}/edit', [\App\Http\Controllers\InertiaPanelController::class, 'editPastor'])->name("panel.{$prefix}.pastor.edit");
        Route::get('/pastor/edit/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'editPastor'])->name("panel.{$prefix}.pastor.edit.alt");
        Route::get('/master-referensi/pastor/edit/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'editPastor'])->name("panel.{$prefix}.master-referensi.pastor.edit");
        Route::post('/master-pastor/store', [\App\Http\Controllers\InertiaPanelController::class, 'storePastor'])->name("panel.{$prefix}.master-pastor.store");
        Route::post('/pastor/store', [\App\Http\Controllers\InertiaPanelController::class, 'storePastor'])->name("panel.{$prefix}.pastor.store");
        Route::post('/master-pastor/{id}/update', [\App\Http\Controllers\InertiaPanelController::class, 'updatePastor'])->name("panel.{$prefix}.master-pastor.update");
        Route::post('/pastor/{id}/update', [\App\Http\Controllers\InertiaPanelController::class, 'updatePastor'])->name("panel.{$prefix}.pastor.update");
        Route::get('/profil-paroki', [\App\Http\Controllers\InertiaPanelController::class, 'profilParoki'])->name("panel.{$prefix}.profil-paroki");
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
        Route::post('/backup-database/generate', [\App\Http\Controllers\InertiaPanelController::class, 'generateDatabaseBackup'])->name("panel.{$prefix}.backup-database.generate");
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

        // Settings Hub (Pengaturan Terpadu: Pembayaran, OTP, Video, Slider, SEO, Widget)
        Route::get('/pengaturan-aplikasi', [\App\Http\Controllers\InertiaPanelController::class, 'pengaturanHub'])->name("panel.{$prefix}.pengaturan-aplikasi");
        Route::get('/pengaturan/pembayaran', fn (\Illuminate\Http\Request $req) => app(\App\Http\Controllers\InertiaPanelController::class)->pengaturanHub($req, 'pembayaran'))->name("panel.{$prefix}.pengaturan.pembayaran");
        Route::get('/pengaturan-pembayaran', fn (\Illuminate\Http\Request $req) => app(\App\Http\Controllers\InertiaPanelController::class)->pengaturanHub($req, 'pembayaran'))->name("panel.{$prefix}.pengaturan-pembayaran");
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

        // Settings Hub POST Actions
        Route::post('/pengaturan/pembayaran/save', [\App\Http\Controllers\InertiaPanelController::class, 'saveMetodePembayaran'])->name("panel.{$prefix}.pengaturan.pembayaran.save");
        Route::delete('/pengaturan/pembayaran/{id}/delete', [\App\Http\Controllers\InertiaPanelController::class, 'deleteMetodePembayaran'])->name("panel.{$prefix}.pengaturan.pembayaran.delete");
        Route::post('/pengaturan/pembayaran/{id}/delete', [\App\Http\Controllers\InertiaPanelController::class, 'deleteMetodePembayaran'])->name("panel.{$prefix}.pengaturan.pembayaran.delete.post");
        Route::post('/pengaturan/otp/save', [\App\Http\Controllers\InertiaPanelController::class, 'savePengaturanOtp'])->name("panel.{$prefix}.pengaturan.otp.save");
        Route::post('/pengaturan/otp/test', [\App\Http\Controllers\InertiaPanelController::class, 'testKirimWhatsapp'])->name("panel.{$prefix}.pengaturan.otp.test");
        Route::post('/pengaturan/video/save', [\App\Http\Controllers\InertiaPanelController::class, 'saveVideoHeader'])->name("panel.{$prefix}.pengaturan.video.save");
        Route::post('/pengaturan/slider/save', [\App\Http\Controllers\InertiaPanelController::class, 'saveSlider'])->name("panel.{$prefix}.pengaturan.slider.save");
        Route::delete('/pengaturan/slider/{id}/delete', [\App\Http\Controllers\InertiaPanelController::class, 'deleteSlider'])->name("panel.{$prefix}.pengaturan.slider.delete");
        Route::post('/pengaturan/slider/{id}/delete', [\App\Http\Controllers\InertiaPanelController::class, 'deleteSlider'])->name("panel.{$prefix}.pengaturan.slider.delete.post");
        Route::post('/pengaturan/seo/save', [\App\Http\Controllers\InertiaPanelController::class, 'saveSeoMeta'])->name("panel.{$prefix}.pengaturan.seo.save");
        Route::post('/pengaturan/widget/save', [\App\Http\Controllers\InertiaPanelController::class, 'saveWidgetSettings'])->name("panel.{$prefix}.pengaturan.widget.save");
        Route::post('/pengaturan/maintenance/save', [\App\Http\Controllers\InertiaPanelController::class, 'saveMaintenanceSettings'])->name("panel.{$prefix}.pengaturan.maintenance.save");

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
        Route::get('/{slug}', [\App\Http\Controllers\InertiaPanelController::class, 'module'])->name("panel.{$prefix}.module");
        Route::post('/{slug}', [\App\Http\Controllers\InertiaPanelController::class, 'storeModule'])->name("panel.{$prefix}.module.store");
        Route::put('/{slug}/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'updateModule'])->name("panel.{$prefix}.module.update");
        Route::post('/{slug}/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'updateModule'])->name("panel.{$prefix}.module.update.post");
        Route::delete('/{slug}/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'destroyModule'])->name("panel.{$prefix}.module.destroy");
    });
}
});

// Global Dashboard & Admin Aliases
Route::middleware([\App\Http\Middleware\PanelAccess::class])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\InertiaPanelController::class, 'dashboard'])->name('dashboard');
});
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
    Route::post('/backup-database/generate', [\App\Http\Controllers\InertiaPanelController::class, 'generateDatabaseBackup'])->name('admin.backup-database.generate');
    Route::get('/backup-database/{id}/download', [\App\Http\Controllers\InertiaPanelController::class, 'downloadDatabaseBackup'])->name('admin.backup-database.download');
    Route::post('/backup-database/{id}/restore', [\App\Http\Controllers\InertiaPanelController::class, 'restoreDatabaseBackup'])->name('admin.backup-database.restore');
    Route::post('/backup-database/upload-restore', [\App\Http\Controllers\InertiaPanelController::class, 'uploadRestoreDatabaseBackup'])->name('admin.backup-database.upload-restore');
    Route::delete('/backup-database/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'deleteDatabaseBackup'])->name('admin.backup-database.delete');
    Route::get('/master-referensi', [MasterReferensiController::class, 'index'])->name('admin.master-referensi.index');
    Route::get('/master-referensi/pastor/create', [\App\Http\Controllers\InertiaPanelController::class, 'createPastor'])->name('admin.master-referensi.pastor.create');
    Route::get('/master-referensi/pastor/{id}/edit', [\App\Http\Controllers\InertiaPanelController::class, 'editPastor'])->name('admin.master-referensi.pastor.edit');
    Route::get('/master-referensi/pastor/edit/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'editPastor'])->name('admin.master-referensi.pastor.edit.alt');
    Route::get('/master-referensi/{type}', [MasterReferensiController::class, 'list'])->name('admin.master-referensi.list');
    Route::get('/master-referensi/options/{relTable}', [MasterReferensiController::class, 'options'])->name('admin.master-referensi.options');
    Route::post('/master-referensi/{type}', [MasterReferensiController::class, 'store'])->name('admin.master-referensi.store');
    Route::put('/master-referensi/{type}/{id}', [MasterReferensiController::class, 'update'])->name('admin.master-referensi.update');
    Route::post('/master-referensi/{type}/{id}', [MasterReferensiController::class, 'update'])->name('admin.master-referensi.update.post');
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
    Route::post('/{slug}/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'updateModule'])->name('admin.module.update.post');
    Route::delete('/{slug}/{id}', [\App\Http\Controllers\InertiaPanelController::class, 'destroyModule'])->name('admin.module.destroy');
});
Route::get('/admin', fn () => redirect('/admin/master-referensi'))->name('admin');

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

// Fallback redirect untuk URL lawas /v2/* ke /superadmin/*
Route::get('/v2/{path?}', function ($path = '') {
    $target = '/superadmin' . ($path ? '/' . $path : '');
    return redirect($target);
})->where('path', '.*');
