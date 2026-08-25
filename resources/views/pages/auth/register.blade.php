<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Akun Umat - {{ $globalNamaParoki ?? 'SIPAROKI' }}</title>
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
<body class="bg-slate-100 dark:bg-[#090e1a] text-slate-900 dark:text-white min-h-screen flex items-center justify-center p-4 py-8">

    <div class="max-w-xl w-full">
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
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">Pendaftaran Akun Umat</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Registrasi mandiri Portal Umat Katolik</p>
            </div>

            @if ($errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-red-50 dark:bg-red-950/50 border border-red-200 dark:border-red-900 text-xs text-red-600 dark:text-red-400 space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form action="{{ route('register.process') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Row 1: Nama & NIK -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Nama Lengkap &amp; Baptis <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap') }}" required placeholder="Contoh: Petrus Paulus" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-[#263a55] bg-white dark:bg-[#07111f] text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            NIK (16 Digit KTP)
                        </label>
                        <input type="text" name="nik" value="{{ old('nik') }}" maxlength="16" placeholder="5371xxxxxxxxxxxx" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-[#263a55] bg-white dark:bg-[#07111f] text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                    </div>
                </div>

                <!-- Row 2: Wilayah & Stasi/Kapela & KUB -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Wilayah</label>
                        <select name="wilayah_id" id="select_wilayah" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-[#263a55] bg-white dark:bg-[#07111f] text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                            <option value="">-- Wilayah --</option>
                            @foreach($wilayahs ?? [] as $w)
                                <option value="{{ $w->id }}" {{ old('wilayah_id') == $w->id ? 'selected' : '' }}>{{ $w->nama_wilayah ?? $w->nama ?? ('Wilayah ' . $w->id) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Stasi / Kapela</label>
                        <select name="kapela_id" id="select_kapela" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-[#263a55] bg-white dark:bg-[#07111f] text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                            <option value="">-- Stasi / Kapela --</option>
                            @foreach($kapelas ?? [] as $ka)
                                <option value="{{ $ka->id }}" {{ old('kapela_id') == $ka->id ? 'selected' : '' }}>{{ $ka->nama_kapela ?? ('Stasi / Kapela ' . $ka->id) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">KUB</label>
                        <select name="kub_id" id="select_kub" class="w-full px-3 py-2.5 rounded-xl border border-slate-300 dark:border-[#263a55] bg-white dark:bg-[#07111f] text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                            <option value="">-- KUB --</option>
                            @foreach($kubs ?? [] as $k)
                                <option value="{{ $k->id }}" data-wilayah="{{ $k->wilayah_id ?? '' }}" data-kapela="{{ $k->kapela_id ?? '' }}" {{ old('kub_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kub ?? $k->nama ?? ('KUB ' . $k->id) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Row 3: Kontak & Akun -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            No. WhatsApp <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="no_hp" value="{{ old('no_hp') }}" required placeholder="08123456789" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-[#263a55] bg-white dark:bg-[#07111f] text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Alamat Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="email@domain.com" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-[#263a55] bg-white dark:bg-[#07111f] text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Username
                        </label>
                        <input type="text" name="username" value="{{ old('username') }}" placeholder="petrus12" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-[#263a55] bg-white dark:bg-[#07111f] text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                    </div>
                </div>

                <!-- Row 4: Password -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Kata Sandi <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password" required placeholder="Minimal 6 karakter" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-[#263a55] bg-white dark:bg-[#07111f] text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Konfirmasi Sandi <span class="text-red-500">*</span>
                        </label>
                        <input type="password" name="password_confirmation" required placeholder="Ulangi kata sandi" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 dark:border-[#263a55] bg-white dark:bg-[#07111f] text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 px-4 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-sm transition shadow-md shadow-amber-600/20">
                        Daftar Akun Sekarang
                    </button>
                </div>
            </form>

            <!-- Bottom Links -->
            <div class="mt-6 pt-4 border-t border-slate-200 dark:border-slate-800 text-xs">
                <div class="flex items-center justify-between gap-2">
                    <a href="{{ url('/admin/login') }}" class="text-slate-500 hover:text-amber-600 dark:hover:text-amber-400 font-medium transition">
                        Sudah punya akun? Masuk
                    </a>
                    <a href="{{ url('/lupa-password') }}" class="text-slate-500 hover:text-amber-600 dark:hover:text-amber-400 font-medium transition">
                        Lupa kata sandi?
                    </a>
                </div>

                <div class="mt-4 text-center">
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                            <polyline points="9 22 9 12 15 12 15 22"/>
                        </svg>
                        <span>Kembali ke Beranda Website</span>
                    </a>
                </div>
            </div>
        </div>
    </div>    <script src="/js/pages/register.js" defer></script></body>
</html>


