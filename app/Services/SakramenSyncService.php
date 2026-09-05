<?php

namespace App\Services;

use App\Models\Sakramen;
use App\Models\Umat;
use Illuminate\Support\Facades\Log;

class SakramenSyncService
{
    /**
     * Sinkronisasi data sakramen dari satu Umat ke tabel sakramen
     */
    public static function syncFromUmat(Umat $umat): void
    {
        if (!$umat->id) {
            return;
        }

        // 1. Sakramen Baptis
        $hasBaptis = !empty($umat->tgl_baptis)
            || in_array(strtolower((string)$umat->status_baptis), ['sudah', '1', 'true', 'ya'], true)
            || !empty($umat->buku_baptis_no)
            || !empty($umat->paroki_baptis);

        if ($hasBaptis) {
            Sakramen::updateOrCreate(
                [
                    'id_umat' => $umat->id,
                    'tipe_sakramen' => 'Baptis',
                ],
                [
                    'tanggal' => $umat->tgl_baptis ?: $umat->created_at,
                    'tempat' => $umat->paroki_baptis ?: 'Paroki St. Vinsensius a Paulo Benlutu',
                    'pastor' => $umat->pastor_baptis,
                    'pelaksana' => $umat->pastor_baptis,
                    'wali_baptis' => $umat->wali_baptis,
                    'liber_vol' => $umat->buku_baptis_vol,
                    'liber_hal' => $umat->buku_baptis_hal,
                    'liber_no' => $umat->buku_baptis_no,
                    'status' => 'Sah',
                    'catatan' => $umat->jenis_penerimaan_baptis ? ('Penerimaan: ' . $umat->jenis_penerimaan_baptis) : null,
                ]
            );
        }

        // 2. Sakramen Komuni Pertama (Ekaristi)
        if (!empty($umat->tgl_komuni_1) || !empty($umat->paroki_komuni_1)) {
            Sakramen::updateOrCreate(
                [
                    'id_umat' => $umat->id,
                    'tipe_sakramen' => 'Komuni Pertama',
                ],
                [
                    'tanggal' => $umat->tgl_komuni_1 ?: $umat->created_at,
                    'tempat' => $umat->paroki_komuni_1 ?: 'Paroki St. Vinsensius a Paulo Benlutu',
                    'status' => 'Sah',
                ]
            );
        }

        // 3. Sakramen Krisma (Penguatan)
        if (!empty($umat->tgl_krisma) || !empty($umat->paroki_krisma)) {
            Sakramen::updateOrCreate(
                [
                    'id_umat' => $umat->id,
                    'tipe_sakramen' => 'Krisma',
                ],
                [
                    'tanggal' => $umat->tgl_krisma ?: $umat->created_at,
                    'tempat' => $umat->paroki_krisma ?: 'Paroki St. Vinsensius a Paulo Benlutu',
                    'status' => 'Sah',
                ]
            );
        }

        // 4. Sakramen Perkawinan
        $hasNikah = !empty($umat->tgl_perkawinan)
            || (!empty($umat->nama_pasangan) && in_array(strtolower((string)$umat->status_perkawinan), ['menikah', 'kawin', 'menikah katolik', 'kawin katolik'], true));

        if ($hasNikah) {
            Sakramen::updateOrCreate(
                [
                    'id_umat' => $umat->id,
                    'tipe_sakramen' => 'Perkawinan',
                ],
                [
                    'tanggal' => $umat->tgl_perkawinan ?: $umat->created_at,
                    'tempat' => $umat->paroki_perkawinan ?: 'Paroki St. Vinsensius a Paulo Benlutu',
                    'nama_pasangan' => $umat->nama_pasangan,
                    'id_pasangan' => $umat->pasangan_umat_id,
                    'jenis_perkawinan' => $umat->status_perkawinan_kanonik ?: 'Kanonik Katolik',
                    'status' => 'Sah',
                ]
            );
        }

        // 5. Sakramen Tahbisan / Kaul
        $hasTahbisan = !empty($umat->tgl_tahbisan_kaul) || !empty($umat->nama_ordo_kongregasi);
        if ($hasTahbisan) {
            $keteranganParts = array_filter([
                $umat->status_panggilan,
                $umat->nama_ordo_kongregasi,
                $umat->tahap_panggilan,
                $umat->tempat_tugas_biara,
            ]);

            Sakramen::updateOrCreate(
                [
                    'id_umat' => $umat->id,
                    'tipe_sakramen' => 'Tahbisan',
                ],
                [
                    'tanggal' => $umat->tgl_tahbisan_kaul ?: $umat->created_at,
                    'keterangan' => implode(' - ', $keteranganParts),
                    'status' => 'Sah',
                ]
            );
        }
    }

    /**
     * Rekap otomatis seluruh data sakramen dari seluruh tabel Umat
     */
    public static function syncAll(): int
    {
        $count = 0;
        $umats = Umat::whereNull('deleted_at')->get();
        foreach ($umats as $umat) {
            self::syncFromUmat($umat);
            $count++;
        }
        return $count;
    }
}
