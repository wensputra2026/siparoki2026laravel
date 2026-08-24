@extends('layouts.app')

@section('title', 'Pengumuman - SIPAROKI')

@section('content')
<div class="page-hero py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-white">Pengumuman</h1>
        <p class="text-sky-100 mt-2">Informasi dan pengumuman terbaru dari paroki</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="space-y-6">
        @forelse($pengumuman ?? [] as $item)
        <article class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
            <div class="flex items-center gap-3 mb-3">
                <span class="text-xs font-semibold text-amber-600 bg-amber-50 px-3 py-1 rounded-full">{{ $item->kategori ?? 'PENGUMUMAN' }}</span>
                <span class="text-sm text-gray-400">{{ $item->created_at->format('d M Y') }}</span>
            </div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">{{ $item->judul }}</h2>
            @if($item->ringkasan)
                <p class="text-gray-600 mb-3">{{ $item->ringkasan }}</p>
            @endif
            <div class="prose prose-sm max-w-none text-gray-700">
                {!! $item->isi !!}
            </div>
        </article>
        @empty
        <div class="bg-white rounded-xl shadow-md p-12 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
            <p class="text-gray-500 text-lg">Belum ada pengumuman terbaru.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
