<?php

namespace App\Models\Concerns;

use Illuminate\Support\Str;

trait HasUuid
{
    /**
     * Boot trait to automatically assign a UUID v4 on model creation.
     */
    public static function bootHasUuid(): void
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Scope query to find by UUID or integer ID (or obfuscated hashid).
     */
    public function scopeWhereUuidOrId($query, int|string|null $idOrUuid)
    {
        if (empty($idOrUuid)) {
            return $query->whereRaw('1 = 0');
        }

        $val = (string) $idOrUuid;

        if (Str::isUuid($val)) {
            return $query->where('uuid', $val);
        }

        $decoded = function_exists('decode_id') ? decode_id($val) : null;
        if ($decoded) {
            return $query->where($this->getKeyName(), $decoded);
        }

        if (is_numeric($val)) {
            return $query->where($this->getKeyName(), (int) $val);
        }

        return $query->where('uuid', $val);
    }

    /**
     * Find a model by UUID or ID, or return null.
     */
    public static function findByUuidOrId(int|string|null $idOrUuid)
    {
        if (empty($idOrUuid)) {
            return null;
        }

        return static::whereUuidOrId($idOrUuid)->first();
    }

    /**
     * Find a model by UUID or ID, or throw 404.
     */
    public static function findByUuidOrIdOrFail(int|string|null $idOrUuid)
    {
        $model = static::findByUuidOrId($idOrUuid);
        if (!$model) {
            abort(404, 'Data tidak ditemukan.');
        }

        return $model;
    }
}
