<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('kategori_konten')) {
            Schema::create('kategori_konten', function (Blueprint $table) {
                $table->id('id_kategori');
                $table->string('nama_kategori', 100);
                $table->string('slug', 120)->unique();
                $table->text('deskripsi')->nullable();
                $table->string('ikon', 50)->default('fa-newspaper');
                $table->integer('urutan')->default(1);
                $table->string('status', 20)->default('Aktif');
                $table->timestamps();
            });

            // Seed standard pastoral categories
            \Illuminate\Support\Facades\DB::table('kategori_konten')->insert([
                ['nama_kategori' => 'Berita & Warta Paroki', 'slug' => 'berita-warta-paroki', 'deskripsi' => 'Liputan kegiatan dan berita terkini di Paroki', 'ikon' => 'fa-newspaper', 'urutan' => 1, 'status' => 'Aktif', 'created_at' => now(), 'updated_at' => now()],
                ['nama_kategori' => 'Pengumuman Resmi Paroki', 'slug' => 'pengumuman-resmi-paroki', 'deskripsi' => 'Pengumuman misa, sakramen, dan sekretariat', 'ikon' => 'fa-bullhorn', 'urutan' => 2, 'status' => 'Aktif', 'created_at' => now(), 'updated_at' => now()],
                ['nama_kategori' => 'Renungan Harian & Rohani', 'slug' => 'renungan-harian-rohani', 'deskripsi' => 'Santapan rohani, renungan injil, dan katekese', 'ikon' => 'fa-book-open', 'urutan' => 3, 'status' => 'Aktif', 'created_at' => now(), 'updated_at' => now()],
                ['nama_kategori' => 'Kategorial & Komunitas', 'slug' => 'kategorial-komunitas', 'deskripsi' => 'Warta kegiatan OMK, WKRI, Legio Mariae, dll.', 'ikon' => 'fa-users', 'urutan' => 4, 'status' => 'Aktif', 'created_at' => now(), 'updated_at' => now()],
                ['nama_kategori' => 'Liturgi & Peribadatan', 'slug' => 'liturgi-peribadatan', 'deskripsi' => 'Pedoman dan jadwal perayaan ekaristi & liturgi', 'ikon' => 'fa-cross', 'urutan' => 5, 'status' => 'Aktif', 'created_at' => now(), 'updated_at' => now()],
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_konten');
    }
};
