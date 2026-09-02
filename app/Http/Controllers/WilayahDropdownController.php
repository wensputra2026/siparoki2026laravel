<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\WilayahProvinsi;
use App\Models\WilayahKabupaten;
use App\Models\WilayahKecamatan;
use App\Models\WilayahDesa;
use Illuminate\Http\JsonResponse;

class WilayahDropdownController extends Controller
{
    public function getProvinsi(): JsonResponse
    {
        return response()->json(WilayahProvinsi::orderBy('nama')->get(['kode', 'nama']));
    }

    public function getKabupaten(string $provinsiKode): JsonResponse
    {
        return response()->json(
            WilayahKabupaten::where('provinsi_kode', $provinsiKode)->orderBy('nama')->get(['kode', 'nama'])
        );
    }

    public function getKecamatan(string $kabupatenKode): JsonResponse
    {
        return response()->json(
            WilayahKecamatan::where('kabupaten_kode', $kabupatenKode)->orderBy('nama')->get(['kode', 'nama'])
        );
    }

    public function getDesa(string $kecamatanKode): JsonResponse
    {
        return response()->json(
            WilayahDesa::where('kecamatan_kode', $kecamatanKode)->orderBy('nama')->get(['kode', 'nama', 'kode_pos'])
        );
    }
}
