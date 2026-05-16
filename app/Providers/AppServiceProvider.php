<?php

namespace App\Providers;

use App\Modules\Presets\Events\VerticalPresetUpdated;
use App\Modules\Presets\Listeners\BustPresetCache;
use App\Modules\Tenancy\Auth\TenantAwareUserProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Event::listen(VerticalPresetUpdated::class, BustPresetCache::class);

        Auth::provider(
            'tenant_aware_eloquent',
            fn ($app, $config) => new TenantAwareUserProvider(
                $app['hash'],
                $config['model']
            )
        );
    }
}
