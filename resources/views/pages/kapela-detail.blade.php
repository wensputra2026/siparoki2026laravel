@extends('layouts.public')

@section('title', ($kapela->nama_stasi_kapela ?? $kapela->nama_kapela ?? 'Detail Stasi / Kapela') . ' - ' . ($globalNamaParoki ?? 'SIPAROKI'))
@section('description', 'Profil dan informasi wilayah pelayanan ' . ($kapela->nama_stasi_kapela ?? $kapela->nama_kapela ?? 'Stasi') . ' di Paroki ' . ($globalNamaParoki ?? 'SIPAROKI') . '.')

@section('content')
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
@endphp

<!-- Page Header / Breadcrumbs -->
<section class="page-header">
    <div class="container">
        <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
            <span class="st-badge"><i class="fas fa-church me-1"></i> {{ $tipe }}</span>
            @if($kode)
                <span class="badge bg-white text-dark px-3 py-1.5 rounded-pill shadow-sm" style="font-size: 0.75rem;">Kode: {{ $kode }}</span>
            @endif
            <span class="badge {{ $isAktif ? 'bg-success' : 'bg-secondary' }} px-3 py-1.5 rounded-pill shadow-sm" style="font-size: 0.75rem;">
                <i class="fas fa-circle me-1" style="font-size: 8px;"></i> {{ $isAktif ? 'Aktif Melayani' : 'Non-Aktif' }}
            </span>
        </div>
        <h1 class="fw-bold text-white mb-2">{{ $nama }}</h1>
        
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/"><i class="fas fa-home me-1"></i> Beranda</a></li>
                <li class="breadcrumb-separator"><i class="fas fa-chevron-right"></i></li>
                <li class="breadcrumb-item"><a href="/profil-kapela">Stasi &amp; Kapela</a></li>
                <li class="breadcrumb-separator"><i class="fas fa-chevron-right"></i></li>
                <li class="breadcrumb-item active" aria-current="page">{{ $nama }}</li>
            </ol>
        </nav>
    </div>
</section>

