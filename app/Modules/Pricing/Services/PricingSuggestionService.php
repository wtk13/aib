<?php

namespace App\Modules\Pricing\Services;

use App\Modules\AI\Models\AIUsageLog;
use App\Modules\AI\Services\AnthropicClient;
use App\Modules\Crm\Models\Client;
use App\Modules\Pricing\Models\PricingSuggestion;
use App\Modules\Scheduling\Models\Job;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Support\Facades\Log;

class PricingSuggestionService
{
    private const MODEL = 'claude-haiku-4-5-20251001';
    private const PROMPT_VERSION = 'pricing_v1';

    public function __construct(
        private readonly AnthropicClient $anthropic,
    ) {}

    public function suggest(
        Client $client,
        ?Job $job = null,
        ?string $serviceTypeKey = null,
    ): ?PricingSuggestion {
        try {
            $contextBuilder = new PricingContextBuilder($client, $job, $serviceTypeKey);
            $context = $contextBuilder->build();

            $systemPrompt = file_get_contents(app_path('Prompts/pricing_v1.md'));
            if ($systemPrompt === false) {
                Log::error('PricingSuggestionService: could not load prompt file');

                return null;
            }

            $userMessage = 'Generate a pricing suggestion for the following client and context: '
                . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

            $result = $this->anthropic->messages(
                model: self::MODEL,
                system: $systemPrompt,
                messages: [
                    ['role' => 'user', 'content' => $userMessage],
                ],
                maxTokens: 1024,
            );

            if ($result === null) {
                return null;
            }

            $content = $result['content'];

            // Strip markdown fences if present
            $content = preg_replace('/^```(?:json)?\s*/m', '', $content);
            $content = preg_replace('/\s*```$/m', '', $content);
            $content = trim($content);

            $parsed = json_decode($content, true);

            if (! is_array($parsed)
                || ! isset($parsed['suggested_total'])
                || ! isset($parsed['breakdown'])
            ) {
                Log::error('PricingSuggestionService: invalid AI response structure', [
                    'content' => $content,
                ]);

                return null;
            }

            // Cost: Haiku pricing approx. × 4 PLN/USD
            $costPln = ($result['input_tokens'] / 1_000_000 * 1.0)
                + ($result['output_tokens'] / 1_000_000 * 5.0);

            $tenantId = Tenant::currentId();

            $usageLog = AIUsageLog::create([
                'tenant_id' => $tenantId,
                'feature' => 'pricing_suggestion',
                'provider' => 'anthropic',
                'model' => self::MODEL,
                'prompt_version' => self::PROMPT_VERSION,
                'input_tokens' => $result['input_tokens'],
                'output_tokens' => $result['output_tokens'],
                'cost_pln' => round($costPln, 6),
                'latency_ms' => $result['latency_ms'],
                'status' => 'ok',
            ]);

            $suggestion = PricingSuggestion::create([
                'tenant_id' => $tenantId,
                'quote_id' => 0,
                'suggested_total' => $parsed['suggested_total'],
                'breakdown' => $parsed['breakdown'],
                'reasoning' => $parsed['reasoning'] ?? null,
                'confidence' => $parsed['confidence'] ?? null,
                'prompt_version' => self::PROMPT_VERSION,
                'ai_usage_log_id' => $usageLog->id,
            ]);

            return $suggestion;
        } catch (\Throwable $e) {
            Log::error('PricingSuggestionService: unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }
}
