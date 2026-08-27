@extends('layouts.public')

@section('title', 'Profil Kapela & Stasi - ' . ($globalNamaParoki ?? 'SIPAROKI'))
@section('description', 'Daftar stasi dan kapela dalam wilayah pelayanan ' . ($globalNamaParoki ?? 'SIPAROKI') . '.')

@section('content')
@php
    $kapelaItems = collect($kapela ?? []);
@endphp

<section class="page-header">
    <div class="container">
        <span class="st-badge"><i class="fas fa-location-dot me-1"></i> Wilayah Pelayanan</span>
        <h1>Stasi &amp; Kapela</h1>
        <p>Profil wilayah pelayanan, pusat komunitas umat, dan lokasi peribadatan dalam teritori paroki.</p>
    </div>
</section>

<section class="content-section chapel-page">
    <div class="container">
        <div class="chapel-toolbar">
            <div>
                <span class="chapel-kicker">Total Wilayah</span>
                <strong>{{ $kapelaItems->count() ?: 1 }} lokasi pelayanan</strong>
            </div>
            <a href="/peta-kapela" class="chapel-map-link">
                <i class="fas fa-map-location-dot"></i>
                Lihat Peta
            </a>
        </div>

        <div class="chapel-grid">
            @forelse($kapelaItems as $k)
                @php
                    $nama = $k->nama_stasi_kapela ?? $k->nama_kapela ?? $k->nama_stasi ?? $k->nama ?? 'Gereja Stasi / Kapela';
                    $tipe = $k->tipe ?? 'Stasi / Kapela';
                    $alamat = $k->alamat ?? $k->lokasi ?? null;
                    $kode = $k->kode_stasi_kapela ?? $k->kode_kapela ?? null;
                @endphp
                <article class="chapel-card">
                    <div class="chapel-icon">
                        <i class="fas fa-church"></i>
                    </div>
                    <div class="chapel-card-body">
                        <div class="chapel-card-head">
                            <span>{{ $tipe }}</span>
                            @if($kode)
                                <em>{{ $kode }}</em>
                            @endif
                        </div>
                        <h2>{{ $nama }}</h2>
                        @if($alamat)
                            <p><i class="fas fa-map-marker-alt"></i> {{ $alamat }}</p>
                        @else
                            <p><i class="fas fa-map-marker-alt"></i> Lokasi akan dilengkapi oleh pengelola paroki.</p>
                        @endif
                    </div>
                </article>
            @empty
                <article class="chapel-card chapel-card-featured">
                    <div class="chapel-icon">
                        <i class="fas fa-church"></i>
                    </div>
                    <div class="chapel-card-body">
                        <div class="chapel-card-head">
                            <span>Pusat Paroki</span>
                        </div>
                        <h2>Gereja Pusat Paroki</h2>
                        <p><i class="fas fa-map-marker-alt"></i> {{ $globalNamaParoki ?? 'Paroki St. Vinsensius a Paulo Benlutu' }}</p>
                    </div>
                </article>
            @endforelse
        </div>
    </div>
</section>
@endsection
