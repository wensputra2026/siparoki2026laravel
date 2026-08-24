@extends('layouts.app')

@section('title', 'Berita Paroki - ' . ($globalNamaParoki ?? 'SIPAROKI'))

@section('content')
@php
    $imageUrl = function ($path) {
        if (empty($path)) {
            return asset('images/news-placeholder.svg');
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $clean = ltrim($path, '/');
        $base = basename($clean);

        if (str_starts_with($clean, 'storage/') || str_starts_with($clean, 'assets/') || str_starts_with($clean, 'uploads/')) {
            return asset($clean);
        }

        return asset('assets/uploads/konten/' . $base);
    };
@endphp

<section class="page-banner page-hero" id="main-content">
    <div class="page-banner-shape page-banner-shape--1" aria-hidden="true"></div>
    <div class="page-banner-shape page-banner-shape--2" aria-hidden="true"></div>
    <div class="container page-banner-content">
        <span class="page-banner-badge"><i class="fas fa-newspaper"></i> Berita & Artikel</span>
        <h1>Berita <span class="text-gradient">{{ $globalNamaParoki ?? 'SIPAROKI' }}</span></h1>
        <p>Informasi terkini seputar kegiatan, pelayanan, pengumuman, dan dinamika kehidupan umat paroki.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="news-layout">
            <div class="news-main">
                <div class="news-filter">
                    <a href="{{ route('berita') }}" class="nf-btn {{ empty($activeCategory) && empty($activeTag) ? 'active' : '' }}">Semua</a>
                    @foreach(($categories ?? collect())->take(5) as $cat)
                        <a href="{{ route('berita', ['category' => $cat->kategori]) }}"
                           class="nf-btn {{ ($activeCategory ?? '') === $cat->kategori ? 'active' : '' }}">
                            {{ $cat->kategori }}
                        </a>
                    @endforeach
                </div>

                <div class="news-grid">
                    @forelse($berita ?? [] as $item)
                        @php
                            $publishedAt = $item->tanggal_publish ?? $item->created_at ?? now();
                            $excerpt = $item->excerpt ?? Str::limit(strip_tags($item->isi ?? ''), 150);
                        @endphp
                        <article class="news-card" data-category="{{ Str::slug($item->kategori ?? 'berita') }}">
                            <div class="bc-img">
                                <img loading="lazy" src="{{ $imageUrl($item->gambar ?? null) }}" alt="{{ $item->judul }}">
                                <span class="bc-cat">{{ $item->kategori ?? $item->tipe ?? 'Berita' }}</span>
                            </div>
                            <div class="bc-body">
                                <div class="bc-meta">
                                    <span><i class="far fa-calendar"></i> {{ \Carbon\Carbon::parse($publishedAt)->translatedFormat('j F Y') }}</span>
                                    <span><i class="far fa-user"></i> {{ $item->penulis ?? 'Admin' }}</span>
                                </div>
                                <h3>{{ $item->judul }}</h3>
                                <p>{{ $excerpt }}</p>
                                <div class="bc-footer">
                                    <span><i class="far fa-eye"></i> {{ number_format((int) ($item->views ?? 0), 0, ',', '.') }}</span>
                                    <a href="{{ route('berita.detail', $item->slug) }}" class="bc-link">
                                        Baca Selengkapnya <i class="fas fa-arrow-right"></i>
                                    </a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <p>Tidak ada berita saat ini.</p>
                    @endforelse
                </div>

                @if(($berita ?? null) && $berita->hasPages())
                    <div class="news-pagination">
                        {{ $berita->withQueryString()->links() }}
                    </div>
                @endif
            </div>

            <aside class="news-sidebar">
                <div class="ns-widget ns-pastor-widget">
                    <span class="ns-widget-kicker"><i class="fas fa-quote-left"></i> Sambutan Pastor Paroki</span>
                    <h3>{{ $pastor_paroki ?? 'Pastor Paroki' }}</h3>
                    <p>Salve, Salam Sehat dan Berkah Dalem. Selamat datang di website resmi {{ $globalNamaParoki ?? $nama_paroki ?? 'SIPAROKI' }}.</p>
                    <a href="{{ route('sambutan') }}" class="ns-pastor-link">
                        Baca Sambutan <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

                <div class="ns-widget">
                    <h3>Pencarian</h3>
                    <form class="ns-search" action="{{ route('berita') }}" method="get">
                        <input type="text" name="search" placeholder="Cari berita..." value="{{ $search ?? '' }}">
                        <button type="submit" aria-label="Cari berita"><i class="fas fa-search"></i></button>
                    </form>
                </div>

                <div class="ns-widget">
                    <h3>Kategori</h3>
                    <div class="ns-categories">
                        @forelse(($categories ?? collect()) as $cat)
                            <a href="{{ route('berita', ['category' => $cat->kategori]) }}" class="{{ ($activeCategory ?? '') === $cat->kategori ? 'active' : '' }}">
                                {{ $cat->kategori }} <span>{{ $cat->total }}</span>
                            </a>
                        @empty
                            <p class="ns-muted">Belum ada kategori.</p>
                        @endforelse
                    </div>
                </div>

                <div class="ns-widget">
                    <h3>Berita Terbaru</h3>
                    <div class="ns-recent">
                        @forelse(($recentNews ?? collect()) as $recent)
                            @php $recentDate = $recent->tanggal_publish ?? $recent->created_at ?? now(); @endphp
                            <a href="{{ route('artikel.detail', $recent->slug) }}" class="nsr-item">
                                <img loading="lazy" src="{{ $imageUrl($recent->gambar ?? null) }}" alt="{{ $recent->judul }}">
                                <div>
                                    <h4>{{ $recent->judul }}</h4>
                                    <span>{{ \Carbon\Carbon::parse($recentDate)->translatedFormat('j F Y') }}</span>
                                </div>
                            </a>
                        @empty
                            <p class="ns-muted">Belum ada berita terbaru.</p>
                        @endforelse
                    </div>
                </div>

                <div class="ns-widget">
                    <h3>Arsip Berita</h3>
                    <div class="ns-archive">
                        @forelse(($archive ?? collect()) as $item)
                            <a href="{{ route('berita', ['month' => $item->month_key]) }}" class="{{ ($activeMonth ?? '') === $item->month_key ? 'active' : '' }}">
                                {{ $item->label }} <span>{{ $item->total }}</span>
                            </a>
                        @empty
                            <p class="ns-muted">Belum ada arsip.</p>
                        @endforelse
                    </div>
                </div>

                <div class="ns-widget">
                    <h3>Tags</h3>
                    <div class="ns-tags">
                        @forelse(($tags ?? collect()) as $tag)
                            <a href="{{ route('berita', ['tag' => $tag]) }}" class="{{ ($activeTag ?? '') === $tag ? 'active' : '' }}">{{ $tag }}</a>
                        @empty
                            <p class="ns-muted">Belum ada tag.</p>
                        @endforelse
                    </div>
                </div>
            </aside>
        </div>
    </div>
</section>
@endsection

