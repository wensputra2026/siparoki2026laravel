@extends('layouts.app')

@section('title', 'Profil Paroki - SIPAROKI')

@section('content')
<div class="bg-amber-600 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-white">Profil Paroki</h1>
        <p class="text-amber-100 mt-2">Sejarah dan profil Paroki Benlutu</p>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    @if($profil)
    <div class="bg-white rounded-xl shadow-md p-8">
        @if($profil->foto ?? false)
        <div class="mb-8 rounded-xl overflow-hidden">
            <img src="{{ asset('storage/' . $profil->foto) }}" alt="Paroki Benlutu" class="w-full h-64 object-cover">
        </div>
        @endif

        <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $profil->nama_paroki ?? 'Paroki Benlutu' }}</h2>

        @if($profil->pastor_paroki ?? false)
        <p class="text-amber-600 font-medium mb-4">Pastor: {{ $profil->pastor_paroki }}</p>
        @endif

        @if($profil->sejarah ?? $profil->deskripsi ?? false)
        <div class="prose prose-amber max-w-none mb-6">
            {!! nl2br(e($profil->sejarah ?? $profil->deskripsi)) !!}
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 border-t pt-6">
            @if($profil->alamat ?? false)
            <div class="flex items-start space-x-3">
                <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <div>
                    <p class="font-medium text-gray-900">Alamat</p>
                    <p class="text-gray-600 text-sm">{{ $profil->alamat }}</p>
                </div>
            </div>
            @endif
            @if($profil->telepon ?? false)
            <div class="flex items-start space-x-3">
                <svg class="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                <div>
                    <p class="font-medium text-gray-900">Telepon</p>
                    <p class="text-gray-600 text-sm">{{ $profil->telepon }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
    @else
    <div class="bg-white rounded-xl shadow-md p-12 text-center">
        <p class="text-gray-500 text-lg">Profil paroki belum tersedia.</p>
    </div>
    @endif
</div>
@endsection
