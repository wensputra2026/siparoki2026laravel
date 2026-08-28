@extends('layouts.app')

@section('title', (!empty($activeCategory) ? ('Warta: ' . $activeCategory . ' - ') : 'Warta Paroki - ') . ($globalNamaParoki ?? $nama_paroki ?? 'SIPAROKI'))
@section('description', 'Kumpulan warta paroki, berita gereja, artikel rohani, pengumuman pastoral, dan renungan iman Katolik di ' . ($globalNamaParoki ?? $nama_paroki ?? 'SIPAROKI'))

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

    $activeCatText = $activeCategory ?? request()->query('category') ?? request()->query('kategori') ?? '';
    $currentPage = ($berita ?? null) ? $berita->currentPage() : 1;
    $isFirstPage = $currentPage === 1 && empty($search) && empty($activeMonth) && empty($activeTag);
    $featuredItem = ($isFirstPage && ($berita ?? null) && $berita->count() > 0) ? $berita->first() : null;
    $gridItems = ($featuredItem && empty($activeCatText)) ? $berita->slice(1) : ($berita ?? collect());
@endphp

<!-- Page Header / Breadcrumb Konoha Style -->
<section class="page-header">
    <div class="container">
        <h1 style="font-size: 2.6rem; font-weight: 800; margin-bottom: 12px; color: #ffffff; letter-spacing: -0.5px;">
            {{ !empty($activeCatText) ? ('Warta: ' . $activeCatText) : 'Warta Paroki' }}
        </h1>
        <p style="font-size: 1rem; color: rgba(255,255,255,0.88); margin-bottom: 18px; max-width: 650px; margin-left: auto; margin-right: auto; line-height: 1.6;">
            Pusat informasi resmi, kabar pelayanan, artikel pastoral, pengumuman, dan renungan iman {{ $globalNamaParoki ?? $nama_paroki ?? 'Paroki' }}.
        </p>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="display: inline-flex; list-style: none; padding: 0; margin: 0 auto; gap: 8px; background: transparent; justify-content: center; align-items: center; flex-wrap: wrap;">
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/" style="color: white; text-decoration: none; font-weight: 500;">
                        <i class="fa-solid fa-house me-1" style="font-size: 0.75rem;"></i> Beranda
                    </a>
                </li>
                <li class="breadcrumb-separator" style="color: rgba(255,255,255,0.75); font-size: 0.7rem; display: inline-flex; align-items: center;">
                    <i class="fa-solid fa-chevron-right"></i>
                </li>
                <li class="breadcrumb-item {{ empty($activeCatText) ? 'active' : '' }}" style="{{ empty($activeCatText) ? 'background: var(--primary-orange, #ff9800); color: white; box-shadow: 0 4px 12px rgba(255,152,0,0.35);' : 'background: rgba(255,255,255,0.22); color: white;' }} padding: 6px 18px; border-radius: 25px; font-size: 0.85rem; font-weight: 700;">
                    @if(!empty($activeCatText))
                        <a href="/warta" style="color: white; text-decoration: none;">Warta Paroki</a>
                    @else
                        Warta Paroki
                    @endif
                </li>
                @if(!empty($activeCatText))
                    <li class="breadcrumb-separator" style="color: rgba(255,255,255,0.75); font-size: 0.7rem; display: inline-flex; align-items: center;">
                        <i class="fa-solid fa-chevron-right"></i>
                    </li>
                    <li class="breadcrumb-item active" style="background: var(--primary-orange, #ff9800); color: white; padding: 6px 18px; border-radius: 25px; font-size: 0.85rem; font-weight: 700; box-shadow: 0 4px 12px rgba(255,152,0,0.35);">
                        {{ $activeCatText }}
                    </li>
                @endif
            </ol>
        </nav>
    </div>
</section>

