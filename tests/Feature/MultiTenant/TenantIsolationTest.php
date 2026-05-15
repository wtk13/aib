<?php

use App\Modules\Tenancy\Exceptions\TenantContextMissingException;
use App\Modules\Tenancy\Models\Tenant;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class);

// --- Context management ---

it('can set and retrieve current tenant', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);

    expect(Tenant::currentId())->toBe($tenant->id);
})->group('tenancy');

it('clears tenant context', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);
    Tenant::clear();

    expect(Tenant::currentId())->toBeNull();
})->group('tenancy');

it('bypass block works and restores state', function () {
    Tenant::clear();
    $bypassedDuring = false;
    $result = Tenant::bypass(function () use (&$bypassedDuring) {
        $bypassedDuring = Tenant::isBypassed();
        return 'inside';
    });

    expect($result)->toBe('inside');
    expect($bypassedDuring)->toBeTrue();
    expect(Tenant::isBypassed())->toBeFalse();
})->group('tenancy');

// Parametric isolation tests over all BelongsToTenant models are added in Task 22
// after all domain models and their migrations exist.
