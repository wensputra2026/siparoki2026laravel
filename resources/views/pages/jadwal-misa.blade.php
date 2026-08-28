@extends('layouts.app')
@section('title', 'Jadwal Perayaan Misa & Ekaristi - ' . ($globalNamaParoki ?? 'St. Vinsensius a Paulo Benlutu'))

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

    $namaBulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    $namaHari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    // Group schedules by date
    $groupedJadwal = $jadwalMisa
        ->filter(fn ($item) => !empty($item->tanggal))
        ->sortBy([
            ['tanggal', 'asc'],
            ['waktu', 'asc'],
            ['jam_perayaan', 'asc'],
        ])
        ->groupBy(fn ($item) => Carbon::parse($item->tanggal)->toDateString());

    $datesWithJadwal = $groupedJadwal->keys()->all();
    $firstDayOffset = (int) $displayDate->copy()->startOfMonth()->dayOfWeek;
    $daysInMonth = $displayDate->daysInMonth;
@endphp

<!-- Page Header / Breadcrumb Konoha Style -->
<section class="page-header">
    <div class="container">
        <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 12px; color: #ffffff;">Jadwal Perayaan Misa</h1>
        <p style="font-size: 1rem; color: rgba(255,255,255,0.85); margin-bottom: 18px; max-width: 650px; margin-left: auto; margin-right: auto;">
            Kalender dan jadwal perayaan Ekaristi harian, mingguan, serta misa perayaan hari raya di Paroki dan Kapela / Stasi.
        </p>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="display: inline-flex; list-style: none; padding: 0; margin: 0 auto; gap: 10px; background: transparent; justify-content: center; align-items: center; flex-wrap: wrap;">
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.2); padding: 5px 16px; border-radius: 25px; font-size: 0.82rem;">
                    <a href="/" style="color: white; text-decoration: none; font-weight: 500;">Beranda</a>
                </li>
                <li class="breadcrumb-item active" style="background: var(--primary-orange, #ff9800); color: white; padding: 5px 16px; border-radius: 25px; font-size: 0.82rem; font-weight: 700;">
                    Jadwal Misa
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- Content Section Konoha Style -->
<section class="content-section" style="padding: 50px 0 80px; background: #f4faf9;">
    <div class="container">
        
        <!-- Kalender Container Card -->
        <div style="background: #ffffff; border-radius: 20px; padding: 30px; box-shadow: 0 5px 25px rgba(0,0,0,0.06); border: 1px solid #e2e8f0; margin-bottom: 40px;">
            
            <!-- Month Navigation & Controls Bar -->
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 25px; padding-bottom: 20px; border-bottom: 2px solid #f1f5f9;">
                
                <div style="display: flex; align-items: center; gap: 10px;">
                    <a href="{{ route('jadwal-misa', ['bulan' => $prevDate->month, 'tahun' => $prevDate->year]) }}" 
                       style="display: inline-flex; align-items: center; gap: 6px; background: #f8fafc; border: 1px solid #cbd5e1; color: #334155; padding: 8px 16px; border-radius: 12px; font-size: 0.85rem; font-weight: 700; text-decoration: none; transition: 0.2s;">
                        <i class="fa-solid fa-chevron-left"></i> {{ $namaBulan[$prevDate->month] }}
                    </a>
                    
                    <a href="{{ route('jadwal-misa', ['bulan' => $nextDate->month, 'tahun' => $nextDate->year]) }}" 
                       style="display: inline-flex; align-items: center; gap: 6px; background: #f8fafc; border: 1px solid #cbd5e1; color: #334155; padding: 8px 16px; border-radius: 12px; font-size: 0.85rem; font-weight: 700; text-decoration: none; transition: 0.2s;">
                        {{ $namaBulan[$nextDate->month] }} <i class="fa-solid fa-chevron-right"></i>
                    </a>
                </div>

                <!-- Current Month / Year Title -->
                <div style="text-align: center;">
                    <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-orange, #ff9800); display: block;">
                        Kalender Liturgi Misa
                    </span>
                    <h2 style="font-size: 1.6rem; font-weight: 800; color: #0f172a; margin: 0;">
                        {{ $namaBulan[$bulanAktif] }} {{ $tahunAktif }}
                    </h2>
                </div>

                <!-- Month & Year Jump Selector -->
                <form method="GET" action="{{ route('jadwal-misa') }}" style="display: flex; align-items: center; gap: 8px;">
                    <select name="bulan" onchange="this.form.submit()" style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 10px; padding: 8px 12px; font-size: 0.82rem; font-weight: 600; color: #334155; outline: none;">
                        @foreach($namaBulan as $num => $bName)
                            <option value="{{ $num }}" {{ $bulanAktif == $num ? 'selected' : '' }}>{{ $bName }}</option>
                        @endforeach
                    </select>

                    <select name="tahun" onchange="this.form.submit()" style="background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 10px; padding: 8px 12px; font-size: 0.82rem; font-weight: 600; color: #334155; outline: none;">
                        @for($y = now()->year - 2; $y <= now()->year + 2; $y++)
                            <option value="{{ $y }}" {{ $tahunAktif == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </form>

            </div>

            <!-- Calendar Table Grid -->
            <div style="overflow-x: auto;">
                <div style="min-width: 680px;">
                    
                    <!-- Days of Week Header -->
                    <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px; margin-bottom: 8px; text-align: center;">
                        @foreach($namaHari as $idx => $dayName)
                            <div style="padding: 10px 4px; font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; border-radius: 8px; background: {{ $idx === 0 ? '#fee2e2' : '#f1f5f9' }}; color: {{ $idx === 0 ? '#b91c1c' : '#475569' }};">
                                {{ $dayName }}
                            </div>
                        @endforeach
                    </div>

                    <!-- Day Cells Grid -->
                    <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 8px;">
                        
                        <!-- Empty Offset Days -->
                        @for($i = 0; $i < $firstDayOffset; $i++)
                            <div style="min-height: 95px; background: #fafafa; border-radius: 12px; border: 1px dashed #e2e8f0; opacity: 0.4;"></div>
                        @endfor

                        <!-- Days of Active Month -->
                        @for($day = 1; $day <= $daysInMonth; $day++)
                            @php
                                $dateObj = $displayDate->copy()->day($day);
                                $dateStr = $dateObj->toDateString();
                                $hasJadwal = isset($groupedJadwal[$dateStr]);
                                $dayMisas = $hasJadwal ? $groupedJadwal[$dateStr] : collect();
                                $isToday = $dateStr === $todayStr;
                                $dayOfWeek = $dateObj->dayOfWeek;
                            @endphp

                            <div 
                                @if($hasJadwal) onclick="document.getElementById('jadwal-{{ $dateStr }}')?.scrollIntoView({behavior: 'smooth', block: 'center'})" @endif
                                style="min-height: 95px; background: {{ $isToday ? '#ecfdf5' : ($hasJadwal ? '#ffffff' : '#f8fafc') }}; border: {{ $isToday ? '2px solid #10b981' : ($hasJadwal ? '1.5px solid var(--primary-teal, #00897b)' : '1px solid #e2e8f0') }}; border-radius: 12px; padding: 8px 10px; display: flex; flex-direction: column; justify-content: space-between; position: relative; transition: all 0.2s; {{ $hasJadwal ? 'cursor: pointer; box-shadow: 0 2px 8px rgba(0,137,123,0.12);' : '' }}"
                                onmouseover="if({{ $hasJadwal ? 'true' : 'false' }}) { this.style.transform='translateY(-2px)'; this.style.borderColor='var(--primary-orange, #ff9800)'; }"
                                onmouseout="if({{ $hasJadwal ? 'true' : 'false' }}) { this.style.transform='none'; this.style.borderColor='var(--primary-teal, #00897b)'; }"
                            >
                                <!-- Date Number and Badge -->
                                <div style="display: flex; align-items: center; justify-content: space-between;">
                                    <span style="font-size: 0.95rem; font-weight: 800; color: {{ $dayOfWeek === 0 ? '#dc2626' : ($isToday ? '#059669' : '#1e293b') }};">
                                        {{ $day }}
                                    </span>
                                    @if($isToday)
                                        <span style="font-size: 0.65rem; font-weight: 700; background: #10b981; color: #fff; padding: 1px 6px; border-radius: 6px;">Hari Ini</span>
                                    @elseif($hasJadwal)
                                        <span style="font-size: 0.65rem; font-weight: 700; background: rgba(0,137,123,0.12); color: var(--primary-teal, #00897b); padding: 1px 6px; border-radius: 6px;">
                                            {{ $dayMisas->count() }} Misa
                                        </span>
                                    @endif
                                </div>

                                <!-- Schedule Mass Pills inside Calendar Cell -->
                                <div style="margin-top: 4px; display: flex; flex-direction: column; gap: 3px;">
                                    @foreach($dayMisas->take(2) as $m)
                                        @php
                                            $timeStr = $m->waktu ? substr($m->waktu, 0, 5) : ($m->jam_perayaan ?? '');
                                            $titleStr = $m->jenis_perayaan ?? $m->jenis_misa ?? 'Misa';
                                        @endphp
                                        <div style="background: rgba(0,137,123,0.08); border-left: 2.5px solid var(--primary-teal, #00897b); padding: 2px 5px; border-radius: 4px; font-size: 0.7rem; font-weight: 700; color: #00695c; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <i class="fa-solid fa-clock" style="font-size: 8px;"></i> {{ $timeStr }} {{ Str::limit($titleStr, 12) }}
                                        </div>
                                    @endforeach

                                    @if($dayMisas->count() > 2)
                                        <span style="font-size: 0.65rem; font-weight: 700; color: var(--primary-orange, #ff9800); text-align: center;">
                                            +{{ $dayMisas->count() - 2 }} lainnya
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @endfor

                    </div>
                </div>
            </div>

            <!-- Calendar Footer Guide -->
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-top: 20px; padding-top: 15px; border-top: 1px solid #f1f5f9; font-size: 0.8rem; color: #64748b;">
                <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                    <span style="display: inline-flex; align-items: center; gap: 6px;">
                        <span style="width: 12px; height: 12px; border-radius: 3px; background: #ecfdf5; border: 2px solid #10b981;"></span> Hari Ini
                    </span>
                    <span style="display: inline-flex; align-items: center; gap: 6px;">
                        <span style="width: 12px; height: 12px; border-radius: 3px; background: #ffffff; border: 2px solid var(--primary-teal, #00897b);"></span> Ada Jadwal Misa
                    </span>
                </div>
                <div style="font-weight: 600; color: var(--primary-teal, #00897b);">
                    <i class="fa-solid fa-circle-info me-1"></i> Klik tanggal pada kalender untuk melihat rincian jadwal misa
                </div>
            </div>

        </div>


        <!-- Detailed Schedules List Section -->
        <div style="margin-top: 20px;">
            
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 15px; margin-bottom: 25px;">
                <div>
                    <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-orange, #ff9800);">
                        Daftar Perayaan Ekaristi
                    </span>
                    <h3 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin: 2px 0 0;">
                        Rincian Jadwal Misa {{ $namaBulan[$bulanAktif] }} {{ $tahunAktif }}
                    </h3>
                </div>
                <span style="display: inline-flex; align-items: center; gap: 6px; background: #ffffff; border: 1px solid #cbd5e1; color: #334155; font-size: 0.82rem; font-weight: 700; padding: 6px 16px; border-radius: 20px;">
                    <i class="fa-solid fa-church text-amber-600"></i> Total: {{ $jadwalMisa->count() }} Jadwal Misa
                </span>
            </div>

            @if($groupedJadwal->isNotEmpty())
                <div style="display: flex; flex-direction: column; gap: 24px;">
                    @foreach($groupedJadwal as $dateKey => $misas)
                        @php
                            $dt = Carbon::parse($dateKey);
                            $isToday = $dateKey === $todayStr;
                            $dayName = $namaHari[$dt->dayOfWeek];
                            $formattedDate = $dayName . ', ' . $dt->format('d') . ' ' . $namaBulan[$dt->month] . ' ' . $dt->year;
                        @endphp

                        <div id="jadwal-{{ $dateKey }}" style="background: #ffffff; border-radius: 18px; border: 1px solid #e2e8f0; border-left: 5px solid {{ $isToday ? '#10b981' : 'var(--primary-teal, #00897b)' }}; box-shadow: 0 4px 18px rgba(0,0,0,0.04); overflow: hidden; transition: transform 0.2s;">
                            
                            <!-- Date Header Bar -->
                            <div style="background: {{ $isToday ? '#f0fdf4' : '#f8fafc' }}; padding: 14px 24px; border-bottom: 1px solid #e2e8f0; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 36px; height: 36px; border-radius: 10px; background: {{ $isToday ? '#10b981' : 'var(--primary-teal, #00897b)' }}; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 0.95rem; font-weight: 800;">
                                        {{ $dt->format('d') }}
                                    </div>
                                    <div>
                                        <h4 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0;">
                                            {{ $formattedDate }}
                                        </h4>
                                        <span style="font-size: 0.75rem; color: #64748b; font-weight: 600;">
                                            {{ $misas->count() }} Perayaan Misa Terjadwal
                                        </span>
                                    </div>
                                </div>
                                @if($isToday)
                                    <span style="background: #10b981; color: #ffffff; font-size: 0.72rem; font-weight: 700; padding: 4px 12px; border-radius: 12px;">
                                        Perayaan Hari Ini
                                    </span>
                                @endif
                            </div>

                            <!-- Mass Cards List -->
                            <div style="padding: 20px 24px; display: flex; flex-direction: column; gap: 16px;">
                                @foreach($misas as $misa)
                                    @php
                                        $time = $misa->waktu ? substr($misa->waktu, 0, 5) . ' WITA' : ($misa->jam_perayaan ?? 'Waktu Belum Ditentukan');
                                        $title = $misa->jenis_perayaan ?? $misa->jenis_misa ?? 'Perayaan Ekaristi';
                                        $location = $misa->tempat ?? $misa->lokasi ?? 'Gereja Paroki St. Vinsensius a Paulo Benlutu';
                                    @endphp

                                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 18px 20px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 16px;">
                                        
                                        <!-- Time & Mass Name -->
                                        <div style="display: flex; align-items: flex-start; gap: 14px; flex: 1; min-width: 260px;">
                                            <div style="background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); padding: 10px 14px; border-radius: 12px; text-align: center; shrink-0;">
                                                <i class="fa-solid fa-clock" style="font-size: 1rem; display: block; margin-bottom: 2px;"></i>
                                                <span style="font-size: 0.85rem; font-weight: 800; white-space: nowrap;">{{ $time }}</span>
                                            </div>
                                            <div>
                                                <span style="display: inline-block; background: rgba(255,152,0,0.12); color: #d97706; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; padding: 2px 8px; border-radius: 8px; margin-bottom: 4px;">
                                                    {{ $misa->jenis_misa ?? 'Misa Kudus' }}
                                                </span>
                                                <h5 style="font-size: 1rem; font-weight: 800; color: #1e293b; margin: 0 0 4px;">
                                                    {{ $title }}
                                                </h5>
                                                <p style="font-size: 0.82rem; color: #64748b; margin: 0; display: flex; align-items: center; gap: 5px;">
                                                    <i class="fa-solid fa-church text-teal-600"></i> {{ $location }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Pelayan & Intensi -->
                                        <div style="display: flex; flex-direction: column; gap: 6px; min-width: 220px;">
                                            @if(!empty($misa->pelayan))
                                                <div style="font-size: 0.8rem; color: #334155; display: flex; align-items: center; gap: 6px;">
                                                    <i class="fa-solid fa-user-tie text-amber-600"></i>
                                                    <span><strong>Pemimpin:</strong> {{ $misa->pelayan }}</span>
                                                </div>
                                            @endif
                                            @if(!empty($misa->intensi))
                                                <div style="font-size: 0.78rem; color: #9f1239; background: #fff1f2; padding: 4px 10px; border-radius: 8px; border: 1px solid #ffe4e6;">
                                                    <i class="fa-solid fa-heart me-1"></i> <strong>Intensi:</strong> {{ Str::limit($misa->intensi, 60) }}
                                                </div>
                                            @endif
                                            @if(!empty($misa->catatan))
                                                <div style="font-size: 0.75rem; color: #475569;">
                                                    <i class="fa-solid fa-circle-info text-blue-500 me-1"></i> {{ $misa->catatan }}
                                                </div>
                                            @endif
                                        </div>

                                    </div>
                                @endforeach
                            </div>

                        </div>
                    @endforeach
                </div>
            @else
                <!-- Empty State -->
                <div style="background: #ffffff; border-radius: 20px; padding: 60px 20px; text-align: center; border: 1px solid #e2e8f0; box-shadow: 0 5px 20px rgba(0,0,0,0.04);">
                    <div style="width: 64px; height: 64px; border-radius: 50%; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; margin: 0 auto 16px;">
                        <i class="fa-regular fa-calendar-xmark"></i>
                    </div>
                    <h4 style="font-size: 1.15rem; font-weight: 800; color: #1e293b; margin-bottom: 6px;">
                        Belum Ada Jadwal Misa Terdaftar di Bulan Ini
                    </h4>
                    <p style="font-size: 0.88rem; color: #64748b; margin-bottom: 18px;">
                        Silakan gunakan tombol navigasi bulan di bagian atas untuk melihat jadwal pada bulan lainnya.
                    </p>
                    <a href="{{ route('jadwal-misa', ['bulan' => now()->month, 'tahun' => now()->year]) }}" 
                       style="display: inline-flex; align-items: center; gap: 6px; background: var(--primary-teal, #00897b); color: #ffffff; font-size: 0.82rem; font-weight: 700; padding: 8px 20px; border-radius: 20px; text-decoration: none;">
                        <i class="fa-solid fa-calendar-day"></i> Lihat Bulan Ini
                    </a>
                </div>
            @endif

        </div>

        <!-- Share Buttons -->
        <div style="margin-top: 35px;">
            @include('partials.share-buttons', ['title' => 'Jadwal Misa & Perayaan Ekaristi - ' . ($globalNamaParoki ?? 'Paroki')])
        </div>

    </div>
</section>
@endsection
