@extends('layouts.app')

@section('title', 'Pusat Unduhan Formulir & Dokumen - ' . ($globalNamaParoki ?? 'SIPAROKI'))

@section('content')
<section class="page-banner page-hero" id="main-content">
    <div class="page-banner-shape page-banner-shape--1" aria-hidden="true"></div>
    <div class="page-banner-shape page-banner-shape--2" aria-hidden="true"></div>
    <div class="container page-banner-content">
        <span class="page-banner-badge"><i class="fas fa-download"></i> Download Center</span>
        <h1>Pusat Unduhan <span class="text-gradient">{{ $globalNamaParoki ?? 'SIPAROKI' }}</span></h1>
        <p>Unduh formulir, dokumen pelayanan, warta, dan arsip digital paroki.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        @forelse(($downloadGroups ?? collect()) as $category => $items)
            <div class="download-group">
                <div class="download-group-header">
                    <i class="fas fa-folder-open"></i>
                    <h2>{{ $category ?: 'Dokumen Resmi' }}</h2>
                    <span class="download-group-count">{{ $items->count() }} berkas</span>
                </div>

                <div class="download-list">
                    @foreach($items as $d)
                        @php
                            $filePath = $d->file_path ?? $d->file_name ?? $d->file ?? null;
                            $isManagedDownload = ($source ?? 'downloads') === 'downloads' && !empty($d->id);
                            $fileUrl = $isManagedDownload
                                ? route('downloads.file', $d->id)
                                : ((($source ?? 'downloads') === 'arsip_digital' && !empty($d->id))
                                    ? route('downloads.arsip.file', $d->id)
                                    : ($filePath ? (str_starts_with($filePath, 'http') ? $filePath : asset('assets/uploads/arsip/' . basename($filePath))) : '#'));
                            $size = $d->file_size ?? $d->ukuran_file ?? null;
                            $downloadCount = (int) ($d->download_count ?? 0);
                            $description = $d->keterangan ?? $d->deskripsi ?? null;
                        @endphp

                        <a href="{{ $fileUrl }}" target="_blank" rel="noopener" class="download-card">
                            <div class="dc-icon">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="dc-body">
                                <h3>{{ $d->judul ?? $d->nama_file ?? 'Dokumen Paroki' }}</h3>
                                @if($description)
                                    <p>{{ Str::limit(strip_tags($description), 120) }}</p>
                                @endif
                            </div>
                            <div class="dc-meta">
                                @if($size)
                                    <span class="dc-size"><i class="fas fa-weight-hanging"></i> {{ is_numeric($size) ? number_format(((int) $size) / 1024, 1, ',', '.') . ' KB' : $size }}</span>
                                @endif
                                <span class="dc-download" title="Diunduh {{ number_format($downloadCount, 0, ',', '.') }} kali"><i class="fas fa-download"></i></span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="download-empty">
                <i class="fas fa-download"></i>
                <p>Belum ada berkas unduhan yang tersedia.</p>
            </div>
        @endforelse

        <div class="mt-8">
            {{ $downloads->links() }}
        </div>
    </div>
</section>
@endsection
