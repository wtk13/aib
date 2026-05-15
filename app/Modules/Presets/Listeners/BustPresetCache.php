<?php

namespace App\Modules\Presets\Listeners;

use App\Modules\Presets\Events\VerticalPresetUpdated;
use App\Modules\Presets\PresetRegistry;
use App\Modules\Tenancy\Models\Tenant;

class BustPresetCache
{
    public function handle(VerticalPresetUpdated $event): void
    {
        Tenant::bypass(function () use ($event) {
            Tenant::where('preset_id', $event->presetId)
                ->pluck('id')
                ->each(fn ($id) => PresetRegistry::forgetTenant($id));
        });
    }
}
