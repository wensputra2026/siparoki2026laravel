@extends('layouts.app')

@php
    $id = $kapela->id_stasi_kapela ?? $kapela->id ?? 1;
    $nama = $kapela->nama_stasi_kapela ?? $kapela->nama_kapela ?? $kapela->nama_stasi ?? $kapela->nama ?? 'Gereja Stasi / Kapela';
    $tipe = $kapela->tipe ?? 'Stasi / Kapela';
    $alamat = $kapela->alamat ?? $kapela->lokasi ?? null;
    $kode = $kapela->kode_stasi_kapela ?? $kapela->kode_kapela ?? null;
    $pelindung = $kapela->nama_pelindung ?? $kapela->pelindung ?? null;
    $pj = $kapela->penanggung_jawab ?? null;
    $statusStr = $kapela->status ?? 'Aktif';
    $isAktif = (is_numeric($statusStr) && $statusStr == 1) || str_contains(strtolower((string)$statusStr), 'aktif');
    
    $foto = $kapela->foto ?? null;
    $imageUrl = null;
    if ($foto) {
        if (str_starts_with($foto, 'http://') || str_starts_with($foto, 'https://')) {
            $imageUrl = $foto;
        } elseif (file_exists(public_path($foto))) {
            $imageUrl = asset($foto);
        } elseif (file_exists(public_path('assets/uploads/kapela/' . $foto))) {
            $imageUrl = asset('assets/uploads/kapela/' . $foto);
        } elseif (file_exists(public_path('uploads/' . $foto))) {
            $imageUrl = asset('uploads/' . $foto);
        } elseif (file_exists(public_path('storage/' . $foto))) {
            $imageUrl = asset('storage/' . $foto);
        }
    }

    $wilayahParts = array_filter([
        $kapela->nama_desa ?? null,
        $kapela->nama_kecamatan ?? null,
        $kapela->nama_kabupaten ?? null,
        $kapela->nama_provinsi ?? null
    ]);
    $wilayahStr = !empty($wilayahParts) ? implode(', ', $wilayahParts) : null;

    $mapsUrl = $kapela->maps_url ?? null;
    if (!$mapsUrl && !empty($kapela->latitude) && !empty($kapela->longitude)) {
        $mapsUrl = 'https://www.google.com/maps?q=' . $kapela->latitude . ',' . $kapela->longitude;
    }

    $backRoute = '/profil-kapela';
    $backLabel = 'Kembali ke Daftar Stasi & Kapela';
@endphp

@section('title', $nama . ' - ' . ($globalNamaParoki ?? 'SIPAROKI'))
@section('description', 'Profil dan informasi wilayah pelayanan ' . $nama . ' di Paroki ' . ($globalNamaParoki ?? 'SIPAROKI') . '.')
@section('image', $imageUrl)

