@extends('layouts.app')

@section('title', 'Jadwal Misa - SIPAROKI')

@section('content')
<div class="bg-amber-600 py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold text-white">Jadwal Misa</h1>
        <p class="text-amber-100 mt-2">Jadwal perayaan misa di Paroki Benlutu</p>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    {{-- Filter Bulan --}}
    <div class="mb-8 flex flex-wrap gap-2">
        @php
            $bulanSekarang = now()->month;
            $bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
        @endphp
        @for($i = 1; $i <= 12; $i++)
        <a href="/jadwal-misa?bulan={{ $i }}" wire:navigate
           class="px-4 py-2 rounded-lg text-sm font-medium transition {{ ($bulanFilter ?? $bulanSekarang) == $i ? 'bg-amber-600 text-white' : 'bg-white text-gray-700 hover:bg-amber-50 border' }}">
            {{ $bulan[$i-1] }}
        </a>
        @endfor
    </div>

    {{-- Daftar Misa --}}
    <div class="space-y-4">
        @forelse($jadwalMisa ?? [] as $misa)
        <div class="bg-white rounded-xl shadow-md p-6 hover:shadow-lg transition">
            <div class="flex flex-col md:flex-row md:items-center md:space-x-6">
                <div class="flex items-center space-x-4 mb-4 md:mb-0">
                    <div class="w-16 h-16 bg-amber-100 rounded-xl flex flex-col items-center justify-center flex-shrink-0">
                        <span class="text-amber-700 text-2xl font-bold">{{ \Carbon\Carbon::parse($misa->tanggal)->format('d') }}</span>
                        <span class="text-amber-600 text-xs font-medium">{{ \Carbon\Carbon::parse($misa->tanggal)->format('M') }}</span>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">{{ $misa->jenis_perayaan }}</p>
                        <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($misa->tanggal)->translatedFormat('l, d F Y') }}</p>
                    </div>
                </div>
                <div class="flex-1 md:text-right">
                    <p class="text-amber-600 font-bold text-lg">{{ $misa->jam_perayaan ?? $misa->waktu }}</p>
                    <p class="text-gray-500 text-sm">{{ $misa->tempat }}</p>
                </div>
            </div>
            @if($misa->pelayan)
            <div class="mt-4 pt-4 border-t border-gray-100">
                <p class="text-sm text-gray-600"><span class="font-medium">Pelayan:</span> {!! nl2br(e($misa->pelayan)) !!}</p>
            </div>
            @endif
            @if($misa->intensi)
            <div class="mt-2">
                <p class="text-sm text-gray-600"><span class="font-medium">Intensi:</span> {{ Str::limit($misa->intensi, 100) }}</p>
            </div>
            @endif
        </div>
        @empty
        <div class="bg-white rounded-xl shadow-md p-12 text-center">
            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <p class="text-gray-500 text-lg">Jadwal misa belum tersedia untuk bulan ini.</p>
        </div>
        @endforelse
    </div>
</div>
@endsection
