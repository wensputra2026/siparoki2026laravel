<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengaturanAplikasi extends Model
{
    protected $table = 'pengaturan_aplikasi';

    protected $primaryKey = 'id_pengaturan';

    public $incrementing = false;

    protected $fillable = [
        'id_pengaturan',
        'nama_aplikasi',
        'nama_paroki',
        'alamat_paroki',
        'telepon_paroki',
        'email_paroki',
        'logo',
        'favicon',
        'is_setup_completed',
        'keuskupan_id',
        'dekenat_id',
        'paroki_id',
    ];

    protected function casts(): array
    {
        return [
            'is_setup_completed' => 'boolean',
        ];
    }

    protected static function booted()
    {
        $clearCache = function () {
            try {
                \Illuminate\Support\Facades\Cache::forget('global_app_profile');
                \Illuminate\Support\Facades\Cache::forget('global_app_settings');
                \Illuminate\Support\Facades\Cache::forget('global_pengaturan_aplikasi_first');
                \Illuminate\Support\Facades\Cache::increment('global_view_data_version');
                for ($v = 1; $v <= 20; $v++) {
                    \Illuminate\Support\Facades\Cache::forget("frontend.common_data.{$v}");
                }
            } catch (\Throwable $e) {
                // Ignore cache clearing errors
            }
        };

        static::saved($clearCache);
        static::deleted($clearCache);
    }

    /**
     * Self-healing migration check for setup-related columns.
     */
    public static function ensureSetupColumns(): void
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('pengaturan_aplikasi')) {
                \Illuminate\Support\Facades\Schema::table('pengaturan_aplikasi', function ($table) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('pengaturan_aplikasi', 'is_setup_completed')) {
                        $table->boolean('is_setup_completed')->default(true)->after('id_pengaturan');
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('pengaturan_aplikasi', 'keuskupan_id')) {
                        $table->unsignedInteger('keuskupan_id')->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('pengaturan_aplikasi', 'dekenat_id')) {
                        $table->unsignedInteger('dekenat_id')->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('pengaturan_aplikasi', 'paroki_id')) {
                        $table->unsignedInteger('paroki_id')->nullable();
                    }
                });
            }

            if (\Illuminate\Support\Facades\Schema::hasTable('profil_paroki')) {
                \Illuminate\Support\Facades\Schema::table('profil_paroki', function ($table) {
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('profil_paroki', 'keuskupan_id')) {
                        $table->unsignedInteger('keuskupan_id')->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('profil_paroki', 'dekenat_id')) {
                        $table->unsignedInteger('dekenat_id')->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('profil_paroki', 'paroki_id')) {
                        $table->unsignedInteger('paroki_id')->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('profil_paroki', 'pelindung')) {
                        $table->string('pelindung', 150)->nullable();
                    }
                    if (!\Illuminate\Support\Facades\Schema::hasColumn('profil_paroki', 'foto_pastor')) {
                        $table->string('foto_pastor', 255)->nullable();
                    }
                });
            }
        } catch (\Throwable $e) {
            // Silently continue
        }
    }
}
