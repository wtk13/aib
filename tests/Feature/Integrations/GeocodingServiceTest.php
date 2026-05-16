<?php

use App\Modules\Crm\Models\Address;
use App\Modules\Integrations\Geocoding\GeocodeAddressJob;
use App\Modules\Integrations\Geocoding\GeocodingService;
use App\Modules\Tenancy\Models\Tenant;
use Database\Seeders\CleaningPresetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CleaningPresetSeeder::class);
});

it('geocodes an address and stores lat/lng', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);

    Http::fake([
        'maps.googleapis.com/*' => Http::response([
            'status' => 'OK',
            'results' => [[
                'geometry' => ['location' => ['lat' => 52.2297, 'lng' => 21.0122]],
            ]],
        ]),
    ]);

    $address = Address::create([
        'line1' => 'ul. Marszałkowska 1',
        'postcode' => '00-001',
        'city' => 'Warszawa',
    ]);

    $service = new GeocodingService;
    $service->geocode($address);

    $fresh = $address->fresh();
    expect((float) $fresh->lat)->toBe(52.2297)
        ->and((float) $fresh->lng)->toBe(21.0122)
        ->and($fresh->geocoded_at)->not->toBeNull();
});

it('does nothing silently when API returns no results', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);

    Http::fake([
        'maps.googleapis.com/*' => Http::response(['status' => 'ZERO_RESULTS', 'results' => []]),
    ]);

    $address = Address::create([
        'line1' => 'Nieznana 999',
        'postcode' => '00-000',
        'city' => 'Nowhere',
    ]);

    $service = new GeocodingService;
    $service->geocode($address);

    expect($address->fresh()->lat)->toBeNull();
});

it('GeocodeAddressJob geocodes the address in tenant context', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);

    Http::fake([
        'maps.googleapis.com/*' => Http::response([
            'status' => 'OK',
            'results' => [[
                'geometry' => ['location' => ['lat' => 50.0614, 'lng' => 19.9372]],
            ]],
        ]),
    ]);

    $address = Address::create([
        'line1' => 'ul. Floriańska 1',
        'postcode' => '31-019',
        'city' => 'Kraków',
    ]);

    // QUEUE_CONNECTION=sync in tests — job runs immediately
    GeocodeAddressJob::dispatch($address->id);

    expect((float) $address->fresh()->lat)->toBe(50.0614);
});
