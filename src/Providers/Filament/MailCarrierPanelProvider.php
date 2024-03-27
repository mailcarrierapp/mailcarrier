<?php

namespace MailCarrier\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use MailCarrier\Pages\Login;
use MailCarrier\Preview\PreviewPlugin;
use MailCarrier\Resources;
use MailCarrier\Widgets\SentFailureChartWidget;
use MailCarrier\Widgets\StatsOverviewWidget;

class MailCarrierPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('mailcarrier')
            ->path('')
            ->login()
            ->font('Poppins')
            ->brandName('MailCarrier')
            ->favicon(asset('vendor/mailcarrier/images/favicon.ico'))
            ->brandLogo(asset('vendor/mailcarrier/images/logo-dark.svg'))
            ->darkModeBrandLogo(asset('vendor/mailcarrier/images/logo-light.svg'))
            ->theme(asset('vendor/mailcarrier/css/theme.css'))
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->collapsibleNavigationGroups(false)
            ->resources([
                Resources\LogResource::class,
                Resources\LayoutResource::class,
                Resources\TemplateResource::class,
                Resources\ApiTokenResource::class,
                Resources\UserResource::class,
            ])
            ->pages([
                Pages\Dashboard::class,
            ])
            ->widgets([
                StatsOverviewWidget::class,
                SentFailureChartWidget::class,
            ])
            ->plugins([
                PreviewPlugin::make(),
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
            ])
            ->login(Login::class);
    }
}
