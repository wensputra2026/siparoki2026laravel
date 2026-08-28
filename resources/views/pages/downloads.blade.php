@extends('layouts.app')

@section('title', 'Pusat Unduhan Dokumen & Formulir - ' . ($globalNamaParoki ?? 'SIPAROKI'))

@section('content')
<!-- Page Header / Breadcrumb Konoha Style -->
<section class="page-header">
    <div class="container">
        <h1 style="font-size: 2.6rem; font-weight: 700; margin-bottom: 15px; color: #ffffff;">Pusat Unduhan Dokumen</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="display: inline-flex; list-style: none; padding: 0; margin: 0 auto; gap: 12px; background: transparent; justify-content: center; align-items: center;">
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/" style="color: white; text-decoration: none; font-weight: 500;">Beranda</a>
                </li>
                <li class="breadcrumb-item active" style="background: var(--primary-orange, #ff9800); color: white; padding: 6px 18px; border-radius: 25px; font-size: 0.85rem; font-weight: 600;">
                    Unduhan
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- Content Section Konoha Style -->
<section class="content-section" style="padding: 60px 0 80px; background: #f4faf9;">
    <div class="container">
        @forelse(($downloadGroups ?? collect()) as $category => $items)
            <div style="background: #ffffff; border-radius: 15px; padding: 30px; box-shadow: 0 5px 20px rgba(0,0,0,0.06); margin-bottom: 30px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px; padding-bottom: 14px; border-bottom: 1px solid #eef2f6;">
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--primary-teal, #00897b); margin: 0; border-left: 4px solid var(--primary-orange, #ff9800); padding-left: 12px; display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-folder-open" style="color: #ff9800;"></i> {{ $category ?: 'Dokumen Resmi' }}
                    </h3>
                    <span style="background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); padding: 4px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 700;">
                        {{ $items->count() }} berkas
                    </span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(310px, 1fr)); gap: 18px;">
                    @foreach($items as $d)
                        @php
                            $filePath = $d->file_path ?? $d->file_name ?? $d->file ?? null;
                            $isManagedDownload = ($source ?? 'downloads') === 'downloads' && !empty($d->id);
                            $fileUrl = $isManagedDownload
                                ? route('downloads.file', encode_id($d->id))
                                : ((($source ?? 'downloads') === 'arsip_digital' && !empty($d->id))
                                    ? route('downloads.arsip.file', encode_id($d->id))
                                    : ($filePath ? (str_starts_with($filePath, 'http') ? $filePath : asset('assets/uploads/arsip/' . basename($filePath))) : '#'));
                            $size = $d->file_size ?? $d->ukuran_file ?? null;
                            $downloadCount = (int) ($d->download_count ?? 0);
                            $description = $d->keterangan ?? $d->deskripsi ?? null;
                        @endphp

                        <a href="{{ $fileUrl }}" target="_blank" rel="noopener" style="text-decoration: none; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; display: flex; align-items: center; justify-content: space-between; gap: 15px; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.borderColor='var(--primary-teal, #00897b)';" onmouseout="this.style.transform='translateY(0)'; this.style.borderColor='#e2e8f0';">
                            <div style="display: flex; align-items: center; gap: 14px; min-width: 0;">
                                <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); display: flex; align-items: center; justify-content: center; font-size: 1.25rem; flex-shrink: 0;">
                                    <i class="fas fa-file-pdf"></i>
                                </div>
                                <div style="min-width: 0;">
                                    <h4 style="font-size: 0.95rem; font-weight: 700; color: #1e293b; margin: 0 0 4px; line-height: 1.35; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        {{ $d->judul ?? $d->nama_file ?? 'Dokumen Paroki' }}
                                    </h4>
                                    <div style="font-size: 0.78rem; color: #94a3b8; display: flex; align-items: center; gap: 10px;">
                                        @if($size)
                                            <span><i class="fas fa-hdd" style="margin-right: 2px;"></i> {{ is_numeric($size) ? number_format(((int) $size) / 1024, 1, ',', '.') . ' KB' : $size }}</span>
                                        @endif
                                        <span><i class="fas fa-download" style="margin-right: 2px;"></i> {{ number_format($downloadCount, 0, ',', '.') }}x</span>
                                    </div>
                                </div>
                            </div>
                            <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--primary-orange, #ff9800); color: white; display: flex; align-items: center; justify-content: center; font-size: 0.88rem; flex-shrink: 0;">
                                <i class="fas fa-arrow-down"></i>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @empty
            <div style="background: #ffffff; border-radius: 15px; padding: 60px 20px; text-align: center; color: #94a3b8; box-shadow: 0 5px 20px rgba(0,0,0,0.06);">
                <i class="fas fa-folder-open" style="font-size: 3.5rem; margin-bottom: 15px; display: block; color: #cbd5e1;"></i>
                <p style="font-size: 1.05rem; font-weight: 600; margin: 0;">Belum ada dokumen atau formulir unduhan yang tersedia.</p>
            </div>
        @endforelse

        @if(isset($downloads) && method_exists($downloads, 'links'))
            <div style="margin-top: 40px; display: flex; justify-content: center;">
                {{ $downloads->links() }}
            </div>
        @endif

        <!-- Share Buttons -->
        <div style="margin-top: 35px;">
            @include('partials.share-buttons', ['title' => 'Pusat Unduhan Dokumen & Formulir - ' . ($globalNamaParoki ?? 'Paroki')])
        </div>
    </div>
</section>
@endsection

