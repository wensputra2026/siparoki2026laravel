<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - {{ $globalNamaParoki ?? 'SIPAROKI' }}</title>
    @if(!empty($globalLogo))
        <link rel="icon" type="image/jpeg" href="{{ $globalLogo }}">
    @else
        <link rel="icon" type="image/jpeg" href="/assets/uploads/profil/logo_paroki_1787370466.jpeg">
    @endif
    <!-- Local Offline Poppins Fonts & FontAwesome -->
    <link rel="preload" href="/fonts/poppins/poppins-latin-400-normal.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/poppins/poppins-latin-600-normal.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="/fonts/poppins/poppins-latin-700-normal.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="/vendor/fontawesome/css/all.min.css">
    <style>
        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 400;
            font-display: swap;
            src: url('/fonts/poppins/poppins-latin-400-normal.woff2') format('woff2');
        }
        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 600;
            font-display: swap;
            src: url('/fonts/poppins/poppins-latin-600-normal.woff2') format('woff2');
        }
        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 700;
            font-display: swap;
            src: url('/fonts/poppins/poppins-latin-700-normal.woff2') format('woff2');
        }
        body, html {
            font-family: 'Poppins', sans-serif;
        }
    </style>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: ['selector', '[data-theme="dark"]'],
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        primary: '#0c4a6e',
                        accent: '#0284c7',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-100 dark:bg-[#090e1a] text-slate-900 dark:text-white min-h-screen flex items-center justify-center p-4">

    <div class="max-w-md w-full">
        <!-- Card -->
        <div class="bg-white dark:bg-[#101d31] rounded-3xl p-8 border border-slate-200/80 dark:border-[#263a55] shadow-xl">
            <!-- Header -->
            <div class="text-center mb-6">
                <div class="w-16 h-16 rounded-full overflow-hidden mx-auto mb-3 shadow-md ring-4 ring-sky-500/10 flex items-center justify-center bg-sky-50 dark:bg-sky-950/50">
                    @if(!empty($globalLogo))
                        <img src="{{ $globalLogo }}" alt="Logo Paroki" class="w-full h-full object-contain">
                    @else
                        <img src="/assets/uploads/profil/logo_paroki_1787370466.jpeg" alt="Logo Paroki" class="w-full h-full object-contain" onerror="this.src='/favicon.ico'">
                    @endif
                </div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">Masuk SIPAROKI</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Sistem Informasi Pelayanan &amp; Administrasi Paroki</p>
            </div>

            @if (session('status'))
                <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-900 text-xs text-emerald-700 dark:text-emerald-300">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('success'))
                <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-900 text-xs text-emerald-700 dark:text-emerald-300">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-900 text-xs text-red-600 dark:text-red-400 space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('login.process') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Email atau Username <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-user text-xs"></i>
                        </div>
                        <input type="text" name="login" value="{{ old('login') }}" required autofocus placeholder="Masukkan email atau username" class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-[#263a55] bg-white dark:bg-[#07111f] text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                            Kata Sandi <span class="text-red-500">*</span>
                        </label>
                        <a href="{{ route('lupa-password') }}" class="text-xs text-sky-600 dark:text-sky-400 hover:underline">Lupa sandi?</a>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-lock text-xs"></i>
                        </div>
                        <input type="password" name="password" required placeholder="••••••••" class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-[#263a55] bg-white dark:bg-[#07111f] text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-sky-500 transition">
                    </div>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember" class="w-4 h-4 text-sky-600 rounded border-slate-300 focus:ring-sky-500">
                    <label for="remember" class="ml-2 text-xs text-slate-600 dark:text-slate-400">Ingat saya</label>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white text-sm font-semibold shadow-md shadow-sky-600/20 transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    <span>Masuk ke Akun</span>
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-800 text-center">
                <p class="text-xs text-slate-500 dark:text-slate-400">
                    Belum memiliki akun umat?
                    <a href="{{ route('register') }}" class="font-semibold text-sky-600 dark:text-sky-400 hover:underline">Daftar Sekarang</a>
                </p>
                <div class="mt-3">
                    <a href="{{ route('beranda') }}" class="text-xs text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition inline-flex items-center gap-1">
                        <i class="fa-solid fa-arrow-left text-[10px]"></i> Kembali ke Beranda
                    </a>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
