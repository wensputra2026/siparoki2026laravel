<?php

namespace App\Http\Controllers\Concerns;

use App\Models\DesaKelurahan;
use App\Models\Dekenat;
use App\Models\Kabupaten;
use App\Models\Kapela;
use App\Models\Kecamatan;
use App\Models\Keuskupan;
use App\Models\Kevikepan;
use App\Models\KkKatolik;
use App\Models\Kub;
use App\Models\Lingkungan;
use App\Models\Paroki;
use App\Models\Provinsi;
use App\Models\Sakramen;
use App\Models\Umat;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

trait KkModuleTrait
{
    public function createKk(Request $request): Response
    {
        $this->ensureKkKatolikColumns();
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? 'superadmin';
        $roleMap = [
            'superadmin' => 'Super Admin',
            'paroki' => 'Admin Paroki',
            'pastor' => 'Pastor',
            'wilayah' => 'Admin Wilayah',
            'kapela' => 'Admin Kapela / Stasi',
            'kub' => 'Ketua KUB',
            'bendahara' => 'Bendahara',
            'penulis' => 'Penulis',
            'umat' => 'Umat',
        ];
        $resolvedRole = $roleMap[$firstSegment] ?? auth()->user()?->role?->nama_role ?? 'Super Admin';

        $defaultParokiId = $this->defaultParokiIdFromProfile();
        $defaultParoki = Paroki::with(['keuskupan', 'provinsi', 'kabupaten', 'kecamatan', 'desa'])->find($defaultParokiId)
            ?? Paroki::with(['keuskupan', 'provinsi', 'kabupaten', 'kecamatan', 'desa'])->first();
        $civilRegion = $this->resolveKkCivilRegion(null, $defaultParoki);

        $rawKeuskupanCode = $defaultParoki?->keuskupan?->kode_keuskupan ?? '012';
        $numericKeuskupanCode = preg_replace('/[^0-9]/', '', (string)$rawKeuskupanCode);
        $kodeKeuskupan = str_pad(substr($numericKeuskupanCode ?: '012', 0, 3), 3, '0', STR_PAD_LEFT);

        $rawParokiCode = $defaultParoki?->kode_paroki ?? '001';
        $numericParokiCode = preg_replace('/[^0-9]/', '', (string)$rawParokiCode);
        $kodeParoki = str_pad(substr($numericParokiCode ?: '001', 0, 3), 3, '0', STR_PAD_LEFT);

        $nextSeq = str_pad((\App\Models\KkKatolik::count() + 1), 3, '0', STR_PAD_LEFT);
        $defaultNoKk = 'K' . $kodeKeuskupan . $kodeParoki . $nextSeq;

        return Inertia::render('Inertia/KkForm', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
            'isEdit' => false,
            'kkItem' => null,
            'defaultNoKk' => $defaultNoKk,
            'kodeKeuskupan' => $kodeKeuskupan,
            'kodeParoki' => $kodeParoki,
            'nextKkNumber' => $nextSeq,
            'namaParoki' => $defaultParoki?->nama_paroki ?? 'Paroki St. Vinsensius a Paulo Benlutu',
            'namaKeuskupan' => $defaultParoki?->keuskupan?->nama_keuskupan ?? 'Keuskupan Agung Kupang',
            'wilayahList' => \App\Models\Wilayah::orderBy('nama_wilayah')->get(['id', 'nama_wilayah']),
            'kubList' => \App\Models\Kub::orderBy('nama_kub')->get(['id', 'nama_kub', 'wilayah_id']),
            'kapelaList' => \App\Models\Kapela::orderBy('nama_kapela')->get(['id', 'nama_kapela']),
            'pastorList' => \App\Models\Pastor::orderBy('nama_pastor')->get(['id', 'nama_pastor', 'jabatan']),
            'parokiList' => \App\Models\Paroki::orderBy('nama_paroki')->get(['id_paroki', 'nama_paroki']),
            'provinsiList' => \App\Models\Provinsi::orderBy('nama_provinsi')->get(['id_provinsi', 'nama_provinsi']),
            'kabupatenList' => \App\Models\Kabupaten::orderBy('nama_kabupaten')->get(['id_kabupaten', 'provinsi_id', 'nama_kabupaten']),
            'kecamatanList' => \App\Models\Kecamatan::orderBy('nama_kecamatan')->get(['id_kecamatan', 'kabupaten_id', 'nama_kecamatan']),
            'desaList' => $this->desaOptionsForKecamatan($civilRegion['kecamatan_id'] ?? null),
            'civilRegion' => $civilRegion,
        ]);
    }


    public function editKk(Request $request, string|int $id): Response
    {
        $this->ensureKkKatolikColumns();
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? 'superadmin';
        $roleMap = [
            'superadmin' => 'Super Admin',
            'paroki' => 'Admin Paroki',
            'pastor' => 'Pastor',
            'wilayah' => 'Admin Wilayah',
            'kapela' => 'Admin Kapela / Stasi',
            'kub' => 'Ketua KUB',
            'bendahara' => 'Bendahara',
            'penulis' => 'Penulis',
            'umat' => 'Umat',
        ];
        $resolvedRole = $roleMap[$firstSegment] ?? auth()->user()?->role?->nama_role ?? 'Super Admin';

        $decodedId = decode_id($id) ?: $id;
        $kkItem = is_numeric($decodedId)
            ? \App\Models\KkKatolik::with('anggota')->findOrFail($decodedId)
            : \App\Models\KkKatolik::with('anggota')->where('no_kk_kw', $id)->firstOrFail();
        $kkItem->hashid = encode_id($kkItem->id);
        $kkItem->iid = $kkItem->hashid;

        $defaultParokiId = $this->defaultParokiIdFromProfile();
        $defaultParoki = Paroki::with(['keuskupan', 'provinsi', 'kabupaten', 'kecamatan', 'desa'])->find($defaultParokiId)
            ?? Paroki::with(['keuskupan', 'provinsi', 'kabupaten', 'kecamatan', 'desa'])->first();
        $civilRegion = $this->resolveKkCivilRegion($kkItem, $defaultParoki);

        $rawKeuskupanCode = $defaultParoki?->keuskupan?->kode_keuskupan ?? '012';
        $numericKeuskupanCode = preg_replace('/[^0-9]/', '', (string)$rawKeuskupanCode);
        $kodeKeuskupan = str_pad(substr($numericKeuskupanCode ?: '012', 0, 3), 3, '0', STR_PAD_LEFT);

        $rawParokiCode = $defaultParoki?->kode_paroki ?? '001';
        $numericParokiCode = preg_replace('/[^0-9]/', '', (string)$rawParokiCode);
        $kodeParoki = str_pad(substr($numericParokiCode ?: '001', 0, 3), 3, '0', STR_PAD_LEFT);

        $nextSeq = str_pad((\App\Models\KkKatolik::count() + 1), 3, '0', STR_PAD_LEFT);
        $defaultNoKk = $kkItem->no_kk_kw ?: ('K' . $kodeKeuskupan . $kodeParoki . $nextSeq);

        return Inertia::render('Inertia/KkForm', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
            'isEdit' => true,
            'kkItem' => $kkItem,
            'defaultNoKk' => $defaultNoKk,
            'kodeKeuskupan' => $kodeKeuskupan,
            'kodeParoki' => $kodeParoki,
            'nextKkNumber' => $nextSeq,
            'namaParoki' => $defaultParoki?->nama_paroki ?? 'Paroki St. Vinsensius a Paulo Benlutu',
            'namaKeuskupan' => $defaultParoki?->keuskupan?->nama_keuskupan ?? 'Keuskupan Agung Kupang',
            'wilayahList' => \App\Models\Wilayah::orderBy('nama_wilayah')->get(['id', 'nama_wilayah']),
            'kubList' => \App\Models\Kub::orderBy('nama_kub')->get(['id', 'nama_kub', 'wilayah_id']),
            'kapelaList' => \App\Models\Kapela::orderBy('nama_kapela')->get(['id', 'nama_kapela']),
            'pastorList' => \App\Models\Pastor::orderBy('nama_pastor')->get(['id', 'nama_pastor', 'jabatan']),
            'parokiList' => \App\Models\Paroki::orderBy('nama_paroki')->get(['id_paroki', 'nama_paroki']),
            'provinsiList' => \App\Models\Provinsi::orderBy('nama_provinsi')->get(['id_provinsi', 'nama_provinsi']),
            'kabupatenList' => \App\Models\Kabupaten::orderBy('nama_kabupaten')->get(['id_kabupaten', 'provinsi_id', 'nama_kabupaten']),
            'kecamatanList' => \App\Models\Kecamatan::orderBy('nama_kecamatan')->get(['id_kecamatan', 'kabupaten_id', 'nama_kecamatan']),
            'desaList' => $this->desaOptionsForKecamatan($civilRegion['kecamatan_id'] ?? null),
            'civilRegion' => $civilRegion,
        ]);
    }


    public function viewKk(Request $request, string|int $id)
    {
        $firstSegment = explode('/', trim($request->path(), '/'))[0] ?? 'superadmin';
        $roleMap = [
            'superadmin' => 'Super Admin',
            'paroki' => 'Admin Paroki',
            'pastor' => 'Pastor',
            'wilayah' => 'Admin Wilayah',
            'kapela' => 'Admin Kapela / Stasi',
            'kub' => 'Ketua KUB',
            'bendahara' => 'Bendahara',
            'penulis' => 'Penulis',
            'umat' => 'Umat',
        ];
        $resolvedRole = $roleMap[$firstSegment] ?? auth()->user()?->role?->nama_role ?? 'Super Admin';

        $decodedId = decode_id($id) ?: $id;
        $kk = is_numeric($decodedId)
            ? \App\Models\KkKatolik::with(['anggota', 'wilayah', 'kapela', 'kub'])->find($decodedId)
            : \App\Models\KkKatolik::with(['anggota', 'wilayah', 'kapela', 'kub'])->where('no_kk_kw', $id)->first();

        if (!$kk) {
            $kk = \App\Models\KkKatolik::with(['anggota', 'wilayah', 'kapela', 'kub'])->firstOrFail();
        }
        $kk->hashid = encode_id($kk->id);
        $kk->iid = $kk->hashid;

        $defaultParokiId = $this->defaultParokiIdFromProfile();
        $defaultParoki = Paroki::with('keuskupan')->find($defaultParokiId)
            ?? Paroki::with('keuskupan')->first();

        return Inertia::render('Inertia/KkDetail', [
            'role' => $resolvedRole,
            'prefix' => $firstSegment,
            'kk' => $kk,
            'paroki' => $defaultParoki,
            'keuskupan' => $defaultParoki?->keuskupan,
        ]);
    }


    public function exportKkPdf(Request $request, string|int $id)
    {
        $decodedId = decode_id($id) ?: $id;
        $kk = is_numeric($decodedId)
            ? \App\Models\KkKatolik::with(['anggota', 'wilayah', 'kapela', 'kub'])->find($decodedId)
            : \App\Models\KkKatolik::with(['anggota', 'wilayah', 'kapela', 'kub'])->where('no_kk_kw', $id)->first();

        if (!$kk) {
            $kk = \App\Models\KkKatolik::with(['anggota', 'wilayah', 'kapela', 'kub'])->firstOrFail();
        }

        $defaultParokiId = $this->defaultParokiIdFromProfile();
        $paroki = Paroki::with('keuskupan')->find($defaultParokiId)
            ?? Paroki::with('keuskupan')->first();

        // Ambil data Keuskupan dan Profil Paroki aktif
        $keuskupan = $paroki?->keuskupan ?? \App\Models\Keuskupan::first();
        $profilParoki = \App\Models\ProfilParoki::first();

        $keuskupanLogo = $keuskupan?->logo ?: '/uploads/keuskupan/logo_keuskupan_kupang.svg';
        $parokiLogo = $paroki?->logo ?: '/assets/uploads/profil/logo_paroki_1787370466.jpeg';

        return response()->view('exports.kk-pdf', [
            'kk' => $kk,
            'paroki' => $paroki,
            'keuskupan' => $keuskupan,
            'profilParoki' => $profilParoki,
            'keuskupanLogo' => $keuskupanLogo,
            'parokiLogo' => $parokiLogo,
            'printedAt' => now()->format('d/m/Y H:i'),
        ]);
    }


    public function desaKelurahanOptions(Request $request)
    {
        $validated = $request->validate([
            'kecamatan_id' => ['nullable', 'integer'],
        ]);

        return response()->json([
            'data' => $this->desaOptionsForKecamatan($validated['kecamatan_id'] ?? null),
        ]);
    }


    protected function desaOptionsForKecamatan($kecamatanId)
    {
        if (!$kecamatanId) {
            return collect();
        }

        return \App\Models\DesaKelurahan::where('kecamatan_id', $kecamatanId)
            ->orderBy('nama_desa')
            ->get(['id_desa', 'kecamatan_id', 'nama_desa']);
    }


    protected function resolveKkCivilRegion(?\App\Models\KkKatolik $kkItem, ?\App\Models\Paroki $defaultParoki): array
    {
        $provinsi = $kkItem?->provinsi
            ? \App\Models\Provinsi::where('nama_provinsi', $kkItem->provinsi)->first(['id_provinsi', 'nama_provinsi'])
            : null;

        $kabupaten = $kkItem?->kota_kabupaten
            ? \App\Models\Kabupaten::when($provinsi, fn ($query) => $query->where('provinsi_id', $provinsi->id_provinsi))
                ->where('nama_kabupaten', $kkItem->kota_kabupaten)
                ->first(['id_kabupaten', 'provinsi_id', 'nama_kabupaten'])
            : null;

        $kecamatan = $kkItem?->kecamatan
            ? \App\Models\Kecamatan::when($kabupaten, fn ($query) => $query->where('kabupaten_id', $kabupaten->id_kabupaten))
                ->where('nama_kecamatan', $kkItem->kecamatan)
                ->first(['id_kecamatan', 'kabupaten_id', 'nama_kecamatan'])
            : null;

        $desa = $kkItem?->desa_kelurahan
            ? \App\Models\DesaKelurahan::when($kecamatan, fn ($query) => $query->where('kecamatan_id', $kecamatan->id_kecamatan))
                ->where('nama_desa', $kkItem->desa_kelurahan)
                ->first(['id_desa', 'kecamatan_id', 'nama_desa'])
            : null;

        $kecamatan = $kecamatan
            ?: ($desa ? \App\Models\Kecamatan::where('id_kecamatan', $desa->kecamatan_id)->first(['id_kecamatan', 'kabupaten_id', 'nama_kecamatan']) : null)
            ?: $defaultParoki?->kecamatan;

        $kabupaten = $kabupaten
            ?: ($kecamatan ? \App\Models\Kabupaten::where('id_kabupaten', $kecamatan->kabupaten_id)->first(['id_kabupaten', 'provinsi_id', 'nama_kabupaten']) : null)
            ?: $defaultParoki?->kabupaten;

        $provinsi = $provinsi
            ?: ($kabupaten ? \App\Models\Provinsi::where('id_provinsi', $kabupaten->provinsi_id)->first(['id_provinsi', 'nama_provinsi']) : null)
            ?: $defaultParoki?->provinsi;

        $desa = $desa ?: $defaultParoki?->desa;

        return [
            'provinsi_id' => $provinsi?->id_provinsi,
            'kabupaten_id' => $kabupaten?->id_kabupaten,
            'kecamatan_id' => $kecamatan?->id_kecamatan,
            'desa_id' => $desa?->id_desa,
            'provinsi' => $provinsi?->nama_provinsi,
            'kota_kabupaten' => $kabupaten?->nama_kabupaten,
            'kecamatan' => $kecamatan?->nama_kecamatan,
            'desa_kelurahan' => $desa?->nama_desa,
        ];
    }


    protected function validateKkRequest(Request $request, $item = null): void
    {
        $ignoreId = $item?->id;
        $unique = fn (string $column) => \Illuminate\Validation\Rule::unique('kk_katolik', $column)->ignore($ignoreId);

        $request->validate([
            'no_kk_kw' => ['required', 'string', 'max:50', $unique('no_kk_kw')],
            'no_kk_dukcapil' => ['nullable', 'digits_between:10,16', $unique('no_kk_dukcapil')],
            'nik_pemilik' => ['required', 'digits:16', $unique('nik_pemilik')],
            'nama_baptis_pemilik' => ['required', 'string', 'max:150'],
            'nama_lahir_pemilik' => ['required', 'string', 'max:150'],
            'nama_pasangan' => ['nullable', 'string', 'max:150'],
            'wilayah_id' => ['nullable', 'integer', 'exists:wilayah,id'],
            'kub_id' => ['nullable', 'integer', 'exists:kub,id'],
            'kapela_id' => ['nullable', 'integer', 'exists:kapela,id'],
            'lingkungan_id' => ['nullable', 'integer', 'exists:lingkungan,id'],
            'paroki_id' => ['nullable', 'integer'],
            'alamat_sekarang' => ['required', 'string', 'max:500'],
            'handphone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:150'],
            'status_verifikasi' => ['required', \Illuminate\Validation\Rule::in(['Belum', 'Terverifikasi', 'Ditolak'])],
            'status_kk' => ['required', \Illuminate\Validation\Rule::in(['Aktif', 'Pindah KUB', 'Pindah Wilayah', 'Pindah Paroki', 'Pecah KK', 'Tidak Aktif'])],
            'anggota' => ['nullable', 'array'],
            'anggota.*.id' => ['nullable', 'integer'],
            'anggota.*.nama_lengkap' => ['nullable', 'string', 'max:150'],
            'anggota.*.nama_baptis' => ['nullable', 'string', 'max:150'],
            'anggota.*.nik' => ['nullable', 'digits:16', 'distinct'],
            'anggota.*.hubungan_keluarga' => ['nullable', 'string', 'max:50'],
            'anggota.*.jenis_kelamin' => ['nullable', \Illuminate\Validation\Rule::in(['Laki-Laki', 'Perempuan'])],
            'anggota.*.tanggal_lahir' => ['nullable', 'date'],
            'anggota.*.tempat_lahir' => ['nullable', 'string', 'max:120'],
            'anggota.*.status_perkawinan' => ['nullable', 'string', 'max:80'],
        ]);

        foreach (array_values($request->input('anggota', [])) as $idx => $member) {
            if (!is_array($member)) {
                continue;
            }

            $nik = preg_replace('/\D+/', '', (string) ($member['nik'] ?? ''));
            if ($nik === '') {
                continue;
            }

            $query = \App\Models\Umat::where('nik', $nik);
            if (!empty($member['id'])) {
                $query->where('id', '!=', $member['id']);
            }
            if ($ignoreId) {
                $query->where(function ($q) use ($ignoreId) {
                    $q->where('kk_id', '!=', $ignoreId)->orWhereNull('kk_id');
                });
            }

            if ($query->exists()) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "anggota.{$idx}.nik" => "NIK anggota {$nik} sudah digunakan oleh data umat lain.",
                ]);
            }
        }
    }


    protected function normalizeKkPayload(array $data, bool $isCreate, $item = null): array
    {
        unset($data['anggota']);

        foreach (['no_kk_dukcapil', 'nik_pemilik', 'handphone'] as $field) {
            if (!empty($data[$field])) {
                $data[$field] = preg_replace('/\D+/', '', (string) $data[$field]);
            }
        }

        foreach (['wilayah_id', 'kub_id', 'kapela_id', 'paroki_id', 'lingkungan_id'] as $fk) {
            if (array_key_exists($fk, $data) && ($data[$fk] === '' || $data[$fk] === 'null')) {
                $data[$fk] = null;
            }
        }

        $data['status_kk'] = $data['status_kk'] ?? 'Aktif';
        $data['status_verifikasi'] = $data['status_verifikasi'] ?? 'Belum';

        if (empty($data['nama_baptis_pemilik']) && !empty($data['nama_lahir_pemilik'])) {
            $data['nama_baptis_pemilik'] = $data['nama_lahir_pemilik'];
        }
        if (empty($data['nama_lahir_pemilik']) && !empty($data['nama_baptis_pemilik'])) {
            $data['nama_lahir_pemilik'] = $data['nama_baptis_pemilik'];
        }

        if ($isCreate && empty($data['created_by']) && auth()->id()) {
            $data['created_by'] = auth()->id();
        }
        if (!$isCreate && auth()->id()) {
            $data['updated_by'] = auth()->id();
        }

        return $data;
    }


    protected function syncKkAnggota(\App\Models\KkKatolik $kk, $members): void
    {
        if (!is_array($members)) {
            return;
        }

        $validColumns = $this->schemaColumns('umat');
        $keepIds = [];

        foreach (array_values($members) as $idx => $member) {
            if (!is_array($member)) {
                continue;
            }

            $namaLengkap = trim((string) ($member['nama_lengkap'] ?? $member['nama_lahir'] ?? ''));
            $namaBaptis = trim((string) ($member['nama_baptis'] ?? ''));
            $nik = preg_replace('/\D+/', '', (string) ($member['nik'] ?? ''));

            if ($namaLengkap === '' && $namaBaptis === '' && $nik === '') {
                continue;
            }

            $payload = [
                'kk_id' => $kk->id,
                'no_urut_anggota' => $idx + 1,
                'kode_anggota' => $member['kode_anggota'] ?? null,
                'suku_etnis' => $member['suku_etnis'] ?? null,
                'nik' => $nik ?: null,
                'nama_lengkap' => $namaLengkap ?: $namaBaptis,
                'nama_lahir' => $namaLengkap ?: $namaBaptis,
                'nama_baptis' => $namaBaptis ?: $namaLengkap,
                'no_kk_kw' => $kk->no_kk_kw,
                'nama_pemilik_kk' => $kk->nama_lahir_pemilik ?: $kk->nama_baptis_pemilik,
                'hubungan_keluarga' => $member['hubungan_keluarga'] ?? ($idx === 0 ? 'Kepala Keluarga' : 'Anak'),
                'jenis_kelamin' => $member['jenis_kelamin'] ?? null,
                'tempat_lahir' => $member['tempat_lahir'] ?? null,
                'tanggal_lahir' => $member['tanggal_lahir'] ?? null,
                'status_menikah' => $member['status_perkawinan'] ?? $member['status_menikah'] ?? null,
                'status_perkawinan' => $member['status_perkawinan'] ?? null,
                'agama_asal' => $member['agama_asal'] ?? null,
                'pendidikan_saat_ini' => $member['pendidikan_saat_ini'] ?? $member['pendidikan'] ?? null,
                'pendidikan' => $member['pendidikan'] ?? $member['pendidikan_saat_ini'] ?? null,
                'pekerjaan' => $member['pekerjaan'] ?? null,
                'golongan_darah' => $member['golongan_darah'] ?? null,
                'talenta' => $member['talenta'] ?? null,
                'disabilitas' => $member['disabilitas'] ?? null,
                'status_baptis' => $member['status_baptis'] ?? null,
                'jenis_penerimaan_baptis' => $member['jenis_penerimaan_baptis'] ?? null,
                'tgl_baptis' => $member['tgl_baptis'] ?? null,
                'paroki_baptis' => $member['paroki_baptis'] ?? null,
                'pastor_baptis' => $member['pastor_baptis'] ?? null,
                'wali_baptis' => $member['wali_baptis'] ?? null,
                'buku_baptis_vol' => $member['buku_baptis_vol'] ?? null,
                'buku_baptis_hal' => $member['buku_baptis_hal'] ?? null,
                'buku_baptis_no' => $member['buku_baptis_no'] ?? null,
                'tgl_komuni_1' => $member['tgl_komuni_1'] ?? null,
                'paroki_komuni_1' => $member['paroki_komuni_1'] ?? null,
                'tgl_krisma' => $member['tgl_krisma'] ?? null,
                'paroki_krisma' => $member['paroki_krisma'] ?? null,
                'tgl_perkawinan' => $member['tgl_perkawinan'] ?? null,
                'paroki_perkawinan' => $member['paroki_perkawinan'] ?? null,
                'nama_pasangan' => $member['nama_pasangan'] ?? null,
                'status_perkawinan_kanonik' => $member['status_perkawinan_kanonik'] ?? null,
                'peristiwa_lain' => $member['peristiwa_lain'] ?? null,
                'no_surat_peristiwa' => $member['no_surat_peristiwa'] ?? null,
                'status_panggilan' => $member['status_panggilan'] ?? 'Awam',
                'nama_ordo_kongregasi' => $member['nama_ordo_kongregasi'] ?? null,
                'tahap_panggilan' => $member['tahap_panggilan'] ?? null,
                'tempat_tugas_biara' => $member['tempat_tugas_biara'] ?? null,
                'tgl_tahbisan_kaul' => $member['tgl_tahbisan_kaul'] ?? null,
                'status_aktif' => 1,
                'status_umat' => 'Aktif',
                'handphone' => $member['handphone'] ?? null,
                'email' => $member['email'] ?? null,
                'updated_by' => auth()->id(),
            ];

            $umat = null;
            if (!empty($member['id'])) {
                $umat = \App\Models\Umat::where('id', $member['id'])->where('kk_id', $kk->id)->first();
            }
            if (!$umat && $nik) {
                $umat = \App\Models\Umat::where('nik', $nik)->where('kk_id', $kk->id)->first();
            }
            if (!$umat && $nik && \App\Models\Umat::where('nik', $nik)->where('kk_id', '!=', $kk->id)->exists()) {
                continue;
            }

            $cleanPayload = array_intersect_key($payload, array_flip($validColumns));

            if ($umat) {
                $umat->update($cleanPayload);
            } else {
                if (in_array('created_by', $validColumns, true)) {
                    $cleanPayload['created_by'] = auth()->id();
                }
                $umat = \App\Models\Umat::create($cleanPayload);
            }

            $keepIds[] = $umat->id;
        }

        if (!empty($keepIds)) {
            \App\Models\Umat::where('kk_id', $kk->id)->whereNotIn('id', $keepIds)->update([
                'kk_id' => null,
                'tanggal_keluar_dari_kk' => now(),
                'updated_by' => auth()->id(),
            ]);
        }
    }


    protected function kkImportIdentityKeys(array $payload, array $validColumns): array
    {
        $keys = [];
        foreach (['no_kk_kw', 'no_kk_dukcapil', 'nik_pemilik'] as $column) {
            if (in_array($column, $validColumns, true) && !empty($payload[$column])) {
                $keys[] = Str::lower($column . ':' . trim((string) $payload[$column]));
            }
        }

        return array_values(array_unique($keys));
    }


    protected function findExistingKkForImport(string $modelClass, array $payload, array $validColumns)
    {
        $query = $modelClass::query();
        $hasIdentity = false;

        foreach (['no_kk_kw', 'no_kk_dukcapil', 'nik_pemilik'] as $column) {
            if (in_array($column, $validColumns, true) && !empty($payload[$column])) {
                $method = $hasIdentity ? 'orWhere' : 'where';
                $query->{$method}($column, trim((string) $payload[$column]));
                $hasIdentity = true;
            }
        }

        return $hasIdentity ? $query->first() : null;
    }


    protected function kkMemberImportHeadings(int $count = 2): array
    {
        $fields = [
            'Hubungan Keluarga',
            'NIK',
            'Nama Lengkap Sipil',
            'Nama Baptis Santo/Santa',
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Golongan Darah',
            'Agama Asal',
            'Pendidikan',
            'Pekerjaan',
            'Bidang Keahlian / Talenta Paroki',
            'Disabilitas/Kebutuhan Khusus',
            'Status Baptis',
            'Jenis Penerimaan Baptis',
            'Tanggal Baptis',
            'Paroki Tempat Baptis',
            'Pastor Pembaptis',
            'Nama Wali Baptis',
            'Buku Baptis Vol',
            'Buku Baptis Hal',
            'Buku Baptis No',
            'Tanggal Krisma',
            'Paroki Krisma',
            'Tanggal Perkawinan',
            'Paroki Perkawinan',
            'Nama Pasangan',
            'Status Perkawinan Kanonik',
            'Peristiwa Lain',
            'No Surat Peristiwa',
        ];

        $headings = [];
        for ($i = 1; $i <= $count; $i++) {
            foreach ($fields as $field) {
                $headings[] = "Anggota {$i} - {$field}";
            }
        }

        return $headings;
    }


    protected function kkMemberExportValues($kk, int $count = 2): array
    {
        $members = collect($kk->anggota ?? [])->values();
        $values = [];

        for ($i = 0; $i < $count; $i++) {
            $member = $members->get($i);
            $values = array_merge($values, [
                $member->hubungan_keluarga ?? '',
                $member->nik ?? '',
                $member->nama_lengkap ?? $member->nama_lahir ?? '',
                $member->nama_baptis ?? '',
                $member->jenis_kelamin ?? '',
                $member->tempat_lahir ?? '',
                $this->formatNullableDateForExport($member->tanggal_lahir ?? null),
                $member->golongan_darah ?? '',
                $member->agama_asal ?? $member->agama_saat_ini ?? '',
                $member->pendidikan_saat_ini ?? '',
                $member->pekerjaan ?? '',
                $member->talenta ?? '',
                $member->disabilitas ?? '',
                $member->status_baptis ?? '',
                $member->jenis_penerimaan_baptis ?? '',
                $member->tgl_baptis ?? '',
                $member->paroki_baptis ?? '',
                $member->pastor_baptis ?? '',
                $member->wali_baptis ?? '',
                $member->buku_baptis_vol ?? '',
                $member->buku_baptis_hal ?? '',
                $member->buku_baptis_no ?? '',
                $member->tgl_krisma ?? '',
                $member->paroki_krisma ?? '',
                $member->tgl_perkawinan ?? '',
                $member->paroki_perkawinan ?? '',
                $member->nama_pasangan ?? '',
                $member->status_perkawinan_kanonik ?? $member->status_perkawinan ?? '',
                $member->peristiwa_lain ?? '',
                $member->no_surat_peristiwa ?? '',
            ]);
        }

        return $values;
    }


    protected function extractKkImportMembers(array $row, array $rawHeaders): array
    {
        $members = [];
        $mapping = [
            'hubungan_keluarga' => 'hubungan_keluarga',
            'nik' => 'nik',
            'nama_lengkap_sipil' => 'nama_lengkap',
            'nama_baptis_santo_santa' => 'nama_baptis',
            'jenis_kelamin' => 'jenis_kelamin',
            'tempat_lahir' => 'tempat_lahir',
            'tanggal_lahir' => 'tanggal_lahir',
            'golongan_darah' => 'golongan_darah',
            'agama_asal' => 'agama_asal',
            'pendidikan' => 'pendidikan_saat_ini',
            'pekerjaan' => 'pekerjaan',
            'bidang_keahlian_talenta_paroki' => 'talenta',
            'disabilitas_kebutuhan_khusus' => 'disabilitas',
            'status_baptis' => 'status_baptis',
            'jenis_penerimaan_baptis' => 'jenis_penerimaan_baptis',
            'tanggal_baptis' => 'tgl_baptis',
            'paroki_tempat_baptis' => 'paroki_baptis',
            'pastor_pembaptis' => 'pastor_baptis',
            'nama_wali_baptis' => 'wali_baptis',
            'buku_baptis_vol' => 'buku_baptis_vol',
            'buku_baptis_hal' => 'buku_baptis_hal',
            'buku_baptis_no' => 'buku_baptis_no',
            'tanggal_krisma' => 'tgl_krisma',
            'paroki_krisma' => 'paroki_krisma',
            'tanggal_perkawinan' => 'tgl_perkawinan',
            'paroki_perkawinan' => 'paroki_perkawinan',
            'nama_pasangan' => 'nama_pasangan',
            'status_perkawinan_kanonik' => 'status_perkawinan_kanonik',
            'peristiwa_lain' => 'peristiwa_lain',
            'no_surat_peristiwa' => 'no_surat_peristiwa',
        ];

        foreach ($rawHeaders as $column => $heading) {
            if (!preg_match('/^anggota_(\d+)_(.+)$/', $heading, $matches)) {
                continue;
            }

            $index = (int) $matches[1] - 1;
            $fieldKey = $matches[2];
            $target = $mapping[$fieldKey] ?? null;
            if (!$target) {
                continue;
            }

            $value = $row[$column] ?? null;
            if (is_string($value)) {
                $value = trim($value);
            }
            if ($value === '') {
                $value = null;
            }

            if (in_array($target, ['nik'], true) && $value !== null) {
                $value = preg_replace('/\.0$/', '', trim((string) $value));
                $value = preg_replace('/\D+/', '', $value);
            }

            if (in_array($target, ['tanggal_lahir', 'tgl_baptis', 'tgl_krisma', 'tgl_perkawinan'], true)) {
                $value = $this->normalizeImportDateValue($value);
            }

            $members[$index][$target] = $value;
        }

        return collect($members)
            ->sortKeys()
            ->filter(function ($member) {
                return collect($member)->filter(fn ($value) => $value !== null && $value !== '')->isNotEmpty();
            })
            ->values()
            ->all();
    }


    protected function formatNullableDateForExport($value): string
    {
        if (!$value) {
            return '';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        $timestamp = strtotime((string) $value);
        return $timestamp ? date('Y-m-d', $timestamp) : (string) $value;
    }


    protected function normalizeImportDateValue($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        if (is_numeric($value)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)->format('Y-m-d');
            } catch (\Throwable) {
                return null;
            }
        }

        $text = trim((string) $value);
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $text, $matches)) {
            return sprintf('%04d-%02d-%02d', (int) $matches[3], (int) $matches[2], (int) $matches[1]);
        }

        $timestamp = strtotime($text);
        return $timestamp ? date('Y-m-d', $timestamp) : null;
    }


    protected function buildEcclesiasticalReferenceData(): array
    {
        return [
            'Wilayah Pastoral' => \App\Models\Wilayah::orderBy('nama_wilayah')->pluck('nama_wilayah')->filter()->values()->all() ?: ['Wilayah I - St. Petrus', 'Wilayah II - St. Paulus'],
            'KUB / KBG' => \App\Models\Kub::orderBy('nama_kub')->pluck('nama_kub')->filter()->values()->all() ?: ['KUB Sta. Maria', 'KUB St. Yosef'],
            'Stasi / Kapela' => \App\Models\Kapela::orderBy('nama_kapela')->pluck('nama_kapela')->filter()->values()->all() ?: ['Kapela St. Fransiskus'],
            'Lingkungan' => \App\Models\Lingkungan::orderBy('nama_lingkungan')->pluck('nama_lingkungan')->filter()->values()->all() ?: ['Lingkungan St. Yohanes', 'Lingkungan St. Gabriel'],
            'Paroki' => \App\Models\Paroki::orderBy('nama_paroki')->pluck('nama_paroki')->filter()->values()->all() ?: ['Paroki St. Vinsensius a Paulo Benlutu'],
            'Kevikepan / Dekenat' => \App\Models\Dekenat::orderBy('nama_kevikepan')->pluck('nama_kevikepan')->filter()->values()->all() ?: ['Dekenat Timor Tengah Selatan (TTS)'],
            'Kepemilikan Rumah' => ['Milik Sendiri', 'Sewa / Kontrak', 'Ikut Orang Tua', 'Rumah Dinas'],
            'Kategori Ekonomi Pastoral' => ['Prasejahtera', 'Sejahtera / Mandiri', 'Mampu'],
            'Jenis Penerimaan Baptis (KHK 849)' => ['Baptis Bayi (Infantis)', 'Baptis Dewasa (Adultus)', 'Receptio (Penerimaan ke Katolik)'],
            'Status Perkawinan Kanonik (KHK 1055)' => ['Katolik Organik', 'Beda Agama (Dispensasi)', 'Beda Gereja (Izin)'],
            'Disabilitas / Khusus' => ['Tidak Ada', 'Rungu / Wicara', 'Netra', 'Daksa / Fisik', 'Mental', 'Lansia Perawatan'],
            'Agama Asal' => ['Katolik sejak lahir', 'Katekumen', 'Protestan', 'Islam', 'Hindu', 'Budha', 'Lainnya'],
            'Status KK' => ['Aktif', 'Pindah KUB', 'Pindah Wilayah', 'Pindah Paroki', 'Pecah KK', 'Tidak Aktif'],
            'Status Verifikasi' => ['Terverifikasi', 'Belum', 'Ditolak'],
        ];
    }


    protected function buildCivilReferenceData(): array
    {
        return [
            'Provinsi' => \App\Models\Provinsi::orderBy('nama_provinsi')->pluck('nama_provinsi')->filter()->values()->all() ?: ['Nusa Tenggara Timur'],
            'Kabupaten / Kota' => \App\Models\Kabupaten::orderBy('nama_kabupaten')->pluck('nama_kabupaten')->filter()->values()->all() ?: ['Kabupaten Timor Tengah Selatan', 'Kota Kupang'],
            'Kecamatan' => \App\Models\Kecamatan::take(100)->orderBy('nama_kecamatan')->pluck('nama_kecamatan')->filter()->values()->all() ?: ['Kecamatan Batu Putih', 'Kecamatan Kota Soe'],
            'Desa / Kelurahan' => \App\Models\DesaKelurahan::take(250)->orderBy('nama_desa')->pluck('nama_desa')->filter()->values()->all() ?: ['Desa Benlutu', 'Desa Oebobo'],
            'Hubungan Keluarga' => ['Kepala Keluarga', 'Istri', 'Anak', 'Orang Tua', 'Mertua', 'Menantu', 'Cucu', 'Famili Lain'],
            'Pekerjaan' => ['PNS / ASN', 'TNI / Polri', 'Karyawan Swasta', 'Wiraswasta / Pedagang', 'Petani / Pekebun', 'Peternak', 'Nelayan', 'Guru / Dosen', 'Tenaga Medis / Perawat / Dokter', 'Tukang / Buruh Bangunan', 'Pelajar / Mahasiswa', 'Ibu Rumah Tangga', 'Pensiunan', 'Belum / Tidak Bekerja', 'Lainnya'],
            'Pendidikan' => ['Tidak / Belum Sekolah', 'SD / Sederajat', 'SMP / Sederajat', 'SMA / SMK / Sederajat', 'Diploma (D1-D3)', 'Sarjana (S1)', 'Magister (S2)', 'Doktoral (S3)'],
            'Golongan Darah' => ['A', 'B', 'AB', 'O', 'Tidak Tahu'],
            'Status Perkawinan' => ['Belum Menikah', 'Menikah Katolik', 'Menikah Campur Beda Agama', 'Menikah Campur Beda Gereja', 'Duda', 'Janda'],
            'Penghasilan / Ekonomi' => ['< Rp 1.000.000', 'Rp 1.000.000 - Rp 3.000.000', 'Rp 3.000.000 - Rp 5.000.000', 'Rp 5.000.000 - Rp 10.000.000', '> Rp 10.000.000'],
            'Suku / Etnis' => ['Timor / Dawan', 'Rote', 'Sabu', 'Flores / Manggarai', 'Sumba', 'Jawa', 'Tionghoa', 'Lainnya'],
        ];
    }


    protected function ensureKkKatolikColumns(): void
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('kk_katolik')) {
                \Illuminate\Support\Facades\Schema::table('kk_katolik', function ($table) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('kk_katolik', 'status_kepemilikan_rumah')) {
                        $table->string('status_kepemilikan_rumah', 50)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('kk_katolik', 'kategori_ekonomi')) {
                        $table->string('kategori_ekonomi', 50)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('kk_katolik', 'bantuan_pastoral')) {
                        $table->text('bantuan_pastoral')->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('kk_katolik', 'pekerjaan')) {
                        $table->string('pekerjaan', 150)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('kk_katolik', 'pendidikan')) {
                        $table->string('pendidikan', 100)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('kk_katolik', 'golongan_darah')) {
                        $table->string('golongan_darah', 20)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('kk_katolik', 'penghasilan')) {
                        $table->string('penghasilan', 100)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('kk_katolik', 'lingkungan_id')) {
                        $table->unsignedInteger('lingkungan_id')->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('kk_katolik', 'kub_id')) {
                        $table->unsignedInteger('kub_id')->nullable();
                    }
                });
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('umat')) {
                \Illuminate\Support\Facades\Schema::table('umat', function ($table) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'kode_anggota')) {
                        $table->string('kode_anggota', 50)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'suku_etnis')) {
                        $table->string('suku_etnis', 100)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'agama_asal')) {
                        $table->string('agama_asal', 50)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'talenta')) {
                        $table->string('talenta', 255)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'disabilitas')) {
                        $table->string('disabilitas', 50)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'status_baptis')) {
                        $table->string('status_baptis', 50)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'jenis_penerimaan_baptis')) {
                        $table->string('jenis_penerimaan_baptis', 50)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'tgl_baptis')) {
                        $table->date('tgl_baptis')->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'paroki_baptis')) {
                        $table->string('paroki_baptis', 150)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'pastor_baptis')) {
                        $table->string('pastor_baptis', 150)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'wali_baptis')) {
                        $table->string('wali_baptis', 150)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'buku_baptis_vol')) {
                        $table->string('buku_baptis_vol', 20)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'buku_baptis_hal')) {
                        $table->string('buku_baptis_hal', 20)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'buku_baptis_no')) {
                        $table->string('buku_baptis_no', 20)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'tgl_komuni_1')) {
                        $table->date('tgl_komuni_1')->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'paroki_komuni_1')) {
                        $table->string('paroki_komuni_1', 150)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'tgl_krisma')) {
                        $table->date('tgl_krisma')->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'paroki_krisma')) {
                        $table->string('paroki_krisma', 150)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'tgl_perkawinan')) {
                        $table->date('tgl_perkawinan')->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'paroki_perkawinan')) {
                        $table->string('paroki_perkawinan', 150)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'nama_pasangan')) {
                        $table->string('nama_pasangan', 150)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'status_perkawinan_kanonik')) {
                        $table->string('status_perkawinan_kanonik', 50)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'no_surat_peristiwa')) {
                        $table->string('no_surat_peristiwa', 100)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'status_panggilan')) {
                        $table->string('status_panggilan', 100)->nullable()->default('Awam');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'nama_ordo_kongregasi')) {
                        $table->string('nama_ordo_kongregasi', 150)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'tahap_panggilan')) {
                        $table->string('tahap_panggilan', 100)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'tempat_tugas_biara')) {
                        $table->string('tempat_tugas_biara', 255)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('umat', 'tgl_tahbisan_kaul')) {
                        $table->date('tgl_tahbisan_kaul')->nullable();
                    }
                });
            }
            \Illuminate\Support\Facades\Cache::forget('schema_columns_kk_katolik');
            \Illuminate\Support\Facades\Cache::forget('schema_columns_umat');
        } catch (\Throwable $e) {
            // Silently continue
        }
    }

}
