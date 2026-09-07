<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // -----------------------------------------------------------------
        // 1. TABEL ADMINISTRASI PEMERINTAHAN (KEMENDAGRI / CAHYADSN)
        // -----------------------------------------------------------------
        if (!Schema::hasTable('wilayah_provinsis')) {
            Schema::create('wilayah_provinsis', function (Blueprint $table) {
                $table->char('kode', 2)->primary();
                $table->string('nama', 100);
            });
        }

        if (!Schema::hasTable('wilayah_kabupatens')) {
            Schema::create('wilayah_kabupatens', function (Blueprint $table) {
                $table->char('kode', 5)->primary();
                $table->char('provinsi_kode', 2)->index();
                $table->string('nama', 100);
                $table->foreign('provinsi_kode')->references('kode')->on('wilayah_provinsis')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('wilayah_kecamatans')) {
            Schema::create('wilayah_kecamatans', function (Blueprint $table) {
                $table->char('kode', 8)->primary();
                $table->char('kabupaten_kode', 5)->index();
                $table->string('nama', 100);
                $table->foreign('kabupaten_kode')->references('kode')->on('wilayah_kabupatens')->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('wilayah_desas')) {
            Schema::create('wilayah_desas', function (Blueprint $table) {
                $table->char('kode', 13)->primary();
                $table->char('kecamatan_kode', 8)->index();
                $table->string('nama', 100);
                $table->char('kode_pos', 5)->nullable();
                $table->foreign('kecamatan_kode')->references('kode')->on('wilayah_kecamatans')->cascadeOnDelete();
            });
        }

        // -----------------------------------------------------------------
        // 2. TABEL HIERARKI GEREJAWI (DENGAN ALAMAT SIPIL LENGKAP)
        // -----------------------------------------------------------------

        // A. KEUSKUPAN
        if (!Schema::hasTable('keuskupans')) {
            Schema::create('keuskupans', function (Blueprint $table) {
                $table->id();
                $table->string('kode', 30)->index();
                $table->string('nama', 150);
                $table->string('nama_latin', 180)->nullable();
                $table->string('nama_uskup', 150)->nullable();
                $table->string('tipe', 50)->default('Keuskupan Sufragan');
                
                // 4 Level Wilayah Sipil (Pusat Kuria Keuskupan)
                $table->char('provinsi_kode', 2)->nullable();
                $table->char('kabupaten_kode', 5)->nullable();
                $table->char('kecamatan_kode', 8)->nullable();
                $table->char('desa_kode', 13)->nullable();

                $table->text('alamat_kantor')->nullable();
                $table->string('kontak', 100)->nullable();
                $table->boolean('is_aktif')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $this->addWilayahForeignKeys($table);
            });
        }

        // B. KEVIKEPAN / DEKENAT
        if (!Schema::hasTable('kevikepans')) {
            Schema::create('kevikepans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('keuskupan_id')->constrained('keuskupans')->cascadeOnDelete();
                $table->string('kode', 30)->index();
                $table->string('nama', 150);
                $table->string('nama_vikep', 150)->nullable();

                // 4 Level Wilayah Sipil (Kantor Kevikepan)
                $table->char('provinsi_kode', 2)->nullable();
                $table->char('kabupaten_kode', 5)->nullable();
                $table->char('kecamatan_kode', 8)->nullable();
                $table->char('desa_kode', 13)->nullable();

                $table->text('alamat_kantor')->nullable();
                $table->string('kontak', 100)->nullable();
                $table->boolean('is_aktif')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $this->addWilayahForeignKeys($table);
            });
        }

        // C. PAROKI / KUASI PAROKI
        if (!Schema::hasTable('parokis')) {
            Schema::create('parokis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('kevikepan_id')->constrained('kevikepans')->cascadeOnDelete();
                $table->string('kode', 30)->index();
                $table->string('nama', 150);
                $table->enum('tipe', ['Paroki', 'Kuasi Paroki'])->default('Paroki');
                $table->string('pelindung', 150)->nullable();
                $table->string('pastor_kepala', 150)->nullable();

                // 4 Level Wilayah Sipil (Lokasi Pastoran / Gereja Paroki)
                $table->char('provinsi_kode', 2)->nullable();
                $table->char('kabupaten_kode', 5)->nullable();
                $table->char('kecamatan_kode', 8)->nullable();
                $table->char('desa_kode', 13)->nullable();

                $table->text('alamat')->nullable();
                $table->string('kontak', 100)->nullable();
                $table->boolean('is_aktif')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $this->addWilayahForeignKeys($table);
            });
        }

        // D. STASI / KAPELA
        if (!Schema::hasTable('stasis')) {
            Schema::create('stasis', function (Blueprint $table) {
                $table->id();
                $table->foreignId('paroki_id')->constrained('parokis')->cascadeOnDelete();
                $table->string('kode', 30)->index();
                $table->string('nama', 150);
                $table->enum('tipe', ['Stasi', 'Kapela'])->default('Stasi');
                $table->string('pelindung', 100)->nullable();

                // 4 Level Wilayah Sipil (Lokasi Gedung Kapela/Stasi)
                $table->char('provinsi_kode', 2)->nullable();
                $table->char('kabupaten_kode', 5)->nullable();
                $table->char('kecamatan_kode', 8)->nullable();
                $table->char('desa_kode', 13)->nullable();

                $table->text('alamat')->nullable();
                $table->boolean('is_aktif')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $this->addWilayahForeignKeys($table);
            });
        }

        // E. WILAYAH ROHANI
        if (!Schema::hasTable('wilayahs')) {
            Schema::create('wilayahs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('paroki_id')->constrained('parokis')->cascadeOnDelete();
                $table->string('kode', 30)->index();
                $table->string('nama', 150);
                $table->string('nomor_wilayah', 20)->nullable();
                $table->string('nama_koordinator', 150)->nullable();

                // 4 Level Wilayah Sipil (Cakupan Teritori Wilayah)
                $table->char('provinsi_kode', 2)->nullable();
                $table->char('kabupaten_kode', 5)->nullable();
                $table->char('kecamatan_kode', 8)->nullable();
                $table->char('desa_kode', 13)->nullable();

                $table->boolean('is_aktif')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $this->addWilayahForeignKeys($table);
            });
        }

        // F. KUB (KOMUNITAS UMAT BASIS)
        if (!Schema::hasTable('kubs')) {
            Schema::create('kubs', function (Blueprint $table) {
                $table->id();
                $table->string('kode', 50)->index();
                $table->string('nama', 150);

                // Induk Gerejawi (XOR: Salah satu antara Stasi atau Wilayah)
                $table->foreignId('stasi_id')->nullable()->constrained('stasis')->nullOnDelete();
                $table->foreignId('wilayah_id')->nullable()->constrained('wilayahs')->nullOnDelete();

                // 4 Level Wilayah Sipil (Titik Pusat Komunitas Basis)
                $table->char('provinsi_kode', 2)->nullable();
                $table->char('kabupaten_kode', 5)->nullable();
                $table->char('kecamatan_kode', 8)->nullable();
                $table->char('desa_kode', 13)->nullable();

                $table->string('dusun', 100)->nullable();
                $table->string('rw', 10)->nullable();
                $table->string('rt', 10)->nullable();
                $table->string('nama_ketua', 150)->nullable();
                $table->boolean('is_aktif')->default(true);
                $table->timestamps();
                $table->softDeletes();

                $this->addWilayahForeignKeys($table);
            });
        }
    }

    /**
     * Helper membuat foreign key ke 4 tingkatan wilayah sipil
     */
    private function addWilayahForeignKeys(Blueprint $table): void
    {
        $table->foreign('provinsi_kode')->references('kode')->on('wilayah_provinsis')->nullOnDelete();
        $table->foreign('kabupaten_kode')->references('kode')->on('wilayah_kabupatens')->nullOnDelete();
        $table->foreign('kecamatan_kode')->references('kode')->on('wilayah_kecamatans')->nullOnDelete();
        $table->foreign('desa_kode')->references('kode')->on('wilayah_desas')->nullOnDelete();
    }

    public function down(): void
    {
        Schema::dropIfExists('kubs');
        Schema::dropIfExists('wilayahs');
        Schema::dropIfExists('stasis');
        Schema::dropIfExists('parokis');
        Schema::dropIfExists('kevikepans');
        Schema::dropIfExists('keuskupans');
        Schema::dropIfExists('wilayah_desas');
        Schema::dropIfExists('wilayah_kecamatans');
        Schema::dropIfExists('wilayah_kabupatens');
        Schema::dropIfExists('wilayah_provinsis');
    }
};
