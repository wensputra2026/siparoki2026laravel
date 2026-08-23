@extends('layouts.app')
@section('title', 'Agenda Kegiatan Paroki - Kristus Raja Katedral / Bonipoi')
@section('content')
<div class="py-12 bg-slate-100 dark:bg-[#090e1a]">
    <div class="max-w-5xl mx-auto px-4">
        <div class="section-title">
            <span class="st-badge"><i class="fas fa-calendar-check me-1"></i> Agenda Paroki</span>
            <h2>Kalender <span class="text-gradient">Kegiatan &amp; Pertemuan</span></h2>
            <p>Jadwal kegiatan pastoral, pembinaan umat, dan perayaan gerejani.</p>
        </div>
        <div class="space-y-4">
            @forelse($agenda ?? [] as $item)
            <div class="bg-white dark:bg-[#101d31] p-6 rounded-2xl border border-slate-200 dark:border-[#263a55] shadow-sm flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white">{{ $item->judul ?? $item->nama_kegiatan }}</h3>
                    <p class="text-xs text-slate-500 mt-1"><i class="fa-solid fa-location-dot mr-1 text-sky-500"></i> {{ $item->lokasi ?? $item->tempat ?? 'Gereja Paroki' }}</p>
                </div>
                <span class="text-xs font-bold text-sky-600 bg-sky-50 dark:bg-sky-950 px-3 py-1.5 rounded-full">{{ \Carbon\Carbon::parse($item->tanggal_mulai ?? now())->format('d M Y') }}</span>
            </div>
            @empty
            <div class="bg-white dark:bg-[#101d31] p-8 rounded-2xl text-center text-slate-400">
                <p>Belum ada agenda kegiatan yang terdaftar.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
