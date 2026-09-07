<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $replacements = [
            'ΓÇ£' => '“',
            'ΓÇ¥' => '”',
            'ΓÇÖ' => '’',
            'ΓÇÿ' => '‘',
            'ΓÇö' => '—',
            'ΓÇô' => '–',
            'ΓÇª' => '…',
            'Çœ' => '“',
            'Ç ' => '”',
        ];

        $tables = [
            'kapela',
            'paroki',
            'wilayah',
            'kub',
            'konten',
            'profil_paroki',
            'jadwal_misa',
            'kegiatan',
            'arsip_digital',
            'rapat',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            $cols = Schema::getColumnListing($table);
            $textColumns = [];

            foreach ($cols as $col) {
                try {
                    $type = Schema::getColumnType($table, $col);
                    if (in_array($type, ['string', 'text', 'mediumtext', 'longtext'], true)) {
                        $textColumns[] = $col;
                    }
                } catch (\Throwable $e) {
                    if (in_array($col, ['sejarah', 'keterangan', 'deskripsi', 'visi', 'misi', 'lokasi', 'alamat', 'judul', 'isi', 'nama_kapela', 'nama_paroki', 'nama_wilayah', 'nama_kub'], true)) {
                        $textColumns[] = $col;
                    }
                }
            }

            foreach ($textColumns as $col) {
                foreach ($replacements as $bad => $good) {
                    try {
                        DB::table($table)
                            ->where($col, 'like', "%{$bad}%")
                            ->update([
                                $col => DB::raw("REPLACE({$col}, " . DB::getPdo()->quote($bad) . ", " . DB::getPdo()->quote($good) . ")")
                            ]);
                    } catch (\Throwable $e) {
                        // Silent catch
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
