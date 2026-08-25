<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;

// Models
use App\Models\Umat;
use App\Models\KkKatolik;
use App\Models\Sakramen;
use App\Models\PengajuanSakramen;
use App\Models\JadwalMisa;
use App\Models\IntensiMisa;
use App\Models\Keuangan;
use App\Models\Aset;
use App\Models\Paroki;
use App\Models\Wilayah;
use App\Models\Lingkungan;
use App\Models\Kapela;
use App\Models\Konten;
use App\Models\Pengumuman;
use App\Models\Galeri;
use App\Models\SuratMasuk;
use App\Models\SuratKeluar;
use App\Models\ArsipDigital;
use App\Models\User;
use App\Models\Role;

// Policies
use App\Policies\UmatPolicy;
use App\Policies\KkKatolikPolicy;
use App\Policies\SakramenPolicy;
use App\Policies\PengajuanSakramenPolicy;
use App\Policies\JadwalMisaPolicy;
use App\Policies\IntensiMisaPolicy;
use App\Policies\KeuanganPolicy;
use App\Policies\AsetPolicy;
use App\Policies\ParokiPolicy;
use App\Policies\WilayahPolicy;
use App\Policies\LingkunganPolicy;
use App\Policies\KapelaPolicy;
use App\Policies\KontenPolicy;
use App\Policies\PengumumanPolicy;
use App\Policies\GaleriPolicy;
use App\Policies\SuratMasukPolicy;
use App\Policies\SuratKeluarPolicy;
use App\Policies\ArsipDigitalPolicy;
use App\Policies\UserPolicy;
use App\Policies\RolePolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     */
    protected $policies = [
        Umat::class             => UmatPolicy::class,
        KkKatolik::class        => KkKatolikPolicy::class,
        Sakramen::class         => SakramenPolicy::class,
        PengajuanSakramen::class => PengajuanSakramenPolicy::class,
        JadwalMisa::class       => JadwalMisaPolicy::class,
        IntensiMisa::class      => IntensiMisaPolicy::class,
        Keuangan::class         => KeuanganPolicy::class,
        Aset::class             => AsetPolicy::class,
        Paroki::class           => ParokiPolicy::class,
        Wilayah::class          => WilayahPolicy::class,
        Lingkungan::class       => LingkunganPolicy::class,
        Kapela::class           => KapelaPolicy::class,
        Konten::class           => KontenPolicy::class,
        Pengumuman::class       => PengumumanPolicy::class,
        Galeri::class           => GaleriPolicy::class,
        SuratMasuk::class       => SuratMasukPolicy::class,
        SuratKeluar::class      => SuratKeluarPolicy::class,
        ArsipDigital::class     => ArsipDigitalPolicy::class,
        User::class             => UserPolicy::class,
        Role::class             => RolePolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Auto cleanup stale Vite hot file if dev server is not running
        if (file_exists(public_path('hot'))) {
            $fp = @fsockopen('127.0.0.1', 5173, $errno, $errstr, 0.05);
            if (!$fp) {
                @unlink(public_path('hot'));
            } else {
                @fclose($fp);
            }
        }

        // Auto sync assets from C:/laragon/www/katedral/assets/frontend/siparoki/images if present
        $katedralImagesPath = 'C:/laragon/www/katedral/assets/frontend/siparoki/images';
        $destPath = public_path('assets/frontend/siparoki/images');
        $publicImagesPath = public_path('images');

        if (is_dir($katedralImagesPath)) {
            if (!is_dir($destPath)) {
                @mkdir($destPath, 0777, true);
            }
            if (!is_dir($publicImagesPath)) {
                @mkdir($publicImagesPath, 0777, true);
            }
            $files = @scandir($katedralImagesPath) ?: [];
            foreach ($files as $f) {
                if ($f !== '.' && $f !== '..' && is_file($katedralImagesPath . '/' . $f)) {
                    @copy($katedralImagesPath . '/' . $f, $destPath . '/' . $f);
                    @copy($katedralImagesPath . '/' . $f, $publicImagesPath . '/' . $f);
                }
            }

            if (file_exists($destPath . '/default-pastor.jpg')) {
                @copy($destPath . '/default-pastor.jpg', $publicImagesPath . '/pastor-avatar.jpg');
                @copy($destPath . '/default-pastor.jpg', $publicImagesPath . '/imam.jpg');
            }
        }

        $this->registerPolicies();

        Model::unguard();

        try {
            \App\Models\KomentarArtikel::ensureTableExists();
            \App\Models\PengaturanAplikasi::ensureSetupColumns();
        } catch (\Throwable $e) {
            // Silently continue
        }

        // Superadmin bypass — Pastor Paroki & Admin full access
        Gate::before(function (User $user, string $ability): ?bool {
            if ($user->role_id == 1 || $user->id == 1) {
                return true;
            }
            $slug = str_replace(['_', '-', ' '], '', strtolower(trim($user->role?->slug ?? $user->role?->nama_role ?? '')));
            if (in_array($slug, ['superadmin', 'superadministrator', 'admin', 'administrator', 'pastor', 'pastorparoki'])) {
                return true;
            }
            return null;
        });

        // Global data is injected into many partials; cache it to avoid repeated DB hits per page render.
        View::composer('*', function ($view) {
            $defaultId = session('default_paroki_id') ?: 'default';
            $version = Cache::get('global_view_data_version', 1);
            $globalData = Cache::remember("global_view_data.{$version}.{$defaultId}", 600, function () use ($defaultId) {
                try {
                    $activeParoki = null;
                    if ($defaultId !== 'default') {
                        $activeParoki = \App\Models\Paroki::find($defaultId);
                    }

                    if (!$activeParoki) {
                        $pengaturan = Cache::remember('global_pengaturan_aplikasi_first', 600, function () {
                            return \Illuminate\Support\Facades\Schema::hasTable('pengaturan_aplikasi')
                                ? \Illuminate\Support\Facades\DB::table('pengaturan_aplikasi')->first()
                                : null;
                        });

                        if ($pengaturan && !empty($pengaturan->nama_paroki)) {
                            $activeParoki = \App\Models\Paroki::where('nama_paroki', $pengaturan->nama_paroki)->first();
                        }
                    }

                    if (!$activeParoki) {
                        $activeParoki = \App\Models\Paroki::first();
                    }

                    $namaParoki = $activeParoki->nama_paroki ?? 'SIPAROKI';
                    $logoUrl = $activeParoki->logo ?? asset('favicon.ico');
                    $bannerUrl = $activeParoki->banner ?? $activeParoki->foto ?? null;

                    return [
                        'globalProfil' => $activeParoki,
                        'globalPengaturan' => $activeParoki,
                        'globalLogo' => $logoUrl,
                        'globalFavicon' => $logoUrl,
                        'globalBanner' => $bannerUrl,
                        'globalNamaParoki' => $namaParoki,
                    ];
                } catch (\Throwable $e) {
                    return [
                        'globalLogo' => asset('favicon.ico'),
                        'globalFavicon' => asset('favicon.ico'),
                        'globalBanner' => null,
                        'globalNamaParoki' => 'SIPAROKI',
                    ];
                }
            });

            $view->with($globalData);
        });
    }
}
