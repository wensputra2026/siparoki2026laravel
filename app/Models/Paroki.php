<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Paroki extends Model
{

    protected $table = 'paroki';
    protected $primaryKey = 'id_paroki';

    protected $fillable = [
        'keuskupan_id',
        'dekenat_id',
        'kode_paroki',
        'nama_paroki',
        'pelindung_paroki',
        'status_paroki',
        'tanggal_berdiri',
        'nama_pastor_paroki_aktif',
        'nama_pastor_rekan',
        'alamat',
        'provinsi_id',
        'kabupaten_id',
        'kecamatan_id',
        'desa_id',
        'telepon',
        'whatsapp',
        'email',
        'website',
        'logo',
        'maps_embed',
        'maps_url',
        'latitude',
        'longitude',
        'keterangan',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_berdiri' => 'date',
            'is_deleted' => 'boolean',
        ];
    }
}
