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

if (!function_exists('nama_bulan_indonesia')) {
    function nama_bulan_indonesia(int $m): string
    {
        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        return $bulan[$m] ?? '';
    }
}

if (!function_exists('nama_hari_indonesia')) {
    function nama_hari_indonesia(int $d): string
    {
        $hari = [
            0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa',
            3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'
        ];
        return $hari[$d] ?? '';
    }
}

if (!function_exists('format_tanggal_indonesia')) {
    function format_tanggal_indonesia($date, bool $withDay = false, bool $shortMonth = false): string
    {
        if (empty($date)) return '';
        try {
            $c = is_a($date, \Carbon\Carbon::class) ? $date : \Carbon\Carbon::parse($date);
            $bulan = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $bulanSingkat = [
                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
                5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
                9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
            ];
            $hari = [
                0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa',
                3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'
            ];
            
            $bName = $shortMonth ? ($bulanSingkat[$c->month] ?? '') : ($bulan[$c->month] ?? '');
            $res = $c->day . ' ' . $bName . ' ' . $c->year;
            
            if ($withDay) {
                $hName = $hari[$c->dayOfWeek] ?? '';
                $res = $hName . ', ' . $res;
            }
            return $res;
        } catch (\Throwable $e) {
            return (string)$date;
        }
    }
}

if (!function_exists('format_bulan_indonesia')) {
    function format_bulan_indonesia($monthKey): string
    {
        if (empty($monthKey)) return '';
        try {
            $engToId = [
                'january' => 'Januari', 'february' => 'Februari', 'march' => 'Maret',
                'april' => 'April', 'may' => 'Mei', 'june' => 'Juni',
                'july' => 'Juli', 'august' => 'Agustus', 'september' => 'September',
                'october' => 'Oktober', 'november' => 'November', 'december' => 'Desember',
                'jan' => 'Jan', 'feb' => 'Feb', 'mar' => 'Mar', 'apr' => 'Apr',
                'jun' => 'Jun', 'jul' => 'Jul', 'aug' => 'Agu', 'sep' => 'Sep',
                'oct' => 'Okt', 'nov' => 'Nov', 'dec' => 'Des'
            ];

            if (preg_match('/^(\d{4})[-_\/](\d{1,2})/', (string)$monthKey, $m)) {
                $year = (int)$m[1];
                $month = (int)$m[2];
                return nama_bulan_indonesia($month) . ' ' . $year;
            }

            $str = (string)$monthKey;
            foreach ($engToId as $eng => $idn) {
                $str = preg_replace('/\b' . $eng . '\b/i', $idn, $str);
            }
            return $str;
        } catch (\Throwable $e) {
            return (string)$monthKey;
        }
    }
}
