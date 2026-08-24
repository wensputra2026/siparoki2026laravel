@extends('layouts.app')

@section('title', 'Hubungi Kami - ' . ($nama_paroki ?? $globalNamaParoki ?? 'SIPAROKI'))
@section('description', 'Hubungi sekretariat ' . ($nama_paroki ?? $globalNamaParoki ?? 'SIPAROKI') . ' untuk informasi pelayanan, administrasi, jadwal, dan lokasi paroki.')

@section('content')
@php
    $paroki = $activeParoki ?? null;
    $displayName = $nama_paroki ?? $paroki?->nama_paroki ?? $globalNamaParoki ?? 'SIPAROKI';
    $mapLat = $latitude ?? $paroki?->latitude ?? null;
    $mapLng = $longitude ?? $paroki?->longitude ?? null;
    $primaryAddress = $alamat ?? $paroki?->alamat ?? 'Benlutu, Kec. Batu Putih, Kab. Timor Tengah Selatan, Nusa Tenggara Timur';
    $telp = $telepon ?? $paroki?->telepon ?? '0812-3456-7890';
    $wa = $whatsapp ?? $paroki?->whatsapp ?? '0812-3456-7890';
    $mail = $email ?? $paroki?->email ?? 'sekretariat@parokibenlutu.org';
@endphp

