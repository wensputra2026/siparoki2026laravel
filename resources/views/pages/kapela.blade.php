@extends('layouts.app')
@section('title', 'Profil Kapela & Stasi - ' . ($globalNamaParoki ?? 'SIPAROKI'))
@section('content')
<div class="py-12 bg-slate-100 dark:bg-[#090e1a]">
    <div class="max-w-5xl mx-auto px-4">
        <div class="section-title">
            <span class="st-badge"><i class="fas fa-church me-1"></i> Stasi &amp; Kapela</span>
            <h2>Daftar Wilayah <span class="text-gradient">Kapela &amp; Stasi</span></h2>
            <p>Informasi stasi dan kapela dalam reksa pastoral paroki.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white dark:bg-[#101d31] p-6 rounded-2xl border border-slate-200 dark:border-[#263a55] shadow-sm">
                <h3 class="font-bold text-lg text-slate-900 dark:text-white">Gereja Pusat Paroki</h3>
                <p class="text-xs text-sky-600 font-semibold mt-1">{{ $globalNamaParoki ?? 'SIPAROKI' }}</p>
                <p class="text-xs text-slate-500 mt-2">Fontein, Kec. Kota Raja, Kota Kupang</p>
            </div>
        </div>
    </div>
</div>
@endsection
