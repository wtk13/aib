<?php

namespace App\Modules\Scheduling\Filament\Resources\JobResource\Pages;

use App\Modules\Scheduling\Filament\Resources\JobResource;
use App\Modules\Scheduling\Models\Job;
use App\Modules\Scheduling\Models\JobOccurrence;
use Carbon\Carbon;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateJob extends CreateRecord
{
    protected static string $resource = JobResource::class;

    protected function afterCreate(): void
    {
        /** @var Job $job */
        $job = $this->getRecord();

        DB::transaction(function () use ($job): void {
            $this->generateOccurrences($job);
        });
    }

    private function generateOccurrences(Job $job): void
    {
        $starts = Carbon::instance($job->starts_at);
        $rule = $job->recurrence_rule ?: null;

        $dates = match ($rule) {
            'weekly' => $this->weeklyDates($starts, 12),
            'biweekly' => $this->biweeklyDates($starts, 6),
            'monthly' => $this->monthlyDates($starts, 3),
            default => [$starts],
        };

        foreach ($dates as $date) {
            JobOccurrence::create([
                'job_id' => $job->id,
                'occurrence_at' => $date,
                'status' => 'planned',
            ]);
        }
    }

    /** @return Carbon[] */
    private function weeklyDates(Carbon $start, int $count): array
    {
        return array_map(fn (int $i) => $start->copy()->addWeeks($i), range(0, $count - 1));
    }

    /** @return Carbon[] */
    private function biweeklyDates(Carbon $start, int $count): array
    {
        return array_map(fn (int $i) => $start->copy()->addDays($i * 14), range(0, $count - 1));
    }

    /** @return Carbon[] */
    private function monthlyDates(Carbon $start, int $count): array
    {
        return array_map(fn (int $i) => $start->copy()->addMonths($i), range(0, $count - 1));
    }
}
