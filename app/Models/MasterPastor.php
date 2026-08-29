<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\HasIndirectId;

class MasterPastor extends Model
{
    use HasIndirectId;

    protected $table = 'master_pastor';
    protected $fillable = [
        'nama_pastor',
        'nama_singkat',
        'gelar_depan',
        'gelar_belakang',
        'jabatan',
        'jenis_imam',
        'ordo_kongregasi',
        'keuskupan',
        'keuskupan_id',
        'dekenat_id',
        'paroki_id',
        'no_hp',
        'email',
        'foto',
        'tanggal_tahbisan',
        'keterangan',
        'status',
        'jenis_tempat_tugas',
        'periode_mulai',
        'periode_selesai',
        'tampil_frontend',
        'urutan',
        'catatan_pelayanan',
        'riwayat_tambahan',
        'nama_baptis',
        'nama_lahir',
        'tempat_lahir',
        'tanggal_lahir',
        'tgl_tahbisan_diakon',
        'ordo',
        'tgl_tahbisan',
        'uskup_penahbis',
        'tempat_tahbisan',
        'motto_tahbisan',
        'paroki_tugas',
        'pendidikan_terakhir',
        'seminari_tinggi',
        'sedang_bertugas',
        'is_deleted',
    ];

    protected function casts(): array
    {
        return [
            'sedang_bertugas' => 'boolean',
            'is_deleted' => 'boolean',
            'tgl_tahbisan' => 'date',
            'tanggal_tahbisan' => 'date',
            'tanggal_lahir' => 'date',
        ];
    }

    protected $appends = ['nama_lengkap_gelar', 'hashid'];

    /**
     * Format Nama Pastor dengan Gelar Depan (RD. / RP.) dan Gelar Belakang Ordo (misal: CMF, SVD).
     */
    public function getNamaLengkapGelarAttribute(): string
    {
        return self::formatNama($this);
    }

    /**
     * Static Helper Format Nama Pastor Standar Gereja Katolik.
     * - Imam Diosesan / Projo: RD. [Nama] [Gelar Akademik]
     * - Imam Ordo / Kongregasi: RP. [Nama], [ORDO] [Gelar Akademik]
     * - Uskup: Mgr. [Nama]
     */
    public static function formatNama($pastor, ?string $fallbackOrdo = null, ?string $fallbackJenis = null, ?string $fallbackGelar = null): string
    {
        if (is_object($pastor)) {
            $rawName = trim((string)($pastor->nama_pastor ?? $pastor->nama ?? ''));
            $ordo = trim((string)($pastor->ordo ?? $pastor->ordo_kongregasi ?? $fallbackOrdo ?? ''));
            $jenisImam = strtolower(trim((string)($pastor->jenis_imam ?? $fallbackJenis ?? '')));
            $gelarDepan = trim((string)($pastor->gelar_depan ?? $fallbackGelar ?? ''));
            $gelarBelakang = trim((string)($pastor->gelar_belakang ?? $pastor->gelar ?? ''));
        } elseif (is_array($pastor)) {
            $rawName = trim((string)($pastor['nama_pastor'] ?? $pastor['nama'] ?? ''));
            $ordo = trim((string)($pastor['ordo'] ?? $pastor['ordo_kongregasi'] ?? $fallbackOrdo ?? ''));
            $jenisImam = strtolower(trim((string)($pastor['jenis_imam'] ?? $fallbackJenis ?? '')));
            $gelarDepan = trim((string)($pastor['gelar_depan'] ?? $fallbackGelar ?? ''));
            $gelarBelakang = trim((string)($pastor['gelar_belakang'] ?? $pastor['gelar'] ?? ''));
        } else {
            $rawName = trim((string)$pastor);
            $ordo = trim((string)$fallbackOrdo);
            $jenisImam = strtolower(trim((string)$fallbackJenis));
            $gelarDepan = trim((string)$fallbackGelar);
            $gelarBelakang = '';
        }

        if (empty($rawName)) return '';

        // Deteksi ordo jika tertulis di dalam kurung atau koma di akhir nama
        if (empty($ordo) && preg_match('/[,\(]\s*([A-Za-z]+)\s*[\)]?$/', $rawName, $m)) {
            $potentialOrdo = strtoupper($m[1]);
            if (in_array($potentialOrdo, ['CMF', 'SVD', 'OFM', 'OCARM', 'SJ', 'CSSR', 'MSF', 'PR', 'CP', 'OSB', 'SCJ', 'SX', 'OMI', 'SDB', 'CICM', 'CDD', 'CM'])) {
                if ($potentialOrdo !== 'PR') {
                    $ordo = $potentialOrdo;
                }
                $rawName = trim(preg_replace('/[,\(]\s*[A-Za-z]+\s*[\)]?$/', '', $rawName));
            }
        }

        // Tentukan Gelar Depan
        if (!empty($gelarDepan)) {
            $gd = rtrim($gelarDepan, '.');
            if (in_array(strtoupper($gd), ['RD', 'RP', 'FR', 'MGR'])) {
                $gelarDepan = strtoupper($gd) . '.';
            } elseif (in_array(strtolower($gd), ['pater', 'p'])) {
                $gelarDepan = 'RP.';
            } elseif (in_array(strtolower($gd), ['romo'])) {
                $gelarDepan = 'RD.';
            }
        } else {
            if (!empty($ordo) || str_contains($jenisImam, 'religius') || str_contains($jenisImam, 'ordo') || str_contains($jenisImam, 'kongregasi') || preg_match('/^(P\.|RP\.|RP|Pater)/i', $rawName)) {
                $gelarDepan = 'RP.';
            } elseif (str_contains($jenisImam, 'uskup') || str_contains($jenisImam, 'episkopal')) {
                $gelarDepan = 'Mgr.';
            } else {
                $gelarDepan = 'RD.';
            }
        }

        // Bersihkan duplikasi gelar di depan nama
        $cleanName = preg_replace('/^(RD\.|RP\.|P\.|RD|RP|Pater|Romo|Fr\.|Frater|Mgr\.)\s+/i', '', $rawName);

        // Bersihkan duplikasi ordo di belakang nama
        if (!empty($ordo)) {
            $cleanName = preg_replace('/,\s*' . preg_quote($ordo, '/') . '$/i', '', $cleanName);
        }

        $formatted = $gelarDepan . ' ' . $cleanName;

        if (!empty($ordo) && strtoupper($ordo) !== 'PR' && strtoupper($ordo) !== 'PROJO' && !str_contains(strtoupper($formatted), strtoupper($ordo))) {
            $formatted .= ', ' . strtoupper($ordo);
        }

        if (!empty($gelarBelakang) && !str_contains($formatted, $gelarBelakang)) {
            $formatted .= ', ' . $gelarBelakang;
        }

        return $formatted;
    }

    // ─── Relasi Hierarki Pastor ────────────────────────────────────────────────

    public function keuskupan()
    {
        return $this->belongsTo(Keuskupan::class, 'keuskupan_id', 'id_keuskupan');
    }

    public function dekenat()
    {
        return $this->belongsTo(Dekenat::class, 'dekenat_id', 'id_dekenat');
    }

    /** Alias dekenat() menggunakan istilah Kevikepan */
    public function kevikepan()
    {
        return $this->belongsTo(Dekenat::class, 'dekenat_id', 'id_dekenat');
    }

    public function paroki()
    {
        return $this->belongsTo(Paroki::class, 'paroki_id', 'id_paroki');
    }
}
