<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WilayahKecamatan extends Model
{
    protected $table = 'wilayah_kecamatans';
    protected $primaryKey = 'kode';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'kode',
        'kabupaten_kode',
        'nama',
    ];

    public function kabupaten(): BelongsTo
    {
        return $this->belongsTo(WilayahKabupaten::class, 'kabupaten_kode', 'kode');
    }

    public function desas(): HasMany
    {
        return $this->hasMany(WilayahDesa::class, 'kecamatan_kode', 'kode');
    }
}
