@extends('layouts.app')
@section('title', 'Kata Sambutan Pastor Paroki - ' . ($globalNamaParoki ?? $nama_paroki ?? 'SIPAROKI'))
@section('content')
@php
    $imamImage = file_exists(public_path('assets/frontend/siparoki/images/default-pastor.jpg'))
        ? asset('assets/frontend/siparoki/images/default-pastor.jpg')
        : (file_exists(public_path('assets/frontend/siparoki/images/default-principal.jpg'))
            ? asset('assets/frontend/siparoki/images/default-principal.jpg')
            : asset('images/pastor-avatar.svg'));
@endphp
<div class="py-12 bg-slate-100 dark:bg-[#090e1a]">
    <div class="max-w-5xl mx-auto px-4">
        <div class="bg-white dark:bg-[#101d31] rounded-3xl overflow-hidden border border-slate-200 dark:border-[#263a55] shadow-sm grid grid-cols-1 md:grid-cols-12">
            <!-- Left: Pastor Photo -->
            <div class="md:col-span-4 bg-[#ff7a00] p-8 flex flex-col items-center justify-center text-center relative">
                <span class="absolute top-4 left-4 bg-black/25 text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">KATA SAMBUTAN</span>
                <img src="{{ !empty($pastor_foto) ? $pastor_foto : $imamImage }}" alt="Pastor Paroki" class="w-48 h-48 object-cover rounded-2xl shadow-lg mt-6 mb-4">
                <h3 class="text-white font-extrabold text-lg">{{ $pastor_paroki ?? 'Pastor Paroki' }}</h3>
                <p class="text-white/90 text-xs font-semibold">Pastor Paroki {{ $globalNamaParoki ?? 'SIPAROKI' }}</p>
            </div>

            <!-- Right: Content -->
            <div class="md:col-span-8 p-8 md:p-10 flex flex-col justify-center">
                <span class="st-badge mb-3"><i class="fas fa-quote-left me-1"></i> Gembala Umat</span>
                <h1 class="text-2xl md:text-3xl font-extrabold text-slate-900 dark:text-white mb-6">Sambutan Pastor Paroki</h1>
                <div class="prose dark:prose-invert max-w-none text-slate-600 dark:text-slate-300 leading-relaxed space-y-4 text-sm md:text-base">
                    <p class="text-lg font-semibold text-slate-900 dark:text-white italic">"Salve, Salam Sehat dan Berkah Dalem."</p>
                    <p>Puji syukur kita haturkan kepada Tuhan Yang Maha Esa atas hadirnya media informasi dan sistem pendataan digital paroki ini. Website ini menjadi sarana komunikasi, pewartaan, dan pelayanan yang menghubungkan seluruh umat dan komunitas basis gerejani.</p>
                    <p>Semoga sistem informasi ini memberikan kemudahan dalam pelayanan sakramen, administrasi, dan mempererat tali persekutuan kita sebagai satu tubuh Kristus.</p>
                    <div class="pt-6 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between flex-wrap gap-4">
                        <div>
                            <p class="font-bold text-slate-900 dark:text-white">{{ $pastor_paroki ?? 'Pastor Paroki' }}</p>
                            <p class="text-xs font-semibold text-teal-600 dark:text-teal-400">Pastor Paroki {{ $globalNamaParoki ?? $nama_paroki ?? 'SIPAROKI' }}</p>
                        </div>
                        <a href="/" class="inline-flex items-center gap-2 text-xs font-bold text-teal-600 hover:text-teal-700 bg-teal-50 dark:bg-teal-950/40 px-4 py-2 rounded-full transition">
                            <i class="fas fa-arrow-left"></i> Kembali ke Beranda
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
