<?php

use App\Modules\Crm\Models\Address;
use App\Modules\Crm\Models\Client;
use App\Modules\Presets\Models\VerticalPreset;
use App\Modules\Quoting\Filament\Resources\QuoteResource\Pages\CreateQuote;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\TenantSettings;
use App\Modules\Tenancy\Models\User;
use Database\Seeders\CleaningPresetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CleaningPresetSeeder::class);
});

function commuteSetup(): array
{
    $preset = VerticalPreset::where('slug', 'cleaning')->first();
    $tenant = Tenant::factory()->create(['preset_id' => $preset?->id]);
    $user = Tenant::bypass(fn () => User::factory()->for($tenant, 'tenant')->create());
    Tenant::setCurrent($tenant);

    // Origin address (firm's base)
    $origin = Address::create([
        'line1' => 'ul. Biurowa 1',
        'city' => 'Warszawa',
        'postcode' => '00-001',
        'lat' => 52.2297,
        'lng' => 21.0122,
    ]);

    // Client address
    $dest = Address::create([
        'line1' => 'ul. Klienta 5',
        'city' => 'Warszawa',
        'postcode' => '00-002',
        'lat' => 52.2400,
        'lng' => 21.0200,
    ]);

    // Seed distance cache (~1.5 km)
    DB::table('distance_caches')->insert([
        'tenant_id' => $tenant->id,
        'origin_address_id' => $origin->id,
        'destination_address_id' => $dest->id,
        'distance_meters' => 1500,
        'duration_seconds' => 300,
        'raw_response' => json_encode(['source' => 'test']),
        'computed_at' => now(),
    ]);

    TenantSettings::updateOrCreate(['tenant_id' => $tenant->id], [
        'origin_address_id' => $origin->id,
        'fuel_rate_pln_per_km' => 2.00,
    ]);

    $client = Client::create(['name' => 'Klient z adresem', 'address_id' => $dest->id]);

    return [$user, $client, $tenant];
}

it('auto-adds commute line item when client with cached distance is selected', function () {
    [$user, $client] = commuteSetup();

    // 1.5 km × 2 × 2.00 PLN/km = 6.00 PLN
    Livewire::actingAs($user)
        ->test(CreateQuote::class)
        ->set('data.client_id', $client->id)
        ->assertSet('data.items', fn (array $items) => count(array_filter($items, fn ($i) => ($i['source'] ?? '') === 'commute')) === 1
        );
});

it('replaces commute line when client is changed a second time', function () {
    [$user, $client] = commuteSetup();

    Livewire::actingAs($user)
        ->test(CreateQuote::class)
        ->set('data.client_id', $client->id)
        ->set('data.client_id', $client->id) // select same client again
        ->assertSet('data.items', fn (array $items) => count(array_filter($items, fn ($i) => ($i['source'] ?? '') === 'commute')) === 1
        );
});

it('does not add commute when client has no cached distance', function () {
    [$user] = commuteSetup();

    $clientWithoutAddress = Client::create(['name' => 'Bez adresu']);

    Livewire::actingAs($user)
        ->test(CreateQuote::class)
        ->set('data.client_id', $clientWithoutAddress->id)
        ->assertSet('data.items', fn (array $items) => count(array_filter($items, fn ($i) => ($i['source'] ?? '') === 'commute')) === 0
        );
});
