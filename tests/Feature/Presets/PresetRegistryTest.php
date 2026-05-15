<?php

use App\Modules\Presets\Preset;
use App\Modules\Presets\PresetRegistry;
use App\Modules\Tenancy\Models\Tenant;
use Database\Seeders\CleaningPresetSeeder;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CleaningPresetSeeder::class);
});

it('returns cleaning preset for tenant', function () {
    $tenant = Tenant::factory()->create(['preset_id' => \App\Modules\Presets\Models\VerticalPreset::where('slug', 'cleaning')->value('id')]);
    Tenant::setCurrent($tenant);

    $preset = PresetRegistry::for($tenant);

    expect($preset)->toBeInstanceOf(Preset::class);
    expect($preset->serviceTypes())->toHaveCount(5);
    expect($preset->serviceTypes()[0]['key'])->toBe('basic');

    Tenant::clear();
});

it('caches preset and returns same instance', function () {
    $tenant = Tenant::factory()->create(['preset_id' => \App\Modules\Presets\Models\VerticalPreset::where('slug', 'cleaning')->value('id')]);

    $first  = PresetRegistry::for($tenant);
    $second = PresetRegistry::for($tenant);

    expect($first->slug())->toBe($second->slug());
});

it('busts cache on VerticalPresetUpdated event', function () {
    $tenant = Tenant::factory()->create(['preset_id' => \App\Modules\Presets\Models\VerticalPreset::where('slug', 'cleaning')->value('id')]);
    Tenant::setCurrent($tenant);

    PresetRegistry::for($tenant); // warm cache

    event(new \App\Modules\Presets\Events\VerticalPresetUpdated($tenant->preset_id));

    $preset = PresetRegistry::for($tenant);
    expect($preset)->toBeInstanceOf(Preset::class);

    Tenant::clear();
});
