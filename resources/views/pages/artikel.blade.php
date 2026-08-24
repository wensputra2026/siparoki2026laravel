@extends('layouts.app')

@section('title', 'Artikel & Warta - ' . ($globalNamaParoki ?? 'SIPAROKI'))

@section('content')
<!-- Page Header / Breadcrumb Konoha Style -->
<section class="page-header" style="background: linear-gradient(rgba(10, 30, 25, 0.75), rgba(10, 30, 25, 0.85)), url('{{ $globalHeroBg ?? '/assets/uploads/profil/hero_bg.jpg' }}') center/cover; padding: 90px 0 50px; color: white; text-align: center;">
    <div class="container" style="max-width: 1180px; margin: 0 auto; padding: 0 20px;">
        <h1 style="font-size: 2.6rem; font-weight: 700; margin-bottom: 15px; color: #ffffff;">Artikel & Katekese</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="display: inline-flex; list-style: none; padding: 0; margin: 0 auto; gap: 12px; background: transparent; justify-content: center; align-items: center;">
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/" style="color: white; text-decoration: none; font-weight: 500;">Beranda</a>
                </li>
                <li class="breadcrumb-item active" style="background: var(--primary-orange, #ff9800); color: white; padding: 6px 18px; border-radius: 25px; font-size: 0.85rem; font-weight: 600;">
                    Artikel
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- News/Article Section Konoha Style -->
<section class="news-section" style="padding: 60px 0 80px; background: #f4faf9;">
    <div class="container" style="max-width: 1180px; margin: 0 auto; padding: 0 20px;">
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 30px;">
            @forelse($artikel ?? [] as $item)
                @php
                    $img = $item->gambar ?? null;
                    $imgUrl = null;
                    if ($img) {
                        $base = basename(ltrim($img, '/'));
                        if (file_exists(public_path('uploads/konten/' . $base))) {
                            $imgUrl = asset('uploads/konten/' . $base);
                        } elseif (file_exists(public_path('assets/uploads/konten/' . $base))) {
                            $imgUrl = asset('assets/uploads/konten/' . $base);
                        } elseif (str_starts_with($img, 'http')) {
                            $imgUrl = $img;
                        } else {
                            $imgUrl = asset('uploads/konten/' . $base);
                        }
                    }
                    $date = $item->tanggal_publish ?? $item->created_at ?? now();
                @endphp
                <div class="news-card" style="background: #ffffff; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.06); display: flex; flex-direction: column; height: 100%; transition: transform 0.3s, box-shadow 0.3s;">
                    <div style="position: relative; overflow: hidden; height: 210px; background: #e2e8f0;">
                        @if($imgUrl)
                            <img src="{{ $imgUrl }}" alt="{{ $item->judul }}" class="news-img" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s;">
                        @else
                            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: #94a3b8; background: #f1f5f9;">
                                <i class="fas fa-book-open" style="font-size: 2.5rem;"></i>
                            </div>
                        @endif
                        <span class="news-category category-news" style="position: absolute; top: 15px; left: 15px; background: #5c6bc0; color: white; padding: 4px 14px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase;">
                            {{ $item->kategori ?? 'ARTIKEL' }}
                        </span>
                    </div>
                    <div style="padding: 24px; display: flex; flex-direction: column; flex-grow: 1;">
                        <span class="news-date" style="display: inline-flex; align-items: center; gap: 6px; color: var(--primary-orange, #ff9800); font-size: 0.8rem; font-weight: 600; margin-bottom: 10px;">
                            <i class="far fa-calendar-alt"></i> {{ \Carbon\Carbon::parse($date)->translatedFormat('d M Y') }}
                        </span>
                        <h4 class="card-title" style="font-size: 1.1rem; font-weight: 700; line-height: 1.45; margin-bottom: 10px; color: #1e293b;">
                            <a href="/artikel/{{ $item->slug }}" style="color: inherit; text-decoration: none;">
                                {{ $item->judul }}
                            </a>
                        </h4>
                        <p class="card-text" style="font-size: 0.88rem; color: #64748b; line-height: 1.6; margin-bottom: 20px; flex-grow: 1; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $item->ringkasan ?? $item->excerpt ?? Str::limit(strip_tags($item->konten ?? $item->isi ?? ''), 120) }}
                        </p>
                        <div>
                            <a href="/artikel/{{ $item->slug }}" class="btn-news" style="display: inline-flex; align-items: center; gap: 6px; color: var(--primary-teal, #00897b); font-weight: 700; font-size: 0.88rem; text-decoration: none; transition: transform 0.2s;">
                                Selengkapnya <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; background: #ffffff; border-radius: 15px; padding: 50px 20px; text-align: center; color: #94a3b8; box-shadow: 0 5px 20px rgba(0,0,0,0.06);">
                    <i class="far fa-folder-open" style="font-size: 3rem; margin-bottom: 15px; display: block;"></i>
                    <p style="font-size: 1rem; font-weight: 600;">Belum ada artikel yang dipublikasikan.</p>
                </div>
            @endforelse
        </div>

        @if(isset($artikel) && method_exists($artikel, 'links'))
            <div style="margin-top: 50px; display: flex; justify-content: center;">
                {{ $artikel->links() }}
            </div>
        @endif

    </div>
</section>
@endsection


