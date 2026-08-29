<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Defunctorum extends Model
{
    protected $table = 'defunctorum';
    protected $primaryKey = 'id_defunctorum';
    protected $fillable = [
        'umat_id',
        'kk_id',
        'wilayah_id',
        'kapela_id',
        'kub_id',
        'nama_lengkap',
        'nama_baptis',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'tanggal_meninggal',
        'tempat_meninggal',
        'sakramen_diterima',
        'status',
        'keterangan',
        'foto',
        'is_deleted',
    ];
}
