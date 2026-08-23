<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanSakramen extends Model
{
    protected $table = 'pengajuan_sakramen';

    protected $fillable = [
        'umat_id',
        'nama_lengkap',
        'whatsapp',
        'tipe_sakramen',
        'tanggal_pelaksanaan',
        'keterangan',
        'bukti_pembayaran',
        'status_pembayaran',
        'status_pengajuan',
        'biaya_administrasi',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_pelaksanaan' => 'date',
            'biaya_administrasi' => 'decimal:2',
        ];
    }
}
