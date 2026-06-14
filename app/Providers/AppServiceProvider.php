<?php

namespace App\Providers;

use App\Modules\AI\Services\AnthropicClient;
use App\Modules\ClientChat\Services\ClientChatService;
use App\Modules\ClientChat\Services\RagRetriever;
use App\Modules\Notes\Models\Note;
use App\Modules\Notes\Observers\NoteObserver;
use App\Modules\Notes\Services\EmbeddingService;
use App\Modules\Notes\Services\WhisperService;
use App\Modules\Employees\Filament\Resources\EmployeeResource\Widgets\EmployeeEarningsStatsWidget;
use App\Modules\Employees\Filament\Resources\EmployeeResource\Widgets\EmployeeReportWidget;
use App\Modules\Presets\Events\VerticalPresetUpdated;
use App\Modules\Presets\Listeners\BustPresetCache;
use App\Modules\Tenancy\Auth\TenantAwareUserProvider;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AnthropicClient::class, fn () => new AnthropicClient(new Client(['timeout' => 30.0, 'connect_timeout' => 5.0])));
        $this->app->singleton(WhisperService::class, fn () => new WhisperService(new Client(['timeout' => 60.0, 'connect_timeout' => 5.0])));
        $this->app->singleton(EmbeddingService::class, fn () => new EmbeddingService(new Client(['timeout' => 30.0, 'connect_timeout' => 5.0])));
        $this->app->singleton(RagRetriever::class, fn ($app) => new RagRetriever($app->make(EmbeddingService::class)));
        $this->app->singleton(ClientChatService::class, fn ($app) => new ClientChatService(
            $app->make(AnthropicClient::class),
            $app->make(RagRetriever::class),
        ));
    }

    public function boot(): void
    {
        Event::listen(VerticalPresetUpdated::class, BustPresetCache::class);

        Note::observe(NoteObserver::class);

        Auth::provider(
            'tenant_aware_eloquent',
            fn ($app, $config) => new TenantAwareUserProvider(
                $app['hash'],
                $config['model']
            )
        );

        Livewire::component('employee-earnings-stats-widget', EmployeeEarningsStatsWidget::class);
        Livewire::component('employee-report-widget', EmployeeReportWidget::class);
    }
}
