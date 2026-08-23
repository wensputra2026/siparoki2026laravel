<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sakramen extends Model
{
    protected $table = 'sakramen';
    protected $primaryKey = 'id_sakramen';

    protected $fillable = [
        'id_umat',
        'tipe_sakramen',
        'jenis_perkawinan',
        'no_surat',
        'tanggal',
        'tempat',
        'pelaksana',
        'nama_pendamping',
        'catatan',
        'dokumen',
        'status',
        'pastor',
        'keterangan',
        'id_pasangan',
        'nama_pasangan',
        'wali_baptis',
        'saksi_1',
        'saksi_2',
        'no_surat_dispensasi',
        'tgl_surat_dispensasi',
        'tgl_nikah_sipil_awal',
        'no_keputusan_tribunal',
        'liber_vol',
        'liber_hal',
        'liber_no',
        'status_liber',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'tgl_surat_dispensasi' => 'date',
            'tgl_nikah_sipil_awal' => 'date',
        ];
    }

    public function umat()
    {
        return $this->belongsTo(Umat::class, 'id_umat');
    }
}
