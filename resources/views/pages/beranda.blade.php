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
        $heroVideoType = $pengaturan->video_header_type ?? $pengaturan->hero_video_type ?? 'youtube';
        $heroVideoFile = $pengaturan->video_header_file ?? $pengaturan->hero_video_file ?? null;
        $heroVideoYoutube = $pengaturan->video_header_url ?? $pengaturan->hero_video_youtube ?? null;
        $heroVideoPoster = $pengaturan->video_header_poster ?? $pengaturan->hero_video_poster ?? null;
        $videoStatus = $pengaturan->video_header_status ?? 'Aktif';
        $videoTitle = !empty($pengaturan->video_header_title) ? $pengaturan->video_header_title : ('Selamat Datang di Website Resmi ' . ($globalNamaParoki ?? 'Paroki'));
        $videoSubtitle = !empty($pengaturan->video_header_subtitle) ? $pengaturan->video_header_subtitle : 'Membangun persekutuan umat yang beriman, melayani, dan bertumbuh dalam kasih.';
        $videoBtnText = $pengaturan->video_header_btn_text ?? 'Lihat Jadwal Misa';
        $videoBtnLink = $pengaturan->video_header_btn_link ?? '/jadwal-misa';
        $overlayOpacity = (int) ($pengaturan->video_header_overlay_opacity ?? 50) / 100;
    @endphp

    <div class="hero-video-bg">
        @if($videoStatus === 'Aktif' && ($heroVideoType === 'youtube' || $heroVideoType === 'url') && !empty($heroVideoYoutube))
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
        @elseif($videoStatus === 'Aktif' && !empty($heroVideoFile))
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
        <div class="hero-video-overlay" style="opacity: {{ $overlayOpacity }};"></div>
    </div>

    <div class="hero-video-text">
        <h1>
            <span class="hero-typing-text" data-typing-text="{{ $videoTitle }}">
                {{ $videoTitle }}
            </span>
        </h1>
        <p class="hero-subtitle-readable">{{ $videoSubtitle }}</p>
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
            <div class="cc-icon"><i class="fas fa-graduation-cap" aria-hidden="true"></i></div>
            <div class="cc-content">
                <h3><span>{{ $stats['total_kk'] ?? 0 }}</span>+</h3>
                <p>KK Katolik</p>
            </div>
        </div>
        <div class="counter-card">
            <div class="cc-icon"><i class="fas fa-id-card" aria-hidden="true"></i></div>
            <div class="cc-content">
                <h3><span>{{ $stats['total_umat'] ?? 0 }}</span>+</h3>
                <p>Umat</p>
            </div>
        </div>
        <div class="counter-card">
            <div class="cc-icon"><i class="fas fa-trophy" aria-hidden="true"></i></div>
            <div class="cc-content">
                <h3><span>{{ $stats['total_kapela'] ?? 0 }}</span>+</h3>
                <p>Kapela</p>
            </div>
        </div>
        <div class="counter-card">
            <div class="cc-icon"><i class="fas fa-book-bookmark" aria-hidden="true"></i></div>
            <div class="cc-content">
                <h3><span>{{ $stats['total_kub'] ?? 0 }}</span>+</h3>
                <p>KUB</p>
            </div>
        </div>
    </div>
</section>

<!-- ===== SAMBUTAN PASTOR PAROKI SECTION ===== -->
@php
    $imamImage = file_exists(public_path('assets/frontend/siparoki/images/default-pastor.jpg'))
        ? asset('assets/frontend/siparoki/images/default-pastor.jpg')
        : (file_exists(public_path('assets/frontend/siparoki/images/default-principal.jpg'))
            ? asset('assets/frontend/siparoki/images/default-principal.jpg')
            : asset('images/pastor-avatar.svg'));
