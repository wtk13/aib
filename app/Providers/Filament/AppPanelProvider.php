<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Register;
use App\Http\Middleware\EnforceNoindex;
use App\Http\Middleware\SetLocaleMiddleware;
use App\Modules\Crm\Filament\Resources\ClientResource;
use App\Modules\Scheduling\Filament\Resources\JobResource;
use App\Modules\Tenancy\Middleware\ResolveTenantFromSubdomain;
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
            ->colors(['primary' => Color::Blue])
            ->pages([Pages\Dashboard::class])
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
