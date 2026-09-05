<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Akun - {{ $globalNamaParoki ?? 'SIPAROKI' }}</title>
    @if(!empty($globalLogo))
        <link rel="icon" type="image/jpeg" href="{{ $globalLogo }}">
    @else
        <link rel="icon" type="image/jpeg" href="/assets/uploads/profil/logo_paroki_1787370466.jpeg">
    @endif
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Tailwind CSS -->
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

    <style>
        /* Custom Select2 Styling to Match Tailwind Design */
        .select2-container--default .select2-selection--single {
            height: 42px !important;
            padding: 6px 12px !important;
            border-radius: 0.75rem !important;
            border: 1px solid #cbd5e1 !important;
            background-color: #ffffff !important;
            font-size: 0.875rem !important;
            display: flex !important;
            align-items: center !important;
        }
        [data-theme="dark"] .select2-container--default .select2-selection--single {
            background-color: #07111f !important;
            border-color: #263a55 !important;
            color: #ffffff !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #1e293b !important;
            line-height: normal !important;
            padding-left: 0 !important;
        }
        [data-theme="dark"] .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #f1f5f9 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            right: 8px !important;
        }
        .select2-dropdown {
            border-radius: 0.75rem !important;
            border: 1px solid #cbd5e1 !important;
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1) !important;
            font-size: 0.875rem !important;
            overflow: hidden !important;
            z-index: 9999 !important;
        }
        [data-theme="dark"] .select2-dropdown {
            background-color: #0d1b2e !important;
            border-color: #263a55 !important;
            color: #fff !important;
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border-radius: 0.5rem !important;
            border: 1px solid #cbd5e1 !important;
            padding: 6px 10px !important;
            font-size: 0.8125rem !important;
        }
        [data-theme="dark"] .select2-container--default .select2-search--dropdown .select2-search__field {
            background-color: #07111f !important;
            border-color: #263a55 !important;
            color: #fff !important;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #f59e0b !important;
            color: #ffffff !important;
        }
        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #fef3c7 !important;
            color: #92400e !important;
        }
        [data-theme="dark"] .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #78350f !important;
            color: #fef3c7 !important;
        }
    </style>
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
                <h1 class="text-xl font-bold text-slate-900 dark:text-white">Pendaftaran Akun</h1>
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

                <!-- Row 2: Wilayah & Stasi/Kapela & KUB (Select2 Searchable) -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Wilayah</label>
                        <select name="wilayah_id" id="select_wilayah" class="select2 w-full">
                            <option value="">-- Semua Wilayah --</option>
                            @foreach($wilayahs ?? [] as $w)
                                <option value="{{ $w->id }}" {{ old('wilayah_id') == $w->id ? 'selected' : '' }}>{{ $w->nama_wilayah ?? $w->nama ?? ('Wilayah ' . $w->id) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Stasi / Kapela</label>
                        <select name="kapela_id" id="select_kapela" class="select2 w-full">
                            <option value="">-- Semua Stasi / Kapela --</option>
                            @foreach($kapelas ?? [] as $ka)
                                <option value="{{ $ka->id }}" {{ old('kapela_id') == $ka->id ? 'selected' : '' }}>{{ $ka->nama_kapela ?? ('Stasi / Kapela ' . $ka->id) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">KUB (Basis)</label>
                        <select name="kub_id" id="select_kub" class="select2 w-full">
                            <option value="">-- Pilih KUB --</option>
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
                        <div class="relative">
                            <input type="password" name="password" id="reg_password" required placeholder="Minimal 6 karakter" class="w-full pl-3.5 pr-10 py-2.5 rounded-xl border border-slate-300 dark:border-[#263a55] bg-white dark:bg-[#07111f] text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                            <button type="button" onclick="toggleRegPassword('reg_password', 'icon_reg_pwd')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer transition focus:outline-none">
                                <i class="fa-solid fa-eye text-xs" id="icon_reg_pwd"></i>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                            Konfirmasi Sandi <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" name="password_confirmation" id="reg_password_confirmation" required placeholder="Ulangi kata sandi" class="w-full pl-3.5 pr-10 py-2.5 rounded-xl border border-slate-300 dark:border-[#263a55] bg-white dark:bg-[#07111f] text-slate-900 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition">
                            <button type="button" onclick="toggleRegPassword('reg_password_confirmation', 'icon_reg_pwd_confirm')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 cursor-pointer transition focus:outline-none">
                                <i class="fa-solid fa-eye text-xs" id="icon_reg_pwd_confirm"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 px-4 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-sm transition shadow-md shadow-amber-600/20">
                        Daftar Akun Sekarang
                    </button>
                </div>
            </form>

            <!-- Bottom Icon Actions -->
            <div class="mt-6 pt-5 border-t border-slate-200 dark:border-slate-800 flex items-center justify-center gap-4">
                <a href="{{ route('login') }}" title="Sudah Punya Akun? Masuk" aria-label="Masuk ke Akun" class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-500 hover:text-amber-500 hover:border-amber-300 dark:hover:border-amber-500 hover:bg-amber-50 dark:hover:bg-amber-950/30 shadow-sm transition-all duration-200">
                    <i class="fa-solid fa-arrow-right-to-bracket text-sm"></i>
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

    <!-- jQuery & Select2 JS -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Initialize Select2 on all three dropdowns
            $('#select_wilayah').select2({
                placeholder: '-- Pilih Wilayah --',
                allowClear: true,
                width: '100%'
            });

            $('#select_kapela').select2({
                placeholder: '-- Pilih Stasi / Kapela --',
                allowClear: true,
                width: '100%'
            });

            $('#select_kub').select2({
                placeholder: '-- Pilih KUB --',
                allowClear: true,
                width: '100%'
            });

            // Cache all KUB options data
            const allKubOptions = [];
            $('#select_kub option').each(function() {
                if ($(this).val()) {
                    allKubOptions.push({
                        value: $(this).val(),
                        text: $(this).text(),
                        wilayah: $(this).data('wilayah') || '',
                        kapela: $(this).data('kapela') || ''
                    });
                }
            });

            let isSyncing = false;

            function filterKubSelect2() {
                const wId = $('#select_wilayah').val();
                const kId = $('#select_kapela').val();
                const currentKubVal = $('#select_kub').val();

                let filtered = allKubOptions;
                if (wId) {
                    filtered = allKubOptions.filter(k => k.wilayah == wId);
                } else if (kId) {
                    filtered = allKubOptions.filter(k => k.kapela == kId);
                }

                // Rebuild KUB options
                let html = '<option value="">-- Pilih KUB --</option>';
                filtered.forEach(k => {
                    const sel = (k.value == currentKubVal) ? 'selected' : '';
                    html += `<option value="${k.value}" data-wilayah="${k.wilayah}" data-kapela="${k.kapela}" ${sel}>${k.text}</option>`;
                });

                $('#select_kub').html(html).trigger('change.select2');
            }

            $('#select_wilayah').on('change', function() {
                if (isSyncing) return;
                const wId = $(this).val();

                if (wId) {
                    // Wilayah dipilih -> Kapela dikosongkan dan dinonaktifkan
                    isSyncing = true;
                    $('#select_kapela').val('').prop('disabled', true).trigger('change.select2');
                    isSyncing = false;
                } else {
                    // Wilayah dihapus -> Kapela diaktifkan kembali
                    $('#select_kapela').prop('disabled', false).trigger('change.select2');
                }

                filterKubSelect2();
            });

            $('#select_kapela').on('change', function() {
                if (isSyncing) return;
                const kId = $(this).val();

                if (kId) {
                    // Kapela dipilih -> Wilayah dikosongkan dan dinonaktifkan
                    isSyncing = true;
                    $('#select_wilayah').val('').prop('disabled', true).trigger('change.select2');
                    isSyncing = false;
                } else {
                    // Kapela dihapus -> Wilayah diaktifkan kembali
                    $('#select_wilayah').prop('disabled', false).trigger('change.select2');
                }

                filterKubSelect2();
            });

            // Initial check if old values exist
            if ($('#select_wilayah').val()) {
                $('#select_kapela').prop('disabled', true).trigger('change.select2');
                filterKubSelect2();
            } else if ($('#select_kapela').val()) {
                $('#select_wilayah').prop('disabled', true).trigger('change.select2');
                filterKubSelect2();
            }
        });

        function toggleRegPassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (!input || !icon) return;
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
