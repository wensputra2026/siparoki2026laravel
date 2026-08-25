<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kolekte extends Model
{
    protected $table = 'kolekte';

    protected $fillable = [
        'tanggal',
        'kategori_misa',
        'nominal',
        'petugas_penghitung',
        'lokasi_misa',
        'keterangan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'nominal' => 'decimal:2',
        ];
    }
}
