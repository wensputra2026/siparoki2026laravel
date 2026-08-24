@extends('layouts.app')

@section('title', 'Renungan Harian - SIPAROKI')

@section('content')
<div class="page-hero py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-white">Renungan Harian</h1>
        <p class="text-sky-100 mt-2">Bacaan rohani dan refleksi iman</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($renungan ?? [] as $item)
        <article class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition">
            <div class="p-6">
                <p class="text-xs text-amber-600 font-semibold mb-2">{{ isset($item->created_at) ? \Carbon\Carbon::parse($item->created_at)->format('d M Y') : (isset($item->tanggal_publish) ? \Carbon\Carbon::parse($item->tanggal_publish)->format('d M Y') : '') }}</p>
                <h2 class="font-bold text-lg text-gray-900 mb-3 line-clamp-2">{{ $item->judul }}</h2>
                <p class="text-gray-600 text-sm line-clamp-4">{{ Str::limit(strip_tags($item->isi ?? $item->konten ?? $item->ringkasan ?? ''), 200) }}</p>
            </div>
        </article>
        @empty
        <div class="md:col-span-2 lg:col-span-3 bg-white rounded-xl shadow-md p-12 text-center">
            <p class="text-gray-500 text-lg">Belum ada renungan.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-8">{{ $renungan->links() }}</div>
</div>
@endsection
