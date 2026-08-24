@extends('layouts.app')
@section('title', 'Direktori Putra-Putri Altar / Misdinar - ' . ($globalNamaParoki ?? 'SIPAROKI'))
@section('content')
<div class="py-12 bg-slate-100 dark:bg-[#090e1a]">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white dark:bg-[#101d31] rounded-3xl p-8 border border-slate-200 dark:border-[#263a55] shadow-sm">
            <span class="st-badge mb-2"><i class="fas fa-cross me-1"></i> Pelayan Altar</span>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white mb-6">Putra-Putri Altar (Misdinar)</h1>
            <p class="text-slate-600 dark:text-slate-300">Daftar anggota dan pengurus paguyuban pelayan altar paroki.</p>
        </div>
    </div>
</div>
@endsection
