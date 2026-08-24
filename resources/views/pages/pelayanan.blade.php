@extends('layouts.app')
@section('title', 'Daftar Layanan Sakramen & Pastoral - ' . ($globalNamaParoki ?? 'SIPAROKI'))
@section('content')
<div class="py-12 bg-slate-100 dark:bg-[#090e1a]">
    <div class="max-w-5xl mx-auto px-4">
        <div class="section-title">
            <span class="st-badge"><i class="fas fa-hand-holding-heart me-1"></i> Pelayanan</span>
            <h2>Layanan <span class="text-gradient">Pastoral &amp; Sakramen</span></h2>
            <p>Daftar alur dan prosedur permohonan penerimaan sakramen di gereja.</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
            <div class="bg-white dark:bg-[#101d31] p-6 rounded-2xl border border-slate-200 dark:border-[#263a55] shadow-sm text-center">
                <div class="w-14 h-14 bg-sky-100 dark:bg-sky-950 text-sky-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-xl">
                    <i class="fa-solid fa-water"></i>
                </div>
                <h3 class="font-bold text-slate-900 dark:text-white">Sakramen Baptis</h3>
                <p class="text-xs text-slate-500 mt-2">Baptis bayi, anak-anak, dan dewasa.</p>
            </div>
            <div class="bg-white dark:bg-[#101d31] p-6 rounded-2xl border border-slate-200 dark:border-[#263a55] shadow-sm text-center">
                <div class="w-14 h-14 bg-purple-100 dark:bg-purple-950 text-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-xl">
                    <i class="fa-solid fa-bread-slice"></i>
                </div>
                <h3 class="font-bold text-slate-900 dark:text-white">Komuni Pertama</h3>
                <p class="text-xs text-slate-500 mt-2">Penerimaan tubuh Kristus pertama kali.</p>
            </div>
            <div class="bg-white dark:bg-[#101d31] p-6 rounded-2xl border border-slate-200 dark:border-[#263a55] shadow-sm text-center">
                <div class="w-14 h-14 bg-amber-100 dark:bg-amber-950 text-amber-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-xl">
                    <i class="fa-solid fa-fire"></i>
                </div>
                <h3 class="font-bold text-slate-900 dark:text-white">Sakramen Krisma</h3>
                <p class="text-xs text-slate-500 mt-2">Penerimaan Roh Kudus kepenuhan iman.</p>
            </div>
            <div class="bg-white dark:bg-[#101d31] p-6 rounded-2xl border border-slate-200 dark:border-[#263a55] shadow-sm text-center">
                <div class="w-14 h-14 bg-emerald-100 dark:bg-emerald-950 text-emerald-600 rounded-2xl flex items-center justify-center mx-auto mb-4 text-xl">
                    <i class="fa-solid fa-ring"></i>
                </div>
                <h3 class="font-bold text-slate-900 dark:text-white">Sakramen Pernikahan</h3>
                <p class="text-xs text-slate-500 mt-2">KPPK dan penyelidikan kanonik.</p>
            </div>
        </div>
        <div class="text-center mt-10">
            <a href="/sakramen" class="inline-flex items-center gap-2 bg-sky-600 hover:bg-sky-700 text-white font-bold px-8 py-3.5 rounded-2xl text-sm transition shadow-lg shadow-sky-600/20">
                <i class="fa-solid fa-file-pen"></i> <span>Formulir Pengajuan Sakramen Online</span>
            </a>
        </div>
    </div>
</div>
@endsection

