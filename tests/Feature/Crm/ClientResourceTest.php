<?php

use App\Modules\Crm\Filament\Resources\ClientResource;
use App\Modules\Crm\Filament\Resources\ClientResource\Pages\CreateClient;
use App\Modules\Crm\Filament\Resources\ClientResource\Pages\EditClient;
use App\Modules\Crm\Filament\Resources\ClientResource\Pages\ListClients;
use App\Modules\Crm\Models\Client;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\User;
use Database\Seeders\CleaningPresetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CleaningPresetSeeder::class);
});

// Helper — creates seeded tenant + user, sets context
function actingAsOwner(): User
{
    $preset = \App\Modules\Presets\Models\VerticalPreset::where('slug', 'cleaning')->first();
    $tenant = Tenant::factory()->create(['preset_id' => $preset?->id]);
    $user   = Tenant::bypass(fn () => User::factory()->for($tenant, 'tenant')->create());
    Tenant::setCurrent($tenant);

    return $user;
}

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

it('can list clients', function () {
    $user   = actingAsOwner();
    $client = Client::create(['name' => 'Pani Nowak', 'client_type' => 'person']);

    Livewire::actingAs($user)
        ->test(ListClients::class)
        ->assertCanSeeTableRecords([$client]);
});

it('can create a person client', function () {
    $user = actingAsOwner();

    Livewire::actingAs($user)
        ->test(CreateClient::class)
        ->fillForm([
            'client_type'   => 'person',
            'name'          => 'Jan Testowy',
            'phone'         => '600100200',
            'email'         => 'jan@test.pl',
            'addr_line1'    => 'ul. Przykładowa 1',
            'addr_postcode' => '00-001',
            'addr_city'     => 'Warszawa',
        ])
        ->call('create')
        ->assertHasNoErrors();

    $client = Client::where('name', 'Jan Testowy')->first();
    expect($client)->not->toBeNull()
        ->and($client->phone)->toBe('600100200')
        ->and($client->address->line1)->toBe('ul. Przykładowa 1');
});

it('can edit a client', function () {
    $user   = actingAsOwner();
    $client = Client::create(['name' => 'Stara Nazwa', 'client_type' => 'person']);

    Livewire::actingAs($user)
        ->test(EditClient::class, ['record' => $client->getRouteKey()])
        ->fillForm(['name' => 'Nowa Nazwa'])
        ->call('save')
        ->assertHasNoErrors();

    expect($client->fresh()->name)->toBe('Nowa Nazwa');
});
