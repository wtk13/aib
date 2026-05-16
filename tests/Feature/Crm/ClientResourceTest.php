<?php

use App\Modules\Crm\Models\Client;
use App\Modules\Tenancy\Models\Tenant;
use Database\Seeders\CleaningPresetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CleaningPresetSeeder::class);
});

it('client has a client_type defaulting to person', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);

    $client = Client::create(['name' => 'Jan Kowalski']);

    expect($client->client_type)->toBe('person');
});

it('client can store regon', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);

    $client = Client::create([
        'name' => 'Firma ABC',
        'client_type' => 'company',
        'regon' => '123456789',
    ]);

    expect($client->fresh()->regon)->toBe('123456789');
});
