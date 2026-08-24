@extends('layouts.app')
@section('title', 'Riwayat Pastor Paroki - ' . ($globalNamaParoki ?? 'St. Vinsensius a Paulo Benlutu'))
@section('content')
<div class="py-12 bg-slate-100 dark:bg-[#090e1a]">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white dark:bg-[#101d31] rounded-3xl p-6 sm:p-8 border border-slate-200 dark:border-[#263a55] shadow-sm">
            <span class="st-badge mb-2"><i class="fas fa-user-clock me-1"></i> Gembala Paroki</span>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 dark:text-white mb-2">Riwayat Pastor Paroki dari Masa ke Masa</h1>
            <p class="text-xs sm:text-sm text-slate-500 mb-8">{{ $globalNamaParoki ?? 'Paroki St. Vinsensius a Paulo Benlutu' }}</p>

            <div class="space-y-4">
                @forelse($riwayat ?? [] as $r)
                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-800/40 border border-slate-200 dark:border-slate-700/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300 flex items-center justify-center font-bold text-lg shrink-0">
                                <i class="fa-solid fa-user-tie"></i>
                            </div>
                            <div>
                                <h3 class="font-extrabold text-base text-slate-900 dark:text-white">
                                    {{ $r->nama_lengkap_gelar ?? \App\Models\MasterPastor::formatNama($r) }}
                                </h3>
                                <p class="text-xs text-sky-600 dark:text-sky-400 font-semibold">{{ $r->jabatan ?? 'Pastor Paroki' }}</p>
                            </div>
                        </div>
                        <div class="text-left sm:text-right">
                            <span class="inline-block px-3 py-1 rounded-full text-xs font-bold bg-slate-200/80 dark:bg-slate-700 text-slate-700 dark:text-slate-200">
                                {{ $r->periode_mulai ?? $r->tahun_mulai ?? '-' }} &mdash; {{ $r->periode_selesai ?? $r->tahun_selesai ?? 'Sekarang' }}
                            </span>
                            <p class="text-[11px] text-emerald-600 dark:text-emerald-400 font-bold mt-1">
                                {{ $r->status_pelayanan ?? $r->status ?? 'Aktif' }}
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 text-slate-400">
                        <i class="fas fa-user-shield text-4xl mb-3"></i>
                        <p>Belum ada data riwayat pastor.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
