<?php

use App\Modules\Crm\Models\Address;
use App\Modules\Integrations\Distance\DistanceResult;
use App\Modules\Integrations\Distance\DistanceService;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('returns null when origin has no lat/lng', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);
    $origin = Address::create(['line1' => 'ul. Marszałkowska 1', 'city' => 'Warszawa']);
    $dest = Address::create(['line1' => 'ul. Mokotowska 12', 'city' => 'Warszawa', 'lat' => 52.2, 'lng' => 21.0]);

    $service = app(DistanceService::class);
    $result = $service->getDistance($tenant->id, $origin, $dest);

    expect($result)->toBeNull();
});

it('returns null when destination has no lat/lng', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);
    $origin = Address::create(['line1' => 'ul. Marszałkowska 1', 'city' => 'Warszawa', 'lat' => 52.23, 'lng' => 21.01]);
    $dest = Address::create(['line1' => 'ul. Mokotowska 12', 'city' => 'Warszawa']);

    $service = app(DistanceService::class);
    $result = $service->getDistance($tenant->id, $origin, $dest);

    expect($result)->toBeNull();
});

it('returns DistanceResult with Haversine distance', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);
    // Warsaw city center to Mokotów — ~3 km straight line
    $origin = Address::create(['line1' => 'ul. Marszałkowska 1', 'city' => 'Warszawa', 'lat' => 52.2297, 'lng' => 21.0122]);
    $dest = Address::create(['line1' => 'ul. Puławska 100', 'city' => 'Warszawa', 'lat' => 52.1976, 'lng' => 21.0122]);

    $service = app(DistanceService::class);
    $result = $service->getDistance($tenant->id, $origin, $dest);

    expect($result)->toBeInstanceOf(DistanceResult::class);
    expect($result->distanceKm)->toBeGreaterThan(2.0)->toBeLessThan(5.0);
    expect($result->label)->toContain('km');
});

it('caches the distance result in distance_caches', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);
    $origin = Address::create(['line1' => 'ul. A', 'city' => 'Warszawa', 'lat' => 52.2297, 'lng' => 21.0122]);
    $dest = Address::create(['line1' => 'ul. B', 'city' => 'Warszawa', 'lat' => 52.1976, 'lng' => 21.0122]);

    $service = app(DistanceService::class);
    $service->getDistance($tenant->id, $origin, $dest);

    expect(\DB::table('distance_caches')
        ->where('tenant_id', $tenant->id)
        ->where('origin_address_id', $origin->id)
        ->where('destination_address_id', $dest->id)
        ->exists()
    )->toBeTrue();
});

it('returns cached result on second call without creating duplicate', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);
    $origin = Address::create(['line1' => 'ul. A', 'city' => 'Warszawa', 'lat' => 52.2297, 'lng' => 21.0122]);
    $dest = Address::create(['line1' => 'ul. B', 'city' => 'Warszawa', 'lat' => 52.1976, 'lng' => 21.0122]);

    $service = app(DistanceService::class);
    $first = $service->getDistance($tenant->id, $origin, $dest);
    $second = $service->getDistance($tenant->id, $origin, $dest);

    expect(\DB::table('distance_caches')->count())->toBe(1);
    expect($second->distanceKm)->toBe($first->distanceKm);
});

it('computes commute cost with fuel rate', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);
    $origin = Address::create(['line1' => 'ul. A', 'city' => 'Warszawa', 'lat' => 52.2297, 'lng' => 21.0122]);
    $dest = Address::create(['line1' => 'ul. B', 'city' => 'Warszawa', 'lat' => 52.1976, 'lng' => 21.0122]);

    $service = app(DistanceService::class);
    $result = $service->getDistance($tenant->id, $origin, $dest, fuelRatePln: 2.00);

    // commuteCostPln = distanceKm * 2 * fuelRate (round trip)
    expect(round($result->commuteCostPln, 1))->toBe(round($result->distanceKm * 2 * 2.00, 1));
});
