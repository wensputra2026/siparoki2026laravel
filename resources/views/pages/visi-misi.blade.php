@extends('layouts.app')
@section('title', 'Visi & Misi - Kristus Raja Katedral / Bonipoi')
@section('content')
<div class="py-12 bg-slate-100 dark:bg-[#090e1a]">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white dark:bg-[#101d31] rounded-3xl p-8 border border-slate-200 dark:border-[#263a55] shadow-sm">
            <span class="st-badge mb-2"><i class="fas fa-bullseye me-1"></i> Arah Pastoral</span>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white mb-6">Visi &amp; Misi Paroki</h1>
            <div class="space-y-6 text-slate-600 dark:text-slate-300">
                <div class="bg-sky-50 dark:bg-sky-950/40 p-6 rounded-2xl border border-sky-100 dark:border-sky-900/50">
                    <h3 class="text-lg font-bold text-sky-800 dark:text-sky-300 mb-2">VISI</h3>
                    <p class="leading-relaxed">Menjadi persekutuan umat Allah yang mandiri, solider, beriman teguh, dan berakar pada Sabda Allah serta Tradisi Gereja Katolik.</p>
                </div>
                <div class="bg-slate-50 dark:bg-slate-800/40 p-6 rounded-2xl border border-slate-200 dark:border-slate-700/50">
                    <h3 class="text-lg font-bold text-slate-800 dark:text-slate-200 mb-2">MISI</h3>
                    <ul class="list-disc list-inside space-y-2 leading-relaxed">
                        <li>Meningkatkan penghayatan iman melalui perayaan sakramental yang hidup dan berbuah.</li>
                        <li>Memperkuat basis komunitas umat beriman melalui perjumpaan doa KUB dan lingkungan.</li>
                        <li>Mengembangkan pelayanan pastoral yang inklusif, ramah, dan solider terhadap kaum miskin dan terlantar.</li>
                        <li>Mendorong transparansi dan digitalisasi tata kelola administrasi paroki.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
