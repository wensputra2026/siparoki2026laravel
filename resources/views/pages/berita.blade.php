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

<!-- Page Header -->
<section class="page-header">
    <div class="container">
        <h1>Berita &amp; Pengumuman</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/">Beranda</a></li>
                <li class="breadcrumb-item active">Berita</li>
            </ol>
        </nav>
    </div>
</section>

<!-- Content Section -->
<section class="content-section">
    <div class="container">
        @if(($berita ?? null) && $berita->total() > 0)
            <!-- News Info -->
            <div style="margin-bottom: 40px; padding: 20px; background: linear-gradient(135deg, rgba(0,137,123,0.05), rgba(255,152,0,0.05)); border-radius: 12px; border-left: 4px solid var(--primary-orange);">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <p style="margin: 0; color: #6B7280; font-size: 14px;">
                            <i class="fas fa-layer-group" style="color: var(--primary-teal); margin-right: 8px;"></i>
                            Menampilkan <strong style="color: var(--primary-orange);">{{ $berita->firstItem() }} - {{ $berita->lastItem() }}</strong> dari <strong style="color: var(--primary-teal);">{{ $berita->total() }}</strong> berita
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <span style="color: #6B7280; font-size: 14px;">
                            <i class="fas fa-bookmark" style="color: var(--primary-orange); margin-right: 8px;"></i>
                            Halaman <strong style="color: var(--primary-orange);">{{ $berita->currentPage() }}</strong> dari <strong style="color: var(--primary-teal);">{{ $berita->lastPage() }}</strong>
                        </span>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                @foreach($berita as $item)
                    @php
                        $publishedAt = $item->tanggal_publish ?? $item->created_at ?? now();
                        $excerpt = $item->excerpt ?? Str::limit(strip_tags($item->isi ?? ''), 150);
                        $catRaw = strtolower($item->kategori ?? $item->tipe ?? 'berita');
                        $isPengumuman = str_contains($catRaw, 'pengum') || str_contains($catRaw, 'pengumuman');
                        $catClass = $isPengumuman ? 'category-announcement' : 'category-news';
                        $catIcon = $isPengumuman ? 'fa-megaphone' : 'fa-newspaper';
                    @endphp
                    <div class="col-lg-4 col-md-6">
                        <div class="card news-card">
                            <div class="position-relative">
                                <span class="news-category {{ $catClass }}">
                                    <i class="fas {{ $catIcon }} me-1"></i>
                                    {{ $item->kategori ?? $item->tipe ?? 'Berita' }}
                                </span>
                                <img src="{{ $imageUrl($item->gambar ?? null) }}" class="card-img-top news-img" alt="{{ $item->judul }}" loading="lazy">
                            </div>
                            <div class="card-body">
                                <span class="news-date"><i class="far fa-calendar"></i> {{ \Carbon\Carbon::parse($publishedAt)->translatedFormat('j F Y') }}</span>
                                <h5 class="card-title">{{ $item->judul }}</h5>
                                <p class="card-text">{{ $excerpt }}</p>
                                <a href="{{ route('berita.detail', $item->slug) }}" class="btn-news">
                                    <i class="fas fa-arrow-right me-1"></i> Selengkapnya
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            @if($berita->hasPages())
                <div class="news-pagination mt-4 d-flex justify-content-center">
                    {{ $berita->withQueryString()->links() }}
                </div>
            @endif
        @else
            <p class="ns-muted">Belum ada berita saat ini.</p>
        @endif
    </div>
</section>
@endsection
