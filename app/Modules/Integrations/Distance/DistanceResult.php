<?php

namespace App\Modules\Integrations\Distance;

readonly class DistanceResult
{
    public string $label;

    public function __construct(
        public float $distanceKm,
        public float $commuteCostPln,
    ) {
        $this->label = sprintf('~%d km · ~%d PLN', (int) round($distanceKm), (int) round($commuteCostPln));
    }
}
