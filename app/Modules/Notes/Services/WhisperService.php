<?php

namespace App\Modules\Notes\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class WhisperService
{
    public function __construct(private readonly Client $http) {}

    public function transcribe(string $storagePath, string $language = 'pl'): ?string
    {
        $apiKey = config('services.openai.key');

        if (empty($apiKey)) {
            Log::warning('WhisperService: OPENAI_API_KEY not set');

            return null;
        }

        $absolutePath = Storage::disk('local')->path($storagePath);

        if (! file_exists($absolutePath)) {
            Log::warning('WhisperService: audio file not found', ['path' => $absolutePath]);

            return null;
        }

        try {
            $response = $this->http->post('https://api.openai.com/v1/audio/transcriptions', [
                'headers' => [
                    'Authorization' => 'Bearer '.$apiKey,
                ],
                'multipart' => [
                    ['name' => 'model', 'contents' => 'whisper-1'],
                    ['name' => 'language', 'contents' => $language],
                    ['name' => 'response_format', 'contents' => 'text'],
                    [
                        'name'     => 'file',
                        'contents' => fopen($absolutePath, 'r'),
                        'filename' => basename($absolutePath),
                    ],
                ],
            ]);

            return trim((string) $response->getBody());
        } catch (GuzzleException $e) {
            Log::warning('WhisperService: transcription failed', ['error' => $e->getMessage()]);

            return null;
        }
    }
}
