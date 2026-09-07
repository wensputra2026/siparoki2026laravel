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
        if (!Schema::hasTable('kuasi_paroki')) {
            return;
        }

        // 1. Kuasi Paroki Haukoto
        DB::table('kuasi_paroki')
            ->where('NamaKuasiParoki', 'LIKE', '%Haukoto%')
            ->orWhere('id', 1)
            ->update([
                'NamaKuasiParoki' => 'Kuasi Paroki Haukoto',
                'nama_kuasi' => 'Kuasi Paroki Haukoto',
                'paroki_id' => 392, // St. Gregorius Agung - Oeleta
                'dekenat_id' => 13, // Kevikepan/Dekenat Kota Kupang
                'keuskupan_id' => 5,  // Keuskupan Agung Kupang
                'AlamatKuasiParoki' => 'Haukoto',
                'lokasi' => 'Haukoto',
                'Kota' => 'Kota Kupang',
                'StatusAktif' => 'N',
                'status' => 'Nonaktif',
            ]);

        // 2. Kuasi Paroki Lasiana
        DB::table('kuasi_paroki')
            ->where('NamaKuasiParoki', 'LIKE', '%Lasiana%')
            ->orWhere('id', 2)
            ->update([
                'NamaKuasiParoki' => 'Kuasi Paroki Lasiana',
                'nama_kuasi' => 'Kuasi Paroki Lasiana',
                'paroki_id' => 384, // Sta. Maria Assumpta Kota Baru
                'dekenat_id' => 13, // Kevikepan/Dekenat Kota Kupang
                'keuskupan_id' => 5,  // Keuskupan Agung Kupang
                'AlamatKuasiParoki' => 'Lasiana',
                'lokasi' => 'Lasiana',
                'Kota' => 'Kota Kupang',
                'StatusAktif' => 'Y',
                'status' => 'Aktif',
            ]);

        // 3. Kuasi Paroki Manulai
        DB::table('kuasi_paroki')
            ->where('NamaKuasiParoki', 'LIKE', '%Manulai%')
            ->orWhere('id', 3)
            ->update([
                'NamaKuasiParoki' => 'Kuasi Paroki Manulai',
                'nama_kuasi' => 'Kuasi Paroki Manulai',
                'paroki_id' => 385, // St. Yoseph Naikoten
                'dekenat_id' => 13, // Kevikepan/Dekenat Kota Kupang
                'keuskupan_id' => 5,  // Keuskupan Agung Kupang
                'AlamatKuasiParoki' => 'Manulai',
                'lokasi' => 'Manulai',
                'Kota' => 'Kota Kupang',
                'StatusAktif' => 'Y',
                'status' => 'Aktif',
            ]);

        // 4. Kuasi Paroki Semau
        DB::table('kuasi_paroki')
            ->where('NamaKuasiParoki', 'LIKE', '%Semau%')
            ->orWhere('id', 4)
            ->update([
                'NamaKuasiParoki' => 'Kuasi Paroki Semau',
                'nama_kuasi' => 'Kuasi Paroki Semau',
                'paroki_id' => 392, // St. Gregorius Agung - Oeleta
                'dekenat_id' => 13, // Kevikepan/Dekenat Kota Kupang
                'keuskupan_id' => 5,  // Keuskupan Agung Kupang
                'AlamatKuasiParoki' => 'Semau',
                'lokasi' => 'Pulau Semau',
                'Kota' => 'Kabupaten Kupang',
                'pelindung' => 'Santo Petrus Bau-Kuanag',
                'Keterangan' => 'Santo Petrus Bau-Kuanag',
                'StatusAktif' => 'Y',
                'status' => 'Aktif',
            ]);

        // 5. Kuasi Paroki Oesapa
        DB::table('kuasi_paroki')
            ->where('NamaKuasiParoki', 'LIKE', '%Oesapa%')
            ->orWhere('id', 5)
            ->update([
                'NamaKuasiParoki' => 'Kuasi Paroki Oesapa',
                'nama_kuasi' => 'Kuasi Paroki Oesapa',
                'paroki_id' => 397, // St. Yoseph Pekerja - Penfui
                'dekenat_id' => 13, // Kevikepan/Dekenat Kota Kupang
                'keuskupan_id' => 5,  // Keuskupan Agung Kupang
                'AlamatKuasiParoki' => 'Oesapa',
                'lokasi' => 'Oesapa',
                'Kota' => 'Kota Kupang',
                'pelindung' => 'Santo Petrus dan Paulus',
                'Keterangan' => 'Santo Petrus dan Paulus',
                'StatusAktif' => 'Y',
                'status' => 'Aktif',
            ]);

        // 6. Sta Maria Reinha Rosari Siolais
        DB::table('kuasi_paroki')
            ->where('NamaKuasiParoki', 'LIKE', '%Siolais%')
            ->orWhere('id', 6)
            ->update([
                'NamaKuasiParoki' => 'Sta Maria Reinha Rosari Siolais',
                'nama_kuasi' => 'Sta Maria Reinha Rosari Siolais',
                'paroki_id' => 382, // St. Maria Dolorosa - Soe
                'dekenat_id' => 14, // Kevikepan/Dekenat TTS
                'keuskupan_id' => 5,  // Keuskupan Agung Kupang
                'TahunDidirikan' => '2000',
                'StatusAktif' => 'Y',
                'status' => 'Aktif',
            ]);

        // 7. Santo Vinsensius (Nunohonis)
        DB::table('kuasi_paroki')
            ->where('NamaKuasiParoki', 'LIKE', '%Nunohonis%')
            ->orWhere('id', 7)
            ->update([
                'NamaKuasiParoki' => 'Santo Vinsensius (Nunohonis)',
                'nama_kuasi' => 'Santo Vinsensius (Nunohonis)',
                'paroki_id' => 382, // St. Maria Dolorosa - Soe
                'dekenat_id' => 14, // Kevikepan/Dekenat TTS
                'keuskupan_id' => 5,  // Keuskupan Agung Kupang
                'StatusAktif' => 'N',
                'status' => 'Nonaktif',
            ]);

        // 8. Tahon – Santa Maria Fatima
        DB::table('kuasi_paroki')
            ->where(function($q) {
                $q->where('NamaKuasiParoki', 'LIKE', '%Tahon%')
                  ->orWhere('id', 14);
            })
            ->update([
                'NamaKuasiParoki' => 'Tahon – Santa Maria Fatima',
                'nama_kuasi' => 'Tahon – Santa Maria Fatima',
                'paroki_id' => 409, // Paroki Katedral Atambua
                'dekenat_id' => 4,   // Belu Utara
                'keuskupan_id' => 14, // Keuskupan Atambua
                'StatusAktif' => 'Y',
                'status' => 'Aktif',
            ]);

        // 9. Data dari migrasi lama tabel paroki yang pada file Excel kosong paroki induknya (-)
        $orphanIds = [13, 15, 16, 19, 20, 21];
        DB::table('kuasi_paroki')->whereIn('id', $orphanIds)->update([
            'paroki_id' => null,
            'dekenat_id' => null,
            'keuskupan_id' => null,
        ]);

        // Bersihkan nama yang rapi
        DB::table('kuasi_paroki')->where('id', 15)->update([
            'NamaKuasiParoki' => 'Hati Kudus Yesus - Daruba',
            'nama_kuasi' => 'Hati Kudus Yesus - Daruba',
        ]);
        DB::table('kuasi_paroki')->where('id', 16)->update([
            'NamaKuasiParoki' => 'Maronggela - Kurubhoko',
            'nama_kuasi' => 'Maronggela - Kurubhoko',
        ]);
        DB::table('kuasi_paroki')->where('id', 13)->update([
            'NamaKuasiParoki' => 'Onekore - Puurere',
            'nama_kuasi' => 'Onekore - Puurere',
        ]);
        DB::table('kuasi_paroki')->where('id', 19)->update([
            'NamaKuasiParoki' => 'St. Fransiskus Xaverius - Kairatu/Meliau',
            'nama_kuasi' => 'St. Fransiskus Xaverius - Kairatu/Meliau',
        ]);
        DB::table('kuasi_paroki')->where('id', 20)->update([
            'NamaKuasiParoki' => 'St. Petrus - Seira',
            'nama_kuasi' => 'St. Petrus - Seira',
        ]);
        DB::table('kuasi_paroki')->where('id', 21)->update([
            'NamaKuasiParoki' => 'St. Petrus dan Paulus - Benu',
            'nama_kuasi' => 'St. Petrus dan Paulus - Benu',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
