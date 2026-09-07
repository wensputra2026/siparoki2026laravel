<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Pastikan kolom dekenat_id, nama_kuasi, kode_kuasi, dll ada di tabel kuasi_paroki.
     */
    public function up(): void
    {
        if (!Schema::hasTable('kuasi_paroki')) {
            return;
        }

        Schema::table('kuasi_paroki', function (Blueprint $table) {
            if (!Schema::hasColumn('kuasi_paroki', 'dekenat_id')) {
                $table->unsignedBigInteger('dekenat_id')->nullable()->after('paroki_id');
            }
            if (!Schema::hasColumn('kuasi_paroki', 'keuskupan_id')) {
                $table->unsignedBigInteger('keuskupan_id')->nullable()->after('dekenat_id');
            }
            if (!Schema::hasColumn('kuasi_paroki', 'nama_kuasi')) {
                $table->string('nama_kuasi', 255)->nullable()->after('keuskupan_id');
            }
            if (!Schema::hasColumn('kuasi_paroki', 'kode_kuasi')) {
                $table->string('kode_kuasi', 50)->nullable()->after('nama_kuasi');
            }
            if (!Schema::hasColumn('kuasi_paroki', 'pastor_administrator')) {
                $table->string('pastor_administrator', 255)->nullable()->after('kode_kuasi');
            }
            if (!Schema::hasColumn('kuasi_paroki', 'lokasi')) {
                $table->text('lokasi')->nullable()->after('pastor_administrator');
            }
            if (!Schema::hasColumn('kuasi_paroki', 'status')) {
                $table->string('status', 50)->nullable()->default('Aktif')->after('lokasi');
            }
            if (!Schema::hasColumn('kuasi_paroki', 'pelindung')) {
                $table->string('pelindung', 255)->nullable()->after('status');
            }
            if (!Schema::hasColumn('kuasi_paroki', 'keterangan')) {
                $table->text('keterangan')->nullable()->after('pelindung');
            }
        });

        // Sync: isi nama_kuasi dari NamaKuasiParoki jika masih kosong
        DB::table('kuasi_paroki')
            ->whereNull('nama_kuasi')
            ->whereNotNull('NamaKuasiParoki')
            ->update([
                'nama_kuasi' => DB::raw('NamaKuasiParoki'),
            ]);

        // Sync: isi kode_kuasi dari KodeKuasiParoki jika masih kosong
        DB::table('kuasi_paroki')
            ->whereNull('kode_kuasi')
            ->whereNotNull('KodeKuasiParoki')
            ->update([
                'kode_kuasi' => DB::raw('KodeKuasiParoki'),
            ]);

        // Sync: isi pastor_administrator dari PastorKuasiParoki jika masih kosong
        DB::table('kuasi_paroki')
            ->whereNull('pastor_administrator')
            ->whereNotNull('PastorKuasiParoki')
            ->update([
                'pastor_administrator' => DB::raw('PastorKuasiParoki'),
            ]);

        // Sync: isi lokasi dari AlamatKuasiParoki jika masih kosong
        DB::table('kuasi_paroki')
            ->whereNull('lokasi')
            ->whereNotNull('AlamatKuasiParoki')
            ->where('AlamatKuasiParoki', '!=', '')
            ->update([
                'lokasi' => DB::raw('AlamatKuasiParoki'),
            ]);

        // Sync: isi status dari StatusAktif
        DB::table('kuasi_paroki')
            ->whereNull('status')
            ->update([
                'status' => DB::raw("CASE WHEN StatusAktif IN ('N','Nonaktif','0') THEN 'Nonaktif' ELSE 'Aktif' END"),
            ]);

        // Sync: isi keterangan dari Keterangan lama
        DB::table('kuasi_paroki')
            ->whereNull('keterangan')
            ->whereNotNull('Keterangan')
            ->where('Keterangan', '!=', '')
            ->update([
                'keterangan' => DB::raw('Keterangan'),
            ]);
    }

    public function down(): void
    {
        Schema::table('kuasi_paroki', function (Blueprint $table) {
            $cols = ['dekenat_id', 'keuskupan_id', 'nama_kuasi', 'kode_kuasi', 'pastor_administrator', 'lokasi', 'status', 'pelindung', 'keterangan'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('kuasi_paroki', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
