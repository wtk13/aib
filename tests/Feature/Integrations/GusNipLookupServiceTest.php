<?php

use App\Modules\Integrations\Gus\GusNipLookupService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);

it('returns company data for a valid NIP', function () {
    Http::fake([
        'wyszukiwarkaregon.stat.gov.pl/*' => Http::sequence()
            // Login response
            ->push(['sessionId' => 'fake-session-123'])
            // Search response
            ->push([
                'name' => 'Firma ABC Sp. z o.o.',
                'street' => 'ul. Testowa 1',
                'city' => 'Warszawa',
                'postcode' => '00-001',
                'regon' => '123456789',
            ])
            // Logout response
            ->push(['ok' => true]),
    ]);

    $service = new GusNipLookupService;
    $result = $service->lookup('1234567890');

    expect($result)->not->toBeNull()
        ->and($result['name'])->toBe('Firma ABC Sp. z o.o.')
        ->and($result['regon'])->toBe('123456789');
});

it('returns null for an unknown NIP', function () {
    Http::fake([
        'wyszukiwarkaregon.stat.gov.pl/*' => Http::sequence()
            ->push(['sessionId' => 'fake-session-123'])
            ->push(null)  // empty result
            ->push(['ok' => true]),
    ]);

    $service = new GusNipLookupService;
    $result = $service->lookup('0000000000');

    expect($result)->toBeNull();
});

it('returns null and logs warning on API error', function () {
    Http::fake([
        'wyszukiwarkaregon.stat.gov.pl/*' => Http::response(null, 500),
    ]);

    $service = new GusNipLookupService;
    $result = $service->lookup('1234567890');

    expect($result)->toBeNull();
});
