@extends('layouts.app')

@section('title', 'Cek Data Sensus & Status Sakramen Umat - ' . ($globalNamaParoki ?? 'SIPAROKI'))
@section('description', 'Pengecekan mandiri data kependudukan gerejani, KUB, dan status kelengkapan sakramen inisiasi umat.')

@push('styles')
<style>
    .search-card-konoha {
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 35px rgba(0, 77, 64, 0.08);
        border: 1px solid #e2e8f0;
        overflow: hidden;
        margin-bottom: 30px;
    }
    .search-card-header {
        background: linear-gradient(135deg, #f0fdfa 0%, #e6fffa 100%);
        border-bottom: 1px solid #ccfbf1;
        padding: 22px 28px;
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .search-card-header .icon-circle {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: #00897b;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.35rem;
        box-shadow: 0 4px 10px rgba(0, 137, 123, 0.25);
        flex-shrink: 0;
    }
    .search-card-header h2 {
        font-size: 1.25rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
    }
    .search-card-header p {
        font-size: 0.85rem;
        color: #64748b;
        margin: 2px 0 0;
    }
    .search-card-body {
        padding: 30px 32px;
    }
    @media (max-width: 576px) {
        .search-card-body {
            padding: 22px 18px;
        }
        .search-card-header {
            padding: 18px 20px;
        }
    }
    .form-group-konoha {
        margin-bottom: 20px;
    }
    .form-group-konoha label {
        font-weight: 700;
        font-size: 0.9rem;
        color: #1e293b;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .input-konoha-wrap {
        display: flex;
        align-items: center;
        border: 2px solid #cbd5e1;
        border-radius: 12px;
        background: #ffffff;
        transition: all 0.2s ease;
        overflow: hidden;
    }
    .input-konoha-wrap:focus-within {
        border-color: #00897b;
        box-shadow: 0 0 0 4px rgba(0, 137, 123, 0.12);
    }
    .input-konoha-wrap .icon-slot {
        width: 46px;
        height: 46px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8fafc;
        color: #00897b;
        font-size: 1.1rem;
        border-right: 1px solid #e2e8f0;
        flex-shrink: 0;
    }
    .input-konoha-wrap .form-control {
        border: none !important;
        box-shadow: none !important;
        padding: 11px 16px;
        font-size: 1rem;
        font-weight: 600;
        color: #0f172a;
        background: transparent;
    }
    .btn-konoha-search {
        background: linear-gradient(135deg, #00897b 0%, #004d40 100%);
        color: #ffffff;
        border: none;
        border-radius: 12px;
        padding: 13px 28px;
        font-weight: 700;
        font-size: 1rem;
        width: 100%;
        transition: all 0.25s ease;
        box-shadow: 0 4px 14px rgba(0, 137, 123, 0.28);
    }
    .btn-konoha-search:hover {
        background: linear-gradient(135deg, #00796b 0%, #00332c 100%);
        color: #ffffff;
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 137, 123, 0.38);
    }
    .search-card-footer {
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        padding: 12px 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 18px;
        flex-wrap: wrap;
        font-size: 0.8rem;
        color: #64748b;
    }
    .umat-result-card {
        background: #ffffff;
        border-radius: 20px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
        overflow: hidden;
        margin-top: 25px;
    }
    .result-card-header {
        background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%);
        border-bottom: 2px solid #99f6e4;
        padding: 24px 28px;
    }
    .bento-info-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 18px 20px;
        height: 100%;
    }
    .bento-info-box h5 {
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .sakramen-box {
        background: #ffffff;
        border-radius: 14px;
        padding: 15px;
        border: 1px solid #e2e8f0;
        height: 100%;
    }
    .sakramen-box.sudah {
        background: #f0fdf4;
        border-color: #86efac;
    }
    .sakramen-box.belum {
        background: #f8fafc;
        border-color: #e2e8f0;
        opacity: 0.9;
    }
    .cta-wa-card {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        border-radius: 16px;
        padding: 22px 26px;
        color: #ffffff;
        box-shadow: 0 6px 20px rgba(5, 150, 105, 0.25);
    }
    .btn-wa-link {
        background: #ffffff;
        color: #047857;
        font-weight: 800;
        border-radius: 12px;
        padding: 12px 22px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        transition: all 0.25s ease;
    }
    .btn-wa-link:hover {
        background: #ecfdf5;
        color: #065f46;
        transform: translateY(-2px);
    }
    @media print {
        .navbar, .page-header, .search-card-konoha, .cta-wa-card, .btn-no-print, footer, .top-bar {
            display: none !important;
        }
        .umat-result-card {
            box-shadow: none !important;
            border: 1px solid #000 !important;
            margin-top: 0 !important;
        }
    }
</style>
@endpush

@section('content')
<!-- Page Header / Breadcrumb Konoha Style -->
<section class="page-header">
    <div class="container text-center">
        <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 12px; color: #ffffff;">Cek Data Sensus &amp; Sakramen Umat</h1>
        <p style="font-size: 1rem; color: rgba(255,255,255,0.85); margin-bottom: 18px; max-width: 650px; margin-left: auto; margin-right: auto;">
            Pengecekan mandiri data kependudukan gerejani, wilayah KUB, dan status kelengkapan inisiasi sakramen.
        </p>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="display: inline-flex; list-style: none; padding: 0; margin: 0 auto; gap: 10px; background: transparent; justify-content: center; align-items: center; flex-wrap: wrap;">
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.2); padding: 5px 16px; border-radius: 25px; font-size: 0.82rem;">
                    <a href="/" style="color: white; text-decoration: none; font-weight: 500;"><i class="fa-solid fa-house me-1"></i> Beranda</a>
                </li>
                <li class="breadcrumb-item active" style="background: var(--primary-orange, #ff9800); color: white; padding: 5px 16px; border-radius: 25px; font-size: 0.82rem; font-weight: 700;">
                    Cek Data Umat
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- Content Section Konoha Style -->
<section class="content-section" style="padding: 50px 0 80px; background: #f4faf9;">
    <div class="container">
        
        <div class="row justify-content-center">
            <div class="col-lg-8 col-xl-7">
                
                <!-- Card Pencarian NIK -->
                <div class="search-card-konoha">
                    <div class="search-card-header">
                        <div class="icon-circle">
                            <i class="fa-solid fa-id-card"></i>
                        </div>
                        <div>
                            <h2>Pencarian Data Umat</h2>
                            <p>Masukkan 16 digit Nomor Induk Kependudukan (NIK) Anda.</p>
                        </div>
                    </div>

                    <div class="search-card-body">
                        @if(session('info'))
                            <div class="alert alert-info alert-dismissible fade show rounded-3 mb-3 py-2.5 px-3 small" role="alert">
                                <i class="fa-solid fa-circle-info me-1.5"></i> {{ session('info') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="padding: 0.75rem 1rem;"></button>
                            </div>
                        @endif

                        @if(!empty($errorMessage))
                            <div class="alert alert-danger rounded-3 mb-3 py-2.5 px-3 small">
                                <i class="fa-solid fa-circle-exclamation me-1.5"></i> {{ $errorMessage }}
                            </div>
                        @endif

                        <form action="{{ route('cek-data-umat') }}" method="POST" id="formCekNik">
                            @csrf
                            
                            <!-- Input NIK -->
                            <div class="form-group-konoha">
                                <label for="nik">
                                    <span>Nomor Induk Kependudukan (NIK) <span class="text-danger">*</span></span>
                                    <span class="badge bg-light text-secondary border font-monospace fw-normal" style="font-size: 0.72rem;">16 Digit</span>
                                </label>
                                <div class="input-konoha-wrap">
                                    <div class="icon-slot">
                                        <i class="fa-solid fa-id-card"></i>
                                    </div>
                                    <input type="text" 
                                           class="form-control font-monospace" 
                                           id="nik" 
                                           name="nik" 
                                           value="{{ $nikInput ?? '' }}" 
                                           placeholder="Contoh: 5302011204820002" 
                                           maxlength="16" 
                                           inputmode="numeric" 
                                           pattern="[0-9]*" 
                                           required>
                                </div>
                                <div class="form-text text-muted small mt-1">
                                    Pastikan 16 digit NIK sesuai dengan yang tertera pada KTP atau Kartu Keluarga (KK).
                                </div>
                            </div>

                            <!-- Input Tanggal Lahir (Indonesia dd/mm/yyyy) -->
                            <div class="form-group-konoha">
                                <label for="tanggal_lahir">
                                    <span>Tanggal Lahir <span class="text-muted fw-normal font-monospace" style="font-size: 0.75rem;">(Opsional)</span></span>
                                    <span class="badge bg-light text-teal border font-monospace" style="font-size: 0.72rem; color: #00897b;">dd/mm/yyyy</span>
                                </label>
                                <div class="input-konoha-wrap">
                                    <div class="icon-slot" style="color: #64748b;">
                                        <i class="fa-solid fa-calendar-days"></i>
                                    </div>
                                    <input type="text" 
                                           class="form-control font-monospace" 
                                           id="tanggal_lahir" 
                                           name="tanggal_lahir" 
                                           value="{{ $tglLahirDisplay ?? ($tglLahirInput ?? '') }}" 
                                           placeholder="dd/mm/yyyy (Contoh: 12/04/1982)" 
                                           maxlength="10"
                                           autocomplete="off">
                                </div>
                                <div class="form-text text-muted small mt-1">
                                    Format Indonesia: <strong>dd/mm/yyyy</strong> (Hari/Bulan/Tahun). Digunakan untuk verifikasi ganda.
                                </div>
                            </div>

                            <button type="submit" class="btn-konoha-search mt-2">
                                <i class="fa-solid fa-magnifying-glass me-1.5"></i> Periksa Data Sensus
                            </button>
                        </form>
                    </div>

                    <div class="search-card-footer">
                        <span><i class="fa-solid fa-shield-halved text-success me-1"></i> Data Terlindungi</span>
                        <span><i class="fa-solid fa-user-lock text-primary me-1"></i> NIK Disamarkan</span>
                        <span><i class="fa-solid fa-church text-warning me-1"></i> Sensus Resmi Paroki</span>
                    </div>
                </div>

                @if(!$searchPerformed)
                    <!-- Panduan Ringkas 3 Langkah -->
                    <div class="row g-3 text-center">
                        <div class="col-md-4">
                            <div class="p-3 bg-white rounded-3 border h-100 shadow-sm">
                                <div class="fw-bold text-dark small mb-1"><span class="badge bg-teal text-white rounded-circle me-1" style="background: #00897b;">1</span> Masukkan NIK</div>
                                <p class="text-muted small mb-0" style="font-size: 0.8rem;">Ketik 16 digit NIK dari KTP atau Kartu Keluarga Anda.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-white rounded-3 border h-100 shadow-sm">
                                <div class="fw-bold text-dark small mb-1"><span class="badge bg-teal text-white rounded-circle me-1" style="background: #00897b;">2</span> Cek Sakramen</div>
                                <p class="text-muted small mb-0" style="font-size: 0.8rem;">Lihat status Baptis, Komuni, Krisma, dan KUB Anda.</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-white rounded-3 border h-100 shadow-sm">
                                <div class="fw-bold text-dark small mb-1"><span class="badge bg-teal text-white rounded-circle me-1" style="background: #00897b;">3</span> Hubungi Admin</div>
                                <p class="text-muted small mb-0" style="font-size: 0.8rem;">Laporkan perbaikan data langsung via WhatsApp.</p>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>

        @if($searchPerformed)
            <div class="row justify-content-center mt-3">
                <div class="col-lg-10 col-xl-9">
                    @if($umat)
                        <!-- HASIL DATA DITEMUKAN -->
                        <div class="umat-result-card">
                            <!-- Header Hasil -->
                            <div class="result-card-header">
                                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <div style="width: 58px; height: 58px; border-radius: 14px; background: linear-gradient(135deg, #00897b 0%, #004d40 100%); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; font-weight: 800; box-shadow: 0 4px 12px rgba(0,137,123,0.3); flex-shrink: 0;">
                                            {{ strtoupper(substr($umat->nama_lengkap ?? 'U', 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <h3 class="mb-0 fw-bold text-dark" style="font-size: 1.3rem;">
                                                    {{ $umat->nama_lengkap }}
                                                </h3>
                                                @if($umat->nama_baptis)
                                                    <span class="badge px-2.5 py-1 rounded-pill fw-semibold" style="background: #ccfbf1; color: #0f766e; border: 1px solid #99f6e4; font-size: 0.8rem;">
                                                        <i class="fa-solid fa-cross me-1"></i> {{ $umat->nama_baptis }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-muted small mt-1 d-flex align-items-center gap-3 flex-wrap">
                                                <span><i class="fa-solid fa-id-card me-1" style="color: #00897b;"></i> NIK: <strong class="font-monospace">{{ $umat->masked_nik ?? $nikInput }}</strong></span>
                                                @if(!empty($umat->niu))
                                                    <span><i class="fa-solid fa-hashtag me-1 text-muted"></i> NIU: {{ $umat->niu }}</span>
                                                @endif
                                                <span>
                                                    <i class="fa-solid fa-circle-check text-success me-1"></i>
                                                    Status: <span class="badge bg-success text-white px-2 py-0.5 rounded-pill" style="font-size: 0.72rem;">{{ $umat->status_umat ?? ($umat->status_aktif ? 'Terdaftar & Aktif' : 'Non-Aktif') }}</span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 btn-no-print">
                                        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-1.5 fw-semibold">
                                            <i class="fa-solid fa-print me-1"></i> Cetak Ringkasan
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Body Hasil Data -->
                            <div class="p-4">
                                <div class="row g-3">
                                    <!-- 1. Identitas Pribadi -->
                                    <div class="col-md-6">
                                        <div class="bento-info-box">
                                            <h5>
                                                <i class="fa-solid fa-user" style="color: #00897b;"></i> Identitas Pribadi
                                            </h5>
                                            <table class="table table-sm table-borderless mb-0 small">
                                                <tbody>
                                                    <tr>
                                                        <td class="text-muted text-nowrap" style="width: 40%;">Jenis Kelamin</td>
                                                        <td class="fw-bold text-dark">: {{ $umat->jenis_kelamin ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted text-nowrap">Tempat Lahir</td>
                                                        <td class="fw-bold text-dark">: {{ $umat->tempat_lahir ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted text-nowrap">Tanggal Lahir</td>
                                                        <td class="fw-bold text-dark">: 
                                                            @if($umat->tanggal_lahir)
                                                                <span class="font-monospace fw-bold" style="color: #00796b;">
                                                                    {{ \Carbon\Carbon::parse($umat->tanggal_lahir)->format('d/m/Y') }}
                                                                </span>
                                                                <span class="text-muted fw-normal ms-1">({{ \Carbon\Carbon::parse($umat->tanggal_lahir)->translatedFormat('d F Y') }})</span>
                                                                @if($umat->usia)
                                                                    <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $umat->usia }} thn</span>
                                                                @endif
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted text-nowrap">Golongan Darah</td>
                                                        <td class="fw-bold text-dark">: {{ $umat->golongan_darah ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted text-nowrap">Pendidikan</td>
                                                        <td class="fw-bold text-dark">: {{ $umat->pendidikan ?? $umat->pendidikan_saat_ini ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted text-nowrap">Pekerjaan</td>
                                                        <td class="fw-bold text-dark">: {{ $umat->pekerjaan ?? '-' }}</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- 2. Teritorial Gerejani (KUB & Kapela) -->
                                    <div class="col-md-6">
                                        <div class="bento-info-box">
                                            <h5>
                                                <i class="fa-solid fa-church" style="color: #00897b;"></i> Wilayah Pastoral Gereja
                                            </h5>
                                            <table class="table table-sm table-borderless mb-0 small">
                                                <tbody>
                                                    <tr>
                                                        <td class="text-muted text-nowrap" style="width: 40%;">Paroki</td>
                                                        <td class="fw-bold text-dark">: {{ $namaParoki ?? 'St. Vinsensius a Paulo Benlutu' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted text-nowrap">Stasi / Kapela</td>
                                                        <td class="fw-bold" style="color: #00897b;">: {{ $umat->kapela?->nama_kapela ?? $umat->kapela?->nama_stasi_kapela ?? 'Gereja Pusat Paroki' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted text-nowrap">Wilayah Rohani</td>
                                                        <td class="fw-bold text-dark">: {{ $umat->effective_wilayah?->nama_wilayah ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted text-nowrap">Lingkungan</td>
                                                        <td class="fw-bold text-dark">: {{ $umat->effective_lingkungan?->nama_lingkungan ?? '-' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td class="text-muted text-nowrap">Komunitas (KUB)</td>
                                                        <td class="fw-bold text-dark">: 
                                                            <span class="badge" style="background: #00897b; color: white;">
                                                                {{ $umat->effective_kub?->nama_kub ?? '-' }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <!-- 3. Keluarga & Kartu Keluarga (KK) -->
                                    <div class="col-12">
                                        <div class="bento-info-box">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <h5 class="mb-0">
                                                    <i class="fa-solid fa-people-roof" style="color: #00897b;"></i> Data Keluarga &amp; Hubungan KK
                                                </h5>
                                                @if(!empty($umat->no_kk_kw) || !empty($umat->kk?->no_kk_kw))
                                                    <span class="badge bg-secondary-subtle text-dark border fw-semibold small">
                                                        No. KK Katolik: {{ $umat->no_kk_kw ?? $umat->kk?->no_kk_kw }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="row g-2 pt-2">
                                                <div class="col-md-4">
                                                    <div class="text-muted small">Kepala Keluarga:</div>
                                                    <div class="fw-bold text-dark">{{ $umat->nama_pemilik_kk ?? $umat->kk?->nama_lahir_pemilik ?? '-' }}</div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-muted small">Hubungan dalam Keluarga:</div>
                                                    <div class="fw-bold text-dark">
                                                        <span class="badge bg-primary-subtle text-primary border px-2 py-0.5" style="font-size: 0.78rem;">
                                                            {{ $umat->hubungan_keluarga ?? 'Anggota Keluarga' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="text-muted small">Status Perkawinan:</div>
                                                    <div class="fw-bold text-dark">{{ $umat->status_menikah ?? $umat->status_perkawinan ?? '-' }}</div>
                                                </div>
                                            </div>

                                            @if($anggotaKeluarga->isNotEmpty())
                                                <hr class="my-2.5">
                                                <div class="small fw-bold text-muted mb-1.5">
                                                    <i class="fa-solid fa-users me-1"></i> Anggota Keluarga Lain dalam KK yang Sama:
                                                </div>
                                                <div class="d-flex flex-wrap gap-1.5">
                                                    @foreach($anggotaKeluarga as $ak)
                                                        <div class="badge bg-light text-dark border p-1.5 text-start" style="font-weight: 500; font-size: 0.78rem;">
                                                            <strong class="d-block text-dark">{{ $ak->nama_lengkap }}</strong>
                                                            <span class="text-muted small">{{ $ak->hubungan_keluarga ?? 'Anggota' }} • {{ $ak->masked_nik }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- 4. Status Penerimaan Sakramen Inisiasi -->
                                    <div class="col-12">
                                        <div class="d-flex align-items-center justify-content-between mb-2 mt-2">
                                            <h5 class="mb-0 fw-bold text-dark" style="font-size: 0.95rem;">
                                                <i class="fa-solid fa-hands-praying me-1" style="color: #00897b;"></i> Status Sakramen Inisiasi &amp; Kanonik
                                            </h5>
                                            <span class="badge bg-light text-secondary border small font-monospace">dd/mm/yyyy</span>
                                        </div>
                                        <div class="row g-2.5">
                                            <!-- Sakramen Baptis -->
                                            @php
                                                $sudahBaptis = !empty($umat->tgl_baptis) || strtolower($umat->status_baptis ?? '') === 'sudah';
                                            @endphp
                                            <div class="col-md-6 col-lg-3">
                                                <div class="sakramen-box {{ $sudahBaptis ? 'sudah' : 'belum' }}">
                                                    <div class="d-flex align-items-center justify-content-between mb-1.5">
                                                        <span class="fw-bold text-dark small">Sakramen Baptis</span>
                                                        @if($sudahBaptis)
                                                            <i class="fa-solid fa-circle-check text-success fs-6"></i>
                                                        @else
                                                            <i class="fa-solid fa-clock text-muted fs-6"></i>
                                                        @endif
                                                    </div>
                                                    <div class="small">
                                                        Status: <strong class="{{ $sudahBaptis ? 'text-success' : 'text-muted' }}">{{ $sudahBaptis ? 'Sudah Menerima' : 'Belum Tercatat' }}</strong>
                                                    </div>
                                                    @if($umat->tgl_baptis)
                                                        <div class="small text-dark mt-1">
                                                            Tgl: <strong class="font-monospace">{{ \Carbon\Carbon::parse($umat->tgl_baptis)->format('d/m/Y') }}</strong>
                                                        </div>
                                                    @endif
                                                    @if($umat->paroki_baptis)
                                                        <div class="small text-muted text-truncate" title="{{ $umat->paroki_baptis }}">
                                                            Di: {{ $umat->paroki_baptis }}
                                                        </div>
                                                    @endif
                                                    @if($umat->buku_baptis_no || $umat->buku_baptis_vol)
                                                        <div class="small text-muted mt-0.5 font-monospace" style="font-size: 0.72rem;">
                                                            Reg: {{ $umat->buku_baptis_vol ? 'Vol.'.$umat->buku_baptis_vol.' ' : '' }}{{ $umat->buku_baptis_hal ? 'Hal.'.$umat->buku_baptis_hal.' ' : '' }}{{ $umat->buku_baptis_no ? 'No.'.$umat->buku_baptis_no : '' }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Sakramen Komuni Pertama -->
                                            @php
                                                $sudahKomuni = !empty($umat->tgl_komuni_1);
                                            @endphp
                                            <div class="col-md-6 col-lg-3">
                                                <div class="sakramen-box {{ $sudahKomuni ? 'sudah' : 'belum' }}">
                                                    <div class="d-flex align-items-center justify-content-between mb-1.5">
                                                        <span class="fw-bold text-dark small">Komuni Pertama</span>
                                                        @if($sudahKomuni)
                                                            <i class="fa-solid fa-circle-check text-success fs-6"></i>
                                                        @else
                                                            <i class="fa-solid fa-clock text-muted fs-6"></i>
                                                        @endif
                                                    </div>
                                                    <div class="small">
                                                        Status: <strong class="{{ $sudahKomuni ? 'text-success' : 'text-muted' }}">{{ $sudahKomuni ? 'Sudah Menerima' : 'Belum Tercatat' }}</strong>
                                                    </div>
                                                    @if($umat->tgl_komuni_1)
                                                        <div class="small text-dark mt-1">
                                                            Tgl: <strong class="font-monospace">{{ \Carbon\Carbon::parse($umat->tgl_komuni_1)->format('d/m/Y') }}</strong>
                                                        </div>
                                                    @endif
                                                    @if($umat->paroki_komuni_1)
                                                        <div class="small text-muted text-truncate" title="{{ $umat->paroki_komuni_1 }}">
                                                            Di: {{ $umat->paroki_komuni_1 }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Sakramen Krisma -->
                                            @php
                                                $sudahKrisma = !empty($umat->tgl_krisma);
                                            @endphp
                                            <div class="col-md-6 col-lg-3">
                                                <div class="sakramen-box {{ $sudahKrisma ? 'sudah' : 'belum' }}">
                                                    <div class="d-flex align-items-center justify-content-between mb-1.5">
                                                        <span class="fw-bold text-dark small">Sakramen Krisma</span>
                                                        @if($sudahKrisma)
                                                            <i class="fa-solid fa-circle-check text-success fs-6"></i>
                                                        @else
                                                            <i class="fa-solid fa-clock text-muted fs-6"></i>
                                                        @endif
                                                    </div>
                                                    <div class="small">
                                                        Status: <strong class="{{ $sudahKrisma ? 'text-success' : 'text-muted' }}">{{ $sudahKrisma ? 'Sudah Menerima' : 'Belum Tercatat' }}</strong>
                                                    </div>
                                                    @if($umat->tgl_krisma)
                                                        <div class="small text-dark mt-1">
                                                            Tgl: <strong class="font-monospace">{{ \Carbon\Carbon::parse($umat->tgl_krisma)->format('d/m/Y') }}</strong>
                                                        </div>
                                                    @endif
                                                    @if($umat->paroki_krisma)
                                                        <div class="small text-muted text-truncate" title="{{ $umat->paroki_krisma }}">
                                                            Di: {{ $umat->paroki_krisma }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Sakramen Perkawinan -->
                                            @php
                                                $sudahNikah = !empty($umat->tgl_perkawinan) || in_array(strtolower($umat->status_menikah ?? ''), ['menikah gereja', 'kawin', 'menikah katolik', 'menikah']);
                                            @endphp
                                            <div class="col-md-6 col-lg-3">
                                                <div class="sakramen-box {{ $sudahNikah ? 'sudah' : 'belum' }}">
                                                    <div class="d-flex align-items-center justify-content-between mb-1.5">
                                                        <span class="fw-bold text-dark small">Sakramen Nikah</span>
                                                        @if($sudahNikah)
                                                            <i class="fa-solid fa-circle-check text-success fs-6"></i>
                                                        @else
                                                            <i class="fa-solid fa-clock text-muted fs-6"></i>
                                                        @endif
                                                    </div>
                                                    <div class="small">
                                                        Status: <strong class="{{ $sudahNikah ? 'text-success' : 'text-muted' }}">{{ $sudahNikah ? 'Menikah Katolik' : 'Belum / Lajang' }}</strong>
                                                    </div>
                                                    @if($umat->tgl_perkawinan)
                                                        <div class="small text-dark mt-1">
                                                            Tgl: <strong class="font-monospace">{{ \Carbon\Carbon::parse($umat->tgl_perkawinan)->format('d/m/Y') }}</strong>
                                                        </div>
                                                    @endif
                                                    @if($umat->nama_pasangan)
                                                        <div class="small text-muted text-truncate" title="Pasangan: {{ $umat->nama_pasangan }}">
                                                            Psg: {{ $umat->nama_pasangan }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Box Bantuan WhatsApp -->
                                <div class="cta-wa-card mt-4 btn-no-print">
                                    <div class="row align-items-center g-3">
                                        <div class="col-lg-8">
                                            <h4 class="fw-bold mb-1 d-flex align-items-center gap-2" style="font-size: 1.15rem;">
                                                <i class="fa-brands fa-whatsapp fs-3"></i> Ada Data yang Perlu Diperbaiki atau Berubah?
                                            </h4>
                                            <p class="mb-0 text-white-50 small">
                                                Jika ada kesalahan ejaan nama, mutasi KUB, atau catatan sakramen yang belum tercatat, silakan hubungi Sekretariat Paroki melalui WhatsApp. Data akan diverifikasi dan diperbarui oleh admin.
                                            </p>
                                        </div>
                                        <div class="col-lg-4 text-lg-end">
                                            <a href="{{ $waAdminUrl }}" target="_blank" rel="noopener noreferrer" class="btn-wa-link">
                                                <i class="fa-brands fa-whatsapp fs-5 text-success"></i> Hubungi Admin Paroki
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <!-- HASIL DATA TIDAK DITEMUKAN -->
                        <div class="umat-result-card text-center p-4 p-md-5">
                            <div style="width: 65px; height: 65px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 1.8rem; margin: 0 auto 14px;">
                                <i class="fa-solid fa-user-xmark"></i>
                            </div>
                            <h3 class="fw-bold text-dark mb-2" style="font-size: 1.25rem;">Data Umat Tidak Ditemukan</h3>
                            <p class="text-muted mx-auto mb-3 small" style="max-width: 500px;">
                                NIK <strong class="font-monospace">{{ $nikInput }}</strong> belum terdaftar dalam pangkalan data sensus jemaat Paroki.
                            </p>

                            <div class="card bg-light border-0 p-3 mx-auto text-start mb-4" style="max-width: 480px; border-radius: 12px;">
                                <div class="fw-bold text-dark small mb-1.5"><i class="fa-solid fa-lightbulb text-warning me-1"></i> Petunjuk:</div>
                                <ul class="small text-muted mb-0 ps-3" style="line-height: 1.6; font-size: 0.82rem;">
                                    <li>Pastikan 16 digit NIK yang Anda ketik sudah sesuai dengan KTP / Kartu Keluarga.</li>
                                    <li>Jika Anda warga baru atau belum terdata dalam sensus, silakan hubungi Ketua KUB setempat.</li>
                                    <li>Anda juga dapat menghubungi Sekretariat Paroki dengan membawa dokumen pendukung.</li>
                                </ul>
                            </div>

                            <div class="d-flex align-items-center justify-content-center gap-2 flex-wrap">
                                <a href="{{ $waAdminUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-success px-4 py-2 rounded-pill fw-bold small">
                                    <i class="fa-brands fa-whatsapp me-1.5"></i> Hubungi Sekretariat Paroki
                                </a>
                                <a href="{{ route('cek-data-umat') }}" class="btn btn-outline-secondary px-4 py-2 rounded-pill fw-semibold small">
                                    <i class="fa-solid fa-rotate-left me-1"></i> Coba NIK Lain
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

    </div>
</section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Auto-slash formatting for Indonesian date: dd/mm/yyyy
        const tglInput = document.getElementById('tanggal_lahir');
        if (tglInput) {
            tglInput.addEventListener('input', function (e) {
                // If user is deleting/backspacing, let them delete naturally
                if (e.inputType === 'deleteContentBackward' || e.inputType === 'deleteContentForward') return;

                let val = this.value.replace(/[^0-9]/g, '');
                if (val.length > 8) val = val.slice(0, 8);

                let formatted = '';
                if (val.length > 4) {
                    formatted = val.slice(0, 2) + '/' + val.slice(2, 4) + '/' + val.slice(4);
                } else if (val.length > 2) {
                    formatted = val.slice(0, 2) + '/' + val.slice(2);
                } else {
                    formatted = val;
                }
                this.value = formatted;
            });
        }

        // NIK auto-filter: numbers only
        const nikInput = document.getElementById('nik');
        if (nikInput) {
            nikInput.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
    });
</script>
@endpush
