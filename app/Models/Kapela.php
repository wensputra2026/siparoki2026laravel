<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Kapela extends Model
{

    protected $table = 'kapela';

    protected $fillable = [
        'paroki_id',
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

    public function paroki()
    {
        return $this->belongsTo(Paroki::class, 'paroki_id', 'id_paroki');
    }

    public function wilayahs()
    {
        return $this->hasMany(Wilayah::class, 'kapela_id');
    }

    public function kubs()
    {
        return $this->hasMany(Kub::class, 'kapela_id');
    }

    public function lingkungan()
    {
        return $this->hasMany(Lingkungan::class, 'kapela_id');
    }
}
