<?php

namespace App\Http\Controllers\Admin\Pastoral;

use App\Http\Controllers\Controller;
use App\Models\Umat;
use App\Models\KkKatolik;
use App\Models\Kub;
use App\Models\Wilayah;
use App\Models\Paroki;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MutasiUmatController extends Controller
{
    protected function resolvePrefix(Request $request): string
    {
        $first = explode('/', trim($request->path(), '/'))[0] ?? 'superadmin';
        return $first ?: 'superadmin';
    }

    protected function currentParoki(): ?Paroki
    {
        return Paroki::orderBy('id')->first();
    }

    /**
     * Form mutasi umat antar KUB.
     */
    public function showMutasi(Request $request, $id)
    {
        $umat = Umat::with('kk.kub', 'kk.wilayah', 'kk.kapela')->findOrFail($id);
        $kkAsal = $umat->kk;
        $kubAsal = $kkAsal?->kub;

        $kubList = Kub::with('wilayah', 'kapela')->orderBy('nama_kub')->get(['id', 'nama_kub', 'wilayah_id', 'kapela_id', 'kode_kub']);
        $kkList = KkKatolik::orderBy('nama_lahir_pemilik')->get(['id', 'no_kk_kw', 'nama_lahir_pemilik', 'nama_baptis_pemilik', 'wilayah_id', 'kapela_id', 'kub_id']);
        $wilayahList = Wilayah::orderBy('nama_wilayah')->get(['id', 'nama_wilayah']);

        return Inertia::render('Inertia/UmatMutasi', [
            'mode' => 'mutasi',
            'prefix' => $this->resolvePrefix($request),
            'umatItem' => $umat,
            'kubAsal' => $kubAsal,
            'kkAsal' => $kkAsal,
            'kubList' => $kubList,
            'kkList' => $kkList,
            'wilayahList' => $wilayahList,
            'namaParoki' => $this->currentParoki()?->nama_paroki ?? 'Paroki',
        ]);
    }

    /**
     * Proses mutasi umat ke KUB lain (pindah KK dalam KUB tujuan).
     * Mencatat riwayat ke riwayat_mutasi_umat.
     */
    public function prosesMutasi(Request $request, $id)
    {
        $data = $request->validate([
            'kk_tujuan_id' => 'required|integer|exists:kk_katolik,id',
            'alasan' => 'required|string|min:3',
            'no_surat_pindah' => 'nullable|string|max:100',
            'tgl_mutasi' => 'nullable|date',
        ]);

        $umat = Umat::with('kk.kub', 'kk.wilayah', 'kk.kapela')->findOrFail($id);
        $kkLama = $umat->kk;
        $kubAsal = $kkLama?->kub;
        $kkBaru = KkKatolik::with('kub', 'wilayah', 'kapela')->findOrFail($data['kk_tujuan_id']);
        $kubTujuan = $kkBaru->kub;

        if ($kubAsal && $kubTujuan && (int) $kubAsal->id === (int) $kubTujuan->id) {
            return redirect()->back()->with('error', 'KUB tujuan sama dengan KUB saat ini. Pilih KUB yang berbeda.');
        }

        DB::transaction(function () use ($umat, $kkLama, $kkBaru, $kubAsal, $kubTujuan, $data, $request) {
            $umat->kk_sebelumnya_id = $kkLama?->id;
            $umat->kk_id = $kkBaru->id;
            $umat->tanggal_keluar_dari_kk = now();
            $umat->tanggal_menjadi_anggota_kk = now();
            $umat->updated_by = Auth::id();
            $umat->save();

            DB::table('riwayat_mutasi_umat')->insert([
                'umat_id' => $umat->id,
                'jenis_mutasi' => 'Mutasi KUB',
                'kk_id' => $kkBaru->id,
                'kub_asal_id' => $kubAsal?->id,
                'kub_tujuan_id' => $kubTujuan?->id,
                'wilayah_asal_id' => $kkLama?->wilayah_id,
                'wilayah_tujuan_id' => $kkBaru->wilayah_id,
                'paroki_asal_id' => $kubAsal?->paroki_id,
                'paroki_tujuan_id' => $kubTujuan?->paroki_id,
                'alasan' => $data['alasan'],
                'no_surat_pindah' => $data['no_surat_pindah'] ?? null,
                'tgl_surat_pindah' => $data['tgl_mutasi'] ?? null,
                'tgl_mutasi' => $data['tgl_mutasi'] ?? now(),
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $prefix = $this->resolvePrefix($request);
        return redirect()->route("panel.{$prefix}.umat.riwayat", [$id])
            ->with('success', "Mutasi umat ke KUB {$kubTujuan?->nama_kub} berhasil dicatat. Riwayat tersimpan.");
    }

    /**
     * Form pisah KK saat menikah -> bentuk keluarga baru.
     */
    public function showPisah(Request $request, $id)
    {
        $umat = Umat::with('kk.kub', 'kk.wilayah', 'kk.kapela')->findOrFail($id);
        $kkAsal = $umat->kk;
        $kubAsal = $kkAsal?->kub;

        $kubList = Kub::with('wilayah', 'kapela')->orderBy('nama_kub')->get(['id', 'nama_kub', 'wilayah_id', 'kapela_id', 'kode_kub']);
        $wilayahList = Wilayah::orderBy('nama_wilayah')->get(['id', 'nama_wilayah']);

        return Inertia::render('Inertia/UmatMutasi', [
            'mode' => 'pisah',
            'prefix' => $this->resolvePrefix($request),
            'umatItem' => $umat,
            'kubAsal' => $kubAsal,
            'kkAsal' => $kkAsal,
            'kubList' => $kubList,
            'kkList' => [],
            'wilayahList' => $wilayahList,
            'namaParoki' => $this->currentParoki()?->nama_paroki ?? 'Paroki',
        ]);
    }

    /**
     * Proses pisah KK: buat KK baru di KUB tujuan, pindahkan umat ke sana,
     * tandai status menikah, dan catat riwayat.
     */
    public function prosesPisah(Request $request, $id)
    {
        $data = $request->validate([
            'kub_tujuan_id' => 'required|integer|exists:kub,id',
            'no_kk_kw' => 'required|string|max:50',
            'nama_pemilik_kk' => 'required|string|max:150',
            'tgl_perkawinan' => 'nullable|date',
            'nama_pasangan' => 'nullable|string|max:150',
            'alasan' => 'nullable|string|max:500',
        ]);

        $umat = Umat::with('kk.kub', 'kk.wilayah', 'kk.kapela')->findOrFail($id);
        $kkLama = $umat->kk;
        $kubAsal = $kkLama?->kub;
        $kubTujuan = Kub::with('wilayah', 'kapela')->findOrFail($data['kub_tujuan_id']);

        $kkBaru = null;
        DB::transaction(function () use ($umat, $kkLama, $kubTujuan, $kubAsal, $data, &$kkBaru, $request) {
            $kkBaru = KkKatolik::create([
                'no_kk_kw' => $data['no_kk_kw'],
                'nama_lahir_pemilik' => $data['nama_pemilik_kk'],
                'nama_baptis_pemilik' => $data['nama_pemilik_kk'],
                'kub_id' => $kubTujuan->id,
                'wilayah_id' => $kubTujuan->wilayah_id,
                'kapela_id' => $kubTujuan->kapela_id,
                'lingkungan_id' => $kubTujuan->lingkungan_id ?? null,
                'status_kk' => 'Aktif',
                'status_verifikasi' => 'Terverifikasi',
                'created_by' => Auth::id(),
            ]);

            $umat->kk_sebelumnya_id = $kkLama?->id;
            $umat->kk_id = $kkBaru->id;
            $umat->tanggal_keluar_dari_kk = now();
            $umat->tanggal_menjadi_anggota_kk = now();
            $umat->status_perkawinan = 'Menikah';
            $umat->status_menikah = 'Menikah';
            if (!empty($data['tgl_perkawinan'])) {
                $umat->tgl_perkawinan = $data['tgl_perkawinan'];
            }
            if (!empty($data['nama_pasangan'])) {
                $umat->nama_pasangan = $data['nama_pasangan'];
            }
            $umat->updated_by = Auth::id();
            $umat->save();

            DB::table('riwayat_mutasi_umat')->insert([
                'umat_id' => $umat->id,
                'jenis_mutasi' => 'Pisah KK (Menikah)',
                'kk_id' => $kkBaru->id,
                'kub_asal_id' => $kubAsal?->id,
                'kub_tujuan_id' => $kubTujuan->id,
                'wilayah_asal_id' => $kkLama?->wilayah_id,
                'wilayah_tujuan_id' => $kubTujuan->wilayah_id,
                'paroki_asal_id' => $kubAsal?->paroki_id,
                'paroki_tujuan_id' => $kubTujuan->paroki_id,
                'alasan' => $data['alasan'] ?? 'Pisah KK akibat perkawinan',
                'tgl_mutasi' => $data['tgl_perkawinan'] ?? now(),
                'created_by' => Auth::id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        $prefix = $this->resolvePrefix($request);
        return redirect()->route("panel.{$prefix}.umat.riwayat", [$id])
            ->with('success', "Pisah KK berhasil. KK baru (No. {$data['no_kk_kw']}) dibentuk di KUB {$kubTujuan->nama_kub}. Riwayat tersimpan.");
    }

    /**
     * Tampilkan riwayat mutasi/pindah KUB & pisah KK untuk satu umat.
     */
    public function showRiwayat(Request $request, $id)
    {
        $umat = Umat::findOrFail($id);
        $prefix = $this->resolvePrefix($request);

        $riwayat = DB::table('riwayat_mutasi_umat as r')
            ->leftJoin('kub as ka', 'ka.id', '=', 'r.kub_asal_id')
            ->leftJoin('kub as kt', 'kt.id', '=', 'r.kub_tujuan_id')
            ->leftJoin('kk_katolik as kk', 'kk.id', '=', 'r.kk_id')
            ->where('r.umat_id', $id)
            ->orderByDesc('r.id')
            ->select(
                'r.id',
                'r.jenis_mutasi',
                'r.alasan',
                'r.no_surat_pindah',
                'r.tgl_mutasi',
                'r.created_at',
                'r.kub_asal_id',
                'r.kub_tujuan_id',
                'ka.nama_kub as kub_asal_nama',
                'kt.nama_kub as kub_tujuan_nama',
                'kk.no_kk_kw as kk_tujuan_no',
                'r.created_by'
            )
            ->get();

        return Inertia::render('Inertia/UmatRiwayatMutasi', [
            'prefix' => $prefix,
            'umatItem' => $umat,
            'riwayatList' => $riwayat,
            'namaParoki' => $this->currentParoki()?->nama_paroki ?? 'Paroki',
        ]);
    }

    /**
     * Form tambah riwayat mutasi secara mandiri (manual) dengan pilihan
     * umat, jenis mutasi, KUB asal/tujuan, dan wilayah asal/tujuan dari database.
     */
    public function showTambah(Request $request)
    {
        $prefix = $this->resolvePrefix($request);
        $umatId = $request->query('umat_id');

        $umatList = Umat::query()
            ->select('umat.id', 'umat.nama_lengkap', 'kk.kub_id as kub_asal_id', 'kub.wilayah_id as wilayah_asal_id')
            ->leftJoin('kk_katolik as kk', 'kk.id', '=', 'umat.kk_id')
            ->leftJoin('kub', 'kub.id', '=', 'kk.kub_id')
            ->orderBy('umat.nama_lengkap')
            ->get();
        $kubList = Kub::orderBy('nama_kub')->get(['id', 'nama_kub', 'kode_kub', 'wilayah_id']);
        $wilayahList = Wilayah::orderBy('nama_wilayah')->get(['id', 'nama_wilayah']);
        $jenisList = ['Mutasi KUB', 'Pindah Paroki', 'Pisah KK (Menikah)', 'Pindah Wilayah'];

        return Inertia::render('Inertia/UmatRiwayatTambah', [
            'prefix' => $prefix,
            'umatId' => $umatId ? (int) $umatId : null,
            'umatList' => $umatList,
            'kubList' => $kubList,
            'wilayahList' => $wilayahList,
            'jenisList' => $jenisList,
            'namaParoki' => $this->currentParoki()?->nama_paroki ?? 'Paroki',
        ]);
    }

    /**
     * Simpan riwayat mutasi manual ke riwayat_mutasi_umat.
     */
    public function storeTambah(Request $request)
    {
        $data = $request->validate([
            'umat_id' => 'required|integer|exists:umat,id',
            'jenis_mutasi' => 'required|string|max:100',
            'kub_asal_id' => 'nullable|integer|exists:kub,id',
            'kub_tujuan_id' => 'nullable|integer|exists:kub,id',
            'wilayah_asal_id' => 'nullable|integer|exists:wilayah,id',
            'wilayah_tujuan_id' => 'nullable|integer|exists:wilayah,id',
            'tgl_mutasi' => 'required|date',
            'alasan' => 'required|string|min:3',
        ]);

        DB::table('riwayat_mutasi_umat')->insert([
            'umat_id' => $data['umat_id'],
            'jenis_mutasi' => $data['jenis_mutasi'],
            'kub_asal_id' => $data['kub_asal_id'] ?? null,
            'kub_tujuan_id' => $data['kub_tujuan_id'] ?? null,
            'wilayah_asal_id' => $data['wilayah_asal_id'] ?? null,
            'wilayah_tujuan_id' => $data['wilayah_tujuan_id'] ?? null,
            'alasan' => $data['alasan'],
            'tgl_mutasi' => $data['tgl_mutasi'],
            'created_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $prefix = $this->resolvePrefix($request);
        return redirect()->route("panel.{$prefix}.umat.riwayat", [$data['umat_id']])
            ->with('success', 'Riwayat mutasi berhasil ditambahkan.');
    }
}
