@extends('layouts.app')

@section('title', ($globalNamaParoki ?? 'Paroki') . ' - Sistem Informasi Paroki')
@section('description', 'Website resmi ' . ($globalNamaParoki ?? 'Paroki') . ' untuk informasi jadwal perayaan Ekaristi, sakramen, warta paroki, dan sensus umat Katolik.')

@push('styles')
    <link rel="stylesheet" href="/css/pages/beranda.css?v={{ @filemtime(public_path('css/pages/beranda.css')) ?: time() }}">
@endpush

@section('content')

<!-- ===== HERO ===== -->
<section class="hero" id="main-content" aria-label="Beranda {{ $globalNamaParoki ?? 'SIPAROKI' }}">
    @php
        $heroVideoType = $pengaturan->hero_video_type ?? 'file';
        $heroVideoFile = $pengaturan->hero_video_file ?? null;
        $heroVideoYoutube = $pengaturan->hero_video_youtube ?? null;
        $heroVideoPoster = $pengaturan->hero_video_poster ?? null;
    @endphp

    <div class="hero-video-bg">
        @if(($heroVideoType === 'youtube' || $heroVideoType === 'url') && !empty($heroVideoYoutube))
            @php
                $youtubeId = null;
                if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([A-Za-z0-9_-]+)/', $heroVideoYoutube, $matches)) {
                    $youtubeId = $matches[1];
                }
            @endphp
            @if($youtubeId)
                <iframe
                    class="hero-video-element"
                    src="https://www.youtube.com/embed/{{ $youtubeId }}?autoplay=1&mute=1&loop=1&playlist={{ $youtubeId }}&controls=0&showinfo=0&rel=0&modestbranding=1"
                    title="Video profil {{ $globalNamaParoki ?? 'SIPAROKI' }}"
                    loading="lazy"
                    allow="autoplay; encrypted-media; picture-in-picture"
                    allowfullscreen
                ></iframe>
            @endif
        @elseif(!empty($heroVideoFile))
            @php
                $cleanVideoPath = ltrim(str_replace(['assets/uploads/', 'uploads/'], '', $heroVideoFile), '/');
                $posterUrl = '';
                if (!empty($heroVideoPoster)) {
                    if (str_starts_with($heroVideoPoster, 'http')) {
                        $posterUrl = '';
                    } else {
                        $basePoster = basename($heroVideoPoster);
                        if (file_exists(public_path('assets/uploads/video/' . $basePoster))) {
                            $posterUrl = asset('assets/uploads/video/' . $basePoster);
                        } elseif (file_exists(public_path('assets/uploads/profil/' . $basePoster))) {
                            $posterUrl = asset('assets/uploads/profil/' . $basePoster);
                        } else {
                            $posterUrl = asset('assets/uploads/' . $basePoster);
                        }
                    }
                }
            @endphp
            <video class="hero-video-element" autoplay loop muted playsinline poster="{{ $posterUrl }}">
                <source src="{{ asset('assets/uploads/video/' . basename($cleanVideoPath)) }}" type="video/mp4">
                <source src="{{ asset('assets/uploads/' . $cleanVideoPath) }}" type="video/mp4">
                <source src="{{ asset('uploads/' . $cleanVideoPath) }}" type="video/mp4">
                <source src="{{ asset($heroVideoFile) }}" type="video/mp4">
            </video>
        @else
            <video class="hero-video-element" autoplay loop muted playsinline poster="">
                <source src="{{ asset('assets/uploads/video/katedral_bg.mp4') }}" type="video/mp4">
                <source src="{{ asset('assets/uploads/hero_video.mp4') }}" type="video/mp4">
                <source src="{{ asset('uploads/hero_video.mp4') }}" type="video/mp4">
            </video>
        @endif
        <div class="hero-video-overlay"></div>
    </div>

    <div class="hero-video-text">
        <h1>
            <span class="hero-typing-text" data-typing-text="Selamat Datang di Website Resmi {{ $globalNamaParoki ?? 'Paroki' }}">
                Selamat Datang di Website Resmi {{ $globalNamaParoki ?? 'Paroki' }}
            </span>
        </h1>
        <p class="hero-subtitle-readable">Membangun persekutuan umat yang beriman, melayani, dan bertumbuh dalam kasih.</p>
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
            <h3>{{ $pastor_paroki ?? 'Pastor Paroki' }}</h3>
            <span class="sambutan-pastor-role">Pastor Paroki {{ $globalNamaParoki ?? 'SIPAROKI' }}</span>
            <p>Salve, Salam Sehat dan Berkah Dalem. Selamat Datang di Website Resmi {{ $globalNamaParoki ?? 'St. Vinsensius a Paulo - Benlutu' }}.</p>
            <div>
                <a href="/sambutan" class="inline-flex items-center gap-2 bg-sky-600 hover:bg-sky-700 text-white font-semibold px-6 py-2.5 rounded-full text-sm transition">
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
            <p>Pastor Paroki, Pastor Rekan, dan Frater yang sedang melayani umat di {{ $globalNamaParoki ?? 'St. Vinsensius a Paulo - Benlutu' }}.</p>
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
            <a href="/pelayan-pastoral" class="inline-flex items-center gap-2 border border-sky-600 text-sky-600 hover:bg-sky-50 px-5 py-2 rounded-full text-sm font-semibold transition">
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
            <div id="home-map-kapela" class="online-map" style="height: 480px; width: 100%;"></div>
        </div>

        <div class="text-center mt-6">
            <a href="/peta-kapela" class="inline-flex items-center gap-2 bg-sky-600 hover:bg-sky-700 text-white font-semibold px-6 py-2.5 rounded-full text-sm transition shadow-md">
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
            <p>Jadwal Misa Harian dan Minggu {{ $globalNamaParoki ?? 'St. Vinsensius a Paulo - Benlutu' }}.</p>
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
            <a href="/jadwal-misa" class="inline-flex items-center gap-2 border border-slate-300 dark:border-slate-700 hover:border-sky-600 text-slate-700 dark:text-slate-200 px-6 py-2.5 rounded-full text-sm font-semibold transition">
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
                    <a href="/artikel/{{ $item->slug }}" class="inline-block mt-4 text-xs font-bold text-sky-600 hover:text-sky-700">Baca Selengkapnya &rarr;</a>
                </div>
            </div>
            @empty
            <div class="col-span-3 text-center py-8 text-slate-400">
                <p>Artikel belum tersedia.</p>
            </div>
            @endforelse
        </div>

        <div class="text-center mt-8">
            <a href="/berita" class="inline-flex items-center gap-2 border border-slate-300 dark:border-slate-700 hover:border-sky-600 text-slate-700 dark:text-slate-200 px-6 py-2.5 rounded-full text-sm font-semibold transition">
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
            <a href="/galeri" class="inline-flex items-center gap-2 border border-slate-300 dark:border-slate-700 hover:border-sky-600 text-slate-700 dark:text-slate-200 px-6 py-2.5 rounded-full text-sm font-semibold transition">
                Lihat Semua Galeri <i class="fas fa-arrow-right text-xs"></i>
            </a>
        </div>
    </div>
</section>

@push('scripts')
    <script src="/js/pages/beranda.js?v={{ @filemtime(public_path('js/pages/beranda.js')) ?: time() }}" defer></script>
@endpush
@endsection




