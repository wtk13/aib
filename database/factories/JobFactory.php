<?php

namespace Database\Factories;

use App\Modules\Scheduling\Models\Job;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Job> */
class JobFactory extends Factory
{
    protected $model = Job::class;

    public function definition(): array
    {
        return [
            'service_type_key' => 'basic',
            'starts_at' => $this->faker->dateTimeBetween('now', '+30 days'),
            'duration_minutes' => 60,
            'status' => 'planned',
            'recurrence_rule' => null,
            'price_pln' => null,
            'custom_fields' => [],
            'internal_notes' => null,
        ];
    }
}
