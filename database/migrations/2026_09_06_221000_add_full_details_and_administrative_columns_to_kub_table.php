<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom lengkap dan administratif pada tabel kub
        if (Schema::hasTable('kub')) {
            Schema::table('kub', function (Blueprint $table) {
                if (!Schema::hasColumn('kub', 'alamat')) {
                    $table->text('alamat')->nullable()->after('nama_pelindung');
                }
                if (!Schema::hasColumn('kub', 'lokasi')) {
                    $table->text('lokasi')->nullable()->after('alamat');
                }
                if (!Schema::hasColumn('kub', 'jadwal_ibadat')) {
                    $table->string('jadwal_ibadat')->nullable()->after('lokasi');
                }
                if (!Schema::hasColumn('kub', 'pelindung')) {
                    $table->string('pelindung')->nullable()->after('nama_pelindung');
                }
                if (!Schema::hasColumn('kub', 'provinsi_id')) {
                    $table->unsignedBigInteger('provinsi_id')->nullable();
                }
                if (!Schema::hasColumn('kub', 'kabupaten_id')) {
                    $table->unsignedBigInteger('kabupaten_id')->nullable();
                }
                if (!Schema::hasColumn('kub', 'kecamatan_id')) {
                    $table->unsignedBigInteger('kecamatan_id')->nullable();
                }
                if (!Schema::hasColumn('kub', 'desa_id')) {
                    $table->unsignedBigInteger('desa_id')->nullable();
                }
            });
        }

        // 2. Perbarui nama role Ketua KUB menjadi Admin KUB
        if (Schema::hasTable('roles')) {
            DB::table('roles')
                ->where('nama_role', 'Ketua KUB')
                ->orWhere('slug', 'ketua_kub')
                ->update([
                    'nama_role' => 'Admin KUB',
                    'slug' => 'admin_kub',
                    'deskripsi' => 'Admin & Pengurus Komunitas Umat Basis (KUB)'
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('kub')) {
            Schema::table('kub', function (Blueprint $table) {
                $columns = ['alamat', 'lokasi', 'jadwal_ibadat', 'pelindung', 'provinsi_id', 'kabupaten_id', 'kecamatan_id', 'desa_id'];
                foreach ($columns as $column) {
                    if (Schema::hasColumn('kub', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('roles')) {
            DB::table('roles')
                ->where('nama_role', 'Admin KUB')
                ->orWhere('slug', 'admin_kub')
                ->update([
                    'nama_role' => 'Ketua KUB',
                    'slug' => 'ketua_kub',
                    'deskripsi' => 'Ketua & Pengurus Komunitas Umat Basis (KUB)'
                ]);
        }
    }
};
