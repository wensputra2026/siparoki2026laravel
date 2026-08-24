@extends('layouts.app')

@php
    $detailType = $detailType ?? 'artikel';
    $body = $item->isi ?? $item->konten ?? '';
    $description = $item->meta_description ?? $item->excerpt ?? Str::limit(strip_tags($body), 160);
    $imagePath = $item->gambar ?? null;
    $imageUrl = null;

    if ($imagePath) {
        if (Str::startsWith($imagePath, ['http://', 'https://'])) {
            $imageUrl = $imagePath;
        } else {
            $cleanImagePath = ltrim($imagePath, '/');
            $imageUrl = Str::startsWith($cleanImagePath, ['storage/', 'assets/', 'uploads/'])
                ? asset($cleanImagePath)
                : asset('assets/uploads/konten/' . basename($cleanImagePath));
        }
    } elseif (!empty($globalLogo)) {
        $imageUrl = Str::startsWith($globalLogo, ['http://', 'https://']) ? $globalLogo : url($globalLogo);
    }

    $publishedAt = $item->tanggal_publish ?? $item->created_at ?? now();
    $backRoute = $detailType === 'berita' ? route('berita') : route('artikel');
    $backLabel = $detailType === 'berita' ? 'Kembali ke Berita' : 'Kembali ke Artikel';
    $relatedRouteName = $detailType === 'berita' ? 'berita.detail' : 'artikel.detail';
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
<section class="page-banner page-hero">
    <div class="page-banner-shape page-banner-shape--1" aria-hidden="true"></div>
    <div class="page-banner-shape page-banner-shape--2" aria-hidden="true"></div>
    <div class="container page-banner-content">
        @if($item->kategori)
            <span class="page-banner-badge"><i class="fas fa-newspaper"></i> {{ $item->kategori }}</span>
        @endif
        <h1>{{ $item->judul }}</h1>
        <p>
            {{ \Carbon\Carbon::parse($publishedAt)->translatedFormat('l, d F Y') }}
            @if($item->penulis) &bull; {{ $item->penulis }} @endif
        </p>
    </div>
</section>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            @if($imageUrl)
                <div class="mb-6 rounded-xl overflow-hidden shadow-md">
                    <img src="{{ $imageUrl }}" alt="{{ $item->judul }}" class="w-full h-72 object-cover">
                </div>
            @endif

            <article class="bg-white rounded-xl shadow-md p-8 prose prose-amber max-w-none">
                {!! $body !!}
            </article>

            <div class="mt-6 flex items-center justify-between">
                <a href="{{ $backRoute }}" class="inline-flex items-center text-amber-600 hover:text-amber-700 font-medium">
                    <i class="fa-solid fa-arrow-left mr-2"></i>
                    {{ $backLabel }}
                </a>
            </div>
        </div>

        <aside class="lg:col-span-1">
            @if($terkait->isNotEmpty())
                <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                    <h3 class="font-bold text-gray-900 mb-4 border-b pb-2">Konten Terkait</h3>
                    <div class="space-y-4">
                        @foreach($terkait as $art)
                            @php
                                $relatedImage = $art->gambar ?? null;
                                if ($relatedImage && !Str::startsWith($relatedImage, ['http://', 'https://', 'storage/', 'assets/', 'uploads/', '/'])) {
                                    $relatedImage = 'assets/uploads/konten/' . basename($relatedImage);
                                }
                            @endphp
                            <a href="{{ route($relatedRouteName, $art->slug) }}" class="flex space-x-3 group">
                                <div class="w-16 h-16 flex-shrink-0 rounded-lg overflow-hidden bg-gray-100">
                                    @if($relatedImage)
                                        <img src="{{ Str::startsWith($relatedImage, ['http://', 'https://']) ? $relatedImage : asset(ltrim($relatedImage, '/')) }}" alt="{{ $art->judul }}" class="w-full h-full object-cover group-hover:scale-105 transition">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                                            <i class="fa-regular fa-image text-xl"></i>
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900 group-hover:text-amber-600 transition line-clamp-2">{{ $art->judul }}</p>
                                    <p class="text-xs text-gray-400 mt-1">{{ \Carbon\Carbon::parse($art->created_at)->format('d M Y') }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="bg-amber-50 border border-amber-100 rounded-xl p-6 text-center">
                <div class="w-12 h-12 bg-amber-600 rounded-full flex items-center justify-center mx-auto mb-3 text-white">
                    <i class="fa-solid fa-plus"></i>
                </div>
                <h4 class="font-bold text-gray-900 mb-2">Butuh Layanan Sakramen?</h4>
                <p class="text-sm text-gray-600 mb-4">Ajukan permohonan sakramen secara online.</p>
                <a href="/sakramen" class="block bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-amber-700 transition">Ajukan Sekarang</a>
            </div>
        </aside>
    </div>
</div>
@endsection
