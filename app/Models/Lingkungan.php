<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Lingkungan extends Model
{

    protected $table = 'lingkungan';

    protected $fillable = [
        'wilayah_id',
        'kapela_id',
        'kode_lingkungan',
        'nama_lingkungan',
        'ketua_lingkungan',
        'no_hp',
        'keterangan',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'is_deleted' => 'boolean',
        ];
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class);
    }

    public function kapela()
    {
        return $this->belongsTo(Kapela::class);
    }
}
