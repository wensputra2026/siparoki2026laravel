<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} - {{ $paroki->nama_paroki ?? 'SIPAROKI' }}</title>

    <!-- Favicon Resmi Profil Paroki -->
    @php
        $rawParokiLogo = $parokiLogo ?? $paroki?->logo ?? $profilParoki?->logo_paroki ?? '';
        $rawKeuskupanLogo = $keuskupanLogo ?? $keuskupan?->logo ?? '';

        $formatLogo = function($path, $default = '') {
            if (!$path) return asset($default);
            if (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])) return $path;
            return asset(ltrim($path, '/'));
        };

        $parokiLogoUrl = $formatLogo($rawParokiLogo, 'uploads/paroki/1787494152_6a8aff08b47a5.webp');
        $keuskupanLogoUrl = $formatLogo($rawKeuskupanLogo, 'uploads/keuskupan/048f46b735f4e047e8f0055bc654ca4f.png');
    @endphp

    <link rel="icon" type="image/jpeg" href="{{ $parokiLogoUrl }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ $parokiLogoUrl }}">
    <link rel="apple-touch-icon" href="{{ $parokiLogoUrl }}">

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            color: #0f172a;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            font-size: 11px;
            background: #f1f5f9;
        }

        .sheet {
            width: min(100% - 32px, 1300px);
            margin: 24px auto;
            background: #fff;
            border: 1px solid #d7dee8;
            box-shadow: 0 18px 45px rgba(15, 23, 42, .10);
            border-radius: 12px;
            overflow: hidden;
        }

        .toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }

        .toolbar .nav-left, .toolbar .nav-right {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .toolbar button {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 8px 14px;
            background: #fff;
            color: #1e293b;
            cursor: pointer;
            font-weight: 700;
            font-size: 12px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s ease;
        }

        .toolbar button:hover {
            background: #f1f5f9;
            border-color: #94a3b8;
        }

        .toolbar .primary {
            border-color: #1b365d;
            background: #1b365d;
            color: #fff;
        }

        .toolbar .primary:hover {
            background: #0f2341;
        }

        /* KOP RESMI GEREJAWI */
        .kop-surat {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px 32px 18px;
            border-bottom: 3px double #0f172a;
            background: #fff;
            gap: 20px;
        }

        .kop-logo {
            width: 72px;
            height: 72px;
            object-fit: contain;
            flex-shrink: 0;
        }

        .kop-center {
            text-align: center;
            flex-grow: 1;
        }

        .kop-keuskupan {
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #475569;
            margin-bottom: 2px;
        }

        .kop-paroki {
            font-size: 18px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .kop-alamat {
            font-size: 10px;
            color: #64748b;
            line-height: 1.4;
        }

        /* REPORT TITLE BANNER */
        .report-banner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 32px;
            background: linear-gradient(135deg, #1b365d 0%, #203864 54%, #2f5597 100%);
            color: #fff;
        }

        .report-banner h1 {
            margin: 0;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.01em;
        }

        .report-banner .subtitle {
            margin-top: 3px;
            font-size: 11px;
            color: #bfdbfe;
        }

        .report-banner .summary-badge {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 8px;
            padding: 8px 14px;
            text-align: right;
            backdrop-blur: 4px;
        }

        .summary-badge .number {
            display: block;
            font-size: 20px;
            font-weight: 900;
            line-height: 1;
        }

        .summary-badge .label {
            font-size: 10px;
            color: #e0ecff;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 32px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10.5px;
            color: #64748b;
        }

        /* TABLE STYLING */
        .content {
            padding: 18px 32px 28px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        th {
            background: #f1f5f9;
            color: #1e293b;
            font-weight: 800;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.03em;
            text-align: left;
            white-space: nowrap;
        }

        td {
            padding: 8px 12px;
            border: 1px solid #e2e8f0;
            color: #334155;
            vertical-align: middle;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        tbody tr:hover {
            background: #f1f5f9;
        }

        td.no {
            text-align: center;
            font-weight: 700;
            color: #64748b;
            background: #f8fafc;
            width: 44px;
        }

        td.empty {
            text-align: center;
            padding: 36px 12px;
            color: #94a3b8;
            font-style: italic;
        }

        tfoot tr {
            background: #f1f5f9;
            border-top: 2px solid #94a3b8;
        }

        tfoot th {
            background: #e2e8f0;
            color: #0f172a;
            font-weight: 900;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            font-size: 11px;
        }

        tfoot th.total-badge {
            background: #0f172a;
            color: #ffffff;
            text-align: center;
        }

        /* FOOTER & SIGNATURE */
        .footer {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding: 16px 32px 28px;
            border-top: 1px solid #e2e8f0;
            font-size: 10px;
            color: #64748b;
            background: #fff;
        }

        .signature {
            text-align: center;
            min-width: 180px;
        }

        .signature .line {
            margin-top: 50px;
            border-top: 1px solid #334155;
            padding-top: 4px;
            font-weight: 700;
            color: #0f172a;
        }

        @media print {
            body {
                background: #fff;
            }
            .sheet {
                width: 100%;
                margin: 0;
                border: none;
                box-shadow: none;
                border-radius: 0;
            }
            .toolbar {
                display: none !important;
            }
            .kop-surat, .report-banner, .content, .footer, .meta-row {
                padding-left: 12px;
                padding-right: 12px;
            }
            .report-banner {
                background: #1b365d !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            th, tfoot th {
                background: #f1f5f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            tfoot th {
                background: #e2e8f0 !important;
            }
            tbody tr:nth-child(even) {
                background: #f8fafc !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            @page {
                size: landscape;
                margin: 10mm;
            }
        }
    </style>
</head>
<body>
    <main class="sheet">
        <!-- Toolbar Navigasi -->
        <div class="toolbar">
            <div class="nav-left">
                <button type="button" onclick="handleGoBack()">
                    <span>&larr; Kembali</span>
                </button>
            </div>
            <div class="nav-right">
                <button type="button" class="primary" onclick="window.print()">
                    <span>&#128438; Cetak / Simpan PDF</span>
                </button>
            </div>
        </div>

        <!-- Kop Dokumen Resmi: Kiri Logo Keuskupan, Kanan Logo Paroki -->
        <header class="kop-surat">
            <img src="{{ $keuskupanLogoUrl }}" alt="Logo Keuskupan" class="kop-logo" onerror="this.style.visibility='hidden'">
            <div class="kop-center">
                <div class="kop-keuskupan">{{ $keuskupan->nama_keuskupan ?? 'KEUSKUPAN AGUNG KUPANG' }}</div>
                <div class="kop-paroki">{{ $paroki->nama_paroki ?? 'PAROKI ST. VINSENSIUS A PAULO BENLUTU' }}</div>
                <div class="kop-alamat">
                    {{ $paroki->alamat ?? 'Jl. Raya Timor No. 12, Benlutu, Batu Putih, Timor Tengah Selatan, NTT' }}<br>
                    Telepon/WA: {{ $paroki->telepon ?? '(0380) 123456' }} &bull; Email: {{ $paroki->email ?? 'sekretariat@parokibenlutu.org' }}
                </div>
            </div>
            <img src="{{ $parokiLogoUrl }}" alt="Logo Paroki" class="kop-logo" onerror="this.style.visibility='hidden'">
        </header>

        @php
            $isStatistik = str_contains(strtolower($title ?? ''), 'statistik') || str_contains(strtolower($title ?? ''), 'demografi');
        @endphp

        <!-- Report Title Banner -->
        <section class="report-banner">
            <div>
                <h1>{{ $title }}</h1>
                <div class="subtitle">Laporan resmi data pastoral dan gerejawi yang diekspor dari sistem informasi paroki.</div>
            </div>
            <div class="summary-badge">
                <span class="number">{{ $summaryNumber ?? number_format($rows->count(), 0, ',', '.') }}</span>
                <span class="label">{{ $summaryLabel ?? ($isStatistik ? 'Total Umat (Jiwa)' : 'Total Record') }}</span>
            </div>
        </section>

        <!-- Meta Bar -->
        <div class="meta-row">
            <span>Dicetak pada: <strong>{{ $printedAt }} WITA</strong></span>
            <span>Sistem: <strong>SIPAROKI &bull; Dokumen Cetak / PDF</strong></span>
        </div>

        <!-- Content Table -->
        <section class="content">
            <table>
                <thead>
                    <tr>
                        <th style="width: 44px; text-align: center;">No</th>
                        @foreach ($headings as $heading)
                            <th>{{ $heading }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php $lastCategory = null; @endphp
                    @forelse ($rows as $index => $row)
                        @php
                            $rowArr = is_array($row) ? $row : (array) $row;
                            $currentCategory = $rowArr[0] ?? '';
                            $isNewCategory = ($currentCategory !== $lastCategory);
                            $lastCategory = $currentCategory;
                        @endphp
                        @if ($isStatistik && $isNewCategory)
                            <tr class="category-header-row" style="background: #e2e8f0; border-top: 2px solid #94a3b8; page-break-after: avoid;">
                                <td colspan="{{ count($headings) + 1 }}" style="font-weight: 900; font-size: 11px; text-transform: uppercase; color: #0f172a; padding: 8px 12px; letter-spacing: 0.04em; background: #e2e8f0;">
                                    &#9656; {{ $currentCategory }}
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td class="no">{{ $index + 1 }}</td>
                            @foreach ($row as $value)
                                <td>{{ $value ?: '-' }}</td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($headings) + 1 }}" class="empty">Belum ada data untuk dicetak.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($rows->count() > 0)
                <tfoot>
                    @if ($isStatistik)
                    <tr class="total-row">
                        <th style="text-align: center; font-weight: 900; background: #0f172a; color: #fff;">&sum;</th>
                        <th style="font-weight: 900; text-transform: uppercase; color: #0f172a; background: #e2e8f0;">
                            TOTAL POPULASI UMAT
                        </th>
                        <th style="font-weight: 800; color: #334155; background: #e2e8f0;">
                            Seluruh Jiwa Terdaftar Paroki
                        </th>
                        <th style="font-weight: 900; color: #0f172a; background: #e2e8f0; font-size: 11.5px;">
                            {{ number_format($totalUmat ?? 5420, 0, ',', '.') }} Jiwa
                        </th>
                        <th style="font-weight: 900; color: #0f172a; background: #e2e8f0;">
                            100%
                        </th>
                        <th style="font-weight: 700; color: #475569; background: #e2e8f0;">
                            Basis Data Sensus Umat Aktif
                        </th>
                    </tr>
                    @else
                    <tr class="total-row">
                        <th style="text-align: center; font-weight: 900; background: #0f172a; color: #fff;">&sum;</th>
                        <th style="font-weight: 900; text-transform: uppercase; color: #0f172a; background: #e2e8f0;">
                            JUMLAH TOTAL KESELURUHAN
                        </th>
                        <th colspan="{{ max(1, count($headings) - 1) }}" style="font-weight: 900; color: #0f172a; background: #e2e8f0; font-size: 11px;">
                            {{ number_format($rows->count(), 0, ',', '.') }} {{ $summaryLabel ?? 'Data Terdaftar' }}
                        </th>
                    </tr>
                    @endif
                </tfoot>
                @endif
            </table>
        </section>

        <!-- Footer & Lembar Pengesahan -->
        <footer class="footer">
            <div>
                Dokumen ini dihasilkan secara otomatis oleh SIPAROKI.<br>
                Harap simpan dan arsipkan sesuai dengan tata tertib administrasi sekretariat paroki.
            </div>
            <div class="signature">
                <div>Dicetak oleh,</div>
                <div class="line">Administrator Paroki</div>
            </div>
        </footer>
    </main>

    <script>
        function handleGoBack() {
            if (window.opener && !window.opener.closed) {
                window.close();
            } else if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = '/superadmin/umat';
            }
        }
    </script>
</body>
</html>
