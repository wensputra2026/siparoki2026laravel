<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

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
        $this->registerPolicies();

        Model::unguard();

        // Superadmin bypass — Pastor Paroki & Admin full access
        Gate::before(function (User $user, string $ability): ?bool {
            $slug = strtolower(trim($user->role?->slug ?? $user->role?->nama_role ?? ''));
            if (in_array($slug, ['superadmin', 'admin', 'pastor-paroki', 'administrator', 'pastor'])) {
                return true;
            }
            return null;
        });
    }
}
