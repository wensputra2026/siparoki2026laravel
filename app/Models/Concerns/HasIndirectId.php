<?php

namespace App\Models\Concerns;

use App\Support\IdObfuscator;

trait HasIndirectId
{
    /**
     * Dapatkan Indirect ID (IID / Hashid) dari model ini.
     */
    public function getHashidAttribute(): ?string
    {
        return IdObfuscator::encode($this->getKey());
    }

    /**
     * Alias getIidAttribute.
     */
    public function getIidAttribute(): ?string
    {
        return $this->getHashidAttribute();
    }

    /**
     * Scope untuk query berdasarkan Indirect ID (IID / Hashid) atau numeric ID.
     */
    public function scopeWhereHashid($query, int|string|null $hashid)
    {
        $id = IdObfuscator::decode($hashid);
        return $query->where($this->getKeyName(), $id ?: $hashid);
    }

    /**
     * Temukan model berdasarkan Indirect ID (IID / Hashid) atau lempar 404.
     */
    public static function findByHashidOrFail(int|string|null $hashid)
    {
        $id = IdObfuscator::decodeOrFail($hashid);
        return static::findOrFail($id);
    }

    /**
     * Temukan model berdasarkan Indirect ID (IID / Hashid) atau null.
     */
    public static function findByHashid(int|string|null $hashid)
    {
        $id = IdObfuscator::decode($hashid);
        return $id ? static::find($id) : null;
    }
}
