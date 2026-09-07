<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasUuid;

class Kub extends Model
{
    use HasUuid;

    protected $table = 'kub';
    protected $fillable = [
        'uuid',
        'paroki_id',
        'wilayah_id',
        'kapela_id',
        'lingkungan_id',
        'kode_kub',
        'nama_kub',
        'nama_pelindung',
        'pelindung',
        'ketua_kub',
        'deskripsi',
        'no_hp',
        'alamat',
        'lokasi',
        'jadwal_ibadat',
        'provinsi_id',
        'kabupaten_id',
        'kecamatan_id',
        'desa_id',
        'keterangan',
        'status',
        'delete_reason',
        'is_deleted',
    ];

    protected $appends = [
        'jumlah_kk',
    ];

    public function getJumlahKkAttribute(): int
    {
        if (array_key_exists('jumlah_kk', $this->attributes)) {
            return (int) $this->attributes['jumlah_kk'];
        }
        if ($this->relationLoaded('kks')) {
            return $this->kks->count();
        }
        return $this->kks()->count();
    }

    public function kks()
    {
        return $this->hasMany(KkKatolik::class, 'kub_id');
    }

    public function umats()
    {
        return $this->hasMany(Umat::class, 'kub_id');
    }

    public function lingkungan()
    {
        return $this->belongsTo(Lingkungan::class, 'lingkungan_id');
    }

    public function wilayah()
    {
        return $this->belongsTo(Wilayah::class, 'wilayah_id');
    }

    public function paroki()
    {
        return $this->belongsTo(Paroki::class, 'paroki_id', 'id_paroki');
    }

    public function kapela()
    {
        return $this->belongsTo(Kapela::class, 'kapela_id');
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
}

