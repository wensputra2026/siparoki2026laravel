<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Rapat extends Model
{

    protected $table = 'rapat';

    protected $fillable = [
        'agenda',
        'tanggal',
        'waktu',
        'lokasi',
        'notulen',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
        ];
    }
}
