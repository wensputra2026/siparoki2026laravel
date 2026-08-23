<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->spa()
            ->sidebarCollapsibleOnDesktop()
            ->sidebarFullyCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->brandName('SIPAROKI Admin')
            ->brandLogo(asset('uploads/profil/logo_paroki_1787370466.jpeg'))
            ->favicon(asset('uploads/profil/logo_paroki_1787370466.jpeg'))
            ->navigationGroups([
                NavigationGroup::make('Wilayah & Referensi')
                    ->icon('heroicon-o-building-library'),
                NavigationGroup::make('Umat & Pelayanan')
                    ->icon('heroicon-o-users'),
                NavigationGroup::make('Lapak & Toko')
                    ->icon('heroicon-o-shopping-bag'),
                NavigationGroup::make('Sekretariat Paroki')
                    ->icon('heroicon-o-inbox'),
                NavigationGroup::make('Keuangan & Aset')
                    ->icon('heroicon-o-banknotes'),
                NavigationGroup::make('Website Paroki')
                    ->icon('heroicon-o-globe-alt'),
                NavigationGroup::make('Sistem & Aplikasi')
                    ->icon('heroicon-o-cog-6-tooth'),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\StatsOverview::class,
                \App\Filament\Widgets\SakramenChart::class,
                \App\Filament\Widgets\UmatChart::class,
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
