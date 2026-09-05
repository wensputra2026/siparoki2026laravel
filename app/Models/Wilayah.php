<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasUuid;

class Wilayah extends Model
{
    use HasUuid;

    protected $table = 'wilayah';

    protected $fillable = [
        'uuid',
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
        return $this->belongsTo(Paroki::class, 'paroki_id', 'id_paroki');
    }

    public function kapelas()
    {
        return $this->hasManyThrough(Kapela::class, Lingkungan::class, 'wilayah_id', 'id', 'id', 'kapela_id')->distinct();
    }

    public function kapela()
    {
        return $this->hasOneThrough(Kapela::class, Lingkungan::class, 'wilayah_id', 'id', 'id', 'kapela_id');
    }

    public function stasi()
    {
        return $this->kapela();
    }

    public function lingkungan()
    {
        return $this->hasMany(Lingkungan::class, 'wilayah_id');
    }

    public function kubs()
    {
        return $this->hasMany(Kub::class, 'wilayah_id');
    }
}
