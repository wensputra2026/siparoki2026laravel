<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KuasiParoki extends Model
{
    protected $table = 'kuasi_paroki';
    protected $fillable = [
        'paroki_id',
        'NamaKuasiParoki',
        'KodeKuasiParoki',
        'AlamatKuasiParoki',
        'RT',
        'RW',
        'Kelurahan',
        'Kecamatan',
        'Kota',
        'KodePos',
        'Telepon',
        'Email',
        'Website',
        'PastorKuasiParoki',
        'AdminKuasiParoki',
        'TahunDidirikan',
        'TanggalDiresmikan',
        'StatusAktif',
        'Keterangan',
        'Latitude',
        'Longitude',
        'CreatedAt',
        'UpdatedAt',
        'CreatedBy',
        'UpdatedBy',
        'provinsi_id',
        'kabupaten_id',
        'kecamatan_id',
        'desa_id',
    ];
    protected $appends = [
        'nama_kuasi',
        'kode_kuasi',
        'lokasi',
        'pastor_administrator',
        'status',
        'dekenat_nama',
    ];

    const CREATED_AT = 'CreatedAt';
    const UPDATED_AT = 'UpdatedAt';

    public function getNamaKuasiAttribute(): ?string
    {
        return $this->attributes['nama_kuasi'] ?? $this->attributes['NamaKuasiParoki'] ?? null;
    }

    public function getKodeKuasiAttribute(): ?string
    {
        return $this->attributes['kode_kuasi'] ?? $this->attributes['KodeKuasiParoki'] ?? sprintf('KP-%03d', $this->getKey());
    }

    public function getLokasiAttribute(): ?string
    {
        return $this->attributes['lokasi'] ?? $this->attributes['AlamatKuasiParoki'] ?? null;
    }

    public function getPastorAdministratorAttribute(): ?string
    {
        return $this->attributes['pastor_administrator'] ?? $this->attributes['PastorKuasiParoki'] ?? null;
    }

    public function getStatusAttribute(): string
    {
        $status = $this->attributes['status'] ?? $this->attributes['StatusAktif'] ?? null;
        $history = $this->attributes['Keterangan'] ?? '';

        if (str_contains($history, '[ELEVASI-KUASI-PAROKI]') || str_contains($history, 'Dinaikkan menjadi Paroki')) {
            return 'Ditingkatkan Menjadi Paroki (Definitif)';
        }

        return in_array($status, ['N', 'Nonaktif', 0, '0', false], true) ? 'Nonaktif' : 'Aktif';
    }

    public function getDekenatNamaAttribute(): ?string
    {
        $dekenat = $this->dekenat ?? $this->paroki?->dekenat;

        return $dekenat?->nama_kevikepan ?? $dekenat?->nama_dekenat ?? null;
    }

    public function keuskupan()
    {
        return $this->belongsTo(Keuskupan::class, 'keuskupan_id', 'id_keuskupan');
    }

    public function dekenat()
    {
        return $this->belongsTo(Dekenat::class, 'dekenat_id', 'id_dekenat');
    }

    public function paroki()
    {
        return $this->belongsTo(Paroki::class, 'paroki_id', 'id_paroki');
    }
}