@endphp
<section id="sambutan-pastor-ringkas" class="sambutan-pastor-section" aria-label="Sambutan Pastor Paroki">
    <div class="sambutan-pastor-card">
        <div class="sambutan-pastor-photo">
            <span class="sambutan-pastor-badge">KATA SAMBUTAN</span>
            <img src="{{ !empty($pastor_foto) ? $pastor_foto : $imamImage }}" alt="Pastor Paroki {{ $globalNamaParoki ?? 'SIPAROKI' }}">
        </div>
        <div class="sambutan-pastor-body">
            <h3>{{ $pastor_paroki ?? 'RD. Herman Hilers Penga' }}</h3>
            <span class="sambutan-pastor-role">Pastor Paroki {{ $globalNamaParoki ?? 'SIPAROKI' }}</span>
            <p>Salve, Salam Sehat dan Berkah Dalem. Selamat Datang di Website Resmi {{ $globalNamaParoki ?? 'SIPAROKI' }}.</p>
            <div>
                <a href="/sambutan" class="btn-sambutan">
                    Baca Selengkapnya <i class="fas fa-arrow-right text-xs"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- ===== PELAYAN PASTORAL SAAT INI SECTION (KONOHA THEME) ===== -->
<section id="pelayan-pastoral" class="pelayan-pastoral-section" aria-label="Pelayan Pastoral Paroki">
    <div class="pelayan-pastoral-container">
        <div class="pelayan-pastoral-header">
            <span class="pelayan-pastoral-badge">
                <i class="fas fa-users-rays me-1"></i> Pelayan Pastoral
            </span>
            <h2>Yang Bertugas <span>Saat Ini</span></h2>
            <p>Pastor Paroki, Pastor Rekan, dan Frater yang sedang melayani umat di {{ $globalNamaParoki ?? 'St. Vinsensius a Paulo - Benlutu' }}.</p>
        </div>

        <div class="pelayan-pastoral-grid">
            
            {{-- Card 1: Pastor Paroki --}}
            <div class="pelayan-card pastor-paroki">
                <div>
                    <div class="pelayan-avatar-wrap">
                        <img src="{{ !empty($pastor_foto) ? $pastor_foto : $imamImage }}" 
                             alt="Pastor Paroki" 
                             class="pelayan-avatar-img">
                        <span class="pelayan-tag">
                            <i class="fa-solid fa-cross text-[9px] me-0.5"></i> Paroki
                        </span>
                    </div>

                    <span class="pelayan-role-badge">Pastor Paroki</span>
                    <h4>{{ $pastor_paroki ?? 'RD. Herman Hilers Penga' }}</h4>
                    <div class="pelayan-subrole">{{ $pastor_paroki_obj->catatan_pelayanan ?? $pastor_paroki_obj->jabatan ?? 'Ketua Dewan Pastoral Paroki' }}</div>

                    <div class="pelayan-divider"></div>

                    <ul class="pelayan-duties">
                        <li>
                            <i class="fa-solid fa-check-circle"></i>
                            <span>Penggembalaan &amp; Reksa Pastoral Paroki</span>
                        </li>
                        <li>
                            <i class="fa-solid fa-check-circle"></i>
                            <span>Melayani Perayaan Ekaristi &amp; Sakramen</span>
                        </li>
                    </ul>
                </div>

                <div class="pelayan-card-footer">
                    <span class="pelayan-status-label">Status</span>
                    <span class="pelayan-status-val">
                        <span class="pelayan-status-dot"></span> {{ $pastor_paroki_obj->status ?? 'Aktif Bertugas' }}
                    </span>
                </div>
            </div>

            {{-- Card 2: Pastor Rekan --}}
            <div class="pelayan-card pastor-rekan">
                <div>
                    <div class="pelayan-avatar-wrap">
                        @if(!empty($pastor_rekan_obj?->foto))
                            <img src="{{ asset($pastor_rekan_obj->foto) }}" alt="Pastor Rekan" class="pelayan-avatar-img">
                        @else
                            <div class="pelayan-avatar-icon rekan">
                                <i class="fa-solid fa-hands-praying"></i>
                            </div>
                        @endif
                        <span class="pelayan-tag vikaris">
                            <i class="fa-solid fa-church text-[9px] me-0.5"></i> Vikaris
                        </span>
                    </div>

                    <span class="pelayan-role-badge rekan">Pastor Rekan</span>
                    <h4>{{ $pastor_rekan ?? 'Pastor Rekan Paroki' }}</h4>
                    <div class="pelayan-subrole">{{ $pastor_rekan_obj->jabatan ?? 'Vikaris Paroki' }}</div>

                    <div class="pelayan-divider"></div>

                    <ul class="pelayan-duties">
                        <li>
                            <i class="fa-solid fa-check-circle"></i>
                            <span>Pelayanan Sakramen &amp; Pastoral KUB</span>
                        </li>
                        <li>
                            <i class="fa-solid fa-check-circle"></i>
                            <span>Kunjungan Pastoral Stasi &amp; Lingkungan</span>
                        </li>
                    </ul>
                </div>

                <div class="pelayan-card-footer">
                    <span class="pelayan-status-label">Status</span>
                    <span class="pelayan-status-val">
                        <span class="pelayan-status-dot"></span> {{ $pastor_rekan_obj->status ?? 'Aktif Bertugas' }}
                    </span>
                </div>
            </div>

            {{-- Card 3: Frater / Katekis --}}
            <div class="pelayan-card frater-katekis">
                <div>
                    <div class="pelayan-avatar-wrap">
                        @if(!empty($frater_obj?->foto))
                            <img src="{{ asset($frater_obj->foto) }}" alt="Frater" class="pelayan-avatar-img">
                        @else
                            <div class="pelayan-avatar-icon frater">
                                <i class="fa-solid fa-book-bible"></i>
                            </div>
                        @endif
                        <span class="pelayan-tag pastoral">
                            <i class="fa-solid fa-book-open text-[9px] me-0.5"></i> Pastoral
                        </span>
                    </div>

                    <span class="pelayan-role-badge frater">Frater / Katekis</span>
                    <h4>{{ $frater ?? 'Frater Pastoral / Katekis' }}</h4>
                    <div class="pelayan-subrole">{{ $frater_obj->jabatan ?? 'Pendamping Pastoral' }}</div>

                    <div class="pelayan-divider"></div>

                    <ul class="pelayan-duties">
                        <li>
                            <i class="fa-solid fa-check-circle"></i>
                            <span>Katekese Sakramen &amp; Bina Iman Remaja</span>
                        </li>
                        <li>
                            <i class="fa-solid fa-check-circle"></i>
                            <span>Pendampingan OMK &amp; Putera-Puteri Altar</span>
                        </li>
                    </ul>
                </div>

                <div class="pelayan-card-footer">
                    <span class="pelayan-status-label">Status</span>
                    <span class="pelayan-status-val">
                        <span class="pelayan-status-dot"></span> {{ $frater_obj->status ?? 'Aktif Bertugas' }}
                    </span>
                </div>
            </div>

        </div>

        <div class="pelayan-btn-group">
            <a href="/pelayan-pastoral" class="btn-konoha-teal">
                <i class="fa-solid fa-users text-xs"></i>
                <span>Lihat Halaman Pelayan Pastoral</span>
                <i class="fas fa-arrow-right text-xs"></i>
            </a>
            <a href="/riwayat-pastor" class="btn-konoha-amber-outline">
                <i class="fa-solid fa-clock-rotate-left text-xs"></i>
                <span>Riwayat Pastor Paroki</span>
            </a>
        </div>
    </div>
