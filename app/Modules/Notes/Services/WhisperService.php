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

        // Guard against path traversal: ensure resolved path stays within storage root
        $storageRoot = realpath(Storage::disk('local')->path(''));
        $realPath = realpath($absolutePath);
        if ($realPath === false || $storageRoot === false || ! str_starts_with($realPath, $storageRoot.DIRECTORY_SEPARATOR)) {
            Log::warning('WhisperService: path traversal attempt', ['path' => $storagePath]);

            return null;
        }

        if (! file_exists($absolutePath)) {
            Log::warning('WhisperService: audio file not found', ['path' => $storagePath]);

            return null;
        }

        $handle = fopen($absolutePath, 'r');

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
                        'contents' => $handle,
                        'filename' => basename($absolutePath),
                    ],
                ],
            ]);

            return trim((string) $response->getBody());
        } catch (GuzzleException $e) {
            Log::warning('WhisperService: transcription failed', ['error' => $e->getMessage()]);

            return null;
        } finally {
            if (is_resource($handle)) {
                fclose($handle);
            }
        }
    }
}
