@extends('layouts.app')
@section('title', 'Agenda Kegiatan Paroki - ' . ($globalNamaParoki ?? 'SIPAROKI'))

@section('content')
<!-- Page Header / Breadcrumb Konoha Style -->
<section class="page-header">
    <div class="container">
        <h1 style="font-size: 2.6rem; font-weight: 700; margin-bottom: 15px; color: #ffffff;">Agenda & Kegiatan</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="display: inline-flex; list-style: none; padding: 0; margin: 0 auto; gap: 12px; background: transparent; justify-content: center; align-items: center;">
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/" style="color: white; text-decoration: none; font-weight: 500;">Beranda</a>
                </li>
                <li class="breadcrumb-item active" style="background: var(--primary-orange, #ff9800); color: white; padding: 6px 18px; border-radius: 25px; font-size: 0.85rem; font-weight: 600;">
                    Agenda
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- Content Section Konoha Style -->
<section class="content-section" style="padding: 60px 0 80px; background: #f4faf9;">
    <div class="container">
        
        <div style="display: flex; flex-direction: column; gap: 20px;">
            @forelse($agenda ?? [] as $item)
                @php
                    $aDate = $item->tanggal_mulai ?? now();
                @endphp
                <div style="background: #ffffff; border-radius: 15px; border-left: 5px solid var(--primary-orange, #ff9800); padding: 25px 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.06); display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 20px; transition: transform 0.2s, box-shadow 0.2s;">
                    <div>
                        <h4 style="font-size: 1.15rem; font-weight: 700; color: #1e293b; margin-bottom: 8px;">
                            {{ $item->judul ?? $item->nama_kegiatan }}
                        </h4>
                        <p style="font-size: 0.88rem; color: #64748b; margin: 0; display: flex; align-items: center; gap: 6px;">
                            <i class="fas fa-map-marker-alt" style="color: var(--primary-teal, #00897b);"></i> 
                            {{ $item->lokasi ?? $item->tempat ?? 'Gereja Paroki' }}
                        </p>
                    </div>

                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 10px 20px; border-radius: 12px; text-align: center;">
                        <span style="display: block; font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase;">
                            {{ nama_hari_indonesia(\Carbon\Carbon::parse($aDate)->dayOfWeek) }}
                        </span>
                        <span style="font-size: 1.05rem; font-weight: 700; color: var(--primary-teal, #00897b);">
                            {{ format_tanggal_indonesia($aDate) }}
                        </span>
                    </div>
                </div>
            @empty
                <div style="background: #ffffff; border-radius: 15px; padding: 60px 20px; text-align: center; color: #94a3b8; box-shadow: 0 5px 20px rgba(0,0,0,0.06);">
                    <i class="far fa-calendar-times" style="font-size: 3rem; margin-bottom: 15px; display: block; color: #cbd5e1;"></i>
                    <p style="font-size: 1.05rem; font-weight: 600; margin: 0;">Belum ada agenda kegiatan yang terdaftar.</p>
                </div>
            @endforelse
        </div>

        @if(isset($agenda) && method_exists($agenda, 'links'))
            <div style="margin-top: 45px; display: flex; justify-content: center;">
                {{ $agenda->links() }}
            </div>
        @endif

    </div>
</section>
@endsection

