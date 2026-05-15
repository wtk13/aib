<?php

use App\Modules\Tenancy\Models\Tenant;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class);

it('can set and retrieve current tenant', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);

    expect(Tenant::currentId())->toBe($tenant->id);
    expect(Tenant::current()->id)->toBe($tenant->id);
});

it('clears tenant context', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);
    Tenant::clear();

    expect(Tenant::currentId())->toBeNull();
});

it('runs bypass block without tenant context', function () {
    Tenant::clear();

    $result = Tenant::bypass(fn () => 'ok');

    expect($result)->toBe('ok');
    expect(Tenant::isBypassed())->toBeFalse();
});
