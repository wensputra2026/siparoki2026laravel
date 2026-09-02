<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WilayahProvinsi extends Model
{
    protected $table = 'wilayah_provinsis';
    protected $primaryKey = 'kode';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'kode',
        'nama',
    ];

    public function kabupatens(): HasMany
    {
        return $this->hasMany(WilayahKabupaten::class, 'provinsi_kode', 'kode');
    }
}
