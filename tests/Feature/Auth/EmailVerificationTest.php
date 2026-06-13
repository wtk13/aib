<?php

use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\User;
use Database\Seeders\CleaningPresetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CleaningPresetSeeder::class);
});

afterEach(function () {
    Tenant::clear();
});

it('unverified user is redirected to email verification prompt', function () {
    $user = Tenant::bypass(fn () => User::factory()->create(['email_verified_at' => null]));
    $tenant = Tenant::bypass(fn () => Tenant::find($user->tenant_id));
    Tenant::setCurrent($tenant);

    $this->actingAs($user)
        ->get('/admin')
        ->assertRedirectContains('email-verification');
});

it('verified user can access the panel', function () {
    $user = Tenant::bypass(fn () => User::factory()->verified()->create());
    $tenant = Tenant::bypass(fn () => Tenant::find($user->tenant_id));
    Tenant::setCurrent($tenant);

    $this->actingAs($user)
        ->get('/admin')
        ->assertOk();
});
