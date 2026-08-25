<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $maintenanceTitle ?? 'Pemeliharaan Sistem' }} - {{ $globalNamaParoki ?? 'SIPAROKI' }}</title>
    <link rel="icon" type="image/x-icon" href="{{ $globalFavicon ?? asset('favicon.ico') }}">
    <link rel="stylesheet" href="/vendor/fontawesome/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #092e28 0%, #0d1527 50%, #061e1a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f8fafc;
            padding: 24px;
            position: relative;
            overflow-x: hidden;
        }

        .bg-decor {
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(20, 184, 166, 0.15) 0%, rgba(0,0,0,0) 70%);
            top: -100px;
            right: -100px;
            pointer-events: none;
        }

        .bg-decor-2 {
            position: absolute;
            width: 450px;
            height: 450px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.12) 0%, rgba(0,0,0,0) 70%);
            bottom: -100px;
            left: -100px;
            pointer-events: none;
        }

        .card {
            background: rgba(15, 23, 42, 0.75);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 28px;
            max-width: 640px;
            width: 100%;
            padding: 48px 36px;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(20, 184, 166, 0.2);
            position: relative;
            z-index: 10;
        }

        .icon-box {
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
            border-radius: 24px;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: #ffffff;
            box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.4);
            animation: pulse 2.5s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .paroki-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 16px;
            border-radius: 9999px;
            background: rgba(20, 184, 166, 0.15);
            border: 1px solid rgba(20, 184, 166, 0.3);
            color: #2dd4bf;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 20px;
        }

        h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 28px;
            font-weight: 800;
            line-height: 1.25;
            color: #ffffff;
            margin-bottom: 16px;
        }

        p.desc {
            font-size: 14px;
            line-height: 1.65;
            color: #94a3b8;
            margin-bottom: 28px;
        }

        .info-box {
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 18px;
            padding: 16px 20px;
            margin-bottom: 28px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            text-align: left;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 13px;
            color: #cbd5e1;
        }

        .info-item i {
            color: #f59e0b;
            font-size: 15px;
            width: 20px;
            text-align: center;
        }

        .btn-admin {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 24px;
            border-radius: 14px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #ffffff;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .btn-admin:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .footer-text {
            margin-top: 24px;
            font-size: 11px;
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="bg-decor"></div>
    <div class="bg-decor-2"></div>

    <div class="card">
        <div class="icon-box">
            <i class="fa-solid fa-screwdriver-wrench"></i>
        </div>

        <div class="paroki-badge">
            <i class="fa-solid fa-church"></i>
            {{ $globalNamaParoki ?? 'Paroki St. Vinsensius a Paulo Benlutu' }}
        </div>

        <h1>{{ $maintenanceTitle ?? 'Website Sedang Dalam Pemeliharaan' }}</h1>

        <p class="desc">
            {{ $maintenanceMessage ?? 'Mohon maaf atas ketidaknyamanannya. Website paroki kami sedang melakukan pembaruan berkala untuk meningkatkan kenyamanan dan kecepatan pelayanan data umat. Kami akan segera kembali online.' }}
        </p>

        <div class="info-box">
            @if(!empty($maintenanceUntil))
            <div class="info-item">
                <i class="fa-solid fa-clock"></i>
                <span><strong>Estimasi Selesai:</strong> {{ $maintenanceUntil }}</span>
            </div>
            @endif

            @if(!empty($maintenanceContact))
            <div class="info-item">
                <i class="fa-solid fa-phone"></i>
                <span><strong>Kontak Darurat Sekretariat:</strong> {{ $maintenanceContact }}</span>
            </div>
            @endif

            <div class="info-item">
                <i class="fa-solid fa-shield-halved"></i>
                <span><strong>Status Sistem:</strong> Sedang dalam pemeliharaan terjadwal</span>
            </div>
        </div>

        <a href="/login" class="btn-admin">
            <i class="fa-solid fa-lock"></i>
            Login Panel Petugas / Admin
        </a>

        <div class="footer-text">
            SIPAROKI &copy; {{ date('Y') }} {{ $globalNamaParoki ?? 'St. Vinsensius a Paulo Benlutu' }}. All rights reserved.
        </div>
    </div>
</body>
</html>
