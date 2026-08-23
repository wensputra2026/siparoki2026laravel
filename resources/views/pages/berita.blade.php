@extends('layouts.app')
@section('title', 'Berita Paroki - Kristus Raja Katedral / Bonipoi')
@section('content')
<div class="py-12 bg-slate-100 dark:bg-[#090e1a]">
    <div class="max-w-6xl mx-auto px-4">
        <div class="section-title">
            <span class="st-badge"><i class="fas fa-newspaper me-1"></i> Warta Paroki</span>
            <h2>Berita <span class="text-gradient">Terkini</span></h2>
            <p>Liputan seputar peristiwa, perayaan, dan dinamika kehidupan umat paroki.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @forelse($berita ?? [] as $item)
            <div class="bg-white dark:bg-[#101d31] rounded-2xl overflow-hidden border border-slate-200 dark:border-[#263a55] shadow-sm">
                <div class="p-6">
                    <span class="text-xs font-bold text-sky-600 bg-sky-50 dark:bg-sky-950 px-2.5 py-1 rounded-full">{{ $item->kategori ?? 'BERITA' }}</span>
                    <h3 class="font-bold text-lg text-slate-900 dark:text-white mt-3 line-clamp-2">{{ $item->judul }}</h3>
                    <p class="text-xs text-slate-500 mt-2 line-clamp-3">{{ $item->ringkasan ?? Str::limit(strip_tags($item->konten), 120) }}</p>
                    <a href="/artikel/{{ $item->slug }}" wire:navigate class="inline-block mt-4 text-xs font-bold text-sky-600 hover:text-sky-700">Baca Selengkapnya &rarr;</a>
                </div>
            </div>
            @empty
            <div class="col-span-3 text-center py-8 text-slate-400">
                <p>Belum ada berita terbaru.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
