@extends('layouts.app')

@section('title', 'Profil Paroki - ' . ($nama_paroki ?? $globalNamaParoki ?? 'SIPAROKI'))

@section('content')
@php
    $paroki = $activeParoki ?? $profil ?? null;
    $displayName = $nama_paroki ?? $paroki?->nama_paroki ?? $globalNamaParoki ?? 'SIPAROKI';
    $pastorName = $pastor_paroki ?? $paroki?->pastor_paroki ?? $paroki?->nama_pastor_paroki_aktif ?? null;
    $profileText = $profil->sejarah ?? $profil->deskripsi ?? $paroki?->keterangan ?? null;
    $profileRows = [
        ['icon' => 'fas fa-map-marker-alt', 'label' => 'Alamat Paroki', 'value' => $alamat ?? $paroki?->alamat ?? null],
        ['icon' => 'fas fa-phone-alt', 'label' => 'Telepon / HP', 'value' => $telepon ?? $paroki?->telepon ?? null],
        ['icon' => 'fas fa-envelope', 'label' => 'Email Resmi', 'value' => $email ?? $paroki?->email ?? null],
        ['icon' => 'fas fa-globe', 'label' => 'Website', 'value' => $website ?? $paroki?->website ?? null],
    ];
@endphp

<!-- Page Header / Breadcrumb Konoha Style -->
<section class="page-header">
    <div class="container" style="max-width: 1180px; margin: 0 auto; padding: 0 20px;">
        <h1 style="font-size: 2.6rem; font-weight: 700; margin-bottom: 15px; color: #ffffff;">Profil Paroki</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="display: inline-flex; list-style: none; padding: 0; margin: 0 auto; gap: 12px; background: transparent; justify-content: center; align-items: center;">
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/" style="color: white; text-decoration: none; font-weight: 500;">Beranda</a>
                </li>
                <li class="breadcrumb-item active" style="background: var(--primary-orange, #ff9800); color: white; padding: 6px 18px; border-radius: 25px; font-size: 0.85rem; font-weight: 600;">
                    Profil
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- Content Section Konoha Style -->
<section class="content-section" style="padding: 60px 0 80px; background: #f4faf9;">
    <div class="container" style="max-width: 1000px; margin: 0 auto; padding: 0 20px;">
        
        <div style="background: #ffffff; border-radius: 15px; padding: 40px; box-shadow: 0 5px 20px rgba(0,0,0,0.06);">
            
            <div style="text-align: center; margin-bottom: 35px;">
                @if(!empty($globalLogo))
                    <div style="width: 90px; height: 90px; margin: 0 auto 18px; border-radius: 50%; background: #f8fafc; border: 2px solid #e2e8f0; display: flex; align-items: center; justify-content: center; overflow: hidden; padding: 10px;">
                        <img src="{{ $globalLogo }}" alt="{{ $displayName }}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    </div>
                @endif
                <h2 style="font-size: 1.8rem; font-weight: 700; color: #1e293b; margin-bottom: 8px;">
                    {{ $displayName }}
                </h2>
                @if($pastorName)
                    <p style="font-size: 0.95rem; font-weight: 600; color: var(--primary-teal, #00897b); margin: 0;">
                        <i class="fas fa-user-tie" style="color: var(--primary-orange, #ff9800); margin-right: 6px;"></i> Pastor Paroki: {{ $pastorName }}
                    </p>
                @endif
            </div>

            @if($profileText)
                <div style="color: #334155; font-size: 0.98rem; line-height: 1.85; margin-bottom: 35px; border-top: 1px solid #f1f5f9; padding-top: 25px;">
                    {!! nl2br(e($profileText)) !!}
                </div>
            @endif

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; border-top: 1px solid #eef2f6; padding-top: 30px;">
                @foreach($profileRows as $row)
                    @if(filled($row['value']))
                        <div style="display: flex; align-items: flex-start; gap: 14px; background: #f8fafc; border-radius: 12px; padding: 16px 20px;">
                            <div style="width: 38px; height: 38px; border-radius: 50%; background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); display: flex; align-items: center; justify-content: center; font-size: 0.95rem; flex-shrink: 0;">
                                <i class="{{ $row['icon'] }}"></i>
                            </div>
                            <div>
                                <span style="display: block; font-size: 0.78rem; font-weight: 700; color: #94a3b8; text-transform: uppercase;">
                                    {{ $row['label'] }}
                                </span>
                                <span style="font-size: 0.9rem; font-weight: 600; color: #1e293b; margin-top: 2px; display: block;">
                                    {{ $row['value'] }}
                                </span>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

        </div>
    </div>
</section>
@endsection
