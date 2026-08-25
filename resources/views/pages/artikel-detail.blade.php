@extends('layouts.app')

@php
    $detailType = $detailType ?? 'berita';
    $body = $item->isi ?? $item->konten ?? '';
    $description = $item->meta_description ?? $item->excerpt ?? Str::limit(strip_tags($body), 160);
    $imagePath = $item->gambar ?? null;
    $imageUrl = null;

    if ($imagePath) {
        if (Str::startsWith($imagePath, ['http://', 'https://'])) {
            $imageUrl = $imagePath;
        } else {
            $cleanImagePath = ltrim($imagePath, '/');
            $base = basename($cleanImagePath);
            if (str_starts_with($cleanImagePath, 'uploads/konten/') || str_starts_with($cleanImagePath, 'assets/uploads/konten/')) {
                $imageUrl = asset($cleanImagePath);
            } elseif (file_exists(public_path('uploads/konten/' . $base))) {
                $imageUrl = asset('uploads/konten/' . $base);
            } elseif (file_exists(public_path('assets/uploads/konten/' . $base))) {
                $imageUrl = asset('assets/uploads/konten/' . $base);
            } elseif (str_starts_with($cleanImagePath, 'storage/') || str_starts_with($cleanImagePath, 'assets/') || str_starts_with($cleanImagePath, 'uploads/')) {
                $imageUrl = asset($cleanImagePath);
            } else {
                $imageUrl = asset('uploads/konten/' . $base);
            }
        }
    } elseif (!empty($globalLogo)) {
        $imageUrl = Str::startsWith($globalLogo, ['http://', 'https://']) ? $globalLogo : url($globalLogo);
    }

    $publishedAt = $item->tanggal_publish ?? $item->created_at ?? now();
    $backRoute = $detailType === 'berita' ? route('berita') : route('artikel');
    $backLabel = $detailType === 'berita' ? 'Kembali ke Berita' : 'Kembali ke Artikel';
    $relatedRouteName = $detailType === 'berita' ? 'berita.detail' : 'artikel.detail';
    $categoryClass = Str::contains(strtolower($item->kategori ?? ''), 'pengumuman') ? 'category-announcement' : 'category-news';
@endphp

@section('title', ($item->meta_title ?? $item->judul) . ' - ' . ($globalNamaParoki ?? 'SIPAROKI'))
@section('description', $description)
@section('keywords', trim(($item->tags ?? '') . ', ' . ($item->kategori ?? '') . ', paroki, gereja katolik'))
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
    @if(!empty($item->kategori))
        <meta property="article:section" content="{{ $item->kategori }}">
    @endif
