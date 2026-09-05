<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Keluarga Katolik - {{ $kk->no_kk_kw }} - {{ $kk->nama_lahir_pemilik }}</title>

    <!-- Favicon Resmi Profil Paroki -->
    @php
        $keuskupanLogoUrl = !empty($keuskupanLogo)
            ? (\Illuminate\Support\Str::startsWith($keuskupanLogo, ['http://', 'https://']) ? $keuskupanLogo : asset(ltrim($keuskupanLogo, '/')))
            : asset('images/logo-keuskupan.png');

        $parokiLogoUrl = !empty($parokiLogo)
            ? (\Illuminate\Support\Str::startsWith($parokiLogo, ['http://', 'https://']) ? $parokiLogo : asset(ltrim($parokiLogo, '/')))
            : asset('uploads/paroki/1787494152_6a8aff08b47a5.webp');
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
<body>

    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; background: #0f172a; color: #ffffff; padding: 10px 18px; border-radius: 8px; margin-bottom: 14px; gap: 12px; flex-wrap: wrap; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
        <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
            <div>
                <span style="font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: bold; display: block;">Kartu Keluarga Katolik:</span>
                <strong style="font-size: 13.5px; color: #f8fafc;">{{ $kk->nama_lahir_pemilik }}</strong> 
                <span style="font-size: 11.5px; color: #cbd5e1;">(No. KW: {{ $kk->no_kk_kw }})</span>
            </div>

            <!-- Selector Pastor Penandatangan (Solusi bila Pastor Paroki berhalangan) -->
            <div style="background: rgba(255, 255, 255, 0.08); padding: 5px 12px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.15); display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <label for="selectPastor" style="font-size: 12px; font-weight: 600; color: #cbd5e1; white-space: nowrap;">
                    ✍️ Penandatangan Pastor:
                </label>
                <select id="selectPastor" onchange="updatePastorSignature(this)" style="background: #1e293b; color: #ffffff; border: 1px solid #475569; border-radius: 5px; padding: 4px 8px; font-size: 12px; outline: none; cursor: pointer; font-weight: 600;">
                    @php
                        $selectedFound = false;
                    @endphp
                    @if(isset($daftarPastor) && count($daftarPastor) > 0)
                        @foreach($daftarPastor as $p)
                            @php
                                $fullName = $p->nama_pastor;
                                $jabatan = $p->jabatan ?: 'Pastor';
                                $isCurrent = ($fullName === $namaPastorParoki || str_contains($namaPastorParoki, $fullName) || str_contains($fullName, $namaPastorParoki) || str_contains($fullName, $cleanPastorName ?? 'Herman'));
                                if ($isCurrent && !$selectedFound) {
                                    $selectedFound = true;
                                    $isSelected = true;
                                } else {
                                    $isSelected = false;
                                }
                            @endphp
                            <option value="{{ $fullName }}" data-jabatan="{{ $jabatan }}" {{ $isSelected ? 'selected' : '' }}>
                                {{ $fullName }} ({{ $jabatan }})
                            </option>
                        @endforeach
                    @endif
                    @if(!$selectedFound)
                        <option value="{{ $namaPastorParoki }}" data-jabatan="{{ $jabatanPastor ?? 'Pastor Paroki' }}" selected>
                            {{ $namaPastorParoki }} ({{ $jabatanPastor ?? 'Pastor Paroki' }})
                        </option>
                    @endif
                    <option value="custom" data-jabatan="custom">✍️ Tulis Manual Nama / Pastor Lain...</option>
                </select>

                <!-- Format Gelar Tanda Tangan -->
                <select id="selectTtdHeader" onchange="updateTtdHeader(this)" style="background: #1e293b; color: #ffffff; border: 1px solid #475569; border-radius: 5px; padding: 4px 8px; font-size: 12px; outline: none; cursor: pointer;" title="Format Keterangan di Atas Tanda Tangan">
                    <option value="auto">Header: Otomatis sesuai Jabatan</option>
                    <option value="Pastor Paroki / Sekretariat">Pastor Paroki / Sekretariat</option>
                    <option value="Pastor Rekan / Sekretariat">Pastor Rekan / Sekretariat</option>
                    <option value="a.n. Pastor Paroki (Pastor Rekan)">a.n. Pastor Paroki (Pastor Rekan)</option>
                    <option value="Pjs. Pastor Paroki">Pjs. Pastor Paroki</option>
                </select>
            </div>
        </div>

        <div style="display: flex; gap: 8px;">
            <button onclick="window.print()" class="btn-print" style="background: #0284c7; color: #fff; border: none; padding: 7px 16px; border-radius: 6px; font-weight: bold; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
                <span>🖨️ Cetak / Simpan PDF</span>
            </button>
            <button type="button" onclick="handleGoBack()" class="btn-back" style="background: #475569; color: #fff; border: none; padding: 7px 14px; border-radius: 6px; font-weight: bold; cursor: pointer;">
                <span>⬅️ Kembali</span>
            </button>
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
            <div class="ttd-name">({{ $kk->kub->ketua_kub ?? ($kk->kub->ketua ?? '.........................................') }})</div>
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
            <div><strong id="ttdPastorHeader">{{ (str_contains(strtolower($jabatanPastor ?? ''), 'paroki') ? 'Pastor Paroki' : ($jabatanPastor ?? 'Pastor Paroki')) }} / Sekretariat</strong></div>
            <div class="ttd-space"></div>
            <div id="ttdPastorName" class="ttd-name">({{ $namaPastorParoki }})</div>
            <div id="ttdPastorJabatan" style="font-size: 9px; margin-top: 2px;">{{ $jabatanPastor ?? 'Pastor Paroki' }}</div>
        </div>
    </div>

    <script>
        function updatePastorSignature(select) {
            if (select.value === 'custom') {
                const customName = prompt('Masukkan Nama Lengkap Pastor Pengganti (contoh: RD. Patrisius Tampani):', 'RD. Patrisius Tampani');
                if (!customName) {
                    select.selectedIndex = 0;
                    return;
                }
                const customJabatan = prompt('Masukkan Jabatan (contoh: Pastor Rekan / Pjs. Pastor Paroki):', 'Pastor Rekan');
                const finalJabatan = customJabatan || 'Pastor Rekan';
                
                document.getElementById('ttdPastorName').textContent = '(' + customName + ')';
                document.getElementById('ttdPastorJabatan').textContent = finalJabatan;
                
                const headerSelect = document.getElementById('selectTtdHeader');
                if (headerSelect && headerSelect.value === 'auto') {
                    document.getElementById('ttdPastorHeader').textContent = finalJabatan + ' / Sekretariat';
                }
                return;
            }

            const opt = select.options[select.selectedIndex];
            const name = opt.value;
            const jabatan = opt.getAttribute('data-jabatan') || 'Pastor Rekan';

            document.getElementById('ttdPastorName').textContent = '(' + name + ')';
            document.getElementById('ttdPastorJabatan').textContent = jabatan;

            const headerSelect = document.getElementById('selectTtdHeader');
            if (headerSelect && headerSelect.value === 'auto') {
                if (jabatan.toLowerCase().includes('paroki')) {
                    document.getElementById('ttdPastorHeader').textContent = 'Pastor Paroki / Sekretariat';
                } else {
                    document.getElementById('ttdPastorHeader').textContent = jabatan + ' / Sekretariat';
                }
            }
        }

        function updateTtdHeader(select) {
            if (select.value === 'auto') {
                const pastorSelect = document.getElementById('selectPastor');
                const opt = pastorSelect.options[pastorSelect.selectedIndex];
                const jabatan = opt.getAttribute('data-jabatan') || 'Pastor Rekan';
                if (jabatan.toLowerCase().includes('paroki')) {
                    document.getElementById('ttdPastorHeader').textContent = 'Pastor Paroki / Sekretariat';
                } else {
                    document.getElementById('ttdPastorHeader').textContent = jabatan + ' / Sekretariat';
                }
            } else {
                document.getElementById('ttdPastorHeader').textContent = select.value;
            }
        }

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
