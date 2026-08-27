@extends('layouts.app')

@section('title', 'Kata Sambutan Pastor Paroki - ' . ($globalNamaParoki ?? $nama_paroki ?? 'SIPAROKI'))
@section('description', 'Kata sambutan dan pesan pastoral dari Pastor Paroki ' . ($globalNamaParoki ?? $nama_paroki ?? 'SIPAROKI') . ' untuk segenap umat beriman.')

@section('content')
@php
    $imamImage = file_exists(public_path('assets/frontend/siparoki/images/default-pastor.jpg'))
        ? asset('assets/frontend/siparoki/images/default-pastor.jpg')
        : (file_exists(public_path('assets/frontend/siparoki/images/default-principal.jpg'))
            ? asset('assets/frontend/siparoki/images/default-principal.jpg')
            : asset('images/pastor-avatar.svg'));

    // Dynamic photo resolution
    $resolvedPastorFoto = null;
    $rawFoto = $sambutan->foto_pastor ?? $sambutan->foto ?? null;
    if (!empty($rawFoto)) {
        if (str_starts_with($rawFoto, 'http://') || str_starts_with($rawFoto, 'https://')) {
            $resolvedPastorFoto = $rawFoto;
        } elseif (file_exists(public_path($rawFoto))) {
            $resolvedPastorFoto = asset($rawFoto);
        } elseif (file_exists(public_path('assets/' . $rawFoto))) {
            $resolvedPastorFoto = asset('assets/' . $rawFoto);
        } elseif (file_exists(public_path('assets/uploads/' . $rawFoto))) {
            $resolvedPastorFoto = asset('assets/uploads/' . $rawFoto);
        } elseif (file_exists(public_path('uploads/' . $rawFoto))) {
            $resolvedPastorFoto = asset('uploads/' . $rawFoto);
        } elseif (file_exists(public_path('storage/' . $rawFoto))) {
            $resolvedPastorFoto = asset('storage/' . $rawFoto);
        } else {
            $resolvedPastorFoto = asset($rawFoto);
        }
    }

    if (empty($resolvedPastorFoto)) {
        $resolvedPastorFoto = !empty($pastor_foto) ? $pastor_foto : $imamImage;
    }

    $namaPastor = $sambutan->nama_pastor ?? $pastor_paroki ?? 'Pastor Paroki';
    $jabatanPastor = $sambutan->jabatan_pastor ?? $sambutan->jabatan ?? 'Pastor Paroki';
    $namaParokiText = $globalNamaParoki ?? $nama_paroki ?? 'Paroki St. Vinsensius a Paulo Benlutu';
    $namaKeuskupanText = $globalNamaKeuskupan ?? $nama_keuskupan ?? 'Keuskupan Agung Kupang';
    $judulSambutan = $sambutan->judul_sambutan ?? 'Mewartakan Kasih, Membangun Persekutuan Umat Beriman';
    $kutipanSingkat = $sambutan->kutipan_singkat ?? 'Semoga kehadiran website dan sistem informasi digital ini menjadi jembatan kasih, sarana pewartaan kabar sukacita, serta mempererat tali persaudaraan kita sebagai satu tubuh mistik Kristus.';
    $isiSambutan = $sambutan->isi_sambutan ?? null;
@endphp

