@extends('layouts.app')

@section('title', 'Artikel - SIPAROKI')

@section('content')
<div class="bg-amber-600 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-white">Artikel & Warta Gereja</h1>
        <p class="text-amber-100 mt-2">Bacaan rohani dan berita paroki</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($artikel ?? [] as $item)
        <article class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition">
            <div class="aspect-video bg-gray-200">
                @if($item->gambar)
                    <img src="{{ asset('storage/' . $item->gambar) }}" alt="{{ $item->judul }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    </div>
                @endif
            </div>
            <div class="p-5">
                @if($item->kategori)
                    <span class="text-xs font-semibold text-amber-600 bg-amber-50 px-2 py-1 rounded">{{ $item->kategori }}</span>
                @endif
                <h2 class="font-bold text-lg text-gray-900 mt-2 line-clamp-2">{{ $item->judul }}</h2>
                <p class="text-gray-600 text-sm mt-2 line-clamp-3">{{ $item->ringkasan ?? Str::limit(strip_tags($item->konten), 120) }}</p>
                <div class="flex items-center justify-between mt-4">
                    <span class="text-sm text-gray-400">{{ $item->created_at->format('d M Y') }}</span>
                    <a href="/artikel/{{ $item->slug }}" wire:navigate class="text-amber-600 hover:text-amber-700 font-medium text-sm">Baca &rarr;</a>
                </div>
            </div>
        </article>
        @empty
        <div class="md:col-span-2 lg:col-span-3 bg-white rounded-xl shadow-md p-12 text-center">
            <p class="text-gray-500 text-lg">Belum ada artikel.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