<section class="content-section py-5" style="background: #f8fafc;">
    <div class="container">
        <div class="row g-4">
            
            <!-- Left Main Column -->
            <div class="col-lg-8">
                
                <!-- Main Header Card with Photo -->
                <div class="bg-white rounded-4 shadow-sm border p-4 mb-4 overflow-hidden">
                    @if($fotoUrl)
                        <div class="rounded-4 overflow-hidden mb-4 shadow-sm" style="max-height: 380px;">
                            <a href="{{ $fotoUrl }}" data-lightbox="kapela-detail" data-title="{{ $nama }}">
                                <img src="{{ $fotoUrl }}" alt="{{ $nama }}" class="w-100 h-100 object-fit-cover" style="max-height: 380px; object-fit: cover; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
                            </a>
                        </div>
                    @endif

                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pb-3 border-bottom mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 52px; height: 52px; background: rgba(0, 137, 123, 0.1); color: #00897b; font-size: 1.5rem;">
                                <i class="fas fa-church"></i>
                            </div>
                            <div>
                                <h2 class="h4 fw-bold text-dark mb-0">{{ $nama }}</h2>
                                <span class="text-muted small"><i class="fas fa-tag me-1 text-teal" style="color: #00897b;"></i> {{ $tipe }} {{ $kode ? ' • ' . $kode : '' }}</span>
                            </div>
                        </div>
                        @if($mapsUrl)
                            <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer" class="btn text-white rounded-pill px-4 py-2 fw-bold d-inline-flex align-items-center gap-2 shadow-sm" style="background: #00897b; font-size: 0.85rem;">
                                <i class="fas fa-location-arrow"></i> Petunjuk Arah Maps
                            </a>
                        @endif
                    </div>

                    <!-- Highlight Cards -->
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <div class="p-3 bg-light rounded-3 border-start border-4 border-warning h-100">
                                <span class="text-muted d-block small mb-1"><i class="fas fa-cross text-warning me-1"></i> Santo / Santa Pelindung</span>
                                <strong class="text-dark fs-6">{{ $pelindung ?: '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="p-3 bg-light rounded-3 border-start border-4 border-success h-100">
                                <span class="text-muted d-block small mb-1"><i class="fas fa-user-tie text-success me-1"></i> Penanggung Jawab</span>
                                <strong class="text-dark fs-6">{{ $pj ?: '-' }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Lokasi & Wilayah Lengkap -->
                    <div class="p-3.5 rounded-3 border mb-4" style="background: #f8fafc;">
                        <h6 class="fw-bold mb-3 d-flex align-items-center gap-2" style="color: #00897b;">
                            <i class="fas fa-map-location-dot"></i> Alamat &amp; Wilayah Teritorial
                        </h6>
                        @if($alamat)
                            <p class="text-secondary mb-3"><i class="fas fa-location-dot text-danger me-1"></i> {{ $alamat }}</p>
                        @endif

                        <div class="row g-2 small">
                            <div class="col-6 col-md-3">
                                <span class="text-muted">Desa / Kel:</span><br><strong class="text-dark">{{ $kapela->nama_desa ?? '-' }}</strong>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="text-muted">Kecamatan:</span><br><strong class="text-dark">{{ $kapela->nama_kecamatan ?? '-' }}</strong>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="text-muted">Kabupaten:</span><br><strong class="text-dark">{{ $kapela->nama_kabupaten ?? '-' }}</strong>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="text-muted">Provinsi:</span><br><strong class="text-dark">{{ $kapela->nama_provinsi ?? '-' }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Sejarah & Latar Belakang -->
                    @if(!empty($kapela->sejarah))
                        <div class="mb-4">
                            <h5 class="fw-bold mb-3 d-flex align-items-center gap-2" style="color: #00897b;">
                                <i class="fas fa-book-bookmark"></i> Sejarah &amp; Perkembangan
                            </h5>
                            <div class="text-secondary lh-lg" style="font-size: 0.95rem; text-align: justify;">
                                {!! nl2br(e($kapela->sejarah)) !!}
                            </div>
                        </div>
                    @endif

                    <!-- Visi & Misi -->
                    @if(!empty($kapela->visi) || !empty($kapela->misi))
                        <div class="row g-3 mb-4">
                            @if(!empty($kapela->visi))
                                <div class="col-md-6">
                                    <div class="p-3.5 bg-white rounded-3 shadow-sm border h-100" style="border-top: 3px solid #00897b !important;">
                                        <h6 class="fw-bold mb-2" style="color: #00897b;"><i class="fas fa-eye me-1"></i> Visi</h6>
                                        <p class="text-secondary small mb-0 lh-base">{!! nl2br(e($kapela->visi)) !!}</p>
                                    </div>
                                </div>
                            @endif
                            @if(!empty($kapela->misi))
                                <div class="col-md-6">
                                    <div class="p-3.5 bg-white rounded-3 shadow-sm border h-100" style="border-top: 3px solid #ff9800 !important;">
                                        <h6 class="fw-bold mb-2" style="color: #ff9800;"><i class="fas fa-bullseye me-1"></i> Misi</h6>
                                        <p class="text-secondary small mb-0 lh-base">{!! nl2br(e($kapela->misi)) !!}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- Informasi Tambahan -->
                    @if(!empty($kapela->keterangan))
                        <div class="p-3.5 rounded-3" style="background: #f0fdfa; border: 1px solid #ccfbf1;">
                            <h6 class="fw-bold mb-1 d-flex align-items-center gap-2" style="color: #0f766e;">
                                <i class="fas fa-circle-info"></i> Catatan &amp; Informasi Tambahan
                            </h6>
                            <p class="small mb-0 text-secondary lh-base">{{ $kapela->keterangan }}</p>
                        </div>
                    @endif

                </div>

                <!-- Navigation Action -->
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <a href="/profil-kapela" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold d-inline-flex align-items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Kembali ke Daftar Stasi
                    </a>
                    <a href="/peta-kapela" class="btn text-white rounded-pill px-4 py-2 fw-bold d-inline-flex align-items-center gap-2" style="background: #0f172a;">
                        <i class="fas fa-map-location-dot" style="color: #14b8a6;"></i> Buka di Peta Interaktif
                    </a>
                </div>

            </div>

            <!-- Right Sidebar -->
            <div class="col-lg-4">
                
                <!-- Quick Info Box -->
                <div class="bg-white rounded-4 shadow-sm border p-4 mb-4">
                    <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2" style="color: #00897b;">
                        <i class="fas fa-info-circle"></i> Ringkasan Stasi
                    </h5>

                    <ul class="list-unstyled mb-0 d-flex flex-column gap-3">
                        <li class="d-flex align-items-start gap-2">
                            <i class="fas fa-cross mt-1 text-warning"></i>
                            <div>
                                <span class="text-muted d-block small">Pelindung:</span>
                                <strong class="text-dark">{{ $pelindung ?: '-' }}</strong>
                            </div>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="fas fa-user-tie mt-1" style="color: #00897b;"></i>
                            <div>
                                <span class="text-muted d-block small">Penanggung Jawab:</span>
                                <strong class="text-dark">{{ $pj ?: '-' }}</strong>
                            </div>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="fas fa-map-marker-alt mt-1 text-danger"></i>
                            <div>
                                <span class="text-muted d-block small">Lokasi Teritorial:</span>
                                <strong class="text-dark">{{ $wilayahStr ?: ($alamat ?: '-') }}</strong>
                            </div>
                        </li>
                        <li class="d-flex align-items-start gap-2">
                            <i class="fas fa-shield-halved mt-1 text-success"></i>
                            <div>
                                <span class="text-muted d-block small">Status Pelayanan:</span>
                                <span class="badge {{ $isAktif ? 'bg-success' : 'bg-secondary' }}">{{ $isAktif ? 'Aktif' : 'Non-Aktif' }}</span>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Other Kapela List -->
                @if(isset($otherKapela) && $otherKapela->isNotEmpty())
                    <div class="bg-white rounded-4 shadow-sm border p-4">
                        <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2" style="color: #00897b;">
                            <i class="fas fa-church"></i> Stasi / Kapela Lainnya
                        </h5>

                        <div class="d-flex flex-column gap-3">
                            @foreach($otherKapela as $ok)
                                @php
                                    $okId = $ok->id_stasi_kapela ?? $ok->id ?? 1;
                                    $okNama = $ok->nama_stasi_kapela ?? $ok->nama_kapela ?? 'Gereja Stasi';
                                    $okPelindung = $ok->nama_pelindung ?? $ok->pelindung ?? null;
                                @endphp
                                <a href="/profil-kapela/{{ $okId }}" class="p-2.5 rounded-3 border d-flex align-items-center justify-content-between gap-2 text-decoration-none transition-all" style="background: #f8fafc; color: #1e293b;" onmouseover="this.style.background='#e6f4f1'; this.style.borderColor='#00897b';" onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0';">
                                    <div>
                                        <strong class="d-block text-dark small">{{ $okNama }}</strong>
                                        @if($okPelindung)
                                            <span class="text-muted" style="font-size: 0.75rem;"><i class="fas fa-cross me-1"></i> {{ $okPelindung }}</span>
                                        @endif
                                    </div>
                                    <i class="fas fa-chevron-right text-muted small"></i>
                                </a>
                            @endforeach
                        </div>

                        <div class="mt-3 text-center">
                            <a href="/profil-kapela" class="small fw-bold text-decoration-none" style="color: #00897b;">
                                Lihat Semua Stasi ({{ $totalKapela ?? '14' }}) <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                @endif

            </div>

        </div>
    </div>
</section>
@endsection
