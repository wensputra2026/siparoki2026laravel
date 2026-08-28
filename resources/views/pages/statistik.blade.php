@extends('layouts.app')
@section('title', 'Statistik & Demografi Umat Paroki - ' . ($globalNamaParoki ?? 'St. Vinsensius a Paulo Benlutu'))

@section('content')
<!-- Page Header / Breadcrumb Konoha Style -->
<section class="page-header">
    <div class="container">
        <h1 style="font-size: 2.5rem; font-weight: 800; margin-bottom: 12px; color: #ffffff;">Statistik &amp; Demografi Umat</h1>
        <p style="font-size: 1rem; color: rgba(255,255,255,0.85); margin-bottom: 18px; max-width: 680px; margin-left: auto; margin-right: auto;">
            Transparansi data demografi, komposisi jemaat, statistik sakramen, dan sebaran wilayah pelayanan pastoral Paroki St. Vinsensius a Paulo Benlutu.
        </p>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb" style="display: inline-flex; list-style: none; padding: 0; margin: 0 auto; gap: 8px; background: transparent; justify-content: center; align-items: center; flex-wrap: wrap;">
                <li class="breadcrumb-item" style="background: rgba(255,255,255,0.22); padding: 6px 18px; border-radius: 25px; font-size: 0.85rem;">
                    <a href="/" style="color: white; text-decoration: none; font-weight: 500;"><i class="fa-solid fa-house me-1" style="font-size: 0.75rem;"></i> Beranda</a>
                </li>
                <li class="breadcrumb-separator" style="color: rgba(255,255,255,0.75); font-size: 0.7rem; display: inline-flex; align-items: center;">
                    <i class="fa-solid fa-chevron-right"></i>
                </li>
                <li class="breadcrumb-item active" style="background: var(--primary-orange, #ff9800); color: white; padding: 6px 18px; border-radius: 25px; font-size: 0.85rem; font-weight: 700;">
                    Statistik
                </li>
            </ol>
        </nav>
    </div>
</section>

