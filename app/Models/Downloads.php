<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
class Downloads extends Model
{

    protected $table = 'downloads';

    protected $fillable = [
        'judul',
        'kategori',
        'file_path',
        'file_size',
        'download_count',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'download_count' => 'integer',
        ];
    }
}
