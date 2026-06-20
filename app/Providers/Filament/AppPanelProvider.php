<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Auth\Register;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\TenantSettingsPage;
use App\Http\Middleware\SetLocaleMiddleware;
use App\Modules\Crm\Filament\Resources\ClientResource;
use App\Modules\Employees\Filament\Resources\EmployeeResource;
use App\Modules\Quoting\Filament\Resources\QuoteResource;
use App\Modules\Scheduling\Filament\Resources\JobResource;
use App\Modules\Tenancy\Middleware\ResolveTenantFromSubdomain;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
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
            ->login(Login::class)
            ->registration(Register::class)
            ->emailVerification()
            ->colors(['primary' => Color::hex('#14b8a6')])
            ->darkMode(false)
            ->font('Inter', provider: GoogleFontProvider::class)
            ->brandName('TBA')
            ->brandLogo(new HtmlString(
                '<div style="display:flex;align-items:center;gap:8px;">'
                .'<div style="display:flex;width:28px;height:28px;align-items:center;justify-content:center;border-radius:6px;background:white;color:#0d9488;font-weight:700;font-size:14px;line-height:1;">✦</div>'
                .'<span style="font-size:14px;font-weight:700;color:white;letter-spacing:0.02em;">TBA</span>'
                .'</div>'
            ))
            ->brandLogoHeight('2rem')
            ->renderHook(
                PanelsRenderHook::STYLES_AFTER,
                fn () => new HtmlString('<link rel="stylesheet" href="'.asset('css/filament/app/theme.css').'">')
            )
            ->sidebarCollapsibleOnDesktop()
            ->pages([Dashboard::class, TenantSettingsPage::class])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->resources([
                ClientResource::class,
                JobResource::class,
                EmployeeResource::class,
                QuoteResource::class,
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
                ResolveTenantFromSubdomain::class,
                SetLocaleMiddleware::class,
            ])
            ->authMiddleware([Authenticate::class]);
    }
}