@endsection
@push('head')
    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $item->judul,
            'description' => $description,
            'image' => $imageUrl ? [$imageUrl] : [],
            'datePublished' => \Carbon\Carbon::parse($publishedAt)->toIso8601String(),
            'dateModified' => \Carbon\Carbon::parse($item->updated_at ?? $publishedAt)->toIso8601String(),
            'author' => ['@type' => 'Person', 'name' => $item->penulis ?? 'Admin'],
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
                    <a href="{{ $backRoute }}" style="color: white; text-decoration: none; font-weight: 500;">{{ $detailType === 'berita' ? 'Berita' : 'Artikel' }}</a>
                </li>
                <li class="breadcrumb-item active" style="background: var(--primary-orange, #ff9800); color: white; padding: 6px 20px; border-radius: 25px; font-size: 0.85rem; font-weight: 700;">
                    {{ $item->judul }}
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- Main Detail Content Section Konoha Style -->
<section class="content-section" style="padding: 50px 0 70px; background: #f4faf9;">
    <div class="container" style="max-width: 1180px; margin: 0 auto; padding: 0 20px;">
        
        <!-- Back Link Above Card -->
        <div style="margin-bottom: 25px;">
            <a href="{{ $backRoute }}" class="back-link" style="display: inline-flex; align-items: center; gap: 8px; color: var(--primary-teal, #00897b); font-weight: 600; text-decoration: none; font-size: 0.95rem; transition: transform 0.2s;">
                <i class="fas fa-arrow-left"></i> {{ $backLabel }}
            </a>
        </div>

        <div style="display: grid; grid-template-columns: 2.2fr 1fr; gap: 32px; align-items: start;">
            
            <!-- Left: Main Article Card -->
            <div>
                <div class="detail-content" style="background: #ffffff; padding: 35px 40px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.06);">
                    
                    <!-- 1. Category Pill Badge -->
                    <div style="margin-bottom: 20px;">
                        <span style="display: inline-block; background: #5c6bc0; color: #ffffff; padding: 6px 20px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                            {{ $item->kategori ?? 'BERITA' }}
                        </span>
                    </div>

                    <!-- 2. Meta Info Row -->
                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 24px; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 1px solid #eef2f6; font-size: 0.9rem; color: #64748b;">
                        <span style="display: inline-flex; align-items: center; gap: 7px;">
                            <i class="far fa-calendar-alt" style="color: #ff9800; font-size: 1rem;"></i> 
                            {{ \Carbon\Carbon::parse($publishedAt)->translatedFormat('d M Y') }}
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 7px;">
                            <i class="far fa-user" style="color: #ff9800; font-size: 1rem;"></i> 
                            {{ $item->penulis ?? 'Super Administrator' }}
                        </span>
                        <span style="display: inline-flex; align-items: center; gap: 7px;">
                            <i class="far fa-eye" style="color: #ff9800; font-size: 1rem;"></i> 
                            {{ number_format((int) ($item->views ?? 1), 0, ',', '.') }} views
                        </span>
                    </div>

                    <!-- 3. Featured Image Below Meta -->
                    @if($imageUrl)
                        <div style="border-radius: 12px; overflow: hidden; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.06);">
                            <img src="{{ $imageUrl }}" alt="{{ $item->judul }}" style="width: 100%; max-height: 480px; object-fit: cover; display: block;">
                        </div>
                    @endif

                    <!-- 4. Article HTML Body -->
                    <div class="article-text" style="color: #334155; font-size: 1rem; line-height: 1.85;">
                        {!! $body !!}
                    </div>

                    <!-- 5. Tags / Labels -->
                    @if(!empty($item->tags))
                        <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eef2f6; display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
                            <span style="font-size: 0.85rem; font-weight: 600; color: #64748b; margin-right: 4px;">
                                <i class="fas fa-tags" style="color: var(--primary-teal, #00897b);"></i> Label:
                            </span>
                            @foreach(explode(',', $item->tags) as $tag)
                                @if(trim($tag))
                                    <span style="background: #f1f5f9; color: #475569; padding: 4px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 500;">
                                        #{{ trim($tag) }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right: Sidebar Column -->
            <div>
                <!-- Berita Terkait Box -->
                <div style="background: #ffffff; padding: 25px 28px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.06);">
                    <h4 style="color: var(--primary-teal, #00897b); font-weight: 700; font-size: 1.15rem; margin-bottom: 22px; display: flex; align-items: center; gap: 10px;">
                        <i class="far fa-newspaper" style="color: #ff9800; font-size: 1.2rem;"></i> Berita Terkait
                    </h4>

                    @if($terkait->isNotEmpty())
                        <div style="display: flex; flex-direction: column; gap: 16px;">
                            @foreach($terkait as $art)
                                @php
                                    $relDate = $art->tanggal_publish ?? $art->created_at ?? now();
                                @endphp
                                <div style="background: #f8fafc; border-left: 4px solid var(--primary-orange, #ff9800); border-radius: 0 10px 10px 0; padding: 16px 18px; transition: transform 0.2s, box-shadow 0.2s;">
                                    <a href="{{ route($relatedRouteName, $art->slug) }}" style="text-decoration: none;">
                                        <h5 style="color: #1e293b; font-weight: 700; font-size: 0.95rem; line-height: 1.45; margin-bottom: 8px;">
                                            {{ $art->judul }}
                                        </h5>
                                    </a>
                                    <div style="font-size: 0.8rem; color: #94a3b8; display: flex; align-items: center; gap: 6px;">
                                        <i class="far fa-calendar-alt" style="color: #6366f1;"></i> 
                                        {{ \Carbon\Carbon::parse($relDate)->translatedFormat('d M Y') }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p style="color: #94a3b8; font-size: 0.88rem; margin: 0;">Tidak ada berita terkait lainnya.</p>
                    @endif
                </div>

                <!-- Info Kontak / Pelayanan Card -->
                <div style="background: #ffffff; padding: 25px 28px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.06); margin-top: 24px; text-align: center;">
                    <div style="width: 54px; height: 54px; background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; font-size: 1.4rem;">
                        <i class="fas fa-church"></i>
                    </div>
                    <h5 style="font-weight: 700; color: #1e293b; font-size: 1rem; margin-bottom: 6px;">{{ $globalNamaParoki ?? 'SIPAROKI' }}</h5>
                    <p style="font-size: 0.84rem; color: #64748b; line-height: 1.5; margin-bottom: 16px;">
                        Ikuti terus perkembangan warta, katekese, dan agenda pelayanan pastoral paroki.
                    </p>
                    <a href="/kontak" class="btn-program" style="display: inline-block; background: var(--primary-teal, #00897b); color: white; padding: 8px 24px; border-radius: 20px; font-weight: 600; text-decoration: none; font-size: 0.84rem;">
                        Hubungi Sekretariat
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