</section>

<!-- ===== PETA WILAYAH & KAPELA (WEBGIS INTERAKTIF) SECTION ===== -->
<section id="peta-wilayah-kapela" class="py-16 bg-slate-100/70 dark:bg-[#090e1a] border-t border-b border-slate-200 dark:border-slate-800 relative">
    <div class="max-w-6xl mx-auto px-4 mb-8">
        <div class="section-title text-center mb-0">
            <span class="badge mb-2 px-3 py-2" style="background: rgba(0, 137, 123, 0.1); color: var(--primary-teal, #00897b); font-weight: 700; border-radius: 20px; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">
                <i class="fas fa-map-marked-alt me-1"></i> Teritorial Pastoral
            </span>
            <h2 style="font-size: 2.2rem; font-weight: 800; color: #1e293b;" class="dark:text-white">
                Peta Wilayah <span style="color: var(--primary-orange, #ff9800);">Stasi &amp; Kapela</span>
            </h2>
            <p style="color: #64748b; font-size: 0.95rem; max-width: 680px; margin: 8px auto 0;">
                Persebaran lokasi Gereja Pusat, Stasi, dan Kapela di wilayah teritorial paroki.
            </p>
        </div>
    </div>

    {{-- Full Width Map (Edge to Edge) --}}
    <div class="w-full relative shadow-inner border-y border-slate-300/80 dark:border-[#263a55]">
        <div id="home-map-kapela" class="online-map" style="height: 520px; width: 100%;"></div>

        <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-[400]">
            <a href="/peta-kapela" class="inline-flex items-center gap-2 bg-sky-600/95 hover:bg-sky-600 backdrop-blur-md text-white font-semibold px-6 py-3 rounded-full text-sm transition shadow-xl hover:shadow-sky-600/30 border border-white/20">
                <i class="fas fa-expand-arrows-alt text-xs"></i> <span>Buka Peta Layar Penuh</span>
            </a>
        </div>
    </div>
