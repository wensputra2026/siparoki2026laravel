<div>
    <!-- Hero gradient -->
    <section class="relative overflow-hidden rounded-3xl p-8 md:p-10 text-white mb-8" data-aos="fade-up"
             style="background:linear-gradient(120deg,#2563eb,#4f46e5 55%,#06b6d4);">
        <div class="absolute -top-16 -right-10 w-64 h-64 rounded-full bg-white/10 blur-3xl"></div>
        <div class="relative">
            <span class="inline-block text-xs font-semibold uppercase tracking-wider bg-white/20 px-3 py-1 rounded-full mb-3">
                Panel Branded · MaryUI
            </span>
            <h2 class="text-2xl md:text-3xl font-extrabold">Selamat datang, {{ $user->nama_lengkap ?? 'Admin' }}</h2>
            <p class="mt-2 text-white/80 max-w-xl">
                Ringkasan data pastoral paroki dalam satu tampilan. CRUD mendalam tetap dikelola via Filament,
                halaman ini untuk presentasi & navigasi cepat.
            </p>
            <div class="mt-5 flex flex-wrap gap-3">
                <a href="/mary/umat" class="inline-flex items-center gap-2 bg-white text-blue-700 font-semibold px-5 py-2.5 rounded-full text-sm hover:bg-blue-50 transition">
                    <i class="fa-solid fa-users"></i> Kelola Data Umat
                </a>
                <a href="/admin" target="_blank" class="inline-flex items-center gap-2 bg-white/15 text-white font-semibold px-5 py-2.5 rounded-full text-sm hover:bg-white/25 transition">
                    <i class="fa-solid fa-layer-group"></i> Buka Panel Filament
                </a>
            </div>
        </div>
    </section>

    <!-- Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
        @foreach($stats as $s)
            <div data-aos="fade-up" data-aos-delay="{{ $loop->index * 80 }}">
                <x-mary-stat title="{{ $s['title'] }}" value="{{ number_format($s['value']) }}" icon="{{ $s['icon'] }}" description="{{ $s['desc'] }}" />
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent umat -->
        <div class="lg:col-span-2" data-aos="fade-up">
            <x-mary-card title="Umat Terbaru" subtitle="6 data umat yang baru ditambahkan">
                <div class="divide-y divide-base-content/10">
                    @forelse($latestUmat as $u)
                        <div class="flex items-center gap-3 py-3">
                            <div class="w-10 h-10 rounded-full bg-primary/15 text-primary flex items-center justify-center font-bold">
                                {{ strtoupper(substr($u->nama_lengkap, 0, 1)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold truncate">{{ $u->nama_lengkap }}</div>
                                <div class="text-xs text-base-content/50">
                                    {{ $u->kk?->no_kk_kw ? 'KK: '.$u->kk->no_kk_kw : 'Belum terikat KK' }}
                                    · {{ $u->jenis_kelamin ?? '-' }}
                                </div>
                            </div>
                            <span class="badge badge-soft {{ $u->status_umat == 'Aktif' ? 'badge-success' : 'badge-warning' }} badge-sm">
                                {{ $u->status_umat ?? 'Aktif' }}
                            </span>
                        </div>
                    @empty
                        <div class="py-6 text-center text-base-content/50">Belum ada data umat.</div>
                    @endforelse
                </div>
            </x-mary-card>
        </div>

        <!-- Quick links -->
        <div data-aos="fade-up">
            <x-mary-card title="Akses Cepat">
                <div class="space-y-2">
                    <a href="/mary/profil-paroki" class="btn btn-soft btn-block justify-start"><i class="fa-solid fa-church"></i> Profil Paroki</a>
                    <a href="/admin/sakramen" target="_blank" class="btn btn-soft btn-block justify-start"><i class="fa-solid fa-hand-holding-heart"></i> Pengajuan Sakramen</a>
                    <a href="/admin/jadwal-misa" target="_blank" class="btn btn-soft btn-block justify-start"><i class="fa-solid fa-calendar-days"></i> Jadwal Misa</a>
                    <a href="/admin/keuangan" target="_blank" class="btn btn-soft btn-block justify-start"><i class="fa-solid fa-coins"></i> Keuangan</a>
                    <a href="/admin/galeri" target="_blank" class="btn btn-soft btn-block justify-start"><i class="fa-solid fa-images"></i> Galeri</a>
                </div>
            </x-mary-card>
        </div>
    </div>
</div>
