<?php

use App\Filament\Pages\TenantSettingsPage;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\TenantSettings;
use App\Modules\Tenancy\Models\User;
use Database\Seeders\CleaningPresetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
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

it('can load the settings page', function () {
    $tenant = Tenant::factory()->create();
    $user = Tenant::bypass(fn () => User::factory()->for($tenant, 'tenant')->create());
    Tenant::setCurrent($tenant);

    Livewire::actingAs($user)
        ->test(TenantSettingsPage::class)
        ->assertSuccessful();
});

it('can save fuel rate in settings', function () {
    $tenant = Tenant::factory()->create();
    $user = Tenant::bypass(fn () => User::factory()->for($tenant, 'tenant')->create());
    Tenant::setCurrent($tenant);

    Livewire::actingAs($user)
        ->test(TenantSettingsPage::class)
        ->fillForm(['fuel_rate_pln_per_km' => '2.50'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(TenantSettings::find($tenant->id)?->fuel_rate_pln_per_km)->toBe('2.50');
});
