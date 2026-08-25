@extends('layouts.app')
@section('title', 'Visi & Misi - ' . ($globalNamaParoki ?? 'SIPAROKI'))

@section('content')
<!-- Page Header / Breadcrumb Konoha Style -->
<section class="page-header">
    <div class="container" style="max-width: 1180px; margin: 0 auto; padding: 0 20px;">
        <h1 style="font-size: 2.6rem; font-weight: 700; margin-bottom: 15px; color: #ffffff;">Visi & Misi Paroki</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="display: inline-flex; list-style: none; padding: 0; margin: 0 auto; gap: 12px; background: transparent; justify-content: center; align-items: center;">
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/" style="color: white; text-decoration: none; font-weight: 500;">Beranda</a>
                </li>
                <li class="breadcrumb-item active" style="background: var(--primary-orange, #ff9800); color: white; padding: 6px 18px; border-radius: 25px; font-size: 0.85rem; font-weight: 600;">
                    Visi & Misi
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- Content Section Konoha Style -->
<section class="content-section" style="padding: 60px 0 80px; background: #f4faf9;">
    <div class="container" style="max-width: 1000px; margin: 0 auto; padding: 0 20px;">
        
        <div style="display: grid; grid-template-columns: 1fr; gap: 30px;">
            
            <!-- Visi Card -->
            <div style="background: #ffffff; border-radius: 15px; border-left: 5px solid var(--primary-teal, #00897b); padding: 35px 40px; box-shadow: 0 5px 20px rgba(0,0,0,0.06);">
                <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 18px;">
                    <div style="width: 46px; height: 46px; border-radius: 50%; background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h3 style="font-size: 1.4rem; font-weight: 700; color: #1e293b; margin: 0;">Visi Pastoral</h3>
                </div>
                <p style="font-size: 1.05rem; line-height: 1.8; color: #334155; margin: 0; font-weight: 500;">
                    Menjadi persekutuan umat Allah yang mandiri, solider, beriman teguh, dan berakar pada Sabda Allah serta Tradisi Suci Gereja Katolik di tengah masyarakat.
                </p>
            </div>

            <!-- Misi Card -->
            <div style="background: #ffffff; border-radius: 15px; border-left: 5px solid var(--primary-orange, #ff9800); padding: 35px 40px; box-shadow: 0 5px 20px rgba(0,0,0,0.06);">
                <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 22px;">
                    <div style="width: 46px; height: 46px; border-radius: 50%; background: rgba(255,152,0,0.1); color: var(--primary-orange, #ff9800); display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                        <i class="fas fa-bullseye"></i>
                    </div>
                    <h3 style="font-size: 1.4rem; font-weight: 700; color: #1e293b; margin: 0;">Misi Pastoral</h3>
                </div>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 16px;">
                    <li style="display: flex; align-items: flex-start; gap: 12px; font-size: 0.98rem; color: #334155; line-height: 1.6;">
                        <i class="fas fa-check-circle" style="color: var(--primary-teal, #00897b); margin-top: 4px; font-size: 1.1rem; flex-shrink: 0;"></i>
                        <span>Meningkatkan penghayatan iman melalui perayaan sakramental yang hidup, kudus, dan berbuah nyata.</span>
                    </li>
                    <li style="display: flex; align-items: flex-start; gap: 12px; font-size: 0.98rem; color: #334155; line-height: 1.6;">
                        <i class="fas fa-check-circle" style="color: var(--primary-teal, #00897b); margin-top: 4px; font-size: 1.1rem; flex-shrink: 0;"></i>
                        <span>Memperkuat basis komunitas umat beriman melalui perjumpaan doa, katekese KUB, dan lingkungan.</span>
                    </li>
                    <li style="display: flex; align-items: flex-start; gap: 12px; font-size: 0.98rem; color: var(--primary-teal, #00897b); margin-top: 4px; font-size: 1.1rem; flex-shrink: 0;"></i>
                        <span>Mengembangkan pelayanan pastoral yang inklusif, ramah, dan berbelarasa terhadap sesama yang membutuhkan.</span>
                    </li>
                    <li style="display: flex; align-items: flex-start; gap: 12px; font-size: 0.98rem; color: #334155; line-height: 1.6;">
                        <i class="fas fa-check-circle" style="color: var(--primary-teal, #00897b); margin-top: 4px; font-size: 1.1rem; flex-shrink: 0;"></i>
                        <span>Mendorong transparansi, akuntabilitas, dan digitalisasi tata kelola administrasi paroki modern.</span>
                    </li>
                </ul>
            </div>

        </div>

    </div>
</section>
@endsection

