<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasIndirectId;

class Role extends Model
{
    use HasIndirectId;

    protected $table = 'roles';

    protected $fillable = [
        'nama_role',
        'slug',
        'deskripsi',
        'permissions',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'permissions' => 'array',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
