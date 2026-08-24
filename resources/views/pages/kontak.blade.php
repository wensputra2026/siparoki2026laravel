@extends('layouts.app')

@section('title', 'Kontak & Sekretariat - ' . ($nama_paroki ?? $globalNamaParoki ?? 'SIPAROKI'))
@section('description', 'Hubungi sekretariat ' . ($nama_paroki ?? $globalNamaParoki ?? 'SIPAROKI') . ' untuk informasi pelayanan, administrasi, jadwal, dan lokasi paroki.')

@section('content')
@php
    $paroki = $activeParoki ?? null;
    $displayName = $nama_paroki ?? $paroki?->nama_paroki ?? $globalNamaParoki ?? 'SIPAROKI';
    $mapLat = $latitude ?? $paroki?->latitude ?? null;
    $mapLng = $longitude ?? $paroki?->longitude ?? null;
    $primaryAddress = $alamat ?? $paroki?->alamat ?? null;

    $identityRows = [
        ['icon' => 'fa-hashtag', 'label' => 'Kode Paroki', 'value' => $paroki?->kode_paroki],
        ['icon' => 'fa-shield-heart', 'label' => 'Pelindung Paroki', 'value' => $paroki?->pelindung_paroki],
        ['icon' => 'fa-circle-check', 'label' => 'Status', 'value' => trim(($paroki?->status_paroki ?? '') . ' ' . ($paroki?->status ? '(' . $paroki->status . ')' : ''))],
        ['icon' => 'fa-calendar-days', 'label' => 'Tanggal Berdiri', 'value' => !empty($paroki?->tanggal_berdiri) ? \Illuminate\Support\Carbon::parse($paroki->tanggal_berdiri)->translatedFormat('d F Y') : null],
    ];

    $pastoralRows = [
        ['icon' => 'fa-user-tie', 'label' => 'Pastor Paroki', 'value' => $pastor_paroki ?? $paroki?->nama_pastor_paroki_aktif ?? null],
        ['icon' => 'fa-user-group', 'label' => 'Pastor Rekan', 'value' => $pastor_rekan ?? $paroki?->nama_pastor_rekan ?? null],
    ];

    $locationRows = [
        ['icon' => 'fa-location-dot', 'label' => 'Alamat Lengkap', 'value' => $primaryAddress],
        ['icon' => 'fa-map-pin', 'label' => 'Desa / Kelurahan', 'value' => $paroki?->nama_desa],
        ['icon' => 'fa-map', 'label' => 'Kecamatan', 'value' => $paroki?->nama_kecamatan],
        ['icon' => 'fa-city', 'label' => 'Kabupaten / Kota', 'value' => $paroki?->nama_kabupaten],
        ['icon' => 'fa-earth-asia', 'label' => 'Provinsi', 'value' => $paroki?->nama_provinsi],
        ['icon' => 'fa-landmark', 'label' => 'Keuskupan', 'value' => $paroki?->nama_keuskupan ?? $nama_keuskupan ?? null],
        ['icon' => 'fa-layer-group', 'label' => 'Dekenat / Kevikepan', 'value' => $paroki?->nama_dekenat],
    ];

    $channels = [
        ['icon' => 'fa-phone', 'label' => 'Telepon', 'value' => $telepon ?? $paroki?->telepon ?? null, 'href' => !empty($telepon) ? 'tel:' . preg_replace('/\s+/', '', $telepon) : null],
        ['icon' => 'fa-brands fa-whatsapp', 'label' => 'WhatsApp', 'value' => $whatsapp ?? $paroki?->whatsapp ?? null, 'href' => !empty($whatsapp) ? 'https://wa.me/' . preg_replace('/\D+/', '', $whatsapp) : null],
        ['icon' => 'fa-envelope', 'label' => 'Email', 'value' => $email ?? $paroki?->email ?? null, 'href' => !empty($email) ? 'mailto:' . $email : null],
        ['icon' => 'fa-globe', 'label' => 'Website', 'value' => $website ?? $paroki?->website ?? null, 'href' => !empty($website) ? (str_starts_with($website, 'http') ? $website : 'https://' . $website) : null],
    ];

    $availableChannels = collect($channels)->filter(fn ($item) => filled($item['value']));
@endphp

<section class="page-banner page-hero" id="main-content">
    <div class="page-banner-shape page-banner-shape--1" aria-hidden="true"></div>
    <div class="page-banner-shape page-banner-shape--2" aria-hidden="true"></div>
    <div class="container page-banner-content">
        <span class="page-banner-badge"><i class="fas fa-envelope"></i> Kontak & Lokasi</span>
        <h1>Sekretariat {{ $displayName }}</h1>
        <p>Informasi resmi untuk pelayanan administrasi, pastoral, dan komunikasi umat.</p>
    </div>
