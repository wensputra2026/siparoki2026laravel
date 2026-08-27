@extends('layouts.public')

@section('title', 'Profil Kapela & Stasi - ' . ($globalNamaParoki ?? 'SIPAROKI'))
@section('description', 'Daftar stasi dan kapela dalam wilayah pelayanan ' . ($globalNamaParoki ?? 'SIPAROKI') . '.')

@section('content')
@php
    $kapelaItems = $kapela instanceof \Illuminate\Pagination\AbstractPaginator ? $kapela->items() : (is_iterable($kapela) ? $kapela : collect([]));
@endphp

<section class="page-header">
    <div class="container">
        <span class="st-badge"><i class="fas fa-location-dot me-1"></i> Wilayah Pelayanan</span>
        <h1>Stasi &amp; Kapela</h1>
        <p>Profil wilayah pelayanan, pusat komunitas umat, dan lokasi peribadatan dalam teritori paroki.</p>
    </div>
</section>

<style>
/* Responsive Chapel Toolbar & Search System */
.chapel-toolbar-wrap {
    background: #ffffff !important;
    border-radius: 20px !important;
    padding: 18px 24px !important;
    border: 1px solid #e2e8f0 !important;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04) !important;
    margin-bottom: 30px !important;
    border-left: 5px solid #00897b !important;
}

.chapel-toolbar-grid {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    flex-wrap: wrap;
    margin: 0;
}

.chapel-info-box {
    display: flex;
    align-items: center;
    gap: 12px;
}

.chapel-search-box {
    display: flex;
    align-items: center;
    background: #f8fafc;
    border: 1.5px solid #cbd5e1;
    border-radius: 30px;
    padding: 4px 6px 4px 16px;
    transition: all 0.25s ease;
    flex: 1;
    min-width: 260px;
    max-width: 480px;
}

.chapel-search-box:focus-within {
    border-color: #00897b !important;
    background: #ffffff !important;
    box-shadow: 0 0 0 3px rgba(0, 137, 123, 0.15) !important;
}

.chapel-search-box input {
    border: none;
    outline: none;
    background: transparent;
    width: 100%;
    font-size: 0.88rem;
    color: #0f172a;
    font-weight: 500;
    padding: 6px 0;
    min-width: 0;
}

.chapel-search-box .btn-search {
    background: linear-gradient(135deg, #00897b, #004d40) !important;
    color: #ffffff !important;
    border: none;
    border-radius: 25px;
    padding: 7px 18px;
    font-size: 0.82rem;
    font-weight: 700;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    cursor: pointer;
    flex-shrink: 0;
    transition: transform 0.15s, box-shadow 0.15s;
    box-shadow: 0 2px 8px rgba(0,137,123,0.25);
    white-space: nowrap;
    text-decoration: none;
}

.chapel-search-box .btn-search:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,137,123,0.35);
}

.chapel-map-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background: #0f172a;
    color: #ffffff;
    border-radius: 25px;
    padding: 9px 20px;
    font-size: 0.85rem;
    font-weight: 700;
    text-decoration: none;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
    transition: all 0.2s;
    white-space: nowrap;
}

.chapel-map-btn:hover {
    background: #1e293b;
    color: #ffffff;
    transform: translateY(-1px);
}

/* Tablet & Mobile Adjustments */
@media (max-width: 991px) {
    .chapel-toolbar-grid {
        flex-direction: column;
        align-items: stretch;
        gap: 14px;
    }
    .chapel-search-form {
        width: 100% !important;
        max-width: 100% !important;
    }
    .chapel-search-box {
        max-width: 100%;
        width: 100%;
    }
    .chapel-map-btn {
        width: 100%;
    }
}

@media (max-width: 576px) {
    .chapel-toolbar-wrap {
        padding: 14px 16px !important;
        border-radius: 16px !important;
    }
    .chapel-search-box {
        padding: 3px 4px 3px 12px;
    }
    .chapel-search-box input {
        font-size: 0.82rem;
    }
    .chapel-search-box .btn-search {
        padding: 6px 14px;
        font-size: 0.78rem;
    }
}
</style>

