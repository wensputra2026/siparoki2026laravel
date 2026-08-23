<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Wilayah extends Model
{

    protected $table = 'wilayah';

    protected $fillable = [
        'paroki_id',
        'kode_wilayah',
        'nama_wilayah',
        'ketua_wilayah',
        'no_hp',
        'deskripsi',
        'keterangan',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_deleted' => 'boolean',
        ];
    }

    public function paroki()
    {
        return $this->belongsTo(Paroki::class);
    }

    public function lingkungan()
    {
        return $this->hasMany(Lingkungan::class);
    }
}
