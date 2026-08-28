@extends('layouts.app')
@section('title', 'Peta Wilayah Stasi & Kapela - ' . ($globalNamaParoki ?? 'SIPAROKI'))
@section('content')
<div class="py-8 bg-slate-100 dark:bg-[#090e1a]">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex items-center justify-between mb-6">
            <div>
                <span class="st-badge mb-1"><i class="fas fa-map-marked-alt me-1"></i> WebGIS</span>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Peta Wilayah Stasi &amp; Kapela (Layar Penuh)</h1>
            </div>
            <a href="/" class="text-sm font-semibold text-sky-600 hover:text-sky-700">&larr; Kembali ke Beranda</a>
        </div>
        <div class="bg-white dark:bg-[#101d31] rounded-3xl overflow-hidden border border-slate-200 dark:border-[#263a55] shadow-lg">
            <div id="full-map-kapela" class="online-map" style="height: 650px; width: 100%;"></div>
        </div>

        <!-- Share Buttons -->
        <div class="mt-6 bg-white dark:bg-[#101d31] rounded-3xl p-6 border border-slate-200 dark:border-[#263a55] shadow-md">
            @include('partials.share-buttons', ['title' => 'Peta Wilayah Stasi & Kapela - ' . ($globalNamaParoki ?? 'SIPAROKI')])
        </div>
    </div>
</div>

@push('scripts')
    <script src="/js/pages/peta-kapela.js" defer></script>
@endpush
@endsection


