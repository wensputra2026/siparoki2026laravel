@extends('layouts.app')

@section('title', 'Kegiatan Paroki - SIPAROKI')

@section('content')
<div class="page-hero py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-white">Kegiatan Paroki</h1>
        <p class="text-sky-100 mt-2">Agenda dan kegiatan paroki</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($kegiatan ?? [] as $item)
        <article class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition">
            <div class="p-6">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-12 h-12 bg-amber-100 rounded-lg flex flex-col items-center justify-center flex-shrink-0">
                        <span class="text-amber-700 font-bold text-lg leading-none">
                            {{ \Carbon\Carbon::parse($item->tanggal_mulai ?? $item->tanggal ?? now())->format('d') }}
                        </span>
                        <span class="text-amber-600 text-xs leading-none">
                            {{ \Carbon\Carbon::parse($item->tanggal_mulai ?? $item->tanggal ?? now())->translatedFormat('M') }}
                        </span>
                    </div>
                    <div>
                        <h2 class="font-bold text-gray-900 line-clamp-2">{{ $item->nama_kegiatan ?? $item->judul }}</h2>
                        @if($item->tempat ?? false)
                        <p class="text-sm text-gray-500">{{ $item->tempat }}</p>
                        @endif
                    </div>
                </div>
                @if($item->deskripsi ?? false)
                <p class="text-gray-600 text-sm line-clamp-3">{{ Str::limit(strip_tags($item->deskripsi), 150) }}</p>
                @endif
            </div>
        </article>
        @empty
        <div class="md:col-span-2 lg:col-span-3 bg-white rounded-xl shadow-md p-12 text-center">
            <p class="text-gray-500 text-lg">Belum ada kegiatan.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-8">{{ $kegiatan->links() }}</div>

    <!-- Share Buttons -->
    <div class="mt-8 bg-white rounded-xl shadow-md p-6">
        @include('partials.share-buttons', ['title' => 'Kegiatan Paroki - ' . ($globalNamaParoki ?? 'SIPAROKI')])
    </div>
</div>
@endsection
