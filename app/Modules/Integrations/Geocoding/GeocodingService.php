<?php

namespace App\Modules\Integrations\Geocoding;

use App\Modules\Crm\Models\Address;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    public function geocode(Address $address): void
    {
        $query = implode(', ', array_filter([
            $address->line1,
            $address->postcode,
            $address->city,
            'Polska',
        ]));

        $cacheKey = 'geocode:' . md5($query);

        $result = cache()->rememberForever($cacheKey, function () use ($query) {
            try {
                $response = Http::timeout(5)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                    'address' => $query,
                    'key' => config('services.google_maps.api_key'),
                    'language' => 'pl',
                    'region' => 'pl',
                ]);

                $data = $response->json();

                if (($data['status'] ?? '') !== 'OK' || empty($data['results'])) {
                    return null;
                }

                return $data['results'][0]['geometry']['location'];
            } catch (\Exception $e) {
                Log::warning('Geocoding failed: ' . $e->getMessage(), ['query' => $query]);
                return null;
            }
        });

        if ($result === null) {
            return;
        }

        $address->lat = $result['lat'];
        $address->lng = $result['lng'];
        $address->geocoded_at = now();
        $address->save();
    }
}
