@extends('layouts.app')
@section('title', 'Direktori Katekis & Pengajar Iman - ' . ($globalNamaParoki ?? 'St. Vinsensius a Paulo Benlutu'))

@section('content')
@php
    $defaultAvatar = asset('images/avatar-default.jpg');
@endphp

<!-- Page Header / Breadcrumb Konoha Style -->
<section class="page-header">
    <div class="container">
        <h1 style="font-size: 2.4rem; font-weight: 800; margin-bottom: 12px; color: #ffffff;">Direktori Katekis Paroki</h1>
        <p style="font-size: 1rem; color: rgba(255,255,255,0.85); margin-bottom: 18px; max-width: 680px; margin-left: auto; margin-right: auto;">
            Daftar katekis paroki, pembina katekumen, pendamping sakramen inisiasi, dan pengajar iman yang melayani di Paroki dan Stasi.
        </p>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="display: inline-flex; list-style: none; padding: 0; margin: 0 auto; gap: 10px; background: transparent; justify-content: center; align-items: center; flex-wrap: wrap;">
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.2); padding: 5px 16px; border-radius: 25px; font-size: 0.82rem;">
                    <a href="/" style="color: white; text-decoration: none; font-weight: 500;">Beranda</a>
                </li>
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.2); padding: 5px 16px; border-radius: 25px; font-size: 0.82rem;">
                    <a href="/profil" style="color: white; text-decoration: none; font-weight: 500;">Profil</a>
                </li>
                <li class="breadcrumb-item active" style="background: var(--primary-orange, #ff9800); color: white; padding: 5px 16px; border-radius: 25px; font-size: 0.82rem; font-weight: 700;">
                    Katekis
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- Content Section Konoha Style -->
<section class="content-section" style="padding: 50px 0 80px; background: #f8fafc;">
    <div class="container">
        
        <!-- Header Info Bar -->
        <div style="background: #ffffff; border-radius: 20px; padding: 28px 32px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom: 35px; border: 1px solid #e2e8f0; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 18px;">
            <div style="display: flex; align-items: center; gap: 16px;">
                <div style="width: 52px; height: 52px; border-radius: 16px; background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                    <i class="fa-solid fa-book-bible"></i>
                </div>
                <div>
                    <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-orange, #ff9800);">
                        Pewartaan &amp; Katekese
                    </span>
                    <h3 style="font-size: 1.3rem; font-weight: 800; color: #0f172a; margin: 2px 0 0;">
                        Tenaga Pendidik &amp; Pembina Iman
                    </h3>
                </div>
            </div>
            
            <span style="display: inline-flex; align-items: center; gap: 6px; background: #f1f5f9; color: #475569; font-size: 0.82rem; font-weight: 700; padding: 7px 16px; border-radius: 20px; border: 1px solid #cbd5e1;">
                <i class="fa-solid fa-user-check text-emerald-600"></i> Total: {{ count($katekis ?? []) }} Katekis Terdaftar
            </span>
        </div>

        <!-- Katekis Members Grid -->
        @if(isset($katekis) && count($katekis) > 0)
            <div class="row g-4">
                @foreach($katekis as $item)
                    @php
                        $fotoUrl = !empty($item->foto) 
                            ? (str_starts_with($item->foto, 'http') ? $item->foto : asset('uploads/direktori_katekis/' . $item->foto))
                            : $defaultAvatar;
                        $kategori = $item->jenis_katekis ?? 'Katekis Paroki';
                        $lokasi = $item->wilayah_pelayanan ?? 'Pusat Paroki';
                    @endphp
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div style="background: #ffffff; border-radius: 18px; border: 1px solid #e2e8f0; border-top: 4px solid var(--primary-teal, #00897b); padding: 24px 18px; text-align: center; height: 100%; box-shadow: 0 4px 14px rgba(0,0,0,0.03); display: flex; flex-direction: column; justify-content: space-between; transition: transform 0.2s, box-shadow 0.2s;">
                            <div>
                                <!-- Photo / Avatar -->
                                <div style="position: relative; width: 84px; height: 84px; margin: 0 auto 14px;">
                                    <img 
                                        src="{{ $fotoUrl }}" 
                                        alt="{{ $item->nama_lengkap ?? 'Katekis' }}" 
                                        style="width: 84px; height: 84px; border-radius: 50%; object-fit: cover; border: 3px solid #ccfbf1; padding: 2px; background: #fff;"
                                        onerror="this.onerror=null; this.src='{{ $defaultAvatar }}';"
                                    >
                                </div>

                                <!-- Kategori Badge -->
                                <span style="display: inline-block; background: rgba(0,137,123,0.1); color: #0f766e; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; padding: 3px 12px; border-radius: 12px; margin-bottom: 8px; max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $kategori }}
                                </span>

                                <!-- Member Name -->
                                <h4 style="font-size: 1rem; font-weight: 800; color: #0f172a; margin-bottom: 4px; line-height: 1.35;">
                                    {{ $item->nama_lengkap ?? '-' }}
                                </h4>

                                <!-- Lokasi Pelayanan -->
                                <p style="font-size: 0.78rem; font-weight: 600; color: #64748b; margin-bottom: 8px;">
                                    <i class="fa-solid fa-location-dot text-teal-600 me-1"></i> {{ $lokasi }}
                                </p>

                                @if(!empty($item->sertifikasi))
                                    <p style="font-size: 0.72rem; color: #94a3b8; margin: 0;">
                                        <i class="fa-solid fa-award text-amber-500 me-1"></i> {{ $item->sertifikasi }}
                                    </p>
                                @endif
                            </div>

                            <!-- Status Footer -->
                            <div style="margin-top: 14px; padding-top: 10px; border-top: 1px dashed #e2e8f0; font-size: 0.72rem; color: #94a3b8; display: flex; justify-content: space-between; align-items: center;">
                                <span>No. SK: <strong style="color: #475569;">{{ $item->nomor_sk ?? '-' }}</strong></span>
                                <span style="color: #10b981; font-weight: 700;">
                                    <i class="fa-solid fa-circle" style="font-size: 6px;"></i> Aktif
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div style="background: #ffffff; border-radius: 20px; padding: 60px 30px; text-align: center; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.04); max-width: 600px; margin: 0 auto;">
                <div style="width: 72px; height: 72px; border-radius: 50%; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 18px;">
                    <i class="fa-solid fa-book-bible"></i>
                </div>
                <h4 style="font-size: 1.2rem; font-weight: 800; color: #1e293b; margin-bottom: 8px;">
                    Data Direktori Katekis Sedang Diperbarui
                </h4>
                <p style="font-size: 0.88rem; color: #64748b; line-height: 1.6; margin-bottom: 22px;">
                    Daftar katekis paroki dapat dikelola melalui Panel Admin pada menu <strong>Pelayanan Paroki ➔ Direktori Katekis</strong>.
                </p>
                <a href="/" style="display: inline-flex; align-items: center; gap: 6px; background: var(--primary-teal, #00897b); color: #ffffff; font-size: 0.85rem; font-weight: 700; padding: 10px 22px; border-radius: 25px; text-decoration: none;">
                    <i class="fa-solid fa-house"></i> Kembali ke Beranda
                </a>
            </div>
        @endif

        <!-- Share Buttons -->
        <div style="margin-top: 35px;">
            @include('partials.share-buttons', ['title' => 'Direktori Katekis - ' . ($globalNamaParoki ?? 'Paroki')])
        </div>

    </div>
</section>
@endsection
