<?php

use App\Console\Commands\ExpireOverdueQuotes;
use App\Modules\Crm\Models\Client;
use App\Modules\Quoting\Models\Quote;
use App\Modules\Quoting\Models\QuoteStatusLog;
use App\Modules\Quoting\Services\QuoteTransitionService;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function statusTestContext(): array
{
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);
    $client = Client::create(['name' => 'Test Client']);

    return [$tenant, $client];
}

function makeQuote(string $status = 'draft', ?string $validUntil = null): Quote
{
    [$tenant, $client] = statusTestContext();

    return Quote::create([
        'client_id' => $client->id,
        'number' => '2026/06/001',
        'status' => $status,
        'issued_at' => now(),
        'valid_until' => $validUntil,
        'subtotal' => 500,
        'vat_rate' => 23,
        'total' => 615,
    ]);
}

it('can transition quote from draft to sent', function () {
    $quote = makeQuote('draft');

    $service = app(QuoteTransitionService::class);
    $service->transition($quote, 'sent');

    $quote->refresh();

    expect($quote->status)->toBe('sent');
    expect($quote->sent_at)->not->toBeNull();

    $log = QuoteStatusLog::first();
    expect($log)->not->toBeNull();
    expect($log->from_status)->toBe('draft');
    expect($log->to_status)->toBe('sent');
});

it('can transition quote from sent to accepted', function () {
    $quote = makeQuote('sent');

    $service = app(QuoteTransitionService::class);
    $service->transition($quote, 'accepted');

    $quote->refresh();

    expect($quote->status)->toBe('accepted');
    expect($quote->decided_at)->not->toBeNull();

    $log = QuoteStatusLog::first();
    expect($log)->not->toBeNull();
    expect($log->from_status)->toBe('sent');
    expect($log->to_status)->toBe('accepted');
});

it('can transition quote from sent to rejected', function () {
    $quote = makeQuote('sent');

    $service = app(QuoteTransitionService::class);
    $service->transition($quote, 'rejected');

    $quote->refresh();

    expect($quote->status)->toBe('rejected');
    expect($quote->decided_at)->not->toBeNull();

    $log = QuoteStatusLog::first();
    expect($log)->not->toBeNull();
    expect($log->from_status)->toBe('sent');
    expect($log->to_status)->toBe('rejected');
});

it('expire command expires overdue sent quotes', function () {
    $quote = makeQuote('sent', now()->subDay()->toDateString());

    $this->artisan(ExpireOverdueQuotes::class)->assertSuccessful();

    $quote->refresh();

    expect($quote->status)->toBe('expired');
    expect($quote->expired_at)->not->toBeNull();
});

it('expire command does not expire quotes with valid_until in future', function () {
    $quote = makeQuote('sent', now()->addDay()->toDateString());

    $this->artisan(ExpireOverdueQuotes::class)->assertSuccessful();

    $quote->refresh();

    expect($quote->status)->toBe('sent');
});

it('expire command does not expire accepted quotes', function () {
    $quote = makeQuote('accepted', now()->subDay()->toDateString());

    $this->artisan(ExpireOverdueQuotes::class)->assertSuccessful();

    $quote->refresh();

    expect($quote->status)->toBe('accepted');
});
