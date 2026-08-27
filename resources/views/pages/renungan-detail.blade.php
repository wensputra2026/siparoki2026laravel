@extends('layouts.public')

@section('title', $item->judul . ' - Renungan Harian ' . ($globalNamaParoki ?? 'SIPAROKI'))

@section('meta_description', Str::limit(strip_tags($item->isi_renungan ?? ''), 150))

@section('content')
@php
    $rDate = $item->tanggal ?? $item->created_at ?? now();
    $rImg = !empty($item->gambar) 
        ? (str_starts_with($item->gambar, 'http') ? $item->gambar : asset(ltrim($item->gambar, '/')))
        : null;
@endphp

<!-- HERO SECTION -->
<section style="background: linear-gradient(135deg, rgba(0, 77, 64, 0.95), rgba(0, 137, 123, 0.88)), url('{{ $globalBanner ?? asset('assets/frontend/siparoki/images/gereja.jpg') }}') center/cover no-repeat; padding: 65px 0 55px; color: #ffffff; position: relative; overflow: hidden;">
    <div style="position: absolute; top: -50px; right: -50px; width: 220px; height: 220px; background: rgba(255, 152, 0, 0.15); border-radius: 50%; filter: blur(50px);"></div>
    <div class="container" style="position: relative; z-index: 2;">
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center">
                <span class="badge" style="background: rgba(255, 255, 255, 0.2); backdrop-filter: blur(10px); color: #ffffff; padding: 7px 18px; border-radius: 30px; font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 16px; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-hands-praying" style="color: #ff9800;"></i> Renungan Harian Katolik
                </span>
                <h1 style="font-size: 2.3rem; font-weight: 900; letter-spacing: -0.5px; margin-bottom: 16px; color: #ffffff; line-height: 1.35; text-shadow: 0 4px 15px rgba(0,0,0,0.2);">
                    {{ $item->judul }}
                </h1>
                
                <div style="display: inline-flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 14px; background: rgba(255,255,255,0.12); padding: 8px 24px; border-radius: 30px; backdrop-filter: blur(8px); font-size: 0.88rem;">
                    <span><i class="far fa-calendar-alt text-warning me-1"></i> {{ format_tanggal_indonesia($rDate, true) }}</span>
                    @if(!empty($item->bacaan))
                        <span style="opacity: 0.6;">|</span>
                        <span><i class="fa-solid fa-book-bible text-warning me-1"></i> {{ $item->bacaan }}</span>
                    @endif
                    <span style="opacity: 0.6;">|</span>
                    <span><i class="fa-regular fa-eye text-warning me-1"></i> {{ number_format((int)($item->views ?? 0), 0, ',', '.') }} views</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- MAIN CONTENT SECTION -->
