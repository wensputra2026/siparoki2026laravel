@extends('layouts.app')

@php
    $description = $item->ringkasan ?? Str::limit(strip_tags($item->isi ?? ''), 160);
    $imagePath = $item->gambar ?? null;
    $imageUrl = null;

    if ($imagePath) {
        if (Str::startsWith($imagePath, ['http://', 'https://'])) {
            $imageUrl = $imagePath;
        } else {
            $cleanImagePath = ltrim($imagePath, '/');
            $base = basename($cleanImagePath);
            if (file_exists(public_path('uploads/pengumuman/' . $base))) {
                $imageUrl = asset('uploads/pengumuman/' . $base);
            } elseif (file_exists(public_path('assets/uploads/pengumuman/' . $base))) {
                $imageUrl = asset('assets/uploads/pengumuman/' . $base);
            } else {
                $imageUrl = asset('uploads/pengumuman/' . $base);
            }
        }
    } elseif (!empty($globalLogo)) {
        $imageUrl = Str::startsWith($globalLogo, ['http://', 'https://']) ? $globalLogo : url($globalLogo);
    }
    $publishedAt = $item->created_at ?? now();
@endphp

@section('title', $item->judul . ' - ' . ($globalNamaParoki ?? 'SIPAROKI'))
@section('description', $description)
@section('keywords', trim(($item->kategori ?? '') . ', pengumuman, paroki, gereja katolik'))
@section('og_type', 'article')
@section('image', $imageUrl)
@section('article_meta')
    <meta property="article:published_time" content="{{ \Carbon\Carbon::parse($publishedAt)->toIso8601String() }}">
    @if(!empty($item->updated_at))
        <meta property="article:modified_time" content="{{ \Carbon\Carbon::parse($item->updated_at)->toIso8601String() }}">
    @endif
    @if(!empty($item->penulis))
        <meta property="article:author" content="{{ $item->penulis }}">
    @endif
@endsection
@push('head')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'NewsArticle',
            'headline' => $item->judul,
            'description' => $description,
            'image' => $imageUrl ? [$imageUrl] : [],
            'datePublished' => \Carbon\Carbon::parse($publishedAt)->toIso8601String(),
            'dateModified' => \Carbon\Carbon::parse($item->updated_at ?? $publishedAt)->toIso8601String(),
            'author' => ['@type' => 'Person', 'name' => $item->penulis ?? 'Sekretariat'],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $globalNamaParoki ?? 'SIPAROKI',
                'logo' => ['@type' => 'ImageObject', 'url' => !empty($globalLogo) ? url($globalLogo) : null],
            ],
            'mainEntityOfPage' => url()->current(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@section('content')
<!-- Page Header / Breadcrumb Konoha Style -->
<section class="page-header">
    <div class="container" style="max-width: 1180px; margin: 0 auto; padding: 0 20px;">
        <h1 style="font-size: 2.6rem; font-weight: 700; margin-bottom: 18px; color: #ffffff; line-height: 1.25; max-width: 960px; margin-left: auto; margin-right: auto;">
            {{ $item->judul }}
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="display: inline-flex; list-style: none; padding: 0; margin: 0 auto; gap: 10px; background: transparent; justify-content: center; align-items: center; flex-wrap: wrap;">
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/" style="color: white; text-decoration: none; font-weight: 500;">Beranda</a>
                </li>
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/pengumuman" style="color: white; text-decoration: none; font-weight: 500;">Pengumuman</a>
                </li>
                <li class="breadcrumb-item active" style="background: var(--primary-orange, #ff9800); color: white; padding: 6px 20px; border-radius: 25px; font-size: 0.85rem; font-weight: 700;">
                    {{ Str::limit($item->judul, 25) }}
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- Detail Content Section Konoha Style -->
<section class="content-section" style="padding: 50px 0 70px; background: #f4faf9;">
    <div class="container" style="max-width: 960px; margin: 0 auto; padding: 0 20px;">
        
        <div style="margin-bottom: 25px;">
            <a href="/pengumuman" class="back-link" style="display: inline-flex; align-items: center; gap: 8px; color: var(--primary-teal, #00897b); font-weight: 600; text-decoration: none; font-size: 0.95rem;">
                <i class="fas fa-arrow-left"></i> Kembali ke Pengumuman
            </a>
        </div>

        <div class="detail-content" style="background: #ffffff; padding: 35px 40px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.06); border-left: 5px solid var(--primary-orange, #ff9800);">
            
            <div style="margin-bottom: 20px;">
                <span style="display: inline-block; background: #e0f2fe; color: #0284c7; padding: 6px 20px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">
                    {{ $item->kategori ?? 'PENGUMUMAN' }}
                </span>
            </div>

            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 24px; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #eef2f6; font-size: 0.9rem; color: #64748b;">
                <span style="display: inline-flex; align-items: center; gap: 7px;">
                    <i class="far fa-calendar-alt" style="color: #ff9800; font-size: 1rem;"></i> 
                    {{ \Carbon\Carbon::parse($publishedAt)->translatedFormat('l, d F Y') }}
                </span>
                <span style="display: inline-flex; align-items: center; gap: 7px;">
                    <i class="far fa-user" style="color: #ff9800; font-size: 1rem;"></i> 
                    {{ $item->penulis ?? 'Sekretariat Paroki' }}
                </span>
            </div>

            @if($imageUrl)
                <div style="border-radius: 12px; overflow: hidden; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.06);">
                    <img src="{{ $imageUrl }}" alt="{{ $item->judul }}" style="width: 100%; max-height: 450px; object-fit: cover; display: block;">
                </div>
            @endif

            <div class="article-text" style="color: #334155; font-size: 1rem; line-height: 1.85;">
                {!! $item->isi !!}
            </div>
        </div>
    </div>
</section>
@endsection

