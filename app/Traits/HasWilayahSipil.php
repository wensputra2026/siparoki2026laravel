<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\WilayahProvinsi;
use App\Models\WilayahKabupaten;
use App\Models\WilayahKecamatan;
use App\Models\WilayahDesa;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasWilayahSipil
{
    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(WilayahProvinsi::class, 'provinsi_kode', 'kode');
    }

    public function kabupaten(): BelongsTo
    {
        return $this->belongsTo(WilayahKabupaten::class, 'kabupaten_kode', 'kode');
    }

    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(WilayahKecamatan::class, 'kecamatan_kode', 'kode');
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(WilayahDesa::class, 'desa_kode', 'kode');
    }

    /**
     * Mengambil susunan teks alamat sipil lengkap
     */
    public function getAlamatSipilLengkapAttribute(): string
    {
        $parts = array_filter([
            $this->desa?->nama ? "Desa/Kel. {$this->desa->nama}" : null,
            $this->kecamatan?->nama ? "Kec. {$this->kecamatan->nama}" : null,
            $this->kabupaten?->nama ? "{$this->kabupaten->nama}" : null,
            $this->provinsi?->nama ? "Prov. {$this->provinsi->nama}" : null,
        ]);

        return empty($parts) ? '-' : implode(', ', $parts);
    }
}