<!-- Content Section Konoha Style -->
<section class="content-section" style="padding: 50px 0 80px; background: #f4faf9;">
    <div class="container">
        
        <!-- TOP KPI CARDS (Summary Counters) -->
        <div class="row g-3 mb-5">
            <!-- Total Umat -->
            <div class="col-xl-3 col-md-6">
                <div style="background: #ffffff; border-radius: 20px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-top: 4px solid var(--primary-teal, #00897b); display: flex; align-items: center; justify-content: space-between; gap: 14px;">
                    <div>
                        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #64748b; display: block; margin-bottom: 4px;">
                            Total Jiwa Umat
                        </span>
                        <h2 style="font-size: 1.85rem; font-weight: 900; color: #0f172a; margin: 0;">
                            {{ number_format((int) ($totalUmat ?? 0), 0, ',', '.') }}
                        </h2>
                        <span style="font-size: 0.75rem; font-weight: 600; color: #059669; display: inline-flex; align-items: center; gap: 4px; margin-top: 4px;">
                            <i class="fa-solid fa-circle-check"></i> Terdata Aktif
                        </span>
                    </div>
                    <div style="width: 54px; height: 54px; border-radius: 16px; background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
            </div>

            <!-- Kepala Keluarga (KK) -->
            <div class="col-xl-3 col-md-6">
                <div style="background: #ffffff; border-radius: 20px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-top: 4px solid var(--primary-orange, #ff9800); display: flex; align-items: center; justify-content: space-between; gap: 14px;">
                    <div>
                        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #64748b; display: block; margin-bottom: 4px;">
                            Kepala Keluarga (KK)
                        </span>
                        <h2 style="font-size: 1.85rem; font-weight: 900; color: #0f172a; margin: 0;">
                            {{ number_format((int) ($totalKK ?? 0), 0, ',', '.') }}
                        </h2>
                        <span style="font-size: 0.75rem; font-weight: 600; color: #d97706; display: inline-flex; align-items: center; gap: 4px; margin-top: 4px;">
                            <i class="fa-solid fa-house-chimney-user"></i> Kartu Keluarga
                        </span>
                    </div>
                    <div style="width: 54px; height: 54px; border-radius: 16px; background: rgba(255,152,0,0.12); color: var(--primary-orange, #ff9800); display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                        <i class="fa-solid fa-house-user"></i>
                    </div>
                </div>
            </div>

            <!-- Komunitas Basis (KUB) -->
            <div class="col-xl-3 col-md-6">
                <div style="background: #ffffff; border-radius: 20px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-top: 4px solid #10b981; display: flex; align-items: center; justify-content: space-between; gap: 14px;">
                    <div>
                        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #64748b; display: block; margin-bottom: 4px;">
                            Komunitas KUB
                        </span>
                        <h2 style="font-size: 1.85rem; font-weight: 900; color: #0f172a; margin: 0;">
                            {{ number_format((int) ($totalKUB ?? 0), 0, ',', '.') }}
                        </h2>
                        <span style="font-size: 0.75rem; font-weight: 600; color: #059669; display: inline-flex; align-items: center; gap: 4px; margin-top: 4px;">
                            <i class="fa-solid fa-people-group"></i> Basis Umat
                        </span>
                    </div>
                    <div style="width: 54px; height: 54px; border-radius: 16px; background: #ecfdf5; color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                        <i class="fa-solid fa-hands-holding-child"></i>
                    </div>
                </div>
            </div>

            <!-- Kapela & Stasi -->
            <div class="col-xl-3 col-md-6">
                <div style="background: #ffffff; border-radius: 20px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; border-top: 4px solid #8b5cf6; display: flex; align-items: center; justify-content: space-between; gap: 14px;">
                    <div>
                        <span style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #64748b; display: block; margin-bottom: 4px;">
                            Stasi / Kapela
                        </span>
                        <h2 style="font-size: 1.85rem; font-weight: 900; color: #0f172a; margin: 0;">
                            {{ number_format((int) ($totalKapela ?? 0), 0, ',', '.') }}
                        </h2>
                        <span style="font-size: 0.75rem; font-weight: 600; color: #7c3aed; display: inline-flex; align-items: center; gap: 4px; margin-top: 4px;">
                            <i class="fa-solid fa-place-of-worship"></i> Tempat Ibadah
                        </span>
                    </div>
                    <div style="width: 54px; height: 54px; border-radius: 16px; background: #f5f3ff; color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
                        <i class="fa-solid fa-church"></i>
                    </div>
                </div>
            </div>
        </div>


        <!-- ROW 1 CHARTS: GENDER RATIO & AGE DEMOGRAPHICS -->
        <div class="row g-4 mb-4">
            
            <!-- Chart: Rasio Gender (Laki-laki vs Perempuan) -->
            <div class="col-lg-5">
                <div style="background: #ffffff; border-radius: 20px; padding: 28px; box-shadow: 0 5px 22px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; height: 100%; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
                            <div>
                                <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-teal, #00897b);">
                                    Komposisi Gender
                                </span>
                                <h3 style="font-size: 1.2rem; font-weight: 800; color: #0f172a; margin: 2px 0 0;">
                                    Rasio Jenis Kelamin
                                </h3>
                            </div>
                            <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                                <i class="fa-solid fa-venus-mars"></i>
                            </div>
                        </div>

                        <!-- Canvas Container -->
                        <div style="position: relative; height: 230px; margin: 10px 0 20px;">
                            <canvas id="genderDonutChart"></canvas>
                        </div>
                    </div>

                    <!-- Gender Stats Details -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; padding-top: 15px; border-top: 1px solid #f1f5f9;">
                        <div style="background: #eff6ff; border: 1px solid #dbeafe; border-radius: 14px; padding: 12px 14px; text-align: center;">
                            <span style="font-size: 0.72rem; font-weight: 700; color: #1d4ed8; text-transform: uppercase; display: block; margin-bottom: 2px;">
                                <i class="fa-solid fa-mars me-1"></i> Laki-laki
                            </span>
                            <div style="font-size: 1.35rem; font-weight: 900; color: #1e3a8a;">
                                {{ number_format($genderStats['pria'] ?? 0) }}
                            </div>
                            <span style="font-size: 0.72rem; font-weight: 700; color: #3b82f6;">
                                {{ $genderStats['pria_percent'] ?? 50 }}% dari Total
                            </span>
                        </div>

                        <div style="background: #fdf2f8; border: 1px solid #fce7f3; border-radius: 14px; padding: 12px 14px; text-align: center;">
                            <span style="font-size: 0.72rem; font-weight: 700; color: #be185d; text-transform: uppercase; display: block; margin-bottom: 2px;">
                                <i class="fa-solid fa-venus me-1"></i> Perempuan
                            </span>
                            <div style="font-size: 1.35rem; font-weight: 900; color: #831843;">
                                {{ number_format($genderStats['wanita'] ?? 0) }}
                            </div>
                            <span style="font-size: 0.72rem; font-weight: 700; color: #ec4899;">
                                {{ $genderStats['wanita_percent'] ?? 50 }}% dari Total
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart: Piramida Usia Demografi -->
            <div class="col-lg-7">
                <div style="background: #ffffff; border-radius: 20px; padding: 28px; box-shadow: 0 5px 22px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; height: 100%; display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
                            <div>
                                <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-orange, #ff9800);">
                                    Piramida Demografi
                                </span>
                                <h3 style="font-size: 1.2rem; font-weight: 800; color: #0f172a; margin: 2px 0 0;">
                                    Distribusi Kelompok Usia
                                </h3>
                            </div>
                            <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(255,152,0,0.12); color: var(--primary-orange, #ff9800); display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                                <i class="fa-solid fa-chart-simple"></i>
                            </div>
                        </div>

                        <!-- Canvas Container -->
                        <div style="position: relative; height: 230px; margin: 10px 0 20px;">
                            <canvas id="ageBarChart"></canvas>
                        </div>
                    </div>

                    <!-- Age Grid Summary -->
                    <div class="row g-2 pt-3" style="border-top: 1px solid #f1f5f9;">
                        @foreach($usiaStats as $u)
                            <div class="col-6 col-md-3">
                                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px 12px; text-align: center;">
                                    <span style="font-size: 0.68rem; font-weight: 700; color: #64748b; text-transform: uppercase; display: block; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <i class="fa-solid {{ $u['icon'] }} me-1" style="color: {{ $u['color'] }};"></i> {{ $u['label'] }}
                                    </span>
                                    <div style="font-size: 1.15rem; font-weight: 900; color: #0f172a;">
                                        {{ number_format($u['count']) }}
                                    </div>
                                    <span style="font-size: 0.68rem; font-weight: 700; color: {{ $u['color'] }};">
                                        {{ $u['percent'] }}%
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>


        <!-- ROW 2 CHARTS: SAKRAMEN & STATUS PERKAWINAN -->
        <div class="row g-4 mb-4">
            
            <!-- Chart: Pencapaian Sakramen -->
            <div class="col-lg-6">
                <div style="background: #ffffff; border-radius: 20px; padding: 28px; box-shadow: 0 5px 22px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; height: 100%;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
                        <div>
                            <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #0284c7;">
                                Inisiasi &amp; Pelayanan
                            </span>
                            <h3 style="font-size: 1.2rem; font-weight: 800; color: #0f172a; margin: 2px 0 0;">
                                Status Penerimaan Sakramen
                            </h3>
                        </div>
                        <div style="width: 38px; height: 38px; border-radius: 10px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                            <i class="fa-solid fa-water"></i>
                        </div>
                    </div>

                    <!-- Canvas Container -->
                    <div style="position: relative; height: 250px; margin-bottom: 20px;">
                        <canvas id="sakramenRadarChart"></canvas>
                    </div>

                    <!-- Sakramen Badges List -->
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; padding-top: 15px; border-top: 1px solid #f1f5f9;">
                        <div style="display: flex; align-items: center; justify-content: space-between; background: #f0fdf4; border: 1px solid #dcfce7; border-radius: 10px; padding: 8px 12px;">
                            <span style="font-size: 0.78rem; font-weight: 700; color: #166534;"><i class="fa-solid fa-droplet text-emerald-500 me-1"></i> Baptis</span>
                            <strong style="font-size: 0.95rem; color: #14532d;">{{ number_format($sakramenCount['baptis'] ?? 0) }}</strong>
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; background: #eff6ff; border: 1px solid #dbeafe; border-radius: 10px; padding: 8px 12px;">
                            <span style="font-size: 0.78rem; font-weight: 700; color: #1e40af;"><i class="fa-solid fa-bread-slice text-blue-500 me-1"></i> Komuni 1</span>
                            <strong style="font-size: 0.95rem; color: #1e3a8a;">{{ number_format($sakramenCount['komuni'] ?? 0) }}</strong>
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; background: #fefce8; border: 1px solid #fef08a; border-radius: 10px; padding: 8px 12px;">
                            <span style="font-size: 0.78rem; font-weight: 700; color: #854d0e;"><i class="fa-solid fa-fire text-amber-500 me-1"></i> Krisma</span>
                            <strong style="font-size: 0.95rem; color: #713f12;">{{ number_format($sakramenCount['krisma'] ?? 0) }}</strong>
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; background: #fdf2f8; border: 1px solid #fce7f3; border-radius: 10px; padding: 8px 12px;">
                            <span style="font-size: 0.78rem; font-weight: 700; color: #9d174d;"><i class="fa-solid fa-ring text-pink-500 me-1"></i> Perkawinan</span>
                            <strong style="font-size: 0.95rem; color: #831843;">{{ number_format($sakramenCount['perkawinan'] ?? 0) }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart: Status Perkawinan Umat -->
            <div class="col-lg-6">
                <div style="background: #ffffff; border-radius: 20px; padding: 28px; box-shadow: 0 5px 22px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; height: 100%;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px;">
                        <div>
                            <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #d97706;">
                                Status Keluarga
                            </span>
                            <h3 style="font-size: 1.2rem; font-weight: 800; color: #0f172a; margin: 2px 0 0;">
                                Status Pernikahan Umat
                            </h3>
                        </div>
                        <div style="width: 38px; height: 38px; border-radius: 10px; background: #fef3c7; color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                            <i class="fa-solid fa-ring"></i>
                        </div>
                    </div>

                    <!-- Canvas Container -->
                    <div style="position: relative; height: 250px; margin-bottom: 20px;">
                        <canvas id="marriagePieChart"></canvas>
                    </div>

                    <!-- Marriage Badges List -->
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; padding-top: 15px; border-top: 1px solid #f1f5f9;">
                        @foreach($statusKawinStats as $m)
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 8px 10px; text-align: center;">
                                <span style="font-size: 0.68rem; font-weight: 700; color: #64748b; display: block; margin-bottom: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $m['label'] }}
                                </span>
                                <div style="font-size: 1.1rem; font-weight: 900; color: #0f172a;">
                                    {{ number_format($m['count']) }}
                                </div>
                                <span style="font-size: 0.68rem; font-weight: 700; color: {{ $m['color'] }};">
                                    {{ $m['percent'] }}%
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>


        <!-- ROW 3: SEBARAN TERITORI PER KUB -->
        @if(count($sebaranStats ?? []) > 0)
        <div style="background: #ffffff; border-radius: 20px; padding: 30px; box-shadow: 0 5px 22px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; margin-bottom: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 22px;">
                <div>
                    <span style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary-teal, #00897b);">
                        Sebaran Teritori Pastoral
                    </span>
                    <h3 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 2px 0 0;">
                        Sebaran Jumlah Umat per Komunitas Basis (KUB)
                    </h3>
                </div>
                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(0,137,123,0.1); color: var(--primary-teal, #00897b); display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                    <i class="fa-solid fa-map-location-dot"></i>
                </div>
            </div>

            <!-- Canvas Horizontal Bar Chart -->
            <div style="position: relative; height: 280px;">
                <canvas id="kubHorizontalBarChart"></canvas>
            </div>
        </div>
        @endif

        <!-- Share Buttons -->
        <div style="margin-top: 30px; background: #ffffff; border-radius: 20px; padding: 25px 35px; box-shadow: 0 5px 22px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">
            @include('partials.share-buttons', ['title' => 'Statistik & Demografi Umat - ' . ($globalNamaParoki ?? 'Paroki')])
        </div>

    </div>
</section>

<!-- Include Chart.js via CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. GENDER DONUT CHART ---
    const ctxGender = document.getElementById('genderDonutChart');
    if (ctxGender) {
        new Chart(ctxGender, {
            type: 'doughnut',
            data: {
                labels: ['Laki-laki (Pria)', 'Perempuan (Wanita)'],
                datasets: [{
                    data: [{{ $genderStats['pria'] ?? 50 }}, {{ $genderStats['wanita'] ?? 50 }}],
                    backgroundColor: ['#2563eb', '#ec4899'],
                    hoverBackgroundColor: ['#1d4ed8', '#db2777'],
                    borderWidth: 3,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { family: 'Poppins', size: 12, weight: '600' },
                            padding: 16,
                            usePointStyle: true,
                        }
                    }
                }
            }
        });
    }

    // --- 2. AGE DISTRIBUTION BAR CHART ---
    const ctxAge = document.getElementById('ageBarChart');
    if (ctxAge) {
        const ageLabels = {!! json_encode(array_column($usiaStats, 'label')) !!};
        const ageCounts = {!! json_encode(array_column($usiaStats, 'count')) !!};
        const ageColors = {!! json_encode(array_column($usiaStats, 'color')) !!};

        new Chart(ctxAge, {
            type: 'bar',
            data: {
                labels: ageLabels,
                datasets: [{
                    label: 'Jumlah Jiwa',
                    data: ageCounts,
                    backgroundColor: ageColors,
                    borderRadius: 8,
                    borderSkipped: false,
                    maxBarThickness: 45
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { family: 'Poppins', size: 11 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Poppins', size: 11, weight: '600' } }
                    }
                }
            }
        });
    }

    // --- 3. SAKRAMEN RADAR / BAR CHART ---
    const ctxSakramen = document.getElementById('sakramenRadarChart');
    if (ctxSakramen) {
        new Chart(ctxSakramen, {
            type: 'bar',
            data: {
                labels: ['Baptis', 'Komuni Pertama', 'Krisma', 'Perkawinan'],
                datasets: [{
                    label: 'Penerima Sakramen',
                    data: [
                        {{ $sakramenCount['baptis'] ?? 0 }},
                        {{ $sakramenCount['komuni'] ?? 0 }},
                        {{ $sakramenCount['krisma'] ?? 0 }},
                        {{ $sakramenCount['perkawinan'] ?? 0 }}
                    ],
                    backgroundColor: ['#10b981', '#3b82f6', '#f59e0b', '#ec4899'],
                    borderRadius: 8,
                    maxBarThickness: 38
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { family: 'Poppins', size: 11 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Poppins', size: 11, weight: '600' } }
                    }
                }
            }
        });
    }

    // --- 4. MARRIAGE PIE CHART ---
    const ctxMarriage = document.getElementById('marriagePieChart');
    if (ctxMarriage) {
        const marrLabels = {!! json_encode(array_column($statusKawinStats, 'label')) !!};
        const marrCounts = {!! json_encode(array_column($statusKawinStats, 'count')) !!};
        const marrColors = {!! json_encode(array_column($statusKawinStats, 'color')) !!};

        new Chart(ctxMarriage, {
            type: 'pie',
            data: {
                labels: marrLabels,
                datasets: [{
                    data: marrCounts,
                    backgroundColor: marrColors,
                    borderWidth: 3,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { family: 'Poppins', size: 11, weight: '600' },
                            padding: 14,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
    }

    // --- 5. KUB HORIZONTAL BAR CHART ---
    const ctxKub = document.getElementById('kubHorizontalBarChart');
    if (ctxKub) {
        const kubLabels = {!! json_encode(array_column($sebaranStats, 'nama')) !!};
        const kubCounts = {!! json_encode(array_column($sebaranStats, 'count')) !!};

        new Chart(ctxKub, {
            type: 'bar',
            data: {
                labels: kubLabels,
                datasets: [{
                    label: 'Jumlah Umat (Jiwa)',
                    data: kubCounts,
                    backgroundColor: 'rgba(0, 137, 123, 0.85)',
                    hoverBackgroundColor: 'rgba(0, 137, 123, 1)',
                    borderRadius: 6,
                    maxBarThickness: 24
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { family: 'Poppins', size: 11 } }
                    },
                    y: {
                        grid: { display: false },
                        ticks: { font: { family: 'Poppins', size: 11, weight: '600' } }
                    }
                }
            }
        });
    }

});
</script>
@endsection