</section>

<section class="contact-page section bg-slate-100 dark:bg-[#090e1a]">
    <div class="container">
        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800">
                <i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-800">
                <p class="font-bold mb-1"><i class="fa-solid fa-triangle-exclamation mr-2"></i>Periksa kembali isian Anda.</p>
                <ul class="list-disc pl-5 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="contact-layout">
            <div class="contact-main space-y-6">
                <div class="bg-white dark:bg-[#101d31] rounded-3xl border border-slate-200 dark:border-[#263a55] shadow-sm overflow-hidden">
                    <div class="p-6 sm:p-8">
                        <div class="flex flex-col sm:flex-row gap-5 sm:items-center">
                            <div class="w-20 h-20 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-center overflow-hidden shrink-0">
                                @if(!empty($globalLogo))
                                    <img src="{{ $globalLogo }}" alt="Logo {{ $displayName }}" class="w-full h-full object-contain p-2">
                                @else
                                    <i class="fa-solid fa-church text-3xl text-sky-600"></i>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-black uppercase tracking-wider text-sky-700">Sekretariat Paroki</p>
                                <h2 class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-white leading-tight mt-1">{{ $displayName }}</h2>
                                <p class="text-sm text-slate-500 dark:text-slate-300 mt-2">{{ filled($primaryAddress) ? $primaryAddress : 'Alamat sekretariat belum diisi.' }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-6">
                            @foreach ($identityRows as $row)
                                <div class="rounded-2xl bg-slate-50 dark:bg-[#0b1728] border border-slate-100 dark:border-[#263a55] p-4">
                                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                        <i class="fa-solid {{ $row['icon'] }} text-teal-700 w-4"></i>
                                        <span>{{ $row['label'] }}</span>
                                    </p>
                                    <p class="mt-1 text-sm font-black text-slate-900 dark:text-white">{{ filled($row['value']) ? $row['value'] : 'Belum diisi' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-[#101d31] rounded-3xl border border-slate-200 dark:border-[#263a55] shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 dark:border-[#263a55]">
                            <h3 class="font-black text-lg text-slate-900 dark:text-white">Pelayan Pastoral</h3>
                        </div>
                        <div class="divide-y divide-slate-100 dark:divide-[#263a55]">
                            @foreach ($pastoralRows as $row)
                                <div class="px-6 py-4">
                                    <p class="text-sm font-bold text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                        <i class="fa-solid {{ $row['icon'] }} text-teal-700 w-4"></i>
                                        <span>{{ $row['label'] }}</span>
                                    </p>
                                    <p class="mt-1 text-sm font-black text-slate-900 dark:text-white">{{ filled($row['value']) ? $row['value'] : 'Belum diisi' }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="bg-white dark:bg-[#101d31] rounded-3xl border border-slate-200 dark:border-[#263a55] shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 dark:border-[#263a55]">
                            <h3 class="font-black text-lg text-slate-900 dark:text-white">Aksi Cepat</h3>
                        </div>
                        <div class="p-6 space-y-3">
                            @forelse ($availableChannels as $channel)
                                <a href="{{ $channel['href'] ?? '#' }}" target="{{ !empty($channel['href']) && str_starts_with($channel['href'], 'http') ? '_blank' : '_self' }}" class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-700 hover:border-teal-300 hover:bg-teal-50 hover:text-teal-800 dark:border-[#263a55] dark:bg-[#0b1728] dark:text-slate-200">
                                    <i class="{{ str_starts_with($channel['icon'], 'fa-brands') ? $channel['icon'] : 'fa-solid ' . $channel['icon'] }} text-teal-700 w-5"></i>
                                    <span>{{ $channel['label'] }}</span>
                                    <span class="ml-auto text-xs text-slate-400 truncate max-w-[160px]">{{ $channel['value'] }}</span>
                                </a>
                            @empty
                                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                                    Kanal komunikasi belum diisi di profil paroki.
                                </div>
                            @endforelse

                            @if (!empty($maps_url))
                                <a href="{{ $maps_url }}" target="_blank" rel="noopener" class="flex items-center justify-center gap-2 rounded-2xl bg-teal-600 px-4 py-3 text-sm font-black text-white hover:bg-teal-700">
                                    <i class="fa-solid fa-map-location-dot"></i>
                                    <span>Buka Google Maps</span>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-[#101d31] rounded-3xl border border-slate-200 dark:border-[#263a55] shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 dark:border-[#263a55]">
                        <h3 class="font-black text-lg text-slate-900 dark:text-white">Lokasi & Wilayah Administratif</h3>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2">
                        @foreach ($locationRows as $row)
                            <div class="border-b sm:odd:border-r border-slate-100 dark:border-[#263a55] px-6 py-4">
                                <p class="text-sm font-bold text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                    <i class="fa-solid {{ $row['icon'] }} text-teal-700 w-4"></i>
                                    <span>{{ $row['label'] }}</span>
                                </p>
                                <p class="mt-1 text-sm font-black text-slate-900 dark:text-white">{{ filled($row['value']) ? $row['value'] : 'Belum diisi' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <aside class="contact-side space-y-6">
                <div class="bg-white dark:bg-[#101d31] rounded-3xl p-6 sm:p-7 border border-slate-200 dark:border-[#263a55] shadow-sm">
                    <div class="mb-5">
                        <p class="text-xs font-black uppercase tracking-wider text-teal-700">Pesan Pengunjung</p>
                        <h3 class="font-black text-2xl text-slate-900 dark:text-white mt-1">Kirim Pesan</h3>
                        <p class="text-sm text-slate-500 dark:text-slate-300 mt-2">Gunakan formulir ini untuk pertanyaan administrasi, pelayanan, jadwal, atau informasi paroki.</p>
                    </div>

                    <form action="{{ route('kontak.kirim') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="hidden" aria-hidden="true">
                            <label for="website_url">Website</label>
                            <input id="website_url" name="website_url" type="text" tabindex="-1" autocomplete="off">
                        </div>

                        <div>
                            <label for="nama" class="block text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">Nama Lengkap <span class="text-rose-500">*</span></label>
                            <input id="nama" name="nama" type="text" value="{{ old('nama') }}" required maxlength="150" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none focus:border-teal-500 focus:bg-white dark:border-[#263a55] dark:bg-[#0b1728] dark:text-white" placeholder="Nama Anda">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="telepon" class="block text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">WhatsApp / Telepon</label>
                                <input id="telepon" name="telepon" type="text" value="{{ old('telepon') }}" maxlength="30" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none focus:border-teal-500 focus:bg-white dark:border-[#263a55] dark:bg-[#0b1728] dark:text-white" placeholder="081234567890">
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">Email</label>
                                <input id="email" name="email" type="email" value="{{ old('email') }}" maxlength="150" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none focus:border-teal-500 focus:bg-white dark:border-[#263a55] dark:bg-[#0b1728] dark:text-white" placeholder="nama@email.com">
                            </div>
                        </div>

                        <div>
                            <label for="subjek" class="block text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">Subjek</label>
                            <input id="subjek" name="subjek" type="text" value="{{ old('subjek') }}" maxlength="180" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none focus:border-teal-500 focus:bg-white dark:border-[#263a55] dark:bg-[#0b1728] dark:text-white" placeholder="Keperluan pesan">
                        </div>

                        <div>
                            <label for="pesan" class="block text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">Isi Pesan <span class="text-rose-500">*</span></label>
                            <textarea id="pesan" name="pesan" required minlength="10" maxlength="3000" rows="5" class="w-full rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-900 outline-none focus:border-teal-500 focus:bg-white dark:border-[#263a55] dark:bg-[#0b1728] dark:text-white" placeholder="Tuliskan pesan Anda...">{{ old('pesan') }}</textarea>
                            <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Isi email atau nomor WhatsApp agar sekretariat dapat membalas.</p>
                        </div>

                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 rounded-2xl bg-teal-600 px-6 py-3 text-sm font-black text-white shadow-sm hover:bg-teal-700">
                            <i class="fa-solid fa-paper-plane"></i>
                            <span>Kirim Pesan</span>
                        </button>
                    </form>
                </div>

                <div class="contact-map-card bg-white dark:bg-[#101d31] rounded-3xl p-4 border border-slate-200 dark:border-[#263a55] shadow-sm overflow-hidden">
                    <div id="contact-map" class="online-map w-full h-[360px] rounded-2xl" data-title="{{ e($displayName) }}" data-address="{{ e($primaryAddress) }}" data-lat="{{ e($mapLat) }}" data-lng="{{ e($mapLng) }}"></div>
                    @if (!empty($mapLat) && !empty($mapLng))
                        <div class="contact-map-meta mt-4 rounded-2xl bg-slate-50 dark:bg-[#0b1728] px-4 py-3 text-sm font-bold text-slate-700 dark:text-slate-200">
                            <i class="fa-solid fa-location-crosshairs text-teal-700"></i>
                            <span>{{ $mapLat }}, {{ $mapLng }}</span>
                        </div>
                    @endif
                </div>

            </aside>
        </div>
    </div>
</section>

@push('scripts')
    <script src="/js/pages/kontak.js?v={{ @filemtime(public_path('js/pages/kontak.js')) ?: time() }}" defer></script>
@endpush
@endsection
