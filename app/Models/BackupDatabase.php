<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupDatabase extends Model
{
    protected $table = 'backup_database';

    protected $fillable = [
        'nama_file',
        'ukuran',
        'dibuat_oleh',
    ];

    protected function casts(): array
    {
        return [
            'ukuran' => 'integer',
        ];
    }
}
