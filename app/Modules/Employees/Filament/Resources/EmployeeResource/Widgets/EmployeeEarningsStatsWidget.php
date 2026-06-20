<?php

namespace App\Modules\Employees\Filament\Resources\EmployeeResource\Widgets;

use App\Modules\Employees\Models\Employee;
use App\Modules\Employees\Models\JobEmployee;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EmployeeEarningsStatsWidget extends BaseWidget
{
    public ?Employee $record = null;

    protected function getStats(): array
    {
        if (! $this->record) {
            return [];
        }

        $now = now();

        $base = fn () => JobEmployee::query()
            ->where('job_employees.employee_id', $this->record->id)
            ->join('jobs', 'jobs.id', '=', 'job_employees.job_id')
            ->whereNull('jobs.deleted_at');

        $thisMonth = $base()
            ->whereBetween('jobs.starts_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->sum('job_employees.payout_pln');

        $thisYear = $base()
            ->whereBetween('jobs.starts_at', [$now->copy()->startOfYear(), $now->copy()->endOfYear()])
            ->sum('job_employees.payout_pln');

        $allTime = $base()->sum('job_employees.payout_pln');
        $allTimeCount = $base()->count();

        return [
            Stat::make(__('employee.stats.this_month'), 'PLN '.number_format((float) $thisMonth, 2, ',', ' ')),
            Stat::make(__('employee.stats.this_year'), 'PLN '.number_format((float) $thisYear, 2, ',', ' ')),
            Stat::make(__('employee.stats.all_time'), 'PLN '.number_format((float) $allTime, 2, ',', ' '))
                ->description($allTimeCount.' '.__('employee.stats.jobs')),
        ];
    }
}
