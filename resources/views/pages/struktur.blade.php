@extends('layouts.app')
@section('title', 'Struktur Organisasi DPP - ' . ($globalNamaParoki ?? 'SIPAROKI'))

@section('content')
<!-- Page Header / Breadcrumb Konoha Style -->
<section class="page-header">
    <div class="container">
        <h1 style="font-size: 2.6rem; font-weight: 700; margin-bottom: 15px; color: #ffffff;">Struktur Organisasi</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="display: inline-flex; list-style: none; padding: 0; margin: 0 auto; gap: 12px; background: transparent; justify-content: center; align-items: center;">
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/" style="color: white; text-decoration: none; font-weight: 500;">Beranda</a>
                </li>
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/profil" style="color: white; text-decoration: none; font-weight: 500;">Tentang</a>
                </li>
                <li class="breadcrumb-item active" style="background: var(--primary-orange, #ff9800); color: white; padding: 6px 18px; border-radius: 25px; font-size: 0.85rem; font-weight: 600;">
                    Struktur DPP
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- Content Section Konoha Style -->
<section class="content-section" style="padding: 60px 0 80px; background: #f4faf9;">
    <div class="container">
        
        <div style="background: #ffffff; border-radius: 15px; padding: 35px 40px; box-shadow: 0 5px 20px rgba(0,0,0,0.06); margin-bottom: 30px;">
            <h3 style="font-size: 1.3rem; font-weight: 700; color: var(--primary-teal, #00897b); margin-bottom: 25px; border-left: 4px solid var(--primary-orange, #ff9800); padding-left: 14px;">
                Dewan Pastoral Paroki (DPP) & Dewan Keuangan Paroki (DKP)
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                @forelse($dpp ?? [] as $d)
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 14px;">
                        <div style="width: 46px; height: 46px; border-radius: 50%; overflow: hidden; border: 2px solid #cbd5e1; flex-shrink: 0; background: #fff;">
                            <img src="{{ !empty($d->foto) ? (str_starts_with($d->foto, 'http') ? $d->foto : asset('uploads/direktori_dpp/' . $d->foto)) : asset('images/avatar-default.jpg') }}" alt="{{ $d->nama ?? 'Pengurus DPP' }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.onerror=null;this.src='{{ asset('images/avatar-default.jpg') }}';">
                        </div>
                        <div>
                            <h5 style="font-size: 0.95rem; font-weight: 700; color: #1e293b; margin: 0 0 3px;">{{ $d->nama ?? 'Pengurus DPP' }}</h5>
                            <span style="font-size: 0.8rem; font-weight: 600; color: var(--primary-teal, #00897b);">{{ $d->jabatan ?? 'Anggota DPP' }}</span>
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1; background: #f8fafc; border-radius: 12px; padding: 30px; text-align: center; color: #64748b;">
                        <p style="margin: 0; font-size: 0.95rem; line-height: 1.6;">
                            Struktur Dewan Pastoral Paroki (DPP) dan Dewan Keuangan Paroki (DKP) periode aktif dipimpin oleh Pastor Paroki bersama seksi-seksi liturgi, pewartaan, persekutuan, dan pelayanan kasih.
                        </p>
                    </div>
                @endforelse
            </div>

            <!-- Share Buttons -->
            @include('partials.share-buttons', ['title' => 'Struktur Organisasi DPP - ' . ($globalNamaParoki ?? 'Paroki')])
        </div>

    </div>
</section>
@endsection

