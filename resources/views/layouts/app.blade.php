<!DOCTYPE html>
<html lang="id" prefix="og: https://ogp.me/ns# fb: https://ogp.me/ns/fb# article: https://ogp.me/ns/article#" data-theme="light">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0" />

    <!-- Basic Meta Tags -->
    <title>@yield('title', 'Kristus Raja - Katedral / Bonipoi - Sistem Informasi Paroki')</title>
    <meta name="title" content="@yield('title', 'Kristus Raja - Katedral / Bonipoi - Sistem Informasi Paroki')">
    <meta name="description" content="@yield('description', 'Website resmi Kristus Raja - Katedral / Bonipoi untuk informasi jadwal perayaan Ekaristi, sakramen, warta paroki, dan sensus umat Katolik.')">
    <meta name="keywords" content="paroki katedral kupang, kristus raja bonipoi, gereja katolik kupang, jadwal misa, warta paroki, keuskupan agung kupang">
    <meta name="author" content="Kristus Raja - Katedral / Bonipoi">
    <meta name="theme-color" content="#0c4a6e">

    <!-- Fonts & Vendor Icons (CDN) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: ['selector', '[data-theme="dark"]'],
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        primary: '#0c4a6e',
                        accent: '#0284c7',
                    }
                }
            }
        }
    </script>

    <style>
        :root {
            --font: 'Poppins', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
            --primary: #0c4a6e;
            --primary-dark: #07273b;
            --accent: #0284c7;
            --accent-dark: #0369a1;
            --surface: #ffffff;
            --border: #e2e8f0;
            --text-main: #0f172a;
            --text-soft: #475569;
            --text-muted: #94a3b8;
            --radius: 16px;
            --shadow-lg: 0 14px 34px rgba(15, 23, 42, 0.08);
        }

        html, body {
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-color: #f8fafc;
            color: #0f172a;
        }

        main { flex: 1 0 auto; }

        /* Dark Theme Variables */
        [data-theme="dark"] {
            --surface: #101d31;
            --border: #263a55;
            --text-main: #f8fbff;
            --text-soft: #cbd5e1;
            --text-muted: #64748b;
        }
        [data-theme="dark"] body {
            background-color: #07111f !important;
            color: #e5edf8 !important;
        }

        /* ─── Ultra Elegant Deep Sapphire Glassmorphism Header ─── */
        .header {
            position: sticky !important;
            top: 0 !important;
            left: 0 !important;
            right: 0 !important;
            z-index: 1000 !important;
            background: linear-gradient(135deg, rgba(6, 20, 39, 0.96) 0%, rgba(10, 32, 60, 0.93) 50%, rgba(7, 16, 31, 0.97) 100%) !important;
            backdrop-filter: blur(20px) saturate(1.8) !important;
            -webkit-backdrop-filter: blur(20px) saturate(1.8) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.12) !important;
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5) !important;
            transition: all 0.3s ease !important;
        }

        .header-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 74px;
            max-width: 1240px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .logo.paroki-logo {
            display: inline-flex !important;
            align-items: center !important;
            gap: 12px !important;
            text-decoration: none !important;
            margin-right: 18px !important;
        }
        .logo.paroki-logo .church-icon-box {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            border: 1px solid rgba(255,255,255,0.25);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1.25rem;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
        }
        .logo.paroki-logo span {
            font-size: 0.95rem !important;
            font-weight: 700 !important;
            color: #ffffff !important;
            white-space: nowrap !important;
            line-height: 1.2 !important;
        }

        .header .nav {
            display: flex;
            align-items: center;
            gap: 4px;
            margin: 0;
            padding: 0;
        }
        .header .nav > a,
        .header .nav-dd-btn {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: 38px !important;
            padding: 0 12px !important;
            font-size: 0.88rem !important;
            font-weight: 500 !important;
            color: rgba(255, 255, 255, 0.88) !important;
            border-radius: 6px !important;
            background: transparent !important;
            border: none !important;
            cursor: pointer;
            text-decoration: none !important;
            transition: all 0.2s ease !important;
        }
        .header .nav > a:hover,
        .header .nav-dd-btn:hover {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.08) !important;
        }
        .header .nav > a.active,
        .header .nav-dd-btn.active {
            color: #38bdf8 !important;
            background: rgba(56, 189, 248, 0.1) !important;
            font-weight: 600 !important;
        }

        /* Dropdowns */
        .header .nav-dropdown {
            position: relative;
            display: inline-flex;
            align-items: center;
            height: 50px;
        }
        .header .nav-dd-btn i {
            font-size: 0.65rem !important;
            margin-left: 6px !important;
            color: rgba(255, 255, 255, 0.65) !important;
            transition: transform 0.25s ease !important;
        }
        .header .nav-dropdown:hover .nav-dd-btn i,
        .header .nav-dropdown.active .nav-dd-btn i {
            transform: rotate(180deg) !important;
        }
        .header .nav-dd-menu {
            position: absolute !important;
            top: 100% !important;
            left: 50% !important;
            transform: translateX(-50%) translateY(0px) !important;
            min-width: 220px !important;
            background: rgba(11, 22, 40, 0.98) !important;
            backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(255, 255, 255, 0.14) !important;
            border-radius: 12px !important;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6) !important;
            padding: 8px !important;
            z-index: 1000 !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
            transition: all 0.2s ease !important;
        }
        /* Invisible hover bridge to eliminate mouse deadzone */
        .header .nav-dd-menu::before {
            content: '';
            position: absolute;
            top: -12px;
            left: 0;
            right: 0;
            height: 14px;
            background: transparent;
        }
        .header .nav-dropdown:hover .nav-dd-menu,
        .header .nav-dropdown.active .nav-dd-menu {
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
            transform: translateX(-50%) translateY(2px) !important;
        }
        .header .nav-dd-menu a {
            display: block !important;
            padding: 9px 14px !important;
            font-size: 0.84rem !important;
            color: #cbd5e1 !important;
            border-radius: 8px !important;
            text-decoration: none !important;
            transition: all 0.2s ease !important;
            white-space: nowrap !important;
        }
        .header .nav-dd-menu a:hover {
            color: #38bdf8 !important;
            background: rgba(56, 189, 248, 0.14) !important;
            transform: translateX(3px) !important;
        }

        /* Dashboard Button in Nav */
        .header .nav-login-btn {
            height: 36px !important;
            padding: 0 16px !important;
            margin-left: 6px !important;
            border-radius: 8px !important;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%) !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            box-shadow: 0 3px 10px rgba(2, 132, 199, 0.3) !important;
            border: 1px solid rgba(255, 255, 255, 0.18) !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            cursor: pointer;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .dark-toggle, .nav-toggle {
            width: 36px !important;
            height: 36px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 8px !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            background: rgba(255, 255, 255, 0.08) !important;
            color: #38bdf8 !important;
            cursor: pointer;
            transition: all 0.25s ease !important;
        }
        .dark-toggle:hover, .nav-toggle:hover {
            background: rgba(255, 255, 255, 0.18) !important;
            color: #ffffff !important;
        }
        .nav-toggle { display: none !important; }

        @media (max-width: 991px) {
            .nav-toggle { display: inline-flex !important; }
            .header .nav {
                position: fixed !important;
                top: 74px !important;
                left: 12px !important;
                right: 12px !important;
                background: rgba(11, 22, 40, 0.98) !important;
                backdrop-filter: blur(20px) !important;
                border: 1px solid rgba(255, 255, 255, 0.14) !important;
                border-radius: 18px !important;
                padding: 16px !important;
                box-shadow: 0 25px 60px rgba(0, 0, 0, 0.75) !important;
                flex-direction: column !important;
                gap: 4px !important;
                max-height: calc(100vh - 90px) !important;
                overflow-y: auto !important;
                display: none !important;
                z-index: 99999 !important;
            }
            .header .nav.active { display: flex !important; }
            .header .nav > a, .header .nav-dd-btn {
                width: 100% !important;
                justify-content: space-between !important;
                height: 42px !important;
            }
            .header .nav-dropdown {
                width: 100% !important;
                height: auto !important;
                flex-direction: column !important;
            }
            .header .nav-dropdown .nav-dd-menu {
                position: static !important;
                transform: none !important;
                box-shadow: none !important;
                border-left: 3px solid #38bdf8 !important;
                width: 100% !important;
                max-height: 0 !important;
                overflow: hidden !important;
                opacity: 0 !important;
                visibility: hidden !important;
                display: block !important;
                padding: 0 6px !important;
                transition: max-height 0.35s ease, opacity 0.25s ease !important;
            }
            .header .nav-dropdown.active .nav-dd-menu {
                max-height: 800px !important;
                opacity: 1 !important;
                visibility: visible !important;
                padding: 6px !important;
            }
        }

        /* ─── Footer ─── */
        footer.footer {
            background: #050b14;
            color: #c8d5e7;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            margin-top: auto;
        }
        .footer-grid {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr;
            gap: 36px;
            padding: 60px 0 40px;
            max-width: 1240px;
            margin: 0 auto;
            padding-left: 20px;
            padding-right: 20px;
        }
        @media (max-width: 991px) {
            .footer-grid { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 640px) {
            .footer-grid { grid-template-columns: 1fr; }
        }
        .footer-col h4 {
            color: #ffffff;
            font-size: 0.92rem;
            font-weight: 700;
            margin-bottom: 18px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .footer-col a {
            display: block;
            color: #94a3b8;
            font-size: 0.85rem;
            margin-bottom: 10px;
            text-decoration: none;
            transition: color 0.2s ease, transform 0.2s ease;
        }
        .footer-col a:hover {
            color: #38bdf8;
            transform: translateX(4px);
        }
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding: 22px 20px;
            text-align: center;
            font-size: 0.8rem;
            color: #64748b;
        }
    </style>

    @livewireStyles
</head>
<body>

    <!-- ===== HEADER MAIN ===== -->
    <header class="header" role="banner">
        <div class="header-inner">
            <a href="/" wire:navigate class="logo paroki-logo" title="Kristus Raja - Katedral / Bonipoi">
                <div class="church-icon-box">
                    <i class="fa-solid fa-cross"></i>
                </div>
                <span>Kristus Raja - Katedral / Bonipoi</span>
            </a>

            <nav class="nav" id="navMenu" role="navigation" aria-label="Navigasi utama">
                <a href="/" wire:navigate class="{{ request()->is('/') ? 'active' : '' }}">Beranda</a>

                {{-- Dropdown Profil --}}
                <div class="nav-dropdown">
                    <button class="nav-dd-btn {{ request()->is('profil*') || request()->is('sejarah*') || request()->is('visi-misi*') || request()->is('riwayat-pastor*') || request()->is('kronik*') || request()->is('struktur*') || request()->is('kapela*') || request()->is('direktori*') ? 'active' : '' }}">
                        Profil <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </button>
                    <div class="nav-dd-menu" role="menu">
                        <a href="/profil" wire:navigate role="menuitem">Profil Umum</a>
                        <a href="/sejarah" wire:navigate role="menuitem">Sejarah Paroki</a>
                        <a href="/visi-misi" wire:navigate role="menuitem">Visi &amp; Misi</a>
                        <a href="/riwayat-pastor" wire:navigate role="menuitem">Riwayat Pastor</a>
                        <a href="/kronik" wire:navigate role="menuitem">Kronik Paroki</a>
                        <a href="/struktur" wire:navigate role="menuitem">Dewan Pastoral</a>
                        <a href="/kapela" wire:navigate role="menuitem">Profil Kapela</a>
                        <a href="/direktori-dpp" wire:navigate role="menuitem">Anggota DPP</a>
                        <a href="/direktori-katekis" wire:navigate role="menuitem">Katekis</a>
                        <a href="/direktori-misdinar" wire:navigate role="menuitem">Misdinar</a>
                    </div>
                </div>

                {{-- Dropdown Jadwal --}}
                <div class="nav-dropdown">
                    <button class="nav-dd-btn {{ request()->is('jadwal-misa*') || request()->is('agenda*') || request()->is('kegiatan*') ? 'active' : '' }}">
                        Jadwal <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </button>
                    <div class="nav-dd-menu" role="menu">
                        <a href="/jadwal-misa" wire:navigate role="menuitem">Jadwal Misa</a>
                        <a href="/agenda" wire:navigate role="menuitem">Agenda Kegiatan</a>
                    </div>
                </div>

                {{-- Dropdown Berita --}}
                <div class="nav-dropdown">
                    <button class="nav-dd-btn {{ request()->is('berita*') || request()->is('artikel*') || request()->is('pengumuman*') || request()->is('renungan*') ? 'active' : '' }}">
                        Berita <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </button>
                    <div class="nav-dd-menu" role="menu">
                        <a href="/berita" wire:navigate role="menuitem">Berita Paroki</a>
                        <a href="/artikel" wire:navigate role="menuitem">Artikel &amp; Renungan</a>
                        <a href="/pengumuman" wire:navigate role="menuitem">Pengumuman</a>
                    </div>
                </div>

                {{-- Dropdown Galeri --}}
                <div class="nav-dropdown">
                    <button class="nav-dd-btn {{ request()->is('galeri*') || request()->is('video*') ? 'active' : '' }}">
                        Galeri <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </button>
                    <div class="nav-dd-menu" role="menu">
                        <a href="/galeri" wire:navigate role="menuitem">Galeri Foto</a>
                        <a href="/video" wire:navigate role="menuitem">Video</a>
                    </div>
                </div>

                <a href="/statistik" wire:navigate class="{{ request()->is('statistik*') ? 'active' : '' }}">Statistik</a>
                <a href="/kontak" wire:navigate class="{{ request()->is('kontak*') ? 'active' : '' }}">Kontak</a>
                <a href="/downloads" wire:navigate class="{{ request()->is('downloads*') ? 'active' : '' }}">Download</a>

                {{-- Dashboard Dropdown --}}
                <div class="nav-dropdown nav-login-dropdown">
                    <button class="nav-dd-btn nav-login-btn">
                        <i class="fas fa-th-large" aria-hidden="true"></i> Dashboard <i class="fas fa-chevron-down" aria-hidden="true"></i>
                    </button>
                    <div class="nav-dd-menu" role="menu">
                        <div style="padding: 10px 16px; border-bottom: 1px solid rgba(255,255,255,0.08); background: rgba(255,255,255,0.04); border-top-left-radius: 12px; border-top-right-radius: 12px;">
                            <div style="font-weight: 700; color: #f8fafc; font-size: 0.88rem;">SIPAROKI Panels</div>
                            <div style="font-size: 0.72rem; color: #38bdf8; margin-top: 3px;">Pilih Level Akses</div>
                        </div>
                        <a href="/admin" role="menuitem"><i class="fas fa-columns me-2 text-sky-400"></i> Admin Utama</a>
                        <a href="/pastor" role="menuitem"><i class="fas fa-church me-2 text-purple-400"></i> Portal Pastor</a>
                        <a href="/sekretariat" role="menuitem"><i class="fas fa-file-alt me-2 text-blue-400"></i> Sekretariat</a>
                        <a href="/bendahara" role="menuitem"><i class="fas fa-coins me-2 text-emerald-400"></i> Bendahara</a>
                        <a href="/umat" role="menuitem"><i class="fas fa-user me-2 text-amber-400"></i> Portal Umat</a>
                        <a href="/sakramen" wire:navigate role="menuitem"><i class="fas fa-cross me-2 text-red-400"></i> Pengajuan Sakramen</a>
                    </div>
                </div>
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
        <div class="footer-grid">
            <div class="footer-brand">
                <a href="/" wire:navigate class="logo paroki-logo">
                    <div class="church-icon-box">
                        <i class="fa-solid fa-cross"></i>
                    </div>
                    <span>Kristus Raja - Katedral / Bonipoi</span>
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
                <a href="/" wire:navigate>Beranda</a>
                <a href="/profil" wire:navigate>Profil</a>
                <a href="/jadwal-misa" wire:navigate>Jadwal Misa</a>
                <a href="/berita" wire:navigate>Berita</a>
                <a href="/galeri" wire:navigate>Galeri</a>
                <a href="/kontak" wire:navigate>Kontak</a>
            </div>

            <div class="footer-col">
                <h4>Pelayanan</h4>
                <a href="/pelayanan" wire:navigate>Daftar Pelayanan</a>
                <a href="/sakramen" wire:navigate>Pengajuan Sakramen</a>
                <a href="/kapela" wire:navigate>Kapela &amp; Stasi</a>
                <a href="/statistik" wire:navigate>Statistik Paroki</a>
            </div>

            <div class="footer-col">
                <h4>Profil</h4>
                <a href="/sejarah" wire:navigate>Sejarah Paroki</a>
                <a href="/visi-misi" wire:navigate>Visi &amp; Misi</a>
                <a href="/struktur" wire:navigate>Struktur Organisasi</a>
                <a href="/riwayat-pastor" wire:navigate>Riwayat Pastor</a>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; {{ date('Y') }} <a href="/" wire:navigate class="text-sky-400 font-medium">Kristus Raja - Katedral / Bonipoi</a> | SIPAROKI - Sistem Informasi Paroki - Hak Cipta Dilindungi.</p>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Dark Mode & Mobile Nav Logic
        (function() {
            var navToggle = document.getElementById('navToggle');
            var darkToggle = document.getElementById('darkToggle');
            var html = document.documentElement;
            var savedTheme = localStorage.getItem('theme') || 'light';
            html.setAttribute('data-theme', savedTheme);

            if (darkToggle) {
                var icon = darkToggle.querySelector('i');
                if (icon) icon.className = savedTheme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
                darkToggle.addEventListener('click', function() {
                    var current = html.getAttribute('data-theme') || 'light';
                    var next = current === 'dark' ? 'light' : 'dark';
                    html.setAttribute('data-theme', next);
                    localStorage.setItem('theme', next);
                    if (icon) icon.className = next === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
                });
            }

            if (navToggle) {
                navToggle.addEventListener('click', function() {
                    var nav = document.getElementById('navMenu');
                    if (!nav) return;
                    nav.classList.toggle('active');
                    var icon = navToggle.querySelector('i');
                    if (icon) icon.className = nav.classList.contains('active') ? 'fas fa-times' : 'fas fa-bars';
                });
            }

            // Dropdown Click & Outside Click Management
            document.querySelectorAll('.header .nav-dropdown').forEach(function(dd) {
                var btn = dd.querySelector('.nav-dd-btn, .nav-login-btn');
                if (btn) {
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        var wasActive = dd.classList.contains('active');
                        document.querySelectorAll('.header .nav-dropdown').forEach(function(other) {
                            other.classList.remove('active');
                        });
                        if (!wasActive) {
                            dd.classList.add('active');
                        }
                    });
                }
            });

            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.header .nav-dropdown')) {
                    document.querySelectorAll('.header .nav-dropdown').forEach(function(dd) {
                        dd.classList.remove('active');
                    });
                }
            });

            // Close mobile menu on link click
            document.querySelectorAll('.header .nav a').forEach(function(link) {
                link.addEventListener('click', function() {
                    var nav = document.getElementById('navMenu');
                    if (nav && window.innerWidth <= 991) {
                        nav.classList.remove('active');
                        var navToggle = document.getElementById('navToggle');
                        if (navToggle) {
                            var icon = navToggle.querySelector('i');
                            if (icon) icon.className = 'fas fa-bars';
                        }
                    }
                    document.querySelectorAll('.header .nav-dropdown').forEach(function(dd) {
                        dd.classList.remove('active');
                    });
                });
            });
        })();
    </script>

    @livewireScripts
</body>
</html>
