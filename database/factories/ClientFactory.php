<?php

namespace Database\Factories;

use App\Modules\Crm\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Client> */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'client_type' => 'person',
            'name' => $this->faker->name(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'nip' => null,
            'regon' => null,
            'custom_fields' => [],
        ];
    }
}
