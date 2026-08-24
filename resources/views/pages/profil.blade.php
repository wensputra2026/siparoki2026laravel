@extends('layouts.app')

@section('title', 'Profil Paroki - ' . ($nama_paroki ?? $globalNamaParoki ?? 'SIPAROKI'))

@section('content')
@php
    $paroki = $activeParoki ?? $profil ?? null;
    $displayName = $nama_paroki ?? $paroki?->nama_paroki ?? $globalNamaParoki ?? 'SIPAROKI';
    $pastorName = $pastor_paroki ?? $paroki?->pastor_paroki ?? $paroki?->nama_pastor_paroki_aktif ?? null;
    $profileText = $profil->sejarah ?? $profil->deskripsi ?? $paroki?->keterangan ?? null;
    $profileRows = [
        ['icon' => 'fa-location-dot', 'label' => 'Alamat', 'value' => $alamat ?? $paroki?->alamat ?? null],
        ['icon' => 'fa-phone', 'label' => 'Telepon', 'value' => $telepon ?? $paroki?->telepon ?? null],
        ['icon' => 'fa-envelope', 'label' => 'Email', 'value' => $email ?? $paroki?->email ?? null],
        ['icon' => 'fa-globe', 'label' => 'Website', 'value' => $website ?? $paroki?->website ?? null],
    ];
@endphp

<section class="page-banner">
    <span class="page-banner-shape page-banner-shape--1"></span>
    <span class="page-banner-shape page-banner-shape--2"></span>
    <div class="page-banner-content container">
        <span class="page-banner-badge"><i class="fa-solid fa-church"></i> Profil Paroki</span>
        <h1>{{ $displayName }}</h1>
        <p>Informasi umum, sejarah, dan identitas pastoral paroki.</p>
    </div>
</section>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    @if($profil || $paroki)
    <div class="bg-white rounded-xl shadow-md p-8">
        @if($profil->foto ?? false)
        <div class="mb-8 rounded-xl overflow-hidden">
            <img src="{{ asset('storage/' . $profil->foto) }}" alt="{{ $displayName }}" class="w-full h-64 object-cover">
        </div>
        @endif

        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $displayName }}</h2>

        @if($pastorName)
        <p class="text-amber-600 font-medium mb-4">Pastor: {{ $pastorName }}</p>
        @endif

        @if($profileText)
        <div class="prose prose-amber max-w-none mb-6">
            {!! nl2br(e($profileText)) !!}
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 border-t pt-6">
            @foreach($profileRows as $row)
                @if(filled($row['value']))
                    <div class="flex items-start space-x-3">
                        <i class="fa-solid {{ $row['icon'] }} text-amber-600 mt-1 w-5 text-center shrink-0"></i>
                        <div>
                            <p class="font-medium text-gray-900">{{ $row['label'] }}</p>
                            <p class="text-gray-600 text-sm">{{ $row['value'] }}</p>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
    @else
    <div class="bg-white rounded-xl shadow-md p-12 text-center">
        <p class="text-gray-500 text-lg">Profil paroki belum tersedia.</p>
    </div>
    @endif
</div>
@endsection
