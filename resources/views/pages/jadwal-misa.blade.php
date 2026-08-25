@extends('layouts.app')

@section('title', 'Jadwal Misa - SIPAROKI')

@push('styles')
    <link rel="stylesheet" href="/css/pages/jadwal-misa.css">
@endpush

@section('content')
@php
    use Carbon\Carbon;
    use Illuminate\Support\Str;

    $bulanAktif = max(1, min(12, (int) ($bulanFilter ?? now()->month)));
    $tahunAktif = (int) ($tahunFilter ?? now()->year);
    $displayDate = Carbon::create($tahunAktif, $bulanAktif, 1);
    $prevDate = $displayDate->copy()->subMonth();
    $nextDate = $displayDate->copy()->addMonth();
    $todayStr = now()->toDateString();
    $namaBulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $namaHari = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];

    $groupedJadwal = $jadwalMisa
        ->filter(fn ($item) => !empty($item->tanggal))
        ->sortBy([
            ['tanggal', 'asc'],
            ['jam_perayaan', 'asc'],
        ])
        ->groupBy(fn ($item) => Carbon::parse($item->tanggal)->toDateString());

    $datesWithJadwal = $groupedJadwal->keys()->all();
    $firstDayOffset = (int) $displayDate->copy()->startOfMonth()->dayOfWeek;
    $daysInMonth = $displayDate->daysInMonth;

    $formatTime = fn ($misa) => $misa->waktu ?? $misa->jam_perayaan ?? '-';
    $formatJenis = fn ($misa) => $misa->jenis_misa ?? $misa->jenis_perayaan ?? null;
    $formatLokasi = fn ($misa) => $misa->tempat ?? $misa->lokasi ?? null;
@endphp


