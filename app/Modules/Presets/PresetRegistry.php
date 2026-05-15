<?php

namespace App\Modules\Presets;

use App\Modules\Presets\Models\VerticalPreset;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Support\Facades\Cache;

final class PresetRegistry
{
    private static int $ttl = 3600;

    public static function for(Tenant $tenant): Preset
    {
        if ($tenant->preset_id === null) {
            throw new \RuntimeException("Tenant [{$tenant->id}] has no preset assigned.");
        }

        $cacheKey = "preset:tenant:{$tenant->id}";

        return Cache::remember($cacheKey, static::$ttl, function () use ($tenant) {
            $model = VerticalPreset::findOrFail($tenant->preset_id);
            return Preset::fromModel($model);
        });
    }

    public static function forgetTenant(int $tenantId): void
    {
        Cache::forget("preset:tenant:{$tenantId}");
    }
}
