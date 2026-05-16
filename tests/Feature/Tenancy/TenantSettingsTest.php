<?php

use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\TenantSettings;
use Database\Seeders\CleaningPresetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CleaningPresetSeeder::class);
});

it('can create tenant settings', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);

    $settings = TenantSettings::create([
        'tenant_id' => $tenant->id,
        'fuel_rate_pln_per_km' => '2.00',
        'is_vat_payer' => false,
        'locale' => 'pl',
    ]);

    expect($settings->fuel_rate_pln_per_km)->toBe('2.00');
    expect($settings->is_vat_payer)->toBeFalse();
});

it('tenant settings uses tenant_id as primary key', function () {
    $tenant = Tenant::factory()->create();

    $settings = TenantSettings::create([
        'tenant_id' => $tenant->id,
        'fuel_rate_pln_per_km' => '1.80',
    ]);

    expect(TenantSettings::find($tenant->id)?->fuel_rate_pln_per_km)->toBe('1.80');
});
