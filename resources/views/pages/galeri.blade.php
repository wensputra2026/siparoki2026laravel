@extends('layouts.app')

@section('title', 'Galeri Foto & Video - ' . ($globalNamaParoki ?? 'SIPAROKI'))

@section('content')
@php
    $galleryImage = function ($item) {
        $path = $item->gambar ?? $item->youtube_thumbnail ?? null;

        if (empty($path)) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        $clean = ltrim($path, '/');
        $base = basename($clean);

        if (file_exists(public_path('uploads/galeri/' . $base))) {
            return asset('uploads/galeri/' . $base);
        }

        if (file_exists(public_path('assets/uploads/galeri/' . $base))) {
            return asset('assets/uploads/galeri/' . $base);
        }

        if (str_starts_with($clean, 'storage/') || str_starts_with($clean, 'assets/') || str_starts_with($clean, 'uploads/')) {
            return asset($clean);
        }

        return asset('uploads/galeri/' . $base);
    };
@endphp

<!-- Page Header / Breadcrumb Konoha Style -->
<section class="page-header">
    <div class="container">
        <h1 style="font-size: 2.6rem; font-weight: 700; margin-bottom: 15px; color: #ffffff;">Galeri Dokumentasi</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="display: inline-flex; list-style: none; padding: 0; margin: 0 auto; gap: 12px; background: transparent; justify-content: center; align-items: center;">
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/" style="color: white; text-decoration: none; font-weight: 500;">Beranda</a>
                </li>
                <li class="breadcrumb-item active" style="background: var(--primary-orange, #ff9800); color: white; padding: 6px 18px; border-radius: 25px; font-size: 0.85rem; font-weight: 600;">
                    Galeri
                </li>
            </ol>
        </nav>
    </div>
</section>

<section class="content-section" style="padding: 60px 0 80px; background: #f8fafc;" id="gallery" aria-label="Galeri dokumentasi">
    <div class="container">
        @if(($galeri ?? collect())->count())
            <div class="row g-3 g-md-4">
                @foreach($galeri as $i => $item)
                    @php
                        $img = $galleryImage($item);
                        $title = $item->judul ?? $item->album ?? 'Dokumentasi ' . ($i + 1);
                    @endphp

                    @if($img)
                        <div class="col-6 col-md-4 col-lg-3">
                            <a href="{{ $img }}" 
                               class="home-gallery-card" 
                               data-lightbox="galeri" 
                               data-title="{{ $title }}">
                                <img src="{{ $img }}" 
                                     alt="{{ $title }}" 
                                     loading="lazy">
                                <div class="home-gallery-overlay">
                                    <span class="home-gallery-badge">
                                        <i class="fas fa-search-plus me-1"></i> Perbesar
                                    </span>
                                    @if(!empty($title))
                                        <p class="home-gallery-title" title="{{ $title }}">
                                            {{ $title }}
                                        </p>
                                    @endif
                                </div>
                            </a>
                        </div>
                    @endif
                @endforeach
            </div>

            @if(method_exists($galeri, 'links'))
                <div class="d-flex justify-content-center mt-5">
                    {{ $galeri->links() }}
                </div>
            @endif
        @else
            <div style="background: #ffffff; border-radius: 15px; padding: 60px 20px; text-align: center; color: #94a3b8; box-shadow: 0 5px 20px rgba(0,0,0,0.06);">
                <i class="far fa-images" style="font-size: 3rem; margin-bottom: 15px; display: block; color: #cbd5e1;"></i>
                <p style="font-size: 1.05rem; font-weight: 600; margin: 0;">Belum ada dokumentasi foto galeri yang diunggah.</p>
            </div>
        @endif
    </div>
</section>
@endsection

