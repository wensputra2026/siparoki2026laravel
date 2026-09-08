<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasIndirectId;
use App\Models\Concerns\HasUuid;

class Kapela extends Model
{
    use HasIndirectId, HasUuid;

    protected $table = 'kapela';
    protected $appends = ['hashid', 'iid'];

    protected $fillable = [
        'uuid',
        'paroki_id',
        'kode_kapela',
        'nama_kapela',
        'slug',
        'lokasi',
        'penanggung_jawab',
        'no_hp',
        'keterangan',
        'status',
        'created_by',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            if (empty($model->slug) && !empty($model->nama_kapela)) {
                $model->slug = \Illuminate\Support\Str::slug($model->nama_kapela);
            }
        });
    }

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
        return $this->hasManyThrough(Wilayah::class, Lingkungan::class, 'kapela_id', 'id', 'id', 'wilayah_id')->distinct();
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
