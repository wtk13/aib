<?php

use App\Modules\AI\Models\AIUsageLog;
use App\Modules\AI\Services\AnthropicClient;
use App\Modules\Crm\Models\Client;
use App\Modules\Pricing\Models\PricingSuggestion;
use App\Modules\Pricing\Models\PricingSuggestionFeedback;
use App\Modules\Pricing\Services\PricingContextBuilder;
use App\Modules\Pricing\Services\PricingSuggestionFeedbackRecorder;
use App\Modules\Pricing\Services\PricingSuggestionService;
use App\Modules\Quoting\Models\Quote;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function pricingContext(): array
{
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);
    $client = Client::create(['name' => 'Pani Kowalska']);

    return [$tenant, $client];
}

it('context builder includes client name and custom fields', function () {
    [$tenant, $client] = pricingContext();

    $client->update(['custom_fields' => ['area_m2' => 80, 'property_type' => 'apartment']]);

    $builder = new PricingContextBuilder();
    $context = $builder->build($client);

    expect($context['client']['name'])->toBe('Pani Kowalska');
    expect($context['client']['area_m2'])->toBe(80);
});

it('context builder marks cold_start true when no past quotes', function () {
    [$tenant, $client] = pricingContext();

    $builder = new PricingContextBuilder();
    $context = $builder->build($client);

    expect($context['cold_start'])->toBeTrue();
});

it('suggestion service returns null when anthropic client returns null', function () {
    [$tenant, $client] = pricingContext();

    $mockClient = Mockery::mock(AnthropicClient::class);
    $mockClient->shouldReceive('messages')->andReturn(null);

    $service = new PricingSuggestionService($mockClient, new PricingContextBuilder());

    $result = $service->suggest($client);

    expect($result)->toBeNull();
});

it('suggestion service creates PricingSuggestion from valid response', function () {
    [$tenant, $client] = pricingContext();

    $validResponse = json_encode([
        'suggested_total' => 450,
        'breakdown' => [
            ['description' => 'Sprzątanie', 'unit' => 'piece', 'quantity' => 1, 'rate' => 450, 'line_total' => 450],
        ],
        'reasoning' => 'Based on area',
        'confidence' => 0.85,
    ]);

    $mockClient = Mockery::mock(AnthropicClient::class);
    $mockClient->shouldReceive('messages')->andReturn([
        'content' => $validResponse,
        'input_tokens' => 100,
        'output_tokens' => 50,
        'latency_ms' => 200,
    ]);

    $service = new PricingSuggestionService($mockClient, new PricingContextBuilder());

    $suggestion = $service->suggest($client);

    expect($suggestion)->toBeInstanceOf(PricingSuggestion::class);
    expect((float) $suggestion->suggested_total)->toBe(450.0);
    expect(AIUsageLog::count())->toBe(1);
});

it('suggestion service returns null on invalid JSON', function () {
    [$tenant, $client] = pricingContext();

    $mockClient = Mockery::mock(AnthropicClient::class);
    $mockClient->shouldReceive('messages')->andReturn([
        'content' => 'This is not valid JSON at all',
        'input_tokens' => 10,
        'output_tokens' => 5,
        'latency_ms' => 100,
    ]);

    $service = new PricingSuggestionService($mockClient, new PricingContextBuilder());

    $result = $service->suggest($client);

    expect($result)->toBeNull();
});

it('feedback recorder sets decision accepted for diff less than 15 percent', function () {
    [$tenant, $client] = pricingContext();

    $quote = Quote::create([
        'client_id' => $client->id,
        'number' => '2026/06/001',
        'status' => 'draft',
        'issued_at' => now(),
        'subtotal' => '400.00',
        'total' => '420.00',
    ]);

    $suggestion = PricingSuggestion::create([
        'quote_id' => $quote->id,
        'suggested_total' => 400,
        'breakdown' => [],
        'prompt_version' => 'pricing_v1',
    ]);

    $recorder = new PricingSuggestionFeedbackRecorder();
    $recorder->record($suggestion, 420.0);

    $feedback = PricingSuggestionFeedback::first();

    expect($feedback->decision)->toBe('accepted');
    expect((float) $feedback->diff_pct)->toBe(5.0);
});

it('feedback recorder sets decision adjusted for diff between 15 and 50 percent', function () {
    [$tenant, $client] = pricingContext();

    $quote = Quote::create([
        'client_id' => $client->id,
        'number' => '2026/06/001',
        'status' => 'draft',
        'issued_at' => now(),
        'subtotal' => '400.00',
        'total' => '550.00',
    ]);

    $suggestion = PricingSuggestion::create([
        'quote_id' => $quote->id,
        'suggested_total' => 400,
        'breakdown' => [],
        'prompt_version' => 'pricing_v1',
    ]);

    $recorder = new PricingSuggestionFeedbackRecorder();
    $recorder->record($suggestion, 550.0);

    $feedback = PricingSuggestionFeedback::first();

    expect($feedback->decision)->toBe('adjusted');
});
