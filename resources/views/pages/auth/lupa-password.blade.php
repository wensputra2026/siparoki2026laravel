<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Kata Sandi - {{ $globalNamaParoki ?? 'SIPAROKI' }}</title>
    @if(!empty($globalLogo))
        <link rel="icon" type="image/jpeg" href="{{ $globalLogo }}">
    @else
        <link rel="icon" type="image/jpeg" href="/assets/uploads/profil/logo_paroki_1787370466.jpeg">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
                <div class="w-16 h-16 rounded-full overflow-hidden mx-auto mb-3 shadow-md ring-4 ring-amber-500/10 flex items-center justify-center bg-amber-50 dark:bg-amber-950/50">
                    @if(!empty($globalLogo))
                        <img src="{{ $globalLogo }}" alt="Logo Paroki" class="w-full h-full object-contain">
                    @else
                        <img src="/assets/uploads/profil/logo_paroki_1787370466.jpeg" alt="Logo Paroki" class="w-full h-full object-contain">
                    @endif
                </div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">Lupa Kata Sandi</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Masukkan data akun untuk verifikasi pemulihan</p>
            </div>

            @if (session('status'))
                <div class="mb-5 p-3.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 border border-emerald-200 dark:border-emerald-900 text-xs text-emerald-700 dark:text-emerald-300">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-900 text-xs text-red-600 dark:text-red-400 space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('lupa-password.process') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                        Alamat Email / Username / No. WhatsApp <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="identitas" value="{{ old('identitas') }}" required autofocus placeholder="Masukkan email, username, atau no. WA" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-[#263a55] bg-white dark:bg-[#07111f] text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 px-4 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-sm transition shadow-md shadow-amber-600/20">
                        Kirim Permintaan Pemulihan
                    </button>
                </div>
            </form>

            <!-- Bottom Icon Actions -->
            <div class="mt-6 pt-5 border-t border-slate-200 dark:border-slate-800 flex items-center justify-center gap-4">
                <a href="{{ route('login') }}" title="Sudah Ingat Sandi? Masuk ke Akun" aria-label="Masuk ke Akun" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-amber-500 hover:border-amber-300 dark:hover:border-amber-500 hover:bg-amber-50 dark:hover:bg-amber-950/30 shadow-sm transition-all duration-200">
                    <i class="fa-solid fa-arrow-right-to-bracket text-sm"></i>
                </a>
                <a href="{{ route('beranda') }}" title="Kembali ke Beranda" aria-label="Kembali ke Beranda" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-amber-500 hover:border-amber-300 dark:hover:border-amber-500 hover:bg-amber-50 dark:hover:bg-amber-950/30 shadow-sm transition-all duration-200">
                    <i class="fa-solid fa-house text-sm"></i>
                </a>
                <a href="{{ route('register') }}" title="Daftar Akun Umat Baru" aria-label="Daftar Akun Umat" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-amber-500 hover:border-amber-300 dark:hover:border-amber-500 hover:bg-amber-50 dark:hover:bg-amber-950/30 shadow-sm transition-all duration-200">
                    <i class="fa-solid fa-user-plus text-sm"></i>
                </a>
            </div>
        </div>
    </div>

</body>
</html>

