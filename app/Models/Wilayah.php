<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasIndirectId;
use App\Models\Concerns\HasUuid;

class Wilayah extends Model
{
    use HasIndirectId, HasUuid;

    protected $table = 'wilayah';
    protected $appends = ['hashid', 'iid'];

    protected $fillable = [
        'uuid',
        'paroki_id',
        'kapela_id',
        'kode_wilayah',
        'nama_wilayah',
        'ketua_wilayah',
        'no_hp',
        'alamat',
        'provinsi_id',
        'kabupaten_id',
        'kecamatan_id',
        'desa_id',
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

    public function kapela()
    {
        return $this->belongsTo(Kapela::class, 'kapela_id');
    }

    public function kapelas()
    {
        return $this->hasManyThrough(Kapela::class, Lingkungan::class, 'wilayah_id', 'id', 'id', 'kapela_id')->distinct();
    }

    public function stasi()
    {
        return $this->kapela();
    }

    public function provinsi()
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id', 'id_provinsi');
    }

    public function kabupaten()
    {
        return $this->belongsTo(Kabupaten::class, 'kabupaten_id', 'id_kabupaten');
    }

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id', 'id_kecamatan');
    }

    public function desa()
    {
        return $this->belongsTo(DesaKelurahan::class, 'desa_id', 'id_desa');
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
