<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provinsi extends Model
{
    protected $table = 'provinsi';
    protected $primaryKey = 'id_provinsi';
    protected $guarded = [];

    public function kabupatens()
    {
        return $this->hasMany(Kabupaten::class, 'provinsi_id', 'id_provinsi');
    }
}
