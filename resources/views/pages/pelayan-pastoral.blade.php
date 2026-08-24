@extends('layouts.app')
@section('title', 'Pelayan Pastoral - ' . ($globalNamaParoki ?? 'St. Vinsensius a Paulo Benlutu'))
@section('content')
<div class="py-12 bg-slate-100 dark:bg-[#090e1a]">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white dark:bg-[#101d31] rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-[#263a55] shadow-sm">
            <span class="st-badge mb-2"><i class="fas fa-users me-1"></i> Reksa Pastoral</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mb-2">Pelayan Pastoral Saat Ini</h1>
            <p class="text-xs sm:text-sm text-slate-500 mb-8">{{ $globalNamaParoki ?? 'Paroki St. Vinsensius a Paulo Benlutu' }}</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @forelse($pastorList ?? [] as $p)
                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700/50 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-sky-100 text-sky-700 dark:bg-sky-900/40 dark:text-sky-300 flex items-center justify-center font-bold text-lg shrink-0">
                            <i class="fa-solid fa-user-tie"></i>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-sm text-slate-900 dark:text-white">
                                {{ $p->nama_lengkap_gelar ?? \App\Models\MasterPastor::formatNama($p) }}
                            </h3>
                            <p class="text-xs text-sky-600 dark:text-sky-400 font-semibold">{{ $p->jabatan ?? 'Pastor Rekan' }}</p>
                            <p class="text-[11px] text-slate-400 mt-0.5">{{ $p->keuskupan ?? 'Keuskupan Agung Kupang' }}</p>
                        </div>
                    </div>
                @empty
                    <div class="p-6 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700/50 col-span-2">
                        <h3 class="font-bold text-lg text-slate-900 dark:text-white">Pastor Paroki</h3>
                        <p class="text-sm text-sky-600 font-semibold mt-1">{{ $pastor_paroki ?? $profil->pastor_paroki ?? 'Pastor Paroki' }}</p>
                        <p class="text-xs text-slate-500 mt-2">Penanggung jawab umum reksa pastoral dan paroki.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
