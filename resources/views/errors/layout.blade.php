<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ $globalNamaParoki ?? config('app.name', 'SIPAROKI') }}</title>
    <link rel="icon" type="image/x-icon" href="{{ $globalFavicon ?? asset('favicon.ico') }}">
    <link rel="stylesheet" href="/vendor/fontawesome/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #061917 0%, #0b1524 50%, #03100e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #f8fafc;
            padding: 24px;
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient Glow & Decorative Elements */
        .bg-glow-1 {
            position: absolute;
            width: 550px;
            height: 550px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(20, 184, 166, 0.15) 0%, rgba(0,0,0,0) 70%);
            top: -120px;
            right: -120px;
            pointer-events: none;
            animation: floatGlow 8s ease-in-out infinite alternate;
        }

        .bg-glow-2 {
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(245, 158, 11, 0.14) 0%, rgba(0,0,0,0) 70%);
            bottom: -120px;
            left: -120px;
            pointer-events: none;
            animation: floatGlow 10s ease-in-out infinite alternate-reverse;
        }

        @keyframes floatGlow {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, 30px) scale(1.08); }
        }

        .cross-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 420px;
            color: rgba(255, 255, 255, 0.015);
            pointer-events: none;
            z-index: 1;
            font-family: serif;
            user-select: none;
        }

        .card {
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 32px;
            max-width: 620px;
            width: 100%;
            padding: 48px 36px;
            text-align: center;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(255, 255, 255, 0.05);
            position: relative;
            z-index: 10;
        }

        .status-badge-wrapper {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 18px;
            border-radius: 9999px;
            background: rgba(20, 184, 166, 0.12);
            border: 1px solid rgba(20, 184, 166, 0.3);
            color: #2dd4bf;
            font-size: 11.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            margin-bottom: 24px;
        }

        .error-code-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 8px;
        }

        .error-code {
            font-family: 'Outfit', sans-serif;
            font-size: 96px;
            font-weight: 900;
            line-height: 1;
            letter-spacing: -0.04em;
            background: linear-gradient(135deg, #f59e0b 0%, #fbbf24 40%, #14b8a6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 10px 30px rgba(245, 158, 11, 0.2);
        }

        .icon-floating-badge {
            position: absolute;
            right: -14px;
            top: -6px;
            width: 48px;
            height: 48px;
            border-radius: 16px;
            background: rgba(30, 41, 59, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #f59e0b;
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.5);
            animation: bounceSoft 3s ease-in-out infinite;
        }

        @keyframes bounceSoft {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        h1.title {
            font-family: 'Outfit', sans-serif;
            font-size: 26px;
            font-weight: 800;
            line-height: 1.3;
            color: #ffffff;
            margin-bottom: 12px;
        }

        p.description {
            font-size: 14px;
            line-height: 1.7;
            color: #94a3b8;
            margin-bottom: 32px;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .action-group {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 12px 22px;
            border-radius: 14px;
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: #ffffff;
            box-shadow: 0 8px 20px -4px rgba(245, 158, 11, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            transform: translateY(-2px);
            box-shadow: 0 12px 24px -4px rgba(245, 158, 11, 0.5);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #f1f5f9;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.14);
            border-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        .footer-note {
            margin-top: 36px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            font-size: 11.5px;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }

        .footer-note a {
            color: #2dd4bf;
            text-decoration: none;
            font-weight: 600;
        }

        .footer-note a:hover {
            text-decoration: underline;
        }

        @media (max-width: 640px) {
            .card {
                padding: 36px 20px;
            }
            .error-code {
                font-size: 76px;
            }
            h1.title {
                font-size: 22px;
            }
            .action-group {
                flex-direction: column;
                width: 100%;
            }
            .btn {
                width: 100%;
            }
            .footer-note {
                flex-direction: column;
                text-align: center;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="bg-glow-1"></div>
    <div class="bg-glow-2"></div>
    <div class="cross-watermark">✝</div>

    <div class="card">
        <div class="status-badge-wrapper">
            <i class="fa-solid fa-church"></i>
            <span>{{ $globalNamaParoki ?? 'SIPAROKI' }}</span>
        </div>

        <div class="error-code-wrapper">
            <div class="error-code">@yield('code')</div>
            <div class="icon-floating-badge">
                @yield('icon')
            </div>
        </div>

        <h1 class="title">@yield('heading')</h1>

        <p class="description">
            @yield('message')
        </p>

        <div class="action-group">
            <a href="/" class="btn btn-primary">
                <i class="fa-solid fa-house"></i>
                <span>Kembali ke Beranda</span>
            </a>
            <button onclick="window.history.length > 1 ? window.history.back() : window.location.href='/'" class="btn btn-secondary">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Halaman Sebelumnya</span>
            </button>
            @auth
            <a href="{{ auth()->user()->role ? '/' . strtolower(preg_replace('/[^a-z0-9]/', '', auth()->user()->role->slug ?? 'superadmin')) : '/login' }}" class="btn btn-secondary">
                <i class="fa-solid fa-gauge-high"></i>
                <span>Dashboard Saya</span>
            </a>
            @else
            <a href="/login" class="btn btn-secondary">
                <i class="fa-solid fa-lock"></i>
                <span>Login Petugas</span>
            </a>
            @endauth
        </div>

        <div class="footer-note">
            <span>SIPAROKI &copy; {{ date('Y') }} Sistem Informasi Manajemen Paroki</span>
            <span>Kode Error: <strong style="color:#cbd5e1">HTTP-@yield('code')</strong></span>
        </div>
    </div>
</body>
</html>
