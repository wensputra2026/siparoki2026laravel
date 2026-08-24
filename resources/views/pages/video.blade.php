@extends('layouts.app')
@section('title', 'Galeri Video - ' . ($globalNamaParoki ?? 'SIPAROKI'))
@section('content')
<div class="py-12 bg-slate-100 dark:bg-[#090e1a]">
    <div class="max-w-6xl mx-auto px-4">
        <div class="section-title">
            <span class="st-badge"><i class="fas fa-play me-1"></i> Video Paroki</span>
            <h2>Dokumentasi <span class="text-gradient">Audio Visual</span></h2>
            <p>Tayangan misa, perayaan sakramental, dan liputan peristiwa pastoral.</p>
        </div>
        <div class="text-center py-12 text-slate-400">
            <i class="fas fa-video text-4xl mb-3"></i>
            <p>Video dokumentasi belum tersedia.</p>
        </div>
    </div>
</div>
@endsection
