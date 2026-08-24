@extends('layouts.app')
@section('title', 'Dewan Pastoral Paroki - ' . ($globalNamaParoki ?? 'SIPAROKI'))
@section('content')
<div class="py-12 bg-slate-100 dark:bg-[#090e1a]">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white dark:bg-[#101d31] rounded-3xl p-8 border border-slate-200 dark:border-[#263a55] shadow-sm">
            <span class="st-badge mb-2"><i class="fas fa-sitemap me-1"></i> Struktur Organisasi</span>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white mb-6">Dewan Pastoral Paroki (DPP)</h1>
            <p class="text-slate-600 dark:text-slate-300">Bagan susunan dan pengurus Dewan Pastoral Paroki serta Dewan Keuangan Paroki.</p>
        </div>
    </div>
</div>
@endsection
