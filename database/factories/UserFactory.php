<?php

namespace Database\Factories;

use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password',
            'role' => 'owner',
            'email_verified_at' => null,
        ];
    }

    public function verified(): static
    {
        return $this->state(['email_verified_at' => now()]);
    }
}
