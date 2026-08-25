@extends('layouts.app')
@section('title', 'Riwayat Pastor Paroki - ' . ($globalNamaParoki ?? 'SIPAROKI'))

@section('content')
<!-- Page Header / Breadcrumb Konoha Style -->
<section class="page-header">
    <div class="container" style="max-width: 1180px; margin: 0 auto; padding: 0 20px;">
        <h1 style="font-size: 2.6rem; font-weight: 700; margin-bottom: 15px; color: #ffffff;">Riwayat Pastor Paroki</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="display: inline-flex; list-style: none; padding: 0; margin: 0 auto; gap: 12px; background: transparent; justify-content: center; align-items: center;">
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/" style="color: white; text-decoration: none; font-weight: 500;">Beranda</a>
                </li>
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/profil" style="color: white; text-decoration: none; font-weight: 500;">Tentang</a>
                </li>
                <li class="breadcrumb-item active" style="background: var(--primary-orange, #ff9800); color: white; padding: 6px 18px; border-radius: 25px; font-size: 0.85rem; font-weight: 600;">
                    Riwayat Pastor
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- Content Section Konoha Style -->
<section class="content-section" style="padding: 60px 0 80px; background: #f4faf9;">
    <div class="container" style="max-width: 1000px; margin: 0 auto; padding: 0 20px;">
        
        <div style="background: #ffffff; border-radius: 15px; padding: 35px 40px; box-shadow: 0 5px 20px rgba(0,0,0,0.06); margin-bottom: 30px;">
            <h3 style="font-size: 1.3rem; font-weight: 700; color: var(--primary-teal, #00897b); margin-bottom: 25px; border-left: 4px solid var(--primary-orange, #ff9800); padding-left: 14px;">
                Gembala Umat dari Masa ke Masa
            </h3>

            <div style="display: flex; flex-direction: column; gap: 18px;">
                @forelse($riwayat ?? [] as $r)
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px 24px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; transition: transform 0.2s, box-shadow 0.2s;">
                        <div style="display: flex; align-items: center; gap: 16px;">
                            <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0;">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <div>
                                <h4 style="font-size: 1.05rem; font-weight: 700; color: #1e293b; margin: 0 0 4px;">
                                    {{ $r->nama_lengkap_gelar ?? \App\Models\MasterPastor::formatNama($r) }}
                                </h4>
                                <span style="font-size: 0.85rem; font-weight: 600; color: var(--primary-teal, #00897b);">
                                    <i class="fas fa-cross" style="font-size: 0.75rem; margin-right: 4px;"></i> {{ $r->jabatan ?? 'Pastor Paroki' }}
                                </span>
                            </div>
                        </div>

                        <div style="text-align: right;">
                            <span style="display: inline-block; background: var(--primary-orange, #ff9800); color: white; padding: 4px 16px; border-radius: 20px; font-size: 0.8rem; font-weight: 700;">
                                {{ $r->periode_mulai ?? $r->tahun_mulai ?? '-' }} &mdash; {{ $r->periode_selesai ?? $r->tahun_selesai ?? 'Sekarang' }}
                            </span>
                            <span style="display: block; font-size: 0.78rem; font-weight: 600; color: #10b981; margin-top: 5px;">
                                {{ $r->status_pelayanan ?? $r->status ?? 'Aktif' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div style="text-align: center; padding: 50px 20px; color: #94a3b8;">
                        <i class="fas fa-user-tie" style="font-size: 3rem; margin-bottom: 12px; display: block; color: #cbd5e1;"></i>
                        <p style="font-size: 1rem; font-weight: 600; margin: 0;">Belum ada data riwayat pastor paroki yang tercatat.</p>
                    </div>
                @endforelse
            </div>
        </div>

    </div>
</section>
@endsection

