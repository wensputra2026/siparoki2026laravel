@extends('layouts.app')

@section('title', 'Kontak & Sekretariat - ' . ($nama_paroki ?? 'Kristus Raja - Katedral / Bonipoi'))

@section('content')
<div class="py-12 bg-slate-100 dark:bg-[#090e1a]">
    <div class="max-w-5xl mx-auto px-4">
        <div class="section-title">
            <span class="st-badge"><i class="fas fa-envelope me-1"></i> Kontak &amp; Lokasi</span>
            <h2>Sekretariat <span class="text-gradient">{{ $nama_paroki ?? 'Paroki' }}</span></h2>
            <p>{{ $nama_keuskupan ?? 'Keuskupan Agung Kupang' }}</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white dark:bg-[#101d31] rounded-3xl p-8 border border-slate-200 dark:border-[#263a55] shadow-sm space-y-6">
                <h3 class="font-bold text-xl text-slate-900 dark:text-white">Informasi Kontak</h3>
                <div class="space-y-4 text-slate-600 dark:text-slate-300 text-sm">
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-church text-sky-600 mt-1"></i>
                        <div>
                            <p class="font-bold text-slate-900 dark:text-white">Paroki</p>
                            <p>{{ $nama_paroki }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-location-dot text-sky-600 mt-1"></i>
                        <div>
                            <p class="font-bold text-slate-900 dark:text-white">Alamat</p>
                            <p>{{ $alamat }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-phone text-sky-600 mt-1"></i>
                        <div>
                            <p class="font-bold text-slate-900 dark:text-white">Telepon</p>
                            <p>{{ $telepon }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-envelope text-sky-600 mt-1"></i>
                        <div>
                            <p class="font-bold text-slate-900 dark:text-white">Email</p>
                            <p>{{ $email }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-[#101d31] rounded-3xl p-4 border border-slate-200 dark:border-[#263a55] shadow-sm overflow-hidden min-h-[320px] flex items-center justify-center">
                @if(!empty($google_maps))
                    <div class="w-full h-full [&>iframe]:w-full [&>iframe]:h-[340px] [&>iframe]:rounded-2xl">
                        {!! $google_maps !!}
                    </div>
                @else
                    <div id="contact-map" class="w-full h-[340px] rounded-2xl"></div>
                    <script>
                        document.addEventListener("DOMContentLoaded", function() {
                            var map = L.map('contact-map', { center: [-10.1626, 123.5796], zoom: 16 });
                            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', { maxZoom: 19 }).addTo(map);
                            L.marker([-10.1626, 123.5796]).addTo(map).bindPopup('<strong>{{ $nama_paroki }}</strong><br>{{ $alamat }}').openPopup();
                        });
                    </script>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
