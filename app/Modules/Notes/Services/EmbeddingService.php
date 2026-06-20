<?php

namespace App\Modules\Notes\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    public const MODEL = 'text-embedding-3-small';

    public function __construct(private readonly Client $http) {}

    /** @return float[]|null */
    public function embed(string $text): ?array
    {
        $apiKey = config('services.openai.key');

        if (empty($apiKey)) {
            Log::warning('EmbeddingService: OPENAI_API_KEY not set');

            return null;
        }

        try {
            $response = $this->http->post('https://api.openai.com/v1/embeddings', [
                'headers' => [
                    'Authorization' => 'Bearer '.$apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'model' => self::MODEL,
                    'input' => mb_substr($text, 0, 8000),
                ],
            ]);

            $body = json_decode((string) $response->getBody(), true);

            return $body['data'][0]['embedding'] ?? null;
        } catch (GuzzleException $e) {
            Log::warning('EmbeddingService: embed failed', ['error' => $e->getMessage()]);

            return null;
        }
    }

    public static function vectorToPostgres(array $floats): string
    {
        return '['.implode(',', $floats).']';
    }
}
