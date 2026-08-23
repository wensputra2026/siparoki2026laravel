@extends('layouts.app')

@section('title', 'Kristus Raja - Katedral / Bonipoi - Sistem Informasi Paroki')
@section('description', 'Website resmi Kristus Raja - Katedral / Bonipoi untuk informasi jadwal perayaan Ekaristi, sakramen, warta paroki, dan sensus umat Katolik.')

@section('content')
<style>
/* ─── Hero Section ─── */
.hero {
    position: relative;
    overflow: hidden;
    background: #090e1a !important;
    min-height: calc(100vh - 74px) !important;
    display: flex !important;
    align-items: center;
    justify-content: center;
    padding: 60px 0 !important;
}
.hero-bg-gradient {
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at center, #1e293b 0%, #090e1a 100%);
    z-index: 1;
}
.hero-video-text {
    position: relative;
    z-index: 3;
    width: min(880px, calc(100% - 48px));
    margin: 0 auto;
    text-align: center;
    color: #ffffff;
    text-shadow: 0 4px 24px rgba(0, 0, 0, 0.45);
}
.hero-logo-intro {
    width: 88px;
    height: 88px;
    margin: 0 auto 20px;
    padding: 12px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.95);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #0284c7;
    font-size: 2.2rem;
}
.hero-video-text h1 {
    margin: 0 0 16px;
    color: #ffffff;
    font-size: clamp(1.8rem, 3.4vw, 2.8rem);
    font-weight: 800;
    line-height: 1.2;
}
.hero-typing-text {
    display: inline-block;
    border-right: 3px solid rgba(255, 255, 255, 0.88);
    animation: heroCaret 0.72s step-end infinite;
}
.hero-video-text p {
    max-width: 760px;
    margin: 0 auto;
    color: rgba(255, 255, 255, 0.88);
    font-size: clamp(0.95rem, 1.6vw, 1.15rem);
    line-height: 1.65;
}
@keyframes heroCaret {
    0%, 100% { border-color: transparent; }
    50% { border-color: rgba(255, 255, 255, 0.88); }
}

/* Quote Ticker */
.hero-quote-ticker {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 3;
    width: 100%;
    overflow: hidden;
    border-top: 1px solid rgba(255, 255, 255, 0.2);
    background: rgba(15, 23, 42, 0.75);
    color: #ffffff;
    backdrop-filter: blur(14px);
    display: flex;
    align-items: center;
}
.hero-quote-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: rgba(15, 23, 42, 0.95);
    border-right: 1px solid rgba(251, 191, 36, 0.35);
    color: #fbbf24;
    font-weight: 800;
    font-size: 0.85rem;
    letter-spacing: 0.05em;
    text-transform: uppercase;
    white-space: nowrap;
    flex-shrink: 0;
}
.hero-quote-marquee {
    flex: 1;
    min-width: 0;
    overflow: hidden;
    display: flex;
    align-items: center;
}
.hero-quote-track {
    display: inline-flex;
    align-items: center;
    gap: 28px;
    min-width: 100%;
    white-space: nowrap;
    padding: 13px 0;
    animation: heroQuoteScroll 35s linear infinite;
}
.hero-quote-item {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.92rem;
    color: #f1f5f9;
}
@keyframes heroQuoteScroll {
    0% { transform: translateX(100%); }
    100% { transform: translateX(-100%); }
}

