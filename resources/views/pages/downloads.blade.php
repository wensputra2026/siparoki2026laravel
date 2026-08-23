@extends('layouts.app')
@section('title', 'Pusat Unduhan Formulir & Dokumen - Kristus Raja Katedral')
@section('content')
<div class="py-12 bg-slate-100 dark:bg-[#090e1a]">
    <div class="max-w-5xl mx-auto px-4">
        <div class="section-title">
            <span class="st-badge"><i class="fas fa-download me-1"></i> Unduhan Dokumen</span>
            <h2>Formulir &amp; <span class="text-gradient">Dokumen Paroki</span></h2>
            <p>Unduh formulir pendaftaran sakramen, surat pengantar, dan warta mingguan.</p>
        </div>
        <div class="space-y-3">
            @forelse($downloads ?? [] as $d)
            <div class="bg-white dark:bg-[#101d31] p-4 rounded-2xl border border-slate-200 dark:border-[#263a55] flex items-center justify-between shadow-sm">
                <div>
                    <h4 class="font-bold text-slate-900 dark:text-white text-sm">{{ $d->judul }}</h4>
                    <p class="text-xs text-slate-500">{{ $d->kategori ?? 'Dokumen' }}</p>
                </div>
                <a href="{{ asset('storage/' . $d->file) }}" target="_blank" class="bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold px-4 py-2 rounded-xl transition">
                    <i class="fas fa-download mr-1"></i> Unduh
                </a>
            </div>
            @empty
            <div class="bg-white dark:bg-[#101d31] p-8 rounded-2xl text-center text-slate-400">
                <p>Belum ada berkas unduhan yang tersedia.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
