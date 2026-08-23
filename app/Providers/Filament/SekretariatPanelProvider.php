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

class SekretariatPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('sekretariat')
            ->path('sekretariat')
            ->login()
            ->spa()
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->brandName('SIPAROKI — Sekretariat')
            ->navigationGroups([
                NavigationGroup::make('Sekretariat Paroki')
                    ->icon('heroicon-o-document-text'),
                NavigationGroup::make('Umat & Pelayanan')
                    ->icon('heroicon-o-users'),
                NavigationGroup::make('Sakramen')
                    ->icon('heroicon-o-sun'),
            ])
            ->discoverResources(in: app_path('Filament/Sekretariat/Resources'), for: 'App\\Filament\\Sekretariat\\Resources')
            ->discoverPages(in: app_path('Filament/Sekretariat/Pages'), for: 'App\\Filament\\Sekretariat\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Sekretariat/Widgets'), for: 'App\\Filament\\Sekretariat\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                \Illuminate\View\Middleware\ShareErrorsFromSession::class,
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