</section>

<!-- Section Jadwal Misa Paroki -->
<section id="jadwal-misa" class="py-16 bg-white dark:bg-[#07111f]">
    <div class="container">
        <div class="section-title text-center mb-5">
            <span class="badge mb-2 px-3 py-2" style="background: rgba(255, 152, 0, 0.12); color: var(--primary-orange, #ff9800); font-weight: 700; border-radius: 20px; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">
                <i class="fas fa-calendar-alt me-1"></i> Jadwal Misa
            </span>
            <h2 style="font-size: 2.2rem; font-weight: 800; color: #1e293b;" class="dark:text-white">
                Perayaan <span style="color: var(--primary-teal, #00897b);">Ekaristi</span>
            </h2>
            <p style="color: #64748b; font-size: 0.95rem; max-width: 680px; margin: 8px auto 0;">
                Jadwal Misa Harian dan Minggu {{ $globalNamaParoki ?? 'St. Vinsensius a Paulo - Benlutu' }}.
            </p>
        </div>

        <div class="row g-4 justify-content-center">
            @forelse($jadwalMisa ?? [] as $idx => $misa)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 transition-all hover:shadow-md hover:-translate-y-1" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center font-bold text-white shadow-sm" style="width: 52px; height: 52px; background: linear-gradient(135deg, #00897b, #004d40); font-size: 1.1rem; flex-shrink: 0;">
                            M{{ $idx + 1 }}
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold text-dark" style="font-size: 1.05rem;">{{ $misa->nama_misa ?? $misa->jenis_perayaan ?? 'Misa Kudus' }}</h5>
                            <span class="text-muted small"><i class="fa-solid fa-church text-amber-600 me-1"></i> {{ $misa->lokasi ?? $misa->tempat ?? 'Gereja Pusat Paroki' }}</span>
                        </div>
                    </div>
                    <div class="pt-2 border-top border-slate-200/80 mt-auto d-flex align-items-center justify-content-between">
                        <span class="fw-bold" style="color: var(--primary-teal, #00897b); font-size: 1rem;">
                            <i class="fa-regular fa-clock me-1 text-teal-600"></i> {{ $misa->jam ?? $misa->jam_perayaan ?? $misa->waktu ?? '08.00 WITA' }}
                        </span>
                        <span class="badge rounded-pill px-3 py-1.5" style="background: rgba(0, 137, 123, 0.1); color: var(--primary-teal, #00897b); font-size: 0.75rem;">
                            {{ $misa->hari ?? 'Minggu' }}
                        </span>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 transition-all hover:shadow-md hover:-translate-y-1" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center font-bold text-white shadow-sm" style="width: 52px; height: 52px; background: linear-gradient(135deg, #00897b, #004d40); font-size: 1.1rem; flex-shrink: 0;">
                            M1
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold text-dark" style="font-size: 1.05rem;">Misa Minggu I (Pagi)</h5>
                            <span class="text-muted small"><i class="fa-solid fa-church text-amber-600 me-1"></i> Gereja Pusat Paroki</span>
                        </div>
                    </div>
                    <div class="pt-2 border-top border-slate-200/80 mt-auto d-flex align-items-center justify-content-between">
                        <span class="fw-bold" style="color: var(--primary-teal, #00897b); font-size: 1rem;">
                            <i class="fa-regular fa-clock me-1 text-teal-600"></i> 06.00 WITA
                        </span>
                        <span class="badge rounded-pill px-3 py-1.5" style="background: rgba(0, 137, 123, 0.1); color: var(--primary-teal, #00897b); font-size: 0.75rem;">
                            Minggu
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 transition-all hover:shadow-md hover:-translate-y-1" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center font-bold text-white shadow-sm" style="width: 52px; height: 52px; background: linear-gradient(135deg, #00897b, #004d40); font-size: 1.1rem; flex-shrink: 0;">
                            M2
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold text-dark" style="font-size: 1.05rem;">Misa Minggu II (Pagi)</h5>
                            <span class="text-muted small"><i class="fa-solid fa-church text-amber-600 me-1"></i> Gereja Pusat Paroki</span>
                        </div>
                    </div>
                    <div class="pt-2 border-top border-slate-200/80 mt-auto d-flex align-items-center justify-content-between">
                        <span class="fw-bold" style="color: var(--primary-teal, #00897b); font-size: 1rem;">
                            <i class="fa-regular fa-clock me-1 text-teal-600"></i> 08.00 WITA
                        </span>
                        <span class="badge rounded-pill px-3 py-1.5" style="background: rgba(0, 137, 123, 0.1); color: var(--primary-teal, #00897b); font-size: 0.75rem;">
                            Minggu
                        </span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 transition-all hover:shadow-md hover:-translate-y-1" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-3 d-flex align-items-center justify-content-center font-bold text-white shadow-sm" style="width: 52px; height: 52px; background: linear-gradient(135deg, #00897b, #004d40); font-size: 1.1rem; flex-shrink: 0;">
                            M3
                        </div>
                        <div>
                            <h5 class="mb-0 fw-bold text-dark" style="font-size: 1.05rem;">Misa Minggu III (Sore)</h5>
                            <span class="text-muted small"><i class="fa-solid fa-church text-amber-600 me-1"></i> Gereja Pusat Paroki</span>
                        </div>
                    </div>
                    <div class="pt-2 border-top border-slate-200/80 mt-auto d-flex align-items-center justify-content-between">
                        <span class="fw-bold" style="color: var(--primary-teal, #00897b); font-size: 1rem;">
                            <i class="fa-regular fa-clock me-1 text-teal-600"></i> 17.00 WITA
                        </span>
                        <span class="badge rounded-pill px-3 py-1.5" style="background: rgba(0, 137, 123, 0.1); color: var(--primary-teal, #00897b); font-size: 0.75rem;">
                            Minggu
                        </span>
                    </div>
                </div>
            </div>
            @endforelse
        </div>

        <div class="text-center mt-5">
            <a href="/jadwal-misa" class="btn-konoha-teal px-5 py-2.5 d-inline-flex align-items-center gap-2" style="border-radius: 50px; font-weight: 700; text-decoration: none;">
                <span>Lihat Semua Jadwal Misa</span> <i class="fas fa-arrow-right text-xs"></i>
            </a>
        </div>
    </div>
