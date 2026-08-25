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

<section class="content-section" style="padding: 60px 0 80px; background: #f4faf9;" id="gallery" aria-label="Galeri dokumentasi">
    <div class="container">
        @if(($galeri ?? collect())->count())
            <div class="gallery-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px;">
                @foreach($galeri as $i => $item)
                    @php
                        $img = $galleryImage($item);
                        $title = $item->judul ?? $item->album ?? 'Dokumentasi ' . ($i + 1);
                    @endphp

                    @if($img)
                        <a href="{{ $img }}" class="gallery-item" data-lightbox="galeri" data-title="{{ $title }}" style="display: block; border-radius: 15px; overflow: hidden; height: 240px; box-shadow: 0 5px 20px rgba(0,0,0,0.06); position: relative; transition: transform 0.3s, box-shadow 0.3s;">
                            <img src="{{ $img }}" alt="{{ $title }}" loading="lazy" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s;">
                            <span class="gi-overlay" style="position: absolute; inset: 0; background: rgba(0,137,123,0.5); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; opacity: 0; transition: opacity 0.3s;">
                                <i class="fas fa-search-plus" aria-hidden="true"></i>
                            </span>
                        </a>
                    @endif
                @endforeach
            </div>
        @else
            <div style="background: #ffffff; border-radius: 15px; padding: 60px 20px; text-align: center; color: #94a3b8; box-shadow: 0 5px 20px rgba(0,0,0,0.06);">
                <i class="far fa-images" style="font-size: 3rem; margin-bottom: 15px; display: block; color: #cbd5e1;"></i>
                <p style="font-size: 1.05rem; font-weight: 600; margin: 0;">Belum ada dokumentasi foto galeri yang diunggah.</p>
            </div>
        @endif
    </div>
</section>
@endsection

