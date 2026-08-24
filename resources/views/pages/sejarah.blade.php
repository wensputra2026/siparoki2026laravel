@extends('layouts.app')
@section('title', 'Sejarah Paroki - ' . ($globalNamaParoki ?? 'SIPAROKI'))
@section('content')
<div class="py-12 bg-slate-100 dark:bg-[#090e1a]">
    <div class="max-w-4xl mx-auto px-4">
        <div class="bg-white dark:bg-[#101d31] rounded-3xl p-8 border border-slate-200 dark:border-[#263a55] shadow-sm">
            <span class="st-badge mb-2"><i class="fas fa-book-open me-1"></i> Sejarah Paroki</span>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white mb-6">Sejarah {{ $globalNamaParoki ?? 'SIPAROKI' }}</h1>
            <div class="prose dark:prose-invert max-w-none text-slate-600 dark:text-slate-300 leading-relaxed space-y-4">
                <p>{{ $globalNamaParoki ?? $nama_paroki ?? 'Paroki' }} merupakan komunitas umat Katolik yang bertumbuh dalam iman, persekutuan, pelayanan, dan pewartaan di tengah masyarakat.</p>
                <p>Dengan semangat persekutuan, paroki ini terus membina kehidupan iman umat melalui perayaan sakramen, pembinaan kategorial, serta karya sosial karitatif.</p>
            </div>
        </div>
    </div>
</div>
@endsection
