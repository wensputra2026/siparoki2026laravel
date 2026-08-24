@extends('layouts.app')
@section('title', 'Statistik Paroki - ' . ($globalNamaParoki ?? 'SIPAROKI'))

@section('content')
<!-- Page Header / Breadcrumb Konoha Style -->
<section class="page-header" style="background: linear-gradient(rgba(10, 30, 25, 0.75), rgba(10, 30, 25, 0.85)), url('{{ $globalHeroBg ?? '/assets/uploads/profil/hero_bg.jpg' }}') center/cover; padding: 90px 0 50px; color: white; text-align: center;">
    <div class="container" style="max-width: 1180px; margin: 0 auto; padding: 0 20px;">
        <h1 style="font-size: 2.6rem; font-weight: 700; margin-bottom: 15px; color: #ffffff;">Statistik Umat Paroki</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="display: inline-flex; list-style: none; padding: 0; margin: 0 auto; gap: 12px; background: transparent; justify-content: center; align-items: center;">
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/" style="color: white; text-decoration: none; font-weight: 500;">Beranda</a>
                </li>
                <li class="breadcrumb-item active" style="background: var(--primary-orange, #ff9800); color: white; padding: 6px 18px; border-radius: 25px; font-size: 0.85rem; font-weight: 600;">
                    Statistik
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- Content Section Konoha Style -->
<section class="content-section" style="padding: 60px 0 80px; background: #f4faf9;">
    <div class="container" style="max-width: 1100px; margin: 0 auto; padding: 0 20px;">
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 24px;">
            <div style="background: #ffffff; border-radius: 15px; padding: 30px 20px; text-align: center; box-shadow: 0 5px 20px rgba(0,0,0,0.06); border-bottom: 4px solid var(--primary-teal, #00897b); transition: transform 0.2s;">
                <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; font-size: 1.3rem;">
                    <i class="fas fa-users"></i>
                </div>
                <h3 style="font-size: 2rem; font-weight: 800; color: #1e293b; margin: 0 0 4px;">{{ number_format((int) ($totalUmat ?? 0), 0, ',', '.') }}</h3>
                <p style="font-size: 0.85rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin: 0;">Total Jiwa Umat</p>
            </div>

            <div style="background: #ffffff; border-radius: 15px; padding: 30px 20px; text-align: center; box-shadow: 0 5px 20px rgba(0,0,0,0.06); border-bottom: 4px solid var(--primary-orange, #ff9800); transition: transform 0.2s;">
                <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(255,152,0,0.1); color: var(--primary-orange, #ff9800); display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; font-size: 1.3rem;">
                    <i class="fas fa-house-user"></i>
                </div>
                <h3 style="font-size: 2rem; font-weight: 800; color: #1e293b; margin: 0 0 4px;">{{ number_format((int) ($totalKK ?? 0), 0, ',', '.') }}</h3>
                <p style="font-size: 0.85rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin: 0;">Kepala Keluarga (KK)</p>
            </div>

            <div style="background: #ffffff; border-radius: 15px; padding: 30px 20px; text-align: center; box-shadow: 0 5px 20px rgba(0,0,0,0.06); border-bottom: 4px solid #6366f1; transition: transform 0.2s;">
                <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(99,102,241,0.1); color: #6366f1; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; font-size: 1.3rem;">
                    <i class="fas fa-people-arrows"></i>
                </div>
                <h3 style="font-size: 2rem; font-weight: 800; color: #1e293b; margin: 0 0 4px;">{{ number_format((int) ($totalKUB ?? 0), 0, ',', '.') }}</h3>
                <p style="font-size: 0.85rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin: 0;">Komunitas Basis (KUB)</p>
            </div>

            <div style="background: #ffffff; border-radius: 15px; padding: 30px 20px; text-align: center; box-shadow: 0 5px 20px rgba(0,0,0,0.06); border-bottom: 4px solid #10b981; transition: transform 0.2s;">
                <div style="width: 50px; height: 50px; border-radius: 50%; background: rgba(16,185,129,0.1); color: #10b981; display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; font-size: 1.3rem;">
                    <i class="fas fa-church"></i>
                </div>
                <h3 style="font-size: 2rem; font-weight: 800; color: #1e293b; margin: 0 0 4px;">{{ number_format((int) ($totalKapela ?? 0), 0, ',', '.') }}</h3>
                <p style="font-size: 0.85rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin: 0;">Stasi &amp; Kapela</p>
            </div>
        </div>

    </div>
</section>
@endsection

