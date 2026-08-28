@extends('layouts.app')
@section('title', 'Sejarah Paroki - ' . ($globalNamaParoki ?? 'SIPAROKI'))

@section('content')
<!-- Page Header / Breadcrumb Konoha Style -->
<section class="page-header">
    <div class="container">
        <h1 style="font-size: 2.6rem; font-weight: 700; margin-bottom: 15px; color: #ffffff;">Sejarah Paroki</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="display: inline-flex; list-style: none; padding: 0; margin: 0 auto; gap: 12px; background: transparent; justify-content: center; align-items: center;">
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/" style="color: white; text-decoration: none; font-weight: 500;">Beranda</a>
                </li>
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/profil" style="color: white; text-decoration: none; font-weight: 500;">Tentang</a>
                </li>
                <li class="breadcrumb-item active" style="background: var(--primary-orange, #ff9800); color: white; padding: 6px 18px; border-radius: 25px; font-size: 0.85rem; font-weight: 600;">
                    Sejarah
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- Content Section Konoha Style -->
<section class="content-section" style="padding: 60px 0 80px; background: #f4faf9;">
    <div class="container">
        
        <div style="background: #ffffff; border-radius: 15px; padding: 40px; box-shadow: 0 5px 20px rgba(0,0,0,0.06); border-left: 5px solid var(--primary-teal, #00897b);">
            <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 22px;">
                <div style="width: 48px; height: 48px; border-radius: 50%; background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                    <i class="fas fa-landmark"></i>
                </div>
                <h3 style="font-size: 1.45rem; font-weight: 700; color: #1e293b; margin: 0;">Sejarah Perjalanan Iman Paroki</h3>
            </div>

            <div style="color: #334155; font-size: 1rem; line-height: 1.85; display: flex; flex-direction: column; gap: 16px;">
                <p>
                    <strong>{{ $globalNamaParoki ?? $nama_paroki ?? 'Paroki St. Vinsensius a Paulo Benlutu' }}</strong> merupakan komunitas persekutuan umat beriman yang bertumbuh subur dalam karya keselamatan, pewartaan Sabda, dan pelayanan kasih di tengah masyarakat.
                </p>
                <p>
                    Sejak awal berdirinya, paroki ini terus menghidupi semangat Santo Vinsensius a Paulo dengan mengutamakan bela rasa, pelayanan kepada kaum kecil dan tersingkir, serta penguatan iman melalui liturgi sakramen dan doa rukun di Komunitas Basis Gerejani (KUB).
                </p>
                <p>
                    Kini, dengan dukungan stasi-stasi dan kapela yang tersebar di wilayah teritorial paroki, gerak reksa pastoral terus diperbarui seiring perkembangan zaman menuju Gereja yang mandiri, misioner, dan berakar kuat dalam iman Katolik.
                </p>
            </div>

            <!-- Share Buttons -->
            @include('partials.share-buttons', ['title' => 'Sejarah Paroki - ' . ($globalNamaParoki ?? 'Paroki')])
        </div>

    </div>
</section>
@endsection

