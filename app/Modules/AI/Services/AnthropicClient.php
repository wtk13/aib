<?php

namespace App\Modules\AI\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class AnthropicClient
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';
    private const API_VERSION = '2023-06-01';

    public function __construct(private readonly Client $http) {}

    /**
     * Send a messages request to the Anthropic API.
     *
     * @param  array<array{role: string, content: string}>  $messages
     * @return array{content: string, input_tokens: int, output_tokens: int, latency_ms: int}|null
     */
    public function messages(
        string $model,
        string $system,
        array $messages,
        int $maxTokens = 1024,
    ): ?array {
        $apiKey = config('services.anthropic.key');

        if (empty($apiKey)) {
            Log::warning('AnthropicClient: no API key configured, skipping request');

            return null;
        }

        $start = (int) (microtime(true) * 1000);

        try {
            $response = $this->http->post(self::API_URL, [
                'headers' => [
                    'x-api-key' => $apiKey,
                    'anthropic-version' => self::API_VERSION,
                    'content-type' => 'application/json',
                ],
                'json' => [
                    'model' => $model,
                    'max_tokens' => $maxTokens,
                    'system' => $system,
                    'messages' => $messages,
                ],
            ]);

            $latencyMs = (int) (microtime(true) * 1000) - $start;
            $body = json_decode((string) $response->getBody(), true);

            $content = $body['content'][0]['text'] ?? '';
            $inputTokens = $body['usage']['input_tokens'] ?? 0;
            $outputTokens = $body['usage']['output_tokens'] ?? 0;

            return [
                'content' => $content,
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'latency_ms' => $latencyMs,
            ];
        } catch (GuzzleException $e) {
            Log::error('AnthropicClient: network error', ['error' => $e->getMessage()]);

            return null;
        }
    }
}
