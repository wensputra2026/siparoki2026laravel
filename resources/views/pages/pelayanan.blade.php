@extends('layouts.app')
@section('title', 'Daftar Layanan Sakramen & Pastoral - ' . ($globalNamaParoki ?? 'SIPAROKI'))

@section('content')
<!-- Page Header / Breadcrumb Konoha Style -->
<section class="page-header">
    <div class="container">
        <h1 style="font-size: 2.6rem; font-weight: 700; margin-bottom: 15px; color: #ffffff;">Layanan Pastoral & Sakramen</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="display: inline-flex; list-style: none; padding: 0; margin: 0 auto; gap: 12px; background: transparent; justify-content: center; align-items: center;">
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/" style="color: white; text-decoration: none; font-weight: 500;">Beranda</a>
                </li>
                <li class="breadcrumb-item active" style="background: var(--primary-orange, #ff9800); color: white; padding: 6px 18px; border-radius: 25px; font-size: 0.85rem; font-weight: 600;">
                    Pelayanan
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- Content Section Konoha Style -->
<section class="content-section" style="padding: 60px 0 80px; background: #f4faf9;">
    <div class="container">
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 24px;">
            
            <div style="background: #ffffff; border-radius: 15px; padding: 30px 24px; box-shadow: 0 5px 20px rgba(0,0,0,0.06); text-align: center; border-bottom: 4px solid var(--primary-teal, #00897b); transition: transform 0.2s;">
                <div style="width: 55px; height: 55px; background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 1.4rem;">
                    <i class="fas fa-water"></i>
                </div>
                <h4 style="font-size: 1.15rem; font-weight: 700; color: #1e293b; margin-bottom: 8px;">Sakramen Baptis</h4>
                <p style="font-size: 0.88rem; color: #64748b; margin: 0; line-height: 1.5;">Baptis bayi, anak-anak, dan calon katekumen dewasa.</p>
            </div>

            <div style="background: #ffffff; border-radius: 15px; padding: 30px 24px; box-shadow: 0 5px 20px rgba(0,0,0,0.06); text-align: center; border-bottom: 4px solid var(--primary-orange, #ff9800); transition: transform 0.2s;">
                <div style="width: 55px; height: 55px; background: rgba(255,152,0,0.1); color: var(--primary-orange, #ff9800); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 1.4rem;">
                    <i class="fas fa-bread-slice"></i>
                </div>
                <h4 style="font-size: 1.15rem; font-weight: 700; color: #1e293b; margin-bottom: 8px;">Komuni Pertama</h4>
                <p style="font-size: 0.88rem; color: #64748b; margin: 0; line-height: 1.5;">Penerimaan Tubuh Kristus perdana bagi anak-anak yang telah dibina.</p>
            </div>

            <div style="background: #ffffff; border-radius: 15px; padding: 30px 24px; box-shadow: 0 5px 20px rgba(0,0,0,0.06); text-align: center; border-bottom: 4px solid #6366f1; transition: transform 0.2s;">
                <div style="width: 55px; height: 55px; background: rgba(99,102,241,0.1); color: #6366f1; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 1.4rem;">
                    <i class="fas fa-fire"></i>
                </div>
                <h4 style="font-size: 1.15rem; font-weight: 700; color: #1e293b; margin-bottom: 8px;">Sakramen Krisma</h4>
                <p style="font-size: 0.88rem; color: #64748b; margin: 0; line-height: 1.5;">Penerimaan Roh Kudus dan kedewasaan iman dalam Gereja Katolik.</p>
            </div>

            <div style="background: #ffffff; border-radius: 15px; padding: 30px 24px; box-shadow: 0 5px 20px rgba(0,0,0,0.06); text-align: center; border-bottom: 4px solid #10b981; transition: transform 0.2s;">
                <div style="width: 55px; height: 55px; background: rgba(16,185,129,0.1); color: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; font-size: 1.4rem;">
                    <i class="fas fa-ring"></i>
                </div>
                <h4 style="font-size: 1.15rem; font-weight: 700; color: #1e293b; margin-bottom: 8px;">Sakramen Pernikahan</h4>
                <p style="font-size: 0.88rem; color: #64748b; margin: 0; line-height: 1.5;">Kursus Persiapan Pernikahan Katolik (KPPK) & penyelidikan kanonik.</p>
            </div>

        </div>

        <div style="text-align: center; margin-top: 45px;">
            <a href="/sakramen" class="btn-program" style="display: inline-flex; align-items: center; gap: 8px; background: var(--primary-teal, #00897b); color: white; padding: 12px 32px; border-radius: 25px; font-weight: 700; text-decoration: none; font-size: 0.95rem; box-shadow: 0 5px 15px rgba(0,137,123,0.3);">
                <i class="fas fa-file-signature"></i>
                <span>Pengajuan Formulir Sakramen Online</span>
            </a>
        </div>

        <!-- Share Buttons -->
        <div style="margin-top: 35px; background: #ffffff; border-radius: 15px; padding: 25px 35px; box-shadow: 0 5px 20px rgba(0,0,0,0.06);">
            @include('partials.share-buttons', ['title' => 'Layanan Pastoral & Sakramen - ' . ($globalNamaParoki ?? 'Paroki')])
        </div>

    </div>
</section>
@endsection


