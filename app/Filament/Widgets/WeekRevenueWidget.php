<?php

namespace App\Filament\Widgets;

use App\Modules\Scheduling\Models\Job;
use App\Modules\Scheduling\Models\JobOccurrence;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class WeekRevenueWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();
        $lastWeekStart = $weekStart->copy()->subWeek();
        $lastWeekEnd = $weekEnd->copy()->subWeek();

        $thisWeekRevenue = Job::query()
            ->join('job_occurrences', 'jobs.id', '=', 'job_occurrences.job_id')
            ->whereBetween('job_occurrences.occurrence_at', [$weekStart, $weekEnd])
            ->where('job_occurrences.status', 'completed')
            ->sum('jobs.price_pln');

        $lastWeekRevenue = Job::query()
            ->join('job_occurrences', 'jobs.id', '=', 'job_occurrences.job_id')
            ->whereBetween('job_occurrences.occurrence_at', [$lastWeekStart, $lastWeekEnd])
            ->where('job_occurrences.status', 'completed')
            ->sum('jobs.price_pln');

        $thisWeekJobs = JobOccurrence::query()
            ->whereBetween('occurrence_at', [$weekStart, $weekEnd])
            ->where('status', 'completed')
            ->count();

        $nextWeekJobs = JobOccurrence::query()
            ->whereBetween('occurrence_at', [$weekEnd->copy()->addSecond(), $weekEnd->copy()->addWeek()])
            ->where('status', 'planned')
            ->count();

        $revenueDiff = $lastWeekRevenue > 0
            ? (int) round((($thisWeekRevenue - $lastWeekRevenue) / $lastWeekRevenue) * 100)
            : 0;

        $description = $revenueDiff >= 0
            ? "+{$revenueDiff}% " . __('dashboard.widgets.revenue.vs_last_week')
            : '-' . abs($revenueDiff) . '% ' . __('dashboard.widgets.revenue.vs_last_week');

        return [
            Stat::make(__('dashboard.widgets.revenue.this_week'), 'PLN ' . number_format((float) $thisWeekRevenue, 2))
                ->description($description)
                ->color($revenueDiff >= 0 ? 'success' : 'danger'),
            Stat::make(__('dashboard.widgets.revenue.jobs_this_week'), (string) $thisWeekJobs),
            Stat::make(__('dashboard.widgets.revenue.jobs_next_week'), (string) $nextWeekJobs),
        ];
    }
}
