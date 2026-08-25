<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'activity_logs', 'alert', 'aset_maintenance', 'banner', 'disposisi_pastor',
            'hubungan_kk', 'intensi_misa', 'katekumen', 'kegiatan',
            'liber_baptis', 'liber_defunctorum', 'liber_krisma', 'liber_perkawinan',
            'log_aktivitas', 'log_backup', 'log_notifikasi', 'log_restore', 'log_wa',
            'login_activity', 'login_attempts', 'login_otp', 'master_coa', 'panduan_doa',
            'pembayaran_cetak_sakramen', 'pembinaan_kelas', 'pengaturan', 'permissions',
            'profil_paroki', 'rapat', 'recent_activities', 'renungan_harian',
            'riwayat_mutasi_umat', 'riwayat_panggilan', 'riwayat_peran_umat',
            'role_permissions', 'sakramen_margo', 'security_logs', 'seo_pages',
            'surat_keluar', 'surat_masuk', 'downloads'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    if (!Schema::hasColumn($table, 'created_at')) {
                        $t->timestamp('created_at')->nullable();
                    }
                    if (!Schema::hasColumn($table, 'updated_at')) {
                        $t->timestamp('updated_at')->nullable();
                    }
                });
            }
        }
    }

    public function down(): void
    {
    }
};
