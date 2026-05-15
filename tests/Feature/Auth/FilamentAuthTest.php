<?php

use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('ania can log in to Filament and see her tenant scope', function () {
    $this->seed(\Database\Seeders\CleaningPresetSeeder::class);
    $preset = \App\Modules\Presets\Models\VerticalPreset::where('slug', 'cleaning')->first();
    $tenant = Tenant::factory()->create(['slug' => 'ania-test', 'preset_id' => $preset?->id]);
    \Illuminate\Support\Facades\DB::table('users')->insert([
        'tenant_id'  => $tenant->id,
        'name'       => 'Ania',
        'email'      => 'ania-test@wyceny.app',
        'password'   => bcrypt('password'),
        'role'       => 'owner',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->post('/admin/login', [
        'email'    => 'ania-test@wyceny.app',
        'password' => 'password',
    ])->assertRedirect('/admin');
});

it('app subdomain returns noindex header', function () {
    $response = $this->get('/admin/login');

    $response->assertHeader('X-Robots-Tag', 'noindex, nofollow');
});
