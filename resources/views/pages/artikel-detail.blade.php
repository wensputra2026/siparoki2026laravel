@extends('layouts.app')

@section('title', $item->judul . ' - SIPAROKI')
@section('description', Str::limit(strip_tags($item->konten ?? ''), 160))

@section('content')
<div class="bg-amber-600 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($item->kategori)
            <span class="inline-block bg-white/20 text-white text-xs font-semibold px-3 py-1 rounded-full mb-3">{{ $item->kategori }}</span>
        @endif
        <h1 class="text-3xl font-bold text-white">{{ $item->judul }}</h1>
        <p class="text-amber-100 mt-2 text-sm">
            {{ $item->created_at->translatedFormat('l, d F Y') }}
            @if($item->penulis) &bull; {{ $item->penulis }} @endif
        </p>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Konten Utama --}}
        <div class="lg:col-span-2">
            @if($item->gambar)
            <div class="mb-6 rounded-xl overflow-hidden shadow-md">
                <img src="{{ asset('storage/' . $item->gambar) }}" alt="{{ $item->judul }}" class="w-full h-72 object-cover">
            </div>
            @endif

            <div class="bg-white rounded-xl shadow-md p-8 prose prose-amber max-w-none">
                {!! $item->konten !!}
            </div>

            {{-- Share & Back --}}
            <div class="mt-6 flex items-center justify-between">
                <a href="/artikel" wire:navigate class="inline-flex items-center text-amber-600 hover:text-amber-700 font-medium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Kembali ke Artikel
                </a>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="lg:col-span-1">
            {{-- Artikel Terkait --}}
            @if($terkait->isNotEmpty())
            <div class="bg-white rounded-xl shadow-md p-6 mb-6">
                <h3 class="font-bold text-gray-900 mb-4 border-b pb-2">Artikel Terkait</h3>
                <div class="space-y-4">
                    @foreach($terkait as $art)
                    <a href="/artikel/{{ $art->slug }}" wire:navigate class="flex space-x-3 group">
                        <div class="w-16 h-16 flex-shrink-0 rounded-lg overflow-hidden bg-gray-100">
                            @if($art->gambar)
                                <img src="{{ asset('storage/' . $art->gambar) }}" alt="{{ $art->judul }}" class="w-full h-full object-cover group-hover:scale-105 transition">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-gray-300">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01"/></svg>
                                </div>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900 group-hover:text-amber-600 transition line-clamp-2">{{ $art->judul }}</p>
                            <p class="text-xs text-gray-400 mt-1">{{ $art->created_at->format('d M Y') }}</p>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- CTA Sakramen --}}
            <div class="bg-amber-50 border border-amber-100 rounded-xl p-6 text-center">
                <div class="w-12 h-12 bg-amber-600 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </div>
                <h4 class="font-bold text-gray-900 mb-2">Butuh Layanan Sakramen?</h4>
                <p class="text-sm text-gray-600 mb-4">Ajukan permohonan sakramen secara online.</p>
                <a href="/sakramen" wire:navigate class="block bg-amber-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-amber-700 transition">Ajukan Sekarang</a>
            </div>
        </div>
    </div>
</div>
@endsection
