<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasIndirectId;
use App\Models\Concerns\HasUuid;

class Role extends Model
{
    use HasIndirectId, HasUuid;

    protected $table = 'roles';

    protected $fillable = [
        'uuid',
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
