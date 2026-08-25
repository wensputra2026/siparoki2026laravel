<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kolekte extends Model
{
    protected $table = 'kolekte';

    protected $fillable = [
        'tanggal',
        'kategori_misa',
        'jadwal_misa_id',
        'nominal',
        'petugas_penghitung',
        'lokasi_misa',
        'keterangan',
        'wilayah_id',
        'kapela_id',
        'kub_id',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'nominal' => 'decimal:2',
        ];
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id');
    }

    public function kapela()
    {
        return $this->belongsTo(Kapela::class, 'kapela_id');
    }

    public function jadwalMisa()
    {
        return $this->belongsTo(JadwalMisa::class, 'jadwal_misa_id');
    }
}
