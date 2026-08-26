<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SIPAROKI 2026 — Web Installer Wizard</title>
    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Select2 Searchable Dropdown CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        :root {
            --primary: #00897b;
            --primary-dark: #004d40;
            --primary-light: #e0f2f1;
            --amber: #f59e0b;
            --amber-dark: #d97706;
            --slate-900: #0f172a;
            --slate-800: #1e293b;
            --slate-700: #334155;
            --slate-600: #475569;
            --slate-200: #e2e8f0;
            --slate-100: #f1f5f9;
            --slate-50: #f8fafc;
            --danger: #ef4444;
            --success: #10b981;
        }

        /* Custom Select2 Styling for SIPAROKI */
        .select2-container {
            width: 100% !important;
        }
        .select2-container--default .select2-selection--single {
            height: 42px !important;
            padding: 6px 14px !important;
            border-radius: 12px !important;
            border: 1.5px solid var(--slate-200) !important;
            background-color: var(--slate-50) !important;
            font-size: 13.5px !important;
            display: flex !important;
            align-items: center !important;
            transition: all 0.2s ease !important;
        }
        .select2-container--default.select2-container--open .select2-selection--single,
        .select2-container--default .select2-selection--single:focus {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 3px rgba(0, 137, 123, 0.18) !important;
            background-color: #ffffff !important;
            outline: none !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: var(--slate-800) !important;
            font-weight: 600 !important;
            line-height: normal !important;
            padding-left: 0 !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px !important;
            right: 12px !important;
        }
        .select2-dropdown {
            border: 1px solid var(--slate-200) !important;
            border-radius: 14px !important;
            box-shadow: 0 18px 40px rgba(0, 0, 0, 0.16) !important;
            overflow: hidden !important;
            background: #ffffff !important;
            z-index: 9999 !important;
        }
        .select2-search--dropdown {
            padding: 8px 10px !important;
            background: var(--slate-50) !important;
            border-bottom: 1px solid var(--slate-200) !important;
        }
        .select2-search--dropdown .select2-search__field {
            border: 1.5px solid var(--slate-200) !important;
            border-radius: 8px !important;
            padding: 7px 12px !important;
            font-size: 13px !important;
            outline: none !important;
            background: #ffffff !important;
        }
        .select2-search--dropdown .select2-search__field:focus {
            border-color: var(--primary) !important;
        }
        .select2-results__option {
            padding: 9px 14px !important;
            font-size: 13px !important;
            color: var(--slate-800) !important;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: var(--primary) !important;
            color: #ffffff !important;
        }
        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: var(--primary-light) !important;
            color: var(--primary-dark) !important;
            font-weight: 700 !important;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #07111f 0%, #0d2137 50%, #092c3e 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            color: var(--slate-800);
        }

        .installer-container {
            width: 100%;
            max-width: 820px;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(255, 255, 255, 0.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .installer-header {
            background: linear-gradient(135deg, #004d40 0%, #00897b 60%, #0f766e 100%);
            color: #ffffff;
            padding: 32px 36px 28px;
            position: relative;
        }

        .header-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-icon {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #fbbf24;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        .brand-text h1 {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.5px;
            color: #ffffff;
        }

        .brand-text p {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.85);
            margin-top: 2px;
            font-weight: 500;
        }

        .version-badge {
            background: rgba(245, 158, 11, 0.2);
            color: #fef3c7;
            border: 1px solid rgba(245, 158, 11, 0.4);
            font-size: 11px;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Step Wizard Indicators */
        .steps-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: relative;
        }

        .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
            cursor: default;
        }

        .step-circle {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.18);
            border: 2px solid rgba(255, 255, 255, 0.35);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .step-item.active .step-circle {
            background: var(--amber);
            border-color: #ffffff;
            color: #ffffff;
            box-shadow: 0 0 16px rgba(245, 158, 11, 0.7);
            transform: scale(1.1);
        }

        .step-item.completed .step-circle {
            background: #ffffff;
            border-color: #ffffff;
            color: var(--primary);
        }

        .step-label {
            font-size: 11px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.75);
            margin-top: 6px;
            text-align: center;
        }

        .step-item.active .step-label {
            color: #ffffff;
            font-weight: 700;
        }

        .step-line {
            position: absolute;
            top: 19px;
            left: 30px;
            right: 30px;
            height: 2px;
            background: rgba(255, 255, 255, 0.2);
            z-index: 1;
        }

        /* Body & Steps Content */
        .installer-body {
            padding: 36px 36px 32px;
            flex: 1;
        }

        .wizard-step {
            display: none;
            animation: fadeIn 0.3s ease-in-out;
        }

        .wizard-step.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .step-title {
            font-size: 18px;
            font-weight: 800;
            color: var(--slate-900);
            margin-bottom: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .step-desc {
            font-size: 13px;
            color: var(--slate-600);
            margin-bottom: 24px;
            line-height: 1.5;
        }

        /* Checklist Component */
        .check-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-bottom: 24px;
        }

        @media (max-width: 640px) {
            .check-grid { grid-template-columns: 1fr; }
        }

        .check-card {
            background: var(--slate-50);
            border: 1px solid var(--slate-200);
            border-radius: 12px;
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .check-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--slate-800);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .check-status {
            font-size: 12px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
        }

        .check-status.pass {
            background: #dcfce7;
            color: #15803d;
        }

        .check-status.fail {
            background: #fee2e2;
            color: #b91c1c;
        }

        /* Form Controls */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .col-full {
            grid-column: span 2;
        }

        @media (max-width: 640px) {
            .form-grid { grid-template-columns: 1fr; }
            .col-full { grid-column: span 1; }
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .form-group label {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--slate-700);
        }

        .form-group label .req {
            color: var(--danger);
        }

        .form-control {
            width: 100%;
            height: 42px;
            padding: 8px 14px;
            border-radius: 10px;
            border: 1px solid var(--slate-200);
            background: #ffffff;
            font-size: 13.5px;
            color: var(--slate-800);
            outline: none;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(0, 137, 123, 0.15);
        }

        textarea.form-control {
            height: auto;
            min-height: 80px;
            resize: vertical;
        }

        /* Alerts & Notice Boxes */
        .alert-box {
            border-radius: 12px;
            padding: 14px 16px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .alert-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .alert-warning {
            background: #fffbeb;
            border: 1px solid #fde68a;
            color: #92400e;
        }

        .alert-danger {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        /* Actions Footer */
        .installer-footer {
            background: var(--slate-50);
            border-top: 1px solid var(--slate-200);
            padding: 20px 36px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 22px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            outline: none;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-secondary {
            background: #ffffff;
            border: 1px solid var(--slate-200);
            color: var(--slate-700);
        }

        .btn-secondary:hover {
            background: var(--slate-100);
            color: var(--slate-900);
        }

        .btn-primary {
            background: linear-gradient(135deg, #00897b, #004d40);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 137, 123, 0.25);
        }

        .btn-primary:hover {
            opacity: 0.95;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(0, 137, 123, 0.35);
        }

        .btn-amber {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.25);
        }

        .btn-amber:hover {
            opacity: 0.95;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(245, 158, 11, 0.35);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }

        /* Progress Modal / Overlay */
        .install-progress-card {
            background: var(--slate-50);
            border: 1px solid var(--slate-200);
            border-radius: 16px;
            padding: 24px;
            text-align: center;
        }

        .progress-bar-wrap {
            height: 10px;
            background: var(--slate-200);
            border-radius: 20px;
            overflow: hidden;
            margin: 18px 0 12px;
            position: relative;
        }

        .progress-bar-inner {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #00897b, #f59e0b);
            border-radius: 20px;
            transition: width 0.4s ease;
        }

        .spinner {
            display: inline-block;
            width: 28px;
            height: 28px;
            border: 3px solid rgba(0, 137, 123, 0.2);
            border-radius: 50%;
            border-top-color: var(--primary);
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>

<div class="installer-container">
    <!-- Header -->
    <div class="installer-header">
        <div class="header-top">
            <div class="brand">
                <div class="brand-icon">
                    <i class="fa-solid fa-church"></i>
                </div>
                <div class="brand-text">
                    <h1>SIPAROKI 2026</h1>
                    <p>Sistem Informasi &amp; Administrasi Pastoral Terpadu</p>
                </div>
            </div>
            <span class="version-badge">Web Installer</span>
        </div>

        <!-- Stepper Navigation -->
        <div class="steps-nav">
            <div class="step-line"></div>
            
            <div class="step-item active" id="nav-step-1">
                <div class="step-circle">1</div>
                <span class="step-label">Cek Server</span>
            </div>
            <div class="step-item" id="nav-step-2">
                <div class="step-circle">2</div>
                <span class="step-label">Database</span>
            </div>
            <div class="step-item" id="nav-step-3">
                <div class="step-circle">3</div>
                <span class="step-label">Paroki</span>
            </div>
            <div class="step-item" id="nav-step-4">
                <div class="step-circle">4</div>
                <span class="step-label">Super Admin</span>
            </div>
            <div class="step-item" id="nav-step-5">
                <div class="step-circle"><i class="fa-solid fa-check"></i></div>
                <span class="step-label">Selesai</span>
            </div>
        </div>
    </div>

    <!-- Body / Steps Content -->
    <div class="installer-body">
        
        <!-- STEP 1: Pengecekan Persyaratan Server -->
        <div class="wizard-step active" id="step-1">
            <div class="step-title">
                <i class="fa-solid fa-server text-teal-600"></i> Pengecekan Persyaratan Server
            </div>
            <p class="step-desc">Memastikan lingkungan hosting atau server lokal Anda memenuhi spesifikasi minimum untuk menjalankan SIPAROKI 2026.</p>

            @if(!$allPassed)
                <div class="alert-box alert-danger">
                    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                    <div>
                        <strong>Lingkungan server belum memenuhi syarat!</strong>
                        <p class="text-xs mt-1">Harap aktifkan ekstensi PHP yang berstatus merah atau sesuaikan izin tulis folder sebelum melanjutkan.</p>
                    </div>
                </div>
            @else
                <div class="alert-box alert-success">
                    <i class="fa-solid fa-circle-check text-lg"></i>
                    <div>
                        <strong>Semua persyaratan server terpenuhi!</strong>
                        <p class="text-xs mt-1">Server siap digunakan untuk instalasi SIPAROKI 2026.</p>
                    </div>
                </div>
            @endif

            <h4 style="font-size: 13px; font-weight: 800; text-transform: uppercase; color: var(--slate-600); margin-bottom: 10px;">Versi PHP &amp; Ekstensi</h4>
            <div class="check-grid">
                <div class="check-card">
                    <span class="check-name"><i class="fa-brands fa-php text-blue-600"></i> PHP Version (&ge; {{ $requirements['minPhpVersion'] }})</span>
                    <span class="check-status {{ $requirements['phpPassed'] ? 'pass' : 'fail' }}">
                        {{ $requirements['phpVersion'] }} {{ $requirements['phpPassed'] ? '✓' : '✗' }}
                    </span>
                </div>
                @foreach($requirements['extensions'] as $ext => $loaded)
                <div class="check-card">
                    <span class="check-name"><i class="fa-solid fa-puzzle-piece text-slate-400"></i> Ekstensi {{ $ext }}</span>
                    <span class="check-status {{ $loaded ? 'pass' : 'fail' }}">
                        {{ $loaded ? 'Tersedia ✓' : 'Tidak Ada ✗' }}
                    </span>
                </div>
                @endforeach
            </div>

            <h4 style="font-size: 13px; font-weight: 800; text-transform: uppercase; color: var(--slate-600); margin-bottom: 10px;">Izin Tulis Direktori</h4>
            <div class="check-grid">
                @foreach($permissions['permissions'] as $path => $writable)
                <div class="check-card">
                    <span class="check-name"><i class="fa-regular fa-folder-open text-amber-600"></i> {{ $path }}</span>
                    <span class="check-status {{ $writable ? 'pass' : 'fail' }}">
                        {{ $writable ? 'Writable ✓' : 'Not Writable ✗' }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- STEP 2: Konfigurasi Database -->
        <div class="wizard-step" id="step-2">
            <div class="step-title">
                <i class="fa-solid fa-database text-teal-600"></i> Konfigurasi Koneksi Database
            </div>
            <p class="step-desc">Masukkan kredensial database MySQL Anda. Database akan dibuat otomatis jika belum ada.</p>

            <div id="db-test-alert" style="display: none;"></div>

            <div class="form-grid">
                <div class="form-group">
                    <label>DB Host <span class="req">*</span></label>
                    <input type="text" id="db_host" class="form-control" value="{{ $currentEnv['host'] }}" placeholder="127.0.0.1">
                </div>
                <div class="form-group">
                    <label>DB Port <span class="req">*</span></label>
                    <input type="text" id="db_port" class="form-control" value="{{ $currentEnv['port'] }}" placeholder="3306">
                </div>
                <div class="form-group col-full">
                    <label>Nama Database <span class="req">*</span></label>
                    <input type="text" id="db_database" class="form-control" value="{{ $currentEnv['database'] }}" placeholder="siparoki_db">
                </div>
                <div class="form-group">
                    <label>DB Username <span class="req">*</span></label>
                    <input type="text" id="db_username" class="form-control" value="{{ $currentEnv['username'] }}" placeholder="root">
                </div>
                <div class="form-group">
                    <label>DB Password</label>
                    <input type="password" id="db_password" class="form-control" value="{{ $currentEnv['password'] }}" placeholder="Kosongkan jika tanpa password">
                </div>
            </div>

            <div style="margin-top: 20px;">
                <button type="button" class="btn btn-secondary" id="btn-test-db">
                    <i class="fa-solid fa-plug"></i> Uji Koneksi Database
                </button>
            </div>
        </div>

        <!-- STEP 3: Setup Keuskupan & Paroki -->
        <div class="wizard-step" id="step-3">
            <div class="step-title">
                <i class="fa-solid fa-place-of-worship text-teal-600"></i> Identitas Keuskupan &amp; Paroki
            </div>
            <p class="step-desc">Pilih Keuskupan, Dekenat/Kevikepan, dan Paroki Anda dari master data referensi gerejawi nasional KWI.</p>

            <div class="form-grid">
                <div class="form-group col-full">
                    <label>Keuskupan <span class="req">*</span></label>
                    <select id="select_keuskupan" class="form-control">
                        <option value="">-- Pilih Keuskupan (39 Keuskupan KWI) --</option>
                        @foreach($keuskupanList as $k)
                            <option value="{{ $k['id_keuskupan'] }}" data-nama="{{ $k['nama_keuskupan'] }}" {{ ($k['nama_keuskupan'] === 'Keuskupan Agung Kupang' || $k['id_keuskupan'] == 8 || $k['id_keuskupan'] == 5) ? 'selected' : '' }}>
                                {{ $k['nama_keuskupan'] }} (Regio {{ $k['regio'] ?? 'Indonesia' }})
                            </option>
                        @endforeach
                        <option value="custom">+ Keuskupan Lainnya (Ketik Manual)</option>
                    </select>
                </div>

                <div class="form-group col-full" id="custom_keuskupan_wrap" style="display: none;">
                    <label>Nama Keuskupan Baru <span class="req">*</span></label>
                    <input type="text" id="custom_keuskupan" class="form-control" placeholder="Contoh: Keuskupan Agung Kupang">
                </div>

                <div class="form-group col-full">
                    <label>Dekenat / Kevikepan</label>
                    <select id="select_dekenat" class="form-control">
                        <option value="">-- Semua Dekenat / Kevikepan --</option>
                        <option value="custom">+ Dekenat Baru (Ketik Manual)</option>
                    </select>
                </div>

                <div class="form-group col-full" id="custom_dekenat_wrap" style="display: none;">
                    <label>Nama Dekenat / Kevikepan Baru</label>
                    <input type="text" id="custom_dekenat" class="form-control" placeholder="Contoh: Dekenat Timor Tengah Selatan (TTS)">
                </div>

                <div class="form-group col-full">
                    <label>Paroki Terdaftar <span class="req">*</span></label>
                    <select id="select_paroki" class="form-control">
                        <option value="">-- Pilih Paroki Terdaftar --</option>
                        <option value="custom" selected>+ Paroki Baru (Ketik Manual)</option>
                    </select>
                </div>

                <div class="form-group col-full" id="custom_paroki_wrap">
                    <label>Nama Paroki Lengkap <span class="req">*</span></label>
                    <input type="text" id="nama_paroki" class="form-control" value="Paroki St. Vinsensius a Paulo Benlutu" placeholder="Contoh: Paroki St. Vinsensius a Paulo Benlutu">
                </div>

                <div class="form-group col-full">
                    <label>Nama Pastor Paroki Aktif <span class="req">*</span></label>
                    <input type="text" id="pastor_paroki" class="form-control" value="RD. Herman Hilers Penga" placeholder="Contoh: RD. Herman Hilers Penga">
                </div>

                <div class="form-group col-full">
                    <label>Alamat Paroki</label>
                    <textarea id="alamat_paroki" class="form-control" placeholder="Alamat lengkap sekretariat paroki...">Jl. Raya Paroki No. 1, Benlutu, TTS, NTT</textarea>
                </div>
            </div>
        </div>

        <!-- STEP 4: Akun Super Admin Pertama -->
        <div class="wizard-step" id="step-4">
            <div class="step-title">
                <i class="fa-solid fa-user-shield text-teal-600"></i> Akun Super Admin Pertama
            </div>
            <p class="step-desc">Buat akun administrator utama dengan hak akses penuh ke seluruh modul sistem.</p>

            <div class="form-grid">
                <div class="form-group col-full">
                    <label>Nama Lengkap Administrator <span class="req">*</span></label>
                    <input type="text" id="admin_name" class="form-control" value="Super Administrator" placeholder="Nama Lengkap">
                </div>
                <div class="form-group">
                    <label>Username <span class="req">*</span></label>
                    <input type="text" id="admin_username" class="form-control" value="superadmin" placeholder="superadmin">
                </div>
                <div class="form-group">
                    <label>Email Admin <span class="req">*</span></label>
                    <input type="email" id="admin_email" class="form-control" value="admin@siparoki.id" placeholder="admin@siparoki.id">
                </div>
                <div class="form-group">
                    <label>Password <span class="req">*</span></label>
                    <input type="password" id="admin_password" class="form-control" value="password123" placeholder="Minimal 6 karakter">
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password <span class="req">*</span></label>
                    <input type="password" id="admin_password_confirm" class="form-control" value="password123" placeholder="Ulangi password">
                </div>
            </div>
        </div>

        <!-- STEP 5: Proses Instalasi & Selesai -->
        <div class="wizard-step" id="step-5">
            <div id="install-processing">
                <div class="install-progress-card">
                    <div class="spinner" id="install-spinner"></div>
                    <h3 id="install-status-text" style="font-size: 16px; font-weight: 800; color: var(--slate-900); margin-top: 14px;">Memproses Instalasi Sistem...</h3>
                    <p id="install-substatus" style="font-size: 12.5px; color: var(--slate-600); margin-top: 4px;">Menyiapkan konfigurasi .env, database migrasi, dan master referensi.</p>

                    <div class="progress-bar-wrap">
                        <div class="progress-bar-inner" id="install-progress-bar"></div>
                    </div>
                </div>
            </div>

            <div id="install-success" style="display: none; text-align: center; padding: 20px 0;">
                <div style="width: 64px; height: 64px; background: #dcfce7; color: #16a34a; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 32px; margin-bottom: 16px;">
                    <i class="fa-solid fa-check"></i>
                </div>
                <h2 style="font-size: 22px; font-weight: 800; color: var(--slate-900); margin-bottom: 8px;">Selamat, SIPAROKI 2026 Berhasil Terpasang!</h2>
                <p style="font-size: 13.5px; color: var(--slate-600); max-width: 540px; margin: 0 auto 20px;">
                    Aplikasi paroki telah siap digunakan. Seluruh konfigurasi, master data referensi, dan akun Super Administrator Anda telah aktif.
                </p>

                <div class="alert-box alert-success" style="text-align: left; max-width: 520px; margin: 0 auto 16px;">
                    <i class="fa-solid fa-circle-check text-lg"></i>
                    <div>
                        <strong>Akun Super Admin Aktif:</strong>
                        <p class="text-xs mt-1">Email: <strong id="succ-admin-email" class="font-mono"></strong> &bull; Password: <span class="text-slate-500">(Sesuai yang Anda tentukan)</span></p>
                    </div>
                </div>

                <div class="alert-box alert-warning" style="text-align: left; max-width: 520px; margin: 0 auto 24px;">
                    <i class="fa-solid fa-shield-halved text-lg"></i>
                    <div>
                        <strong>Keamanan Installer:</strong>
                        <p class="text-xs mt-1">File installer telah dikunci otomatis (<code style="background: rgba(0,0,0,0.06); padding: 1px 4px; border-radius: 4px;">storage/installed</code>) demi melindungi sistem Anda.</p>
                        <p id="countdown-text" class="text-xs text-amber-800 font-bold mt-2"><i class="fa-solid fa-clock"></i> Mengarahkan ke halaman login dalam <span id="countdown-sec">6</span> detik...</p>
                    </div>
                </div>

                <div style="display: flex; justify-content: center; gap: 12px;">
                    <a id="btn-login-success" href="{{ url('/login') }}" class="btn btn-primary" style="padding: 12px 30px; font-size: 14px;">
                        <i class="fa-solid fa-right-to-bracket"></i> Masuk ke Panel SIPAROKI
                    </a>
                    <a id="btn-home-success" href="{{ url('/') }}" class="btn btn-secondary" style="padding: 12px 24px; font-size: 14px;">
                        <i class="fa-solid fa-globe"></i> Lihat Website Publik
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- Actions Footer -->
    <div class="installer-footer">
        <button type="button" class="btn btn-secondary" id="btn-prev" style="visibility: hidden;">
            <i class="fa-solid fa-arrow-left"></i> Kembali
        </button>

        <div style="display: flex; gap: 10px;">
            <button type="button" class="btn btn-primary" id="btn-next" {{ $allPassed ? '' : 'disabled' }}>
                Lanjut <i class="fa-solid fa-arrow-right"></i>
            </button>
            <button type="button" class="btn btn-amber" id="btn-install" style="display: none;">
                <i class="fa-solid fa-rocket"></i> Mulai Instalasi Sekarang
            </button>
        </div>
    </div>
</div>

<!-- jQuery & Select2 JS -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    let currentStep = 1;
    const totalSteps = 5;
    const allPassed = {{ $allPassed ? 'true' : 'false' }};

    const rawDekenatList = @json($dekenatList);
    const rawParokiList = @json($parokiList);

    function updateStepView(step) {
        // Hide all steps
        document.querySelectorAll('.wizard-step').forEach(el => el.classList.remove('active'));
        document.getElementById(`step-1`).classList.toggle('active', step === 1);
        document.getElementById(`step-2`).classList.toggle('active', step === 2);
        document.getElementById(`step-3`).classList.toggle('active', step === 3);
        document.getElementById(`step-4`).classList.toggle('active', step === 4);
        document.getElementById(`step-5`).classList.toggle('active', step === 5);

        // Update nav stepper
        for (let i = 1; i <= totalSteps; i++) {
            const navEl = document.getElementById(`nav-step-${i}`);
            if (!navEl) continue;
            navEl.classList.remove('active', 'completed');
            if (i < step) {
                navEl.classList.add('completed');
            } else if (i === step) {
                navEl.classList.add('active');
            }
        }

        // Manage Footer Buttons
        const prevBtn = document.getElementById('btn-prev');
        const nextBtn = document.getElementById('btn-next');
        const installBtn = document.getElementById('btn-install');

        prevBtn.style.visibility = (step > 1 && step < 5) ? 'visible' : 'hidden';

        if (step === 4) {
            nextBtn.style.display = 'none';
            installBtn.style.display = 'inline-flex';
        } else if (step === 5) {
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
            installBtn.style.display = 'none';
        } else {
            nextBtn.style.display = 'inline-flex';
            installBtn.style.display = 'none';
        }
    }

    // Step Navigation Buttons
    document.getElementById('btn-next').addEventListener('click', () => {
        if (currentStep === 1 && !allPassed) {
            alert('Persyaratan server belum terpenuhi!');
            return;
        }
        if (currentStep < totalSteps) {
            currentStep++;
            updateStepView(currentStep);
        }
    });

    document.getElementById('btn-prev').addEventListener('click', () => {
        if (currentStep > 1) {
            currentStep--;
            updateStepView(currentStep);
        }
    });

    // Test Database Connection via AJAX
    document.getElementById('btn-test-db').addEventListener('click', async function() {
        const btn = this;
        const alertBox = document.getElementById('db-test-alert');
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Menguji koneksi...';

        try {
            const response = await fetch('{{ route('installer.test-db') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({
                    host: document.getElementById('db_host').value,
                    port: document.getElementById('db_port').value,
                    database: document.getElementById('db_database').value,
                    username: document.getElementById('db_username').value,
                    password: document.getElementById('db_password').value,
                })
            });

            const data = await response.json();
            alertBox.style.display = 'block';

            if (data.success) {
                alertBox.className = 'alert-box alert-success';
                alertBox.innerHTML = `<i class="fa-solid fa-circle-check text-lg"></i> <div><strong>Koneksi Berhasil!</strong><p class="text-xs mt-1">${data.message}</p></div>`;
            } else {
                alertBox.className = 'alert-box alert-danger';
                alertBox.innerHTML = `<i class="fa-solid fa-triangle-exclamation text-lg"></i> <div><strong>Koneksi Gagal!</strong><p class="text-xs mt-1">${data.message || 'Periksa kembali host, port, database, dan kredensial.'}</p></div>`;
            }
        } catch (e) {
            alertBox.style.display = 'block';
            alertBox.className = 'alert-box alert-danger';
            alertBox.innerHTML = `<i class="fa-solid fa-triangle-exclamation text-lg"></i> <div><strong>Kesalahan:</strong><p class="text-xs mt-1">${e.message}</p></div>`;
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });

    // Handle 3-Tier Cascading Selection (Keuskupan -> Dekenat -> Paroki) with Select2
    const $selectKeuskupan = $('#select_keuskupan');
    const $selectDekenat = $('#select_dekenat');
    const $selectParoki = $('#select_paroki');
    const customKeuskupanWrap = document.getElementById('custom_keuskupan_wrap');
    const customDekenatWrap = document.getElementById('custom_dekenat_wrap');
    const customParokiWrap = document.getElementById('custom_paroki_wrap');
    const inputNamaParoki = document.getElementById('nama_paroki');
    const inputAlamatParoki = document.getElementById('alamat_paroki');

    // Initialize Select2 on dropdowns
    $selectKeuskupan.select2({
        placeholder: '-- Pilih Keuskupan (Cari 39 Keuskupan KWI) --',
        width: '100%'
    });

    $selectDekenat.select2({
        placeholder: '-- Semua Dekenat / Kevikepan --',
        width: '100%'
    });

    $selectParoki.select2({
        placeholder: '-- Pilih Paroki Terdaftar --',
        width: '100%'
    });

    function populateDekenatOptions(keuskupanId) {
        let html = '<option value="">-- Semua Dekenat / Kevikepan --</option><option value="custom">+ Dekenat Baru (Ketik Manual)</option>';
        if (keuskupanId && keuskupanId !== 'custom') {
            const filtered = rawDekenatList.filter(d => parseInt(d.keuskupan_id) === parseInt(keuskupanId));
            filtered.forEach(d => {
                html += `<option value="${d.id}">${d.nama_dekenat || d.nama_kevikepan}</option>`;
            });
        }
        $selectDekenat.html(html).trigger('change.select2');
    }

    function populateParokiOptions(keuskupanId, dekenatId) {
        let html = '<option value="">-- Pilih Paroki Terdaftar --</option><option value="custom" selected>+ Paroki Baru (Ketik Manual)</option>';
        if (keuskupanId && keuskupanId !== 'custom') {
            let filtered = rawParokiList.filter(p => parseInt(p.keuskupan_id) === parseInt(keuskupanId));
            if (dekenatId && dekenatId !== 'custom') {
                filtered = filtered.filter(p => parseInt(p.dekenat_id) === parseInt(dekenatId));
            }
            filtered.forEach(p => {
                html += `<option value="${p.id_paroki}" data-alamat="${p.alamat || ''}">${p.nama_paroki}</option>`;
            });
        }
        $selectParoki.html(html).trigger('change.select2');
    }

    $selectKeuskupan.on('change', function() {
        const kId = $(this).val();
        if (kId === 'custom') {
            customKeuskupanWrap.style.display = 'block';
            populateDekenatOptions(null);
            populateParokiOptions(null, null);
        } else {
            customKeuskupanWrap.style.display = 'none';
            populateDekenatOptions(kId);
            populateParokiOptions(kId, $selectDekenat.val());
        }
    });

    $selectDekenat.on('change', function() {
        const dId = $(this).val();
        if (dId === 'custom') {
            customDekenatWrap.style.display = 'block';
        } else {
            customDekenatWrap.style.display = 'none';
        }
        populateParokiOptions($selectKeuskupan.val(), dId);
    });

    $selectParoki.on('change', function() {
        const val = $(this).val();
        if (val === 'custom') {
            customParokiWrap.style.display = 'block';
            inputNamaParoki.value = '';
            inputNamaParoki.focus();
        } else if (val) {
            customParokiWrap.style.display = 'block';
            const selectedOpt = this.options[this.selectedIndex];
            if (selectedOpt) {
                inputNamaParoki.value = selectedOpt.textContent;
                if (selectedOpt.dataset.alamat) {
                    inputAlamatParoki.value = selectedOpt.dataset.alamat;
                }
            }
        }
    });

    // Initial load for default keuskupan
    const initialKeuskupan = $selectKeuskupan.val();
    if (initialKeuskupan && initialKeuskupan !== 'custom') {
        populateDekenatOptions(initialKeuskupan);
        populateParokiOptions(initialKeuskupan, $selectDekenat.val());
    }

    // Submit Installation Execution
    document.getElementById('btn-install').addEventListener('click', async function() {
        // Validation
        const adminPass = document.getElementById('admin_password').value;
        const adminPassConfirm = document.getElementById('admin_password_confirm').value;

        if (!adminPass || adminPass.length < 6) {
            alert('Password Super Admin minimal 6 karakter!');
            return;
        }
        if (adminPass !== adminPassConfirm) {
            alert('Konfirmasi password tidak cocok!');
            return;
        }

        let namaKeuskupan = '';
        if (selectKeuskupan.value === 'custom') {
            namaKeuskupan = document.getElementById('custom_keuskupan').value.trim();
        } else if (selectKeuskupan.selectedIndex >= 0) {
            namaKeuskupan = selectKeuskupan.options[selectKeuskupan.selectedIndex].dataset.nama || selectKeuskupan.options[selectKeuskupan.selectedIndex].textContent.trim();
        }

        let namaDekenat = '';
        if (selectDekenat.value === 'custom') {
            namaDekenat = document.getElementById('custom_dekenat').value.trim();
        } else if (selectDekenat.selectedIndex > 0) {
            namaDekenat = selectDekenat.options[selectDekenat.selectedIndex].textContent.trim();
        }

        const payload = {
            db_host: document.getElementById('db_host').value.trim(),
            db_port: document.getElementById('db_port').value.trim(),
            db_database: document.getElementById('db_database').value.trim(),
            db_username: document.getElementById('db_username').value.trim(),
            db_password: document.getElementById('db_password').value,
            keuskupan_id: selectKeuskupan.value,
            nama_keuskupan: namaKeuskupan,
            dekenat_id: selectDekenat.value,
            nama_dekenat: namaDekenat,
            paroki_id: selectParoki.value,
            nama_paroki: document.getElementById('nama_paroki').value.trim(),
            pastor_paroki: document.getElementById('pastor_paroki').value.trim(),
            alamat_paroki: document.getElementById('alamat_paroki').value.trim(),
            admin_name: document.getElementById('admin_name').value.trim(),
            admin_username: document.getElementById('admin_username').value.trim(),
            admin_email: document.getElementById('admin_email').value.trim(),
            admin_password: adminPass,
        };

        if (!payload.nama_keuskupan || !payload.nama_paroki || !payload.pastor_paroki) {
            alert('Harap lengkapi nama Keuskupan, Paroki, dan Pastor Paroki!');
            return;
        }

        // Move to Step 5
        currentStep = 5;
        updateStepView(5);

        const progressBar = document.getElementById('install-progress-bar');
        const statusText = document.getElementById('install-status-text');
        const substatus = document.getElementById('install-substatus');

        progressBar.style.width = '25%';
        statusText.textContent = 'Menghubungkan ke Database...';

        setTimeout(() => {
            progressBar.style.width = '55%';
            statusText.textContent = 'Menjalankan Migrasi & Import Master Data...';
        }, 1200);

        try {
            const response = await fetch('{{ route('installer.process') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (data.success) {
                progressBar.style.width = '100%';
                statusText.textContent = 'Instalasi Selesai!';
                setTimeout(() => {
                    document.getElementById('install-processing').style.display = 'none';
                    document.getElementById('install-success').style.display = 'block';

                    const loginUrl = data.login_url || data.redirect || '{{ url("/login") }}';
                    const homeUrl = data.home_url || '{{ url("/") }}';
                    
                    document.getElementById('btn-login-success').href = loginUrl;
                    document.getElementById('btn-home-success').href = homeUrl;
                    document.getElementById('succ-admin-email').textContent = data.admin_email || payload.admin_email;

                    let sec = 6;
                    const timer = setInterval(() => {
                        sec--;
                        const el = document.getElementById('countdown-sec');
                        if (el) el.textContent = sec;
                        if (sec <= 0) {
                            clearInterval(timer);
                            window.location.href = loginUrl;
                        }
                    }, 1000);
                }, 800);
            } else {
                throw new Error(data.message || 'Gagal memproses instalasi.');
            }
        } catch (err) {
            progressBar.style.background = '#ef4444';
            statusText.textContent = 'Instalasi Mengalami Kesalahan';
            statusText.style.color = '#dc2626';
            substatus.textContent = err.message;
            document.getElementById('install-spinner').style.display = 'none';
        }
    });
</script>

</body>
</html>
