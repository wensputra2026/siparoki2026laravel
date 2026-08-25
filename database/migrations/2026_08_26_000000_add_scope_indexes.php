<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan index pada kolom scoping (wilayah_id, kapela_id, kub_id, is_deleted)
     * agar query yang membatasi data per tenant (KUB / Wilayah / Kapela) tetap cepat
     * seiring bertambahnya jumlah baris & user.
     *
     * Tabel dibuat secara dinamis (bukan via migration), sehingga tiap penambahan
     * index di-guard dengan hasColumn / hasIndex dan diabaikan bila sudah ada.
     */
    public function up(): void
    {
        $this->ensureIndex('kk_katolik', ['wilayah_id', 'kapela_id', 'kub_id', 'is_deleted']);
        $this->ensureIndex('umat', ['wilayah_id', 'kapela_id', 'kub_id']);
        $this->ensureIndex('iuran', ['wilayah_id', 'kapela_id', 'kub_id', 'is_deleted']);
        $this->ensureIndex('sakramen', ['wilayah_id', 'kapela_id', 'kub_id', 'is_deleted']);
    }

    public function down(): void
    {
        $this->dropIndex('kk_katolik', ['wilayah_id', 'kapela_id', 'kub_id', 'is_deleted']);
        $this->dropIndex('umat', ['wilayah_id', 'kapela_id', 'kub_id']);
        $this->dropIndex('iuran', ['wilayah_id', 'kapela_id', 'kub_id', 'is_deleted']);
        $this->dropIndex('sakramen', ['wilayah_id', 'kapela_id', 'kub_id', 'is_deleted']);
    }

    private function ensureIndex(string $table, array $columns): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $t) use ($table, $columns) {
            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    continue;
                }
                try {
                    if (method_exists(Schema::class, 'hasIndex') && Schema::hasIndex($table, $column)) {
                        continue;
                    }
                    $t->index($column);
                } catch (\Throwable $e) {
                    // Index sudah ada atau tidak didukung — abaikan.
                }
            }
        });
    }

    private function dropIndex(string $table, array $columns): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        Schema::table($table, function (Blueprint $t) use ($table, $columns) {
            foreach ($columns as $column) {
                if (!Schema::hasColumn($table, $column)) {
                    continue;
                }
                try {
                    if (method_exists(Schema::class, 'hasIndex') && !Schema::hasIndex($table, $column)) {
                        continue;
                    }
                    $t->dropIndex([$column]);
                } catch (\Throwable $e) {
                    // Abaikan bila index tidak ada.
                }
            }
        });
    }
};
