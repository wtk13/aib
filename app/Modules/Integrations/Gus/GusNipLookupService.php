<?php

namespace App\Modules\Integrations\Gus;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GusNipLookupService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.gus.base_url'), '/');
        $this->apiKey = config('services.gus.api_key');
    }

    public function lookup(string $nip): ?array
    {
        $cacheKey = 'gus:nip:' . $nip;

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        try {
            $sessionId = $this->login();
            if ($sessionId === null) {
                return null;
            }

            $data = $this->search($sessionId, $nip);
            $this->logout($sessionId);

            if ($data !== null) {
                Cache::put($cacheKey, $data, now()->addDays(30));
            }

            return $data;
        } catch (\Exception $e) {
            Log::warning('GUS NIP lookup failed', ['nip' => $nip, 'error' => $e->getMessage()]);
            return null;
        }
    }

    private function login(): ?string
    {
        $response = Http::timeout(5)
            ->withHeader('userKey', $this->apiKey)
            ->post($this->baseUrl . '/Login');

        if (! $response->successful()) {
            return null;
        }

        return $response->json('sessionId');
    }

    private function search(string $sessionId, string $nip): ?array
    {
        $response = Http::timeout(5)
            ->withHeader('sid', $sessionId)
            ->post($this->baseUrl . '/DaneSzukajPodmioty', ['Nip' => $nip]);

        if (! $response->successful()) {
            return null;
        }

        $data = $response->json();

        if (empty($data) || ! isset($data['name'])) {
            return null;
        }

        return [
            'name'     => $data['name'],
            'line1'    => $data['street'] ?? '',
            'city'     => $data['city'] ?? '',
            'postcode' => $data['postcode'] ?? '',
            'regon'    => $data['regon'] ?? '',
        ];
    }

    private function logout(string $sessionId): void
    {
        Http::timeout(3)
            ->withHeader('sid', $sessionId)
            ->post($this->baseUrl . '/Wyloguj');
    }
}
