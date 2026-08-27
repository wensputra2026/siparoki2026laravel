<!DOCTYPE html>
<html lang="id" data-theme="light" class="scroll-smooth">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0" />

    @php
        $metaTitle = trim($__env->yieldContent('title', ($globalNamaParoki ?? 'Paroki') . ' - Sistem Informasi Paroki'));
        $metaDescription = trim($__env->yieldContent('description', 'Website resmi ' . ($globalNamaParoki ?? 'Paroki') . ' untuk informasi jadwal perayaan Ekaristi, sakramen, warta paroki, dan sensus umat Katolik.'));
        $metaKeywords = trim($__env->yieldContent('keywords', 'paroki, gereja katolik, jadwal misa, warta paroki, keuskupan'));
        $metaType = trim($__env->yieldContent('og_type', 'website'));
        $metaImage = trim($__env->yieldContent('image', $globalLogo ?? ''));
        $metaImageUrl = $metaImage
            ? (\Illuminate\Support\Str::startsWith($metaImage, ['http://', 'https://']) ? $metaImage : url($metaImage))
            : null;
    @endphp
    <title>{{ $metaTitle }}</title>
    <meta name="title" content="{{ $metaTitle }}">
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="keywords" content="{{ $metaKeywords }}">
    <meta name="robots" content="index, follow">
    <meta name="language" content="id">
    <meta name="author" content="{{ $globalNamaParoki ?? 'Paroki' }}">
    <meta name="theme-color" content="#0c4a6e">
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="{{ $metaType }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="{{ $globalNamaParoki ?? 'Sistem Informasi Paroki' }}">
    <meta property="og:locale" content="id_ID">
    @if($metaImageUrl)
        <meta property="og:image" content="{{ $metaImageUrl }}">
        <meta property="og:image:secure_url" content="{{ $metaImageUrl }}">
        <meta property="og:image:alt" content="{{ $metaTitle }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
    @endif
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    @if($metaImageUrl)
        <meta name="twitter:image" content="{{ $metaImageUrl }}">
    @endif
    @yield('article_meta')
    @stack('head')

    <!-- Favicon -->
    @if(!empty($globalLogo))
        <link rel="icon" type="image/x-icon" href="{{ $globalLogo }}">
        <link rel="shortcut icon" type="image/x-icon" href="{{ $globalLogo }}">
        <link rel="apple-touch-icon" href="{{ $globalLogo }}">
    @else
        <link rel="icon" type="image/jpeg" href="/assets/uploads/profil/logo_paroki_1787370466.jpeg">
        <link rel="shortcut icon" type="image/jpeg" href="/assets/uploads/profil/logo_paroki_1787370466.jpeg">
        <link rel="apple-touch-icon" href="/assets/uploads/profil/logo_paroki_1787370466.jpeg">
    @endif

    <!-- SIPAROKI Theme: Bootstrap 5 + Icons + Fonts + Theme CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/siparoki-theme.css') }}?v={{ @filemtime(public_path('assets/css/siparoki-theme.css')) ?: time() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/css/lightbox.min.css">

    <!-- Local Vendor Icons -->
    <link rel="stylesheet" href="/vendor/fontawesome/css/all.min.css">
    <link rel="preload" href="/fonts/poppins/poppins-latin-400-normal.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/poppins/poppins-latin-600-normal.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/poppins/poppins-latin-800-normal.woff2" as="font" type="font/woff2" crossorigin>
    @if(request()->is('/') || request()->is('kontak*') || request()->is('peta-kapela*'))
        <link rel="stylesheet" href="/vendor/leaflet/leaflet.css" />
    @endif
    @php
        $publicViteManifest = public_path('build/manifest.json');
        $publicViteCss = file_exists($publicViteManifest)
            ? (json_decode(file_get_contents($publicViteManifest), true)['resources/css/app.css']['file'] ?? null)
            : null;
    @endphp
    @if($publicViteCss)
        <link rel="stylesheet" href="/build/{{ $publicViteCss }}">
    @endif
    <link rel="stylesheet" href="/css/portal-shell.css?v={{ @filemtime(public_path('css/portal-shell.css')) ?: time() }}">
    <link rel="stylesheet" href="/css/siparoki-tailwind-public.css?v={{ @filemtime(public_path('css/siparoki-tailwind-public.css')) ?: time() }}">
    @php
        $rawBanner = $globalBanner 
            ?? $globalProfil->banner 
            ?? $globalProfil->foto_gedung 
            ?? $globalProfil->foto_banner 
            ?? $globalProfil->foto 
            ?? $globalPengaturan->banner 
            ?? $globalPengaturan->banner_header 
            ?? null;

        $resolvedHeaderBg = null;
        if (!empty($rawBanner)) {
            if (str_starts_with($rawBanner, 'http://') || str_starts_with($rawBanner, 'https://')) {
                $resolvedHeaderBg = $rawBanner;
            } elseif (file_exists(public_path($rawBanner))) {
                $resolvedHeaderBg = asset($rawBanner);
            } elseif (file_exists(public_path('assets/' . $rawBanner))) {
                $resolvedHeaderBg = asset('assets/' . $rawBanner);
            } elseif (file_exists(public_path('assets/uploads/profil/' . $rawBanner))) {
                $resolvedHeaderBg = asset('assets/uploads/profil/' . $rawBanner);
            } elseif (file_exists(public_path('uploads/profil/' . $rawBanner))) {
                $resolvedHeaderBg = asset('uploads/profil/' . $rawBanner);
            } elseif (file_exists(public_path('uploads/' . $rawBanner))) {
                $resolvedHeaderBg = asset('uploads/' . $rawBanner);
            } else {
                $resolvedHeaderBg = asset($rawBanner);
            }
        }
        if (empty($resolvedHeaderBg)) {
            $resolvedHeaderBg = asset('assets/uploads/profil/banner_1786529079.JPG');
        }
    @endphp
    @if(!empty($resolvedHeaderBg))
    <style>
        .page-header {
            background: linear-gradient(135deg, rgba(0, 56, 47, 0.88) 0%, rgba(0, 121, 107, 0.85) 50%, rgba(2, 44, 34, 0.92) 100%), url('{{ $resolvedHeaderBg }}') center/cover no-repeat, linear-gradient(135deg, #004d40 0%, #00796b 50%, #00332c 100%) !important;
        }
    </style>
    @endif
    @stack('styles')
    @if(request()->is('sakramen*'))
        @livewireStyles
    @endif
</head>
<body class="public-portal">

    @php
        $topAlamat = !empty($pengaturan->alamat) ? $pengaturan->alamat : (!empty($alamat) ? $alamat : (!empty($globalProfil->alamat) ? $globalProfil->alamat : 'Benlutu, TTS, NTT'));
        $topEmail = !empty($pengaturan->email) ? $pengaturan->email : (!empty($email) ? $email : (!empty($globalProfil->email) ? $globalProfil->email : 'info@parokibenlutu.org'));
        $topTelepon = !empty($pengaturan->telepon) ? $pengaturan->telepon : (!empty($telepon) ? $telepon : (!empty($globalProfil->telepon) ? $globalProfil->telepon : '0812-3456-7890'));
        $topWa = !empty($pengaturan->whatsapp) ? $pengaturan->whatsapp : (!empty($whatsapp) ? $whatsapp : (!empty($globalProfil->whatsapp) ? $globalProfil->whatsapp : $topTelepon));
        $topWaClean = preg_replace('/[^0-9]/', '', $topWa);
    @endphp

    <!-- Top Bar Konoha Style -->
    <div class="top-bar">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6 col-12">
                    <div class="d-flex align-items-center justify-content-center justify-content-md-start">
                        <span class="follow-us me-2">Follow Us:</span>
                        <div class="social-icons">
                            <a href="{{ $pengaturan->facebook_url ?? '#' }}" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                            <a href="{{ $pengaturan->instagram_url ?? '#' }}" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                            <a href="{{ $pengaturan->youtube_url ?? '#' }}" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                            <a href="https://wa.me/{{ $topWaClean }}" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-12 text-end d-none d-md-block">
                    <a href="/kontak" class="text-white me-3"><i class="fas fa-map-marker-alt me-1"></i> {{ $topAlamat }}</a>
                    <a href="mailto:{{ $topEmail }}" class="text-white me-3"><i class="fas fa-envelope me-1"></i> {{ $topEmail }}</a>
                    <a href="tel:{{ $topTelepon }}" class="text-white"><i class="fas fa-phone me-1"></i> {{ $topTelepon }}</a>
                </div>
            </div>
        </div>
    </div>

        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg sticky-top">
            <div class="container">
                <a class="navbar-brand d-inline-flex align-items-center gap-1 gap-sm-2" href="/" style="text-decoration: none;">
                    @if(!empty($globalLogo))
                        <img src="{{ $globalLogo }}" alt="Logo {{ $globalNamaParoki ?? 'Paroki' }}" style="width: 42px; height: 42px; object-fit: contain; border-radius: 50%; background: #fff; padding: 2px; box-shadow: 0 2px 6px rgba(0,0,0,0.1); flex-shrink: 0; display: inline-block;">
                    @else
                        <i class="bi bi-church" style="font-size: 1.8rem; color: var(--primary-teal, #00897b); flex-shrink: 0;"></i>
                    @endif
                    <span style="font-weight: 800; font-size: clamp(0.72rem, 3.2vw, 1.15rem); color: var(--primary-orange, #ff9800); white-space: nowrap; line-height: 1.1; letter-spacing: -0.02em;">{{ $globalNamaParoki ?? 'SIPAROKI' }}</span>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav mx-auto">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="/">Beranda</a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->is('profil*') || request()->is('pelayan-pastoral*') || request()->is('sejarah*') || request()->is('visi-misi*') || request()->is('riwayat-pastor*') || request()->is('kronik*') || request()->is('struktur*') || request()->is('profil-kapela*') || request()->is('peta-kapela*') || request()->is('direktori*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                                Profil
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/profil">Profil Umum</a></li>
                                <li><a class="dropdown-item" href="/pelayan-pastoral">Pelayan Pastoral</a></li>
                                <li><a class="dropdown-item" href="/sejarah">Sejarah Paroki</a></li>
                                <li><a class="dropdown-item" href="/visi-misi">Visi &amp; Misi</a></li>
                                <li><a class="dropdown-item" href="/riwayat-pastor">Riwayat Pastor</a></li>
                                <li><a class="dropdown-item" href="/kronik">Kronik Paroki</a></li>
                                <li><a class="dropdown-item" href="/struktur">Dewan Pastoral</a></li>
                                <li><a class="dropdown-item" href="/profil-kapela">Profil Kapela</a></li>
                                <li><a class="dropdown-item" href="/direktori-dpp">Anggota DPP</a></li>
                                <li><a class="dropdown-item" href="/direktori-katekis">Katekis</a></li>
                                <li><a class="dropdown-item" href="/direktori-misdinar">Misdinar</a></li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->is('jadwal-misa*') || request()->is('agenda*') || request()->is('kegiatan*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                                Jadwal
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/jadwal-misa">Jadwal Misa</a></li>
                                <li><a class="dropdown-item" href="/agenda">Agenda Kegiatan</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('warta*') || request()->is('berita*') || request()->is('artikel*') || request()->is('pengumuman*') || request()->is('renungan*') || request()->is('kategori*') ? 'active' : '' }}" href="/warta">
                                Warta Paroki
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle {{ request()->is('galeri*') || request()->is('video*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown">
                                Galeri
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/galeri">Galeri Foto</a></li>
                                <li><a class="dropdown-item" href="/video">Video</a></li>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('statistik*') ? 'active' : '' }}" href="/statistik">Statistik</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('kontak*') ? 'active' : '' }}" href="/kontak">Kontak</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->is('downloads*') ? 'active' : '' }}" href="/downloads">Download</a>
                        </li>
                    </ul>

                    @auth
                        <div class="dropdown ms-3">
                            @php
                                $currentUser = auth()->user();
                                $slugClean = strtolower(preg_replace('/[^a-z0-9]/', '', $currentUser?->role?->slug ?? $currentUser?->role?->nama_role ?? ''));
                                
                                $dashboardUrl = '/superadmin';
                                $roleLabel = 'Dashboard';
                                
                                if (str_contains($slugClean, 'wilayah')) {
                                    $dashboardUrl = '/wilayah';
                                    $roleLabel = 'Dashboard Wilayah';
                                } elseif (str_contains($slugClean, 'kapela') || str_contains($slugClean, 'stasi')) {
                                    $dashboardUrl = '/kapela';
                                    $roleLabel = 'Dashboard Stasi / Kapela';
                                } elseif (str_contains($slugClean, 'kub')) {
                                    $dashboardUrl = '/kub';
                                    $roleLabel = 'Dashboard KUB';
                                } elseif (str_contains($slugClean, 'pastor')) {
                                    $dashboardUrl = '/pastor';
                                    $roleLabel = 'Dashboard Pastor';
                                } elseif (str_contains($slugClean, 'bendahara')) {
                                    $dashboardUrl = '/bendahara';
                                    $roleLabel = 'Dashboard Keuangan';
                                } elseif (str_contains($slugClean, 'penulis') || str_contains($slugClean, 'komsos')) {
                                    $dashboardUrl = '/penulis';
                                    $roleLabel = 'Dashboard Penulis';
                                } elseif (str_contains($slugClean, 'umat')) {
                                    $dashboardUrl = '/umat';
                                    $roleLabel = 'Dashboard Umat';
                                } elseif (str_contains($slugClean, 'paroki') || str_contains($slugClean, 'sekretariat')) {
                                    $dashboardUrl = '/paroki';
                                    $roleLabel = 'Dashboard Sekretariat';
                                } elseif (str_contains($slugClean, 'super')) {
                                    $dashboardUrl = '/superadmin';
                                    $roleLabel = 'Dashboard Super Admin';
                                }
                            @endphp
                            <button class="btn btn-apply dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-th-large me-1"></i> {{ $currentUser->nama_lengkap ?? $currentUser->username ?? 'User' }}
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li>
                                    <a class="dropdown-item fw-semibold" href="{{ $dashboardUrl }}">
                                        <i class="fas fa-gauge-high me-2 text-primary"></i> {{ $roleLabel }}
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ $dashboardUrl }}/profil-saya">
                                        <i class="fas fa-user-circle me-2 text-muted"></i> Profil Saya
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="/logout" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-sign-out-alt me-2"></i> Keluar (Logout)
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="/login" class="btn-apply ms-3">
                            <i class="fas fa-sign-in-alt me-1"></i> Login
                        </a>
                    @endauth
                </div>
            </div>
        </nav>

    {{-- MAIN CONTENT --}}
    <main>
        @yield('content')
    </main>

    {{-- FOOTER KONOHA STYLE --}}
    <footer class="footer" role="contentinfo">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5>{{ $globalNamaParoki ?? 'St. Vinsensius a Paulo - Benlutu' }}</h5>
                    <p style="color: rgba(255, 255, 255, 0.7); font-size: 0.9rem; line-height: 1.6; margin-bottom: 20px;">
                        Benlutu, Kec. Batu Putih, Kab. Timor Tengah Selatan, Prov. Nusa Tenggara Timur - Media informasi, pelayanan sakramen, dan pendataan umat paroki.
                    </p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                        <a href="#"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6 mb-4">
                    <h5>Menu</h5>
                    <ul>
                        <li><a href="/">Beranda</a></li>
                        <li><a href="/profil">Tentang</a></li>
                        <li><a href="/warta">Warta Paroki</a></li>
                        <li><a href="/jadwal-misa">Jadwal Misa</a></li>
                        <li><a href="/kontak">Kontak</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Pelayanan</h5>
                    <ul>
                        <li><a href="/pelayanan">Daftar Pelayanan</a></li>
                        <li><a href="/sakramen">Pengajuan Sakramen</a></li>
                        <li><a href="/profil-kapela">Kapela &amp; Stasi</a></li>
                        <li><a href="/downloads">Pusat Unduhan</a></li>
                        <li><a href="/statistik">Statistik Paroki</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6 mb-4">
                    <h5>Informasi</h5>
                    <ul>
                        <li><a href="/sejarah">Sejarah Paroki</a></li>
                        <li><a href="/visi-misi">Visi &amp; Misi</a></li>
                        <li><a href="/struktur">Struktur Organisasi</a></li>
                        <li><a href="/riwayat-pastor">Riwayat Pastor</a></li>
                        <li><a href="/pengumuman">Warta Pengumuman</a></li>
                    </ul>
                </div>
            </div>

            <!-- Visitor Stats Konoha Style (100% Database Driven) -->
            @php
                $statUmat   = $stats['total_umat'] ?? $global_stats['total_umat'] ?? (\Illuminate\Support\Facades\Schema::hasTable('umat') ? \Illuminate\Support\Facades\DB::table('umat')->count() : 0);
                $statStasi  = $stats['total_kapela'] ?? $global_stats['total_kapela'] ?? ((\Illuminate\Support\Facades\Schema::hasTable('kapela') ? \Illuminate\Support\Facades\DB::table('kapela')->count() : 0) + (\Illuminate\Support\Facades\Schema::hasTable('stasi_kapela') ? \Illuminate\Support\Facades\DB::table('stasi_kapela')->count() : 0));
                $statKub    = $stats['total_kub'] ?? $global_stats['total_kub'] ?? ((\Illuminate\Support\Facades\Schema::hasTable('kub') ? \Illuminate\Support\Facades\DB::table('kub')->count() : 0) + (\Illuminate\Support\Facades\Schema::hasTable('lingkungan') ? \Illuminate\Support\Facades\DB::table('lingkungan')->count() : 0));
            @endphp
            <div class="visitor-stats d-flex justify-content-center gap-3 flex-wrap">
                <div class="visitor-item">
                    <i class="fas fa-users"></i>
                    <span class="visitor-count">{{ number_format($statUmat, 0, ',', '.') }}</span>
                    <span class="visitor-label">Total Jiwa Umat</span>
                </div>
                <div class="visitor-item">
                    <i class="fas fa-church"></i>
                    <span class="visitor-count">{{ number_format($statStasi, 0, ',', '.') }}</span>
                    <span class="visitor-label">Stasi & Kapela</span>
                </div>
                <div class="visitor-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span class="visitor-count">{{ number_format($statKub, 0, ',', '.') }}</span>
                    <span class="visitor-label">Komunitas KUB</span>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} {{ $globalNamaParoki ?? 'St. Vinsensius a Paulo - Benlutu' }}. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="/env.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.3/dist/js/lightbox.min.js"></script>
    <script>
        $(document).ready(function() {
            if (typeof lightbox !== 'undefined') {
                lightbox.option({
                    'resizeDuration': 200,
                    'wrapAround': true,
                    'albumLabel': 'Foto %1 dari %2',
                    'fadeDuration': 250,
                    'imageFadeDuration': 250
                });
            }
        });
    </script>
    @if(request()->is('/') || request()->is('kontak*') || request()->is('peta-kapela*'))
        <script src="/vendor/leaflet/leaflet.js"></script>
    @endif
    <script src="/js/portal-shell.js?v={{ @filemtime(public_path('js/portal-shell.js')) ?: time() }}" defer></script>
    <script src="/js/siparoki-public.js?v={{ @filemtime(public_path('js/siparoki-public.js')) ?: time() }}" defer></script>
    <script src="/js/public-layout.js?v={{ @filemtime(public_path('js/public-layout.js')) ?: time() }}" defer></script>

    <!-- Back to Top Floating Button -->
    <button id="backToTop" class="back-to-top" aria-label="Kembali ke atas halaman" title="Kembali ke atas">
        <i class="fas fa-chevron-up"></i>
    </button>

    @stack('scripts')
    @if(request()->is('sakramen*'))
        @livewireScripts
    @endif
</body>
</html>
