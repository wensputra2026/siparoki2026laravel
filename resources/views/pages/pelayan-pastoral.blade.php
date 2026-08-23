@extends('layouts.app')
@section('title', 'Pelayan Pastoral - Kristus Raja Katedral / Bonipoi')
@section('content')
<div class="py-12 bg-slate-100 dark:bg-[#090e1a]">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white dark:bg-[#101d31] rounded-3xl p-8 border border-slate-200 dark:border-[#263a55] shadow-sm">
            <span class="st-badge mb-2"><i class="fas fa-users me-1"></i> Reksa Pastoral</span>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white mb-6">Pelayan Pastoral Saat Ini</h1>
            <div class="space-y-4 text-slate-600 dark:text-slate-300">
                <div class="p-6 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700/50">
                    <h3 class="font-bold text-lg text-slate-900 dark:text-white">Pastor Paroki</h3>
                    <p class="text-sm text-sky-600 font-semibold mt-1">{{ $profil->pastor_paroki ?? 'Pastor Paroki' }}</p>
                    <p class="text-xs text-slate-500 mt-2">Penanggung jawab umum reksa pastoral dan paroki.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
