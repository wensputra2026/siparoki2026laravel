@extends('layouts.app')
@section('title', 'Pelayan Pastoral - ' . ($globalNamaParoki ?? 'St. Vinsensius a Paulo Benlutu'))

@section('content')
@php
    $imamImage = file_exists(public_path('assets/frontend/siparoki/images/default-pastor.jpg'))
        ? asset('assets/frontend/siparoki/images/default-pastor.jpg')
        : (file_exists(public_path('assets/frontend/siparoki/images/default-principal.jpg'))
            ? asset('assets/frontend/siparoki/images/default-principal.jpg')
            : asset('images/pastor-avatar.svg'));
@endphp

<!-- Page Header / Breadcrumb Konoha Style -->
<section class="page-header">
    <div class="container">
        <h1 style="font-size: 2.6rem; font-weight: 700; margin-bottom: 15px; color: #ffffff;">Pelayan Pastoral</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="display: inline-flex; list-style: none; padding: 0; margin: 0 auto; gap: 12px; background: transparent; justify-content: center; align-items: center;">
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/" style="color: white; text-decoration: none; font-weight: 500;">Beranda</a>
                </li>
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/profil" style="color: white; text-decoration: none; font-weight: 500;">Tentang</a>
                </li>
                <li class="breadcrumb-item active" style="background: var(--primary-orange, #ff9800); color: white; padding: 6px 18px; border-radius: 25px; font-size: 0.85rem; font-weight: 600;">
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
                {{-- Pastor Paroki --}}
                <div class="col-lg-4 col-md-6">
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-top: 4px solid #ff9800; border-radius: 16px; padding: 25px 20px; text-align: center; height: 100%; box-shadow: 0 4px 12px rgba(0,0,0,0.03); display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="position: relative; width: 90px; height: 90px; margin: 0 auto 16px;">
                                <img src="{{ !empty($pastor_foto) ? $pastor_foto : $imamImage }}" alt="Pastor Paroki" style="width: 90px; height: 90px; border-radius: 16px; object-fit: cover; border: 2px solid #ff9800; padding: 2px;">
                                <span style="position: absolute; bottom: -6px; right: -6px; background: #ff9800; color: #fff; font-size: 9px; font-weight: 700; padding: 2px 7px; border-radius: 10px;">Paroki</span>
                            </div>
                            <span style="display: inline-block; background: rgba(255,152,0,0.12); color: #d97706; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; padding: 3px 12px; border-radius: 12px; margin-bottom: 8px;">
                                Pastor Paroki
                            </span>
                            <h4 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 4px;">
                                {{ $pastor_paroki ?? 'RD. Herman Hillers Penga' }}
                            </h4>
                            <p style="font-size: 0.8rem; font-weight: 600; color: #059669; margin-bottom: 12px;">
                                Ketua Dewan Pastoral Paroki (DPP)
                            </p>
                            <p style="font-size: 0.78rem; color: #64748b; line-height: 1.5; margin: 0;">
                                Penanggung jawab umum reksa pastoral, perayaan sakramen, dan penggembalaan umat paroki.
                            </p>
                        </div>
                        <div style="margin-top: 18px; padding-top: 12px; border-top: 1px dashed #e2e8f0; display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem;">
                            <span style="color: #94a3b8;">Status</span>
                            <span style="color: #10b981; font-weight: 700;"><i class="fa-solid fa-circle" style="font-size: 7px;"></i> Aktif Melayani</span>
                        </div>
                    </div>
                </div>

                {{-- Pastor Rekan --}}
                <div class="col-lg-4 col-md-6">
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-top: 4px solid #0284c7; border-radius: 16px; padding: 25px 20px; text-align: center; height: 100%; box-shadow: 0 4px 12px rgba(0,0,0,0.03); display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="position: relative; width: 90px; height: 90px; margin: 0 auto 16px;">
                                <img src="{{ !empty($pastor_rekan_foto) ? $pastor_rekan_foto : $imamImage }}" alt="Pastor Rekan" style="width: 90px; height: 90px; border-radius: 16px; object-fit: cover; border: 2px solid #0284c7; padding: 2px;">
                                <span style="position: absolute; bottom: -6px; right: -6px; background: #0284c7; color: #fff; font-size: 9px; font-weight: 700; padding: 2px 7px; border-radius: 10px;">Vikaris</span>
                            </div>
                            <span style="display: inline-block; background: rgba(2,132,199,0.12); color: #0369a1; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; padding: 3px 12px; border-radius: 12px; margin-bottom: 8px;">
                                Pastor Rekan
                            </span>
                            <h4 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 4px;">
                                {{ (!empty($pastor_rekan) && $pastor_rekan !== 'Pastor Rekan') ? $pastor_rekan : 'Pastor Rekan Paroki' }}
                            </h4>
                            <p style="font-size: 0.8rem; font-weight: 600; color: #0284c7; margin-bottom: 12px;">
                                Vikaris Paroki
                            </p>
                            <p style="font-size: 0.78rem; color: #64748b; line-height: 1.5; margin: 0;">
                                Membantu pelayanan sakramen, reksa pastoral teritorial stasi kapela, lingkungan, dan KUB.
                            </p>
                        </div>
                        <div style="margin-top: 18px; padding-top: 12px; border-top: 1px dashed #e2e8f0; display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem;">
                            <span style="color: #94a3b8;">Status</span>
                            <span style="color: #10b981; font-weight: 700;"><i class="fa-solid fa-circle" style="font-size: 7px;"></i> Aktif Melayani</span>
                        </div>
                    </div>
                </div>

                {{-- Frater / Katekis --}}
                <div class="col-lg-4 col-md-6">
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-top: 4px solid #0d9488; border-radius: 16px; padding: 25px 20px; text-align: center; height: 100%; box-shadow: 0 4px 12px rgba(0,0,0,0.03); display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="position: relative; width: 90px; height: 90px; margin: 0 auto 16px;">
                                <div style="width: 90px; height: 90px; border-radius: 16px; background: #ccfbf1; color: #0d9488; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; border: 2px solid #0d9488;">
                                    <i class="fa-solid fa-book-bible"></i>
                                </div>
                                <span style="position: absolute; bottom: -6px; right: -6px; background: #0d9488; color: #fff; font-size: 9px; font-weight: 700; padding: 2px 7px; border-radius: 10px;">Pastoral</span>
                            </div>
                            <span style="display: inline-block; background: rgba(13,148,136,0.12); color: #0f766e; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; padding: 3px 12px; border-radius: 12px; margin-bottom: 8px;">
                                Frater TOP / Katekis
                            </span>
                            <h4 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 4px;">
                                {{ (!empty($frater) && $frater !== 'Frater TOP') ? $frater : 'Frater Pastoral / Katekis' }}
                            </h4>
                            <p style="font-size: 0.8rem; font-weight: 600; color: #0d9488; margin-bottom: 12px;">
                                Pendamping Pastoral &amp; Katekese
                            </p>
                            <p style="font-size: 0.78rem; color: #64748b; line-height: 1.5; margin: 0;">
                                Pendampingan katekese, bina iman anak &amp; remaja, OMK, misdinar, dan kegiatan liturgi paroki.
                            </p>
                        </div>
                        <div style="margin-top: 18px; padding-top: 12px; border-top: 1px dashed #e2e8f0; display: flex; justify-content: space-between; align-items: center; font-size: 0.75rem;">
                            <span style="color: #94a3b8;">Status</span>
                            <span style="color: #10b981; font-weight: 700;"><i class="fa-solid fa-circle" style="font-size: 7px;"></i> Aktif Melayani</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection
