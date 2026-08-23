<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalMisa extends Model
{
    protected $table = 'jadwal_misa';

    protected $fillable = [
        'tanggal',
        'waktu',
        'hari',
        'bulan',
        'jenis_perayaan',
        'jam_perayaan',
        'jenis_misa',
        'tempat',
        'lokasi',
        'pelayan',
        'catatan',
        'intensi',
        'foto',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'waktu' => 'datetime:H:i',
        ];
    }
}
