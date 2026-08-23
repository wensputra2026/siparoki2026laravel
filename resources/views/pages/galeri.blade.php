@extends('layouts.app')

@section('title', 'Galeri - SIPAROKI')

@section('content')
<div class="bg-amber-600 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-white">Galeri</h1>
        <p class="text-amber-100 mt-2">Dokumentasi kegiatan paroki</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    {{-- Filter --}}
    <div class="mb-8 flex flex-wrap gap-2">
        <button class="px-4 py-2 rounded-lg text-sm font-medium bg-amber-600 text-white">Semua</button>
        <button class="px-4 py-2 rounded-lg text-sm font-medium bg-white text-gray-700 hover:bg-amber-50 border">Foto</button>
        <button class="px-4 py-2 rounded-lg text-sm font-medium bg-white text-gray-700 hover:bg-amber-50 border">Video</button>
    </div>

    {{-- Grid Galeri --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @forelse($galeri ?? [] as $item)
        <div class="relative group rounded-xl overflow-hidden shadow-md hover:shadow-xl transition cursor-pointer">
            <div class="aspect-square bg-gray-200">
                @if($item->gambar)
                    <img src="{{ asset('storage/' . $item->gambar) }}" alt="{{ $item->judul }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                @elseif($item->youtube_thumbnail)
                    <img src="{{ $item->youtube_thumbnail }}" alt="{{ $item->judul }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                @endif
            </div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition flex flex-col justify-end p-4">
                @if($item->tipe === 'Video' || $item->youtube_url)
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
                        <div class="w-12 h-12 bg-white/90 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600 ml-0.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        </div>
                    </div>
                @endif
                <p class="text-white font-semibold">{{ $item->judul }}</p>
                <p class="text-white/70 text-sm">{{ $item->album }}</p>
            </div>
        </div>
        @empty
        <div class="col-span-2 md:col-span-4 bg-white rounded-xl shadow-md p-12 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <p class="text-gray-500 text-lg">Belum ada galeri.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
