<?php

use App\Modules\Presets\Preset;
use App\Modules\Presets\PresetRegistry;
use App\Modules\Tenancy\Models\Tenant;
use Database\Seeders\CleaningPresetSeeder;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(TestCase::class, RefreshDatabase::class);

afterEach(fn () => Tenant::clear());

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

    DB::enableQueryLog();
    PresetRegistry::for($tenant);
    PresetRegistry::for($tenant);
    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    // Only one SELECT to vertical_presets (second call is cache hit)
    $presetQueries = array_filter($queries, fn ($q) => str_contains($q['query'], 'vertical_presets'));
    expect(count($presetQueries))->toBe(1);
});

it('busts cache on VerticalPresetUpdated event', function () {
    $tenant = Tenant::factory()->create(['preset_id' => \App\Modules\Presets\Models\VerticalPreset::where('slug', 'cleaning')->value('id')]);

    PresetRegistry::for($tenant); // warm cache

    event(new \App\Modules\Presets\Events\VerticalPresetUpdated($tenant->preset_id));

    // After bust, a second DB query must fire (cache miss)
    DB::enableQueryLog();
    $preset = PresetRegistry::for($tenant);
    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    expect($preset)->toBeInstanceOf(Preset::class);

    $presetQueries = array_filter($queries, fn ($q) => str_contains($q['query'], 'vertical_presets'));
    expect(count($presetQueries))->toBe(1);
});
