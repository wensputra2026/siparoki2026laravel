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
    @stack('styles')
    @if(request()->is('sakramen*'))
        @livewireStyles
    @endif
</head>
<body class="public-portal">

    <!-- ===== HEADER MAIN ===== -->
    <header class="header" role="banner">
        <div class="header-inner">
            <a href="/" class="logo paroki-logo" title="{{ $globalNamaParoki ?? 'SIPAROKI' }}">
                @if(!empty($globalLogo))
                    <img src="{{ $globalLogo }}" alt="Logo {{ $globalNamaParoki ?? 'Paroki' }}" class="church-logo-img" style="width: 42px; height: 42px; object-fit: contain; border-radius: 50%; background: #ffffff; padding: 2px; box-shadow: 0 2px 10px rgba(0,0,0,0.25);">
                @else
                    <div class="church-icon-box">
                        <i class="fa-solid fa-cross"></i>
                    </div>
                @endif
                <span>{{ $globalNamaParoki ?? 'SIPAROKI' }}</span>
            </a>

            <nav class="nav" id="navMenu" role="navigation" aria-label="Navigasi utama">
                <a href="/" class="{{ request()->is('/') ? 'active' : '' }}">Beranda</a>

                {{-- Dropdown Profil --}}
                <div class="nav-dropdown">
                    <button class="nav-dd-btn {{ request()->is('profil*') || request()->is('sejarah*') || request()->is('visi-misi*') || request()->is('riwayat-pastor*') || request()->is('kronik*') || request()->is('struktur*') || request()->is('profil-kapela*') || request()->is('peta-kapela*') || request()->is('direktori*') ? 'active' : '' }}">
                        Profil <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </button>
                    <div class="nav-dd-menu" role="menu">
                        <a href="/profil" role="menuitem">Profil Umum</a>
                        <a href="/sejarah" role="menuitem">Sejarah Paroki</a>
                        <a href="/visi-misi" role="menuitem">Visi &amp; Misi</a>
                        <a href="/riwayat-pastor" role="menuitem">Riwayat Pastor</a>
                        <a href="/kronik" role="menuitem">Kronik Paroki</a>
                        <a href="/struktur" role="menuitem">Dewan Pastoral</a>
                        <a href="/profil-kapela" role="menuitem">Profil Kapela</a>
                        <a href="/direktori-dpp" role="menuitem">Anggota DPP</a>
                        <a href="/direktori-katekis" role="menuitem">Katekis</a>
                        <a href="/direktori-misdinar" role="menuitem">Misdinar</a>
                    </div>
                </div>

                {{-- Dropdown Jadwal --}}
                <div class="nav-dropdown">
                    <button class="nav-dd-btn {{ request()->is('jadwal-misa*') || request()->is('agenda*') || request()->is('kegiatan*') ? 'active' : '' }}">
                        Jadwal <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </button>
                    <div class="nav-dd-menu" role="menu">
                        <a href="/jadwal-misa" role="menuitem">Jadwal Misa</a>
                        <a href="/agenda" role="menuitem">Agenda Kegiatan</a>
                    </div>
                </div>

                {{-- Dropdown Berita --}}
                <div class="nav-dropdown">
                    <button class="nav-dd-btn {{ request()->is('berita*') || request()->is('artikel*') || request()->is('pengumuman*') || request()->is('renungan*') ? 'active' : '' }}">
                        Berita <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </button>
                    <div class="nav-dd-menu" role="menu">
                        <a href="/berita" role="menuitem">Berita Paroki</a>
                        <a href="/artikel" role="menuitem">Artikel &amp; Renungan</a>
                        <a href="/pengumuman" role="menuitem">Pengumuman</a>
                    </div>
                </div>

                {{-- Dropdown Galeri --}}
                <div class="nav-dropdown">
                    <button class="nav-dd-btn {{ request()->is('galeri*') || request()->is('video*') ? 'active' : '' }}">
                        Galeri <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </button>
                    <div class="nav-dd-menu" role="menu">
                        <a href="/galeri" role="menuitem">Galeri Foto</a>
                        <a href="/video" role="menuitem">Video</a>
                    </div>
                </div>

                <a href="/statistik" class="{{ request()->is('statistik*') ? 'active' : '' }}">Statistik</a>
                <a href="/kontak" class="{{ request()->is('kontak*') ? 'active' : '' }}">Kontak</a>
                <a href="/downloads" class="{{ request()->is('downloads*') ? 'active' : '' }}">Download</a>

                {{-- Login / Dashboard Button --}}
                @auth
                    @php
                        $currentUser = auth()->user();
                        $dashboardUrl = '/admin';
                        if ($currentUser->hasRole(['pastor-paroki', 'pastor'])) {
                            $dashboardUrl = '/pastor';
                        } elseif ($currentUser->hasRole('sekretariat')) {
                            $dashboardUrl = '/sekretariat';
                        } elseif ($currentUser->hasRole('bendahara')) {
                            $dashboardUrl = '/bendahara';
                        } elseif ($currentUser->hasRole('umat')) {
                            $dashboardUrl = '/umat';
                        }
                    @endphp
                    <div class="nav-dropdown nav-login-dropdown">
                    <a href="{{ $dashboardUrl }}" class="nav-dd-btn nav-login-btn nav-spmb">
                            <i class="fas fa-th-large me-1.5" aria-hidden="true"></i> Dashboard <i class="fas fa-chevron-down" aria-hidden="true"></i>
                        </a>
                        <div class="nav-dd-menu" role="menu">
                            <div style="padding: 10px 16px; border-bottom: 1px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.04); border-top-left-radius: 12px; border-top-right-radius: 12px;">
                                <div style="font-weight: 700; color: #f8fafc; font-size: 0.88rem;">{{ $currentUser->nama_lengkap ?? $currentUser->username ?? 'User' }}</div>
                                <div style="font-size: 0.72rem; color: #38bdf8; margin-top: 3px;">{{ ucfirst($currentUser->role->nama_role ?? 'Pengguna') }}</div>
                            </div>
                            <a href="{{ $dashboardUrl }}" role="menuitem"><i class="fas fa-columns me-2 text-sky-400"></i> Buka Dashboard</a>
                            @if($currentUser->hasRole(['superadmin', 'admin']))
                                <a href="/admin" role="menuitem"><i class="fas fa-shield-alt me-2 text-sky-400"></i> Admin Utama</a>
                                <a href="/pastor" role="menuitem"><i class="fas fa-church me-2 text-purple-400"></i> Portal Pastor</a>
                                <a href="/sekretariat" role="menuitem"><i class="fas fa-file-alt me-2 text-blue-400"></i> Sekretariat</a>
                                <a href="/bendahara" role="menuitem"><i class="fas fa-coins me-2 text-emerald-400"></i> Bendahara</a>
                                <a href="/umat" role="menuitem"><i class="fas fa-user me-2 text-amber-400"></i> Portal Umat</a>
                            @endif
                            <a href="/logout" role="menuitem" class="!text-red-400 hover:!bg-red-500/10">
                                <i class="fas fa-sign-out-alt me-2"></i> Keluar (Logout)
                            </a>
                        </div>
                    </div>
                @else
                    <a href="/login" class="nav-login-btn nav-spmb">
                        <i class="fas fa-sign-in-alt me-1.5" aria-hidden="true"></i> Login
                    </a>
                @endauth
            </nav>

            <div class="header-actions">
                <button class="dark-toggle" id="darkToggle" aria-label="Alihkan tema" title="Alihkan tema">
                    <i class="fas fa-moon"></i>
                </button>
                <button class="nav-toggle" id="navToggle" aria-label="Buka menu navigasi" aria-expanded="false">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </header>

    {{-- MAIN CONTENT --}}
    <main>
        @yield('content')
    </main>

    {{-- FOOTER --}}
    <footer class="footer" role="contentinfo">
        <div class="footer-shape" aria-hidden="true"></div>
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="/" class="logo paroki-logo">
                    @if(!empty($globalLogo))
                        <img src="{{ $globalLogo }}" alt="Logo {{ $globalNamaParoki ?? 'Paroki' }}" style="width: 40px; height: 40px; object-fit: contain; border-radius: 50%; background: #ffffff; padding: 2px; box-shadow: 0 2px 8px rgba(0,0,0,0.2);">
                    @else
                        <div class="church-icon-box">
                            <i class="fa-solid fa-cross"></i>
                        </div>
                    @endif
                    <span>{{ $globalNamaParoki ?? 'SIPAROKI' }}</span>
                </a>
                <p class="text-sm text-slate-400 mt-3 leading-relaxed">
                    Fontein, Kec. Kota Raja, Kota Kupang, Prov. Nusa Tenggara Timur - Media informasi, pelayanan sakramen, dan pendataan umat.
                </p>
                <div class="flex items-center space-x-3 mt-4 text-slate-400">
                    <a href="#" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-sky-600 hover:text-white flex items-center justify-center transition"><i class="fab fa-facebook-f text-xs"></i></a>
                    <a href="#" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-sky-600 hover:text-white flex items-center justify-center transition"><i class="fab fa-instagram text-xs"></i></a>
                    <a href="#" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-sky-600 hover:text-white flex items-center justify-center transition"><i class="fab fa-youtube text-xs"></i></a>
                    <a href="#" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-sky-600 hover:text-white flex items-center justify-center transition"><i class="fab fa-tiktok text-xs"></i></a>
                </div>
            </div>

            <div class="footer-col">
                <h4>Navigasi</h4>
                <a href="/">Beranda</a>
                <a href="/profil">Profil</a>
                <a href="/jadwal-misa">Jadwal Misa</a>
                <a href="/berita">Berita</a>
                <a href="/galeri">Galeri</a>
                <a href="/kontak">Kontak</a>
            </div>

            <div class="footer-col">
                <h4>Pelayanan</h4>
                <a href="/pelayanan">Daftar Pelayanan</a>
                <a href="/sakramen">Pengajuan Sakramen</a>
                <a href="/profil-kapela">Kapela &amp; Stasi</a>
                <a href="/statistik">Statistik Paroki</a>
            </div>

            <div class="footer-col">
                <h4>Profil</h4>
                <a href="/sejarah">Sejarah Paroki</a>
                <a href="/visi-misi">Visi &amp; Misi</a>
                <a href="/struktur">Struktur Organisasi</a>
                <a href="/riwayat-pastor">Riwayat Pastor</a>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} <a href="/" class="text-sky-400 font-medium">{{ $globalNamaParoki ?? 'SIPAROKI' }}</a> | SIPAROKI - Sistem Informasi Paroki - Hak Cipta Dilindungi.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="/env.js"></script>
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

    <div class="lightbox" id="lightboxModal" role="dialog" aria-modal="true" aria-label="Pratinjau gambar">
        <span class="lightbox-close" role="button" tabindex="0" aria-label="Tutup">&times;</span>
        <img class="lightbox-content" id="modalImage" alt="Pratinjau">
    </div>

    @stack('scripts')
    @if(request()->is('sakramen*'))
        @livewireScripts
    @endif
</body>
</html>


