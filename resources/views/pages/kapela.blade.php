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

<section class="content-section chapel-page">
    <div class="container">
        <!-- Toolbar Header & Search -->
        <div class="chapel-toolbar d-flex align-items-center justify-content-between flex-wrap gap-3 p-3 bg-white rounded-4 shadow-sm border mb-4">
            <!-- Left: Total info -->
            <div class="d-flex align-items-center gap-3">
                <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 44px; height: 44px; background: rgba(0, 137, 123, 0.1); color: #00897b; font-size: 1.25rem;">
                    <i class="fas fa-church"></i>
                </div>
                <div>
                    <span class="chapel-kicker text-muted small fw-bold text-uppercase d-block" style="letter-spacing: 0.5px; font-size: 0.72rem; margin-bottom: 2px;">Total Wilayah Pelayanan</span>
                    <strong class="fs-5 text-dark fw-bold">{{ method_exists($kapela, 'total') ? $kapela->total() : count($kapelaItems) }} <span class="fs-6 fw-normal text-muted">Stasi &amp; Kapela</span></strong>
                </div>
            </div>

            <!-- Center: Modern Search Form -->
            <form action="/profil-kapela" method="GET" class="d-flex align-items-center my-1" style="flex: 1; max-width: 440px; min-width: 260px;">
                <div class="position-relative w-100 d-flex align-items-center">
                    <span class="position-absolute ps-3" style="left: 0; pointer-events: none; color: #94a3b8;">
                        <i class="fas fa-search"></i>
                    </span>
                    <input 
                        type="text" 
                        name="q" 
                        value="{{ $search ?? '' }}" 
                        class="form-control rounded-pill shadow-none" 
                        style="padding-left: 38px; padding-right: 78px; height: 42px; border: 1.5px solid #cbd5e1; font-size: 0.88rem; background: #f8fafc; color: #0f172a;" 
                        placeholder="Cari nama stasi, pelindung, lokasi..." 
                        aria-label="Cari stasi atau kapela"
                    >
                    @if(!empty($search))
                        <a href="/profil-kapela" class="position-absolute d-flex align-items-center justify-content-center" style="right: 70px; width: 24px; height: 24px; text-decoration: none; color: #94a3b8;" title="Reset pencarian">
                            <i class="fas fa-circle-xmark"></i>
                        </a>
                    @endif
                    <button type="submit" class="btn text-white position-absolute rounded-pill px-3 fw-bold d-flex align-items-center justify-content-center" style="right: 4px; height: 34px; background: #00897b; border: none; font-size: 0.8rem;">
                        Cari
                    </button>
                </div>
            </form>

            <!-- Right: Map Button -->
            <a href="/peta-kapela" class="btn text-white rounded-pill px-3.5 py-2 fw-bold d-inline-flex align-items-center gap-2 shadow-sm" style="background: #0f172a; font-size: 0.85rem; text-decoration: none;">
                <i class="fas fa-map-location-dot" style="color: #14b8a6;"></i>
                <span>Lihat Peta Interaktif</span>
            </a>
        </div>

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
