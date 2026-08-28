@extends('layouts.public')

@section('title', 'Renungan Harian & Santapan Rohani - ' . ($globalNamaParoki ?? 'SIPAROKI'))

@section('meta_description', 'Kumpulan renungan harian Katolik, refleksi bacaan Kitab Suci, dan doa penutup untuk menumbuhkan iman umat di ' . ($globalNamaParoki ?? 'SIPAROKI') . '.')

@section('content')
<!-- HERO SECTION -->
<section style="background: linear-gradient(135deg, rgba(0, 77, 64, 0.95), rgba(0, 137, 123, 0.88)), url('{{ $globalBanner ?? asset('assets/frontend/siparoki/images/gereja.jpg') }}') center/cover no-repeat; padding: 70px 0 60px; color: #ffffff; position: relative; overflow: hidden;">
    <div style="position: absolute; top: -50px; right: -50px; width: 220px; height: 220px; background: rgba(255, 152, 0, 0.15); border-radius: 50%; filter: blur(50px);"></div>
    <div class="container" style="position: relative; z-index: 2;">
        <div class="row justify-content-center text-center">
            <div class="col-lg-9">
                <span class="badge" style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px); color: #ffffff; padding: 8px 20px; border-radius: 30px; font-size: 0.85rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 18px; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-hands-praying" style="color: #ff9800;"></i> Santapan Rohani &amp; Sabda Allah
                </span>
                <h1 style="font-size: 2.8rem; font-weight: 900; letter-spacing: -0.5px; margin-bottom: 15px; color: #ffffff; text-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                    Renungan Harian
                </h1>
                <p style="font-size: 1.15rem; color: rgba(255,255,255,0.92); line-height: 1.6; max-width: 680px; margin: 0 auto 25px;">
                    Refleksi sabda Allah, santapan rohani harian, dan untaian doa untuk meneguhkan iman dan menyinari langkah hidup kita setiap hari.
                </p>
                <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.12); padding: 8px 22px; border-radius: 30px; backdrop-filter: blur(8px); font-size: 0.9rem;">
                    <a href="/" style="color: #ffffff; text-decoration: none; font-weight: 600;"><i class="fa-solid fa-house me-1"></i> Beranda</a>
                    <span style="opacity: 0.6;">/</span>
                    <a href="/warta" style="color: #ffffff; text-decoration: none; font-weight: 600;">Warta Paroki</a>
                    <span style="opacity: 0.6;">/</span>
                    <span style="color: #ffb74d; font-weight: 700;">Renungan Harian</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- MAIN CONTENT SECTION -->