<!-- Content Section Konoha Style -->
<section class="content-section" style="padding: 45px 0 80px; background: #f4faf9; min-height: 70vh;">
    <div class="container">
        
        <!-- Unified Category Filter Bar -->
        <div style="background: #ffffff; border-radius: 22px; padding: 16px 24px; box-shadow: 0 6px 25px rgba(0,0,0,0.05); margin-bottom: 32px; border: 1px solid #e2e8f0; border-top: 4px solid var(--primary-teal, #00897b);">
            <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center; justify-content: flex-start;">
                
                <!-- Semua Warta -->
                <a href="/warta" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 25px; font-size: 0.84rem; font-weight: 700; text-decoration: none; transition: all 0.2s; {{ empty($activeCatText) && !request()->is('berita*') && !request()->is('artikel*') && !request()->is('pengumuman*') && !request()->is('renungan*') ? 'background: var(--primary-teal, #00897b); color: #ffffff; box-shadow: 0 4px 12px rgba(0,137,123,0.3);' : 'background: #f1f5f9; color: #334155;' }}">
                    <i class="fa-solid fa-border-all"></i> Semua Warta
                </a>

                <!-- Berita Paroki -->
                <a href="/berita" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 25px; font-size: 0.84rem; font-weight: 700; text-decoration: none; transition: all 0.2s; {{ request()->is('berita*') || strtolower($activeCatText) === 'berita' || strtolower($activeCatText) === 'berita paroki' ? 'background: var(--primary-teal, #00897b); color: #ffffff; box-shadow: 0 4px 12px rgba(0,137,123,0.3);' : 'background: #f1f5f9; color: #334155;' }}">
                    <i class="fa-regular fa-newspaper"></i> Berita Paroki
                </a>

                <!-- Artikel & Opini -->
                <a href="/artikel" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 25px; font-size: 0.84rem; font-weight: 700; text-decoration: none; transition: all 0.2s; {{ request()->is('artikel*') || strtolower($activeCatText) === 'artikel' || strtolower($activeCatText) === 'artikel rohani' ? 'background: var(--primary-teal, #00897b); color: #ffffff; box-shadow: 0 4px 12px rgba(0,137,123,0.3);' : 'background: #f1f5f9; color: #334155;' }}">
                    <i class="fa-solid fa-book-open"></i> Artikel &amp; Opini
                </a>

                <!-- Pengumuman -->
                <a href="/pengumuman" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 25px; font-size: 0.84rem; font-weight: 700; text-decoration: none; transition: all 0.2s; {{ request()->is('pengumuman*') || strtolower($activeCatText) === 'pengumuman' || strtolower($activeCatText) === 'pengumuman paroki' ? 'background: var(--primary-teal, #00897b); color: #ffffff; box-shadow: 0 4px 12px rgba(0,137,123,0.3);' : 'background: #f1f5f9; color: #334155;' }}">
                    <i class="fa-solid fa-bullhorn"></i> Pengumuman
                </a>

                <!-- Renungan -->
                <a href="/renungan" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 25px; font-size: 0.84rem; font-weight: 700; text-decoration: none; transition: all 0.2s; {{ request()->is('renungan*') || strtolower($activeCatText) === 'renungan' || strtolower($activeCatText) === 'renungan harian' ? 'background: var(--primary-teal, #00897b); color: #ffffff; box-shadow: 0 4px 12px rgba(0,137,123,0.3);' : 'background: #f1f5f9; color: #334155;' }}">
                    <i class="fa-solid fa-hands-praying"></i> Renungan
                </a>

                <!-- Dropdown Kategori Lengkap Lainnya -->
                @if(!empty($categories) && count($categories) > 0)
                    @php
                        $otherCats = collect($categories)->filter(function($k) {
                            $n = strtolower(is_object($k) ? ($k->kategori ?? $k->nama_kategori ?? '') : (string)$k);
                            return !in_array($n, ['berita', 'artikel', 'pengumuman', 'renungan'], true);
                        });
                    @endphp
                    @if($otherCats->isNotEmpty())
                        <div class="dropdown d-inline-block">
                            <button class="btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="background: #e6f4f1; color: var(--primary-teal, #00897b); border: 1.5px solid rgba(0,137,123,0.25); border-radius: 25px; font-size: 0.84rem; font-weight: 700; padding: 7px 16px;">
                                <i class="fa-solid fa-tags me-1"></i> Kategori Lainnya ({{ $otherCats->count() }})
                            </button>
                            <ul class="dropdown-menu shadow-lg border-0" style="max-height: 380px; overflow-y: auto; min-width: 270px; border-radius: 14px; padding: 8px;">
                                <li class="dropdown-header text-uppercase text-xs fw-bold text-muted">Daftar Kategori Paroki</li>
                                @foreach($otherCats as $cat)
                                    @php
                                        $catName = is_object($cat) ? ($cat->kategori ?? $cat->nama_kategori ?? '') : (string)$cat;
                                        $catTotal = is_object($cat) ? ($cat->total ?? 0) : 0;
                                        $catPretty = ucwords(strtolower(trim($catName)));
                                        $isSelected = strtolower($activeCatText) === strtolower($catName);
                                    @endphp
                                    @if(!empty($catName))
                                        <li>
                                            <a class="dropdown-item d-flex justify-content-between align-items-center {{ $isSelected ? 'active' : '' }}" href="/warta?category={{ urlencode($catName) }}" style="border-radius: 8px; font-size: 0.85rem; padding: 7px 12px;">
                                                <span><i class="fa-solid fa-tag me-2 text-slate-400"></i> {{ $catPretty }}</span>
                                                @if($catTotal > 0)
                                                    <span class="badge {{ $isSelected ? 'bg-white text-dark' : 'bg-light text-secondary' }} rounded-pill" style="font-size: 0.72rem;">{{ $catTotal }}</span>
                                                @endif
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endif

            </div>
        </div>

        @if(($berita ?? null) && $berita->total() > 0)
            
            <!-- FEATURED WARTA UTAMA (Page 1 Header Card) -->
            @if($featuredItem && empty($activeCatText))
                @php
                    $featPub = $featuredItem->tanggal_publish ?? $featuredItem->created_at ?? now();
                    $featExcerpt = $featuredItem->excerpt ?? Str::limit(strip_tags($featuredItem->isi ?? ''), 180);
                    $featCat = $featuredItem->kategori ?? $featuredItem->tipe ?? 'Warta Utama';
                    $featRoute = !empty($featuredItem->slug) ? route('berita.detail', $featuredItem->slug) : ('/berita/' . ($featuredItem->id ?? ''));
                @endphp
                <div style="background: #ffffff; border-radius: 22px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; margin-bottom: 35px;">
                    <div class="row g-0 align-items-center">
                        <div class="col-lg-6 col-md-12">
                            <a href="{{ $featRoute }}" style="display: block; position: relative; overflow: hidden; height: 320px; text-decoration: none;">
                                <span style="position: absolute; top: 16px; left: 16px; background: var(--primary-orange, #ff9800); color: #ffffff; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; padding: 6px 16px; border-radius: 20px; z-index: 2; box-shadow: 0 4px 14px rgba(255,152,0,0.4);">
                                    <i class="fa-solid fa-star me-1"></i> Warta Utama
                                </span>
                                <img src="{{ $imageUrl($featuredItem->gambar ?? null) }}" alt="{{ $featuredItem->judul }}" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease;" onmouseover="this.style.transform='scale(1.04)'" onmouseout="this.style.transform='scale(1)'">
                            </a>
                        </div>
                        <div class="col-lg-6 col-md-12 p-4 p-lg-5">
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                                <span style="background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); font-size: 0.75rem; font-weight: 800; padding: 4px 12px; border-radius: 12px; text-transform: uppercase;">
                                    <i class="fa-solid fa-tag me-1"></i> {{ $featCat }}
                                </span>
                                <span style="color: #94a3b8; font-size: 0.82rem; font-weight: 600;">
                                    <i class="far fa-calendar-alt text-teal-600 me-1"></i> {{ format_tanggal_indonesia($featPub) }}
                                </span>
                            </div>
                            <h3 style="font-size: 1.55rem; font-weight: 800; color: #0f172a; line-height: 1.38; margin: 0 0 14px;">
                                <a href="{{ $featRoute }}" style="color: inherit; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary-teal, #00897b)'" onmouseout="this.style.color='#0f172a'">
                                    {{ $featuredItem->judul }}
                                </a>
                            </h3>
                            <p style="font-size: 0.92rem; color: #64748b; line-height: 1.7; margin-bottom: 22px;">
                                {{ $featExcerpt }}
                            </p>
                            <a href="{{ $featRoute }}" style="display: inline-flex; align-items: center; gap: 8px; background: var(--primary-teal, #00897b); color: #ffffff; padding: 10px 22px; border-radius: 25px; font-weight: 700; font-size: 0.85rem; text-decoration: none; box-shadow: 0 4px 14px rgba(0,137,123,0.25); transition: all 0.2s;" onmouseover="this.style.background='#00796b'" onmouseout="this.style.background='var(--primary-teal, #00897b)'">
                                Baca Warta Lengkap <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Info Counter Bar -->
            <div style="margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;">
                    <i class="fas fa-layer-group text-teal-600 me-1"></i>
                    Menampilkan <strong style="color: var(--primary-orange, #ff9800);">{{ $berita->firstItem() }} - {{ $berita->lastItem() }}</strong> dari <strong style="color: var(--primary-teal, #00897b);">{{ $berita->total() }}</strong> warta
                </span>
                <span style="font-size: 0.85rem; color: #64748b; font-weight: 600;">
                    <i class="fas fa-bookmark text-amber-500 me-1"></i>
                    Halaman <strong style="color: var(--primary-orange, #ff9800);">{{ $berita->currentPage() }}</strong> dari <strong style="color: var(--primary-teal, #00897b);">{{ $berita->lastPage() }}</strong>
                </span>
            </div>

            <!-- News Grid -->
            <div class="row g-4">
                @foreach($gridItems as $item)
                    @php
                        $publishedAt = $item->tanggal_publish ?? $item->created_at ?? now();
                        $excerpt = $item->excerpt ?? Str::limit(strip_tags($item->isi ?? ''), 130);
                        $catRaw = strtolower($item->kategori ?? $item->tipe ?? 'berita');
                        $isPengumuman = str_contains($catRaw, 'pengum') || str_contains($catRaw, 'pengumuman');
                        $isRenungan = str_contains($catRaw, 'renung') || str_contains($catRaw, 'renungan');
                        $isArtikel = str_contains($catRaw, 'artikel');
                        
                        $catBadgeBg = $isPengumuman ? '#ef4444' : ($isRenungan ? '#8b5cf6' : ($isArtikel ? '#0284c7' : 'var(--primary-teal, #00897b)'));
                        $catIcon = $isPengumuman ? 'fa-bullhorn' : ($isRenungan ? 'fa-hands-praying' : ($isArtikel ? 'fa-book-open' : 'fa-newspaper'));
                        $detailRoute = !empty($item->slug) ? route('berita.detail', $item->slug) : ('/berita/' . ($item->id ?? ''));
                    @endphp
                    <div class="col-lg-4 col-md-6">
                        <div style="background: #ffffff; border-radius: 18px; overflow: hidden; box-shadow: 0 6px 22px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; height: 100%; display: flex; flex-direction: column; transition: transform 0.25s ease, box-shadow 0.25s ease;" onmouseover="this.style.transform='translateY(-6px)'; this.style.boxShadow='0 14px 30px rgba(0,0,0,0.1)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 6px 22px rgba(0,0,0,0.06)';">
                            
                            <!-- Card Image & Category Badge -->
                            <a href="{{ $detailRoute }}" style="display: block; position: relative; overflow: hidden; height: 210px; text-decoration: none;">
                                <span style="position: absolute; top: 14px; left: 14px; background: {{ $catBadgeBg }}; color: #ffffff; font-size: 0.72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; padding: 5px 12px; border-radius: 20px; z-index: 2; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
                                    <i class="fas {{ $catIcon }} me-1"></i>
                                    {{ $item->kategori ?? $item->tipe ?? 'Warta' }}
                                </span>
                                <img src="{{ $imageUrl($item->gambar ?? null) }}" alt="{{ $item->judul }}" loading="lazy" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.35s ease;" onmouseover="this.style.transform='scale(1.06)'" onmouseout="this.style.transform='scale(1)'">
                            </a>

                            <!-- Card Body -->
                            <div style="padding: 22px 24px; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between;">
                                <div>
                                    <span style="font-size: 0.78rem; font-weight: 600; color: #94a3b8; display: block; margin-bottom: 8px;">
                                        <i class="far fa-calendar-alt text-teal-600 me-1"></i> {{ format_tanggal_indonesia($publishedAt) }}
                                    </span>
                                    <h5 style="font-size: 1.12rem; font-weight: 800; color: #0f172a; line-height: 1.45; margin: 0 0 10px;">
                                        <a href="{{ $detailRoute }}" style="color: inherit; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary-teal, #00897b)'" onmouseout="this.style.color='#0f172a'">
                                            {{ $item->judul }}
                                        </a>
                                    </h5>
                                    <p style="font-size: 0.88rem; color: #64748b; line-height: 1.6; margin-bottom: 18px;">
                                        {{ $excerpt }}
                                    </p>
                                </div>

                                <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 14px; border-top: 1px solid #f1f5f9;">
                                    <a href="{{ $detailRoute }}" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.82rem; font-weight: 700; color: var(--primary-teal, #00897b); text-decoration: none; padding: 6px 14px; border-radius: 12px; background: rgba(0,137,123,0.08); transition: all 0.2s;" onmouseover="this.style.background='var(--primary-teal, #00897b)'; this.style.color='#ffffff';" onmouseout="this.style.background='rgba(0,137,123,0.08)'; this.style.color='var(--primary-teal, #00897b)';">
                                        Baca Selengkapnya <i class="fas fa-arrow-right text-xs"></i>
                                    </a>
                                    <span style="font-size: 0.76rem; color: #94a3b8; font-weight: 600;">
                                        <i class="fa-regular fa-clock me-1"></i> Warta Paroki
                                    </span>
                                </div>
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Konoha Styled Pagination with Next & Prev -->
            <div class="mt-5 d-flex justify-content-center">
                <nav aria-label="Warta Navigation">
                    <ul class="pagination pagination-konoha" style="display: flex; gap: 6px; align-items: center; list-style: none; padding: 0; margin: 0;">
                        
                        <!-- Previous Page Link -->
                        @if ($berita->onFirstPage())
                            <li class="page-item disabled" aria-disabled="true">
                                <span class="page-link" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 25px; border: 1.5px solid #e2e8f0; background: #f8fafc; color: #94a3b8; font-weight: 700; font-size: 0.85rem; cursor: not-allowed;">
                                    <i class="fa-solid fa-chevron-left"></i> Sebelumnya
                                </span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $berita->previousPageUrl() }}" rel="prev" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 25px; border: 1.5px solid var(--primary-teal, #00897b); background: #ffffff; color: var(--primary-teal, #00897b); font-weight: 700; font-size: 0.85rem; text-decoration: none; box-shadow: 0 4px 10px rgba(0,137,123,0.1); transition: all 0.2s;">
                                    <i class="fa-solid fa-chevron-left"></i> Sebelumnya
                                </a>
                            </li>
                        @endif

                        <!-- Pagination Number Elements -->
                        @for ($page = 1; $page <= $berita->lastPage(); $page++)
                            @if ($page == $berita->currentPage())
                                <li class="page-item active" aria-current="page">
                                    <span class="page-link" style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; background: var(--primary-teal, #00897b); color: #ffffff; font-weight: 800; font-size: 0.88rem; border: none; box-shadow: 0 4px 12px rgba(0,137,123,0.35);">
                                        {{ $page }}
                                    </span>
                                </li>
                            @elseif ($page == 1 || $page == $berita->lastPage() || abs($page - $berita->currentPage()) <= 2)
                                <li class="page-item">
                                    <a class="page-link" href="{{ $berita->url($page) }}" style="display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; background: #ffffff; color: #475569; font-weight: 700; font-size: 0.88rem; border: 1.5px solid #e2e8f0; text-decoration: none; transition: all 0.2s;">
                                        {{ $page }}
                                    </a>
                                </li>
                            @elseif ($page == 2 || $page == $berita->lastPage() - 1)
                                <li class="page-item disabled" aria-disabled="true">
                                    <span class="page-link" style="padding: 0 4px; border: none; background: transparent; color: #94a3b8; font-size: 1rem;">...</span>
                                </li>
                            @endif
                        @endfor

                        <!-- Next Page Link -->
                        @if ($berita->hasMorePages())
                            <li class="page-item">
                                <a class="page-link" href="{{ $berita->nextPageUrl() }}" rel="next" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 25px; border: 1.5px solid var(--primary-teal, #00897b); background: #ffffff; color: var(--primary-teal, #00897b); font-weight: 700; font-size: 0.85rem; text-decoration: none; box-shadow: 0 4px 10px rgba(0,137,123,0.1); transition: all 0.2s;">
                                    Selanjutnya <i class="fa-solid fa-chevron-right"></i>
                                </a>
                            </li>
                        @else
                            <li class="page-item disabled" aria-disabled="true">
                                <span class="page-link" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 25px; border: 1.5px solid #e2e8f0; background: #f8fafc; color: #94a3b8; font-weight: 700; font-size: 0.85rem; cursor: not-allowed;">
                                    Selanjutnya <i class="fa-solid fa-chevron-right"></i>
                                </span>
                            </li>
                        @endif

                    </ul>
                </nav>
            </div>

        @else
            <!-- Empty State -->
            <div style="background: #ffffff; border-radius: 22px; padding: 60px 20px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
                <div style="width: 70px; height: 70px; margin: 0 auto 16px; border-radius: 50%; background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); display: flex; align-items: center; justify-content: center; font-size: 1.8rem;">
                    <i class="fa-solid fa-newspaper"></i>
                </div>
                <h4 style="font-size: 1.25rem; font-weight: 800; color: #1e293b; margin-bottom: 6px;">Belum Ada Warta untuk Kategori "{{ $activeCatText }}"</h4>
                <p style="font-size: 0.9rem; color: #64748b; max-width: 450px; margin: 0 auto 20px;">
                    Saat ini belum ada artikel atau berita yang dipublikasikan untuk kategori ini. Silakan periksa kategori warta lainnya.
                </p>
                <a href="/warta" style="display: inline-flex; align-items: center; gap: 8px; background: var(--primary-teal, #00897b); color: #ffffff; padding: 10px 22px; border-radius: 20px; font-weight: 700; font-size: 0.85rem; text-decoration: none;">
                    <i class="fa-solid fa-arrow-left"></i> Lihat Semua Warta
                </a>
            </div>
        @endif

        <!-- Share Buttons -->
        <div style="margin-top: 35px;">
            @include('partials.share-buttons', ['title' => 'Warta & Berita Paroki - ' . ($globalNamaParoki ?? 'Paroki')])
        </div>

    </div>
</section>
@endsection
