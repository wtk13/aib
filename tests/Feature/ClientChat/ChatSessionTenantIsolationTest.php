<?php

use App\Modules\ClientChat\Models\ChatSession;
use App\Modules\ClientChat\Services\ClientChatService;
use App\Modules\Crm\Models\Client;
use App\Modules\Tenancy\Models\Tenant;
use App\Modules\Tenancy\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('getOrCreateSession is scoped to tenant', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $userA = Tenant::bypass(fn () => User::factory()->for($tenantA, 'tenant')->create());
    $userB = Tenant::bypass(fn () => User::factory()->for($tenantB, 'tenant')->create());

    $clientA = Tenant::bypass(function () use ($tenantA) {
        Tenant::setCurrent($tenantA);

        return Client::create(['name' => 'Client A', 'client_type' => 'person', 'tenant_id' => $tenantA->id]);
    });

    $clientB = Tenant::bypass(function () use ($tenantB) {
        Tenant::setCurrent($tenantB);

        return Client::create(['name' => 'Client B', 'client_type' => 'person', 'tenant_id' => $tenantB->id]);
    });

    /** @var ClientChatService $service */
    $service = app(ClientChatService::class);

    // Tenant A creates a session
    Tenant::setCurrent($tenantA);
    $this->actingAs($userA);
    $sessionA = $service->getOrCreateSession($clientA->id, $tenantA->id);

    expect($sessionA->tenant_id)->toBe($tenantA->id);
    expect($sessionA->client_id)->toBe($clientA->id);

    // Tenant B creates a session for a different client
    Tenant::setCurrent($tenantB);
    $this->actingAs($userB);
    $sessionB = $service->getOrCreateSession($clientB->id, $tenantB->id);

    expect($sessionB->tenant_id)->toBe($tenantB->id);
    expect($sessionB->id)->not->toBe($sessionA->id);
});

it('chat sessions are not visible across tenants via global scope', function () {
    $tenantA = Tenant::factory()->create();
    $tenantB = Tenant::factory()->create();

    $userA = Tenant::bypass(fn () => User::factory()->for($tenantA, 'tenant')->create());

    $clientA = Tenant::bypass(function () use ($tenantA) {
        Tenant::setCurrent($tenantA);

        return Client::create(['name' => 'Client A', 'client_type' => 'person', 'tenant_id' => $tenantA->id]);
    });

    // Create session for tenant A
    Tenant::setCurrent($tenantA);
    $this->actingAs($userA);
    ChatSession::create([
        'tenant_id' => $tenantA->id,
        'client_id' => $clientA->id,
        'user_id' => $userA->id,
    ]);

    // Switch to tenant B — session should not be visible
    $tenantB = Tenant::factory()->create();
    Tenant::setCurrent($tenantB);

    expect(ChatSession::count())->toBe(0);
});

it('getOrCreateSession is idempotent within same tenant', function () {
    $tenant = Tenant::factory()->create();
    $user = Tenant::bypass(fn () => User::factory()->for($tenant, 'tenant')->create());
    $client = Tenant::bypass(function () use ($tenant) {
        Tenant::setCurrent($tenant);

        return Client::create(['name' => 'Idempotent Client', 'client_type' => 'person', 'tenant_id' => $tenant->id]);
    });

    /** @var ClientChatService $service */
    $service = app(ClientChatService::class);

    Tenant::setCurrent($tenant);
    $this->actingAs($user);

    $first = $service->getOrCreateSession($client->id, $tenant->id);
    $second = $service->getOrCreateSession($client->id, $tenant->id);

    expect($first->id)->toBe($second->id);
    expect(ChatSession::count())->toBe(1);
});
