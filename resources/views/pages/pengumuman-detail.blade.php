@extends('layouts.app')

@section('title', $item->judul . ' - SIPAROKI')
@section('description', Str::limit(strip_tags($item->isi ?? ''), 160))

@section('content')
<div class="bg-amber-600 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        @if($item->kategori)
            <span class="inline-block bg-white/20 text-white text-xs font-semibold px-3 py-1 rounded-full mb-3">{{ $item->kategori }}</span>
        @endif
        <h1 class="text-3xl font-bold text-white">{{ $item->judul }}</h1>
        <p class="text-amber-100 mt-2 text-sm">{{ $item->created_at->translatedFormat('l, d F Y') }}</p>
    </div>
</div>

<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white rounded-xl shadow-md p-8">
        <div class="prose prose-amber max-w-none">
            {!! nl2br(e($item->isi)) !!}
        </div>
    </div>

    <div class="mt-6">
        <a href="/pengumuman" wire:navigate class="inline-flex items-center text-amber-600 hover:text-amber-700 font-medium">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali ke Pengumuman
        </a>
    </div>
</div>
@endsection