</section>

<!-- Section Berita & Artikel Terbaru -->
<section id="artikel" class="py-16 bg-slate-50 dark:bg-[#090e1a]">
    <div class="container">
        <div class="section-title text-center mb-5">
            <span class="badge mb-2 px-3 py-2" style="background: rgba(0, 137, 123, 0.1); color: var(--primary-teal); font-weight: 700; border-radius: 20px; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">Berita &amp; Artikel</span>
            <h2 style="font-size: 2.2rem; font-weight: 800; color: #1e293b;">Berita &amp; <span style="color: var(--primary-orange);">Artikel Terkini</span></h2>
            <p style="color: #64748b; font-size: 0.95rem; max-width: 680px; margin: 8px auto 0;">Informasi terbaru, warta paroki, dan artikel rohani seputar pelayanan serta kegiatan umat.</p>
        </div>

        @php
            $newsImageUrl = function ($path) {
                if (empty($path)) {
                    return asset('images/news-placeholder.svg');
                }

                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
                    return $path;
                }

                $clean = ltrim($path, '/');
                $base = basename($clean);

                if (file_exists(public_path('assets/uploads/konten/' . $base))) {
                    return asset('assets/uploads/konten/' . $base);
                }
                if (file_exists(public_path('assets/uploads/berita/' . $base))) {
                    return asset('assets/uploads/berita/' . $base);
                }
                if (file_exists(public_path('uploads/konten/' . $base))) {
                    return asset('uploads/konten/' . $base);
                }
                if (file_exists(public_path('uploads/berita/' . $base))) {
                    return asset('uploads/berita/' . $base);
                }
                if (str_starts_with($clean, 'storage/') || str_starts_with($clean, 'assets/') || str_starts_with($clean, 'uploads/')) {
                    return asset($clean);
                }

                return asset('assets/uploads/konten/' . $base);
            };
        @endphp

        <div class="row g-4">
            @forelse($artikel ?? [] as $item)
                @php
                    $publishedAt = $item->tanggal_publish ?? $item->created_at ?? now();
                    $excerpt = $item->excerpt ?? $item->ringkasan ?? Str::limit(strip_tags($item->konten ?? $item->isi ?? $item->isi_konten ?? $item->deskripsi ?? ''), 120);
                    $catRaw = strtolower($item->kategori ?? $item->tipe ?? 'berita');
                    $isPengumuman = str_contains($catRaw, 'pengum') || str_contains($catRaw, 'pengumuman');
                    $catClass = $isPengumuman ? 'category-announcement' : 'category-news';
                    $catIcon = $isPengumuman ? 'fa-megaphone' : 'fa-newspaper';
                @endphp
                <div class="col-lg-4 col-md-6">
                    <div class="card news-card h-100 shadow-sm" style="border-radius: 16px; overflow: hidden; border: 1px solid rgba(0,0,0,0.06); transition: transform 0.3s, box-shadow 0.3s; background: #ffffff;">
                        <a href="/artikel/{{ $item->slug }}" class="d-block position-relative overflow-hidden" style="height: 210px; background: #e2e8f0; text-decoration: none;">
                            <span class="news-category {{ $catClass }}" style="position: absolute; top: 12px; left: 12px; z-index: 2; padding: 5px 12px; border-radius: 20px; font-size: 11px; font-weight: 700; color: white; background: var(--primary-teal);">
                                <i class="fas {{ $catIcon }} me-1"></i>
                                {{ $item->kategori ?? $item->tipe ?? 'Berita Paroki' }}
                            </span>
                            <img src="{{ $newsImageUrl($item->gambar ?? null) }}" class="card-img-top w-100 h-100" alt="{{ $item->judul }}" loading="lazy" style="object-fit: cover; transition: transform 0.5s;">
                        </a>
                        <div class="card-body d-flex flex-column p-4">
                            <span class="news-date mb-2" style="display: inline-block; padding: 4px 10px; border-radius: 6px; background: var(--primary-orange); color: white; font-size: 12px; font-weight: 600; width: fit-content;">
                                <i class="far fa-calendar me-1"></i> {{ \Carbon\Carbon::parse($publishedAt)->translatedFormat('j F Y') }}
                            </span>
                            <h5 class="card-title fw-bold mb-2" style="font-size: 1.05rem; line-height: 1.4; color: #1e293b; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                <a href="/artikel/{{ $item->slug }}" style="text-decoration: none; color: inherit; transition: color 0.2s;" onmouseover="this.style.color='var(--primary-teal)'" onmouseout="this.style.color='#1e293b'">
                                    {{ $item->judul }}
                                </a>
                            </h5>
                            <p class="card-text text-muted mb-3 flex-grow-1" style="font-size: 0.85rem; line-height: 1.6; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ $excerpt }}
                            </p>
                            <a href="/artikel/{{ $item->slug }}" class="btn-news">
                                Baca Selengkapnya <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5 text-muted">
                    <i class="far fa-newspaper fa-3x mb-3 text-secondary"></i>
                    <p class="mb-0">Artikel & Berita belum tersedia.</p>
                </div>
            @endforelse
        </div>

        <div class="text-center mt-5">
            <a href="/berita" class="btn btn-outline-teal px-4 py-2" style="border: 2px solid var(--primary-teal); color: var(--primary-teal); border-radius: 30px; font-weight: 600; font-size: 0.9rem; transition: all 0.3s;">
                Lihat Semua Berita &amp; Artikel <i class="fas fa-arrow-right ms-2 text-xs"></i>
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

        @php
            $galleryImage = function ($item) {
                $path = $item->gambar ?? $item->youtube_thumbnail ?? null;
                if (empty($path)) return null;
                if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) return $path;
                $clean = ltrim($path, '/');
                $base = basename($clean);
                if (str_starts_with($clean, 'uploads/galeri/')) return asset($clean);
                if (str_starts_with($clean, 'uploads/') || str_starts_with($clean, 'assets/')) return asset($clean);
                if (file_exists(public_path('uploads/galeri/' . $base))) return asset('uploads/galeri/' . $base);
                if (file_exists(public_path('assets/uploads/galeri/' . $base))) return asset('assets/uploads/galeri/' . $base);
                return asset('uploads/galeri/' . $base);
            };
        @endphp

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @forelse($galeri ?? [] as $item)
            @php $imgUrl = $galleryImage($item); @endphp
            @if($imgUrl)
                <a href="{{ $imgUrl }}" class="group relative rounded-2xl overflow-hidden aspect-square bg-slate-200 dark:bg-slate-800 shadow-sm hover:shadow-xl transition-all duration-300 block" data-lightbox="galeri-beranda" data-title="{{ $item->judul ?? 'Dokumentasi Paroki' }}">
                    <img src="{{ $imgUrl }}" alt="{{ $item->judul ?? 'Galeri Foto' }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-teal-950/80 via-teal-900/40 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex flex-col items-center justify-end p-3 text-white text-center">
                        <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur-sm flex items-center justify-center mb-2 transform translate-y-3 group-hover:translate-y-0 transition-transform duration-300">
                            <i class="fas fa-search-plus text-base text-white"></i>
                        </div>
                        @if(!empty($item->judul))
                            <p class="text-xs font-semibold line-clamp-1 text-white/95">{{ $item->judul }}</p>
                        @endif
                    </div>
                </a>
            @else
                <div class="rounded-2xl overflow-hidden aspect-square bg-slate-200 dark:bg-slate-800 shadow-sm flex items-center justify-center text-slate-400">
                    <i class="far fa-image text-2xl"></i>
                </div>
            @endif
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




