<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Register;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\TenantSettingsPage;
use App\Http\Middleware\EnforceNoindex;
use App\Http\Middleware\SetLocaleMiddleware;
use App\Modules\Crm\Filament\Resources\ClientResource;
use App\Modules\Scheduling\Filament\Resources\JobResource;
use App\Modules\Tenancy\Middleware\ResolveTenantFromSubdomain;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('app')
            ->path('/admin')
            ->login()
            ->registration(Register::class)
            ->colors(['primary' => Color::hex('#14b8a6')])
            ->font('Inter', provider: GoogleFontProvider::class)
            ->brandName('Wyceny')
            ->brandLogo(new HtmlString(
                '<div style="display:flex;align-items:center;gap:8px;">'
                . '<div style="display:flex;width:28px;height:28px;align-items:center;justify-content:center;border-radius:6px;background:white;color:#0d9488;font-weight:700;font-size:14px;line-height:1;">✦</div>'
                . '<span style="font-size:14px;font-weight:700;color:white;letter-spacing:0.02em;">Wyceny</span>'
                . '</div>'
            ))
            ->brandLogoHeight('2rem')
            ->sidebarCollapsibleOnDesktop()
            ->pages([Dashboard::class, TenantSettingsPage::class])
            ->resources([
                ClientResource::class,
                JobResource::class,
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
                EnforceNoindex::class,
                ResolveTenantFromSubdomain::class,
                SetLocaleMiddleware::class,
            ])
            ->authMiddleware([Authenticate::class]);
    }
}
