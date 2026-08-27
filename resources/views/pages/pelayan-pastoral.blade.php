@extends('layouts.app')
@section('title', 'Pelayan Pastoral - ' . ($globalNamaParoki ?? 'St. Vinsensius a Paulo Benlutu'))

@section('content')
@php
    $imamImage = asset('images/avatar-default.jpg');
@endphp

<!-- Page Header / Breadcrumb Konoha Style -->
<section class="page-header">
    <div class="container">
        <h1 style="font-size: 2.6rem; font-weight: 700; margin-bottom: 15px; color: #ffffff;">Pelayan Pastoral</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="display: inline-flex; list-style: none; padding: 0; margin: 0 auto; gap: 8px; background: transparent; justify-content: center; align-items: center; flex-wrap: wrap;">
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/" style="color: white; text-decoration: none; font-weight: 500;"><i class="fa-solid fa-house me-1" style="font-size: 0.75rem;"></i> Beranda</a>
                </li>
                <li class="breadcrumb-separator" style="color: rgba(255,255,255,0.75); font-size: 0.7rem; display: inline-flex; align-items: center;">
                    <i class="fa-solid fa-chevron-right"></i>
                </li>
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/profil" style="color: white; text-decoration: none; font-weight: 500;">Profil</a>
                </li>
                <li class="breadcrumb-separator" style="color: rgba(255,255,255,0.75); font-size: 0.7rem; display: inline-flex; align-items: center;">
                    <i class="fa-solid fa-chevron-right"></i>
                </li>
                <li class="breadcrumb-item active" style="background: var(--primary-orange, #ff9800); color: white; padding: 6px 18px; border-radius: 25px; font-size: 0.85rem; font-weight: 700;">
                    Pelayan Pastoral
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- Content Section -->
<section class="content-section" style="padding: 60px 0 80px; background: #f4faf9;">
    <div class="container">
        
        {{-- Section 1: Pelayan Pastoral Yang Bertugas Saat Ini --}}
        <div style="background: #ffffff; border-radius: 20px; padding: 35px 40px; box-shadow: 0 5px 20px rgba(0,0,0,0.06); margin-bottom: 40px;">
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 15px; margin-bottom: 28px; border-bottom: 2px solid #f1f5f9; padding-bottom: 18px;">
                <div>
                    <span style="display: inline-block; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-orange, #ff9800); margin-bottom: 4px;">
                        <i class="fas fa-users-rays me-1"></i> Reksa Pastoral
                    </span>
                    <h3 style="font-size: 1.4rem; font-weight: 800; color: #1e293b; margin: 0;">
                        Pelayan Pastoral Yang Bertugas Saat Ini
                    </h3>
                </div>
                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="/riwayat-pastor" style="display: inline-flex; align-items: center; gap: 6px; background: #f8fafc; border: 1px solid #cbd5e1; color: #334155; font-size: 0.82rem; font-weight: 600; padding: 6px 16px; border-radius: 20px; text-decoration: none;">
                        <i class="fa-solid fa-clock-rotate-left text-amber-500"></i> Riwayat Pastor
                    </a>
                    <a href="/struktur" style="display: inline-flex; align-items: center; gap: 6px; background: #f8fafc; border: 1px solid #cbd5e1; color: #334155; font-size: 0.82rem; font-weight: 600; padding: 6px 16px; border-radius: 20px; text-decoration: none;">
                        <i class="fa-solid fa-sitemap text-teal-600"></i> Struktur DPP
                    </a>
                </div>
            </div>

            <div class="row g-4">
                @if(isset($pastorBertugas) && count($pastorBertugas) > 0)
                    @foreach($pastorBertugas as $p)
                        @php
                            $isKepala = stripos($p->jabatan ?? '', 'Paroki') !== false && stripos($p->jabatan ?? '', 'Rekan') === false;
                            $borderCol = $isKepala ? '#ff9800' : '#0284c7';
                            $badgeBg = $isKepala ? 'rgba(255,152,0,0.12)' : 'rgba(2,132,199,0.12)';
                            $badgeText = $isKepala ? '#d97706' : '#0369a1';
                            $pFoto = !empty($p->foto) ? (str_starts_with($p->foto, 'http') || str_starts_with($p->foto, '/') ? $p->foto : '/' . $p->foto) : $imamImage;
                        @endphp
                        <div class="col-lg-4 col-md-6">
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-top: 4px solid {{ $borderCol }}; border-radius: 20px; padding: 28px 22px; text-align: center; height: 100%; box-shadow: 0 4px 14px rgba(0,0,0,0.04); display: flex; flex-direction: column; align-items: center; justify-content: space-between;">
                                <div style="width: 100%; display: flex; flex-direction: column; align-items: center;">
                                    <div style="width: 96px; height: 96px; margin: 0 auto 16px; border-radius: 20px; overflow: hidden; border: 2.5px solid {{ $borderCol }}; padding: 2px; background: #ffffff; box-shadow: 0 4px 10px rgba(0,0,0,0.06);">
                                        <img src="{{ $pFoto }}" alt="{{ $p->nama_formatted }}" style="width: 100%; height: 100%; border-radius: 16px; object-fit: cover;">
                                    </div>
                                    <span style="display: inline-block; background: {{ $badgeBg }}; color: {{ $badgeText }}; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; padding: 3px 12px; border-radius: 12px; margin-bottom: 8px;">
                                        {{ $p->jabatan ?? 'Pastor' }}
                                    </span>
                                    <h4 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin-bottom: 6px;">
                                        {{ $p->nama_formatted }}
                                    </h4>
                                    <p style="font-size: 0.84rem; font-weight: 600; color: {{ $isKepala ? '#059669' : '#0284c7' }}; margin: 0;">
                                        {{ $p->jabatan }}
                                    </p>
                                </div>

                                <div style="margin-top: 20px; padding-top: 15px; border-top: 1px dashed #e2e8f0; width: 100%;">
                                    <button 
                                        type="button" 
                                        onclick="openPastorDetailModal({{ json_encode($p) }})"
                                        style="width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 7px; background: #f8fafc; border: 1px solid #cbd5e1; color: #334155; font-size: 0.82rem; font-weight: 700; padding: 9px 16px; border-radius: 14px; transition: all 0.2s; cursor: pointer;"
                                        onmouseover="this.style.background='{{ $borderCol }}'; this.style.color='#ffffff'; this.style.borderColor='{{ $borderCol }}';"
                                        onmouseout="this.style.background='#f8fafc'; this.style.color='#334155'; this.style.borderColor='#cbd5e1';"
                                    >
                                        <i class="fa-solid fa-circle-info text-xs"></i>
                                        <span>Selengkapnya</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Frater / Katekis Card (Dinamis dari Database jika ada) --}}
                    @if(!empty($frater) || !empty($frater_obj))
                        @php
                            $fraterName = $frater ?? (isset($frater_obj->nama_pastor) ? \App\Models\MasterPastor::formatNama($frater_obj) : ($frater_obj->nama_frater ?? $frater_obj->nama_lengkap ?? 'Frater'));
                            $fraterJabatan = $frater_obj->jabatan ?? 'Frater / Katekis';
                            $fraterFoto = !empty($frater_obj?->foto) ? (str_starts_with($frater_obj->foto, 'http') || str_starts_with($frater_obj->foto, '/') ? $frater_obj->foto : '/' . $frater_obj->foto) : null;
                        @endphp
                        <div class="col-lg-4 col-md-6">
                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-top: 4px solid #0d9488; border-radius: 20px; padding: 28px 22px; text-align: center; height: 100%; box-shadow: 0 4px 14px rgba(0,0,0,0.04); display: flex; flex-direction: column; align-items: center; justify-content: space-between;">
                                <div style="width: 100%; display: flex; flex-direction: column; align-items: center;">
                                    <div style="width: 96px; height: 96px; margin: 0 auto 16px; border-radius: 20px; overflow: hidden; border: 2.5px solid #0d9488; padding: 2px; background: #ffffff; box-shadow: 0 4px 10px rgba(0,0,0,0.06); display: flex; align-items: center; justify-content: center;">
                                        @if($fraterFoto)
                                            <img src="{{ $fraterFoto }}" alt="{{ $fraterName }}" style="width: 100%; height: 100%; border-radius: 16px; object-fit: cover;">
                                        @else
                                            <div style="width: 100%; height: 100%; border-radius: 16px; background: #ccfbf1; color: #0d9488; display: flex; align-items: center; justify-content: center; font-size: 2.2rem;">
                                                <i class="fa-solid fa-book-bible"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <span style="display: inline-block; background: rgba(13,148,136,0.12); color: #0f766e; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; padding: 3px 12px; border-radius: 12px; margin-bottom: 8px;">
                                        {{ $fraterJabatan }}
                                    </span>
                                    <h4 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin-bottom: 6px;">
                                        {{ $fraterName }}
                                    </h4>
                                    <p style="font-size: 0.84rem; font-weight: 600; color: #0d9488; margin: 0;">
                                        {{ $frater_obj->catatan_pelayanan ?? $fraterJabatan }}
                                    </p>
                                </div>

                                @if(!empty($frater_obj))
                                <div style="margin-top: 20px; padding-top: 15px; border-top: 1px dashed #e2e8f0; width: 100%;">
                                    <button 
                                        type="button" 
                                        onclick="openPastorDetailModal({{ json_encode($frater_obj) }})"
                                        style="width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 7px; background: #f8fafc; border: 1px solid #cbd5e1; color: #334155; font-size: 0.82rem; font-weight: 700; padding: 9px 16px; border-radius: 14px; transition: all 0.2s; cursor: pointer;"
                                        onmouseover="this.style.background='#0d9488'; this.style.color='#ffffff'; this.style.borderColor='#0d9488';"
                                        onmouseout="this.style.background='#f8fafc'; this.style.color='#334155'; this.style.borderColor='#cbd5e1';"
                                    >
                                        <i class="fa-solid fa-circle-info text-xs"></i>
                                        <span>Selengkapnya</span>
                                    </button>
                                </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @else
                    {{-- Fallback Cards --}}
                    <div class="col-lg-4 col-md-6">
                        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-top: 4px solid #ff9800; border-radius: 20px; padding: 28px 22px; text-align: center; height: 100%; box-shadow: 0 4px 14px rgba(0,0,0,0.04); display: flex; flex-direction: column; align-items: center; justify-content: space-between;">
                            <div style="width: 100%; display: flex; flex-direction: column; align-items: center;">
                                <div style="width: 96px; height: 96px; margin: 0 auto 16px; border-radius: 20px; overflow: hidden; border: 2.5px solid #ff9800; padding: 2px; background: #ffffff;">
                                    <img src="{{ !empty($pastor_foto) ? $pastor_foto : $imamImage }}" alt="Pastor Paroki" style="width: 100%; height: 100%; border-radius: 16px; object-fit: cover;">
                                </div>
                                <span style="display: inline-block; background: rgba(255,152,0,0.12); color: #d97706; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; padding: 3px 12px; border-radius: 12px; margin-bottom: 8px;">
                                    Pastor Paroki
                                </span>
                                <h4 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin-bottom: 6px;">
                                    {{ $pastor_paroki ?? 'Data Pastor Paroki Belum Tersedia' }}
                                </h4>
                                <p style="font-size: 0.84rem; font-weight: 600; color: #059669; margin: 0;">
                                    Pastor Paroki
                                </p>
                            </div>
                            <div style="margin-top: 20px; padding-top: 15px; border-top: 1px dashed #e2e8f0; width: 100%;">
                                <button 
                                    type="button" 
                                    onclick="openPastorDetailModal({{ json_encode($pastor_paroki_obj ?? ['nama_pastor' => $pastor_paroki ?? 'Data Pastor Paroki Belum Tersedia', 'jabatan' => 'Pastor Paroki']) }})"
                                    style="width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 7px; background: #f8fafc; border: 1px solid #cbd5e1; color: #334155; font-size: 0.82rem; font-weight: 700; padding: 9px 16px; border-radius: 14px; transition: all 0.2s; cursor: pointer;"
                                >
                                    <i class="fa-solid fa-circle-info text-xs"></i>
                                    <span>Selengkapnya</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>
</section>

@include('partials.pastor-detail-modal')

@endsection
