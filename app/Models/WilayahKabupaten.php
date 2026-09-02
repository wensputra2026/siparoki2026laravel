<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WilayahKabupaten extends Model
{
    protected $table = 'wilayah_kabupatens';
    protected $primaryKey = 'kode';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'kode',
        'provinsi_kode',
        'nama',
    ];

    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(WilayahProvinsi::class, 'provinsi_kode', 'kode');
    }

    public function kecamatans(): HasMany
    {
        return $this->hasMany(WilayahKecamatan::class, 'kabupaten_kode', 'kode');
    }
}
