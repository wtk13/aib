<?php

namespace App\Modules\Integrations\Distance;

use App\Modules\Crm\Models\Address;
use Illuminate\Support\Facades\DB;

class DistanceService
{
    public function getDistance(
        int $tenantId,
        Address $origin,
        Address $destination,
        float $fuelRatePln = 1.80,
    ): ?DistanceResult {
        if ($origin->lat === null || $origin->lng === null) {
            return null;
        }
        if ($destination->lat === null || $destination->lng === null) {
            return null;
        }

        $cached = DB::table('distance_caches')
            ->where('tenant_id', $tenantId)
            ->where('origin_address_id', $origin->id)
            ->where('destination_address_id', $destination->id)
            ->first();

        if ($cached !== null) {
            $km = $cached->distance_meters / 1000.0;
            return new DistanceResult($km, $km * 2 * $fuelRatePln);
        }

        $distanceMeters = $this->haversineMeters(
            (float) $origin->lat,
            (float) $origin->lng,
            (float) $destination->lat,
            (float) $destination->lng,
        );

        $distanceMetersRounded = (int) round($distanceMeters);

        DB::table('distance_caches')->insert([
            'tenant_id'              => $tenantId,
            'origin_address_id'      => $origin->id,
            'destination_address_id' => $destination->id,
            'distance_meters'        => $distanceMetersRounded,
            'duration_seconds'       => 0,
            'raw_response'           => json_encode(['source' => 'haversine']),
        ]);

        $km = $distanceMetersRounded / 1000.0;
        return new DistanceResult($km, $km * 2 * $fuelRatePln);
    }

    private function haversineMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371000.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
