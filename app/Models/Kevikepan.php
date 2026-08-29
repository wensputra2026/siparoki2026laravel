<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kevikepan extends Model
{
    protected $table = 'kevikepan';
    protected $fillable = [
        'nama_kevikepan',
        'vikep',
        'alamat',
        'telepon',
        'email',
        'keuskupan_id',
        'status',
    ];

    public function keuskupan()
    {
        return $this->belongsTo(Keuskupan::class, 'keuskupan_id', 'id_keuskupan');
    }

    public function parokis()
    {
        return $this->hasMany(Paroki::class, 'dekenat_id');
    }

    public function kuasiParokis()
    {
        return $this->hasMany(KuasiParoki::class, 'dekenat_id');
    }
}
