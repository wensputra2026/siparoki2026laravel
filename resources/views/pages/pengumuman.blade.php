@extends('layouts.app')

@section('title', 'Pengumuman Resmi - ' . ($globalNamaParoki ?? 'SIPAROKI'))

@section('content')
<!-- Page Header / Breadcrumb Konoha Style -->
<section class="page-header">
    <div class="container" style="max-width: 1180px; margin: 0 auto; padding: 0 20px;">
        <h1 style="font-size: 2.6rem; font-weight: 700; margin-bottom: 15px; color: #ffffff;">Warta & Pengumuman</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="display: inline-flex; list-style: none; padding: 0; margin: 0 auto; gap: 12px; background: transparent; justify-content: center; align-items: center;">
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/" style="color: white; text-decoration: none; font-weight: 500;">Beranda</a>
                </li>
                <li class="breadcrumb-item active" style="background: var(--primary-orange, #ff9800); color: white; padding: 6px 18px; border-radius: 25px; font-size: 0.85rem; font-weight: 600;">
                    Pengumuman
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- Content Section Konoha Style -->
<section class="content-section" style="padding: 60px 0 80px; background: #f4faf9;">
    <div class="container" style="max-width: 1000px; margin: 0 auto; padding: 0 20px;">
        
        <div style="display: flex; flex-direction: column; gap: 24px;">
            @forelse($pengumuman ?? [] as $item)
                @php
                    $pDate = $item->created_at ?? now();
                @endphp
                <div style="background: #ffffff; border-radius: 15px; border-left: 5px solid var(--primary-orange, #ff9800); padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.06); transition: transform 0.2s, box-shadow 0.2s;">
                    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 15px;">
                        <span style="display: inline-block; background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); padding: 4px 14px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">
                            <i class="fas fa-bullhorn" style="margin-right: 4px;"></i> {{ $item->kategori ?? 'PENGUMUMAN' }}
                        </span>
                        <span style="font-size: 0.82rem; color: #94a3b8; font-weight: 500;">
                            <i class="far fa-calendar-alt" style="color: #ff9800; margin-right: 4px;"></i> {{ \Carbon\Carbon::parse($pDate)->translatedFormat('l, d F Y') }}
                        </span>
                    </div>

                    <h3 style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 14px; line-height: 1.4;">
                        {{ $item->judul }}
                    </h3>

                    @if(!empty($item->ringkasan))
                        <p style="font-size: 0.92rem; color: #64748b; margin-bottom: 16px; font-style: italic;">
                            {{ $item->ringkasan }}
                        </p>
                    @endif

                    <div style="color: #334155; font-size: 0.95rem; line-height: 1.8; border-top: 1px solid #f1f5f9; padding-top: 16px;">
                        {!! $item->isi !!}
                    </div>
                </div>
            @empty
                <div style="background: #ffffff; border-radius: 15px; padding: 60px 20px; text-align: center; color: #94a3b8; box-shadow: 0 5px 20px rgba(0,0,0,0.06);">
                    <i class="fas fa-bullhorn" style="font-size: 3rem; margin-bottom: 15px; color: #cbd5e1; display: block;"></i>
                    <p style="font-size: 1.05rem; font-weight: 600; margin: 0;">Belum ada pengumuman warta terbaru saat ini.</p>
                </div>
            @endforelse
        </div>

        @if(isset($pengumuman) && method_exists($pengumuman, 'links'))
            <div style="margin-top: 45px; display: flex; justify-content: center;">
                {{ $pengumuman->links() }}
            </div>
        @endif

    </div>
</section>
@endsection
