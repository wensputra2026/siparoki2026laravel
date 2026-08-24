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
            $imageUrl = Str::startsWith($cleanImagePath, ['storage/', 'assets/', 'uploads/'])
                ? asset($cleanImagePath)
                : asset('assets/uploads/pengumuman/' . basename($cleanImagePath));
        }
    } elseif (!empty($globalLogo)) {
        $imageUrl = Str::startsWith($globalLogo, ['http://', 'https://']) ? $globalLogo : url($globalLogo);
    }
@endphp

@section('title', $item->judul . ' - ' . ($globalNamaParoki ?? 'SIPAROKI'))
@section('description', $description)
@section('keywords', trim(($item->kategori ?? '') . ', pengumuman, paroki, gereja katolik'))
@section('og_type', 'article')
@section('image', $imageUrl)
@section('article_meta')
    <meta property="article:published_time" content="{{ \Carbon\Carbon::parse($item->created_at ?? now())->toIso8601String() }}">
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
            'datePublished' => \Carbon\Carbon::parse($item->created_at ?? now())->toIso8601String(),
            'dateModified' => \Carbon\Carbon::parse($item->updated_at ?? $item->created_at ?? now())->toIso8601String(),
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
            <span class="page-banner-badge"><i class="fas fa-bullhorn"></i> {{ $item->kategori }}</span>
        @endif
        <h1>{{ $item->judul }}</h1>
        <p>{{ \Carbon\Carbon::parse($item->created_at ?? now())->translatedFormat('l, d F Y') }}</p>
    </div>
</section>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    @if($imageUrl)
        <div class="mb-6 rounded-xl overflow-hidden shadow-md">
            <img src="{{ $imageUrl }}" alt="{{ $item->judul }}" class="w-full h-72 object-cover">
        </div>
    @endif

    <article class="bg-white rounded-xl shadow-md p-8">
        <div class="prose prose-amber max-w-none">
            {!! nl2br(e($item->isi)) !!}
        </div>
    </article>

    <div class="mt-6">
        <a href="/pengumuman" class="inline-flex items-center text-amber-600 hover:text-amber-700 font-medium">
            <i class="fa-solid fa-arrow-left mr-2"></i>
            Kembali ke Pengumuman
        </a>
    </div>
</div>
@endsection
