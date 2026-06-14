<?php

use App\Modules\Crm\Models\Client;
use App\Modules\Presets\Models\VerticalPreset;
use App\Modules\Quoting\Models\Quote;
use App\Modules\Quoting\Models\QuoteShareToken;
use App\Modules\Quoting\Services\QuoteShareService;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\User;
use Database\Seeders\CleaningPresetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CleaningPresetSeeder::class);
});

function pdfShareContext(): array
{
    $preset = VerticalPreset::where('slug', 'cleaning')->first();
    $tenant = Tenant::factory()->create(['preset_id' => $preset?->id]);
    $user = Tenant::bypass(fn () => User::factory()->for($tenant, 'tenant')->create());
    Tenant::setCurrent($tenant);
    $client = Client::create(['name' => 'PDF Test Klient']);

    return [$user, $client, $tenant];
}

function makePdfQuote(string $status = 'draft', ?array $context = null): array
{
    [$user, $client, $tenant] = $context ?? pdfShareContext();

    $quote = Quote::create([
        'client_id' => $client->id,
        'number' => '2026/06/TEST',
        'status' => $status,
        'issued_at' => now(),
        'subtotal' => '500.00',
        'vat_rate' => 23,
        'total' => '615.00',
    ]);

    return [$user, $client, $tenant, $quote];
}

// ─── PDF ──────────────────────────────────────────────────────────────────────

it('pdf route returns 200 for authenticated quote owner', function () {
    [$user, $client, $tenant, $quote] = makePdfQuote('draft');

    $response = $this->actingAs($user)->get(route('quote.pdf', $quote));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/pdf');
});

// ─── Share token ──────────────────────────────────────────────────────────────

it('share token is created for sent quote', function () {
    [$user, $client, $tenant, $quote] = makePdfQuote('sent');

    $service = app(QuoteShareService::class);
    $url = $service->createLink($quote);

    $this->assertDatabaseHas('quote_share_tokens', [
        'quote_id' => $quote->id,
    ]);

    expect($url)->toContain('/wycena/');
});

it('public quote view returns 200 with valid token', function () {
    [$user, $client, $tenant, $quote] = makePdfQuote('sent');

    $token = QuoteShareToken::create([
        'quote_id' => $quote->id,
        'tenant_id' => $tenant->id,
        'token' => str_repeat('a', 64),
        'expires_at' => now()->addDays(30),
    ]);

    $response = $this->get(route('quote.public', $token->token));

    $response->assertStatus(200);
    $response->assertSee($quote->number);
});

it('public accept sets accepted_at and transitions quote to accepted', function () {
    [$user, $client, $tenant, $quote] = makePdfQuote('sent');

    $token = QuoteShareToken::create([
        'quote_id' => $quote->id,
        'tenant_id' => $tenant->id,
        'token' => str_repeat('b', 64),
        'expires_at' => now()->addDays(30),
    ]);

    $response = $this->post(route('quote.public.accept', $token->token));

    $response->assertRedirect(route('quote.public', $token->token));

    $quote->refresh();
    expect($quote->status)->toBe('accepted');

    $token->refresh();
    expect($token->accepted_at)->not->toBeNull();
});

it('expired token returns 404', function () {
    [$user, $client, $tenant, $quote] = makePdfQuote('sent');

    $token = QuoteShareToken::create([
        'quote_id' => $quote->id,
        'tenant_id' => $tenant->id,
        'token' => str_repeat('c', 64),
        'expires_at' => now()->subDay(),
    ]);

    $response = $this->get(route('quote.public', $token->token));

    $response->assertStatus(404);
});