<section style="background: #f8fafc; padding: 50px 0 80px;">
    <div class="container">

        <!-- FILTER PILL & SEARCH BAR -->
        <div style="background: #ffffff; border-radius: 20px; padding: 18px 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); border: 1px solid #e2e8f0; margin-bottom: 40px; border-left: 5px solid var(--primary-teal, #00897b);">
            <div class="row align-items-center g-3">
                <div class="col-lg-8 col-md-7">
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; align-items: center;">
                        <a href="/warta" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 25px; font-size: 0.84rem; font-weight: 700; text-decoration: none; background: #f1f5f9; color: #334155; transition: all 0.2s;">
                            <i class="fa-solid fa-shapes"></i> Semua Warta
                        </a>
                        <a href="/berita" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 25px; font-size: 0.84rem; font-weight: 700; text-decoration: none; background: #f1f5f9; color: #334155; transition: all 0.2s;">
                            <i class="fa-solid fa-newspaper"></i> Berita Paroki
                        </a>
                        <a href="/artikel" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 25px; font-size: 0.84rem; font-weight: 700; text-decoration: none; background: #f1f5f9; color: #334155; transition: all 0.2s;">
                            <i class="fa-solid fa-book-open"></i> Artikel &amp; Opini
                        </a>
                        <a href="/pengumuman" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 25px; font-size: 0.84rem; font-weight: 700; text-decoration: none; background: #f1f5f9; color: #334155; transition: all 0.2s;">
                            <i class="fa-solid fa-bullhorn"></i> Pengumuman
                        </a>
                        <a href="/renungan" style="display: inline-flex; align-items: center; gap: 6px; padding: 8px 18px; border-radius: 25px; font-size: 0.84rem; font-weight: 700; text-decoration: none; background: var(--primary-teal, #00897b); color: #ffffff; box-shadow: 0 4px 12px rgba(0,137,123,0.3); transition: all 0.2s;">
                            <i class="fa-solid fa-hands-praying"></i> Renungan
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-5">
                    <form action="/renungan" method="GET" style="margin: 0;">
                        <div style="position: relative; display: flex; align-items: center; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 30px; padding: 4px 6px 4px 16px; transition: all 0.25s ease; box-shadow: 0 2px 6px rgba(0,0,0,0.02);" onmouseover="this.style.borderColor='#cbd5e1';" onfocusin="this.style.borderColor='var(--primary-teal, #00897b)'; this.style.boxShadow='0 0 0 3px rgba(0,137,123,0.15)'; this.style.background='#ffffff';" onfocusout="this.style.borderColor='#e2e8f0'; this.style.boxShadow='0 2px 6px rgba(0,0,0,0.02)'; this.style.background='#f8fafc';">
                            <i class="fa-solid fa-magnifying-glass" style="color: #94a3b8; font-size: 0.9rem; margin-right: 10px; flex-shrink: 0;"></i>
                            <input type="text" name="search" placeholder="Cari renungan / ayat firman..." value="{{ $search ?? request('search') }}" style="border: none; outline: none; background: transparent; width: 100%; font-size: 0.88rem; color: #1e293b; font-weight: 500; padding: 6px 0;">
                            @if(!empty($search ?? request('search')))
                                <a href="/renungan" style="color: #94a3b8; text-decoration: none; padding: 0 8px; font-size: 0.95rem; transition: color 0.2s;" onmouseover="this.style.color='#ef4444';" onmouseout="this.style.color='#94a3b8';" title="Hapus Pencarian">
                                    <i class="fa-solid fa-circle-xmark"></i>
                                </a>
                            @endif
                            <button type="submit" style="background: linear-gradient(135deg, var(--primary-teal, #00897b), var(--dark-teal, #004d40)); color: #ffffff; border: none; border-radius: 25px; padding: 7px 18px; font-size: 0.82rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; flex-shrink: 0; transition: transform 0.15s, box-shadow 0.15s; box-shadow: 0 2px 8px rgba(0,137,123,0.25);" onmouseover="this.style.transform='scale(1.02)';" onmouseout="this.style.transform='none';">
                                <span>Cari</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if(!empty($search))
            <div class="alert alert-info d-flex justify-content-between align-items-center mb-4" style="border-radius: 14px; background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534;">
                <div>
                    <i class="fa-solid fa-magnifying-glass me-2 text-teal-600"></i> Menampilkan hasil pencarian renungan untuk: <strong>"{{ $search }}"</strong> ({{ $renungan->total() }} ditemukan)
                </div>
                <a href="/renungan" class="btn btn-sm btn-outline-success" style="border-radius: 20px; font-weight: 600;">Reset Filter</a>
            </div>
        @endif

        <!-- FEATURED RENUNGAN (PAGE 1 ONLY) -->
        @if(isset($featured) && $featured)
            @php
                $featDate = $featured->tanggal ?? $featured->created_at ?? now();
                $featImg = !empty($featured->gambar) 
                    ? (str_starts_with($featured->gambar, 'http') ? $featured->gambar : asset(ltrim($featured->gambar, '/')))
                    : asset('assets/frontend/siparoki/images/gereja.jpg');
            @endphp
            <div style="background: #ffffff; border-radius: 22px; overflow: hidden; box-shadow: 0 8px 30px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; margin-bottom: 45px; border-top: 5px solid var(--primary-orange, #ff9800);">
                <div class="row g-0 align-items-stretch">
                    <div class="col-lg-5" style="min-height: 280px; position: relative;">
                        <img src="{{ $featImg }}" alt="{{ $featured->judul }}" style="width: 100%; height: 100%; object-fit: cover; min-height: 280px;">
                        <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(0,0,0,0.5) 0%, transparent 60%);"></div>
                        <span class="badge" style="position: absolute; top: 16px; left: 16px; background: var(--primary-orange, #ff9800); color: #ffffff; padding: 7px 16px; border-radius: 20px; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; box-shadow: 0 4px 10px rgba(255,152,0,0.4);">
                            <i class="fa-solid fa-star me-1"></i> Renungan Hari Ini
                        </span>
                    </div>
                    <div class="col-lg-7 d-flex flex-column justify-content-between" style="padding: 35px 38px;">
                        <div>
                            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 12px;">
                                @if(!empty($featured->bacaan))
                                    <span style="background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); font-size: 0.82rem; font-weight: 700; padding: 4px 12px; border-radius: 12px;">
                                        <i class="fa-solid fa-book-bible me-1"></i> {{ $featured->bacaan }}
                                    </span>
                                @endif
                                <span style="color: #94a3b8; font-size: 0.85rem; font-weight: 600;">
                                    <i class="far fa-calendar-alt text-teal-600 me-1"></i> {{ format_tanggal_indonesia($featDate, true) }}
                                </span>
                            </div>
                            <h2 style="font-size: 1.65rem; font-weight: 800; color: #0f172a; line-height: 1.38; margin-bottom: 15px;">
                                <a href="/renungan/{{ $featured->slug ?? $featured->id }}" style="color: inherit; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary-teal, #00897b)'" onmouseout="this.style.color='#0f172a'">
                                    {{ $featured->judul }}
                                </a>
                            </h2>
                            <p style="color: #475569; font-size: 0.96rem; line-height: 1.65; margin-bottom: 20px; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ Str::limit(strip_tags($featured->isi_renungan ?? ''), 220) }}
                            </p>

                            @if(!empty($featured->doa_penutup))
                                <div style="background: #fffbeb; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 0 10px 10px 0; margin-bottom: 20px;">
                                    <span style="display: block; font-size: 0.75rem; font-weight: 800; color: #b45309; text-transform: uppercase; margin-bottom: 4px;">
                                        <i class="fa-solid fa-hands-praying me-1"></i> Doa Singkat:
                                    </span>
                                    <p style="font-size: 0.88rem; color: #92400e; font-style: italic; margin: 0; line-height: 1.45;">
                                        "{{ Str::limit($featured->doa_penutup, 140) }}"
                                    </p>
                                </div>
                            @endif
                        </div>

                        <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #f1f5f9; padding-top: 18px;">
                            <span style="font-size: 0.82rem; color: #94a3b8; font-weight: 600;">
                                <i class="fa-regular fa-eye me-1"></i> {{ number_format((int)($featured->views ?? 0), 0, ',', '.') }} pembaca
                            </span>
                            <a href="/renungan/{{ $featured->slug ?? $featured->id }}" class="btn" style="background: var(--primary-teal, #00897b); color: #ffffff; border-radius: 25px; padding: 8px 24px; font-weight: 700; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(0,137,123,0.3); transition: all 0.2s;">
                                Baca Renungan Lengkap <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- RENUNGAN GRID LIST -->
        <div class="row g-4 mb-5">
            @forelse($renungan as $item)
                @php
                    $itemDate = $item->tanggal ?? $item->created_at ?? now();
                    $itemImg = !empty($item->gambar) 
                        ? (str_starts_with($item->gambar, 'http') ? $item->gambar : asset(ltrim($item->gambar, '/')))
                        : null;
                @endphp
                <div class="col-lg-4 col-md-6">
                    <div style="background: #ffffff; border-radius: 18px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 4px 18px rgba(0,0,0,0.04); height: 100%; display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 25px rgba(0,0,0,0.08)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 18px rgba(0,0,0,0.04)';">
                        
                        @if($itemImg)
                            <div style="height: 180px; position: relative; overflow: hidden;">
                                <img src="{{ $itemImg }}" alt="{{ $item->judul }}" style="width: 100%; height: 100%; object-fit: cover;">
                                @if(!empty($item->bacaan))
                                    <span style="position: absolute; bottom: 12px; left: 12px; background: rgba(0, 77, 64, 0.85); backdrop-filter: blur(4px); color: #ffffff; font-size: 0.75rem; font-weight: 700; padding: 4px 12px; border-radius: 12px;">
                                        <i class="fa-solid fa-book-bible me-1"></i> {{ $item->bacaan }}
                                    </span>
                                @endif
                            </div>
                        @endif

                        <div style="padding: 24px; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 12px;">
                                    <span style="font-size: 0.8rem; font-weight: 600; color: #94a3b8;">
                                        <i class="far fa-calendar-alt text-teal-600 me-1"></i> {{ format_tanggal_indonesia($itemDate) }}
                                    </span>
                                    @if(empty($itemImg) && !empty($item->bacaan))
                                        <span style="background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); font-size: 0.75rem; font-weight: 700; padding: 3px 10px; border-radius: 10px;">
                                            {{ $item->bacaan }}
                                        </span>
                                    @endif
                                </div>

                                <h4 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; line-height: 1.45; margin-bottom: 12px;">
                                    <a href="/renungan/{{ $item->slug ?? $item->id }}" style="color: inherit; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary-teal, #00897b)'" onmouseout="this.style.color='#0f172a'">
                                        {{ $item->judul }}
                                    </a>
                                </h4>

                                <p style="color: #64748b; font-size: 0.88rem; line-height: 1.6; margin-bottom: 16px; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                    {{ Str::limit(strip_tags($item->isi_renungan ?? ''), 150) }}
                                </p>
                            </div>

                            <div style="display: flex; align-items: center; justify-content: space-between; border-top: 1px solid #f1f5f9; padding-top: 14px; margin-top: 10px;">
                                <span style="font-size: 0.78rem; color: #94a3b8; font-weight: 600;">
                                    <i class="fa-regular fa-eye me-1"></i> {{ (int)($item->views ?? 0) }}
                                </span>
                                <a href="/renungan/{{ $item->slug ?? $item->id }}" style="color: var(--primary-teal, #00897b); font-weight: 700; font-size: 0.85rem; text-decoration: none; display: inline-flex; align-items: center; gap: 5px;" onmouseover="this.style.color='var(--primary-orange, #ff9800)'" onmouseout="this.style.color='var(--primary-teal, #00897b)'">
                                    Baca Renungan <i class="fa-solid fa-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div style="background: #ffffff; border-radius: 20px; padding: 70px 20px; text-align: center; border: 1px dashed #cbd5e1;">
                        <div style="width: 70px; height: 70px; border-radius: 50%; background: #f1f5f9; color: #94a3b8; display: inline-flex; align-items: center; justify-content: center; font-size: 1.8rem; margin-bottom: 18px;">
                            <i class="fa-solid fa-hands-praying"></i>
                        </div>
                        <h4 style="font-weight: 800; color: #334155; margin-bottom: 8px;">Belum Ada Renungan</h4>
                        <p style="color: #64748b; font-size: 0.95rem; max-width: 450px; margin: 0 auto 20px;">
                            Belum ada publikasi renungan harian yang tersedia. Silakan kembali lagi nanti atau cari artikel rohani lainnya.
                        </p>
                        <a href="/warta" class="btn" style="background: var(--primary-teal, #00897b); color: #ffffff; border-radius: 25px; padding: 8px 24px; font-weight: 700;">
                            Kembali ke Warta Paroki
                        </a>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- KONOHA STYLE PAGINATION -->
        @if($renungan->hasPages())
            <div class="d-flex justify-content-center mt-5">
                <nav aria-label="Navigasi Halaman Renungan">
                    <ul style="display: inline-flex; align-items: center; gap: 8px; list-style: none; padding: 0; margin: 0; background: #ffffff; padding: 8px 16px; border-radius: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
                        
                        {{-- Previous Button --}}
                        @if ($renungan->onFirstPage())
                            <li>
                                <span style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; background: #f8fafc; cursor: not-allowed;">
                                    <i class="fa-solid fa-chevron-left"></i> Sebelumnya
                                </span>
                            </li>
                        @else
                            <li>
                                <a href="{{ $renungan->previousPageUrl() }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 700; color: #334155; background: #f1f5f9; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='var(--primary-teal, #00897b)'; this.style.color='#ffffff';" onmouseout="this.style.background='#f1f5f9'; this.style.color='#334155';">
                                    <i class="fa-solid fa-chevron-left"></i> Sebelumnya
                                </a>
                            </li>
                        @endif

                        {{-- Pagination Elements --}}
                        @foreach ($renungan->getUrlRange(1, $renungan->lastPage()) as $page => $url)
                            @if ($page == $renungan->currentPage())
                                <li>
                                    <span style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; font-size: 0.88rem; font-weight: 800; color: #ffffff; background: var(--primary-teal, #00897b); box-shadow: 0 4px 10px rgba(0,137,123,0.35);">
                                        {{ $page }}
                                    </span>
                                </li>
                            @else
                                <li>
                                    <a href="{{ $url }}" style="display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; font-size: 0.88rem; font-weight: 700; color: #475569; background: transparent; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='#e2e8f0'; this.style.color='#0f172a';" onmouseout="this.style.background='transparent'; this.style.color='#475569';">
                                        {{ $page }}
                                    </a>
                                </li>
                            @endif
                        @endforeach

                        {{-- Next Button --}}
                        @if ($renungan->hasMorePages())
                            <li>
                                <a href="{{ $renungan->nextPageUrl() }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 700; color: #334155; background: #f1f5f9; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='var(--primary-teal, #00897b)'; this.style.color='#ffffff';" onmouseout="this.style.background='#f1f5f9'; this.style.color='#334155';">
                                    Selanjutnya <i class="fa-solid fa-chevron-right"></i>
                                </a>
                            </li>
                        @else
                            <li>
                                <span style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; color: #cbd5e1; background: #f8fafc; cursor: not-allowed;">
                                    Selanjutnya <i class="fa-solid fa-chevron-right"></i>
                                </span>
                            </li>
                        @endif

                    </ul>
                </nav>
            </div>
        @endif

        <!-- Share Buttons -->
        <div style="margin-top: 35px;">
            @include('partials.share-buttons', ['title' => 'Renungan Harian & Pelita Sabda - ' . ($globalNamaParoki ?? 'Paroki')])
        </div>

    </div>
</section>
@endsection
