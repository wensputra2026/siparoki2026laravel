<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil & Biodata Umat - {{ $umat->nama_lengkap ?? $umat->nama_lahir }} - {{ $umat->nik ?? $umat->niu }}</title>

    <!-- Favicon Resmi Profil Paroki -->
    @php
        $keuskupanLogoUrl = !empty($keuskupanLogo)
            ? (\Illuminate\Support\Str::startsWith($keuskupanLogo, ['http://', 'https://']) ? $keuskupanLogo : asset(ltrim($keuskupanLogo, '/')))
            : asset('uploads/keuskupan/048f46b735f4e047e8f0055bc654ca4f.png');

        $parokiLogoUrl = !empty($parokiLogo)
            ? (\Illuminate\Support\Str::startsWith($parokiLogo, ['http://', 'https://']) ? $parokiLogo : asset(ltrim($parokiLogo, '/')))
            : asset('uploads/paroki/1787494152_6a8aff08b47a5.webp');

        // Hitung Usia
        $usiaTahun = null;
        if (!empty($umat->tanggal_lahir)) {
            try {
                $usiaTahun = \Carbon\Carbon::parse($umat->tanggal_lahir)->age;
            } catch (\Exception $e) {
                $usiaTahun = null;
            }
        }

        // Resolusi Foto Default Sesuai Umur & Jenis Kelamin
        $isFemale = in_array(strtolower(trim((string)$umat->jenis_kelamin)), ['perempuan', 'wanita', 'p', 'f', 'female']);
        if (!empty($umat->foto)) {
            $fotoUrl = \Illuminate\Support\Str::startsWith($umat->foto, ['http://', 'https://'])
                ? $umat->foto
                : asset(ltrim($umat->foto, '/'));
        } else {
            if ($usiaTahun !== null && $usiaTahun < 13) {
                $fotoUrl = asset($isFemale ? 'images/anak kecil perempuan.jpg' : 'images/anak kecil laki-laki.jpg');
            } elseif ($usiaTahun !== null && $usiaTahun >= 13 && $usiaTahun <= 24) {
                $fotoUrl = asset($isFemale ? 'images/perempuan muda.jpg' : 'images/laki-laki muda.jpg');
            } else {
                $fotoUrl = asset($isFemale ? 'images/perempuan.jpg' : 'images/laki-laki.jpg');
            }
        }
    @endphp
    <link rel="icon" type="image/jpeg" href="{{ $parokiLogoUrl }}">
    <link rel="shortcut icon" type="image/jpeg" href="{{ $parokiLogoUrl }}">
    <link rel="apple-touch-icon" href="{{ $parokiLogoUrl }}">

    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm 10mm 12mm;
        }
        * {
            box-sizing: border-box;
            font-family: 'Times New Roman', Times, serif;
        }
        body {
            background: #ffffff;
            color: #000000;
            font-size: 11.5px;
            line-height: 1.3;
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
            margin-bottom: 8px;
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
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kop-text h1 {
            margin: 2px 0;
            font-size: 17px;
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
        .title-box .reg-number {
            font-size: 11px;
            font-weight: bold;
            margin-top: 2px;
        }
        .section-header {
            background-color: #f1f5f9;
            border-left: 4px solid #0f172a;
            padding: 3px 8px;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 8px;
            margin-bottom: 4px;
        }
        .grid-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
            font-size: 11px;
        }
        .grid-table td {
            padding: 2.5px 4px;
            vertical-align: top;
        }
        .field-label {
            width: 155px;
            color: #334155;
            font-weight: bold;
        }
        .field-colon {
            width: 10px;
            text-align: center;
            font-weight: bold;
        }
        .field-val {
            color: #0f172a;
        }
        .photo-box {
            width: 110px;
            text-align: center;
            vertical-align: top;
            padding: 4px;
        }
        .photo-box img {
            width: 95px;
            height: 125px;
            object-fit: cover;
            border: 1px solid #000000;
            padding: 2px;
            background: #fff;
        }
        table.sakramen-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 8px;
            font-size: 10.5px;
        }
        table.sakramen-table th, table.sakramen-table td {
            border: 1px solid #000000;
            padding: 4px 6px;
            vertical-align: middle;
        }
        table.sakramen-table th {
            background-color: #f2f2f2;
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
        }
        .ttd-container {
            display: table;
            width: 100%;
            margin-top: 15px;
            page-break-inside: avoid;
        }
        .ttd-box {
            display: table-cell;
            width: 33.3%;
            text-align: center;
            vertical-align: top;
            font-size: 11px;
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
                <span style="font-size: 11px; color: #94a3b8; text-transform: uppercase; font-weight: bold; display: block;">Profil Jiwa Umat:</span>
                <strong style="font-size: 13.5px; color: #f8fafc;">{{ $umat->nama_lengkap ?? $umat->nama_lahir }}</strong> 
                <span style="font-size: 11.5px; color: #cbd5e1;">(NIK: {{ $umat->nik ?? '—' }})</span>
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
                    <option value="Pastor Paroki / Sekretariat,">Pastor Paroki / Sekretariat,</option>
                    <option value="Pastor Rekan / Sekretariat,">Pastor Rekan / Sekretariat,</option>
                    <option value="a.n. Pastor Paroki (Pastor Rekan),">a.n. Pastor Paroki (Pastor Rekan),</option>
                    <option value="Pjs. Pastor Paroki,">Pjs. Pastor Paroki,</option>
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
        <!-- Logo Keuskupan -->
        <div class="kop-logo" style="text-align: left;">
            <img src="{{ $keuskupanLogoUrl }}" alt="Logo Keuskupan">
        </div>
        <!-- Teks Nama Keuskupan & Paroki -->
        <div class="kop-text">
            <h2>{{ strtoupper($keuskupan->nama_keuskupan ?? 'KEUSKUPAN AGUNG KUPANG') }}</h2>
            <h1>{{ strtoupper($paroki->nama_paroki ?? 'PAROKI ST. VINSENSIUS A PAULO BENLUTU') }}</h1>
            <p>{{ $paroki->alamat ?? 'Jl. Timor Raya, Desa Benlutu, Kec. Batu Putih, Kab. Timor Tengah Selatan, NTT' }}</p>
        </div>
        <!-- Logo Paroki -->
        <div class="kop-logo" style="text-align: right;">
            <img src="{{ $parokiLogoUrl }}" alt="Logo Paroki">
        </div>
    </div>

    <!-- JUDUL DOKUMEN -->
    <div class="title-box">
        <h3>PROFIL & BIODATA JIWA UMAT KATOLIK</h3>
        <div class="reg-number">
            NOMOR INDUK UMAT (NIU): {{ $umat->niu ?: ('UMAT-' . str_pad($umat->id, 5, '0', STR_PAD_LEFT)) }} &bull; 
            NIK: {{ $umat->nik ?: '—' }}
        </div>
    </div>

    <!-- DATA UTAMA & FOTO -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 4px;">
        <tr>
            <!-- Kolom Informasi Pribadi -->
            <td style="vertical-align: top; padding-right: 8px;">
                <div class="section-header">I. IDENTITAS PRIBADI UMAT</div>
                <table class="grid-table">
                    <tr>
                        <td class="field-label">Nama Lengkap</td>
                        <td class="field-colon">:</td>
                        <td class="field-val"><strong>{{ strtoupper($umat->nama_lengkap ?? ($umat->nama_baptis . ' ' . $umat->nama_lahir)) }}</strong></td>
                    </tr>
                    <tr>
                        <td class="field-label">Nama Baptis (Pelindung)</td>
                        <td class="field-colon">:</td>
                        <td class="field-val">{{ $umat->nama_baptis ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td class="field-label">Nama Lahir / Marga</td>
                        <td class="field-colon">:</td>
                        <td class="field-val">{{ $umat->nama_lahir ?: '—' }} {{ $umat->nama_marga ? '(' . $umat->nama_marga . ')' : '' }}</td>
                    </tr>
                    <tr>
                        <td class="field-label">Nomor Induk Kependudukan (NIK)</td>
                        <td class="field-colon">:</td>
                        <td class="field-val">{{ $umat->nik ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td class="field-label">Jenis Kelamin</td>
                        <td class="field-colon">:</td>
                        <td class="field-val">{{ $umat->jenis_kelamin ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td class="field-label">Tempat, Tanggal Lahir</td>
                        <td class="field-colon">:</td>
                        <td class="field-val">
                            {{ $umat->tempat_lahir ?: '—' }}, 
                            {{ !empty($umat->tanggal_lahir) ? \Carbon\Carbon::parse($umat->tanggal_lahir)->translatedFormat('d F Y') : '—' }}
                            @if($usiaTahun !== null) (Usia: {{ $usiaTahun }} Tahun) @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="field-label">Golongan Darah / Suku</td>
                        <td class="field-colon">:</td>
                        <td class="field-val">{{ $umat->golongan_darah ?: '—' }} / {{ $umat->suku ?: ($umat->suku_etnis ?: '—') }}</td>
                    </tr>
                    <tr>
                        <td class="field-label">Status Perkawinan Sipil / Gereja</td>
                        <td class="field-colon">:</td>
                        <td class="field-val">
                            {{ $umat->status_menikah ?: ($umat->status_perkawinan ?: 'Belum Menikah') }} 
                            @if(!empty($umat->status_perkawinan_kanonik))
                                (Kanonik: {{ $umat->status_perkawinan_kanonik }})
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="field-label">Pendidikan Terakhir</td>
                        <td class="field-colon">:</td>
                        <td class="field-val">{{ $umat->pendidikan ?: ($umat->pendidikan_saat_ini ?: '—') }}</td>
                    </tr>
                    <tr>
                        <td class="field-label">Pekerjaan</td>
                        <td class="field-colon">:</td>
                        <td class="field-val">{{ $umat->pekerjaan ?: '—' }}</td>
                    </tr>
                    <tr>
                        <td class="field-label">Status Umat / Keaktifan</td>
                        <td class="field-colon">:</td>
                        <td class="field-val">
                            <strong>{{ $umat->status_umat ?: 'Aktif' }}</strong>
                            {{ $umat->status_tinggal ? '• Status Tinggal: ' . $umat->status_tinggal : '' }}
                        </td>
                    </tr>
                </table>
            </td>

            <!-- Kolom Foto Pasfoto -->
            <td class="photo-box">
                <div style="font-size: 10px; font-weight: bold; margin-bottom: 2px;">PASFOTO UMAT</div>
                <img src="{{ $fotoUrl }}" alt="Foto Umat">
                <div style="font-size: 9px; color: #475569; margin-top: 3px;">
                    Ukuran 3 x 4 cm
                </div>
            </td>
        </tr>
    </table>

    <!-- DATA KELUARGA & DOMISILI -->
    <div class="section-header">II. DATA KELUARGA & DOMISILI GEREJAWI</div>
    <table class="grid-table">
        <tr>
            <td class="field-label">Nomor Kartu Keluarga (KK)</td>
            <td class="field-colon">:</td>
            <td class="field-val">
                <strong>{{ $kk->no_kk_kw ?? ($umat->no_kk_kw ?: '—') }}</strong>
                @if(!empty($kk->no_kk_dukcapil)) (No. Dukcapil: {{ $kk->no_kk_dukcapil }}) @endif
            </td>
            <td class="field-label" style="width: 140px;">Komunitas Basis (KUB)</td>
            <td class="field-colon">:</td>
            <td class="field-val"><strong>{{ $namaKub ?: '—' }}</strong></td>
        </tr>
        <tr>
            <td class="field-label">Nama Kepala Keluarga</td>
            <td class="field-colon">:</td>
            <td class="field-val">{{ $kk->nama_lahir_pemilik ?? ($umat->nama_pemilik_kk ?: '—') }}</td>
            <td class="field-label">Wilayah Rohani</td>
            <td class="field-colon">:</td>
            <td class="field-val">{{ $wilayah->nama_wilayah ?? '—' }}</td>
        </tr>
        <tr>
            <td class="field-label">Hubungan dalam Keluarga</td>
            <td class="field-colon">:</td>
            <td class="field-val"><strong>{{ $umat->hubungan_keluarga ?: 'Anggota Keluarga' }}</strong> {{ $umat->anak_ke ? '(Anak Ke-' . $umat->anak_ke . ')' : '' }}</td>
            <td class="field-label">Stasi / Kapela</td>
            <td class="field-colon">:</td>
            <td class="field-val">{{ $kapela->nama_kapela ?? ($paroki->nama_paroki ?? 'Pusat Paroki') }}</td>
        </tr>
        <tr>
            <td class="field-label">Alamat Domisili</td>
            <td class="field-colon">:</td>
            <td class="field-val">{{ $kk->alamat ?? ($umat->alamat ?: 'Desa Benlutu, Kec. Batu Putih, TTS, NTT') }}</td>
            <td class="field-label">Kontak Handphone / WA</td>
            <td class="field-colon">:</td>
            <td class="field-val">{{ $umat->handphone ?: ($kk->telepon_rumah ?: '—') }}</td>
        </tr>
    </table>

    <!-- RIWAYAT PENERIMAAN SAKRAMEN -->
    <div class="section-header">III. CATATAN PENERIMAAN SAKRAMEN & INISIASI GEREJA (LIBER)</div>
    <table class="sakramen-table">
        <thead>
            <tr>
                <th style="width: 20%;">Jenis Sakramen</th>
                <th style="width: 12%;">Status</th>
                <th style="width: 16%;">Tanggal Penerimaan</th>
                <th style="width: 26%;">Tempat / Paroki Penerimaan</th>
                <th style="width: 26%;">Pelayan Sakramen / Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>1. Sakramen Baptis</strong></td>
                <td style="text-align: center;">
                    <strong>{{ !empty($umat->tgl_baptis) || strtolower($umat->status_baptis ?? '') === 'sudah' ? 'SUDAH' : 'BELUM' }}</strong>
                </td>
                <td style="text-align: center;">
                    {{ !empty($umat->tgl_baptis) ? \Carbon\Carbon::parse($umat->tgl_baptis)->translatedFormat('d F Y') : '—' }}
                </td>
                <td>{{ $umat->paroki_baptis ?: ($paroki->nama_paroki ?? 'Paroki St. Vinsensius a Paulo Benlutu') }}</td>
                <td>
                    Pastor: {{ $umat->pastor_baptis ?: '—' }}<br>
                    Wali: {{ $umat->wali_baptis ?: '—' }}
                    @if($umat->buku_baptis_vol || $umat->buku_baptis_no)
                        <br><small>Liber: Vol. {{ $umat->buku_baptis_vol ?: '-' }} Hal. {{ $umat->buku_baptis_hal ?: '-' }} No. {{ $umat->buku_baptis_no ?: '-' }}</small>
                    @endif
                </td>
            </tr>
            <tr>
                <td><strong>2. Ekaristi / Komuni I</strong></td>
                <td style="text-align: center;">
                    <strong>{{ !empty($umat->tgl_komuni_1) ? 'SUDAH' : 'BELUM' }}</strong>
                </td>
                <td style="text-align: center;">
                    {{ !empty($umat->tgl_komuni_1) ? \Carbon\Carbon::parse($umat->tgl_komuni_1)->translatedFormat('d F Y') : '—' }}
                </td>
                <td>{{ $umat->paroki_komuni_1 ?: ($umat->paroki_baptis ?: ($paroki->nama_paroki ?? 'Paroki St. Vinsensius a Paulo Benlutu')) }}</td>
                <td>Penerimaan Sakramen Ekaristi Kudus Pertama</td>
            </tr>
            <tr>
                <td><strong>3. Sakramen Krisma</strong></td>
                <td style="text-align: center;">
                    <strong>{{ !empty($umat->tgl_krisma) ? 'SUDAH' : 'BELUM' }}</strong>
                </td>
                <td style="text-align: center;">
                    {{ !empty($umat->tgl_krisma) ? \Carbon\Carbon::parse($umat->tgl_krisma)->translatedFormat('d F Y') : '—' }}
                </td>
                <td>{{ $umat->paroki_krisma ?: ($paroki->nama_paroki ?? 'Paroki St. Vinsensius a Paulo Benlutu') }}</td>
                <td>Penerimaan Sakramen Penguatan Roh Kudus</td>
            </tr>
            <tr>
                <td><strong>4. Sakramen Perkawinan</strong></td>
                <td style="text-align: center;">
                    <strong>{{ !empty($umat->tgl_perkawinan) ? 'SUDAH' : 'BELUM' }}</strong>
                </td>
                <td style="text-align: center;">
                    {{ !empty($umat->tgl_perkawinan) ? \Carbon\Carbon::parse($umat->tgl_perkawinan)->translatedFormat('d F Y') : '—' }}
                </td>
                <td>{{ $umat->paroki_perkawinan ?: '—' }}</td>
                <td>
                    @if(!empty($umat->nama_pasangan)) Pasangan: {{ $umat->nama_pasangan }} @else — @endif
                </td>
            </tr>
            @if(!empty($umat->status_panggilan) && $umat->status_panggilan !== 'Awam')
            <tr>
                <td><strong>5. Panggilan Khusus</strong></td>
                <td style="text-align: center;"><strong>{{ strtoupper($umat->status_panggilan) }}</strong></td>
                <td style="text-align: center;">
                    {{ !empty($umat->tgl_tahbisan_kaul) ? \Carbon\Carbon::parse($umat->tgl_tahbisan_kaul)->translatedFormat('d F Y') : '—' }}
                </td>
                <td>{{ $umat->tempat_tugas_biara ?: '—' }}</td>
                <td>
                    Ordo: {{ $umat->nama_ordo_kongregasi ?: '—' }} (Tahap: {{ $umat->tahap_panggilan ?: '—' }})
                </td>
            </tr>
            @endif
        </tbody>
    </table>

    <!-- PENGESAHAN & TANDA TANGAN -->
    <div class="ttd-container">
        <div class="ttd-box">
            <div>Mengetahui,</div>
            <div style="margin-top: 2px;">Yang Bersangkutan / Umat,</div>
            <div class="ttd-space"></div>
            <div class="ttd-name">{{ strtoupper($umat->nama_lengkap ?? $umat->nama_lahir) }}</div>
            <div style="font-size: 9.5px; color: #475569;">NIK: {{ $umat->nik ?: '—' }}</div>
        </div>

        <div class="ttd-box">
            <div>Mengetahui,</div>
            <div style="margin-top: 2px;">Ketua Komunitas Umat Basis (KUB),</div>
            <div class="ttd-space"></div>
            <div class="ttd-name">{{ $namaKetuaKub }}</div>
            <div style="font-size: 9.5px; color: #475569;">{{ !empty($namaKub) ? 'KUB ' . $namaKub : 'KUB (Komunitas Basis)' }}</div>
        </div>

        <div class="ttd-box">
            <div>Benlutu, {{ now()->translatedFormat('d F Y') }}</div>
            <div id="ttdPastorHeader" style="margin-top: 2px;">{{ (str_contains(strtolower($jabatanPastor ?? ''), 'paroki') ? 'Pastor Paroki' : ($jabatanPastor ?? 'Pastor Paroki')) }} / Sekretariat,</div>
            <div class="ttd-space"></div>
            <div id="ttdPastorName" class="ttd-name">{{ $namaPastorParoki }}</div>
            <div id="ttdPastorJabatan" style="font-size: 9.5px; color: #475569;">{{ $jabatanPastor ?? 'Pastor Paroki' }}</div>
        </div>
    </div>

    <div style="margin-top: 20px; border-top: 1px dashed #cbd5e1; padding-top: 4px; font-size: 9px; color: #64748b; display: flex; justify-content: space-between;">
        <span>Dicetak melalui Sistem Informasi Paroki (SIPAROKI) &bull; {{ $printedAt }} WITA</span>
        <span>Dokumen Resmi Gereja Katolik &bull; Validasi Kode: {{ strtoupper(substr(md5($umat->id . $umat->nik . 'siparoki'), 0, 10)) }}</span>
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
                
                document.getElementById('ttdPastorName').textContent = customName;
                document.getElementById('ttdPastorJabatan').textContent = finalJabatan;
                
                const headerSelect = document.getElementById('selectTtdHeader');
                if (headerSelect && headerSelect.value === 'auto') {
                    document.getElementById('ttdPastorHeader').textContent = finalJabatan + ' / Sekretariat,';
                }
                return;
            }

            const opt = select.options[select.selectedIndex];
            const name = opt.value;
            const jabatan = opt.getAttribute('data-jabatan') || 'Pastor Rekan';

            document.getElementById('ttdPastorName').textContent = name;
            document.getElementById('ttdPastorJabatan').textContent = jabatan;

            const headerSelect = document.getElementById('selectTtdHeader');
            if (headerSelect && headerSelect.value === 'auto') {
                if (jabatan.toLowerCase().includes('paroki')) {
                    document.getElementById('ttdPastorHeader').textContent = 'Pastor Paroki / Sekretariat,';
                } else {
                    document.getElementById('ttdPastorHeader').textContent = jabatan + ' / Sekretariat,';
                }
            }
        }

        function updateTtdHeader(select) {
            if (select.value === 'auto') {
                const pastorSelect = document.getElementById('selectPastor');
                const opt = pastorSelect.options[pastorSelect.selectedIndex];
                const jabatan = opt.getAttribute('data-jabatan') || 'Pastor Rekan';
                if (jabatan.toLowerCase().includes('paroki')) {
                    document.getElementById('ttdPastorHeader').textContent = 'Pastor Paroki / Sekretariat,';
                } else {
                    document.getElementById('ttdPastorHeader').textContent = jabatan + ' / Sekretariat,';
                }
            } else {
                document.getElementById('ttdPastorHeader').textContent = select.value;
            }
        }

        function handleGoBack() {
            if (window.history.length > 1) {
                window.history.back();
            } else {
                window.close();
            }
        }
    </script>
</body>
</html>
