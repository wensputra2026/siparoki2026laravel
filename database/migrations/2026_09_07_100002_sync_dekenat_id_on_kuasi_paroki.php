<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Sync dekenat_id di kuasi_paroki dari paroki induknya,
     * jika dekenat_id saat ini masih NULL.
     */
    public function up(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('kuasi_paroki')) {
            return;
        }

        // Update kuasi_paroki.dekenat_id dari paroki.dekenat_id (join)
        DB::statement("
            UPDATE kuasi_paroki kp
            INNER JOIN paroki p ON p.id_paroki = kp.paroki_id
            SET kp.dekenat_id = p.dekenat_id
            WHERE kp.dekenat_id IS NULL
              AND p.dekenat_id IS NOT NULL
        ");

        // Juga update keuskupan_id dari paroki jika belum terisi
        if (\Illuminate\Support\Facades\Schema::hasColumn('kuasi_paroki', 'keuskupan_id')) {
            DB::statement("
                UPDATE kuasi_paroki kp
                INNER JOIN paroki p ON p.id_paroki = kp.paroki_id
                SET kp.keuskupan_id = p.keuskupan_id
                WHERE kp.keuskupan_id IS NULL
                  AND p.keuskupan_id IS NOT NULL
            ");
        }
    }

    public function down(): void
    {
        // Tidak bisa di-rollback
    }
};