<section class="content-section chapel-page" style="background: #f8fafc; padding: 40px 0 80px;">
    <div class="container">
        <!-- Toolbar Header & Search -->
        <div class="chapel-toolbar-wrap">
            <div class="chapel-toolbar-grid">
                <!-- Left: Total info -->
                <div class="chapel-info-box">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle flex-shrink-0" style="width: 44px; height: 44px; background: rgba(0, 137, 123, 0.1); color: #00897b; font-size: 1.25rem;">
                        <i class="fas fa-church"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-bold text-uppercase d-block" style="letter-spacing: 0.5px; font-size: 0.72rem; margin-bottom: 2px;">Total Wilayah Pelayanan</span>
                        <strong class="fs-5 text-dark fw-bold" style="letter-spacing: -0.3px;">{{ method_exists($kapela, 'total') ? $kapela->total() : count($kapelaItems) }} <span class="fs-6 fw-normal text-muted">Stasi &amp; Kapela</span></strong>
                    </div>
                </div>

                <!-- Center: Responsive Search Form -->
                <form action="/profil-kapela" method="GET" class="chapel-search-form m-0" style="flex: 1; max-width: 480px;">
                    <div class="chapel-search-box">
                        <i class="fas fa-search me-2 text-muted" style="font-size: 0.88rem; flex-shrink: 0; color: #94a3b8 !important;"></i>
                        <input 
                            type="text" 
                            name="q" 
                            value="{{ $search ?? '' }}" 
                            placeholder="Cari nama stasi, pelindung, lokasi..." 
                            aria-label="Cari stasi atau kapela"
                        >
                        @if(!empty($search))
                            <a href="/profil-kapela" style="color: #94a3b8; text-decoration: none; padding: 0 8px; font-size: 0.95rem; display: flex; align-items: center;" onmouseover="this.style.color='#ef4444';" onmouseout="this.style.color='#94a3b8';" title="Reset pencarian">
                                <i class="fas fa-circle-xmark"></i>
                            </a>
                        @endif
                        <button type="submit" class="btn-search">
                            <span>Cari</span>
                        </button>
                    </div>
                </form>

                <!-- Right: Map Button -->
                <a href="/peta-kapela" class="chapel-map-btn">
                    <i class="fas fa-map-location-dot" style="color: #14b8a6;"></i>
                    <span>Lihat Peta Interaktif</span>
                </a>
            </div>
        </div>

        @if(!empty($search))
            <div class="alert alert-info d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2" style="border-radius: 14px; background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534;">
                <div>
                    <i class="fas fa-search me-2" style="color: #00897b;"></i> Menampilkan hasil pencarian untuk: <strong>"{{ $search }}"</strong> ({{ method_exists($kapela, 'total') ? $kapela->total() : count($kapelaItems) }} ditemukan)
                </div>
                <a href="/profil-kapela" class="btn btn-sm btn-outline-success rounded-pill fw-bold" style="font-size: 0.8rem;">Reset Filter</a>
            </div>
        @endif

        <div class="chapel-grid">
            @forelse($kapelaItems as $k)
                @php
                    $id = $k->id_stasi_kapela ?? $k->id ?? $loop->iteration;
                    $nama = $k->nama_stasi_kapela ?? $k->nama_kapela ?? $k->nama_stasi ?? $k->nama ?? 'Gereja Stasi / Kapela';
                    $tipe = $k->tipe ?? 'Stasi / Kapela';
                    $alamat = $k->alamat ?? $k->lokasi ?? null;
                    $kode = $k->kode_stasi_kapela ?? $k->kode_kapela ?? null;
                    $pelindung = $k->nama_pelindung ?? $k->pelindung ?? null;
                    $pj = $k->penanggung_jawab ?? null;
                    $foto = $k->foto ?? null;
                    $fotoUrl = null;
                    if ($foto) {
                        if (str_starts_with($foto, 'http://') || str_starts_with($foto, 'https://')) {
                            $fotoUrl = $foto;
                        } elseif (file_exists(public_path($foto))) {
                            $fotoUrl = asset($foto);
                        } elseif (file_exists(public_path('assets/uploads/kapela/' . $foto))) {
                            $fotoUrl = asset('assets/uploads/kapela/' . $foto);
                        } elseif (file_exists(public_path('uploads/' . $foto))) {
                            $fotoUrl = asset('uploads/' . $foto);
                        } elseif (file_exists(public_path('storage/' . $foto))) {
                            $fotoUrl = asset('storage/' . $foto);
                        }
                    }

                    $wilayahParts = array_filter([
                        $k->nama_desa ?? null,
                        $k->nama_kecamatan ?? null,
                        $k->nama_kabupaten ?? null
                    ]);
                    $wilayahStr = !empty($wilayahParts) ? implode(', ', $wilayahParts) : null;
                @endphp
                <article class="chapel-card d-flex flex-column h-100 position-relative shadow-sm border-0 rounded-4 overflow-hidden bg-white">
                    @if($fotoUrl)
                        <div class="chapel-card-img-wrapper position-relative" style="height: 180px; overflow: hidden; background: #e2e8f0;">
                            <img src="{{ $fotoUrl }}" alt="{{ $nama }}" class="w-100 h-100 object-fit-cover transition-transform duration-300" style="object-fit: cover;">
                            <div class="position-absolute top-0 end-0 m-3">
                                <span class="badge bg-primary px-3 py-2 rounded-pill shadow-sm" style="font-size: 0.75rem; letter-spacing: 0.5px; background: #00897b !important;">{{ $tipe }}</span>
                            </div>
                        </div>
                    @endif

                    <div class="chapel-card-body p-4 d-flex flex-column flex-grow-1">
                        @if(!$fotoUrl)
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="chapel-icon-circle d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 48px; height: 48px; background: rgba(0, 137, 123, 0.1); color: #00897b; font-size: 1.25rem;">
                                    <i class="fas fa-church"></i>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge px-3 py-1.5 rounded-pill" style="font-size: 0.75rem; font-weight: 700; background: #e6f4f1; color: #00897b; border: 1px solid #b2dfdb;">{{ $tipe }}</span>
                                    @if($kode)
                                        <span class="badge bg-light text-muted border px-2 py-1 rounded" style="font-size: 0.7rem;">{{ $kode }}</span>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                @if($kode)
                                    <span class="badge bg-light text-muted border px-2 py-1 rounded" style="font-size: 0.7rem;">Kode: {{ $kode }}</span>
                                @else
                                    <span></span>
                                @endif
                            </div>
                        @endif

                        <a href="/profil-kapela/{{ $id }}" class="text-decoration-none">
                            <h3 class="h5 fw-bold text-dark mb-1 text-truncate-2" title="{{ $nama }}">{{ $nama }}</h3>
                        </a>
                        
                        @if($pelindung)
                            <div class="text-teal small fw-semibold mb-3" style="color: #00897b;">
                                <i class="fas fa-cross me-1 opacity-75"></i> Pelindung: <strong>{{ $pelindung }}</strong>
                            </div>
                        @else
                            <div class="mb-2"></div>
                        @endif

                        <div class="chapel-meta text-muted small mb-4 flex-grow-1">
                            @if($alamat || $wilayahStr)
                                <div class="d-flex align-items-start gap-2 mb-2">
                                    <i class="fas fa-map-marker-alt text-danger mt-1 flex-shrink-0"></i>
                                    <span class="text-secondary line-clamp-2">
                                        {{ $alamat ?: $wilayahStr }}
                                    </span>
                                </div>
                            @endif

                            @if($pj)
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-user-tie text-secondary flex-shrink-0"></i>
                                    <span class="text-secondary text-truncate">PJ: {{ $pj }}</span>
                                </div>
                            @endif
                        </div>

                        <!-- Card Action Buttons -->
                        <div class="pt-3 border-top d-flex gap-2 align-items-center mt-auto">
                            <a 
                                href="/profil-kapela/{{ $id }}"
                                class="btn btn-sm flex-grow-1 fw-bold rounded-pill py-2 d-inline-flex align-items-center justify-content-center gap-2 text-decoration-none" 
                                style="border: 1.5px solid #00897b; color: #00897b; background: transparent; transition: all 0.2s;" 
                                onmouseover="this.style.background='#00897b'; this.style.color='#ffffff';" 
                                onmouseout="this.style.background='transparent'; this.style.color='#00897b';" 
                            >
                                <span>Lihat Profil Lengkap</span>
                                <i class="fas fa-arrow-right text-xs"></i>
                            </a>
                            @if(!empty($k->maps_url))
                                <a href="{{ $k->maps_url }}" target="_blank" rel="noopener noreferrer" class="btn btn-light btn-sm rounded-circle border p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Buka Google Maps">
                                    <i class="fas fa-location-arrow text-teal" style="color: #00897b;"></i>
                                </a>
                            @elseif(!empty($k->latitude) && !empty($k->longitude))
                                <a href="https://www.google.com/maps?q={{ $k->latitude }},{{ $k->longitude }}" target="_blank" rel="noopener noreferrer" class="btn btn-light btn-sm rounded-circle border p-2 d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;" title="Buka Google Maps">
                                    <i class="fas fa-location-arrow text-teal" style="color: #00897b;"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-12 py-5 text-center bg-white rounded-4 shadow-sm border p-4">
                    <i class="fas fa-church fs-1 text-muted mb-3 opacity-50"></i>
                    <h4 class="fw-bold text-dark">Tidak Ada Data Stasi / Kapela Ditemukan</h4>
                    <p class="text-muted small">Coba ubah kata kunci pencarian atau reset filter.</p>
                    @if(!empty($search))
                        <a href="/profil-kapela" class="btn btn-outline-primary btn-sm rounded-pill px-4">
                            <i class="fas fa-rotate-left me-1"></i> Tampilkan Semua
                        </a>
                    @endif
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if(method_exists($kapela, 'hasPages') && $kapela->hasPages())
            <div class="d-flex justify-content-center mt-5">
                {{ $kapela->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</section>

@endsection
