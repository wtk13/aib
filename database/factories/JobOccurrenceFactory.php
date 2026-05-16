<?php

namespace Database\Factories;

use App\Modules\Scheduling\Models\JobOccurrence;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<JobOccurrence> */
class JobOccurrenceFactory extends Factory
{
    protected $model = JobOccurrence::class;

    public function definition(): array
    {
        return [
            'occurrence_at' => $this->faker->dateTimeBetween('now', '+30 days'),
            'status' => 'planned',
            'rescheduled_to' => null,
            'completed_at' => null,
        ];
    }
}
