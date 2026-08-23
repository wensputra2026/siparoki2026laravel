<?php

namespace App\Providers\Filament;

use App\Models\User;
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

class PastorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('pastor')
            ->path('pastor')
            ->login()
            ->spa()
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Purple,
            ])
            ->brandName('SIPAROKI — Pastor')
            ->navigationGroups([
                NavigationGroup::make('Liturgi & Sakramen')
                    ->icon('heroicon-o-sun'),
                NavigationGroup::make('Umat & Pelayanan')
                    ->icon('heroicon-o-users'),
                NavigationGroup::make('Website Paroki')
                    ->icon('heroicon-o-globe-alt'),
            ])
            ->discoverResources(in: app_path('Filament/Pastor/Resources'), for: 'App\\Filament\\Pastor\\Resources')
            ->discoverPages(in: app_path('Filament/Pastor/Pages'), for: 'App\\Filament\\Pastor\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Pastor/Widgets'), for: 'App\\Filament\\Pastor\\Widgets')
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
            ])
            ->authGuard('web')
            ->authPasswordBroker('users');
    }
}
