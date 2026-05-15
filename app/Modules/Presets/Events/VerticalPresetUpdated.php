<?php

namespace App\Modules\Presets\Events;

class VerticalPresetUpdated
{
    public function __construct(public readonly int $presetId) {}
}
