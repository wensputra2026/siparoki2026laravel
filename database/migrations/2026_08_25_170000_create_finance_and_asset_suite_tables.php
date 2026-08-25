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
        // 1. Jenis Iuran Table
        if (!Schema::hasTable('jenis_iuran')) {
            Schema::create('jenis_iuran', function (Blueprint $table) {
                $table->increments('id');
                $table->string('kode_iuran', 50)->nullable();
                $table->string('nama_iuran', 150);
                $table->string('kategori_iuran', 100)->nullable()->default('Wajib');
                $table->string('basis_penagihan', 100)->nullable()->default('Per Keluarga (KK)');
                $table->decimal('nominal_default', 15, 2)->default(0);
                $table->string('periode', 50)->nullable()->default('Bulanan');
                $table->tinyInteger('wajib')->default(1);
                $table->tinyInteger('status')->default(1);
                $table->text('keterangan')->nullable();
                $table->tinyInteger('is_deleted')->default(0);
                $table->timestamps();
            });

            DB::table('jenis_iuran')->insert([
                ['kode_iuran' => 'IUR-001', 'nama_iuran' => 'Iuran Wajib Umat Bulanan', 'kategori_iuran' => 'Wajib', 'basis_penagihan' => 'Per Keluarga (KK)', 'nominal_default' => 20000, 'periode' => 'Bulanan', 'wajib' => 1, 'status' => 1, 'keterangan' => 'Iuran operasional paroki & KUB bulanan', 'created_at' => now(), 'updated_at' => now()],
                ['kode_iuran' => 'IUR-002', 'nama_iuran' => 'Dana Solidaritas Kematian / Duka', 'kategori_iuran' => 'Wajib', 'basis_penagihan' => 'Per Keluarga (KK)', 'nominal_default' => 10000, 'periode' => 'Insidental', 'wajib' => 1, 'status' => 1, 'keterangan' => 'Dana santunan duka cita keluarga jemaat', 'created_at' => now(), 'updated_at' => now()],
                ['kode_iuran' => 'IUR-003', 'nama_iuran' => 'Iuran Pesta Pelindung Paroki', 'kategori_iuran' => 'Sukarela', 'basis_penagihan' => 'Per Keluarga (KK)', 'nominal_default' => 50000, 'periode' => 'Tahunan', 'wajib' => 0, 'status' => 1, 'keterangan' => 'Partisipasi perayaan pesta pelindung St. Vinsensius', 'created_at' => now(), 'updated_at' => now()],
                ['kode_iuran' => 'IUR-004', 'nama_iuran' => 'Iuran Aksi Puasa Pembangunan (APP)', 'kategori_iuran' => 'Wajib', 'basis_penagihan' => 'Per Jiwa / Umat', 'nominal_default' => 5000, 'periode' => 'Tahunan', 'wajib' => 1, 'status' => 1, 'keterangan' => 'Kotak APP masa prapaskah', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // 2. Iuran Umat Table
        if (!Schema::hasTable('iuran')) {
            Schema::create('iuran', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('kk_id')->nullable();
                $table->unsignedInteger('id_kk')->nullable();
                $table->string('no_kk', 50)->nullable();
                $table->string('nama_kepala', 150)->nullable();
                $table->unsignedInteger('jenis_iuran_id')->nullable();
                $table->string('nama_iuran', 150)->nullable();
                $table->integer('tahun')->default(2026);
                $table->integer('bulan')->nullable();
                $table->string('bulan_lunas', 255)->nullable();
                $table->decimal('jumlah', 15, 2)->default(0);
                $table->decimal('total_jumlah', 15, 2)->default(0);
                $table->date('tanggal_bayar')->nullable();
                $table->string('metode_pembayaran', 100)->nullable()->default('Tunai');
                $table->string('status_bayar', 50)->nullable()->default('Lunas');
                $table->string('kolektor', 150)->nullable();
                $table->string('petugas', 150)->nullable();
                $table->string('bukti_bayar', 255)->nullable();
                $table->text('keterangan')->nullable();
                $table->tinyInteger('is_deleted')->default(0);
                $table->timestamps();
            });
        }

        // 3. Kolekte Misa Table
        if (!Schema::hasTable('kolekte')) {
            Schema::create('kolekte', function (Blueprint $table) {
                $table->increments('id');
                $table->date('tanggal');
                $table->string('kategori_misa', 100);
                $table->decimal('nominal', 15, 2)->default(0);
                $table->string('petugas_penghitung', 150)->nullable();
                $table->string('lokasi_misa', 150)->nullable()->default('Gereja Paroki');
                $table->text('keterangan')->nullable();
                $table->timestamps();
            });

            DB::table('kolekte')->insert([
                ['tanggal' => now()->subDays(2)->format('Y-m-d'), 'kategori_misa' => 'Misa Hari Minggu I (Pagi)', 'nominal' => 2850000, 'petugas_penghitung' => 'Tim Kolekte DPP & Misdinar', 'lokasi_misa' => 'Gereja Paroki Benlutu', 'keterangan' => 'Kolekte persembahan kantong pertama', 'created_at' => now(), 'updated_at' => now()],
                ['tanggal' => now()->subDays(2)->format('Y-m-d'), 'kategori_misa' => 'Misa Hari Minggu II (Sore)', 'nominal' => 1420000, 'petugas_penghitung' => 'Bendahara & Koster', 'lokasi_misa' => 'Gereja Paroki Benlutu', 'keterangan' => 'Kolekte persembahan kantong kedua', 'created_at' => now(), 'updated_at' => now()],
                ['tanggal' => now()->subDays(9)->format('Y-m-d'), 'kategori_misa' => 'Misa Stasi / Wilayah', 'nominal' => 975000, 'petugas_penghitung' => 'Pengurus Stasi St. Mikael', 'lokasi_misa' => 'Stasi St. Mikael', 'keterangan' => 'Persembahan umat stasi', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // 4. Intensi Misa Table
        if (!Schema::hasTable('intensi_misa')) {
            Schema::create('intensi_misa', function (Blueprint $table) {
                $table->increments('id');
                $table->string('nama_pemohon', 150);
                $table->string('kategori_intensi', 100)->default('Ucapan Syukur');
                $table->text('deskripsi');
                $table->date('tanggal_misa');
                $table->decimal('nominal_stipendium', 15, 2)->default(0);
                $table->string('status_pembayaran', 50)->default('Lunas');
                $table->timestamps();
            });

            DB::table('intensi_misa')->insert([
                ['nama_pemohon' => 'Keluarga Bria - Taolin', 'kategori_intensi' => 'Ucapan Syukur', 'deskripsi' => 'Ucapan syukur atas kelulusan dan kesehatan seluruh anggota keluarga', 'tanggal_misa' => now()->addDays(2)->format('Y-m-d'), 'nominal_stipendium' => 100000, 'status_pembayaran' => 'Lunas', 'created_at' => now(), 'updated_at' => now()],
                ['nama_pemohon' => 'Keluarga Fransiskus Xaverius', 'kategori_intensi' => 'Peringatan Arwah', 'deskripsi' => 'Mendoakan ketenteraman arwah Bpk. Petrus dan seluruh leluhur keluarga', 'tanggal_misa' => now()->addDays(3)->format('Y-m-d'), 'nominal_stipendium' => 100000, 'status_pembayaran' => 'Lunas', 'created_at' => now(), 'updated_at' => now()],
                ['nama_pemohon' => 'Keluarga Yosef Neonbeni', 'kategori_intensi' => 'Permohonan Khusus', 'deskripsi' => 'Mohon kesembuhan bagi anggota keluarga yang sedang dirawat di rumah sakit', 'tanggal_misa' => now()->addDays(5)->format('Y-m-d'), 'nominal_stipendium' => 50000, 'status_pembayaran' => 'Lunas', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // 5. Master COA Akuntansi Table
        if (!Schema::hasTable('master_coa')) {
            Schema::create('master_coa', function (Blueprint $table) {
                $table->increments('id');
                $table->string('kode_akun', 20)->unique();
                $table->string('nama_akun', 150);
                $table->enum('jenis', ['pemasukan', 'pengeluaran']);
                $table->text('deskripsi')->nullable();
                $table->tinyInteger('status')->default(1);
                $table->timestamps();
            });

            DB::table('master_coa')->insert([
                ['kode_akun' => '401', 'nama_akun' => 'Kolekte Misa I', 'jenis' => 'pemasukan', 'deskripsi' => 'Persembahan kantong kolekte pertama', 'created_at' => now(), 'updated_at' => now()],
                ['kode_akun' => '402', 'nama_akun' => 'Kolekte Misa II', 'jenis' => 'pemasukan', 'deskripsi' => 'Persembahan kantong kolekte kedua/khusus', 'created_at' => now(), 'updated_at' => now()],
                ['kode_akun' => '403', 'nama_akun' => 'Stipendium Misa & Intensi', 'jenis' => 'pemasukan', 'deskripsi' => 'Intensi permohonan / arwah', 'created_at' => now(), 'updated_at' => now()],
                ['kode_akun' => '404', 'nama_akun' => 'Donasi & Persembahan Umat', 'jenis' => 'pemasukan', 'deskripsi' => 'Sumbangan sukarela donatur', 'created_at' => now(), 'updated_at' => now()],
                ['kode_akun' => '405', 'nama_akun' => 'Iuran KUB & Wilayah', 'jenis' => 'pemasukan', 'deskripsi' => 'Iuran bulanan umat dan KUB', 'created_at' => now(), 'updated_at' => now()],
                ['kode_akun' => '501', 'nama_akun' => 'Operasional Liturgi & Sakramen', 'jenis' => 'pengeluaran', 'deskripsi' => 'Lilin, anggur, hosti, perlengkapan altare', 'created_at' => now(), 'updated_at' => now()],
                ['kode_akun' => '502', 'nama_akun' => 'Pastoran & Honorarium', 'jenis' => 'pengeluaran', 'deskripsi' => 'Insentif petugas, koster, sekretariat', 'created_at' => now(), 'updated_at' => now()],
                ['kode_akun' => '503', 'nama_akun' => 'Pemeliharaan Gedung & Sarana', 'jenis' => 'pengeluaran', 'deskripsi' => 'Perbaikan fisik gereja & sarpras', 'created_at' => now(), 'updated_at' => now()],
                ['kode_akun' => '504', 'nama_akun' => 'Seksi Pelayanan Sosial / Caritas', 'jenis' => 'pengeluaran', 'deskripsi' => 'Bantuan umat sakit & berkekurangan', 'created_at' => now(), 'updated_at' => now()],
                ['kode_akun' => '505', 'nama_akun' => 'Pembinaan Katekese & OMK', 'jenis' => 'pengeluaran', 'deskripsi' => 'Kegiatan OMK, BIA, BIR, Katekumen', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // 6. Ensure columns on Keuangan table
        if (Schema::hasTable('keuangan')) {
            Schema::table('keuangan', function (Blueprint $table) {
                if (!Schema::hasColumn('keuangan', 'kode_coa')) {
                    $table->string('kode_coa', 20)->nullable()->after('kategori');
                }
                if (!Schema::hasColumn('keuangan', 'status_approval')) {
                    $table->string('status_approval', 30)->default('approved')->after('status');
                }
                if (!Schema::hasColumn('keuangan', 'approved_by')) {
                    $table->integer('approved_by')->nullable()->after('status_approval');
                }
                if (!Schema::hasColumn('keuangan', 'approved_at')) {
                    $table->dateTime('approved_at')->nullable()->after('approved_by');
                }
            });
        }

        // 7. Ensure Kategori Aset Table & Aset Table
        if (!Schema::hasTable('kategori_aset')) {
            Schema::create('kategori_aset', function (Blueprint $table) {
                $table->increments('id');
                $table->string('nama_kategori', 100)->unique();
                $table->string('keterangan', 255)->nullable();
                $table->timestamps();
            });

            $defaults = ['Tanah', 'Bangunan', 'Kendaraan', 'Inventaris', 'Elektronik & Sound System', 'Perlengkapan Liturgi & Ibadah', 'Lainnya'];
            foreach ($defaults as $d) {
                DB::table('kategori_aset')->insert([
                    'nama_kategori' => $d,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        if (Schema::hasTable('aset')) {
            Schema::table('aset', function (Blueprint $table) {
                if (!Schema::hasColumn('aset', 'kategori')) {
                    $table->string('kategori', 100)->nullable()->after('nama_aset');
                }
                if (!Schema::hasColumn('aset', 'jumlah')) {
                    $table->integer('jumlah')->default(1)->after('kondisi');
                }
                if (!Schema::hasColumn('aset', 'satuan')) {
                    $table->string('satuan', 50)->default('Unit')->after('jumlah');
                }
                if (!Schema::hasColumn('aset', 'foto')) {
                    $table->string('foto', 255)->nullable()->after('satuan');
                }
                if (!Schema::hasColumn('aset', 'penanggung_jawab')) {
                    $table->string('penanggung_jawab', 150)->nullable()->after('lokasi');
                }
                if (!Schema::hasColumn('aset', 'keterangan')) {
                    $table->text('keterangan')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