<!-- Page Header / Breadcrumb Konoha Style -->
<section class="page-header" style="background: linear-gradient(rgba(10, 30, 25, 0.75), rgba(10, 30, 25, 0.85)), url('{{ $globalHeroBg ?? '/assets/uploads/profil/hero_bg.jpg' }}') center/cover; padding: 90px 0 50px; color: white; text-align: center;">
    <div class="container" style="max-width: 1180px; margin: 0 auto; padding: 0 20px;">
        <h1 style="font-size: 2.6rem; font-weight: 700; margin-bottom: 15px; color: #ffffff;">Hubungi Kami</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="display: inline-flex; list-style: none; padding: 0; margin: 0 auto; gap: 12px; background: transparent; justify-content: center; align-items: center;">
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/" style="color: white; text-decoration: none; font-weight: 500;">Beranda</a>
                </li>
                <li class="breadcrumb-item active" style="background: var(--primary-orange, #ff9800); color: white; padding: 6px 18px; border-radius: 25px; font-size: 0.85rem; font-weight: 600;">
                    Hubungi Kami
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- Contact Main Section Konoha Style -->
<section class="contact-section" style="padding: 60px 0 80px; background: #f4faf9;">
    <div class="container" style="max-width: 1180px; margin: 0 auto; padding: 0 20px;">
        
        @if(session('success'))
            <div style="margin-bottom: 25px; border-radius: 12px; border: 1px solid #a7f3d0; background: #ecfdf5; padding: 16px 20px; font-size: 0.9rem; font-weight: 600; color: #065f46; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-check-circle" style="font-size: 1.2rem; color: #10b981;"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if($errors->any())
            <div style="margin-bottom: 25px; border-radius: 12px; border: 1px solid #fecdd3; background: #fff1f2; padding: 16px 20px; font-size: 0.9rem; color: #9f1239;">
                <p style="font-weight: 700; margin-bottom: 6px;"><i class="fas fa-exclamation-triangle" style="margin-right: 6px;"></i> Mohon periksa isian formulir:</p>
                <ul style="margin: 0; padding-left: 24px; font-size: 0.85rem;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 32px; align-items: start;">
            
            <!-- Left: Contact Information Card -->
            <div class="contact-info" style="background: #ffffff; padding: 35px 30px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.06); height: 100%;">
                <h4 style="font-size: 1.2rem; font-weight: 700; color: var(--primary-teal, #00897b); margin-bottom: 25px; border-left: 4px solid var(--primary-orange, #ff9800); padding-left: 12px;">
                    Informasi Sekretariat
                </h4>

                <!-- Item 1: Alamat -->
                <div class="contact-item" style="display: flex; align-items: flex-start; margin-bottom: 24px;">
                    <div class="contact-icon" style="width: 44px; height: 44px; background: var(--primary-teal, #00897b); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 16px; font-size: 1.1rem; flex-shrink: 0;">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div>
                        <h5 style="font-size: 0.95rem; font-weight: 700; color: #1e293b; margin-bottom: 4px;">Alamat Paroki</h5>
                        <p style="font-size: 0.88rem; color: #64748b; margin: 0; line-height: 1.5;">{{ $primaryAddress }}</p>
                    </div>
                </div>

                <!-- Item 2: Telepon & WhatsApp -->
                <div class="contact-item" style="display: flex; align-items: flex-start; margin-bottom: 24px;">
                    <div class="contact-icon" style="width: 44px; height: 44px; background: var(--primary-teal, #00897b); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 16px; font-size: 1.1rem; flex-shrink: 0;">
                        <i class="fas fa-phone-alt"></i>
                    </div>
                    <div>
                        <h5 style="font-size: 0.95rem; font-weight: 700; color: #1e293b; margin-bottom: 4px;">Telepon & WhatsApp</h5>
                        <p style="font-size: 0.88rem; color: #64748b; margin: 0; line-height: 1.5;">
                            Kantor: <a href="tel:{{ preg_replace('/\s+/', '', $telp) }}" style="color: #0284c7; text-decoration: none;">{{ $telp }}</a><br>
                            WhatsApp: <a href="https://wa.me/{{ preg_replace('/\D+/', '', $wa) }}" target="_blank" style="color: #10b981; text-decoration: none;">{{ $wa }}</a>
                        </p>
                    </div>
                </div>

                <!-- Item 3: Email -->
                <div class="contact-item" style="display: flex; align-items: flex-start; margin-bottom: 24px;">
                    <div class="contact-icon" style="width: 44px; height: 44px; background: var(--primary-teal, #00897b); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 16px; font-size: 1.1rem; flex-shrink: 0;">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div>
                        <h5 style="font-size: 0.95rem; font-weight: 700; color: #1e293b; margin-bottom: 4px;">Email Resmi</h5>
                        <p style="font-size: 0.88rem; color: #64748b; margin: 0; line-height: 1.5;">
                            <a href="mailto:{{ $mail }}" style="color: var(--primary-teal, #00897b); text-decoration: none;">{{ $mail }}</a>
                        </p>
                    </div>
                </div>

                <!-- Item 4: Jam Operasional -->
                <div class="contact-item" style="display: flex; align-items: flex-start; margin-bottom: 28px;">
                    <div class="contact-icon" style="width: 44px; height: 44px; background: var(--primary-teal, #00897b); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 16px; font-size: 1.1rem; flex-shrink: 0;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div>
                        <h5 style="font-size: 0.95rem; font-weight: 700; color: #1e293b; margin-bottom: 4px;">Jam Pelayanan Sekretariat</h5>
                        <p style="font-size: 0.88rem; color: #64748b; margin: 0; line-height: 1.6;">
                            Senin - Jumat: 08.00 - 15.00 WITA<br>
                            Sabtu: 08.00 - 13.00 WITA<br>
                            Minggu & Libur Nasional: Tutup (Hanya Layanan Darurat)
                        </p>
                    </div>
                </div>

                <!-- Media Sosial -->
                <div style="border-top: 1px solid #eef2f6; padding-top: 20px;">
                    <h5 style="font-size: 0.9rem; font-weight: 700; color: #1e293b; margin-bottom: 12px;">Ikuti Kami:</h5>
                    <div class="contact-social-icons" style="display: flex; gap: 10px;">
                        <a href="#" style="width: 38px; height: 38px; background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem; text-decoration: none; transition: all 0.3s;"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" style="width: 38px; height: 38px; background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem; text-decoration: none; transition: all 0.3s;"><i class="fab fa-instagram"></i></a>
                        <a href="#" style="width: 38px; height: 38px; background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem; text-decoration: none; transition: all 0.3s;"><i class="fab fa-youtube"></i></a>
                        <a href="https://wa.me/{{ preg_replace('/\D+/', '', $wa) }}" target="_blank" style="width: 38px; height: 38px; background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem; text-decoration: none; transition: all 0.3s;"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
            </div>

            <!-- Right: Contact Form Card -->
            <div class="contact-form" style="background: #ffffff; padding: 35px 30px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.06);">
                <h4 style="font-size: 1.2rem; font-weight: 700; color: var(--primary-teal, #00897b); margin-bottom: 8px; border-left: 4px solid var(--primary-orange, #ff9800); padding-left: 12px;">
                    Kirim Pesan
                </h4>
                <p style="font-size: 0.88rem; color: #64748b; margin-bottom: 24px; padding-left: 16px;">
                    Sampaikan permohonan informasi, intensi misa, atau pertanyaan ke sekretariat paroki.
                </p>

                <form action="{{ route('kontak.kirim') }}" method="POST">
                    @csrf
                    <div class="hidden" style="display: none;">
                        <input id="website_url" name="website_url" type="text" tabindex="-1" autocomplete="off">
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label for="nama" style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px;">Nama Lengkap <span style="color: #ef4444;">*</span></label>
                        <input id="nama" name="nama" type="text" value="{{ old('nama') }}" required class="form-control" placeholder="Masukkan nama lengkap Anda..." style="width: 100%; border: 1.5px solid #E0E0E0; padding: 10px 14px; border-radius: 10px; font-size: 0.9rem; outline: none;">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px;">
                        <div>
                            <label for="email" style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px;">Email</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" class="form-control" placeholder="nama@email.com" style="width: 100%; border: 1.5px solid #E0E0E0; padding: 10px 14px; border-radius: 10px; font-size: 0.9rem; outline: none;">
                        </div>
                        <div>
                            <label for="telepon" style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px;">WhatsApp / Telepon</label>
                            <input id="telepon" name="telepon" type="text" value="{{ old('telepon') }}" class="form-control" placeholder="081234567890" style="width: 100%; border: 1.5px solid #E0E0E0; padding: 10px 14px; border-radius: 10px; font-size: 0.9rem; outline: none;">
                        </div>
                    </div>

                    <div style="margin-bottom: 16px;">
                        <label for="subjek" style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px;">Subjek Pesan</label>
                        <input id="subjek" name="subjek" type="text" value="{{ old('subjek') }}" class="form-control" placeholder="Contoh: Informasi Sakramen Baptis, Intensi Misa, dll" style="width: 100%; border: 1.5px solid #E0E0E0; padding: 10px 14px; border-radius: 10px; font-size: 0.9rem; outline: none;">
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label for="pesan" style="display: block; font-size: 0.85rem; font-weight: 600; color: #334155; margin-bottom: 6px;">Isi Pesan <span style="color: #ef4444;">*</span></label>
                        <textarea id="pesan" name="pesan" required rows="5" class="form-control" placeholder="Tuliskan pesan atau pertanyaan Anda secara rinci..." style="width: 100%; border: 1.5px solid #E0E0E0; padding: 10px 14px; border-radius: 10px; font-size: 0.9rem; outline: none;">{{ old('pesan') }}</textarea>
                    </div>

                    <button type="submit" class="btn-submit" style="background: var(--primary-teal, #00897b); color: white; padding: 12px 36px; border-radius: 25px; border: none; font-weight: 600; font-size: 0.92rem; cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center; gap: 8px;">
                        <i class="fas fa-paper-plane"></i>
                        <span>Kirim Pesan Sekarang</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- Interactive Map Section Konoha Style -->
        <div style="margin-top: 45px; background: #ffffff; padding: 25px; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.06);">
            <h4 style="font-size: 1.15rem; font-weight: 700; color: var(--primary-teal, #00897b); margin-bottom: 18px; border-left: 4px solid var(--primary-orange, #ff9800); padding-left: 12px;">
                Lokasi Gereja & Sekretariat
            </h4>
            <div id="contact-map" class="online-map" style="width: 100%; height: 380px; border-radius: 12px; overflow: hidden;" data-title="{{ e($displayName) }}" data-address="{{ e($primaryAddress) }}" data-lat="{{ e($mapLat) }}" data-lng="{{ e($mapLng) }}"></div>
        </div>
    </div>
</section>

@push('scripts')
    <script src="/js/pages/kontak.js?v={{ @filemtime(public_path('js/pages/kontak.js')) ?: time() }}" defer></script>
@endpush
@endsection
