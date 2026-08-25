@extends('layouts.app')
@section('title', 'Profil Kapela & Stasi - ' . ($globalNamaParoki ?? 'SIPAROKI'))

@section('content')
<!-- Page Header / Breadcrumb Konoha Style -->
<section class="page-header">
    <div class="container" style="max-width: 1180px; margin: 0 auto; padding: 0 20px;">
        <h1 style="font-size: 2.6rem; font-weight: 700; margin-bottom: 15px; color: #ffffff;">Stasi & Kapela</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="display: inline-flex; list-style: none; padding: 0; margin: 0 auto; gap: 12px; background: transparent; justify-content: center; align-items: center;">
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/" style="color: white; text-decoration: none; font-weight: 500;">Beranda</a>
                </li>
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/profil" style="color: white; text-decoration: none; font-weight: 500;">Pelayanan</a>
                </li>
                <li class="breadcrumb-item active" style="background: var(--primary-orange, #ff9800); color: white; padding: 6px 18px; border-radius: 25px; font-size: 0.85rem; font-weight: 600;">
                    Kapela & Stasi
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- Content Section Konoha Style -->
<section class="content-section" style="padding: 60px 0 80px; background: #f4faf9;">
    <div class="container" style="max-width: 1180px; margin: 0 auto; padding: 0 20px;">
        
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px;">
            @forelse($kapela ?? [] as $k)
                <div style="background: #ffffff; border-radius: 15px; padding: 25px 28px; box-shadow: 0 5px 20px rgba(0,0,0,0.06); border-left: 5px solid var(--primary-teal, #00897b); transition: transform 0.2s, box-shadow 0.2s;">
                    <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 14px;">
                        <div style="width: 44px; height: 44px; border-radius: 50%; background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                            <i class="fas fa-church"></i>
                        </div>
                        <div>
                            <h4 style="font-size: 1.05rem; font-weight: 700; color: #1e293b; margin: 0 0 2px;">
                                {{ $k->nama_kapela ?? $k->nama_stasi ?? $k->nama ?? 'Gereja Stasi / Kapela' }}
                            </h4>
                            <span style="font-size: 0.78rem; font-weight: 600; color: var(--primary-orange, #ff9800); text-transform: uppercase;">
                                {{ $k->tipe ?? 'Stasi / Kapela' }}
                            </span>
                        </div>
                    </div>

                    @if(!empty($k->alamat) || !empty($k->lokasi))
                        <p style="font-size: 0.85rem; color: #64748b; margin: 0; line-height: 1.5; display: flex; align-items: flex-start; gap: 6px;">
                            <i class="fas fa-map-marker-alt" style="color: #94a3b8; margin-top: 3px;"></i>
                            <span>{{ $k->alamat ?? $k->lokasi }}</span>
                        </p>
                    @endif
                </div>
            @empty
                <div style="background: #ffffff; border-radius: 15px; padding: 25px 28px; box-shadow: 0 5px 20px rgba(0,0,0,0.06); border-left: 5px solid var(--primary-teal, #00897b);">
                    <div style="display: flex; align-items: center; gap: 14px; margin-bottom: 10px;">
                        <div style="width: 44px; height: 44px; border-radius: 50%; background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                            <i class="fas fa-church"></i>
                        </div>
                        <div>
                            <h4 style="font-size: 1.05rem; font-weight: 700; color: #1e293b; margin: 0 0 2px;">Gereja Pusat Paroki</h4>
                            <span style="font-size: 0.78rem; font-weight: 600; color: var(--primary-orange, #ff9800); text-transform: uppercase;">Pusat Paroki</span>
                        </div>
                    </div>
                    <p style="font-size: 0.85rem; color: #64748b; margin: 0;">{{ $globalNamaParoki ?? 'Paroki St. Vinsensius a Paulo Benlutu' }}</p>
                </div>
            @endforelse
        </div>

    </div>
</section>
@endsection