<section style="background: #f8fafc; padding: 50px 0 80px;">
    <div class="container">
        
        <!-- BREADCRUMB -->
        <div style="margin-bottom: 30px; display: flex; align-items: center; gap: 8px; font-size: 0.88rem; color: #64748b;">
            <a href="/" style="color: var(--primary-teal, #00897b); text-decoration: none; font-weight: 600;">Beranda</a>
            <span>/</span>
            <a href="/warta" style="color: var(--primary-teal, #00897b); text-decoration: none; font-weight: 600;">Warta Paroki</a>
            <span>/</span>
            <a href="/renungan" style="color: var(--primary-teal, #00897b); text-decoration: none; font-weight: 600;">Renungan</a>
            <span>/</span>
            <span style="color: #94a3b8; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $item->judul }}</span>
        </div>

        <div class="row g-4">
            <!-- LEFT MAIN ARTICLE -->
            <div class="col-lg-8">
                <article style="background: #ffffff; border-radius: 22px; padding: 35px 40px; box-shadow: 0 4px 25px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-top: 5px solid var(--primary-teal, #00897b);">
                    
                    <!-- BACAAN KITAB SUCI HIGHLIGHT BOX -->
                    @if(!empty($item->bacaan))
                        <div style="background: linear-gradient(135deg, rgba(0,137,123,0.08), rgba(255,152,0,0.08)); border: 1.5px solid rgba(0,137,123,0.2); border-radius: 16px; padding: 18px 24px; margin-bottom: 30px; display: flex; align-items: center; gap: 16px;">
                            <div style="width: 48px; height: 48px; border-radius: 12px; background: var(--primary-teal, #00897b); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0;">
                                <i class="fa-solid fa-book-bible"></i>
                            </div>
                            <div>
                                <span style="display: block; font-size: 0.78rem; font-weight: 800; text-transform: uppercase; color: var(--primary-teal, #00897b); letter-spacing: 0.5px;">
                                    Bacaan Kitab Suci Hari Ini
                                </span>
                                <h4 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 2px 0 0;">
                                    {{ $item->bacaan }}
                                </h4>
                            </div>
                        </div>
                    @endif

                    @if($rImg)
                        <div style="border-radius: 16px; overflow: hidden; margin-bottom: 30px; max-height: 400px;">
                            <img src="{{ $rImg }}" alt="{{ $item->judul }}" style="width: 100%; height: 100%; object-fit: cover;">
                        </div>
                    @endif

                    <!-- ISI RENUNGAN -->
                    <div class="renungan-content" style="font-size: 1.05rem; line-height: 1.85; color: #334155; margin-bottom: 35px;">
                        {!! $item->isi_renungan !!}
                    </div>

                    <!-- DOA PENUTUP BOX -->
                    @if(!empty($item->doa_penutup))
                        <div style="background: #fffbeb; border: 1.5px solid #fde68a; border-radius: 18px; padding: 26px 30px; margin-bottom: 35px; position: relative; overflow: hidden;">
                            <div style="position: absolute; right: -15px; bottom: -15px; font-size: 6rem; color: rgba(245,158,11,0.08); pointer-events: none;">
                                <i class="fa-solid fa-hands-praying"></i>
                            </div>
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 12px;">
                                <span style="width: 32px; height: 32px; border-radius: 50%; background: #f59e0b; color: #ffffff; display: inline-flex; align-items: center; justify-content: center; font-size: 0.9rem;">
                                    <i class="fa-solid fa-hands-praying"></i>
                                </span>
                                <h5 style="font-size: 1.05rem; font-weight: 800; color: #b45309; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">
                                    Doa Harian &amp; Penutup
                                </h5>
                            </div>
                            <p style="font-size: 1.02rem; color: #78350f; font-style: italic; line-height: 1.75; margin: 0;">
                                "{!! nl2br(e($item->doa_penutup)) !!}"
                            </p>
                        </div>
                    @endif

                    <!-- ACTIONS & NAV -->
                    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 15px; border-top: 1px solid #f1f5f9; padding-top: 25px;">
                        <a href="/renungan" class="btn" style="background: #f1f5f9; color: #334155; border-radius: 25px; padding: 8px 22px; font-weight: 700; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s;">
                            <i class="fa-solid fa-arrow-left"></i> Semua Renungan
                        </a>
                        <a href="/warta" class="btn" style="background: var(--primary-teal, #00897b); color: #ffffff; border-radius: 25px; padding: 8px 24px; font-weight: 700; font-size: 0.88rem; display: inline-flex; align-items: center; gap: 8px;">
                            <i class="fa-solid fa-shapes"></i> Warta Paroki
                        </a>
                    </div>

                </article>
            </div>

            <!-- RIGHT SIDEBAR -->
            <div class="col-lg-4">
                <div style="display: flex; flex-direction: column; gap: 24px;">

                    <!-- 1. KATA SAMBUTAN PASTOR PAROKI -->
                    <div style="background: #ffffff; padding: 24px; border-radius: 18px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-top: 4px solid var(--primary-orange, #ff9800); text-align: center;">
                        <div style="position: relative; width: 85px; height: 85px; margin: 0 auto 12px;">
                            <img src="{{ asset('images/pastor-avatar.jpg') }}" alt="Pastor Paroki" style="width: 85px; height: 85px; border-radius: 50%; object-fit: cover; border: 3px solid var(--primary-teal, #00897b); box-shadow: 0 4px 12px rgba(0,137,123,0.2);">
                            <span style="position: absolute; bottom: 0; right: 0; background: var(--primary-orange, #ff9800); color: #ffffff; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; font-size: 0.65rem; border: 2px solid #ffffff;">
                                <i class="fa-solid fa-cross"></i>
                            </span>
                        </div>
                        <span style="background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); font-size: 0.72rem; font-weight: 800; padding: 3px 12px; border-radius: 12px; text-transform: uppercase; display: inline-block; margin-bottom: 6px;">
                            Pastor Paroki
                        </span>
                        <h5 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0 0 2px;">
                            RD. Herman Hilers Penga
                        </h5>
                        <p style="color: #64748b; font-size: 0.8rem; margin: 0 0 12px;">
                            Pastor Paroki St. Vinsensius a Paulo - Benlutu
                        </p>
                        <p style="color: #475569; font-size: 0.85rem; font-style: italic; line-height: 1.5; margin: 0 0 14px;">
                            "Salve, Salam Sehat dan Berkah Dalem. Selamat Datang di Website Resmi St. Vinsensius a Paulo - Benlutu."
                        </p>
                        <a href="/sambutan" class="btn btn-sm w-100" style="background: linear-gradient(135deg, var(--primary-teal, #00897b), var(--dark-teal, #004d40)); color: #ffffff; border-radius: 20px; font-weight: 700; font-size: 0.82rem; padding: 7px 14px;">
                            Baca Selengkapnya <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>

                    <!-- 2. RENUNGAN LAINNYA -->
                    @if(isset($terkait) && $terkait->isNotEmpty())
                        <div style="background: #ffffff; padding: 24px; border-radius: 18px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-left: 4px solid var(--primary-teal, #00897b);">
                            <h4 style="font-size: 1.05rem; font-weight: 800; color: var(--primary-teal, #00897b); margin-bottom: 18px; display: flex; align-items: center; gap: 8px;">
                                <i class="fa-solid fa-hands-praying" style="color: #ff9800;"></i> Santapan Rohani Lainnya
                            </h4>
                            <div style="display: flex; flex-direction: column; gap: 14px;">
                                @foreach($terkait as $trk)
                                    @php
                                        $tDate = $trk->tanggal ?? $trk->created_at ?? now();
                                    @endphp
                                    <div style="background: #f8fafc; border-radius: 12px; padding: 12px 14px; transition: transform 0.2s;" onmouseover="this.style.transform='translateX(4px)';" onmouseout="this.style.transform='none';">
                                        <a href="/renungan/{{ $trk->slug ?? $trk->id }}" style="text-decoration: none;">
                                            <h6 style="color: #0f172a; font-weight: 700; font-size: 0.88rem; line-height: 1.45; margin: 0 0 6px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                                {{ $trk->judul }}
                                            </h6>
                                        </a>
                                        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.75rem; color: #94a3b8;">
                                            <span><i class="far fa-calendar-alt me-1 text-teal-600"></i> {{ format_tanggal_indonesia($tDate) }}</span>
                                            @if(!empty($trk->bacaan))
                                                <span style="color: var(--primary-teal, #00897b); font-weight: 700;">{{ $trk->bacaan }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- 3. QUICK LINKS -->
                    <div style="background: #ffffff; padding: 24px; border-radius: 18px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-left: 4px solid var(--primary-orange, #ff9800);">
                        <h4 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 16px;">
                            Layanan &amp; Informasi
                        </h4>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <a href="/jadwal-misa" style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 10px; background: #f8fafc; text-decoration: none; color: #334155; font-size: 0.86rem; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='rgba(0,137,123,0.08)'; this.style.color='var(--primary-teal, #00897b)';" onmouseout="this.style.background='#f8fafc'; this.style.color='#334155';">
                                <i class="fa-solid fa-clock text-teal-600"></i> Jadwal Misa &amp; Sakramen
                            </a>
                            <a href="/warta" style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 10px; background: #f8fafc; text-decoration: none; color: #334155; font-size: 0.86rem; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='rgba(0,137,123,0.08)'; this.style.color='var(--primary-teal, #00897b)';" onmouseout="this.style.background='#f8fafc'; this.style.color='#334155';">
                                <i class="fa-solid fa-newspaper text-warning"></i> Warta Paroki Terkini
                            </a>
                            <a href="/pengumuman" style="display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-radius: 10px; background: #f8fafc; text-decoration: none; color: #334155; font-size: 0.86rem; font-weight: 600; transition: all 0.2s;" onmouseover="this.style.background='rgba(0,137,123,0.08)'; this.style.color='var(--primary-teal, #00897b)';" onmouseout="this.style.background='#f8fafc'; this.style.color='#334155';">
                                <i class="fa-solid fa-bullhorn text-teal-600"></i> Pengumuman Pastoral
                            </a>
                        </div>
                    </div>

                </div>
            </div>

        </div>

    </div>
</section>
@endsection
