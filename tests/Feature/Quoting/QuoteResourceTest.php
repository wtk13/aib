<?php

use App\Modules\Crm\Models\Client;
use App\Modules\Presets\Models\VerticalPreset;
use App\Modules\Quoting\Filament\Resources\QuoteResource\Pages\CreateQuote;
use App\Modules\Quoting\Filament\Resources\QuoteResource\Pages\EditQuote;
use App\Modules\Quoting\Filament\Resources\QuoteResource\Pages\ListQuotes;
use App\Modules\Quoting\Models\Quote;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\TenantSettings;
use App\Modules\Tenancy\Models\User;
use Database\Seeders\CleaningPresetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

beforeEach(function () {
    $this->seed(CleaningPresetSeeder::class);
});

function quoteOwner(): array
{
    $preset = VerticalPreset::where('slug', 'cleaning')->first();
    $tenant = Tenant::factory()->create(['preset_id' => $preset?->id]);
    $user = Tenant::bypass(fn () => User::factory()->for($tenant, 'tenant')->create());
    Tenant::setCurrent($tenant);

    $client = Client::create(['name' => 'Klient Testowy']);

    return [$user, $client, $tenant];
}

// ─── List ─────────────────────────────────────────────────────────────────────

it('can list quotes', function () {
    [$user, $client] = quoteOwner();

    $quote = Quote::create([
        'client_id' => $client->id,
        'number' => '2026/06/001',
        'status' => 'draft',
        'issued_at' => now(),
        'subtotal' => '100.00',
        'total' => '123.00',
    ]);

    Livewire::actingAs($user)
        ->test(ListQuotes::class)
        ->assertCanSeeTableRecords([$quote]);
});

// ─── Create ───────────────────────────────────────────────────────────────────

it('quote number is auto-generated on create', function () {
    [$user, $client, $tenant] = quoteOwner();

    Livewire::actingAs($user)
        ->test(CreateQuote::class)
        ->fillForm([
            'client_id' => $client->id,
            'issued_at' => '2026-06-14',
            'valid_until' => null,
            'internal_note' => null,
        ])
        ->call('create')
        ->assertHasNoErrors();

    $quote = Quote::first();
    expect($quote)->not->toBeNull();
    expect($quote->number)->toMatch('/^\d{4}\/\d{2}\/\d{3}$/');
});

it('can create a quote with items and calculates totals', function () {
    [$user, $client, $tenant] = quoteOwner();

    // Set up tenant settings with VAT
    TenantSettings::updateOrCreate(
        ['tenant_id' => $tenant->id],
        ['is_vat_payer' => true, 'default_vat_rate' => 23]
    );

    Livewire::actingAs($user)
        ->test(CreateQuote::class)
        ->fillForm([
            'client_id' => $client->id,
            'issued_at' => '2026-06-14',
            'valid_until' => null,
            'internal_note' => null,
            'items' => [
                [
                    'description' => 'Sprzątanie podstawowe',
                    'unit' => 'piece',
                    'quantity' => 2,
                    'rate' => 100,
                    'discount_pct' => 0,
                    'vat_pct' => 23,
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoErrors();

    $quote = Quote::with('items')->first();
    expect($quote)->not->toBeNull();

    // 2 * 100 * (1 - 0/100) = 200 subtotal
    expect((float) $quote->subtotal)->toBe(200.0);

    // 200 * 1.23 = 246
    expect((float) $quote->total)->toBe(246.0);

    expect($quote->items)->toHaveCount(1);
    expect((float) $quote->items->first()->line_total)->toBe(200.0);
});

it('creates quote with zero total when no items', function () {
    [$user, $client] = quoteOwner();

    Livewire::actingAs($user)
        ->test(CreateQuote::class)
        ->fillForm([
            'client_id' => $client->id,
            'issued_at' => '2026-06-14',
        ])
        ->call('create')
        ->assertHasNoErrors();

    $quote = Quote::first();
    expect($quote)->not->toBeNull();
    expect((float) $quote->subtotal)->toBe(0.0);
    expect((float) $quote->total)->toBe(0.0);
});

it('applies discount_pct when calculating line_total', function () {
    [$user, $client, $tenant] = quoteOwner();

    TenantSettings::updateOrCreate(
        ['tenant_id' => $tenant->id],
        ['is_vat_payer' => false, 'default_vat_rate' => 23]
    );

    Livewire::actingAs($user)
        ->test(CreateQuote::class)
        ->fillForm([
            'client_id' => $client->id,
            'issued_at' => '2026-06-14',
            'items' => [
                [
                    'description' => 'Pozycja ze zniżką',
                    'unit' => 'piece',
                    'quantity' => 1,
                    'rate' => 200,
                    'discount_pct' => 10,
                    'vat_pct' => 23,
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoErrors();

    $quote = Quote::with('items')->first();
    // 1 * 200 * (1 - 10/100) = 180 line_total
    expect((float) $quote->items->first()->line_total)->toBe(180.0);
    expect((float) $quote->subtotal)->toBe(180.0);
    // non-VAT payer: total = subtotal
    expect((float) $quote->total)->toBe(180.0);
    expect((int) $quote->vat_rate)->toBe(0);
});

// ─── Edit ─────────────────────────────────────────────────────────────────────

it('can edit a quote and recalculates totals', function () {
    [$user, $client, $tenant] = quoteOwner();

    TenantSettings::updateOrCreate(
        ['tenant_id' => $tenant->id],
        ['is_vat_payer' => true, 'default_vat_rate' => 23]
    );

    $quote = Quote::create([
        'client_id' => $client->id,
        'number' => '2026/06/001',
        'status' => 'draft',
        'issued_at' => now(),
        'subtotal' => '0.00',
        'total' => '0.00',
    ]);

    Livewire::actingAs($user)
        ->test(EditQuote::class, ['record' => $quote->getRouteKey()])
        ->fillForm([
            'client_id' => $client->id,
            'issued_at' => '2026-06-14',
            'items' => [
                [
                    'description' => 'Mycie okien',
                    'unit' => 'h',
                    'quantity' => 3,
                    'rate' => 50,
                    'discount_pct' => 0,
                    'vat_pct' => 23,
                ],
            ],
        ])
        ->call('save')
        ->assertHasNoErrors();

    $quote->refresh();
    // 3 * 50 = 150 subtotal, 150 * 1.23 = 184.50 total
    expect((float) $quote->subtotal)->toBe(150.0);
    expect((float) $quote->total)->toBe(184.5);
});
