<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterCoa extends Model
{
    protected $table = 'master_coa';

    protected $fillable = [
        'kode_akun',
        'nama_akun',
        'jenis',
        'deskripsi',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
        ];
    }
}
