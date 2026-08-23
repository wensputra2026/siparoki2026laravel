<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Renungan extends Model
{

    protected $table = 'renungan_harian';

    protected $fillable = [
        'tanggal',
        'judul',
        'slug',
        'isi_renungan',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }
}
