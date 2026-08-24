<div>
    @php
        $p = $paroki;
        $nama = $p->nama_paroki ?? 'Kristus Raja - Katedral / Bonipoi';
        $alamat = $p->alamat ?? 'Fontein, Kec. Kota Raja, Kota Kupang, NTT';
    @endphp

    <section class="relative overflow-hidden rounded-3xl p-8 md:p-10 text-white mb-8" data-aos="fade-up"
             style="background:linear-gradient(120deg,#2563eb,#4f46e5 55%,#06b6d4);">
        <div class="absolute -bottom-20 -left-10 w-72 h-72 rounded-full bg-white/10 blur-3xl"></div>
        <div class="relative flex items-center gap-5">
            <div class="w-20 h-20 rounded-2xl bg-white/20 flex items-center justify-center text-3xl">
                <i class="fa-solid fa-church"></i>
            </div>
            <div>
                <span class="text-xs font-semibold uppercase tracking-wider bg-white/20 px-3 py-1 rounded-full">Profil Paroki</span>
                <h2 class="text-2xl md:text-3xl font-extrabold mt-2">{{ $nama }}</h2>
                <p class="text-white/80 mt-1"><i class="fa-solid fa-location-dot mr-1"></i> {{ $alamat }}</p>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <x-mary-card title="Tentang Paroki" subtitle="Ringkasan profil & identitas">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div class="flex gap-3"><i class="fa-solid fa-signature text-primary mt-1"></i><div><div class="font-semibold">Nama Paroki</div><div class="text-base-content/70">{{ $nama }}</div></div></div>
                    <div class="flex gap-3"><i class="fa-solid fa-location-dot text-primary mt-1"></i><div><div class="font-semibold">Alamat</div><div class="text-base-content/70">{{ $alamat }}</div></div></div>
                    <div class="flex gap-3"><i class="fa-solid fa-user-tie text-primary mt-1"></i><div><div class="font-semibold">Pastor Paroki</div><div class="text-base-content/70">{{ $p->pastor_paroki ?? '—' }}</div></div></div>
                    <div class="flex gap-3"><i class="fa-solid fa-phone text-primary mt-1"></i><div><div class="font-semibold">Kontak</div><div class="text-base-content/70">{{ $p->telepon ?? $p->kontak ?? '—' }}</div></div></div>
                </div>
                @if($p && $p->sejarah)
                    <div class="mt-5 pt-5 border-t border-base-content/10">
                        <div class="font-semibold mb-1">Sejarah</div>
                        <p class="text-base-content/70 leading-relaxed text-sm">{{ $p->sejarah }}</p>
                    </div>
                @endif
            </x-mary-card>
        </div>

        <div class="space-y-6">
            <x-mary-card title="Link Cepat">
                <div class="space-y-2">
                    <a href="/mary/umat" class="btn btn-soft btn-block justify-start"><i class="fa-solid fa-users"></i> Data Umat</a>
                    <a href="/mary/dashboard" class="btn btn-soft btn-block justify-start"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
                    <a href="/admin" target="_blank" class="btn btn-soft btn-block justify-start"><i class="fa-solid fa-layer-group"></i> Panel Filament</a>
                </div>
            </x-mary-card>
            <x-mary-card title="Status">
                <x-mary-badge value="Aktif" class="badge-success" />
                <p class="text-xs text-base-content/60 mt-2">Halaman profil ini dikelola via MaryUI (branded), terpisah dari CRUD Filament.</p>
            </x-mary-card>
        </div>
    </div>
</div>