/* Counter */
.counter-section {
    padding: 40px 20px 0;
}
.counter-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    max-width: 1200px;
    margin: 0 auto;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.06);
}
[data-theme="dark"] .counter-grid {
    background: #101d31;
    border-color: #263a55;
}
.counter-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 10px;
}
.cc-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: rgba(2, 132, 199, 0.1);
    color: #0284c7;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.4rem;
}
.cc-content h3 {
    font-size: 1.6rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.1;
    margin: 0;
}
[data-theme="dark"] .cc-content h3 { color: #ffffff; }
.cc-content p {
    font-size: 0.82rem;
    color: #64748b;
    margin: 4px 0 0;
}

/* Sambutan Pastor */
.sambutan-pastor-section {
    background: linear-gradient(135deg, #f8fafc 0%, #eef8fb 100%);
    padding: 70px 20px;
}
[data-theme="dark"] .sambutan-pastor-section {
    background: #090e1a;
}
.sambutan-pastor-card {
    display: grid;
    grid-template-columns: 320px 1fr;
    max-width: 980px;
    margin: 0 auto;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 24px;
    overflow: hidden;
    box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
}
[data-theme="dark"] .sambutan-pastor-card {
    background: #101d31;
    border-color: #263a55;
}
.sambutan-pastor-photo {
    position: relative;
    background: linear-gradient(135deg, #0c4a6e, #0284c7);
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 280px;
    color: #ffffff;
    font-size: 4.5rem;
}
.sambutan-pastor-badge {
    position: absolute;
    top: 18px;
    left: 18px;
    background: linear-gradient(135deg, #f59e0b, #d97706);
    color: #0f172a;
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    padding: 6px 14px;
    border-radius: 999px;
}
.sambutan-pastor-body {
    padding: 40px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}
.sambutan-pastor-body h3 {
    font-size: 1.8rem;
    font-weight: 800;
    color: #0c4a6e;
    margin: 0 0 6px;
}
[data-theme="dark"] .sambutan-pastor-body h3 { color: #38bdf8; }
.sambutan-pastor-role {
    font-size: 0.88rem;
    font-weight: 600;
    color: #0284c7;
    margin-bottom: 16px;
}
.sambutan-pastor-body p {
    font-size: 0.95rem;
    line-height: 1.75;
    color: #475569;
    font-style: italic;
    margin-bottom: 24px;
}
[data-theme="dark"] .sambutan-pastor-body p { color: #cbd5e1; }

/* Section Titles */
.section-title {
    text-align: center;
    margin-bottom: 40px;
}
.st-badge {
    display: inline-block;
    background: rgba(2, 132, 199, 0.1);
    color: #0284c7;
    font-size: 0.78rem;
    font-weight: 700;
    padding: 5px 14px;
    border-radius: 99px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 10px;
}
.section-title h2 {
    font-size: 2.2rem;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 8px;
}
[data-theme="dark"] .section-title h2 { color: #ffffff; }
.text-gradient {
    background: linear-gradient(135deg, #0284c7, #38bdf8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.section-title p {
    font-size: 0.95rem;
    color: #64748b;
    max-width: 650px;
    margin: 0 auto;
}

@media (max-width: 768px) {
    .counter-grid { grid-template-columns: repeat(2, 1fr); }
    .sambutan-pastor-card { grid-template-columns: 1fr; }
}
</style>

<!-- ===== HERO ===== -->
<section class="hero" id="main-content" aria-label="Beranda Kristus Raja - Katedral / Bonipoi">
    <div class="hero-bg-gradient"></div>
    <div class="hero-video-text">
        <div class="hero-logo-intro" aria-hidden="true">
            <i class="fa-solid fa-cross"></i>
        </div>
        <h1>
            <span class="hero-typing-text" data-typing-text="Selamat Datang di Website Resmi Kristus Raja - Katedral / Bonipoi">
                Selamat Datang di Website Resmi Kristus Raja - Katedral / Bonipoi
            </span>
        </h1>
        <p>Membangun persekutuan umat yang beriman, melayani, dan bertumbuh dalam kasih.</p>
    </div>

    {{-- Quote Marquee --}}
    <div class="hero-quote-ticker" aria-label="Quote video beranda">
        <div class="hero-quote-badge">
            <i class="fas fa-quote-left mr-1"></i>
            <span>Quote :</span>
        </div>
        <div class="hero-quote-marquee">
            <div class="hero-quote-track">
                <span class="hero-quote-item">
                    <span>Ad maiorem Dei gloriam : Demi kemuliaan Allah yang lebih besar - Santo Ignatius dari Loyola</span>
                </span>
                <span class="hero-quote-item">
                    <span>&bull; Totus Tuus ego sum, et omnia mea tua sunt : Aku milik-Mu sepenuhnya, dan segala milikku adalah milik-Mu - Santo Louis-Marie de Montfort</span>
                </span>
                <span class="hero-quote-item">
                    <span>&bull; Ora et labora : Berdoa dan bekerja - Tradisi monastik Santo Benediktus.</span>
                </span>
                <span class="hero-quote-item">
                    <span>&bull; Fides quaerens intellectum : Iman yang mencari pengertian - Santo Anselmus dari Canterbury.</span>
                </span>
                <span class="hero-quote-item">
                    <span>&bull; Tarde te amavi, pulchritudo tam antiqua et tam nova : Terlambat aku mencintai-Mu, ya Keindahan yang begitu purba namun selalu baru - Santo Agustinus</span>
                </span>
            </div>
        </div>
    </div>
</section>

<!-- ===== COUNTER ===== -->
<section class="counter-section" aria-label="Statistik paroki">
    <div class="counter-grid">
        <div class="counter-card">
            <div class="cc-icon"><i class="fas fa-user-graduate" aria-hidden="true"></i></div>
            <div class="cc-content">
                <h3><span>{{ $stats['total_kk'] ?? 0 }}</span>+</h3>
                <p>KK Katolik</p>
            </div>
        </div>
        <div class="counter-card">
            <div class="cc-icon"><i class="fas fa-chalkboard-teacher" aria-hidden="true"></i></div>
            <div class="cc-content">
                <h3><span>{{ $stats['total_umat'] ?? 0 }}</span>+</h3>
                <p>Umat</p>
            </div>
        </div>
        <div class="counter-card">
            <div class="cc-icon"><i class="fas fa-church" aria-hidden="true"></i></div>
            <div class="cc-content">
                <h3><span>{{ $stats['total_kapela'] ?? 0 }}</span>+</h3>
                <p>Kapela</p>
            </div>
        </div>
        <div class="counter-card">
            <div class="cc-icon"><i class="fas fa-users" aria-hidden="true"></i></div>
            <div class="cc-content">
                <h3><span>{{ $stats['total_kub'] ?? 0 }}</span>+</h3>
                <p>KUB</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== SAMBUTAN PASTOR PAROKI SECTION ===== -->
<section id="sambutan-pastor-ringkas" class="sambutan-pastor-section" aria-label="Sambutan Pastor Paroki">
    <div class="sambutan-pastor-card">
        <div class="sambutan-pastor-photo">
            <span class="sambutan-pastor-badge">Kata Sambutan</span>
            <i class="fa-solid fa-user-tie"></i>
        </div>
        <div class="sambutan-pastor-body">
            <h3>Pastor Paroki</h3>
            <span class="sambutan-pastor-role">Pastor Paroki Kristus Raja - Katedral / Bonipoi</span>
            <p>Salve, Salam Sehat dan Berkah Dalem. Selamat Datang di Website Resmi Kristus Raja - Katedral / Bonipoi.</p>
            <div>
                <a href="/sambutan" wire:navigate class="inline-flex items-center gap-2 bg-sky-600 hover:bg-sky-700 text-white font-semibold px-6 py-2.5 rounded-full text-sm transition">
                    Baca Selengkapnya <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ===== PELAYAN PASTORAL SAAT INI SECTION ===== -->
<section id="pelayan-pastoral" class="py-16 bg-white dark:bg-[#07111f]">
    <div class="max-w-6xl mx-auto px-4">
        <div class="section-title">
            <span class="st-badge"><i class="fas fa-users me-1"></i> Pelayan Pastoral</span>
            <h2>Yang Bertugas <span class="text-gradient">Saat Ini</span></h2>
            <p>Pastor Paroki, Pastor Rekan, dan Frater yang sedang melayani umat di Kristus Raja - Katedral / Bonipoi.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 max-w-4xl mx-auto">
            <div class="bg-slate-50 dark:bg-[#101d31] border border-slate-200 dark:border-[#263a55] rounded-2xl p-6 text-center shadow-sm">
                <div class="w-20 h-20 bg-sky-100 dark:bg-sky-950 text-sky-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold">
                    <i class="fa-solid fa-church"></i>
                </div>
                <h4 class="font-bold text-slate-900 dark:text-white text-base">Pastor Paroki</h4>
                <p class="text-xs text-sky-600 font-semibold mt-1">Ketua Dewan Pastoral</p>
                <p class="text-xs text-slate-500 mt-2">Melayani Perayaan Ekaristi & Penggembalaan</p>
            </div>

            <div class="bg-slate-50 dark:bg-[#101d31] border border-slate-200 dark:border-[#263a55] rounded-2xl p-6 text-center shadow-sm">
                <div class="w-20 h-20 bg-purple-100 dark:bg-purple-950 text-purple-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold">
                    <i class="fa-solid fa-hands-praying"></i>
                </div>
                <h4 class="font-bold text-slate-900 dark:text-white text-base">Pastor Rekan</h4>
                <p class="text-xs text-purple-600 font-semibold mt-1">Vikaris Paroki</p>
                <p class="text-xs text-slate-500 mt-2">Pelayanan Sakramen & Pastoral KUB</p>
            </div>

            <div class="bg-slate-50 dark:bg-[#101d31] border border-slate-200 dark:border-[#263a55] rounded-2xl p-6 text-center shadow-sm">
                <div class="w-20 h-20 bg-emerald-100 dark:bg-emerald-950 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-4 text-2xl font-bold">
                    <i class="fa-solid fa-book-bible"></i>
                </div>
                <h4 class="font-bold text-slate-900 dark:text-white text-base">Frater / Katekis</h4>
                <p class="text-xs text-emerald-600 font-semibold mt-1">Pendamping Pastoral</p>
                <p class="text-xs text-slate-500 mt-2">Katekese, OMK & Misdinar</p>
            </div>
        </div>

        <div class="text-center mt-8">
            <a href="/pelayan-pastoral" wire:navigate class="inline-flex items-center gap-2 border border-sky-600 text-sky-600 hover:bg-sky-50 px-5 py-2 rounded-full text-sm font-semibold transition">
                <span>Lihat Halaman Pelayan Pastoral</span> <i class="fas fa-arrow-right text-xs"></i>
            </a>
        </div>
    </div>
</section>

<!-- ===== PETA WILAYAH & KAPELA (WEBGIS INTERAKTIF) SECTION ===== -->
<section id="peta-wilayah-kapela" class="py-16 bg-slate-100/70 dark:bg-[#090e1a] border-t border-slate-200 dark:border-slate-800">
    <div class="max-w-6xl mx-auto px-4">
        <div class="section-title">
            <span class="st-badge"><i class="fas fa-map-marked-alt me-1"></i> Teritorial Pastoral</span>
            <h2>Peta Wilayah <span class="text-gradient">Stasi &amp; Kapela</span></h2>
            <p>Persebaran lokasi Gereja Pusat, Stasi, dan Kapela di wilayah teritorial paroki.</p>
        </div>

        <div class="bg-white dark:bg-[#101d31] border border-slate-200 dark:border-[#263a55] rounded-3xl overflow-hidden shadow-xl">
            <div id="home-map-kapela" style="height: 480px; width: 100%;"></div>
        </div>

        <div class="text-center mt-6">
            <a href="/peta-kapela" wire:navigate class="inline-flex items-center gap-2 bg-sky-600 hover:bg-sky-700 text-white font-semibold px-6 py-2.5 rounded-full text-sm transition shadow-md">
                <i class="fas fa-expand-arrows-alt text-xs"></i> <span>Buka Peta Layar Penuh</span>
            </a>
        </div>
    </div>
</section>

<!-- Section Jadwal Misa Paroki -->
<section id="jadwal-misa" class="py-16 bg-white dark:bg-[#07111f]">
    <div class="max-w-6xl mx-auto px-4">
        <div class="section-title">
            <span class="st-badge">Jadwal Misa</span>
            <h2>Perayaan <span class="text-gradient">Ekaristi</span></h2>
            <p>Jadwal Misa Harian dan Minggu Kristus Raja - Katedral / Bonipoi.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse($jadwalMisa ?? [] as $misa)
            <div class="bg-slate-50 dark:bg-[#101d31] border border-slate-200 dark:border-[#263a55] rounded-2xl p-6 shadow-sm">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-12 h-12 bg-sky-100 text-sky-700 rounded-xl flex items-center justify-center font-bold text-lg">
                        {{ \Carbon\Carbon::parse($misa->tanggal)->format('d') }}
                    </div>
                    <div>
                        <p class="font-bold text-slate-900 dark:text-white">{{ $misa->jenis_perayaan ?? 'Misa Kudus' }}</p>
                        <p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($misa->tanggal)->translatedFormat('l, d F Y') }}</p>
                    </div>
                </div>
                <p class="text-sm font-semibold text-sky-600"><i class="fa-regular fa-clock mr-1.5"></i> {{ $misa->jam_perayaan ?? $misa->waktu ?? '08.00 WITA' }}</p>
                <p class="text-xs text-slate-500 mt-1"><i class="fa-solid fa-location-dot mr-1.5"></i> {{ $misa->tempat ?? 'Gereja Paroki' }}</p>
            </div>
            @empty
            <div class="bg-slate-50 dark:bg-[#101d31] border border-slate-200 dark:border-[#263a55] rounded-2xl p-6 shadow-sm">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-12 h-12 bg-sky-100 text-sky-700 rounded-xl flex items-center justify-center font-bold text-lg">M1</div>
                    <div>
                        <p class="font-bold text-slate-900 dark:text-white">Misa Minggu I (Pagi)</p>
                        <p class="text-xs text-slate-500">Gereja Pusat Paroki</p>
                    </div>
                </div>
                <p class="text-sm font-semibold text-sky-600"><i class="fa-regular fa-clock mr-1.5"></i> 06.00 WITA</p>
            </div>
            <div class="bg-slate-50 dark:bg-[#101d31] border border-slate-200 dark:border-[#263a55] rounded-2xl p-6 shadow-sm">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-12 h-12 bg-sky-100 text-sky-700 rounded-xl flex items-center justify-center font-bold text-lg">M2</div>
                    <div>
                        <p class="font-bold text-slate-900 dark:text-white">Misa Minggu II (Pagi)</p>
                        <p class="text-xs text-slate-500">Gereja Pusat Paroki</p>
                    </div>
                </div>
                <p class="text-sm font-semibold text-sky-600"><i class="fa-regular fa-clock mr-1.5"></i> 08.00 WITA</p>
            </div>
            <div class="bg-slate-50 dark:bg-[#101d31] border border-slate-200 dark:border-[#263a55] rounded-2xl p-6 shadow-sm">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-12 h-12 bg-sky-100 text-sky-700 rounded-xl flex items-center justify-center font-bold text-lg">M3</div>
                    <div>
                        <p class="font-bold text-slate-900 dark:text-white">Misa Minggu III (Sore)</p>
                        <p class="text-xs text-slate-500">Gereja Pusat Paroki</p>
                    </div>
                </div>
                <p class="text-sm font-semibold text-sky-600"><i class="fa-regular fa-clock mr-1.5"></i> 17.00 WITA</p>
            </div>
            @endforelse
        </div>

        <div class="text-center mt-8">
            <a href="/jadwal-misa" wire:navigate class="inline-flex items-center gap-2 border border-slate-300 dark:border-slate-700 hover:border-sky-600 text-slate-700 dark:text-slate-200 px-6 py-2.5 rounded-full text-sm font-semibold transition">
                Lihat Semua Jadwal Misa <i class="fas fa-arrow-right text-xs"></i>
            </a>
        </div>
    </div>
</section>

<!-- Section Berita & Artikel Terbaru -->
<section id="artikel" class="py-16 bg-slate-50 dark:bg-[#090e1a]">
    <div class="max-w-6xl mx-auto px-4">
        <div class="section-title">
            <span class="st-badge">Berita &amp; Artikel</span>
            <h2>Berita &amp; <span class="text-gradient">Artikel Terkini</span></h2>
            <p>Informasi terbaru, warta paroki, dan artikel rohani seputar pelayanan serta kegiatan umat.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse($artikel ?? [] as $item)
            <div class="bg-white dark:bg-[#101d31] border border-slate-200 dark:border-[#263a55] rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition">
                <div class="p-6">
                    <span class="text-xs font-bold text-sky-600 bg-sky-50 dark:bg-sky-950 px-2.5 py-1 rounded-full">{{ $item->kategori ?? 'WARTA' }}</span>
                    <h3 class="font-bold text-lg text-slate-900 dark:text-white mt-3 line-clamp-2">{{ $item->judul }}</h3>
                    <p class="text-xs text-slate-500 mt-2 line-clamp-3">{{ $item->ringkasan ?? Str::limit(strip_tags($item->konten), 120) }}</p>
                    <a href="/artikel/{{ $item->slug }}" wire:navigate class="inline-block mt-4 text-xs font-bold text-sky-600 hover:text-sky-700">Baca Selengkapnya &rarr;</a>
                </div>
            </div>
            @empty
            <div class="col-span-3 text-center py-8 text-slate-400">
                <p>Artikel belum tersedia.</p>
            </div>
            @endforelse
        </div>

        <div class="text-center mt-8">
            <a href="/berita" wire:navigate class="inline-flex items-center gap-2 border border-slate-300 dark:border-slate-700 hover:border-sky-600 text-slate-700 dark:text-slate-200 px-6 py-2.5 rounded-full text-sm font-semibold transition">
                Lihat Semua Berita &amp; Artikel <i class="fas fa-arrow-right text-xs"></i>
            </a>
        </div>
    </div>
</section>

<!-- Section Galeri Dokumentasi -->
<section id="gallery" class="py-16 bg-white dark:bg-[#07111f]">
    <div class="max-w-6xl mx-auto px-4">
        <div class="section-title">
            <span class="st-badge">Galeri Foto</span>
            <h2>Galeri &amp; <span class="text-gradient">Dokumentasi</span></h2>
            <p>Foto perayaan liturgi, penerimaan sakramen, dan momen kegiatan umat paroki.</p>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @forelse($galeri ?? [] as $item)
            <div class="rounded-2xl overflow-hidden aspect-square bg-slate-200 dark:bg-slate-800 shadow-sm hover:shadow-md transition">
                @if($item->gambar)
                    <img src="{{ asset('storage/' . $item->gambar) }}" alt="{{ $item->judul }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-slate-400"><i class="fa-regular fa-image text-2xl"></i></div>
                @endif
            </div>
            @empty
            <div class="col-span-4 text-center py-8 text-slate-400">
                <p>Dokumentasi galeri belum tersedia.</p>
            </div>
            @endforelse
        </div>

        <div class="text-center mt-8">
            <a href="/galeri" wire:navigate class="inline-flex items-center gap-2 border border-slate-300 dark:border-slate-700 hover:border-sky-600 text-slate-700 dark:text-slate-200 px-6 py-2.5 rounded-full text-sm font-semibold transition">
                Lihat Semua Galeri <i class="fas fa-arrow-right text-xs"></i>
            </a>
        </div>
    </div>
</section>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Typing Effect
    var typingText = document.querySelector('.hero-typing-text');
    if (typingText) {
        var fullText = typingText.getAttribute('data-typing-text') || typingText.textContent || '';
        var index = 0;
        typingText.textContent = '';
        function typeNextChar() {
            typingText.textContent = fullText.slice(0, index);
            index += 1;
            if (index <= fullText.length) {
                setTimeout(typeNextChar, 45);
            }
        }
        setTimeout(typeNextChar, 500);
    }

    // Initialize WebGIS Map
    if (document.getElementById('home-map-kapela')) {
        var homeMap = L.map('home-map-kapela', {
            center: [-10.1626, 123.5796],
            zoom: 14,
            scrollWheelZoom: false
        });

        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
            attribution: '&copy; CartoDB &copy; OpenStreetMap',
            maxZoom: 19
        }).addTo(homeMap);

        // Fetch GeoJSON from Laravel API
        fetch('/api/kapela-geojson')
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data && data.features && data.features.length > 0) {
                    var geoLayer = L.geoJSON(data, {
                        onEachFeature: function(feature, layer) {
                            var p = feature.properties;
                            layer.bindPopup('<strong>' + p.nama_stasi_kapela + '</strong><br>' + p.alamat);
                        }
                    }).addTo(homeMap);
                    var bounds = geoLayer.getBounds();
                    if (bounds.isValid()) homeMap.fitBounds(bounds, { padding: [30, 30] });
                } else {
                    L.marker([-10.1626, 123.5796]).addTo(homeMap)
                        .bindPopup('<strong>Gereja Katedral Kristus Raja</strong><br>Fontein, Kota Kupang')
                        .openPopup();
                }
            })
            .catch(function(err) {
                L.marker([-10.1626, 123.5796]).addTo(homeMap)
                    .bindPopup('<strong>Gereja Katedral Kristus Raja</strong><br>Fontein, Kota Kupang');
            });
    }
});
</script>
@endsection
