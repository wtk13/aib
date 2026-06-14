<?php

namespace App\Modules\Employees\Filament\Resources\EmployeeResource\Widgets;

use App\Modules\Employees\Models\Employee;
use App\Modules\Employees\Models\JobEmployee;
use Carbon\Carbon;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;

class EmployeeReportWidget extends Widget
{
    protected static string $view = 'filament.widgets.employee-report-widget';

    public ?Employee $record = null;

    #[Computed]
    public function dailyHistory(): Collection
    {
        if (! $this->record) {
            return collect();
        }

        return JobEmployee::query()
            ->where('job_employees.employee_id', $this->record->id)
            ->join('jobs', 'jobs.id', '=', 'job_employees.job_id')
            ->join('clients', 'clients.id', '=', 'jobs.client_id')
            ->whereNull('jobs.deleted_at')
            ->select(
                'job_employees.hours_worked',
                'job_employees.payout_pln',
                'jobs.starts_at',
                'jobs.service_type_key',
                'clients.name as client_name'
            )
            ->orderBy('jobs.starts_at', 'desc')
            ->limit(200)
            ->get();
    }

    #[Computed]
    public function monthlyHistory(): Collection
    {
        return $this->dailyHistory
            ->groupBy(fn ($row) => Carbon::parse($row->starts_at)->format('Y-m'))
            ->map(fn ($group, $monthKey) => (object) [
                'month_label' => Carbon::createFromFormat('Y-m', $monthKey)->format('m.Y'),
                'job_count' => $group->count(),
                'total_hours' => $group->sum('hours_worked'),
                'total_payout' => $group->sum('payout_pln'),
            ])
            ->values();
    }
}
