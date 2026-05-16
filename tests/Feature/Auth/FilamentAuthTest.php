<?php

use App\Modules\Presets\Models\VerticalPreset;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\User;
use Database\Seeders\CleaningPresetSeeder;
use Filament\Pages\Auth\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('a user can log in to Filament under their tenant scope', function () {
    $this->seed(CleaningPresetSeeder::class);
    $preset = VerticalPreset::where('slug', 'cleaning')->first();

    $tenant = Tenant::factory()->create(['preset_id' => $preset?->id]);
    $user = Tenant::bypass(fn () => User::factory()->for($tenant, 'tenant')->create([
        'email' => 'login-test@aib.app',
        'password' => 'password',
    ]));

    Livewire::test(Login::class)
        ->set('data.email', 'login-test@aib.app')
        ->set('data.password', 'password')
        ->call('authenticate')
        ->assertRedirect('/admin');
});

it('app subdomain returns noindex header', function () {
    $response = $this->get('/admin/login');

    $response->assertHeader('X-Robots-Tag', 'noindex, nofollow');
});
