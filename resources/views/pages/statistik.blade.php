@extends('layouts.app')
@section('title', 'Statistik Paroki - Kristus Raja Katedral / Bonipoi')
@section('content')
<div class="py-12 bg-slate-100 dark:bg-[#090e1a]">
    <div class="max-w-5xl mx-auto px-4">
        <div class="section-title">
            <span class="st-badge"><i class="fas fa-chart-pie me-1"></i> Data &amp; Demografi</span>
            <h2>Statistik <span class="text-gradient">Umat Paroki</span></h2>
            <p>Rangkuman data sensus umat Katolik di lingkungan, wilayah, dan kapela.</p>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-[#101d31] p-6 rounded-2xl border border-slate-200 dark:border-[#263a55] text-center shadow-sm">
                <h3 class="text-3xl font-extrabold text-sky-600">{{ $totalUmat ?? 0 }}</h3>
                <p class="text-xs text-slate-500 font-semibold mt-1">Total Umat</p>
            </div>
            <div class="bg-white dark:bg-[#101d31] p-6 rounded-2xl border border-slate-200 dark:border-[#263a55] text-center shadow-sm">
                <h3 class="text-3xl font-extrabold text-purple-600">{{ $totalKK ?? 0 }}</h3>
                <p class="text-xs text-slate-500 font-semibold mt-1">Kepala Keluarga (KK)</p>
            </div>
            <div class="bg-white dark:bg-[#101d31] p-6 rounded-2xl border border-slate-200 dark:border-[#263a55] text-center shadow-sm">
                <h3 class="text-3xl font-extrabold text-emerald-600">{{ $totalKUB ?? 0 }}</h3>
                <p class="text-xs text-slate-500 font-semibold mt-1">Lingkungan / KUB</p>
            </div>
            <div class="bg-white dark:bg-[#101d31] p-6 rounded-2xl border border-slate-200 dark:border-[#263a55] text-center shadow-sm">
                <h3 class="text-3xl font-extrabold text-amber-600">{{ $totalKapela ?? 0 }}</h3>
                <p class="text-xs text-slate-500 font-semibold mt-1">Kapela &amp; Stasi</p>
            </div>
        </div>
    </div>
</div>
@endsection
