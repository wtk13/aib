<?php

use App\Modules\Presets\Models\VerticalPreset;
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

it('registration page is accessible', function () {
    $this->get('/admin/register')->assertOk();
});

it('can register a new tenant and user', function () {
    $preset = VerticalPreset::where('slug', 'cleaning')->firstOrFail();

    Livewire::test(\App\Filament\Pages\Auth\Register::class)
        ->set('data.name', 'Ania Cleaning')
        ->set('data.email', 'ania@example.com')
        ->set('data.password', 'password123')
        ->set('data.password_confirmation', 'password123')
        ->set('data.preset_id', $preset->id)
        ->call('register')
        ->assertHasNoErrors();

    $user = Tenant::bypass(fn () => User::where('email', 'ania@example.com')->first());
    expect($user)->not->toBeNull();

    $tenant = Tenant::bypass(fn () => Tenant::find($user->tenant_id));
    expect($tenant)->not->toBeNull()
        ->and($tenant->preset_id)->toBe($preset->id)
        ->and($tenant->company_name)->toBe('Ania Cleaning');
});

it('registration fails with duplicate email', function () {
    $preset = VerticalPreset::where('slug', 'cleaning')->firstOrFail();
    $tenant = Tenant::factory()->create();
    Tenant::bypass(fn () => User::factory()->for($tenant, 'tenant')->create(['email' => 'taken@example.com']));

    Livewire::test(\App\Filament\Pages\Auth\Register::class)
        ->set('data.name', 'Other')
        ->set('data.email', 'taken@example.com')
        ->set('data.password', 'password123')
        ->set('data.password_confirmation', 'password123')
        ->set('data.preset_id', $preset->id)
        ->call('register')
        ->assertHasErrors(['data.email']);
});
