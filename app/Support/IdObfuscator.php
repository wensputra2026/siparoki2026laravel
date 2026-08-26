<?php

namespace App\Support;

/**
 * Class IdObfuscator (Indirect ID / IID Service)
 *
 * Mengonversi integer ID asli database ke string hash alfanumerik aman (dan sebaliknya)
 * untuk mencegah Insecure Direct Object Reference (IDOR) dan ID Enumeration / Scraping.
 */
class IdObfuscator
{
    private static ?self $instance = null;
    private string $alphabet;
    private string $salt;
    private int $minHashLength;
    private string $seps;
    private string $guards;

    public function __construct(?string $salt = null, int $minHashLength = 8, string $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890')
    {
        $this->salt = $salt ?: (string) (config('app.key') ?: 'siparoki_secure_salt_2026');
        $this->minHashLength = max(4, $minHashLength);
        $this->alphabet = $this->uniqueChars($alphabet);

        $sepDiv = 3.5;
        $guardDiv = 12;

        $this->seps = 'cfhistuCFHISTU';
        $this->alphabet = $this->diffChars($this->alphabet, $this->seps);
        $this->seps = $this->consistentShuffle($this->seps, $this->salt);

        if (empty($this->seps) || (strlen($this->alphabet) / strlen($this->seps)) > $sepDiv) {
            $sepsLength = (int) ceil(strlen($this->alphabet) / $sepDiv);
            if ($sepsLength > strlen($this->seps)) {
                $diff = $sepsLength - strlen($this->seps);
                $this->seps .= substr($this->alphabet, 0, $diff);
                $this->alphabet = substr($this->alphabet, $diff);
            }
        }

        $this->alphabet = $this->consistentShuffle($this->alphabet, $this->salt);
        $guardCount = (int) ceil(strlen($this->alphabet) / $guardDiv);

        if (strlen($this->alphabet) < 3) {
            $this->guards = substr($this->seps, 0, $guardCount);
            $this->seps = substr($this->seps, $guardCount);
        } else {
            $this->guards = substr($this->alphabet, 0, $guardCount);
            $this->alphabet = substr($this->alphabet, $guardCount);
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Encode integer ID ke Indirect ID (IID / Hashid).
     */
    public static function encode(int|string|null $id): ?string
    {
        if ($id === null || $id === '') {
            return null;
        }

        // Jika sudah berupa string bukan angka (misal sudah di-encode sebelumnya), kembalikan
        if (!is_numeric($id) && is_string($id)) {
            return $id;
        }

        $intId = (int) $id;
        if ($intId < 0) {
            return null;
        }

        return self::getInstance()->_encode([$intId]);
    }

    /**
     * Decode Indirect ID (IID / Hashid) kembali ke integer ID asli.
     * Mendukung backward-compatibility jika raw integer ID dikirim.
     */
    public static function decode(int|string|null $hash): ?int
    {
        if ($hash === null || $hash === '') {
            return null;
        }

        // Backward compatibility: jika input murni angka integer > 0
        if (is_int($hash) || (is_string($hash) && ctype_digit($hash))) {
            return (int) $hash;
        }

        $decoded = self::getInstance()->_decode((string) $hash);
        return !empty($decoded) ? (int) $decoded[0] : null;
    }

    /**
     * Decode atau lempar 404 jika hash tidak valid.
     */
    public static function decodeOrFail(int|string|null $hash): int
    {
        $id = self::decode($hash);
        if ($id === null || $id <= 0) {
            abort(404, 'Identifikasi data tidak valid atau telah kadaluarsa.');
        }
        return $id;
    }

    private function _encode(array $numbers): string
    {
        $alphabet = $this->alphabet;
        $numbersSize = count($numbers);
        $numbersHashInt = 0;

        foreach ($numbers as $i => $number) {
            $numbersHashInt += ($number % ($i + 100));
        }

        $lottery = $ret = $alphabet[$numbersHashInt % strlen($alphabet)];

        foreach ($numbers as $i => $number) {
            $buffer = $lottery . $this->salt . $alphabet;
            $alphabet = $this->consistentShuffle($alphabet, substr($buffer, 0, strlen($alphabet)));
            $last = $this->hash($number, $alphabet);

            $ret .= $last;

            if ($i + 1 < $numbersSize) {
                $number %= (ord($last) + $i);
                $sepsIndex = $number % strlen($this->seps);
                $ret .= $this->seps[$sepsIndex];
            }
        }

        if (strlen($ret) < $this->minHashLength) {
            $guardIndex = ($numbersHashInt + ord($ret[0])) % strlen($this->guards);
            $guard = $this->guards[$guardIndex];
            $ret = $guard . $ret;

            if (strlen($ret) < $this->minHashLength) {
                $guardIndex = ($numbersHashInt + ord($ret[2])) % strlen($this->guards);
                $guard = $this->guards[$guardIndex];
                $ret .= $guard;
            }
        }

        $halfLength = (int) (strlen($alphabet) / 2);
        while (strlen($ret) < $this->minHashLength) {
            $alphabet = $this->consistentShuffle($alphabet, $alphabet);
            $ret = substr($alphabet, $halfLength) . $ret . substr($alphabet, 0, $halfLength);
            $excess = strlen($ret) - $this->minHashLength;
            if ($excess > 0) {
                $ret = substr($ret, (int) ($excess / 2), $this->minHashLength);
            }
        }

        return $ret;
    }

    private function _decode(string $hash): array
    {
        $ret = [];
        $hashBreakdown = str_replace(str_split($this->guards), ' ', $hash);
        $hashArray = explode(' ', $hashBreakdown);
        $i = count($hashArray) == 3 || count($hashArray) == 2 ? 1 : 0;
        $hash = $hashArray[$i] ?? '';

        if (!empty($hash)) {
            $lottery = $hash[0];
            $hash = substr($hash, 1);
            $hashBreakdown = str_replace(str_split($this->seps), ' ', $hash);
            $hashArray = explode(' ', $hashBreakdown);

            $alphabet = $this->alphabet;
            foreach ($hashArray as $subHash) {
                $buffer = $lottery . $this->salt . $alphabet;
                $alphabet = $this->consistentShuffle($alphabet, substr($buffer, 0, strlen($alphabet)));
                $val = $this->unhash($subHash, $alphabet);
                if ($val !== null) {
                    $ret[] = $val;
                }
            }
        }

        return $ret;
    }

    private function consistentShuffle(string $alphabet, string $salt): string
    {
        if (!strlen($salt)) {
            return $alphabet;
        }

        $letters = str_split($alphabet);
        for ($i = strlen($alphabet) - 1, $v = 0, $p = 0; $i > 0; $i--, $v++) {
            $v %= strlen($salt);
            $p += $int = ord($salt[$v]);
            $j = ($int + $v + $p) % $i;

            $temp = $letters[$j];
            $letters[$j] = $letters[$i];
            $letters[$i] = $temp;
        }

        return implode('', $letters);
    }

    private function hash(int $input, string $alphabet): string
    {
        $hash = '';
        $alphabetLength = strlen($alphabet);

        do {
            $hash = $alphabet[$input % $alphabetLength] . $hash;
            $input = (int) ($input / $alphabetLength);
        } while ($input);

        return $hash;
    }

    private function unhash(string $input, string $alphabet): ?int
    {
        $number = 0;
        $alphabetLength = strlen($alphabet);

        for ($i = 0; $i < strlen($input); $i++) {
            $pos = strpos($alphabet, $input[$i]);
            if ($pos === false) {
                return null;
            }
            $number = $number * $alphabetLength + $pos;
        }

        return $number;
    }

    private function uniqueChars(string $str): string
    {
        $unique = '';
        for ($i = 0; $i < strlen($str); $i++) {
            if (strpos($unique, $str[$i]) === false) {
                $unique .= $str[$i];
            }
        }
        return $unique;
    }

    private function diffChars(string $str, string $remove): string
    {
        $res = '';
        for ($i = 0; $i < strlen($str); $i++) {
            if (strpos($remove, $str[$i]) === false) {
                $res .= $str[$i];
            }
        }
        return $res;
    }
}
