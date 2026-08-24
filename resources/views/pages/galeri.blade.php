@extends('layouts.app')

@section('title', 'Galeri - SIPAROKI')

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

        if (str_starts_with($clean, 'storage/') || str_starts_with($clean, 'assets/') || str_starts_with($clean, 'uploads/')) {
            return asset($clean);
        }

        return asset('assets/uploads/galeri/' . $base);
    };
@endphp

<section class="section" id="gallery" aria-label="Galeri dokumentasi">
    <div class="container">
        <div class="section-title">
            <span class="st-badge">Galeri</span>
            <h2>Galeri <span class="text-gradient">Dokumentasi kegiatan dan momen berharga</span></h2>
            <p>Dokumentasi kegiatan dan momen berharga paroki.</p>
        </div>

        @if(($galeri ?? collect())->count())
            <div class="gallery-grid">
                @foreach($galeri as $i => $item)
                    @php
                        $img = $galleryImage($item);
                        $title = $item->judul ?? $item->album ?? 'Galeri ' . ($i + 1);
                    @endphp

                    @if($img)
                        <a href="{{ $img }}" class="gallery-item {{ $i === 0 ? 'gi-tall' : '' }}" data-lightbox>
                            <img src="{{ $img }}" alt="{{ $title }}" loading="lazy">
                            <span class="gi-overlay"><i class="fas fa-plus" aria-hidden="true"></i></span>
                        </a>
                    @endif
                @endforeach
            </div>
        @else
            <p class="gallery-empty">Belum ada galeri.</p>
        @endif
    </div>
</section>
@endsection