<div class="jm-page">
    <!-- Page Header / Breadcrumb Konoha Style -->
    <section class="page-header">
        <div class="container">
            <h1 style="font-size: 2.6rem; font-weight: 700; margin-bottom: 15px; color: #ffffff;">Jadwal Perayaan Ekaristi</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb" style="display: inline-flex; list-style: none; padding: 0; margin: 0 auto; gap: 12px; background: transparent; justify-content: center; align-items: center;">
                    <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                        <a href="/" style="color: white; text-decoration: none; font-weight: 500;">Beranda</a>
                    </li>
                    <li class="breadcrumb-item active" style="background: var(--primary-orange, #ff9800); color: white; padding: 6px 18px; border-radius: 25px; font-size: 0.85rem; font-weight: 600;">
                        Jadwal Misa
                    </li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="w-full px-4 sm:px-6 lg:px-10 py-10">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            <div class="lg:col-span-2 space-y-6">
                <div class="jm-card p-5">
                    <div class="flex items-center justify-between gap-3 mb-5">
                        <a href="{{ route('jadwal-misa', ['bulan' => $prevDate->month, 'tahun' => $prevDate->year]) }}"
                           class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                            <i class="fa-solid fa-chevron-left"></i> Seb
                        </a>
                        <h2 class="text-lg font-black text-slate-800 text-center">
                            {{ $namaBulan[$bulanAktif - 1] }} {{ $tahunAktif }}
                        </h2>
                        <a href="{{ route('jadwal-misa', ['bulan' => $nextDate->month, 'tahun' => $nextDate->year]) }}"
                           class="inline-flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                            Ses <i class="fa-solid fa-chevron-right"></i>
                        </a>
                    </div>

                    <div class="grid grid-cols-7 gap-2 mb-3 text-center text-xs font-black uppercase tracking-wide text-slate-500">
                        <div>M</div><div>S</div><div>S</div><div>R</div><div>K</div><div>J</div><div>S</div>
                    </div>

                    <div class="grid grid-cols-7 gap-2">
                        @for($i = 0; $i < $firstDayOffset; $i++)
                            <div class="jm-calendar-day opacity-0"></div>
                        @endfor

                        @for($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $dateStr = $displayDate->copy()->day($day)->toDateString();
                                $hasJadwal = in_array($dateStr, $datesWithJadwal, true);
                                $isToday = $dateStr === $todayStr;
                            @endphp
                            <button type="button"
                                    class="jm-calendar-day {{ $hasJadwal ? 'has-jadwal' : '' }} {{ $isToday ? 'today' : '' }}"
                                    data-date="{{ $dateStr }}"
                                    @if($hasJadwal) onclick="scrollToDate('{{ $dateStr }}')" @endif
                                    {{ $hasJadwal ? '' : 'disabled' }}>
                                {{ $day }}
                            </button>
                        @endfor
                    </div>

                    <div class="mt-4 text-center text-xs text-slate-500">
                        <i class="fa-solid fa-circle-info"></i> Klik tanggal yang memiliki titik untuk menampilkan jadwal pada tanggal itu.
                    </div>
                </div>

                <div id="filterIndicator" class="hidden rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-3 text-blue-900">
                            <i class="fa-solid fa-filter text-xl text-blue-600"></i>
                            <span class="text-sm font-semibold">Menampilkan jadwal untuk <strong id="filterDateDisplay"></strong></span>
                        </div>
                        <button type="button" onclick="showAllDates()" class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700">
                            <i class="fa-solid fa-circle-xmark"></i> Tampilkan Semua
                        </button>
                    </div>
                </div>

                <div class="space-y-8" id="jadwalContainer">
                    @forelse($groupedJadwal as $dateKey => $misas)
                        @php
                            $dt = Carbon::parse($dateKey);
                            $isToday = $dateKey === $todayStr;
                            $tanggalLabel = $namaHari[$dt->dayOfWeek] . ', ' . $dt->format('d') . ' ' . $namaBulan[$dt->month - 1] . ' ' . $dt->year;
                        @endphp

                        <article class="jm-card schedule-card {{ $isToday ? 'today-card' : '' }}" id="jadwal-{{ $dateKey }}">
                            <div class="jm-date-header">
                                <div class="flex items-center justify-between gap-3">
                                    <h2 class="text-xl font-black">{{ $tanggalLabel }}</h2>
                                    <span class="rounded-full bg-white px-3 py-1 text-sm font-black text-slate-900 shadow-sm">
                                        {{ $misas->count() }} Jadwal
                                    </span>
                                </div>
                            </div>

                            <div class="p-5">
                                <div class="space-y-4">
                                    @foreach($misas as $misa)
                                        @php
                                            $jenis = $formatJenis($misa);
                                            $lokasi = $formatLokasi($misa);
                                        @endphp
                                        <div class="jm-item">
                                            <div class="flex flex-col gap-4">
                                                <div class="flex flex-wrap items-center gap-3">
                                                    <div class="inline-flex items-center gap-2 text-blue-700">
                                                        <i class="fa-solid fa-clock text-xl"></i>
                                                        <span class="text-lg font-black">{{ $formatTime($misa) }}</span>
                                                    </div>

                                                    @if(!empty($misa->misa_ke))
                                                        <span class="jm-badge bg-blue-600 text-white">Misa Ke-{{ $misa->misa_ke }}</span>
                                                    @endif

                                                    @if($jenis)
                                                        <span class="jm-badge bg-emerald-600 text-white">
                                                            <i class="fa-solid fa-church"></i> {{ $jenis }}
                                                        </span>
                                                    @endif
                                                </div>

                                                @if($lokasi)
                                                    <div class="flex items-start gap-3 text-sm text-slate-700">
                                                        <i class="fa-solid fa-location-dot text-sky-600 mt-0.5"></i>
                                                        <span>{{ $lokasi }}</span>
                                                    </div>
                                                @endif

                                                @if(!empty($misa->pelayan))
                                                    <div class="flex items-start gap-3 text-sm text-slate-700">
                                                        <i class="fa-solid fa-user-tie text-amber-600 mt-0.5"></i>
                                                        <span><strong>Pemimpin/Pelayan:</strong> {!! nl2br(e($misa->pelayan)) !!}</span>
                                                    </div>
                                                @endif

                                                @if(!empty($misa->koor) || !empty($misa->organis) || !empty($misa->lektor) || !empty($misa->pemazmur) || !empty($misa->pembersih_gereja) || !empty($misa->hias_gereja))
                                                    <div class="jm-liturgi">
                                                        <div class="mb-2 flex items-center gap-2 text-xs font-black uppercase tracking-wide text-slate-600">
                                                            <i class="fa-solid fa-people-group"></i> Tim Liturgi
                                                        </div>
                                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-slate-600">
                                                            @if(!empty($misa->koor))<div><i class="fa-solid fa-music mr-2 text-slate-400"></i><strong>Koor:</strong> {{ $misa->koor }}</div>@endif
                                                            @if(!empty($misa->organis))<div><i class="fa-solid fa-compact-disc mr-2 text-slate-400"></i><strong>Organis:</strong> {{ $misa->organis }}</div>@endif
                                                            @if(!empty($misa->lektor))<div><i class="fa-solid fa-book-bible mr-2 text-slate-400"></i><strong>Lektor:</strong> {!! nl2br(e($misa->lektor)) !!}</div>@endif
                                                            @if(!empty($misa->pemazmur))<div><i class="fa-solid fa-microphone-lines mr-2 text-slate-400"></i><strong>Pemazmur:</strong> {{ $misa->pemazmur }}</div>@endif
                                                            @if(!empty($misa->pembersih_gereja))<div><i class="fa-solid fa-broom mr-2 text-slate-400"></i><strong>Pembersih:</strong> {{ $misa->pembersih_gereja }}</div>@endif
                                                            @if(!empty($misa->hias_gereja))<div><i class="fa-solid fa-palette mr-2 text-slate-400"></i><strong>Hias Gereja:</strong> {{ $misa->hias_gereja }}</div>@endif
                                                        </div>
                                                    </div>
                                                @endif

                                                @if(!empty($misa->intensi))
                                                    <div class="rounded-lg bg-rose-50 border border-rose-100 px-3 py-2 text-sm text-rose-800">
                                                        <i class="fa-solid fa-heart mr-2"></i><strong>Intensi:</strong> {{ Str::limit($misa->intensi, 180) }}
                                                    </div>
                                                @endif

                                                @if(!empty($misa->catatan))
                                                    <div class="rounded-lg bg-slate-50 border border-slate-200 px-3 py-2 text-sm text-slate-600">
                                                        <i class="fa-solid fa-circle-info mr-2 text-blue-600"></i>{{ Str::limit($misa->catatan, 180) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="jm-card p-12 text-center">
                            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-3xl text-slate-300">
                                <i class="fa-regular fa-calendar-xmark"></i>
                            </div>
                            <p class="text-lg font-bold text-slate-600">Belum ada jadwal misa yang tersedia.</p>
                            <p class="mt-1 text-sm text-slate-400">Coba pilih bulan lain melalui kalender di atas.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <aside class="space-y-4">
                <div class="jm-card p-5">
                    <h3 class="mb-3 flex items-center gap-2 text-lg font-black text-slate-800">
                        <i class="fa-solid fa-calendar-check text-blue-600"></i> Ringkasan
                    </h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div class="rounded-xl bg-blue-50 p-4 text-center">
                            <div class="text-2xl font-black text-blue-700">{{ $groupedJadwal->count() }}</div>
                            <div class="text-xs font-bold text-blue-900/70">Hari</div>
                        </div>
                        <div class="rounded-xl bg-emerald-50 p-4 text-center">
                            <div class="text-2xl font-black text-emerald-700">{{ $jadwalMisa->count() }}</div>
                            <div class="text-xs font-bold text-emerald-900/70">Jadwal</div>
                        </div>
                    </div>
                </div>

                <div class="jm-card p-5">
                    <h3 class="mb-3 flex items-center gap-2 text-lg font-black text-slate-800">
                        <i class="fa-solid fa-lightbulb text-amber-500"></i> Panduan
                    </h3>
                    <ul class="space-y-3 text-sm text-slate-600">
                        <li class="flex gap-2"><i class="fa-solid fa-circle-dot text-blue-600 mt-1"></i><span>Titik biru pada kalender berarti tanggal itu memiliki jadwal misa.</span></li>
                        <li class="flex gap-2"><i class="fa-solid fa-circle-dot text-blue-600 mt-1"></i><span>Gunakan tombol Seb/Ses untuk pindah bulan.</span></li>
                        <li class="flex gap-2"><i class="fa-solid fa-circle-dot text-blue-600 mt-1"></i><span>Klik tanggal untuk fokus pada jadwal tanggal tersebut.</span></li>
                    </ul>
                </div>
            </aside>
        </div>
    </section>
</div>

@push('scripts')
    <script src="/js/pages/jadwal-misa.js" defer></script>
@endpush
@endsection



