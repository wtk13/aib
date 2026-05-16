<?php

namespace Database\Factories;

use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        return [
            'ulid' => (string) Str::ulid(),
            'slug' => $this->faker->unique()->slug(2),
            'company_name' => $this->faker->company(),
            'nip' => null,
            'regon' => null,
        ];
    }
}