@section('content')
<!-- Page Header / Breadcrumb Style -->
<section class="page-header">
    <div class="container">
        <h1 style="font-size: 2.5rem; font-weight: 700; margin-bottom: 18px; color: #ffffff; line-height: 1.25; margin-left: auto; margin-right: auto; text-align: center;">
            {{ $nama }}
        </h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="display: inline-flex; list-style: none; padding: 0; margin: 0 auto; gap: 10px; background: transparent; justify-content: center; align-items: center; flex-wrap: wrap;">
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/" style="color: white; text-decoration: none; font-weight: 500;">Beranda</a>
                </li>
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="{{ $backRoute }}" style="color: white; text-decoration: none; font-weight: 500;">Stasi &amp; Kapela</a>
                </li>
                <li class="breadcrumb-item active" style="background: var(--primary-orange, #ff9800); color: white; padding: 6px 20px; border-radius: 25px; font-size: 0.85rem; font-weight: 700;">
                    {{ $nama }}
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- Main Detail Content Section Style -->
<section class="content-section" style="padding: 50px 0 70px; background: #f4faf9;">
    <div class="container">
        
        <!-- Back Link Above Card -->
        <div style="margin-bottom: 25px;">
            <a href="{{ $backRoute }}" class="back-link" style="display: inline-flex; align-items: center; gap: 8px; color: var(--primary-teal, #00897b); font-weight: 600; text-decoration: none; font-size: 0.95rem; transition: transform 0.2s;">
                <i class="fas fa-arrow-left"></i> {{ $backLabel }}
            </a>
        </div>

        <div class="row g-4 align-items-start">
            
            <!-- Left: Main Article Card -->
            <div class="col-lg-8">
                <div class="detail-content" style="background: #ffffff; padding: 32px 34px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.06);">
                    
                    <!-- 1. Category & Status Badges -->
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 20px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="display: inline-block; background: var(--primary-teal, #00897b); color: #ffffff; padding: 6px 20px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">
                                <i class="fas fa-church me-1"></i> {{ $tipe }}
                            </span>
                            @if($kode)
                                <span style="background: #f1f5f9; color: #475569; padding: 5px 14px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; border: 1px solid #e2e8f0;">
                                    Kode: {{ $kode }}
                                </span>
                            @endif
                        </div>

                        <span style="background: {{ $isAktif ? '#dcfce7' : '#f1f5f9' }}; color: {{ $isAktif ? '#15803d' : '#64748b' }}; border: 1px solid {{ $isAktif ? '#bbf7d0' : '#e2e8f0' }}; padding: 5px 14px; border-radius: 20px; font-size: 0.78rem; font-weight: 700; display: inline-flex; align-items: center; gap: 6px;">
                            <i class="fas fa-circle" style="font-size: 7px;"></i> {{ $isAktif ? 'Status: Aktif' : 'Non-Aktif' }}
                        </span>
                    </div>

                    <!-- 2. Meta Info Row -->
                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 20px; margin-bottom: 25px; padding-bottom: 18px; border-bottom: 1px solid #eef2f6; font-size: 0.9rem; color: #64748b;">
                        @if($pelindung)
                            <span style="display: inline-flex; align-items: center; gap: 7px;">
                                <i class="fas fa-cross" style="color: #ff9800; font-size: 1rem;"></i> 
                                Pelindung: <strong style="color: #1e293b;">{{ $pelindung }}</strong>
                            </span>
                        @endif
                        @if($pj)
                            <span style="display: inline-flex; align-items: center; gap: 7px;">
                                <i class="fas fa-user-tie" style="color: #ff9800; font-size: 1rem;"></i> 
                                PJ: <strong style="color: #1e293b;">{{ $pj }}</strong>
                            </span>
                        @endif
                        @if($wilayahStr || $alamat)
                            <span style="display: inline-flex; align-items: center; gap: 7px;">
                                <i class="fas fa-map-marker-alt" style="color: #ff9800; font-size: 1rem;"></i> 
                                {{ $wilayahStr ?: $alamat }}
                            </span>
                        @endif
                    </div>

                    <!-- 3. Featured Image -->
                    @if($imageUrl)
                        <div style="border-radius: 12px; overflow: hidden; margin-bottom: 30px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); max-height: 460px; background: #f1f5f9;">
                            <img src="{{ $imageUrl }}" alt="{{ $nama }}" style="width: 100%; max-height: 460px; object-fit: cover; display: block;">
                        </div>
                    @endif

                    <!-- 4. Quick Highlights Grid -->
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <div style="padding: 16px 18px; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; border-left: 4px solid #ff9800; height: 100%;">
                                <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.4px; display: block; margin-bottom: 4px;">
                                    <i class="fas fa-cross text-warning me-1"></i> Santo / Santa Pelindung
                                </span>
                                <strong style="color: #0f172a; font-size: 1rem;">{{ $pelindung ?: '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div style="padding: 16px 18px; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0; border-left: 4px solid var(--primary-teal, #00897b); height: 100%;">
                                <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.4px; display: block; margin-bottom: 4px;">
                                    <i class="fas fa-user-tie text-teal me-1" style="color: #00897b;"></i> Penanggung Jawab / Ketua
                                </span>
                                <strong style="color: #0f172a; font-size: 1rem;">{{ $pj ?: '-' }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Sejarah & Latar Belakang (Article Body) -->
                    <div class="article-text" style="color: #334155; font-size: 1rem; line-height: 1.85;">
                        <h4 style="font-size: 1.25rem; font-weight: 700; color: #0f172a; margin-top: 10px; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-book-bookmark" style="color: var(--primary-teal, #00897b);"></i> Sejarah &amp; Profil Perkembangan
                        </h4>
                        
                        @if(!empty($kapela->sejarah))
                            <div style="text-align: justify; margin-bottom: 24px;">
                                {!! nl2br(e($kapela->sejarah)) !!}
                            </div>
                        @else
                            <p class="text-muted" style="font-style: italic; margin-bottom: 24px;">
                                Belum ada catatan narasi sejarah khusus untuk {{ $nama }}. Informasi pelayanan stasi diperbarui secara berkala oleh pengurus wilayah dan paroki.
                            </p>
                        @endif

                        <!-- Visi & Misi Cards -->
                        @if(!empty($kapela->visi) || !empty($kapela->misi))
                            <div class="row g-3 my-4">
                                @if(!empty($kapela->visi))
                                    <div class="col-md-6">
                                        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-top: 3px solid var(--primary-teal, #00897b); border-radius: 12px; padding: 18px 20px; height: 100%; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                                            <h6 style="color: var(--primary-teal, #00897b); font-weight: 800; font-size: 0.9rem; text-transform: uppercase; margin-bottom: 8px;">
                                                <i class="fas fa-eye me-1"></i> Visi Stasi
                                            </h6>
                                            <p style="font-size: 0.92rem; color: #475569; margin: 0; line-height: 1.6;">{!! nl2br(e($kapela->visi)) !!}</p>
                                        </div>
                                    </div>
                                @endif
                                @if(!empty($kapela->misi))
                                    <div class="col-md-6">
                                        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-top: 3px solid #ff9800; border-radius: 12px; padding: 18px 20px; height: 100%; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                                            <h6 style="color: #d97706; font-weight: 800; font-size: 0.9rem; text-transform: uppercase; margin-bottom: 8px;">
                                                <i class="fas fa-bullseye me-1"></i> Misi Stasi
                                            </h6>
                                            <p style="font-size: 0.92rem; color: #475569; margin: 0; line-height: 1.6;">{!! nl2br(e($kapela->misi)) !!}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Informasi Wilayah Teritorial -->
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 22px; margin-top: 25px; margin-bottom: 25px;">
                            <h5 style="font-size: 1.05rem; font-weight: 700; color: #0f172a; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-map-location-dot" style="color: var(--primary-teal, #00897b);"></i> Wilayah Teritorial &amp; Lokasi
                            </h5>

                            @if($alamat)
                                <p style="color: #334155; font-size: 0.92rem; margin-bottom: 16px;">
                                    <i class="fas fa-location-dot text-danger me-1"></i> <strong>Alamat:</strong> {{ $alamat }}
                                </p>
                            @endif

                            <div class="row g-2 text-dark" style="font-size: 0.88rem;">
                                <div class="col-6 col-md-3">
                                    <div style="background: #ffffff; padding: 10px 14px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                        <span class="text-muted d-block small" style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700;">Desa / Kelurahan</span>
                                        <strong>{{ $kapela->nama_desa ?? '-' }}</strong>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div style="background: #ffffff; padding: 10px 14px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                        <span class="text-muted d-block small" style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700;">Kecamatan</span>
                                        <strong>{{ $kapela->nama_kecamatan ?? '-' }}</strong>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div style="background: #ffffff; padding: 10px 14px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                        <span class="text-muted d-block small" style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700;">Kabupaten</span>
                                        <strong>{{ $kapela->nama_kabupaten ?? '-' }}</strong>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div style="background: #ffffff; padding: 10px 14px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                        <span class="text-muted d-block small" style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700;">Provinsi</span>
                                        <strong>{{ $kapela->nama_provinsi ?? '-' }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Catatan Tambahan -->
                        @if(!empty($kapela->keterangan))
                            <div style="background: #f0fdfa; border: 1px solid #ccfbf1; border-radius: 12px; padding: 16px 20px; color: #134e4a; margin-top: 20px;">
                                <span style="font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; color: #0f766e; display: block; margin-bottom: 4px;">
                                    <i class="fas fa-circle-info me-1"></i> Informasi Tambahan:
                                </span>
                                <p style="font-size: 0.92rem; color: #334155; margin: 0; line-height: 1.6;">{{ $kapela->keterangan }}</p>
                            </div>
                        @endif

                    </div>

                    <!-- 6. Tags / Labels -->
                    <div style="margin-top: 35px; padding-top: 20px; border-top: 1px solid #eef2f6; display: flex; flex-wrap: wrap; align-items: center; gap: 8px;">
                        <span style="font-size: 0.85rem; font-weight: 600; color: #64748b; margin-right: 4px;">
                            <i class="fas fa-tags" style="color: var(--primary-teal, #00897b);"></i> Label:
                        </span>
                        <span style="background: #f1f5f9; color: #475569; padding: 4px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 500;">
                            #{{ $tipe }}
                        </span>
                        @if($pelindung)
                            <span style="background: #f1f5f9; color: #475569; padding: 4px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 500;">
                                #{{ $pelindung }}
                            </span>
                        @endif
                        @if(!empty($kapela->nama_desa))
                            <span style="background: #f1f5f9; color: #475569; padding: 4px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 500;">
                                #{{ $kapela->nama_desa }}
                            </span>
                        @endif
                        <span style="background: #f1f5f9; color: #475569; padding: 4px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 500;">
                            #ParokiBenlutu
                        </span>
                    </div>

                </div>

                <!-- Bottom Navigation Buttons -->
                <div style="margin-top: 25px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                    <a href="{{ $backRoute }}" style="display: inline-flex; align-items: center; gap: 8px; background: #ffffff; border: 1px solid #cbd5e1; color: #334155; font-size: 0.88rem; font-weight: 700; padding: 10px 22px; border-radius: 25px; text-decoration: none; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
                        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Stasi
                    </a>
                    <a href="/peta-kapela" style="display: inline-flex; align-items: center; gap: 8px; background: #0f172a; color: #ffffff; font-size: 0.88rem; font-weight: 700; padding: 10px 22px; border-radius: 25px; text-decoration: none; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        <i class="fas fa-map-location-dot" style="color: #14b8a6;"></i> Buka Peta Interaktif
                    </a>
                </div>
            </div>

            <!-- Right: Sidebar Column -->
            <div class="col-lg-4">
                
                <!-- Quick Info Box Widget -->
                <div class="sidebar-widget" style="background: #ffffff; border-radius: 15px; padding: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.06); margin-bottom: 25px;">
                    <h4 class="widget-title" style="font-size: 1.15rem; font-weight: 700; color: #1e293b; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 2px solid var(--primary-teal, #00897b); display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-info-circle" style="color: var(--primary-teal, #00897b);"></i> Ringkasan Stasi
                    </h4>

                    <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 14px;">
                        <li style="display: flex; align-items: flex-start; gap: 10px;">
                            <i class="fas fa-cross text-warning mt-1"></i>
                            <div>
                                <span style="font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; display: block;">Pelindung</span>
                                <strong style="color: #0f172a; font-size: 0.95rem;">{{ $pelindung ?: '-' }}</strong>
                            </div>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 10px;">
                            <i class="fas fa-user-tie mt-1" style="color: var(--primary-teal, #00897b);"></i>
                            <div>
                                <span style="font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; display: block;">Penanggung Jawab</span>
                                <strong style="color: #0f172a; font-size: 0.95rem;">{{ $pj ?: '-' }}</strong>
                            </div>
                        </li>
                        <li style="display: flex; align-items: flex-start; gap: 10px;">
                            <i class="fas fa-map-marker-alt text-danger mt-1"></i>
                            <div>
                                <span style="font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; display: block;">Lokasi Teritorial</span>
                                <strong style="color: #0f172a; font-size: 0.95rem;">{{ $wilayahStr ?: ($alamat ?: '-') }}</strong>
                            </div>
                        </li>
                    </ul>

                    @if($mapsUrl)
                        <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid #f1f5f9;">
                            <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer" style="display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; background: var(--primary-teal, #00897b); color: #ffffff; font-weight: 700; font-size: 0.85rem; padding: 10px 16px; border-radius: 20px; text-decoration: none; box-shadow: 0 4px 12px rgba(0,137,123,0.25);">
                                <i class="fas fa-location-arrow"></i> Petunjuk Arah (Google Maps)
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Other Kapela List Widget -->
                @if(isset($otherKapela) && $otherKapela->isNotEmpty())
                    <div class="sidebar-widget" style="background: #ffffff; border-radius: 15px; padding: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.06); margin-bottom: 25px;">
                        <h4 class="widget-title" style="font-size: 1.15rem; font-weight: 700; color: #1e293b; margin-bottom: 18px; padding-bottom: 12px; border-bottom: 2px solid #ff9800; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-church" style="color: #ff9800;"></i> Stasi / Kapela Lainnya
                        </h4>

                        <div style="display: flex; flex-direction: column; gap: 12px;">
                            @foreach($otherKapela as $ok)
                                @php
                                    $okId = $ok->id_stasi_kapela ?? $ok->id ?? 1;
                                    $okNama = $ok->nama_stasi_kapela ?? $ok->nama_kapela ?? 'Gereja Stasi';
                                    $okPelindung = $ok->nama_pelindung ?? $ok->pelindung ?? null;
                                @endphp
                                <a href="/profil-kapela/{{ $okId }}" style="display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 14px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='#e6f4f1'; this.style.borderColor='var(--primary-teal, #00897b)';" onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0';">
                                    <div style="min-width: 0;">
                                        <strong style="color: #0f172a; font-size: 0.88rem; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $okNama }}</strong>
                                        @if($okPelindung)
                                            <span style="font-size: 0.75rem; color: #64748b; display: block;"><i class="fas fa-cross me-1 text-warning"></i> {{ $okPelindung }}</span>
                                        @endif
                                    </div>
                                    <i class="fas fa-chevron-right text-muted" style="font-size: 0.8rem;"></i>
                                </a>
                            @endforeach
                        </div>

                        <div style="margin-top: 18px; text-align: center;">
                            <a href="{{ $backRoute }}" style="font-size: 0.85rem; font-weight: 700; color: var(--primary-teal, #00897b); text-decoration: none;">
                                Lihat Semua Stasi <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                @endif

                <!-- Peta Wilayah Banner Widget -->
                <div class="sidebar-widget" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 15px; padding: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); color: #ffffff;">
                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
                        <div style="width: 42px; height: 42px; border-radius: 50%; background: rgba(20, 184, 166, 0.2); color: #14b8a6; display: flex; align-items: center; justify-content: center; font-size: 1.25rem;">
                            <i class="fas fa-map-location-dot"></i>
                        </div>
                        <h5 style="font-size: 1rem; font-weight: 700; margin: 0; color: #ffffff;">Peta Wilayah Paroki</h5>
                    </div>
                    <p style="font-size: 0.82rem; color: #94a3b8; line-height: 1.5; margin-bottom: 16px;">
                        Lihat persebaran lokasi seluruh gereja stasi dan kapela di wilayah Paroki St. Vinsensius a Paulo Benlutu secara interaktif.
                    </p>
                    <a href="/peta-kapela" style="display: inline-flex; align-items: center; justify-content: center; gap: 6px; width: 100%; background: #14b8a6; color: #ffffff; font-weight: 700; font-size: 0.82rem; padding: 9px 14px; border-radius: 20px; text-decoration: none;">
                        Buka Peta Interaktif <i class="fas fa-arrow-right"></i>
                    </a>
                </div>

            </div>

        </div>
    </div>
</section>
@endsection
