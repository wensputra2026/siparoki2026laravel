<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Keluarga Katolik - {{ $kk->no_kk_kw }} - {{ $kk->nama_lahir_pemilik }}</title>

    <!-- Favicon Resmi Profil Paroki -->
    @php
        $keuskupanLogoUrl = !empty($keuskupanLogo)
            ? (\Illuminate\Support\Str::startsWith($keuskupanLogo, ['http://', 'https://']) ? $keuskupanLogo : asset($keuskupanLogo))
            : asset('uploads/keuskupan/logo_keuskupan_kupang.svg');

        $parokiLogoUrl = !empty($parokiLogo)
            ? (\Illuminate\Support\Str::startsWith($parokiLogo, ['http://', 'https://']) ? $parokiLogo : asset($parokiLogo))
            : asset('assets/uploads/profil/logo_paroki_1787370466.jpeg');
    @endphp
    <link rel="icon" type="image/jpeg" href="{{ $parokiLogoUrl }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ $parokiLogoUrl }}">
    <link rel="apple-touch-icon" href="{{ $parokiLogoUrl }}">

    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 12mm 10mm 12mm;
        }
        * {
            box-sizing: border-box;
            font-family: 'Times New Roman', Times, serif;
        }
        body {
            background: #ffffff;
            color: #000000;
            font-size: 11px;
            line-height: 1.25;
            margin: 0;
            padding: 0;
        }
        .no-print {
            padding: 10px 15px;
            background: #f1f5f9;
            border-bottom: 1px solid #cbd5e1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            font-family: sans-serif;
        }
        .btn-print {
            background: #0284c7;
            color: #ffffff;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            font-size: 13px;
        }
        .btn-back {
            background: #64748b;
            color: #ffffff;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            text-decoration: none;
            font-size: 13px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                padding: 0;
            }
        }
        .kop-container {
            display: table;
            width: 100%;
            border-bottom: 2.5px double #000000;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .kop-logo {
            display: table-cell;
            width: 75px;
            vertical-align: middle;
            text-align: center;
        }
        .kop-logo img {
            width: 65px;
            height: 65px;
            object-fit: contain;
        }
        .kop-text {
            display: table-cell;
            text-align: center;
            vertical-align: middle;
        }
        .kop-text h2 {
            margin: 0;
            font-size: 15px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kop-text h1 {
            margin: 2px 0;
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .kop-text p {
            margin: 0;
            font-size: 10px;
            font-style: italic;
        }
        .title-box {
            text-align: center;
            margin: 6px 0 10px;
        }
        .title-box h3 {
            margin: 0;
            font-size: 14px;
            text-decoration: underline;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .title-box .kk-number {
            font-size: 12px;
            font-weight: bold;
            margin-top: 2px;
        }
        .identitas-grid {
            display: table;
            width: 100%;
            margin-bottom: 8px;
            font-size: 11px;
        }
        .identitas-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .identitas-row {
            display: table;
            width: 100%;
            margin-bottom: 2px;
        }
        .identitas-label {
            display: table-cell;
            width: 145px;
            font-weight: bold;
        }
        .identitas-colon {
            display: table-cell;
            width: 12px;
            text-align: center;
        }
        .identitas-value {
            display: table-cell;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 9.5px;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #000000;
            padding: 4px 5px;
            vertical-align: middle;
        }
        table.data-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .table-section-title {
            font-size: 11px;
            font-weight: bold;
            margin: 6px 0 3px;
        }
        .ttd-container {
            display: table;
            width: 100%;
            margin-top: 12px;
            page-break-inside: avoid;
        }
        .ttd-box {
            display: table-cell;
            width: 25%;
            text-align: center;
            vertical-align: top;
            font-size: 10.5px;
        }
        .ttd-space {
            height: 50px;
        }
        .ttd-name {
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print">
        <div>
            <strong>Kartu Keluarga Katolik:</strong> {{ $kk->no_kk_kw }} - {{ $kk->nama_lahir_pemilik }}
        </div>
        <div>
            <button onclick="window.print()" class="btn-print">🖨️ Cetak Dokumen / Simpan PDF</button>
            <button type="button" onclick="handleGoBack()" class="btn-back">⬅️ Kembali</button>
        </div>
    </div>

    <!-- KOP GEREJA -->
    <div class="kop-container">
        <!-- Logo Keuskupan (Sebelah Kiri) -->
        <div class="kop-logo" style="text-align: left;">
            <img src="{{ $keuskupanLogoUrl }}" alt="Logo Keuskupan">
        </div>
        <!-- Teks Nama Keuskupan & Paroki (Tengah) -->
        <div class="kop-text">
            <h2>{{ strtoupper($keuskupan->nama_keuskupan ?? 'KEUSKUPAN AGUNG KUPANG') }}</h2>
            <h1>{{ strtoupper($paroki->nama_paroki ?? 'PAROKI ST. VINSENSIUS A PAULO BENLUTU') }}</h1>
            <p>{{ $paroki->alamat ?? 'Jl. Timor Raya, Desa Benlutu, Kec. Batu Putih, Kab. Timor Tengah Selatan, NTT' }}</p>
        </div>
        <!-- Logo Paroki (Sebelah Kanan) -->
        <div class="kop-logo" style="text-align: right;">
            <img src="{{ $parokiLogoUrl }}" alt="Logo Paroki">
        </div>
    </div>

    <!-- JUDUL -->
    <div class="title-box">
        <h3>KARTU KELUARGA (KK) KATOLIK</h3>
        <div class="kk-number">NO. REGISTRASI PAROKI: {{ $kk->no_kk_kw }}</div>
    </div>

    <!-- IDENTITAS KK -->
    <div class="identitas-grid">
        <div class="identitas-col">
            <div class="identitas-row">
                <div class="identitas-label">Nama Kepala Keluarga</div>
                <div class="identitas-colon">:</div>
                <div class="identitas-value"><strong>{{ $kk->nama_lahir_pemilik }}</strong></div>
            </div>
            <div class="identitas-row">
                <div class="identitas-label">No. KK Sipil (Dukcapil)</div>
                <div class="identitas-colon">:</div>
                <div class="identitas-value">{{ $kk->no_kk_dukcapil ?: '-' }}</div>
            </div>
            <div class="identitas-row">
                <div class="identitas-label">Wilayah Pastoral</div>
                <div class="identitas-colon">:</div>
                <div class="identitas-value">{{ $kk->wilayah->nama_wilayah ?? ($kk->kapela->nama_kapela ?? 'Pusat Paroki') }}</div>
            </div>
            <div class="identitas-row">
                <div class="identitas-label">KUB / KBG</div>
                <div class="identitas-colon">:</div>
                <div class="identitas-value">{{ $kk->kub->nama_kub ?? '-' }}</div>
            </div>
        </div>
        <div class="identitas-col">
            <div class="identitas-row">
                <div class="identitas-label">Alamat / RT / RW</div>
                <div class="identitas-colon">:</div>
                <div class="identitas-value">{{ $kk->alamat_sekarang ?: 'Benlutu' }} / RT {{ $kk->rt ?: '000' }} / RW {{ $kk->rw ?: '000' }}</div>
            </div>
            <div class="identitas-row">
                <div class="identitas-label">Desa / Kelurahan</div>
                <div class="identitas-colon">:</div>
                <div class="identitas-value">{{ $kk->desa_kelurahan ?: 'Benlutu' }}</div>
            </div>
            <div class="identitas-row">
                <div class="identitas-label">Kecamatan / Kabupaten</div>
                <div class="identitas-colon">:</div>
                <div class="identitas-value">{{ $kk->kecamatan ?: 'Batu Putih' }} / {{ $kk->kota_kabupaten ?: 'Timor Tengah Selatan' }}</div>
            </div>
            <div class="identitas-row">
                <div class="identitas-label">Status Keluarga / Ekonomi</div>
                <div class="identitas-colon">:</div>
                <div class="identitas-value">{{ $kk->status_kk ?? 'Aktif' }} / {{ $kk->kategori_ekonomi ?? 'Mandiri' }}</div>
            </div>
        </div>
    </div>

    <!-- TABEL I: ANGGOTA KELUARGA & BIODATA -->
    <div class="table-section-title">I. DAFTAR SUSUNAN ANGGOTA KELUARGA</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">No</th>
                <th>Nama Lengkap (Sipil)</th>
                <th>Nama Baptis</th>
                <th style="width: 110px;">NIK Dukcapil</th>
                <th style="width: 90px;">Hub. Keluarga</th>
                <th style="width: 45px;">L/P</th>
                <th style="width: 95px;">Tempat Lahir</th>
                <th style="width: 85px;">Tgl Lahir (Usia)</th>
                <th>Pekerjaan</th>
                <th>Pendidikan</th>
                <th style="width: 35px;">Gol.</th>
                <th>Status Nikah</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kk->anggota as $idx => $a)
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td>
                        <strong>{{ $a->nama_lahir ?: $a->nama_lengkap }}</strong>
                        @if($a->status_panggilan && $a->status_panggilan !== 'Awam')
                            <div style="font-size: 8px; color: #581c87; font-weight: bold;">
                                ✟ {{ $a->status_panggilan }} ({{ $a->nama_ordo_kongregasi ?: 'Biarawan' }})
                            </div>
                        @endif
                    </td>
                    <td>{{ $a->nama_baptis ?: '-' }}</td>
                    <td style="text-align: center; font-family: monospace;">{{ $a->nik ?: '-' }}</td>
                    <td style="text-align: center;">{{ $a->hubungan_keluarga ?: '-' }}</td>
                    <td style="text-align: center;">{{ $a->jenis_kelamin === 'Laki-Laki' ? 'L' : ($a->jenis_kelamin === 'Perempuan' ? 'P' : '-') }}</td>
                    <td>{{ $a->tempat_lahir ?: '-' }}</td>
                    <td style="text-align: center;">
                        @if($a->tanggal_lahir)
                            {{ $a->tanggal_lahir->format('d/m/Y') }}<br>
                            <span style="font-size: 8.5px; color: #475569; font-weight: bold;">({{ $a->usia ?? \Carbon\Carbon::parse($a->tanggal_lahir)->age }} Thn)</span>
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $a->pekerjaan ?: '-' }}</td>
                    <td style="text-align: center;">{{ $a->pendidikan ?: '-' }}</td>
                    <td style="text-align: center;">{{ $a->golongan_darah ?: '-' }}</td>
                    <td style="text-align: center;">{{ $a->status_perkawinan ?: ($a->status_menikah ?: '-') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="text-align: center; font-style: italic;">Belum ada anggota keluarga terdaftar.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- TABEL II: DATA SAKRAMEN & BUKU LIBER -->
    <div class="table-section-title">II. RIWAYAT PENERIMAAN SAKRAMEN INISIASI & PERKAWINAN GEREJAWI (BUKU LIBER)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th rowspan="2" style="width: 25px;">No</th>
                <th rowspan="2">Nama Anggota</th>
                <th colspan="4">Sakramen Permandian / Baptis</th>
                <th colspan="2">Komuni Pertama</th>
                <th colspan="2">Sakramen Krisma</th>
                <th colspan="3">Sakramen Pernikahan</th>
            </tr>
            <tr>
                <th style="width: 65px;">Tgl Baptis</th>
                <th>Tempat / Paroki Baptis</th>
                <th style="width: 65px;">Buku Liber (Vol/Hal/No)</th>
                <th>Wali / Saksi Baptis</th>
                <th style="width: 65px;">Tanggal</th>
                <th>Paroki</th>
                <th style="width: 65px;">Tanggal</th>
                <th>Paroki</th>
                <th style="width: 65px;">Tanggal</th>
                <th>Paroki</th>
                <th>Nama Pasangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kk->anggota as $idx => $a)
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td><strong>{{ $a->nama_baptis ?: $a->nama_lengkap }}</strong></td>
                    <td style="text-align: center;">{{ $a->tgl_baptis ? $a->tgl_baptis->format('d/m/Y') : '-' }}</td>
                    <td>{{ $a->paroki_baptis ?: '-' }}</td>
                    <td style="text-align: center; font-weight: bold;">
                        @if($a->buku_baptis_vol || $a->buku_baptis_hal || $a->buku_baptis_no)
                            {{ $a->buku_baptis_vol ?: '-' }}/{{ $a->buku_baptis_hal ?: '-' }}/{{ $a->buku_baptis_no ?: '-' }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $a->wali_baptis ?: '-' }}</td>
                    <td style="text-align: center;">{{ $a->tgl_komuni_1 ?: '-' }}</td>
                    <td>{{ $a->paroki_komuni_1 ?: '-' }}</td>
                    <td style="text-align: center;">{{ $a->tgl_krisma ?: '-' }}</td>
                    <td>{{ $a->paroki_krisma ?: '-' }}</td>
                    <td style="text-align: center;">{{ $a->tgl_perkawinan ?: '-' }}</td>
                    <td>{{ $a->paroki_perkawinan ?: '-' }}</td>
                    <td>{{ $a->nama_pasangan ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" style="text-align: center; font-style: italic;">Belum ada riwayat sakramen.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- TANDA TANGAN -->
    <div class="ttd-container">
        <div class="ttd-box">
            <div>Mengetahui,</div>
            <div><strong>Ketua KUB / KBG</strong></div>
            <div class="ttd-space"></div>
            <div class="ttd-name">({{ $kk->kub->ketua ?? '.........................................' }})</div>
        </div>
        <div class="ttd-box">
            <div>Mengetahui,</div>
            <div><strong>Ketua Wilayah Pastoral</strong></div>
            <div class="ttd-space"></div>
            <div class="ttd-name">({{ $kk->wilayah->ketua_wilayah ?? '.........................................' }})</div>
        </div>
        <div class="ttd-box">
            <div>Kepala Keluarga,</div>
            <div>&nbsp;</div>
            <div class="ttd-space"></div>
            <div class="ttd-name">({{ $kk->nama_lahir_pemilik }})</div>
        </div>
        <div class="ttd-box">
            <div>Benlutu, {{ now()->translatedFormat('d F Y') }}</div>
            <div><strong>Pastor Paroki / Sekretariat</strong></div>
            <div class="ttd-space"></div>
            <div class="ttd-name">(RD. Herman Hillers Penga)</div>
            <div style="font-size: 9px; margin-top: 2px;">Pastor Paroki St. Vinsensius a Paulo</div>
        </div>
    </div>

    <script>
        function handleGoBack() {
            if (window.opener && !window.opener.closed) {
                window.close();
            } else if (document.referrer && document.referrer.includes(window.location.host)) {
                window.location.href = document.referrer;
            } else if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = "{{ url('/superadmin/kk-katolik/' . $kk->id . '/view') }}";
            }
        }
    </script>
</body>
</html>
