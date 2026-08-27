@extends('layouts.app')
@section('title', 'Riwayat Pastor Paroki - ' . ($globalNamaParoki ?? 'St. Vinsensius a Paulo Benlutu'))

@section('content')
@php
    $defaultAvatar = asset('assets/frontend/siparoki/images/default-pastor.jpg');
@endphp

<!-- Page Header / Breadcrumb Konoha Style -->
<section class="page-header">
    <div class="container">
        <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 12px; color: #ffffff;">Riwayat Pastor Paroki</h1>
        <p style="font-size: 1rem; color: rgba(255,255,255,0.85); margin-bottom: 18px; max-width: 680px; margin-left: auto; margin-right: auto;">
            Daftar imam dan gembala umat yang telah dan sedang menggembalakan umat beriman di Paroki St. Vinsensius a Paulo Benlutu dari masa ke masa.
        </p>
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
                    Riwayat Pastor
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- Content Section Konoha Style -->
<section class="content-section" style="padding: 50px 0 80px; background: #f4faf9;">
    <div class="container">
        
        <!-- Header Info Bar -->
        <div style="background: #ffffff; border-radius: 20px; padding: 28px 32px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom: 35px; border: 1px solid #e2e8f0; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 18px;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 52px; height: 52px; border-radius: 16px; background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                    <i class="fa-solid fa-cross"></i>
                </div>
                <div>
                    <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-orange, #ff9800);">
                        Suksesi Gembala Paroki
                    </span>
                    <h3 style="font-size: 1.3rem; font-weight: 800; color: #0f172a; margin: 2px 0 0;">
                        Pastor Paroki dari Masa ke Masa
                    </h3>
                </div>
            </div>
        </div>

        <!-- Pastor Timeline Cards -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            @forelse($riwayat ?? [] as $r)
                @php
                    $fotoName = $r->foto ?? '';
                    if (empty($fotoName) && !empty($r->pastor_id)) {
                        $mp = \App\Models\MasterPastor::find($r->pastor_id);
                        $fotoName = $mp?->foto ?? '';
                    }

                    if (!empty($fotoName)) {
                        if (str_starts_with($fotoName, 'http')) {
                            $fotoUrl = $fotoName;
                        } elseif (str_contains($fotoName, '/')) {
                            $fotoUrl = asset($fotoName);
                        } else {
                            $fotoUrl = url('/foto-pastor/' . $fotoName);
                        }
                    } else {
                        $fotoUrl = $defaultAvatar;
                    }

                    $statusStr = $r->status_pelayanan ?? $r->status ?? 'Mantan';
                    $isAktif = strtolower($statusStr) === 'aktif';
                    $jabatanStr = $r->jabatan ?? 'Pastor Paroki';
                    $periodeStr = ($r->periode_mulai ?? $r->tahun_mulai ?? '-') . ' — ' . ($r->periode_selesai ?? $r->tahun_selesai ?? ($isAktif ? 'Sekarang' : '-'));
                @endphp

                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 18px; padding: 24px 28px; box-shadow: 0 4px 18px rgba(0,0,0,0.04); border-left: 5px solid {{ $isAktif ? 'var(--primary-teal, #00897b)' : '#cbd5e1' }}; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 20px; transition: transform 0.2s, box-shadow 0.2s;">
                    
                    <!-- Left: Avatar + Details -->
                    <div style="display: flex; align-items: center; gap: 20px; flex: 1; min-width: 280px;">
                        
                        <!-- Foto Avatar -->
                        <div style="width: 72px; height: 72px; border-radius: 50%; overflow: hidden; border: 3px solid {{ $isAktif ? '#ccfbf1' : '#e2e8f0' }}; padding: 2px; background: #ffffff; flex-shrink: 0; box-shadow: 0 3px 10px rgba(0,0,0,0.08);">
                            <img 
                                src="{{ $fotoUrl }}" 
                                alt="{{ $r->nama_lengkap_gelar ?? \App\Models\MasterPastor::formatNama($r) }}" 
                                style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%; display: block;"
                                onerror="this.onerror=null; this.src='{{ $defaultAvatar }}';"
                            >
                        </div>

                        <div>
                            <!-- Jabatan Badge -->
                            <span style="display: inline-block; background: {{ $isAktif ? 'rgba(0,137,123,0.1)' : 'rgba(100,116,139,0.1)' }}; color: {{ $isAktif ? 'var(--primary-teal, #00897b)' : '#475569' }}; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; padding: 3px 10px; border-radius: 8px; margin-bottom: 4px;">
                                <i class="fa-solid fa-cross me-1"></i> {{ $jabatanStr }}
                            </span>

                            <!-- Pastor Name -->
                            <h4 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0 0 4px;">
                                {{ $r->nama_lengkap_gelar ?? \App\Models\MasterPastor::formatNama($r) }}
                            </h4>

                            @if(!empty($r->catatan_pelayanan) || !empty($r->biografi_singkat))
                                <p style="font-size: 0.8rem; color: #64748b; margin: 6px 0 0; line-height: 1.5; max-width: 600px;">
                                    {{ Str::limit($r->catatan_pelayanan ?? $r->biografi_singkat, 140) }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Right: Periode & Status Badge & Action -->
                    <div style="text-align: right; min-width: 180px; display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                        <span style="display: inline-block; background: var(--primary-orange, #ff9800); color: #ffffff; padding: 6px 18px; border-radius: 20px; font-size: 0.82rem; font-weight: 800; letter-spacing: 0.3px; box-shadow: 0 2px 8px rgba(255,152,0,0.25);">
                            <i class="fa-regular fa-calendar-check me-1"></i> {{ $periodeStr }}
                        </span>
                        
                        <div style="font-size: 0.78rem; font-weight: 700; color: {{ $isAktif ? '#10b981' : '#64748b' }}; display: flex; align-items: center; justify-content: flex-end; gap: 5px;">
                            <i class="fa-solid fa-circle" style="font-size: 7px;"></i>
                            <span>{{ $isAktif ? 'Sedang Bertugas' : 'Mantan Pastor Paroki' }}</span>
                        </div>

                        <div style="margin-top: 4px;">
                            <button 
                                type="button" 
                                onclick="openPastorDetailModal({{ json_encode($r) }})"
                                style="display: inline-flex; align-items: center; gap: 6px; background: #f8fafc; border: 1px solid #cbd5e1; color: #334155; font-size: 0.78rem; font-weight: 700; padding: 6px 14px; border-radius: 12px; transition: all 0.2s; cursor: pointer;"
                                onmouseover="this.style.background='var(--primary-teal, #00897b)'; this.style.color='#ffffff'; this.style.borderColor='var(--primary-teal, #00897b)';"
                                onmouseout="this.style.background='#f8fafc'; this.style.color='#334155'; this.style.borderColor='#cbd5e1';"
                            >
                                <i class="fa-solid fa-circle-info text-xs"></i>
                                <span>Selengkapnya</span>
                            </button>
                        </div>
                    </div>

                </div>
            @empty
                <div style="background: #ffffff; border-radius: 20px; padding: 60px 30px; text-align: center; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.04); max-width: 600px; margin: 0 auto;">
                    <div style="width: 72px; height: 72px; border-radius: 50%; background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 18px;">
                        <i class="fa-solid fa-user-tie"></i>
                    </div>
                    <h4 style="font-size: 1.2rem; font-weight: 800; color: #1e293b; margin-bottom: 8px;">
                        Belum Ada Data Riwayat Pastor Paroki
                    </h4>
                    <p style="font-size: 0.88rem; color: #64748b; line-height: 1.6; margin-bottom: 22px;">
                        Data riwayat pastor paroki dapat dikelola melalui Panel Admin pada menu <strong>Profil Paroki ➔ Riwayat Pastor</strong>.
                    </p>
                    <a href="/" style="display: inline-flex; align-items: center; gap: 6px; background: var(--primary-teal, #00897b); color: #ffffff; font-size: 0.85rem; font-weight: 700; padding: 10px 22px; border-radius: 25px; text-decoration: none;">
                        <i class="fa-solid fa-house"></i> Kembali ke Beranda
                    </a>
                </div>
            @endforelse
        </div>

    </div>
</section>

@include('partials.pastor-detail-modal')

@endsection
