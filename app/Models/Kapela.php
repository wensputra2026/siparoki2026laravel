<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Kapela extends Model
{

    protected $table = 'kapela';

    protected $fillable = [
        'kode_kapela',
        'nama_kapela',
        'lokasi',
        'penanggung_jawab',
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

    public function lingkungan()
    {
        return $this->hasMany(Lingkungan::class);
    }
}
