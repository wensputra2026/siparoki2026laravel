<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keuangan extends Model
{
    protected $table = 'keuangan';
    protected $keyType = 'string';
    protected $primaryKey = 'id';

    protected $fillable = [
        'tanggal',
        'jenis',
        'kategori',
        'kode_coa',
        'jumlah',
        'keterangan',
        'bukti',
        'penerima',
        'status',
        'status_approval',
        'approved_by',
        'approved_at',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'jumlah' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }
}
