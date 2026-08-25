<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Iuran extends Model
{
    protected $table = 'iuran';

    protected $fillable = [
        'kk_id',
        'id_kk',
        'no_kk',
        'nama_kepala',
        'jenis_iuran_id',
        'nama_iuran',
        'tahun',
        'bulan',
        'bulan_lunas',
        'jumlah',
        'total_jumlah',
        'tanggal_bayar',
        'metode_pembayaran',
        'status_bayar',
        'kolektor',
        'petugas',
        'bukti_bayar',
        'keterangan',
        'is_deleted',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'bulan' => 'integer',
            'jumlah' => 'decimal:2',
            'total_jumlah' => 'decimal:2',
            'tanggal_bayar' => 'date',
            'is_deleted' => 'integer',
        ];
    }

    public function jenisIuran()
    {
        return $this->belongsTo(JenisIuran::class, 'jenis_iuran_id');
    }

    public function kk()
    {
        return $this->belongsTo(KkKatolik::class, 'id_kk');
    }

    public function getNoKkAttribute()
    {
        return $this->kk?->no_kk_kw ?: $this->kk?->no_kk_dukcapil ?: ($this->attributes['no_kk'] ?? '-');
    }

    public function getNamaKepalaAttribute()
    {
        if ($this->kk) {
            $baptis = trim($this->kk->nama_baptis_pemilik ?? '');
            $lahir = trim($this->kk->nama_lahir_pemilik ?? '');
            if ($baptis && !str_contains(strtolower($lahir), strtolower($baptis))) {
                return "{$baptis} {$lahir}";
            }
            return $lahir ?: $baptis ?: ($this->attributes['nama_kepala'] ?? '-');
        }
        return $this->attributes['nama_kepala'] ?? '-';
    }

    public function getNamaIuranAttribute()
    {
        return $this->jenisIuran?->nama_iuran ?: ($this->attributes['nama_iuran'] ?? '-');
    }

    public function getTotalJumlahAttribute()
    {
        return $this->attributes['jumlah'] ?? $this->attributes['total_jumlah'] ?? 0;
    }

    public function getBulanLunasAttribute()
    {
        return $this->attributes['bulan'] ?? $this->attributes['bulan_lunas'] ?? '-';
    }

    public function getStatusBayarAttribute()
    {
        return $this->attributes['status'] ?? $this->attributes['status_bayar'] ?? 'Lunas';
    }
}
