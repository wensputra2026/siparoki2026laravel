@extends('layouts.app')
@section('title', 'Kata Sambutan Pastor Paroki - ' . ($globalNamaParoki ?? $nama_paroki ?? 'SIPAROKI'))
@section('content')
<div class="py-12 bg-slate-100 dark:bg-[#090e1a]">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white dark:bg-[#101d31] rounded-3xl p-8 border border-slate-200 dark:border-[#263a55] shadow-sm">
            <span class="st-badge mb-2"><i class="fas fa-quote-left me-1"></i> Gembala Umat</span>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white mb-6">Sambutan Pastor Paroki</h1>
            <div class="prose dark:prose-invert max-w-none text-slate-600 dark:text-slate-300 leading-relaxed space-y-4">
                <p class="text-lg font-medium text-slate-900 dark:text-white">Salve, Salam Sehat dan Berkah Dalem.</p>
                <p>Puji syukur kita haturkan kepada Tuhan Yang Maha Esa atas hadirnya media informasi dan sistem pendataan digital paroki ini. Website ini menjadi sarana komunikasi, pewartaan, dan pelayanan yang menghubungkan seluruh umat dan komunitas basis gerejani.</p>
                <p>Semoga sistem informasi ini memberikan kemudahan dalam pelayanan sakramen, administrasi, dan mempererat tali persekutuan kita sebagai satu tubuh Kristus.</p>
                <div class="pt-6 border-t border-slate-100 dark:border-slate-800">
                    <p class="font-bold text-slate-900 dark:text-white">Pastor Paroki</p>
                    <p class="text-sm font-semibold text-sky-700">{{ $pastor_paroki ?? 'Pastor Paroki' }}</p>
                    <p class="text-sm text-slate-500">{{ $globalNamaParoki ?? $nama_paroki ?? 'SIPAROKI' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
