<?php

use App\Modules\Tenancy\Exceptions\TenantContextMissingException;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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

// --- Scope isolation tests ---

use App\Modules\Crm\Models\Client;

dataset('tenant_scoped_models', [
    'Client' => [Client::class, fn ($t) => Client::factory()->create(['tenant_id' => $t->id])],
]);

it('model is scoped to tenant and invisible from other tenant', function (string $modelClass, Closure $factory) {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    Tenant::bypass(function () use ($factory, $tenantA) {
        Tenant::setCurrent($tenantA);
        $factory($tenantA);
        Tenant::clear();
    });

    Tenant::setCurrent($tenantB);
    expect($modelClass::count())->toBe(0);
    Tenant::clear();
})->with('tenant_scoped_models')->group('tenancy');

it('TenantScope applies WHERE tenant_id when context is set', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    Tenant::bypass(function () use ($tenantA, $tenantB) {
        Tenant::setCurrent($tenantA);
        Client::factory()->create(['tenant_id' => $tenantA->id]);
        Client::factory()->create(['tenant_id' => $tenantB->id]);
        Tenant::clear();
    });

    Tenant::setCurrent($tenantA);
    expect(Client::count())->toBe(1);

    Tenant::setCurrent($tenantB);
    expect(Client::count())->toBe(1);

    Tenant::clear();
})->group('tenancy');

it('TenantScope throws TenantContextMissingException outside console with no context', function () {
    Tenant::clear();

    // Force app()->runningInConsole() to return false so TenantScope throws instead of skipping.
    $ref = new ReflectionProperty($this->app, 'isRunningInConsole');
    $ref->setAccessible(true);
    $ref->setValue($this->app, false);

    try {
        expect(fn () => Client::count())
            ->toThrow(TenantContextMissingException::class);
    } finally {
        // Restore console mode so subsequent tests are not affected.
        $ref->setValue($this->app, true);
    }
})->group('tenancy');
