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

            @if (!empty($error_message) || session('error'))
                <div class="mb-5 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-900 text-xs text-rose-700 dark:text-rose-300 flex items-start gap-3 shadow-xs">
                    <i class="fa-solid fa-triangle-exclamation text-rose-600 text-base shrink-0 mt-0.5"></i>
                    <div class="space-y-0.5">
                        <span class="font-bold block">Pemberitahuan Pemeliharaan:</span>
                        <p class="leading-relaxed">{{ $error_message ?? session('error') }}</p>
                    </div>
                </div>
            @endif

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

            @if (isset($errors) && $errors->any())
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
                        <input type="text" name="login" value="{{ old('login') }}" required autofocus placeholder="Masukkan email atau username" class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-[#263a55] bg-white dark:bg-[#07111f] text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300">
                            Kata Sandi <span class="text-red-500">*</span>
                        </label>
                        <a href="{{ route('lupa-password') }}" class="text-xs text-amber-600 dark:text-amber-400 hover:underline font-medium">Lupa sandi?</a>
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-lock text-xs"></i>
                        </div>
                        <input type="password" name="password" id="input_password" required placeholder="••••••••" class="w-full pl-10 pr-10 py-2.5 rounded-xl border border-slate-300 dark:border-[#263a55] bg-white dark:bg-[#07111f] text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                        <button type="button" id="btn_toggle_password" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer transition focus:outline-none" title="Tampilkan/Sembunyikan Kata Sandi">
                            <i class="fa-solid fa-eye text-xs" id="icon_toggle_password"></i>
                        </button>
                    </div>
                </div>

                @if (!empty($showCaptcha))
                <!-- Simple Math CAPTCHA -->
                <div class="rounded-2xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-[#263a55] p-3.5 space-y-2.5 shadow-2xs">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-semibold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                            <i class="fa-solid fa-shield-halved text-amber-500 text-xs"></i>
                            <span>Verifikasi Keamanan (CAPTCHA)</span>
                            <span class="text-red-500">*</span>
                        </label>
                        <span class="text-[10px] text-slate-400 font-medium">Anti-Bot</span>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- Tantangan Penjumlahan -->
                        <div class="flex-1 flex items-center justify-center py-2 px-3 rounded-xl bg-amber-500/10 dark:bg-amber-500/20 border border-amber-500/30 text-amber-600 dark:text-amber-400 font-mono font-bold text-sm tracking-wider select-none shadow-inner">
                            <span id="captcha-question">{{ $captchaQuestion ?? '?' }}</span>
                        </div>

                        <!-- Tombol Reload Tantangan -->
                        <button
                            type="button"
                            id="btn-refresh-captcha"
                            class="w-10 h-10 rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-amber-500 dark:hover:text-amber-400 hover:border-amber-400 transition flex items-center justify-center shadow-xs cursor-pointer shrink-0"
                            title="Ganti Soal CAPTCHA"
                        >
                            <i class="fa-solid fa-arrows-rotate text-xs" id="icon-refresh-captcha"></i>
                        </button>
                    </div>

                    <!-- Input Jawaban -->
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <i class="fa-solid fa-calculator text-xs"></i>
                        </div>
                        <input
                            type="number"
                            name="captcha"
                            id="input_captcha"
                            required
                            autocomplete="off"
                            placeholder="Ketik hasil perhitungan angka..."
                            class="w-full pl-10 pr-3.5 py-2 rounded-xl border border-slate-300 dark:border-[#263a55] bg-white dark:bg-[#07111f] text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition"
                        >
                    </div>
                    @error('captcha')
                        <p class="text-xs text-red-500 font-medium mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @endif

                <div class="flex items-center">
                    <input type="checkbox" name="remember" id="remember" class="w-4 h-4 text-amber-500 rounded border-slate-300 focus:ring-amber-500 cursor-pointer">
                    <label for="remember" class="ml-2 text-xs text-slate-600 dark:text-slate-400 cursor-pointer select-none">Ingat saya</label>
                </div>

                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-amber-500 hover:bg-amber-600 active:bg-amber-700 text-white text-sm font-semibold shadow-md shadow-amber-500/20 transition flex items-center justify-center gap-2 cursor-pointer">
                    <i class="fa-solid fa-arrow-right-to-bracket"></i>
                    <span>Masuk ke Akun</span>
                </button>
            </form>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const btnToggle = document.getElementById('btn_toggle_password');
                    const inputPassword = document.getElementById('input_password');
                    const iconToggle = document.getElementById('icon_toggle_password');

                    if (btnToggle && inputPassword && iconToggle) {
                        btnToggle.addEventListener('click', function(e) {
                            e.preventDefault();
                            if (inputPassword.type === 'password') {
                                inputPassword.type = 'text';
                                iconToggle.classList.remove('fa-eye');
                                iconToggle.classList.add('fa-eye-slash');
                            } else {
                                inputPassword.type = 'password';
                                iconToggle.classList.remove('fa-eye-slash');
                                iconToggle.classList.add('fa-eye');
                            }
                        });
                    }

                    // Refresh CAPTCHA
                    const btnRefreshCaptcha = document.getElementById('btn-refresh-captcha');
                    const captchaQuestion = document.getElementById('captcha-question');
                    const iconRefreshCaptcha = document.getElementById('icon-refresh-captcha');

                    if (btnRefreshCaptcha && captchaQuestion) {
                        btnRefreshCaptcha.addEventListener('click', function(e) {
                            e.preventDefault();
                            if (iconRefreshCaptcha) iconRefreshCaptcha.classList.add('fa-spin');
                            fetch('/captcha/refresh')
                                .then(res => res.json())
                                .then(data => {
                                    if (data && data.question) {
                                        captchaQuestion.textContent = data.question;
                                    }
                                })
                                .catch(err => console.error('Gagal reload captcha:', err))
                                .finally(() => {
                                    if (iconRefreshCaptcha) {
                                        setTimeout(() => iconRefreshCaptcha.classList.remove('fa-spin'), 350);
                                    }
                                });
                        });
                    }
                });
            </script>

            <!-- Bottom Icon Actions -->
            <div class="mt-6 pt-5 border-t border-slate-200 dark:border-slate-800 flex items-center justify-center gap-4">
                <a href="{{ route('register') }}" title="Belum Punya Akun? Daftar Sekarang" aria-label="Daftar Akun Baru" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-amber-500 hover:border-amber-300 dark:hover:border-amber-500 hover:bg-amber-50 dark:hover:bg-amber-950/30 shadow-sm transition-all duration-200">
                    <i class="fa-solid fa-user-plus text-sm"></i>
                </a>
                <a href="{{ route('beranda') }}" title="Kembali ke Beranda" aria-label="Kembali ke Beranda" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-amber-500 hover:border-amber-300 dark:hover:border-amber-500 hover:bg-amber-50 dark:hover:bg-amber-950/30 shadow-sm transition-all duration-200">
                    <i class="fa-solid fa-house text-sm"></i>
                </a>
                <a href="{{ route('lupa-password') }}" title="Lupa Kata Sandi?" aria-label="Lupa Kata Sandi" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-amber-500 hover:border-amber-300 dark:hover:border-amber-500 hover:bg-amber-50 dark:hover:bg-amber-950/30 shadow-sm transition-all duration-200">
                    <i class="fa-solid fa-key text-sm"></i>
                </a>
            </div>
        </div>
    </div>

</body>
</html>
