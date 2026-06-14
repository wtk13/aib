<?php

use App\Modules\Crm\Models\Client;
use App\Modules\Quoting\Models\Quote;
use App\Modules\Quoting\Models\QuoteItem;
use App\Modules\Quoting\Services\QuoteNumberingService;
use App\Modules\Tenancy\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function quoteContext(): array
{
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);
    $client = Client::create(['name' => 'Test Klient']);

    return [$tenant, $client];
}

it('quote belongs to tenant and tenant_id is set automatically', function () {
    [$tenant, $client] = quoteContext();

    $quote = Quote::create([
        'client_id' => $client->id,
        'number' => '2026/06/001',
        'status' => 'draft',
        'issued_at' => now(),
        'subtotal' => '100.00',
        'total' => '123.00',
    ]);

    expect($quote->tenant_id)->toBe($tenant->id);
    expect($quote->tenant->id)->toBe($tenant->id);
});

it('quote scopes to tenant so tenant B cannot see tenant A quotes', function () {
    [$tenantA, $clientA] = quoteContext();

    Quote::create([
        'client_id' => $clientA->id,
        'number' => '2026/06/001',
        'status' => 'draft',
        'issued_at' => now(),
        'subtotal' => '100.00',
        'total' => '123.00',
    ]);

    // Switch to tenant B
    $tenantB = Tenant::factory()->create();
    Tenant::setCurrent($tenantB);

    expect(Quote::count())->toBe(0);
});

it('quote has items relationship and returns correct count', function () {
    [$tenant, $client] = quoteContext();

    $quote = Quote::create([
        'client_id' => $client->id,
        'number' => '2026/06/001',
        'status' => 'draft',
        'issued_at' => now(),
        'subtotal' => '200.00',
        'total' => '246.00',
    ]);

    QuoteItem::create([
        'quote_id' => $quote->id,
        'position' => 1,
        'description' => 'Sprzątanie podstawowe',
        'unit' => 'piece',
        'quantity' => '1.00',
        'rate' => '100.00',
        'line_total' => '100.00',
    ]);

    QuoteItem::create([
        'quote_id' => $quote->id,
        'position' => 2,
        'description' => 'Mycie okien',
        'unit' => 'piece',
        'quantity' => '1.00',
        'rate' => '100.00',
        'line_total' => '100.00',
    ]);

    expect($quote->items()->count())->toBe(2);
});

it('QuoteNumberingService generates correct format', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);

    $service = new QuoteNumberingService();
    $date = new \DateTime('2026-06-01');

    $number = $service->next($tenant->id, $date);

    expect($number)->toBe('2026/06/001');
});

it('QuoteNumberingService increments correctly on subsequent calls', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);

    $service = new QuoteNumberingService();
    $date = new \DateTime('2026-06-01');

    $first = $service->next($tenant->id, $date);
    $second = $service->next($tenant->id, $date);

    expect($first)->toBe('2026/06/001');
    expect($second)->toBe('2026/06/002');
});

it('QuoteNumberingService does not blow up on concurrent calls from same tenant and month', function () {
    $tenant = Tenant::factory()->create();
    Tenant::setCurrent($tenant);

    $service = new QuoteNumberingService();
    $date = new \DateTime('2026-06-01');

    $numbers = [];
    for ($i = 0; $i < 5; $i++) {
        $numbers[] = $service->next($tenant->id, $date);
    }

    expect($numbers)->toHaveCount(5);
    expect(array_unique($numbers))->toHaveCount(5);
    expect($numbers[0])->toBe('2026/06/001');
    expect($numbers[4])->toBe('2026/06/005');
});

it('quote isEditable returns true for draft and false for sent', function () {
    [$tenant, $client] = quoteContext();

    $draft = Quote::create([
        'client_id' => $client->id,
        'number' => '2026/06/001',
        'status' => 'draft',
        'issued_at' => now(),
        'subtotal' => '100.00',
        'total' => '123.00',
    ]);

    $sent = Quote::create([
        'client_id' => $client->id,
        'number' => '2026/06/002',
        'status' => 'sent',
        'issued_at' => now(),
        'subtotal' => '100.00',
        'total' => '123.00',
    ]);

    expect($draft->isEditable())->toBeTrue();
    expect($sent->isEditable())->toBeFalse();
});
