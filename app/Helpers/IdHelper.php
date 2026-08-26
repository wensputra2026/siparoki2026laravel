<?php

use App\Support\IdObfuscator;

if (!function_exists('encode_id')) {
    /**
     * Encode integer ID ke Indirect ID (IID / Hashid).
     */
    function encode_id(int|string|null $id): ?string
    {
        return IdObfuscator::encode($id);
    }
}

if (!function_exists('decode_id')) {
    /**
     * Decode Indirect ID (IID / Hashid) kembali ke integer ID asli.
     */
    function decode_id(int|string|null $hash): ?int
    {
        return IdObfuscator::decode($hash);
    }
}

if (!function_exists('decode_id_or_fail')) {
    /**
     * Decode Indirect ID (IID / Hashid) atau lempar 404 jika tidak valid.
     */
    function decode_id_or_fail(int|string|null $hash): int
    {
        return IdObfuscator::decodeOrFail($hash);
    }
}
