<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Sinkronkan skema master_pastor dengan field yang dikirim form PastorForm.vue.
     * Banyak kolom (ordo, tgl_tahbisan, nama_baptis, tempat_lahir, tanggal_lahir, dll)
     * belum ada di tabel sehingga updatePastor menyaringnya dan data tidak tersimpan.
     */
    public function up(): void
    {
        if (!Schema::hasTable('master_pastor')) {
            return;
        }

        Schema::table('master_pastor', function (Blueprint $table) {
            if (!Schema::hasColumn('master_pastor', 'nama_baptis')) {
                $table->string('nama_baptis', 150)->nullable();
            }
            if (!Schema::hasColumn('master_pastor', 'nama_lahir')) {
                $table->string('nama_lahir', 150)->nullable();
            }
            if (!Schema::hasColumn('master_pastor', 'tempat_lahir')) {
                $table->string('tempat_lahir', 150)->nullable();
            }
            if (!Schema::hasColumn('master_pastor', 'tanggal_lahir')) {
                $table->date('tanggal_lahir')->nullable();
            }
            if (!Schema::hasColumn('master_pastor', 'tgl_tahbisan_diakon')) {
                $table->date('tgl_tahbisan_diakon')->nullable();
            }
            if (!Schema::hasColumn('master_pastor', 'ordo')) {
                $table->string('ordo', 100)->nullable();
            }
            if (!Schema::hasColumn('master_pastor', 'tgl_tahbisan')) {
                $table->date('tgl_tahbisan')->nullable();
            }
            if (!Schema::hasColumn('master_pastor', 'uskup_penahbis')) {
                $table->string('uskup_penahbis', 150)->nullable();
            }
            if (!Schema::hasColumn('master_pastor', 'tempat_tahbisan')) {
                $table->string('tempat_tahbisan', 150)->nullable();
            }
            if (!Schema::hasColumn('master_pastor', 'motto_tahbisan')) {
                $table->text('motto_tahbisan')->nullable();
            }
            if (!Schema::hasColumn('master_pastor', 'paroki_tugas')) {
                $table->string('paroki_tugas', 150)->nullable();
            }
            if (!Schema::hasColumn('master_pastor', 'pendidikan_terakhir')) {
                $table->string('pendidikan_terakhir', 100)->nullable();
            }
            if (!Schema::hasColumn('master_pastor', 'seminari_tinggi')) {
                $table->string('seminari_tinggi', 150)->nullable();
            }
        });

        // Salin data lama agar tidak hilang saat berpindah ke nama kolom baru.
        if (Schema::hasColumn('master_pastor', 'ordo_kongregasi')) {
            DB::statement("UPDATE master_pastor SET ordo = ordo_kongregasi WHERE (ordo IS NULL OR ordo = '') AND ordo_kongregasi IS NOT NULL AND ordo_kongregasi != ''");
        }
        if (Schema::hasColumn('master_pastor', 'tanggal_tahbisan')) {
            DB::statement("UPDATE master_pastor SET tgl_tahbisan = tanggal_tahbisan WHERE tgl_tahbisan IS NULL AND tanggal_tahbisan IS NOT NULL");
        }

        // Pastikan cache kolom (schemaColumns) ter-refresh agar kolom baru dikenali.
        try {
            \Illuminate\Support\Facades\Cache::forget('schema_columns_master_pastor');
        } catch (\Throwable $e) {
            // abaikan
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('master_pastor')) {
            return;
        }

        Schema::table('master_pastor', function (Blueprint $table) {
            $cols = [
                'nama_baptis', 'nama_lahir', 'tempat_lahir', 'tanggal_lahir',
                'tgl_tahbisan_diakon', 'ordo', 'tgl_tahbisan', 'uskup_penahbis',
                'tempat_tahbisan', 'motto_tahbisan', 'paroki_tugas',
                'pendidikan_terakhir', 'seminari_tinggi',
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('master_pastor', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
