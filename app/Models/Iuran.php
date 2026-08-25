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

    protected $appends = [
        'no_kk',
        'nama_kepala',
        'nama_iuran',
        'total_jumlah',
        'bulan_lunas',
        'status_bayar',
    ];

    protected function casts(): array
    {
        return [
            'tahun' => 'integer',
            'bulan' => 'string',
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
        $bulanMap = [
            '01' => 'Januari', '1' => 'Januari',
            '02' => 'Februari', '2' => 'Februari',
            '03' => 'Maret', '3' => 'Maret',
            '04' => 'April', '4' => 'April',
            '05' => 'Mei', '5' => 'Mei',
            '06' => 'Juni', '6' => 'Juni',
            '07' => 'Juli', '7' => 'Juli',
            '08' => 'Agustus', '8' => 'Agustus',
            '09' => 'September', '9' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember',
        ];
        $b = (string) ($this->attributes['bulan'] ?? $this->attributes['bulan_lunas'] ?? '');
        return $bulanMap[$b] ?? $b ?: '-';
    }

    public function getStatusBayarAttribute()
    {
        $st = strtolower((string) ($this->attributes['status'] ?? $this->attributes['status_bayar'] ?? 'lunas'));
        return ($st === 'lunas' || $st === 'aktif') ? 'Lunas' : 'Belum Lunas';
    }
}
