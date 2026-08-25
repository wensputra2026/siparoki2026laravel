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
        return $this->belongsTo(KkKatolik::class, 'kk_id');
    }
}
