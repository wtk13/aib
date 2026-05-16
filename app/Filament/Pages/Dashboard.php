<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\OverdueClientsWidget;
use App\Filament\Widgets\TodayJobsWidget;
use App\Filament\Widgets\UpcomingJobsWidget;
use App\Filament\Widgets\WeekRevenueWidget;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    protected static ?int $navigationSort = -2;

    public static function getNavigationLabel(): string
    {
        return __('dashboard.nav_label');
    }

    public function getWidgets(): array
    {
        return [
            WeekRevenueWidget::class,
            TodayJobsWidget::class,
            UpcomingJobsWidget::class,
            OverdueClientsWidget::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return 2;
    }
}
