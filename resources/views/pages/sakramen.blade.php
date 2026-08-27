@extends('layouts.public')

@section('title', 'Pengajuan Sakramen - ' . ($globalNamaParoki ?? 'SIPAROKI'))
@section('description', 'Formulir pendaftaran dan pengajuan sakramen online ' . ($globalNamaParoki ?? 'SIPAROKI') . '.')

@section('content')
<section class="page-header">
    <div class="container">
        <span class="st-badge"><i class="fas fa-hands-praying me-1"></i> Layanan Umat</span>
        <h1>Pengajuan Sakramen</h1>
        <p>Ajukan permohonan sakramen secara online untuk ditindaklanjuti oleh sekretariat paroki.</p>
    </div>
</section>

<section class="content-section sacrament-page">
    <div class="container">
        <div class="sacrament-layout">
            <aside class="sacrament-info">
                <div class="sacrament-info-icon">
                    <i class="fas fa-church"></i>
                </div>
                <h2>Formulir Permohonan</h2>
                <p>Lengkapi data dengan teliti agar petugas dapat memverifikasi permohonan dan menghubungi Anda melalui kontak yang tersedia.</p>
                <div class="sacrament-info-list">
                    <span><i class="fas fa-circle-check"></i> Data pemohon</span>
                    <span><i class="fas fa-circle-check"></i> Jenis sakramen</span>
                    <span><i class="fas fa-circle-check"></i> Kontak aktif</span>
                </div>
            </aside>

            <div class="sacrament-form-card">
                @livewire('form-sakramen')
            </div>
        </div>
    </div>
</section>
@endsection
