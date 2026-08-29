<?php

if (!function_exists('siparoki_resolve_safe_file')) {
    /**
     * Safely resolve a candidate list of file paths, guaranteeing the final
     * realpath stays inside one of the allowed base directories. Prevents
     * directory traversal (e.g. /assets/uploads/../../.env) from disclosing
     * arbitrary files outside the web root.
     */
    function siparoki_resolve_safe_file(array $candidates, array $allowedBases): ?string
    {
        $normalized = [];
        foreach ($allowedBases as $base) {
            $real = realpath($base);
            if ($real === false) {
                $real = $base;
            }
            $normalized[] = rtrim($real, '/\\') . DIRECTORY_SEPARATOR;
        }

        foreach ($candidates as $candidate) {
            $real = realpath($candidate);
            if ($real === false || !is_file($real)) {
                continue;
            }
            $real .= (is_dir($real) ? DIRECTORY_SEPARATOR : '');
            foreach ($normalized as $base) {
                if (strncmp($real, $base, strlen($base)) === 0) {
                    return $real;
                }
            }
        }

        return null;
    }
}