<!-- Page Header / Breadcrumb Konoha Style -->
<section class="page-header">
    <div class="container">
        <h1 style="font-size: 2.6rem; font-weight: 800; margin-bottom: 12px; color: #ffffff; letter-spacing: -0.5px;">Kata Sambutan Pastor</h1>
        <p style="font-size: 1rem; color: rgba(255,255,255,0.88); margin-bottom: 18px; max-width: 650px; margin-left: auto; margin-right: auto; line-height: 1.6;">
            Pesan pastoral dan kata sambutan untuk seluruh keluarga beriman di {{ $namaParokiText }}.
        </p>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="display: inline-flex; list-style: none; padding: 0; margin: 0 auto; gap: 8px; background: transparent; justify-content: center; align-items: center; flex-wrap: wrap;">
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/" style="color: white; text-decoration: none; font-weight: 500;">
                        <i class="fa-solid fa-house me-1" style="font-size: 0.75rem;"></i> Beranda
                    </a>
                </li>
                <li class="breadcrumb-separator" style="color: rgba(255,255,255,0.75); font-size: 0.7rem; display: inline-flex; align-items: center;">
                    <i class="fa-solid fa-chevron-right"></i>
                </li>
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/profil" style="color: white; text-decoration: none; font-weight: 500;">Profil</a>
                </li>
                <li class="breadcrumb-separator" style="color: rgba(255,255,255,0.75); font-size: 0.7rem; display: inline-flex; align-items: center;">
                    <i class="fa-solid fa-chevron-right"></i>
                </li>
                <li class="breadcrumb-item active" style="background: var(--primary-orange, #ff9800); color: white; padding: 6px 18px; border-radius: 25px; font-size: 0.85rem; font-weight: 700; box-shadow: 0 4px 12px rgba(255,152,0,0.35);">
                    Kata Sambutan
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- Content Section Konoha Style -->
<section class="content-section" style="padding: 50px 0 80px; background: #f4faf9; min-height: 70vh;">
    <div class="container">
        
        <div class="row g-4 justify-content-center">
            
            <!-- Left Column: Pastor Identity Card -->
            <div class="col-lg-4 col-md-5">
                <div style="background: #ffffff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); border-top: 5px solid var(--primary-teal, #00897b); overflow: hidden; position: sticky; top: 100px;">
                    
                    <!-- Top Badge & Frame -->
                    <div style="background: linear-gradient(135deg, #00897b 0%, #00695c 100%); padding: 32px 24px 24px; text-align: center; position: relative;">
                        <div style="width: 150px; height: 150px; margin: 0 auto 14px; border-radius: 22px; overflow: hidden; border: 4px solid rgba(255,255,255,0.9); box-shadow: 0 8px 24px rgba(0,0,0,0.22); background: #ffffff;">
                            <img src="{{ $resolvedPastorFoto }}" alt="{{ $namaPastor }}" style="width: 100%; height: 100%; object-fit: cover; object-position: top center; transition: transform 0.3s ease;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                        </div>

                        <h3 style="color: #ffffff; font-size: 1.18rem; font-weight: 800; margin: 0 0 4px; line-height: 1.3;">
                            {{ $namaPastor }}
                        </h3>
                        <p style="color: rgba(255,255,255,0.9); font-size: 0.82rem; font-weight: 600; margin: 0;">
                            {{ $jabatanPastor }}
                        </p>
                    </div>

                    <!-- Details Body -->
                    <div style="padding: 24px 22px;">
                        <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 22px;">
                            <div style="display: flex; align-items: center; gap: 12px; padding: 10px 14px; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                                <div style="width: 34px; height: 34px; border-radius: 50%; background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); display: flex; align-items: center; justify-content: center; font-size: 0.9rem; flex-shrink: 0;">
                                    <i class="fa-solid fa-church"></i>
                                </div>
                                <div>
                                    <span style="font-size: 0.7rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; display: block;">Wilayah Pelayanan</span>
                                    <span style="font-size: 0.85rem; font-weight: 700; color: #1e293b; display: block;">{{ $namaParokiText }}</span>
                                </div>
                            </div>

                            <div style="display: flex; align-items: center; gap: 12px; padding: 10px 14px; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                                <div style="width: 34px; height: 34px; border-radius: 50%; background: rgba(255,152,0,0.1); color: var(--primary-orange, #ff9800); display: flex; align-items: center; justify-content: center; font-size: 0.9rem; flex-shrink: 0;">
                                    <i class="fa-solid fa-place-of-worship"></i>
                                </div>
                                <div>
                                    <span style="font-size: 0.7rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; display: block;">Keuskupan</span>
                                    <span style="font-size: 0.85rem; font-weight: 700; color: #1e293b; display: block;">{{ $namaKeuskupanText }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Links -->
                        <div style="display: flex; flex-direction: column; gap: 10px;">
                            <a href="/jadwal-misa" style="display: flex; align-items: center; justify-content: center; gap: 8px; background: var(--primary-teal, #00897b); color: #ffffff; padding: 10px 16px; border-radius: 12px; font-weight: 700; font-size: 0.84rem; text-decoration: none; box-shadow: 0 4px 14px rgba(0,137,123,0.25); transition: all 0.2s ease;" onmouseover="this.style.background='#00796b'" onmouseout="this.style.background='var(--primary-teal, #00897b)'">
                                <i class="fa-regular fa-clock"></i> Lihat Jadwal Misa
                            </a>
                            <a href="/profil" style="display: flex; align-items: center; justify-content: center; gap: 8px; background: #ffffff; color: var(--primary-teal, #00897b); border: 1.5px solid var(--primary-teal, #00897b); padding: 9px 16px; border-radius: 12px; font-weight: 700; font-size: 0.84rem; text-decoration: none; transition: all 0.2s ease;" onmouseover="this.style.background='rgba(0,137,123,0.06)'" onmouseout="this.style.background='#ffffff'">
                                <i class="fa-solid fa-address-card"></i> Profil Paroki
                            </a>
                            <a href="/kontak" style="display: flex; align-items: center; justify-content: center; gap: 8px; background: #f1f5f9; color: #475569; padding: 9px 16px; border-radius: 12px; font-weight: 700; font-size: 0.84rem; text-decoration: none; transition: all 0.2s ease;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                                <i class="fa-solid fa-envelope"></i> Hubungi Sekretariat
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Right Column: Speech & Pastoral Message -->
            <div class="col-lg-8 col-md-7">
                <div style="background: #ffffff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); padding: 40px 42px; border-left: 5px solid var(--primary-orange, #ff9800);">
                    
                    <!-- Header Badge & Title -->
                    <div style="margin-bottom: 24px;">
                        <span style="display: inline-flex; align-items: center; gap: 6px; background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.8px; padding: 6px 16px; border-radius: 20px; margin-bottom: 12px;">
                            <i class="fa-solid fa-quote-left"></i> Pesan & Reksa Pastoral
                        </span>
                        <h2 style="font-size: 1.85rem; font-weight: 800; color: #0f172a; line-height: 1.35; margin: 0;">
                            {{ $judulSambutan }}
                        </h2>
                    </div>

                    <!-- Golden Quote Highlight Box -->
                    @if(!empty($kutipanSingkat))
                        <div style="background: linear-gradient(135deg, rgba(255,152,0,0.06) 0%, rgba(0,137,123,0.06) 100%); border-left: 4px solid var(--primary-orange, #ff9800); border-radius: 14px; padding: 20px 24px; margin-bottom: 30px; position: relative;">
                            <i class="fa-solid fa-quote-right" style="position: absolute; right: 20px; bottom: 14px; font-size: 2.2rem; color: rgba(255,152,0,0.18);"></i>
                            <p style="margin: 0; font-size: 1.02rem; font-weight: 600; color: #334155; font-style: italic; line-height: 1.7;">
                                "{{ $kutipanSingkat }}"
                            </p>
                        </div>
                    @endif

                    <!-- Speech Content -->
                    <div style="color: #334155; font-size: 1.02rem; line-height: 1.85; display: flex; flex-direction: column; gap: 18px; text-align: justify;">
                        @if(!empty($isiSambutan))
                            {!! nl2br(e($isiSambutan)) !!}
                        @else
                            <p style="font-size: 1.08rem; font-weight: 700; color: var(--primary-teal, #00897b); margin-bottom: 4px;">
                                <em>Salve, Salam Sehat, Kasih dan Berkat Tuhan bagi kita sekalian.</em>
                            </p>
                            <p>
                                Puji dan syukur kita haturkan ke hadirat Tuhan Yang Maha Kasih atas segala berkat dan penyertaan-Nya bagi seluruh umat beriman di <strong>{{ $namaParokiText }}</strong>. Di era transformasi informasi saat ini, kehadiran media informasi digital ini adalah wujud nyata dari upaya kita memperluas warta keselamatan, mempercepat komunikasi, dan meningkatkan kualitas pelayanan pastoral gerejani.
                            </p>
                            <p>
                                Website paroki ini dirancang bukan sekadar sebagai etalase informasi publik, melainkan sebagai rumah perjumpaan digital yang menyatukan stasi-stasi, kapela, lingkungan, dan Komunitas Umat Basis (KUB). Melalui sarana ini, seluruh umat dapat dengan mudah mengakses informasi jadwal liturgi ekaristi, administrasi sakramen, warta kegiatan pastoral, hingga transparansi sensus dan tata kelola paroki.
                            </p>
                            <p>
                                Kami mengajak seluruh dewan pastoral paroki, pengurus stasi, ketua KUB, kaum muda Katolik (OMK), serikat kerasulan awam, dan segenap keluarga beriman untuk terus aktif berpartisipasi, solider, dan saling menguatkan dalam ikatan persaudaraan sejati. Kiranya semangat pelayanan Santo Vinsensius a Paulo senantiasa mengobarkan hati kita untuk melayani sesama dengan tulus, penuh kasih, dan berbelarasa bagi mereka yang membutuhkan.
                            </p>
                            <p>
                                Selamat menjelajahi website paroki kita. Semoga Tuhan Yang Maha Esa senantiasa memberkati setiap langkah reksa pastoral dan karya hidup kita sehari-hari.
                            </p>
                        @endif
                    </div>

                    <!-- Signature Block -->
                    <div style="margin-top: 35px; padding-top: 25px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
                        <div>
                            <p style="font-size: 0.85rem; color: #64748b; margin: 0 0 4px; font-weight: 500;">Salam Kasih dan Doa,</p>
                            <h4 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0;">{{ $namaPastor }}</h4>
                            <p style="font-size: 0.82rem; font-weight: 600; color: var(--primary-teal, #00897b); margin: 2px 0 0;">{{ $jabatanPastor }} {{ $namaParokiText }}</p>
                        </div>
                    </div>

                    <!-- Bottom Nav -->
                    <div style="margin-top: 30px; padding-top: 20px; border-top: 1px dashed #cbd5e1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                        <a href="/" style="display: inline-flex; align-items: center; gap: 8px; color: #64748b; font-weight: 600; font-size: 0.85rem; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='var(--primary-teal, #00897b)'" onmouseout="this.style.color='#64748b'">
                            <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
                        </a>
                        <div style="display: flex; gap: 8px;">
                            <a href="/visi-misi" style="display: inline-flex; align-items: center; gap: 6px; background: #f1f5f9; color: #334155; font-size: 0.82rem; font-weight: 600; padding: 6px 14px; border-radius: 20px; text-decoration: none; transition: background 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                                <i class="fa-solid fa-eye text-teal-600"></i> Visi Misi
                            </a>
                            <a href="/riwayat-pastor" style="display: inline-flex; align-items: center; gap: 6px; background: rgba(255,152,0,0.1); color: var(--primary-orange, #ff9800); font-size: 0.82rem; font-weight: 700; padding: 6px 14px; border-radius: 20px; text-decoration: none; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,152,0,0.2)'" onmouseout="this.style.background='rgba(255,152,0,0.1)'">
                                <i class="fa-solid fa-users"></i> Riwayat Pastor
                            </a>
                        </div>
                    </div>

                </div>
            </div>

        </div>

    </div>
</section>
@endsection
